<?php
/**
 * Testes do AuthController — camada HTTP de autenticação.
 *
 * Usa Request::simular() para montar requisições sem servidor HTTP.
 * Captura ResponseException (lançada pelo JsonResponse em modo teste)
 * para verificar status code e payload da resposta.
 */
declare(strict_types=1);

namespace FetecPy\Tests\Backend;

use FetecPy\Controllers\AuthController;
use FetecPy\Database;
use FetecPy\Http\Request;
use FetecPy\Http\ResponseException;
use PHPUnit\Framework\TestCase;

class AuthControllerTest extends TestCase
{
    private AuthController $controller;

    protected function setUp(): void
    {
        Database::resetar();
        Database::executarMigrations();
        $this->controller = new AuthController();
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    /**
     * Faz login e retorna a ResponseException capturada.
     * Centraliza a criação da requisição de login nos testes.
     */
    private function tentarLogin(string $nome, string $sobrenome, string $pin): ResponseException
    {
        $request = Request::simular('POST', 'auth/login', [
            'nome'      => $nome,
            'sobrenome' => $sobrenome,
            'pin'       => $pin,
        ]);

        try {
            $this->controller->login($request);
            $this->fail('ResponseException não foi lançada pelo controller');
        } catch (ResponseException $e) {
            return $e;
        }
    }

    // ----------------------------------------------------------------
    // Testes de login
    // ----------------------------------------------------------------

    public function testLoginRetornaToken(): void
    {
        $resposta = $this->tentarLogin('Felipe', 'Tavares', '1234');

        // Novo cadastro deve retornar 201
        $this->assertSame(201, $resposta->status);

        $dados = $resposta->dados();
        $this->assertArrayHasKey('token', $dados);
        $this->assertNotEmpty($dados['token']);
        $this->assertTrue($dados['novo']);
        $this->assertArrayHasKey('usuario', $dados);

        // pin_hash nunca deve aparecer na resposta HTTP
        $this->assertArrayNotHasKey('pin_hash', $dados['usuario']);
    }

    public function testLoginExistenteRetorna200(): void
    {
        // Primeiro acesso: cria o usuário (201)
        $this->tentarLogin('Felipe', 'Tavares', '1234');

        // Segundo acesso com mesmo PIN: autentica (200)
        $resposta = $this->tentarLogin('Felipe', 'Tavares', '1234');

        $this->assertSame(200, $resposta->status);
        $this->assertFalse($resposta->dados()['novo']);
    }

    public function testLoginInvalidoRetorna401(): void
    {
        // Cria o usuário com PIN correto
        $this->tentarLogin('Felipe', 'Tavares', '1234');

        // Tenta login com PIN errado
        $resposta = $this->tentarLogin('Felipe', 'Tavares', '9999');

        $this->assertSame(401, $resposta->status);
        $this->assertArrayHasKey('erro', $resposta->dados());
    }

    public function testLoginSemCamposRetorna422(): void
    {
        $request = Request::simular('POST', 'auth/login', [
            'nome' => 'Felipe',
            // sobrenome e pin ausentes
        ]);

        try {
            $this->controller->login($request);
            $this->fail('ResponseException não lançada');
        } catch (ResponseException $e) {
            $this->assertSame(422, $e->status);
        }
    }

    public function testRateLimitBloqueiaApos5Tentativas(): void
    {
        // Cria o usuário
        $this->tentarLogin('Felipe', 'Tavares', '1234');

        // 5 tentativas com PIN errado
        for ($i = 0; $i < 5; $i++) {
            $this->tentarLogin('Felipe', 'Tavares', '0000');
        }

        // Na 6ª tentativa (mesmo com PIN correto), deve ser bloqueado por rate limit
        $resposta = $this->tentarLogin('Felipe', 'Tavares', '1234');

        $this->assertSame(429, $resposta->status);
        $this->assertArrayHasKey('erro', $resposta->dados());
    }

    // ----------------------------------------------------------------
    // Testes de logout
    // ----------------------------------------------------------------

    public function testLogoutSemTokenRetorna401(): void
    {
        // Sem cabeçalho Authorization
        $request = Request::simular('POST', 'auth/logout');

        try {
            $this->controller->logout($request);
            $this->fail('ResponseException não lançada');
        } catch (ResponseException $e) {
            $this->assertSame(401, $e->status);
        }
    }

    public function testLogoutComTokenInvalidoRetorna401(): void
    {
        $request = Request::simular('POST', 'auth/logout', [], [
            'Authorization' => 'Bearer token_completamente_invalido',
        ]);

        try {
            $this->controller->logout($request);
            $this->fail('ResponseException não lançada');
        } catch (ResponseException $e) {
            $this->assertSame(401, $e->status);
        }
    }

    public function testLogoutComTokenValidoRetorna200(): void
    {
        // Faz login para obter token válido
        $respostaLogin = $this->tentarLogin('Felipe', 'Tavares', '1234');
        $token         = $respostaLogin->dados()['token'];

        // Faz logout com o token
        $request = Request::simular('POST', 'auth/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        try {
            $this->controller->logout($request);
            $this->fail('ResponseException não lançada');
        } catch (ResponseException $e) {
            $this->assertSame(200, $e->status);
            $this->assertArrayHasKey('mensagem', $e->dados());
        }
    }
}
