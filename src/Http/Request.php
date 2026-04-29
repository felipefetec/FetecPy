<?php
/**
 * Representa a requisição HTTP recebida pelo servidor.
 *
 * Centraliza o acesso a método, caminho, parâmetros de query,
 * corpo JSON e cabeçalhos — evitando acesso direto a $_SERVER,
 * $_GET e php://input espalhado pelo código.
 */
declare(strict_types=1);

namespace FetecPy\Http;

class Request
{
    // Método HTTP em maiúsculas: GET, POST, PUT, DELETE, PATCH
    public readonly string $method;

    // Caminho da requisição sem o prefixo /api, ex: "modules/01/exercises/ex02"
    public readonly string $path;

    // Parâmetros extraídos da URL pelo Router, ex: ['id' => '01', 'exId' => 'ex02']
    public array $params = [];

    // Query string parseada, ex: ?page=2&limit=10 → ['page' => '2', 'limit' => '10']
    public readonly array $query;

    // Corpo da requisição decodificado como array associativo (espera JSON)
    public readonly array $body;

    // Cabeçalhos HTTP normalizados em lowercase com underscores, ex: 'authorization'
    private array $headers;

    public function __construct()
    {
        $this->method  = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->path    = $this->parsearCaminho();
        $this->query   = $_GET ?? [];
        $this->body    = $this->parsearCorpo();
        $this->headers = $this->parsearCabecalhos();
    }

    /**
     * Retorna o valor de um cabeçalho HTTP, ou null se não existir.
     * O nome é case-insensitive: 'Authorization' e 'authorization' são iguais.
     */
    public function cabecalho(string $nome): ?string
    {
        // Normaliza para lowercase com underscores para busca consistente
        $chave = strtolower(str_replace('-', '_', $nome));
        return $this->headers[$chave] ?? null;
    }

    /**
     * Extrai o token Bearer do cabeçalho Authorization.
     * Retorna null se o cabeçalho não existir ou não for Bearer.
     */
    public function tokenBearer(): ?string
    {
        $auth = $this->cabecalho('authorization');
        if ($auth === null || !str_starts_with($auth, 'Bearer ')) {
            return null;
        }
        // Remove o prefixo "Bearer " e retorna apenas o token
        return substr($auth, 7);
    }

    /**
     * Remove o prefixo /api do caminho e retorna apenas o recurso.
     * Exemplos:
     *   /api/health              → "health"
     *   /api/modules/01          → "modules/01"
     *   /api/modules/01/exercises/ex02 → "modules/01/exercises/ex02"
     */
    private function parsearCaminho(): string
    {
        // REQUEST_URI pode conter query string — descartamos tudo após '?'
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri = rawurldecode($uri ?? '/');

        // Remove o prefixo /api para que o Router trabalhe com caminhos relativos
        $uri = preg_replace('#^/api/?#', '', $uri);

        // Remove barras iniciais e finais residuais
        return trim($uri, '/');
    }

    /**
     * Lê o corpo da requisição e tenta decodificar como JSON.
     * Retorna array vazio se o corpo estiver ausente ou não for JSON válido.
     */
    private function parsearCorpo(): array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        // json_decode retorna null em caso de JSON inválido
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Coleta todos os cabeçalhos HTTP disponíveis no ambiente PHP.
     * Normaliza as chaves para lowercase com underscores.
     */
    private function parsearCabecalhos(): array
    {
        $cabecalhos = [];

        // getallheaders() está disponível em Apache e php -S (servidor embutido)
        // Em outros ambientes, fazemos fallback para $_SERVER
        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $nome => $valor) {
                $chave = strtolower(str_replace('-', '_', $nome));
                $cabecalhos[$chave] = $valor;
            }
        } else {
            // Fallback: extraímos cabeçalhos das variáveis HTTP_* do $_SERVER
            foreach ($_SERVER as $chave => $valor) {
                if (str_starts_with($chave, 'HTTP_')) {
                    $nome = strtolower(substr($chave, 5));
                    $cabecalhos[$nome] = $valor;
                }
            }
        }

        return $cabecalhos;
    }
}
