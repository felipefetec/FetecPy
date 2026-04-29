<?php
/**
 * Responsável por enviar respostas JSON padronizadas para o cliente.
 *
 * Centraliza a serialização e os cabeçalhos HTTP, garantindo que toda
 * a API retorne o mesmo formato — inclusive nos casos de erro.
 */
declare(strict_types=1);

namespace FetecPy\Http;

class JsonResponse
{
    /**
     * Envia uma resposta JSON de sucesso com os dados fornecidos.
     *
     * @param mixed $dados  Qualquer valor serializável em JSON
     * @param int   $status Código HTTP (padrão 200)
     */
    public static function enviar(mixed $dados, int $status = 200): never
    {
        self::definirCabecalhos($status);
        echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Envia uma resposta de erro com mensagem legível pelo cliente.
     *
     * Formato padrão: { "erro": "mensagem descritiva" }
     *
     * @param string $mensagem Descrição do erro em pt-BR
     * @param int    $status   Código HTTP de erro (4xx ou 5xx)
     */
    public static function erro(string $mensagem, int $status = 400): never
    {
        self::definirCabecalhos($status);
        echo json_encode(
            ['erro' => $mensagem],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        exit;
    }

    /**
     * Define os cabeçalhos HTTP obrigatórios para qualquer resposta JSON.
     *
     * CORS permissivo aqui porque o frontend e a API estão no mesmo domínio.
     * Se algum dia separar, ajustar o Access-Control-Allow-Origin para o domínio real.
     */
    private static function definirCabecalhos(int $status): void
    {
        // Impede que cabeçalhos sejam enviados após output (erro fatal em produção)
        if (headers_sent()) {
            return;
        }

        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        // Sem cache para endpoints de API — dados mudam a cada requisição
        header('Cache-Control: no-store, no-cache, must-revalidate');
    }
}
