<?php
/**
 * Testes do AuthService — lógica pura de autenticação.
 *
 * Cada teste começa com banco em memória limpo (setUp reinicia o singleton).
 * Não passa por HTTP — chama o serviço diretamente para testar unidades isoladas.
 */
declare(strict_types=1);

namespace FetecPy\Tests\Backend;

use FetecPy\Database;
use FetecPy\Exceptions\AuthException;
use FetecPy\Services\AuthService;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $service;

    /**
     * Reinicia o banco em memória antes de cada teste.
     * Como DB_PATH=:memory:, resetar o singleton cria uma nova conexão
     * com banco vazio — as migrations recriam as tabelas do zero.
     */
    protected function setUp(): void
    {
        Database::resetar();
        Database::executarMigrations();
        $this->service = new AuthService();
    }

    // ----------------------------------------------------------------
    // normalizarChave
    // ----------------------------------------------------------------

    public function testNormalizarChaveRemoveAcentos(): void
    {
        $this->assertSame('joao_silva',  $this->service->normalizarChave('João', 'Silva'));
        $this->assertSame('maria_lopez', $this->service->normalizarChave('María', 'López'));
        $this->assertSame('jose_cunha',  $this->service->normalizarChave('José', 'Cunhã'));
    }

    public function testNormalizarChaveTrocaEspacosPorUnderline(): void
    {
        // Sobrenomes compostos com espaço devem virar underscores
        $this->assertSame('ana_da_silva',     $this->service->normalizarChave('Ana', 'da Silva'));
        $this->assertSame('carlos_de_souza',  $this->service->normalizarChave('Carlos', 'de Souza'));
    }

    public function testNormalizarChaveLowercase(): void
    {
        // Tudo em maiúsculas deve virar lowercase
        $this->assertSame('felipe_tavares', $this->service->normalizarChave('FELIPE', 'TAVARES'));
        $this->assertSame('ana_silva',      $this->service->normalizarChave('ANA', 'SILVA'));
    }

    // ----------------------------------------------------------------
    // cadastrarOuLogin
    // ----------------------------------------------------------------

    public function testCadastraNovoUsuario(): void
    {
        $resultado = $this->service->cadastrarOuLogin('Felipe', 'Tavares', '1234');

        // Deve indicar que é um novo cadastro
        $this->assertTrue($resultado['novo']);

        // Token deve estar presente e não vazio
        $this->assertNotEmpty($resultado['token']);

        // Dados do usuário devem estar corretos
        $this->assertSame('Felipe',  $resultado['usuario']['nome']);
        $this->assertSame('Tavares', $resultado['usuario']['sobrenome']);
        $this->assertSame(0, (int) $resultado['usuario']['xp_total']);

        // pin_hash nunca deve vazar para fora do serviço
        $this->assertArrayNotHasKey('pin_hash', $resultado['usuario']);
    }

    public function testValidaPinCorreto(): void
    {
        // Cria usuário na primeira chamada
        $this->service->cadastrarOuLogin('Felipe', 'Tavares', '1234');

        // Segunda chamada com PIN correto deve autenticar (não criar novo)
        $resultado = $this->service->cadastrarOuLogin('Felipe', 'Tavares', '1234');

        $this->assertFalse($resultado['novo']);
        $this->assertNotEmpty($resultado['token']);
        $this->assertSame('Felipe', $resultado['usuario']['nome']);
    }

    public function testRejeitaPinIncorreto(): void
    {
        // Cria o usuário primeiro
        $this->service->cadastrarOuLogin('Felipe', 'Tavares', '1234');

        // Tentativa com PIN errado deve lançar AuthException
        $this->expectException(AuthException::class);
        $this->expectExceptionCode(AuthException::PIN_INVALIDO);

        $this->service->cadastrarOuLogin('Felipe', 'Tavares', '9999');
    }

    // ----------------------------------------------------------------
    // validarToken / criarSessao
    // ----------------------------------------------------------------

    public function testCriaSessaoComToken(): void
    {
        $resultado = $this->service->cadastrarOuLogin('Felipe', 'Tavares', '1234');
        $token     = $resultado['token'];

        // Token deve ser hex de 64 caracteres (32 bytes)
        $this->assertSame(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    public function testValidaToken(): void
    {
        $resultado = $this->service->cadastrarOuLogin('Felipe', 'Tavares', '1234');

        $usuario = $this->service->validarToken($resultado['token']);

        $this->assertNotNull($usuario);
        $this->assertSame('Felipe', $usuario['nome']);

        // pin_hash nunca deve estar presente
        $this->assertArrayNotHasKey('pin_hash', $usuario);
    }

    public function testRejeitaUsuarioInexistente(): void
    {
        // Token aleatório não deve corresponder a nenhuma sessão
        $token   = bin2hex(random_bytes(32));
        $usuario = $this->service->validarToken($token);

        $this->assertNull($usuario);
    }

    public function testTokenExpiradoEhInvalido(): void
    {
        $resultado = $this->service->cadastrarOuLogin('Felipe', 'Tavares', '1234');
        $token     = $resultado['token'];

        // Expira a sessão manualmente diretamente no banco
        $pdo = Database::getConnection();
        $pdo->prepare("UPDATE sessions SET expires_at = '2000-01-01 00:00:00' WHERE token = ?")
            ->execute([$token]);

        // Token expirado deve retornar null
        $this->assertNull($this->service->validarToken($token));
    }

    // ----------------------------------------------------------------
    // logout
    // ----------------------------------------------------------------

    public function testLogoutInvalidaToken(): void
    {
        $resultado = $this->service->cadastrarOuLogin('Felipe', 'Tavares', '1234');
        $token     = $resultado['token'];

        // Token válido antes do logout
        $this->assertNotNull($this->service->validarToken($token));

        // Após logout, token não deve mais ser válido
        $this->service->logout($token);
        $this->assertNull($this->service->validarToken($token));
    }
}
