<?php
/**
 * Middleware de autenticação.
 *
 * Valida o token Bearer do cabeçalho Authorization e, se válido,
 * popula $request->user com os dados do aluno logado.
 *
 * Uso no controller: chamar AuthMiddleware::exigir($request) no início
 * de qualquer action que precise de usuário autenticado.
 * Se o token for inválido, a chamada já encerra a requisição com 401.
 */
declare(strict_types=1);

namespace FetecPy\Http;

use FetecPy\Services\AuthService;

class AuthMiddleware
{
    /**
     * Verifica o token da requisição e preenche $request->user.
     *
     * Encerra com 401 se:
     *   - Cabeçalho Authorization estiver ausente
     *   - Token não seguir o formato "Bearer <token>"
     *   - Token não existir ou estiver expirado no banco
     *
     * Se a verificação passar, $request->user ficará disponível
     * para o controller usar sem precisar buscar o usuário de novo.
     */
    public static function exigir(Request $request): void
    {
        $token = $request->tokenBearer();

        if ($token === null) {
            JsonResponse::erro('Autenticação necessária. Envie o header Authorization: Bearer <token>.', 401);
        }

        $authService = new AuthService();
        $usuario     = $authService->validarToken($token);

        if ($usuario === null) {
            JsonResponse::erro('Token inválido ou expirado. Faça login novamente.', 401);
        }

        // Injeta o usuário autenticado na requisição para uso no controller
        $request->user = $usuario;
    }
}
