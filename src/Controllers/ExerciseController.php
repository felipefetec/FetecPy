<?php
/**
 * Controller de exercícios.
 *
 * Serve os exercícios JSON da pasta exercises/ e processa submissões
 * do aluno: valida entrada, persiste progresso, calcula XP, atualiza
 * streak e verifica se novas badges foram conquistadas.
 *
 * Rotas:
 *   GET  /api/modules/:moduloId/exercises/:exId        → retorna exercício (sem solução)
 *   POST /api/modules/:moduloId/exercises/:exId/submit → processa tentativa do aluno
 */
declare(strict_types=1);

namespace FetecPy\Controllers;

use FetecPy\Database;
use FetecPy\Http\AuthMiddleware;
use FetecPy\Http\JsonResponse;
use FetecPy\Http\Request;

class ExerciseController
{
    // Caminho base dos exercícios JSON
    private string $exercisesDir;

    // Mapeamento de dificuldade para XP base
    private const XP_BASE = [
        'facil'   => 10,
        'medio'   => 20,
        'desafio' => 30,
    ];

    // IDs de todas as badges que verificamos automaticamente
    // (badges de comportamento como "velocista" são verificadas no frontend)
    private const BADGES_AUTO = [
        'primeiro_codigo',
        'estudante_dedicado',
        'cacador_bugs',
        'mestre_modulo',
        'formado',
    ];

    public function __construct()
    {
        $this->exercisesDir = dirname(__DIR__, 2) . '/exercises';
    }

    // ----------------------------------------------------------------
    // GET /api/modules/:moduloId/exercises/:exId
    // Retorna o exercício sem o campo "solucao".
    // A solução só é revelada via POST /submit após o limite de tentativas.
    // ----------------------------------------------------------------

    public function show(Request $request): void
    {
        AuthMiddleware::exigir($request);

        $moduloId = $request->params['moduloId'] ?? '';
        $exId     = $request->params['exId'] ?? '';

        $exercicio = $this->carregarExercicio($moduloId, $exId);
        if ($exercicio === null) {
            JsonResponse::erro("Exercício '{$moduloId}/{$exId}' não encontrado.", 404);
        }

        // Remove solução antes de enviar ao frontend
        unset($exercicio['solucao']);

        JsonResponse::enviar($exercicio);
    }

    // ----------------------------------------------------------------
    // POST /api/modules/:moduloId/exercises/:exId/submit
    // Body: { "codigo": "...", "status": "concluido|concluido_com_ajuda|tentado", "tentativas": 3 }
    //
    // O frontend envia o status após validar localmente com Pyodide.
    // O backend confia no status para calcular XP — validação real do
    // código Python fica no frontend (Pyodide), pois não temos Python no servidor.
    // Retorna: { xp_ganho, total_xp, streak_dias, novas_badges, solucao? }
    // ----------------------------------------------------------------

    public function submit(Request $request): void
    {
        AuthMiddleware::exigir($request);

        $moduloId  = $request->params['moduloId'] ?? '';
        $exId      = $request->params['exId'] ?? '';
        $userId    = (int) $request->user['id'];

        // Valida campos da submissão
        $codigo     = $request->body['codigo']     ?? null;
        $status     = $request->body['status']     ?? null;
        $tentativas = (int) ($request->body['tentativas'] ?? 1);

        $statusValidos = ['concluido', 'concluido_com_ajuda', 'tentado'];
        if (!in_array($status, $statusValidos, true)) {
            JsonResponse::erro("Status inválido. Use: " . implode(', ', $statusValidos), 400);
        }
        if ($tentativas < 1) {
            JsonResponse::erro("O número de tentativas deve ser >= 1.", 400);
        }

        // Carrega o exercício para ter os metadados (dificuldade, XP base, solução)
        $exercicio = $this->carregarExercicio($moduloId, $exId);
        if ($exercicio === null) {
            JsonResponse::erro("Exercício '{$moduloId}/{$exId}' não encontrado.", 404);
        }

        $pdo = Database::getConnection();

        // Calcula XP a conceder nesta submissão
        $xpGanho = $this->calcularXp($exercicio, $status);

        // Persiste ou atualiza o progresso deste exercício
        // Usa INSERT OR REPLACE para idempotência — o aluno pode submeter várias vezes
        $stmt = $pdo->prepare(
            'INSERT INTO progress (user_id, modulo, item_tipo, item_id, status, tentativas, codigo_salvo, xp_ganho, concluido_em)
             VALUES (:uid, :mod, "exercicio", :item, :status, :tent, :codigo, :xp, CURRENT_TIMESTAMP)
             ON CONFLICT(user_id, modulo, item_tipo, item_id) DO UPDATE SET
               status       = excluded.status,
               tentativas   = excluded.tentativas,
               codigo_salvo = excluded.codigo_salvo,
               xp_ganho     = excluded.xp_ganho,
               concluido_em = CASE WHEN excluded.status IN ("concluido","concluido_com_ajuda") THEN CURRENT_TIMESTAMP ELSE concluido_em END'
        );
        $stmt->execute([
            ':uid'    => $userId,
            ':mod'    => $moduloId,
            ':item'   => $exId,
            ':status' => $status,
            ':tent'   => $tentativas,
            ':codigo' => $codigo,
            ':xp'     => $xpGanho,
        ]);

        // Atualiza XP total e streak do usuário (apenas para conclusões reais)
        $streakAtual = 0;
        if (in_array($status, ['concluido', 'concluido_com_ajuda'], true)) {
            $streakAtual = $this->atualizarXpEStreak($pdo, $userId, $xpGanho);
        } else {
            // Só lê o streak atual sem alterar
            $row = $pdo->prepare('SELECT streak_dias FROM users WHERE id = ?');
            $row->execute([$userId]);
            $streakAtual = (int) ($row->fetchColumn() ?: 0);
        }

        // Busca XP total atualizado
        $stmtXp = $pdo->prepare('SELECT xp_total FROM users WHERE id = ?');
        $stmtXp->execute([$userId]);
        $totalXp = (int) ($stmtXp->fetchColumn() ?: 0);

        // Verifica novas badges conquistadas após esta submissão
        $novasBadges = $this->verificarBadges($pdo, $userId, $moduloId, $tentativas, $status);

        // Monta a resposta — inclui solução se o aluno está pedindo ou atingiu o limite
        $resposta = [
            'xp_ganho'     => $xpGanho,
            'total_xp'     => $totalXp,
            'streak_dias'  => $streakAtual,
            'novas_badges' => $novasBadges,
        ];

        // Envia a solução quando o aluno escolheu "ver solução" (concluido_com_ajuda)
        // ou quando tiver 3+ tentativas (frontend já desbloqueou o botão, mas backend
        // envia de qualquer forma para casos edge como refresh de página)
        if ($status === 'concluido_com_ajuda' || $tentativas >= 3) {
            $resposta['solucao'] = $exercicio['solucao'] ?? null;
        }

        JsonResponse::enviar($resposta);
    }

    // ----------------------------------------------------------------
    // Helpers privados
    // ----------------------------------------------------------------

    /**
     * Lê e decodifica o JSON de um exercício.
     * Retorna null se o arquivo não existir ou o JSON for inválido.
     */
    private function carregarExercicio(string $moduloId, string $exId): ?array
    {
        // Sanitiza para evitar path traversal — só letras, números e hífen permitidos
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

    /**
     * Calcula o XP a conceder baseado na dificuldade do exercício e no status.
     * Conclusão com ajuda concede 50% do XP base.
     * Status "tentado" (aluno desistiu sem ver solução) não concede XP.
     */
    private function calcularXp(array $exercicio, string $status): int
    {
        if ($status === 'tentado') {
            return 0;
        }

        $dificuldade = $exercicio['dificuldade'] ?? 'facil';
        $xpBase      = self::XP_BASE[$dificuldade] ?? 10;

        // Exercícios sem código (texto_livre) também ganham XP integral ao concluir
        if ($status === 'concluido_com_ajuda') {
            return (int) round($xpBase * 0.5);
        }

        return $xpBase;
    }

    /**
     * Atualiza XP total e streak do aluno após uma conclusão.
     *
     * Lógica de streak:
     * - Mesmo dia de hoje: streak não muda (evita contar duplo no mesmo dia)
     * - Ontem: streak incrementa
     * - Mais de 1 dia atrás: streak reseta para 1
     *
     * Retorna o valor atual do streak após a atualização.
     */
    private function atualizarXpEStreak(\PDO $pdo, int $userId, int $xpGanho): int
    {
        $stmt = $pdo->prepare(
            'SELECT xp_total, streak_dias, ultimo_acesso FROM users WHERE id = ?'
        );
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        $hoje         = date('Y-m-d');
        $ultimoAcesso = $user['ultimo_acesso'] ?? null;
        $streakAtual  = (int) $user['streak_dias'];

        // Calcula nova streak com base na distância entre hoje e o último acesso
        if ($ultimoAcesso === $hoje) {
            // Já estudou hoje — só soma XP, não altera streak
            $novaStreak = $streakAtual;
        } elseif ($ultimoAcesso === date('Y-m-d', strtotime('-1 day'))) {
            // Estudou ontem — mantém a sequência
            $novaStreak = $streakAtual + 1;
        } else {
            // Mais de 1 dia sem estudar (ou primeiro acesso) — reinicia
            $novaStreak = 1;
        }

        // Multiplicador de XP: streak de 7+ dias concede +20%
        $multiplicador = ($novaStreak >= 7) ? 1.2 : 1.0;
        $xpFinal       = (int) round($xpGanho * $multiplicador);

        $update = $pdo->prepare(
            'UPDATE users SET
               xp_total     = xp_total + :xp,
               streak_dias  = :streak,
               ultimo_acesso = :hoje
             WHERE id = :uid'
        );
        $update->execute([
            ':xp'     => $xpFinal,
            ':streak' => $novaStreak,
            ':hoje'   => $hoje,
            ':uid'    => $userId,
        ]);

        return $novaStreak;
    }

    /**
     * Verifica e concede badges automáticas após uma submissão.
     *
     * Badges verificadas:
     * - primeiro_codigo:    primeiro exercício concluído
     * - estudante_dedicado: 10 ou mais exercícios concluídos
     * - cacador_bugs:       concluiu após 3+ tentativas
     * - mestre_modulo:      todos os exercícios de um módulo concluídos
     * - formado:            exercícios de todos os módulos concluídos
     *
     * Retorna array com as badges recém-conquistadas (para exibir toast no frontend).
     */
    private function verificarBadges(\PDO $pdo, int $userId, string $moduloId, int $tentativas, string $status): array
    {
        // Só verifica badges em conclusões reais
        if (!in_array($status, ['concluido', 'concluido_com_ajuda'], true)) {
            return [];
        }

        // Busca badges já conquistadas para não conceder duplicata
        $stmtJa = $pdo->prepare('SELECT badge_id FROM user_badges WHERE user_id = ?');
        $stmtJa->execute([$userId]);
        $jaConquistadas = $stmtJa->fetchAll(\PDO::FETCH_COLUMN);
        $jaSet          = array_flip($jaConquistadas); // flip para busca O(1)

        // Conta total de exercícios concluídos pelo aluno
        $stmtTotal = $pdo->prepare(
            'SELECT COUNT(*) FROM progress
             WHERE user_id = ? AND item_tipo = "exercicio"
               AND status IN ("concluido","concluido_com_ajuda")'
        );
        $stmtTotal->execute([$userId]);
        $totalConcluidos = (int) $stmtTotal->fetchColumn();

        // Lista de exercícios existentes por módulo (lidos da pasta exercises/)
        $exerciciosPorModulo = $this->contarExerciciosPorModulo();

        $novas = [];

        // primeiro_codigo — primeiro exercício concluído
        if (!isset($jaSet['primeiro_codigo']) && $totalConcluidos >= 1) {
            $this->concederBadge($pdo, $userId, 'primeiro_codigo');
            $novas[] = 'primeiro_codigo';
        }

        // estudante_dedicado — 10 exercícios concluídos
        if (!isset($jaSet['estudante_dedicado']) && $totalConcluidos >= 10) {
            $this->concederBadge($pdo, $userId, 'estudante_dedicado');
            $novas[] = 'estudante_dedicado';
        }

        // cacador_bugs — concluiu após 3+ tentativas
        if (!isset($jaSet['cacador_bugs']) && $tentativas >= 3) {
            $this->concederBadge($pdo, $userId, 'cacador_bugs');
            $novas[] = 'cacador_bugs';
        }

        // mestre_modulo — todos os exercícios do módulo atual concluídos
        if (!isset($jaSet['mestre_modulo'])) {
            $totalModulo = $exerciciosPorModulo[$moduloId] ?? 0;
            if ($totalModulo > 0 && $this->moduloConcluido($pdo, $userId, $moduloId, $totalModulo)) {
                $this->concederBadge($pdo, $userId, 'mestre_modulo');
                $novas[] = 'mestre_modulo';
            }
        }

        // formado — todos os exercícios de todos os módulos concluídos
        if (!isset($jaSet['formado']) && count($exerciciosPorModulo) > 0) {
            $tudoConcluido = true;
            foreach ($exerciciosPorModulo as $mod => $total) {
                if (!$this->moduloConcluido($pdo, $userId, $mod, $total)) {
                    $tudoConcluido = false;
                    break;
                }
            }
            if ($tudoConcluido) {
                $this->concederBadge($pdo, $userId, 'formado');
                $novas[] = 'formado';
            }
        }

        return $novas;
    }

    /**
     * Insere uma badge na tabela user_badges.
     * Usa INSERT OR IGNORE para ser idempotente (evita crash em condição de corrida).
     */
    private function concederBadge(\PDO $pdo, int $userId, string $badgeId): void
    {
        $stmt = $pdo->prepare(
            'INSERT OR IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)'
        );
        $stmt->execute([$userId, $badgeId]);
    }

    /**
     * Verifica se todos os exercícios de um módulo estão concluídos pelo aluno.
     *
     * @param int    $total  Número total de exercícios esperados no módulo
     */
    private function moduloConcluido(\PDO $pdo, int $userId, string $moduloId, int $total): bool
    {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM progress
             WHERE user_id = ? AND modulo = ? AND item_tipo = "exercicio"
               AND status IN ("concluido","concluido_com_ajuda")'
        );
        $stmt->execute([$userId, $moduloId]);
        return (int) $stmt->fetchColumn() >= $total;
    }

    /**
     * Conta quantos exercícios JSON existem em cada subpasta de exercises/.
     * Usado para determinar se um módulo está completo.
     *
     * Retorna ['01' => 5, '02' => 5, ...]
     */
    private function contarExerciciosPorModulo(): array
    {
        $resultado = [];
        $pastas    = glob($this->exercisesDir . '/[0-9][0-9]', GLOB_ONLYDIR);
        if (empty($pastas)) {
            return $resultado;
        }

        foreach ($pastas as $pasta) {
            $modId    = basename($pasta);
            $arquivos = glob($pasta . '/ex*.json');
            if (!empty($arquivos)) {
                $resultado[$modId] = count($arquivos);
            }
        }

        return $resultado;
    }
}
