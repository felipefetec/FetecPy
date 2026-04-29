<?php
/**
 * ProgressService — responsável por registrar conclusões de itens do curso,
 * calcular XP, atualizar streak e aplicar multiplicadores de tempo.
 *
 * Centraliza a lógica que antes estava inline no ExerciseController,
 * permitindo que QuizController e futuros controllers também reutilizem
 * as mesmas regras sem duplicação.
 */
declare(strict_types=1);

namespace FetecPy\Services;

use FetecPy\Database;

class ProgressService
{
    // XP base por dificuldade de exercício
    private const XP_EXERCICIO = [
        'facil'   => 10,
        'medio'   => 20,
        'desafio' => 30,
    ];

    // XP fixo para quiz (por resposta correta) e mini-projeto
    public const XP_QUIZ_PERGUNTA = 5;
    public const XP_MINI_PROJETO  = 50;

    // Limiares de streak para multiplicadores
    private const STREAK_MULT_7D  = 7;   // +20%
    private const STREAK_MULT_30D = 30;  // +50%

    // ----------------------------------------------------------------
    // Método principal: registra uma conclusão e devolve o resumo
    // ----------------------------------------------------------------

    /**
     * Registra a conclusão de um exercício e retorna os dados de gamificação.
     *
     * @param int    $userId     ID do aluno
     * @param string $moduloId   Ex: "03"
     * @param string $exId       Ex: "ex02"
     * @param string $status     "concluido" | "concluido_com_ajuda" | "tentado"
     * @param int    $tentativas Número de tentativas do aluno
     * @param string|null $codigo  Código salvo do aluno
     * @param array  $metadados  Metadados do exercício (dificuldade, xp, etc.)
     *
     * @return array { xp_ganho, total_xp, streak_dias, multiplicador }
     */
    public function registrarExercicio(
        int    $userId,
        string $moduloId,
        string $exId,
        string $status,
        int    $tentativas,
        ?string $codigo,
        array  $metadados = []
    ): array {
        $pdo     = Database::getConnection();
        $xpBruto = $this->calcularXpExercicio($metadados, $status);

        // Persiste o progresso (INSERT ou UPDATE)
        $this->salvarProgresso(
            $pdo, $userId, $moduloId, 'exercicio', $exId,
            $status, $tentativas, $codigo, $xpBruto
        );

        // Atualiza XP e streak apenas em conclusões reais
        if (in_array($status, ['concluido', 'concluido_com_ajuda'], true)) {
            [$xpFinal, $streakAtual, $multiplicador] =
                $this->atualizarXpEStreak($pdo, $userId, $xpBruto);
        } else {
            // Lê valores atuais sem alterar
            $xpFinal      = 0;
            $multiplicador = 1.0;
            $streakAtual   = $this->lerStreak($pdo, $userId);
        }

        $totalXp = $this->lerXpTotal($pdo, $userId);

        return [
            'xp_ganho'     => $xpFinal,
            'total_xp'     => $totalXp,
            'streak_dias'  => $streakAtual,
            'multiplicador' => $multiplicador,
        ];
    }

    /**
     * Registra a resposta de uma pergunta de quiz.
     * Devolve XP ganho (apenas para respostas corretas).
     */
    public function registrarQuiz(
        int    $userId,
        string $moduloId,
        string $quizId,
        bool   $correta
    ): array {
        $pdo    = Database::getConnection();
        $status = $correta ? 'concluido' : 'tentado';
        $xpBruto = $correta ? self::XP_QUIZ_PERGUNTA : 0;

        $this->salvarProgresso(
            $pdo, $userId, $moduloId, 'quiz', $quizId,
            $status, 1, null, $xpBruto
        );

        if ($correta) {
            [$xpFinal, $streakAtual, $multiplicador] =
                $this->atualizarXpEStreak($pdo, $userId, $xpBruto);
        } else {
            $xpFinal       = 0;
            $multiplicador = 1.0;
            $streakAtual   = $this->lerStreak($pdo, $userId);
        }

        return [
            'xp_ganho'     => $xpFinal,
            'total_xp'     => $this->lerXpTotal($pdo, $userId),
            'streak_dias'  => $streakAtual,
            'multiplicador' => $multiplicador,
        ];
    }

    /**
     * Marca uma seção de módulo como lida. Não concede XP
     * (seções são de leitura livre, sem gamificação direta).
     */
    public function marcarSecaoLida(
        int    $userId,
        string $moduloId,
        string $secaoId
    ): void {
        $pdo = Database::getConnection();
        $this->salvarProgresso(
            $pdo, $userId, $moduloId, 'secao', $secaoId,
            'concluido', 1, null, 0
        );
    }

    /**
     * Retorna quantas seções de um módulo o aluno já marcou como lidas.
     * Usado pelo frontend para calcular o % de leitura na sidebar.
     */
    public function secoesLidas(int $userId, string $moduloId): int
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM progress
             WHERE user_id = ? AND modulo = ? AND item_tipo = "secao"
               AND status = "concluido"'
        );
        $stmt->execute([$userId, $moduloId]);
        return (int) $stmt->fetchColumn();
    }

    // ----------------------------------------------------------------
    // Helpers internos
    // ----------------------------------------------------------------

    /**
     * Calcula o XP bruto de um exercício baseado na dificuldade e no status.
     * "concluido_com_ajuda" concede 50% do base; "tentado" não concede nada.
     */
    public function calcularXpExercicio(array $metadados, string $status): int
    {
        if ($status === 'tentado') {
            return 0;
        }

        $dificuldade = $metadados['dificuldade'] ?? 'facil';
        // Respeita XP explícito no JSON; cai no default por dificuldade se ausente
        $xpBase = (int) ($metadados['xp'] ?? (self::XP_EXERCICIO[$dificuldade] ?? 10));

        return ($status === 'concluido_com_ajuda')
            ? (int) round($xpBase * 0.5)
            : $xpBase;
    }

    /**
     * Atualiza xp_total e streak_dias do usuário e aplica o multiplicador
     * de streak antes de somar o XP.
     *
     * Regras de streak:
     * - Mesmo dia de hoje → não altera streak (evita contar duas vezes)
     * - Ontem           → streak += 1
     * - Mais antigo     → streak = 1 (quebrou a sequência)
     *
     * Multiplicadores:
     * - streak >= 30d → 1.5x
     * - streak >= 7d  → 1.2x
     * - demais        → 1.0x
     *
     * Retorna: [xp_aplicado, streak_atual, multiplicador]
     */
    public function atualizarXpEStreak(\PDO $pdo, int $userId, int $xpBruto): array
    {
        $stmt = $pdo->prepare(
            'SELECT xp_total, streak_dias, ultimo_acesso FROM users WHERE id = ?'
        );
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        $hoje         = date('Y-m-d');
        $ontem        = date('Y-m-d', strtotime('-1 day'));
        $ultimoAcesso = $user['ultimo_acesso'] ?? null;
        $streakAtual  = (int) ($user['streak_dias'] ?? 0);

        if ($ultimoAcesso === $hoje) {
            // Já estudou hoje — streak não muda
            $novaStreak = $streakAtual;
        } elseif ($ultimoAcesso === $ontem) {
            // Estudou ontem — mantém sequência
            $novaStreak = $streakAtual + 1;
        } else {
            // Quebrou a sequência (ou primeiro acesso)
            $novaStreak = 1;
        }

        // Escolhe o multiplicador baseado na NOVA streak (após atualização)
        $multiplicador = $this->calcularMultiplicador($novaStreak);
        $xpFinal       = (int) round($xpBruto * $multiplicador);

        $update = $pdo->prepare(
            'UPDATE users SET
               xp_total      = xp_total + :xp,
               streak_dias   = :streak,
               ultimo_acesso = :hoje
             WHERE id = :uid'
        );
        $update->execute([
            ':xp'     => $xpFinal,
            ':streak' => $novaStreak,
            ':hoje'   => $hoje,
            ':uid'    => $userId,
        ]);

        return [$xpFinal, $novaStreak, $multiplicador];
    }

    /**
     * Retorna o multiplicador de XP para um dado streak.
     * Público para facilitar testes unitários.
     */
    public function calcularMultiplicador(int $streak): float
    {
        if ($streak >= self::STREAK_MULT_30D) {
            return 1.5;
        }
        if ($streak >= self::STREAK_MULT_7D) {
            return 1.2;
        }
        return 1.0;
    }

    /**
     * Persiste ou atualiza o progresso de um item na tabela progress.
     * Usa ON CONFLICT para ser idempotente — submissões repetidas
     * atualizam o registro existente em vez de criar duplicata.
     */
    private function salvarProgresso(
        \PDO   $pdo,
        int    $userId,
        string $moduloId,
        string $itemTipo,
        string $itemId,
        string $status,
        int    $tentativas,
        ?string $codigo,
        int    $xpGanho
    ): void {
        $stmt = $pdo->prepare(
            'INSERT INTO progress
               (user_id, modulo, item_tipo, item_id, status, tentativas, codigo_salvo, xp_ganho, concluido_em)
             VALUES
               (:uid, :mod, :tipo, :item, :status, :tent, :codigo, :xp, CURRENT_TIMESTAMP)
             ON CONFLICT(user_id, modulo, item_tipo, item_id) DO UPDATE SET
               status       = excluded.status,
               tentativas   = excluded.tentativas,
               codigo_salvo = COALESCE(excluded.codigo_salvo, codigo_salvo),
               xp_ganho     = excluded.xp_ganho,
               concluido_em = CASE
                 WHEN excluded.status IN ("concluido","concluido_com_ajuda")
                 THEN CURRENT_TIMESTAMP
                 ELSE concluido_em
               END'
        );
        $stmt->execute([
            ':uid'    => $userId,
            ':mod'    => $moduloId,
            ':tipo'   => $itemTipo,
            ':item'   => $itemId,
            ':status' => $status,
            ':tent'   => $tentativas,
            ':codigo' => $codigo,
            ':xp'     => $xpGanho,
        ]);
    }

    /** Lê o streak atual sem alterar. */
    private function lerStreak(\PDO $pdo, int $userId): int
    {
        $stmt = $pdo->prepare('SELECT streak_dias FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    /** Lê o XP total atual do usuário. */
    private function lerXpTotal(\PDO $pdo, int $userId): int
    {
        $stmt = $pdo->prepare('SELECT xp_total FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        return (int) ($stmt->fetchColumn() ?: 0);
    }
}
