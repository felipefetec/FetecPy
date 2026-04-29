<?php
/**
 * Testes do UserController — endpoint GET /api/me.
 *
 * Verifica que o perfil do usuário é retornado corretamente,
 * que a rota é protegida por autenticação e que dados sensíveis
 * (pin_hash) nunca aparecem na resposta.
 */
declare(strict_types=1);

namespace FetecPy\Tests\Backend;

use FetecPy\Controllers\UserController;
use FetecPy\Database;
use FetecPy\Http\Request;
use FetecPy\Http\ResponseException;
use FetecPy\Services\AuthService;
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase
{
    private UserController $controller;
    private AuthService    $authService;

    protected function setUp(): void
    {
        Database::resetar();
        Database::executarMigrations();
        $this->controller  = new UserController();
        $this->authService = new AuthService();
    }

    // ----------------------------------------------------------------
    // Helper
    // ----------------------------------------------------------------

    /**
     * Cria um usuário e retorna o token de sessão válido.
     * Atalho usado em todos os testes que precisam de usuário autenticado.
     */
    private function criarUsuarioEObterToken(
        string $nome = 'Felipe',
        string $sobrenome = 'Tavares',
        string $pin = '1234'
    ): string {
        $resultado = $this->authService->cadastrarOuLogin($nome, $sobrenome, $pin);
        return $resultado['token'];
    }

    // ----------------------------------------------------------------
    // Testes
    // ----------------------------------------------------------------

    public function testMeRetornaUsuarioLogado(): void
    {
        $token   = $this->criarUsuarioEObterToken();
        $request = Request::simular('GET', 'me', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        try {
            $this->controller->me($request);
            $this->fail('ResponseException não lançada');
        } catch (ResponseException $e) {
            $this->assertSame(200, $e->status);

            $dados = $e->dados();

            // Campos obrigatórios do perfil
            $this->assertSame('Felipe',  $dados['nome']);
            $this->assertSame('Tavares', $dados['sobrenome']);
            $this->assertArrayHasKey('xp_total',      $dados);
            $this->assertArrayHasKey('streak_dias',   $dados);
            $this->assertArrayHasKey('ultimo_acesso', $dados);
            $this->assertArrayHasKey('created_at',    $dados);

            // Lista de badges deve existir (vazia para usuário novo)
            $this->assertArrayHasKey('badges', $dados);
            $this->assertIsArray($dados['badges']);
        }
    }

    public function testMeSemTokenRetorna401(): void
    {
        // Requisição sem cabeçalho Authorization
        $request = Request::simular('GET', 'me');

        try {
            $this->controller->me($request);
            $this->fail('ResponseException não lançada');
        } catch (ResponseException $e) {
            $this->assertSame(401, $e->status);
        }
    }

    public function testMeComTokenInvalidoRetorna401(): void
    {
        $request = Request::simular('GET', 'me', [], [
            'Authorization' => 'Bearer token_que_nao_existe',
        ]);

        try {
            $this->controller->me($request);
            $this->fail('ResponseException não lançada');
        } catch (ResponseException $e) {
            $this->assertSame(401, $e->status);
        }
    }

    public function testMeNaoExpoeHashPin(): void
    {
        $token   = $this->criarUsuarioEObterToken();
        $request = Request::simular('GET', 'me', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        try {
            $this->controller->me($request);
        } catch (ResponseException $e) {
            // pin_hash nunca deve aparecer em nenhuma resposta da API
            $this->assertArrayNotHasKey('pin_hash', $e->dados());
        }
    }

    public function testMeRetornaDadosDoUsuarioCorreto(): void
    {
        // Cria dois usuários distintos para garantir que /me retorna o certo
        $this->authService->cadastrarOuLogin('João', 'Silva', '1111');
        $tokenFelipe = $this->criarUsuarioEObterToken('Felipe', 'Tavares', '1234');

        $request = Request::simular('GET', 'me', [], [
            'Authorization' => 'Bearer ' . $tokenFelipe,
        ]);

        try {
            $this->controller->me($request);
        } catch (ResponseException $e) {
            // Deve retornar Felipe, não João
            $this->assertSame('Felipe',  $e->dados()['nome']);
            $this->assertSame('Tavares', $e->dados()['sobrenome']);
        }
    }
}
