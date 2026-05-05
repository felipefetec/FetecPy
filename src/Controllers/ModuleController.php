<?php
/**
 * Controller de módulos de ensino.
 *
 * Lê os arquivos Markdown da pasta content/, faz parse do front-matter
 * e retorna o conteúdo estruturado para o frontend renderizar.
 *
 * Rotas:
 *   GET /api/modules        → lista de todos os módulos (sem conteúdo MD)
 *   GET /api/modules/:id    → conteúdo completo + exercícios + quiz
 */
declare(strict_types=1);

namespace FetecPy\Controllers;

use FetecPy\Http\AuthMiddleware;
use FetecPy\Http\JsonResponse;
use FetecPy\Http\Request;
use FetecPy\Services\ProgressService;

class ModuleController
{
    private string $contentDir;
    private string $exercisesDir;
    private string $quizDir;
    private ProgressService $progress;

    public function __construct()
    {
        $raiz               = dirname(__DIR__, 2);
        $this->contentDir   = $raiz . '/content';
        $this->exercisesDir = $raiz . '/exercises';
        $this->quizDir      = $raiz . '/content/quiz';
        $this->progress     = new ProgressService();
    }

    // ----------------------------------------------------------------
    // GET /api/modules — lista todos os módulos disponíveis
    // ----------------------------------------------------------------

    public function index(Request $request): void
    {
        AuthMiddleware::exigir($request);

        $modulos = $this->listarModulos();
        JsonResponse::enviar($modulos);
    }

    // ----------------------------------------------------------------
    // GET /api/modules/:id — conteúdo completo de um módulo
    // ----------------------------------------------------------------

    public function show(Request $request): void
    {
        AuthMiddleware::exigir($request);

        $userId = (int) $request->user['id'];
        $id     = $request->params['id'] ?? '';

        // Valida que o ID é numérico de 2 dígitos (ex: "01", "08")
        if (!preg_match('/^\d{2}$/', $id)) {
            JsonResponse::erro('ID de módulo inválido.', 400);
        }

        $arquivo = $this->encontrarArquivoModulo($id);
        if ($arquivo === null) {
            JsonResponse::erro("Módulo '{$id}' não encontrado.", 404);
        }

        $conteudo    = file_get_contents($arquivo);
        $parsed      = $this->parsearFrontMatter($conteudo);
        $frontmatter = $parsed['frontmatter'];
        $markdown    = $parsed['conteudo'];

        // Exercícios: lista os JSONs sem solução e injeta status do aluno.
        // O status permite o frontend exibir a medalha sem chamada extra à API.
        $exercicios  = $this->listarExercicios($id);
        $concluidos  = $this->statusExercicios($userId, $id);
        foreach ($exercicios as &$ex) {
            $exKey   = explode('-', $ex['id'], 2)[1] ?? ''; // "01-ex03" → "ex03"
            $prog    = $concluidos[$exKey] ?? null;
            // status_aluno: usado para a medalha e para saber se pode "Refazer"
            $ex['status_aluno']  = $prog ? $prog['status']       : null;
            // codigo_salvo: carregado no editor ao clicar em "Refazer" ou reabrir
            $ex['codigo_salvo']  = $prog ? $prog['codigo_salvo'] : null;
        }
        unset($ex);

        // Quiz: retorna perguntas SEM resposta_correta
        $quiz = $this->carregarQuiz($id);

        JsonResponse::enviar([
            'id'           => $id,
            'titulo'       => $frontmatter['titulo']            ?? "Módulo $id",
            'duracao'      => $frontmatter['duracao_estimada']  ?? null,
            'pre_requisito'=> $frontmatter['pre_requisito']     ?? null,
            'conteudo_md'  => $markdown,
            'exercicios'   => $exercicios,
            'quiz'         => $quiz,
        ]);
    }

    // ----------------------------------------------------------------
    // GET /api/modules/:id/sections
    // Retorna quais seções do módulo o aluno já marcou como lidas.
    // ----------------------------------------------------------------

    public function listarSecoes(Request $request): void
    {
        AuthMiddleware::exigir($request);

        $userId   = (int) $request->user['id'];
        $moduloId = $request->params['id'] ?? '';

        if (!preg_match('/^\d{2}$/', $moduloId)) {
            JsonResponse::erro('ID de módulo inválido.', 400);
        }

        $pdo  = \FetecPy\Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT item_id FROM progress
             WHERE user_id = ? AND modulo = ? AND item_tipo = "secao" AND status = "concluido"'
        );
        $stmt->execute([$userId, $moduloId]);
        $lidas = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $total     = $this->contarSecoesModulo($moduloId);
        $percentual = $total > 0 ? (int) round((count($lidas) / $total) * 100) : 0;

        JsonResponse::enviar([
            'lidas'     => $lidas,
            'total'     => $total,
            'percentual' => $percentual,
        ]);
    }

    // ----------------------------------------------------------------
    // POST /api/modules/:id/sections/:secaoId
    // Marca uma seção como lida e retorna % de progresso do módulo.
    // ----------------------------------------------------------------

    public function marcarSecao(Request $request): void
    {
        AuthMiddleware::exigir($request);

        $userId   = (int) $request->user['id'];
        $moduloId = $request->params['id']      ?? '';
        $secaoId  = $request->params['secaoId'] ?? '';

        if (!preg_match('/^\d{2}$/', $moduloId)) {
            JsonResponse::erro('ID de módulo inválido.', 400);
        }
        if (empty($secaoId)) {
            JsonResponse::erro('ID da seção é obrigatório.', 400);
        }

        $this->progress->marcarSecaoLida($userId, $moduloId, $secaoId);

        // Conta total de seções disponíveis no módulo (baseado no arquivo MD)
        $totalSecoes = $this->contarSecoesModulo($moduloId);
        $lidas       = $this->progress->secoesLidas($userId, $moduloId);
        $percent     = $totalSecoes > 0 ? (int) round(($lidas / $totalSecoes) * 100) : 0;

        JsonResponse::enviar([
            'secao_id'     => $secaoId,
            'secoes_lidas' => $lidas,
            'total_secoes' => $totalSecoes,
            'percent'      => $percent,
        ]);
    }

    // ----------------------------------------------------------------
    // Helpers privados
    // ----------------------------------------------------------------

    /**
     * Lista todos os módulos encontrados em content/, em ordem.
     * Retorna apenas os metadados do front-matter, sem o conteúdo MD.
     */
    private function listarModulos(): array
    {
        $arquivos = glob($this->contentDir . '/[0-9][0-9]-*.md');
        if (empty($arquivos)) {
            return [];
        }

        sort($arquivos); // Garante ordem 01, 02, 03...

        $modulos = [];
        foreach ($arquivos as $arquivo) {
            $id      = $this->extrairId($arquivo);
            $conteudo = file_get_contents($arquivo);
            $parsed  = $this->parsearFrontMatter($conteudo);
            $fm      = $parsed['frontmatter'];

            $modulos[] = [
                'id'            => $id,
                'titulo'        => $fm['titulo']           ?? "Módulo $id",
                'duracao'       => $fm['duracao_estimada'] ?? null,
                'pre_requisito' => $fm['pre_requisito']    ?? null,
            ];
        }

        return $modulos;
    }

    /**
     * Encontra o arquivo .md de um módulo pelo ID (ex: "01").
     * O nome do arquivo segue o padrão "01-nome-qualquer.md".
     */
    private function encontrarArquivoModulo(string $id): ?string
    {
        $arquivos = glob($this->contentDir . "/{$id}-*.md");
        return !empty($arquivos) ? $arquivos[0] : null;
    }

    /**
     * Extrai o ID numérico do nome do arquivo.
     * Ex: "/path/content/01-algoritmos.md" → "01"
     */
    private function extrairId(string $caminho): string
    {
        return substr(basename($caminho), 0, 2);
    }

    /**
     * Faz parse do front-matter YAML delimitado por --- no início do arquivo.
     *
     * Suporta apenas chaves simples (key: value) — não suporta listas YAML
     * nem objetos aninhados, o que é suficiente para o front-matter dos módulos.
     *
     * Retorna ['frontmatter' => [...], 'conteudo' => 'markdown sem o front-matter']
     */
    private function parsearFrontMatter(string $texto): array
    {
        $frontmatter = [];
        $conteudo    = $texto;

        // Detecta bloco --- ... --- no início do arquivo
        if (preg_match('/^---\r?\n(.*?)\r?\n---\r?\n/s', $texto, $matches)) {
            $yaml     = $matches[1];
            $conteudo = substr($texto, strlen($matches[0]));

            // Parseia cada linha "chave: valor" do YAML
            foreach (explode("\n", $yaml) as $linha) {
                $linha = trim($linha);
                if (preg_match('/^([\w_]+)\s*:\s*"?([^"]*)"?\s*$/', $linha, $m)) {
                    $frontmatter[trim($m[1])] = trim($m[2]);
                }
            }
        }

        return ['frontmatter' => $frontmatter, 'conteudo' => $conteudo];
    }

    /**
     * Lista os exercícios de um módulo removendo a solução antes de retornar.
     *
     * A solução não deve ser enviada ao frontend antes do aluno tentar —
     * ela é removida aqui e só enviada via endpoint específico após 3 tentativas.
     */
    private function listarExercicios(string $id): array
    {
        $pasta = $this->exercisesDir . '/' . $id;
        if (!is_dir($pasta)) {
            return [];
        }

        $arquivos = glob($pasta . '/*.json');
        if (empty($arquivos)) {
            return [];
        }

        sort($arquivos);

        $exercicios = [];
        foreach ($arquivos as $arquivo) {
            $dados = json_decode(file_get_contents($arquivo), true);
            if (!is_array($dados)) {
                continue;
            }

            // Remove a solução — o aluno deve tentar primeiro
            unset($dados['solucao']);

            $exercicios[] = $dados;
        }

        return $exercicios;
    }

    /**
     * Carrega o quiz de um módulo removendo as respostas corretas.
     *
     * As respostas são validadas pelo backend (Prompt 5.x) — o frontend
     * nunca recebe a chave resposta_correta diretamente.
     */
    private function carregarQuiz(string $id): array
    {
        $arquivo = $this->quizDir . "/{$id}.json";
        if (!file_exists($arquivo)) {
            return [];
        }

        $dados = json_decode(file_get_contents($arquivo), true);
        if (!is_array($dados)) {
            return [];
        }

        // Suporta dois formatos:
        // 1. Array direto de perguntas: [{id, pergunta, ...}, ...]
        // 2. Objeto com chave "perguntas": {modulo, titulo, perguntas: [...]}
        $perguntas = isset($dados['perguntas']) ? $dados['perguntas'] : $dados;
        if (!is_array($perguntas)) {
            return [];
        }

        // Remove respostas corretas de cada pergunta antes de enviar
        return array_map(function (array $pergunta) {
            unset($pergunta['resposta_correta']);
            return $pergunta;
        }, $perguntas);
    }

    /**
     * Conta as seções H2 (##) no arquivo Markdown de um módulo.
     * Usado para calcular % de progresso de leitura.
     */
    private function contarSecoesModulo(string $moduloId): int
    {
        $arquivo = $this->encontrarArquivoModulo($moduloId);
        if ($arquivo === null) {
            return 0;
        }

        $conteudo = file_get_contents($arquivo);
        // Conta linhas que começam com ## (seção de nível 2)
        preg_match_all('/^## .+/m', $conteudo, $matches);
        return count($matches[0]);
    }

    /**
     * Retorna o status de conclusão de cada exercício do módulo para o aluno.
     *
     * Retorna um mapa indexado por item_id (ex: "ex03" → "concluido").
     * Exercícios não tentados ficam fora do mapa — o frontend trata ausência
     * como null (sem medalha).
     *
     * @return array<string, string>  ['ex01' => 'concluido', 'ex03' => 'concluido_com_ajuda', ...]
     */
    /**
     * Retorna status e código salvo de todos os exercícios com progresso do aluno.
     * Inclui também exercícios "tentados" para que o frontend possa carregar
     * o código que o aluno escreveu ao clicar em "Refazer".
     *
     * @return array<string, array{status: string, codigo_salvo: string|null}>
     */
    private function statusExercicios(int $userId, string $moduloId): array
    {
        $pdo  = \FetecPy\Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT item_id, status, codigo_salvo FROM progress
             WHERE user_id = ? AND modulo = ? AND item_tipo = "exercicio"'
        );
        $stmt->execute([$userId, $moduloId]);

        $resultado = [];
        foreach ($stmt->fetchAll() as $row) {
            $resultado[$row['item_id']] = [
                'status'       => $row['status'],
                'codigo_salvo' => $row['codigo_salvo'],
            ];
        }
        return $resultado;
    }
}
