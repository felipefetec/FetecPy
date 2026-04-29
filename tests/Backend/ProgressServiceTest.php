<?php
/**
 * Testes do ProgressService.
 *
 * Cobre: cálculo de XP, lógica de streak, multiplicadores por streak
 * e registro de progresso de exercícios e quizzes.
 * Banco SQLite em memória (configurado no bootstrap.php).
 */
declare(strict_types=1);

namespace FetecPy\Tests\Backend;

use FetecPy\Database;
use FetecPy\Services\ProgressService;
use PHPUnit\Framework\TestCase;

class ProgressServiceTest extends TestCase
{
    private ProgressService $service;
    private \PDO $pdo;
    private int $userId;

    protected function setUp(): void
    {
        $this->service = new ProgressService();
        $this->pdo     = Database::getConnection();

        // Cria um usuário de teste
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (nome, sobrenome, chave, pin_hash, xp_total, streak_dias)
             VALUES ('Teste', 'User', 'teste_user_progress', 'hash', 0, 0)"
        );
        $stmt->execute();
        $this->userId = (int) $this->pdo->lastInsertId();
    }

    protected function tearDown(): void
    {
        // Limpa os dados de teste para isolar entre métodos
        $this->pdo->exec("DELETE FROM users WHERE chave = 'teste_user_progress'");
        $this->pdo->exec("DELETE FROM progress WHERE user_id = {$this->userId}");
        $this->pdo->exec("DELETE FROM user_badges WHERE user_id = {$this->userId}");
    }

    // ----------------------------------------------------------------
    // Cálculo de XP por exercício
    // ----------------------------------------------------------------

    public function testXpExercicioFacil(): void
    {
        $xp = $this->service->calcularXpExercicio(['dificuldade' => 'facil'], 'concluido');
        $this->assertSame(10, $xp);
    }

    public function testXpExercicioMedio(): void
    {
        $xp = $this->service->calcularXpExercicio(['dificuldade' => 'medio'], 'concluido');
        $this->assertSame(20, $xp);
    }

    public function testXpExercicioDesafio(): void
    {
        $xp = $this->service->calcularXpExercicio(['dificuldade' => 'desafio'], 'concluido');
        $this->assertSame(30, $xp);
    }

    public function testXpReduzidoComAjuda(): void
    {
        // Conclusão com ajuda concede 50% do XP base
        $xp = $this->service->calcularXpExercicio(['dificuldade' => 'medio'], 'concluido_com_ajuda');
        $this->assertSame(10, $xp); // 20 * 0.5 = 10
    }

    public function testXpZeroQuandoTentado(): void
    {
        $xp = $this->service->calcularXpExercicio(['dificuldade' => 'facil'], 'tentado');
        $this->assertSame(0, $xp);
    }

    public function testXpExplicitoNoJsonSobrescreve(): void
    {
        // Se o JSON do exercício tem campo "xp", ele tem prioridade
        $xp = $this->service->calcularXpExercicio(['dificuldade' => 'facil', 'xp' => 15], 'concluido');
        $this->assertSame(15, $xp);
    }

    // ----------------------------------------------------------------
    // Multiplicador de streak
    // ----------------------------------------------------------------

    public function testMultiplicadorPadrao(): void
    {
        $this->assertSame(1.0, $this->service->calcularMultiplicador(1));
        $this->assertSame(1.0, $this->service->calcularMultiplicador(6));
    }

    public function testMultiplicador7Dias(): void
    {
        $this->assertSame(1.2, $this->service->calcularMultiplicador(7));
        $this->assertSame(1.2, $this->service->calcularMultiplicador(10));
        $this->assertSame(1.2, $this->service->calcularMultiplicador(29));
    }

    public function testMultiplicador30Dias(): void
    {
        $this->assertSame(1.5, $this->service->calcularMultiplicador(30));
        $this->assertSame(1.5, $this->service->calcularMultiplicador(100));
    }

    // ----------------------------------------------------------------
    // Streak (lógica de atualização)
    // ----------------------------------------------------------------

    public function testStreakPrimeiroAcesso(): void
    {
        // Sem acesso anterior → streak vira 1
        [$xp, $streak, $mult] = $this->service->atualizarXpEStreak($this->pdo, $this->userId, 10);
        $this->assertSame(1, $streak);
        $this->assertSame(1.0, $mult);
        $this->assertSame(10, $xp);
    }

    public function testStreakIncrementaQuandoOntem(): void
    {
        // Simula acesso de ontem
        $ontem = date('Y-m-d', strtotime('-1 day'));
        $this->pdo->exec("UPDATE users SET streak_dias = 4, ultimo_acesso = '{$ontem}' WHERE id = {$this->userId}");

        [, $streak] = $this->service->atualizarXpEStreak($this->pdo, $this->userId, 10);
        $this->assertSame(5, $streak);
    }

    public function testStreakResetaQuandoMaisDeUmDia(): void
    {
        // Simula acesso de há 3 dias
        $antigo = date('Y-m-d', strtotime('-3 days'));
        $this->pdo->exec("UPDATE users SET streak_dias = 10, ultimo_acesso = '{$antigo}' WHERE id = {$this->userId}");

        [, $streak] = $this->service->atualizarXpEStreak($this->pdo, $this->userId, 10);
        $this->assertSame(1, $streak);
    }

    public function testStreakNaoMudaNoMesmoDia(): void
    {
        // Simula que já estudou hoje
        $hoje = date('Y-m-d');
        $this->pdo->exec("UPDATE users SET streak_dias = 7, ultimo_acesso = '{$hoje}' WHERE id = {$this->userId}");

        [, $streak] = $this->service->atualizarXpEStreak($this->pdo, $this->userId, 10);
        $this->assertSame(7, $streak); // manteve
    }

    public function testXpComMultiplicador7Dias(): void
    {
        // Streak de 6 → ontem → vira 7, multiplicador 1.2
        $ontem = date('Y-m-d', strtotime('-1 day'));
        $this->pdo->exec("UPDATE users SET streak_dias = 6, ultimo_acesso = '{$ontem}' WHERE id = {$this->userId}");

        [$xp, $streak, $mult] = $this->service->atualizarXpEStreak($this->pdo, $this->userId, 10);
        $this->assertSame(7, $streak);
        $this->assertSame(1.2, $mult);
        $this->assertSame(12, $xp); // 10 * 1.2 = 12
    }

    public function testXpComMultiplicador30Dias(): void
    {
        // Streak de 29 → ontem → vira 30, multiplicador 1.5
        $ontem = date('Y-m-d', strtotime('-1 day'));
        $this->pdo->exec("UPDATE users SET streak_dias = 29, ultimo_acesso = '{$ontem}' WHERE id = {$this->userId}");

        [$xp, $streak, $mult] = $this->service->atualizarXpEStreak($this->pdo, $this->userId, 20);
        $this->assertSame(30, $streak);
        $this->assertSame(1.5, $mult);
        $this->assertSame(30, $xp); // 20 * 1.5 = 30
    }

    // ----------------------------------------------------------------
    // registrarExercicio — integração completa
    // ----------------------------------------------------------------

    public function testRegistrarExercicioSalvaProgresso(): void
    {
        $this->service->registrarExercicio(
            $this->userId, '01', 'ex01', 'concluido', 1, 'print("oi")', ['dificuldade' => 'facil']
        );

        $stmt = $this->pdo->prepare(
            "SELECT status, xp_ganho FROM progress WHERE user_id = ? AND modulo = '01' AND item_id = 'ex01'"
        );
        $stmt->execute([$this->userId]);
        $row = $stmt->fetch();

        $this->assertSame('concluido', $row['status']);
        $this->assertSame(10, (int) $row['xp_ganho']);
    }

    public function testRegistrarExercicioRetornaXpTotal(): void
    {
        $res = $this->service->registrarExercicio(
            $this->userId, '01', 'ex01', 'concluido', 1, null, ['dificuldade' => 'medio']
        );

        $this->assertSame(20, $res['xp_ganho']);
        $this->assertSame(20, $res['total_xp']);
    }

    public function testRegistrarExercicioTentadoNaoDaXp(): void
    {
        $res = $this->service->registrarExercicio(
            $this->userId, '01', 'ex01', 'tentado', 2, 'print("errado")', ['dificuldade' => 'medio']
        );

        $this->assertSame(0, $res['xp_ganho']);
        $this->assertSame(0, $res['total_xp']);
    }

    // ----------------------------------------------------------------
    // registrarQuiz
    // ----------------------------------------------------------------

    public function testRegistrarQuizCorretoDaXp(): void
    {
        $res = $this->service->registrarQuiz($this->userId, '01', 'q1', true);
        $this->assertSame(ProgressService::XP_QUIZ_PERGUNTA, $res['xp_ganho']);
    }

    public function testRegistrarQuizErradoNaoDaXp(): void
    {
        $res = $this->service->registrarQuiz($this->userId, '01', 'q1', false);
        $this->assertSame(0, $res['xp_ganho']);
    }

    // ----------------------------------------------------------------
    // marcarSecaoLida + secoesLidas
    // ----------------------------------------------------------------

    public function testMarcarSecaoLida(): void
    {
        $this->service->marcarSecaoLida($this->userId, '01', '1.1');
        $this->assertSame(1, $this->service->secoesLidas($this->userId, '01'));
    }

    public function testMarcarSecaoLidaIdempotente(): void
    {
        $this->service->marcarSecaoLida($this->userId, '01', '1.1');
        $this->service->marcarSecaoLida($this->userId, '01', '1.1');
        $this->assertSame(1, $this->service->secoesLidas($this->userId, '01'));
    }
}
