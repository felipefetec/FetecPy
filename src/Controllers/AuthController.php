<?php
/**
 * Controller de autenticação.
 *
 * Gerencia login (cadastro implícito) e logout.
 * Toda validação de regra de negócio fica no AuthService —
 * o controller só trata HTTP: valida entrada, chama serviço, responde.
 */
declare(strict_types=1);

namespace FetecPy\Controllers;

use FetecPy\Exceptions\AuthException;
use FetecPy\Http\AuthMiddleware;
use FetecPy\Http\JsonResponse;
use FetecPy\Http\Request;
use FetecPy\Services\AuthService;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * POST /api/auth/login
     * Body: { "nome": "Felipe", "sobrenome": "Tavares", "pin": "1234" }
     *
     * - Se o aluno nunca acessou: cria conta e retorna token (201)
     * - Se já existe: valida PIN e retorna token (200)
     * - PIN errado: 401
     * - Rate limit: 429
     */
    public function login(Request $request): void
    {
        // Valida presença e tipo dos campos obrigatórios
        $nome      = trim($request->body['nome'] ?? '');
        $sobrenome = trim($request->body['sobrenome'] ?? '');
        $pin       = trim($request->body['pin'] ?? '');

        if ($nome === '' || $sobrenome === '' || $pin === '') {
            JsonResponse::erro('Os campos nome, sobrenome e pin são obrigatórios.', 422);
        }

        // PIN deve ser numérico e ter entre 4 e 8 dígitos
        if (!preg_match('/^\d{4,8}$/', $pin)) {
            JsonResponse::erro('O PIN deve conter apenas dígitos (4 a 8 caracteres).', 422);
        }

        try {
            $resultado = $this->authService->cadastrarOuLogin($nome, $sobrenome, $pin);
        } catch (AuthException $e) {
            $status = match ($e->getCode()) {
                AuthException::RATE_LIMIT => 429,
                AuthException::PIN_INVALIDO => 401,
                default => 400,
            };
            JsonResponse::erro($e->getMessage(), $status);
        }

        // 201 para novo cadastro, 200 para login de usuário existente
        $status = $resultado['novo'] ? 201 : 200;

        JsonResponse::enviar([
            'token'   => $resultado['token'],
            'usuario' => $resultado['usuario'],
            'novo'    => $resultado['novo'],
        ], $status);
    }

    /**
     * POST /api/auth/logout
     * Header: Authorization: Bearer <token>
     *
     * Invalida o token da sessão atual.
     * Requer autenticação — retorna 401 se token inválido.
     */
    public function logout(Request $request): void
    {
        // Valida o token antes de tentar o logout
        AuthMiddleware::exigir($request);

        // tokenBearer() não retorna null aqui pois AuthMiddleware já garantiu
        $token = $request->tokenBearer();
        $this->authService->logout($token);

        JsonResponse::enviar(['mensagem' => 'Sessão encerrada com sucesso.']);
    }
}
