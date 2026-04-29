<?php
/**
 * Teste de sanidade do endpoint GET /api/health.
 *
 * Verifica que o HealthController responde corretamente:
 *   - Status HTTP 200
 *   - Campo "status" igual a "ok"
 *   - Campo "version" presente
 */
declare(strict_types=1);

namespace FetecPy\Tests\Backend;

use FetecPy\Controllers\HealthController;
use FetecPy\Http\Request;
use FetecPy\Http\ResponseException;
use PHPUnit\Framework\TestCase;

class HealthCheckTest extends TestCase
{
    /**
     * Cria um Request falso apontando para GET /api/health.
     * Necessário porque Request lê variáveis globais do servidor ($_SERVER).
     */
    private function criarRequestHealth(): Request
    {
        // Configura o ambiente como se fosse uma requisição HTTP real
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/api/health';

        return new Request();
    }

    public function testHealthRetornaStatus200(): void
    {
        $controller = new HealthController();
        $request    = $this->criarRequestHealth();

        // JsonResponse::$modoTeste = true (ativado no bootstrap),
        // então enviar() lança ResponseException em vez de chamar exit()
        $this->expectException(ResponseException::class);

        $controller->index($request);
    }

    public function testHealthRetornaStatusOk(): void
    {
        $controller = new HealthController();
        $request    = $this->criarRequestHealth();

        try {
            $controller->index($request);
            $this->fail('ResponseException não foi lançada');
        } catch (ResponseException $resposta) {
            // Verifica o código HTTP
            $this->assertSame(200, $resposta->status);

            // Verifica o conteúdo do JSON
            $dados = $resposta->dados();
            $this->assertSame('ok', $dados['status']);
            $this->assertArrayHasKey('version', $dados);
        }
    }
}
