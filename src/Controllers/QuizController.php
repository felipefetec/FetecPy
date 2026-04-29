<?php
/**
 * QuizController — processa respostas de quiz dos módulos.
 *
 * O quiz de cada módulo fica na pasta content/quiz/<moduloId>.json.
 * O frontend envia a resposta escolhida; o backend verifica, registra
 * progresso e retorna se acertou + XP ganho.
 *
 * Rotas:
 *   GET  /api/modules/:moduloId/quiz         → perguntas sem gabarito
 *   POST /api/modules/:moduloId/quiz/submit  → avalia resposta de uma pergunta
 */
declare(strict_types=1);

namespace FetecPy\Controllers;

use FetecPy\Http\AuthMiddleware;
use FetecPy\Http\JsonResponse;
use FetecPy\Http\Request;
use FetecPy\Services\BadgeChecker;
use FetecPy\Services\ProgressService;

class QuizController
{
    private string $quizDir;
    private ProgressService $progress;
    private BadgeChecker $badges;

    public function __construct()
    {
        // Quizzes ficam em content/quiz/<moduloId>.json
        $this->quizDir  = dirname(__DIR__, 2) . '/content/quiz';
        $this->progress = new ProgressService();
        $this->badges   = new BadgeChecker();
    }

    // ----------------------------------------------------------------
    // GET /api/modules/:moduloId/quiz
    // Retorna as perguntas do quiz SEM os campos "resposta_correta".
    // ----------------------------------------------------------------

    public function show(Request $request): void
    {
        AuthMiddleware::exigir($request);

        $moduloId = $request->params['moduloId'] ?? '';
        $quiz     = $this->carregarQuiz($moduloId);

        if ($quiz === null) {
            JsonResponse::erro("Quiz do módulo '{$moduloId}' não encontrado.", 404);
        }

        // Remove gabarito antes de enviar ao frontend
        $perguntas = array_map(function (array $p): array {
            unset($p['resposta_correta']);
            return $p;
        }, $quiz['perguntas'] ?? []);

        JsonResponse::enviar([
            'modulo'    => $moduloId,
            'titulo'    => $quiz['titulo'] ?? "Quiz — Módulo {$moduloId}",
            'perguntas' => $perguntas,
        ]);
    }

    // ----------------------------------------------------------------
    // POST /api/modules/:moduloId/quiz/submit
    // Body: { "pergunta_id": "q1", "resposta": 2 }
    // Retorna: { correta, xp_ganho, total_xp, streak_dias, novas_badges }
    // ----------------------------------------------------------------

    public function submit(Request $request): void
    {
        AuthMiddleware::exigir($request);

        $moduloId   = $request->params['moduloId'] ?? '';
        $userId     = (int) $request->user['id'];
        $perguntaId = $request->body['pergunta_id'] ?? null;
        $resposta   = $request->body['resposta']    ?? null;
        $horaLocal  = $request->body['hora_local']  ?? null;

        if ($perguntaId === null || $resposta === null) {
            JsonResponse::erro("Campos obrigatórios: pergunta_id, resposta.", 400);
        }

        $quiz = $this->carregarQuiz($moduloId);
        if ($quiz === null) {
            JsonResponse::erro("Quiz do módulo '{$moduloId}' não encontrado.", 404);
        }

        // Busca a pergunta pelo ID
        $pergunta = null;
        foreach ($quiz['perguntas'] ?? [] as $p) {
            if ($p['id'] === $perguntaId) {
                $pergunta = $p;
                break;
            }
        }
        if ($pergunta === null) {
            JsonResponse::erro("Pergunta '{$perguntaId}' não encontrada no quiz.", 404);
        }

        // Compara resposta do aluno com o gabarito (índice 0-based)
        $correta = ((int) $resposta === (int) ($pergunta['resposta_correta'] ?? -1));

        // Registra no banco via ProgressService
        $resultado = $this->progress->registrarQuiz($userId, $moduloId, $perguntaId, $correta);

        // Verifica badges (streak, sniper, horário)
        $novasBadges = $this->badges->verificar(
            $userId,
            $moduloId,
            1, // quizzes sempre têm 1 "tentativa" por pergunta neste modelo
            $correta ? 'concluido' : 'tentado',
            $resultado['streak_dias'],
            $horaLocal
        );

        JsonResponse::enviar([
            'correta'      => $correta,
            'gabarito'     => (int) ($pergunta['resposta_correta'] ?? -1),
            'explicacao'   => $pergunta['explicacao'] ?? null,
            'xp_ganho'     => $resultado['xp_ganho'],
            'total_xp'     => $resultado['total_xp'],
            'streak_dias'  => $resultado['streak_dias'],
            'novas_badges' => $novasBadges,
        ]);
    }

    // ----------------------------------------------------------------
    // Helper
    // ----------------------------------------------------------------

    private function carregarQuiz(string $moduloId): ?array
    {
        // Sanitiza: só dois dígitos numéricos
        if (!preg_match('/^\d{2}$/', $moduloId)) {
            return null;
        }

        $caminho = "{$this->quizDir}/{$moduloId}.json";
        if (!file_exists($caminho)) {
            return null;
        }

        $dados = json_decode(file_get_contents($caminho), true);
        return is_array($dados) ? $dados : null;
    }
}
