<?php
/**
 * Controller de exercícios.
 *
 * Serve os exercícios JSON da pasta exercises/ e processa submissões do
 * aluno. A lógica de XP, streak e badges foi extraída para os serviços
 * ProgressService e BadgeChecker — este controller só orquestra a chamada.
 *
 * Rotas:
 *   GET  /api/modules/:moduloId/exercises/:exId        → exercício sem solução
 *   POST /api/modules/:moduloId/exercises/:exId/submit → processa tentativa
 */
declare(strict_types=1);

namespace FetecPy\Controllers;

use FetecPy\Http\AuthMiddleware;
use FetecPy\Http\JsonResponse;
use FetecPy\Http\Request;
use FetecPy\Services\BadgeChecker;
use FetecPy\Services\ProgressService;

class ExerciseController
{
    private string $exercisesDir;
    private ProgressService $progress;
    private BadgeChecker $badges;

    public function __construct()
    {
        $this->exercisesDir = dirname(__DIR__, 2) . '/exercises';
        $this->progress     = new ProgressService();
        $this->badges       = new BadgeChecker();
    }

    // ----------------------------------------------------------------
    // GET /api/modules/:moduloId/exercises/:exId
    // Retorna o exercício sem o campo "solucao".
    // ----------------------------------------------------------------

    public function show(Request $request): void
    {
        AuthMiddleware::exigir($request);

        $moduloId  = $request->params['moduloId'] ?? '';
        $exId      = $request->params['exId']     ?? '';
        $exercicio = $this->carregarExercicio($moduloId, $exId);

        if ($exercicio === null) {
            JsonResponse::erro("Exercício '{$moduloId}/{$exId}' não encontrado.", 404);
        }

        unset($exercicio['solucao']);
        JsonResponse::enviar($exercicio);
    }

    // ----------------------------------------------------------------
    // POST /api/modules/:moduloId/exercises/:exId/submit
    // Body: { "codigo", "status", "tentativas", "hora_local"?, "badge_especial"? }
    // ----------------------------------------------------------------

    public function submit(Request $request): void
    {
        AuthMiddleware::exigir($request);

        $moduloId  = $request->params['moduloId'] ?? '';
        $exId      = $request->params['exId']     ?? '';
        $userId    = (int) $request->user['id'];

        $codigo         = $request->body['codigo']         ?? null;
        $status         = $request->body['status']         ?? null;
        $tentativas     = (int) ($request->body['tentativas']  ?? 1);
        $horaLocal      = $request->body['hora_local']     ?? null;
        $badgeEspecial  = $request->body['badge_especial'] ?? null;

        $statusValidos = ['concluido', 'concluido_com_ajuda', 'tentado'];
        if (!in_array($status, $statusValidos, true)) {
            JsonResponse::erro("Status inválido. Use: " . implode(', ', $statusValidos), 400);
        }
        if ($tentativas < 1) {
            JsonResponse::erro("O número de tentativas deve ser >= 1.", 400);
        }

        $exercicio = $this->carregarExercicio($moduloId, $exId);
        if ($exercicio === null) {
            JsonResponse::erro("Exercício '{$moduloId}/{$exId}' não encontrado.", 404);
        }

        // Registra progresso e obtém dados de gamificação via ProgressService
        $resultado = $this->progress->registrarExercicio(
            $userId, $moduloId, $exId, $status, $tentativas, $codigo, $exercicio
        );

        // Verifica badges automáticas (streak, contagem, horário, etc.)
        $novasBadges = $this->badges->verificar(
            $userId,
            $moduloId,
            $tentativas,
            $status,
            $resultado['streak_dias'],
            $horaLocal
        );

        // Badge enviada explicitamente pelo frontend (velocista, criativo, pyodide_master)
        if ($badgeEspecial && $this->badges->concederBadgeEspecial($userId, $badgeEspecial)) {
            $novasBadges[] = $badgeEspecial;
        }

        $resposta = [
            'xp_ganho'      => $resultado['xp_ganho'],
            'total_xp'      => $resultado['total_xp'],
            'streak_dias'   => $resultado['streak_dias'],
            'multiplicador' => $resultado['multiplicador'],
            'novas_badges'  => $novasBadges,
        ];

        // Envia solução quando o aluno pediu ajuda ou atingiu o limite de tentativas
        if ($status === 'concluido_com_ajuda' || $tentativas >= 3) {
            $resposta['solucao'] = $exercicio['solucao'] ?? null;
        }

        JsonResponse::enviar($resposta);
    }

    // ----------------------------------------------------------------
    // GET /api/modules/:moduloId/exercises/:exId/solution
    // Retorna a solução apenas se o aluno já concluiu o exercício.
    // ----------------------------------------------------------------

    public function solution(Request $request): void
    {
        AuthMiddleware::exigir($request);

        $moduloId = $request->params['moduloId'] ?? '';
        $exId     = $request->params['exId']     ?? '';
        $userId   = (int) $request->user['id'];

        $exercicio = $this->carregarExercicio($moduloId, $exId);
        if ($exercicio === null) {
            JsonResponse::erro("Exercício '{$moduloId}/{$exId}' não encontrado.", 404);
        }

        // Só entrega a solução se o aluno já concluiu — nunca antes
        $pdo  = \FetecPy\Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT 1 FROM progress
             WHERE user_id = ? AND modulo = ? AND item_tipo = "exercicio" AND item_id = ?
               AND status IN ("concluido", "concluido_com_ajuda")'
        );
        $stmt->execute([$userId, $moduloId, $exId]);

        if (!$stmt->fetch()) {
            JsonResponse::erro('Resolva o exercício primeiro para ver a solução.', 403);
        }

        JsonResponse::enviar(['solucao' => $exercicio['solucao'] ?? '']);
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    private function carregarExercicio(string $moduloId, string $exId): ?array
    {
        // Sanitiza: moduloId = dois dígitos, exId = "ex" + dois dígitos
        if (!preg_match('/^\d{2}$/', $moduloId) || !preg_match('/^ex\d{2}$/', $exId)) {
            return null;
        }

        $caminho = "{$this->exercisesDir}/{$moduloId}/{$exId}.json";
        if (!file_exists($caminho)) {
            return null;
        }

        $dados = json_decode(file_get_contents($caminho), true);
        return is_array($dados) ? $dados : null;
    }
}
