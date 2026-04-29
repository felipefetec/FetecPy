<?php
/**
 * Roteador da API — mapeia método HTTP + padrão de caminho para controllers.
 *
 * Suporta parâmetros dinâmicos no caminho usando a notação :param.
 * Exemplos de padrões:
 *   "health"                              → GET /api/health
 *   "modules"                             → GET /api/modules
 *   "modules/:id"                         → GET /api/modules/01
 *   "modules/:moduloId/exercises/:exId"   → GET /api/modules/01/exercises/ex02
 */
declare(strict_types=1);

namespace FetecPy\Http;

class Router
{
    /**
     * Tabela de rotas registradas.
     * Estrutura: [ ['method' => 'GET', 'pattern' => '...', 'handler' => [...]], ... ]
     */
    private array $rotas = [];

    // ----------------------------------------------------------------
    // Métodos de registro de rotas
    // ----------------------------------------------------------------

    public function get(string $padrao, array $handler): void
    {
        $this->registrar('GET', $padrao, $handler);
    }

    public function post(string $padrao, array $handler): void
    {
        $this->registrar('POST', $padrao, $handler);
    }

    public function put(string $padrao, array $handler): void
    {
        $this->registrar('PUT', $padrao, $handler);
    }

    public function delete(string $padrao, array $handler): void
    {
        $this->registrar('DELETE', $padrao, $handler);
    }

    // ----------------------------------------------------------------
    // Despacho da requisição
    // ----------------------------------------------------------------

    /**
     * Tenta encontrar uma rota que corresponda à requisição e executa o handler.
     *
     * Se nenhuma rota bater com o caminho → 404.
     * Se o caminho bater mas o método não → 405 (Method Not Allowed).
     */
    public function despachar(Request $request): void
    {
        $rotasDoPath = [];

        foreach ($this->rotas as $rota) {
            $params = $this->combinar($rota['padrao'], $request->path);

            // Caminho não bate com este padrão — pula
            if ($params === null) {
                continue;
            }

            // Caminho bate — registra para verificação de método
            $rotasDoPath[] = $rota;

            // Método também bate — executa o handler
            if ($rota['method'] === $request->method) {
                $request->params = $params;
                $this->executar($rota['handler'], $request);
                return;
            }
        }

        // Caminho foi encontrado mas sem suporte ao método usado
        if (!empty($rotasDoPath)) {
            $metodosPermitidos = array_column($rotasDoPath, 'method');
            JsonResponse::erro(
                'Método não permitido. Use: ' . implode(', ', $metodosPermitidos),
                405
            );
        }

        // Nenhuma rota encontrada para este caminho
        JsonResponse::erro('Rota não encontrada: ' . $request->path, 404);
    }

    // ----------------------------------------------------------------
    // Internos
    // ----------------------------------------------------------------

    private function registrar(string $method, string $padrao, array $handler): void
    {
        $this->rotas[] = [
            'method'  => strtoupper($method),
            'padrao'  => trim($padrao, '/'),
            'handler' => $handler,
        ];
    }

    /**
     * Tenta combinar o caminho real com o padrão de rota.
     *
     * Converte `:param` em grupo de captura regex e verifica se bate.
     * Retorna array de parâmetros capturados ou null se não houver combinação.
     *
     * Exemplos:
     *   padrão "modules/:id"  + caminho "modules/01" → ['id' => '01']
     *   padrão "health"       + caminho "health"      → []
     *   padrão "modules/:id"  + caminho "health"      → null
     */
    private function combinar(string $padrao, string $caminho): ?array
    {
        // Extrai os nomes dos parâmetros (:param) antes de transformar em regex
        preg_match_all('/:(\w+)/', $padrao, $nomes);
        $nomesParams = $nomes[1]; // ex: ['id', 'exId']

        // Substitui :param por grupo de captura que aceita qualquer valor sem barra
        $regex = preg_replace('/:(\w+)/', '([^/]+)', $padrao);
        $regex = '#^' . $regex . '$#';

        if (!preg_match($regex, $caminho, $valores)) {
            return null;
        }

        // $valores[0] é o match completo — removemos para pegar só os grupos
        array_shift($valores);

        // Monta array associativo: nome do param → valor capturado
        return array_combine($nomesParams, $valores) ?: [];
    }

    /**
     * Instancia o controller e chama a action correspondente.
     *
     * O handler é um array no formato [NomeController::class, 'nomeAction'].
     * A action recebe a Request e deve usar JsonResponse para responder.
     */
    private function executar(array $handler, Request $request): void
    {
        [$classeController, $action] = $handler;

        if (!class_exists($classeController)) {
            JsonResponse::erro('Controller não encontrado: ' . $classeController, 500);
        }

        $controller = new $classeController();

        if (!method_exists($controller, $action)) {
            JsonResponse::erro('Action não encontrada: ' . $action, 500);
        }

        $controller->$action($request);
    }
}
