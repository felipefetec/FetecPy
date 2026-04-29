<?php
/**
 * Exceção lançada pelo JsonResponse quando está em modo de teste.
 *
 * Em produção, JsonResponse chama exit() após enviar o JSON.
 * Em testes, chamar exit() encerraria o processo do PHPUnit inteiro.
 * Por isso, quando JsonResponse::$modoTeste = true, ele lança esta
 * exceção em vez de sair — permitindo que os testes capturem e
 * verifiquem a resposta sem interromper a suíte.
 */
declare(strict_types=1);

namespace FetecPy\Http;

use RuntimeException;

class ResponseException extends RuntimeException
{
    // Conteúdo JSON que seria enviado ao cliente
    public readonly string $corpo;

    // Código de status HTTP da resposta
    public readonly int $status;

    public function __construct(string $corpo, int $status)
    {
        parent::__construct("Resposta interceptada em modo de teste (status $status)");
        $this->corpo  = $corpo;
        $this->status = $status;
    }

    /**
     * Decodifica o corpo JSON e retorna como array associativo.
     * Atalho para verificar campos da resposta nos testes.
     */
    public function dados(): array
    {
        return json_decode($this->corpo, true) ?? [];
    }
}
