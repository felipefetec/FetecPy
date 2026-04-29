<?php
/**
 * BadgeChecker — verifica e concede as 15 badges do sistema.
 *
 * Chamado após cada submissão de exercício ou quiz. Recebe o contexto
 * da interação atual (módulo, tentativas, hora, streak) e consulta
 * o banco para decidir quais badges ainda não foram concedidas e
 * agora se tornaram elegíveis.
 *
 * Cada badge tem seu próprio método privado is<Badge>() para facilitar
 * testes unitários isolados.
 */
declare(strict_types=1);

namespace FetecPy\Services;

use FetecPy\Database;

class BadgeChecker
{
    // ----------------------------------------------------------------
    // Ponto de entrada principal
    // ----------------------------------------------------------------

    /**
     * Verifica todas as badges automáticas e concede as que o aluno
     * ainda não tem mas agora merece.
     *
     * @param int    $userId     ID do aluno
     * @param string $moduloId   Módulo da submissão atual (ex: "03")
     * @param int    $tentativas Tentativas nesta submissão
     * @param string $status     Status da submissão ("concluido" | "concluido_com_ajuda" | "tentado")
     * @param int    $streakAtual Streak atual após atualização
     * @param string|null $horaLocal Hora local do aluno (HH:MM) — enviada pelo frontend
     *
     * @return array Lista de IDs de badges recém-conquistadas (ex: ["streak_7", "cacador_bugs"])
     */
    public function verificar(
        int    $userId,
        string $moduloId,
        int    $tentativas,
        string $status,
        int    $streakAtual,
        ?string $horaLocal = null
    ): array {
        // Badges de comportamento (coruja/madrugador) só fazem sentido em conclusões
        $eConclusao = in_array($status, ['concluido', 'concluido_com_ajuda'], true);

        $pdo = Database::getConnection();

        // Carrega o conjunto de badges já conquistadas para checagem O(1)
        $jaConquistadas = $this->buscarBadgesConquistadas($pdo, $userId);

        // Contagens reutilizadas por múltiplas badges
        $totalExercicios = $this->contarExerciciosConcluidos($pdo, $userId);
        $exerciciosPorModulo = $this->contarExerciciosPorModulo();

        $novas = [];

        // ---- primeiro_codigo: primeiro exercício com print -------------------
        if (!isset($jaConquistadas['primeiro_codigo']) && $eConclusao && $totalExercicios >= 1) {
            $this->conceder($pdo, $userId, 'primeiro_codigo');
            $novas[] = 'primeiro_codigo';
        }

        // ---- streak_3, streak_7, streak_30 ----------------------------------
        foreach ([3 => 'streak_3', 7 => 'streak_7', 30 => 'streak_30'] as $dias => $badgeId) {
            if (!isset($jaConquistadas[$badgeId]) && $streakAtual >= $dias) {
                $this->conceder($pdo, $userId, $badgeId);
                $novas[] = $badgeId;
            }
        }

        // ---- estudante_dedicado: 10 exercícios concluídos -------------------
        if (!isset($jaConquistadas['estudante_dedicado']) && $totalExercicios >= 10) {
            $this->conceder($pdo, $userId, 'estudante_dedicado');
            $novas[] = 'estudante_dedicado';
        }

        // ---- cacador_bugs: resolveu após 3+ tentativas ----------------------
        if (!isset($jaConquistadas['cacador_bugs']) && $eConclusao && $tentativas >= 3) {
            $this->conceder($pdo, $userId, 'cacador_bugs');
            $novas[] = 'cacador_bugs';
        }

        // ---- mestre_modulo: todos os exercícios do módulo atual concluídos --
        if (!isset($jaConquistadas['mestre_modulo'])) {
            $totalMod = $exerciciosPorModulo[$moduloId] ?? 0;
            if ($totalMod > 0 && $this->moduloConcluido($pdo, $userId, $moduloId, $totalMod)) {
                $this->conceder($pdo, $userId, 'mestre_modulo');
                $novas[] = 'mestre_modulo';
            }
        }

        // ---- formado: todos os módulos com exercícios concluídos -----------
        if (!isset($jaConquistadas['formado']) && count($exerciciosPorModulo) > 0) {
            $tudoConcluido = true;
            foreach ($exerciciosPorModulo as $mod => $total) {
                if (!$this->moduloConcluido($pdo, $userId, $mod, $total)) {
                    $tudoConcluido = false;
                    break;
                }
            }
            if ($tudoConcluido) {
                $this->conceder($pdo, $userId, 'formado');
                $novas[] = 'formado';
            }
        }

        // ---- sniper: 5 respostas de quiz corretas consecutivas --------------
        if (!isset($jaConquistadas['sniper']) && $eConclusao) {
            if ($this->isSniper($pdo, $userId)) {
                $this->conceder($pdo, $userId, 'sniper');
                $novas[] = 'sniper';
            }
        }

        // ---- coruja: estudou depois das 23h ---------------------------------
        if (!isset($jaConquistadas['coruja']) && $eConclusao && $horaLocal !== null) {
            if ($this->isCoruja($horaLocal)) {
                $this->conceder($pdo, $userId, 'coruja');
                $novas[] = 'coruja';
            }
        }

        // ---- madrugador: estudou antes das 7h -------------------------------
        if (!isset($jaConquistadas['madrugador']) && $eConclusao && $horaLocal !== null) {
            if ($this->isMadrugador($horaLocal)) {
                $this->conceder($pdo, $userId, 'madrugador');
                $novas[] = 'madrugador';
            }
        }

        // ---- persistente: voltou após 7+ dias sem registrar progresso -------
        if (!isset($jaConquistadas['persistente']) && $eConclusao) {
            if ($this->isPersistente($pdo, $userId)) {
                $this->conceder($pdo, $userId, 'persistente');
                $novas[] = 'persistente';
            }
        }

        // ---- velocista e criativo: enviados pelo frontend via campo especial --
        // Não são verificados aqui — o frontend envia explicitamente (ver ExerciseController).

        return $novas;
    }

    /**
     * Concede badge de comportamento especial enviada pelo frontend.
     * Usado para "velocista" (< 1min) e "criativo" (AST alternativo).
     * O frontend é responsável por verificar a condição; o backend apenas registra.
     */
    public function concederBadgeEspecial(int $userId, string $badgeId): bool
    {
        $badgesEspeciais = ['velocista', 'criativo', 'pyodide_master'];
        if (!in_array($badgeId, $badgesEspeciais, true)) {
            return false; // Rejeita badges que não são "especiais"
        }

        $pdo = Database::getConnection();
        $ja  = $this->buscarBadgesConquistadas($pdo, $userId);
        if (isset($ja[$badgeId])) {
            return false; // Já tem
        }

        $this->conceder($pdo, $userId, $badgeId);
        return true;
    }

    // ----------------------------------------------------------------
    // Verificações individuais — públicas para facilitar testes
    // ----------------------------------------------------------------

    /** Coruja: hora local entre 23:00 e 04:59 */
    public function isCoruja(string $horaLocal): bool
    {
        // Aceita "HH:MM" ou "HH:MM:SS"
        $hora = (int) substr($horaLocal, 0, 2);
        return $hora >= 23 || $hora < 5;
    }

    /** Madrugador: hora local entre 05:00 e 06:59 */
    public function isMadrugador(string $horaLocal): bool
    {
        $hora = (int) substr($horaLocal, 0, 2);
        return $hora >= 5 && $hora < 7;
    }

    /**
     * Sniper: últimas 5 submissões de quiz foram todas corretas.
     * Usa status = "concluido" em item_tipo = "quiz".
     */
    public function isSniper(\PDO $pdo, int $userId): bool
    {
        $stmt = $pdo->prepare(
            'SELECT status FROM progress
             WHERE user_id = ? AND item_tipo = "quiz"
             ORDER BY concluido_em DESC, id DESC
             LIMIT 5'
        );
        $stmt->execute([$userId]);
        $ultimas = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        // Precisa de exatamente 5 respostas, todas corretas
        if (count($ultimas) < 5) {
            return false;
        }
        foreach ($ultimas as $s) {
            if ($s !== 'concluido') {
                return false;
            }
        }
        return true;
    }

    /**
     * Persistente: o aluno voltou a estudar após 7+ dias sem progresso.
     * Compara a data de hoje com a última conclusão registrada antes da atual.
     */
    public function isPersistente(\PDO $pdo, int $userId): bool
    {
        // Busca o penúltimo acesso (ignora o mais recente, que é o atual)
        $stmt = $pdo->prepare(
            'SELECT ultimo_acesso FROM users WHERE id = ?'
        );
        $stmt->execute([$userId]);
        $ultimoAcesso = $stmt->fetchColumn();

        if (!$ultimoAcesso) {
            return false; // Primeiro acesso
        }

        // Calcula diferença em dias entre último acesso anterior e hoje
        $hoje     = new \DateTimeImmutable('today');
        $anterior = new \DateTimeImmutable($ultimoAcesso);
        $diff     = $hoje->diff($anterior)->days;

        // "Voltou" = diferença >= 7 dias (antes da atualização de hoje)
        return $diff >= 7;
    }

    // ----------------------------------------------------------------
    // Helpers internos
    // ----------------------------------------------------------------

    /** Busca as badges já conquistadas em um set (badge_id → true). */
    private function buscarBadgesConquistadas(\PDO $pdo, int $userId): array
    {
        $stmt = $pdo->prepare('SELECT badge_id FROM user_badges WHERE user_id = ?');
        $stmt->execute([$userId]);
        return array_flip($stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    /** Total de exercícios concluídos (qualquer status de conclusão). */
    private function contarExerciciosConcluidos(\PDO $pdo, int $userId): int
    {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM progress
             WHERE user_id = ? AND item_tipo = "exercicio"
               AND status IN ("concluido","concluido_com_ajuda")'
        );
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Conta quantos exercícios JSON existem em cada subpasta de exercises/.
     * Retorna ['01' => 5, '02' => 5, ...].
     */
    private function contarExerciciosPorModulo(): array
    {
        $exercisesDir = dirname(__DIR__, 2) . '/exercises';
        $pastas       = glob($exercisesDir . '/[0-9][0-9]', GLOB_ONLYDIR) ?: [];
        $resultado    = [];
        foreach ($pastas as $pasta) {
            $arquivos = glob($pasta . '/ex*.json') ?: [];
            if (!empty($arquivos)) {
                $resultado[basename($pasta)] = count($arquivos);
            }
        }
        return $resultado;
    }

    /** Verifica se todos os exercícios de um módulo estão concluídos. */
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

    /** Insere badge com INSERT OR IGNORE para ser idempotente. */
    private function conceder(\PDO $pdo, int $userId, string $badgeId): void
    {
        $stmt = $pdo->prepare(
            'INSERT OR IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)'
        );
        $stmt->execute([$userId, $badgeId]);
    }
}
