<?php
/**
 * Testes do BadgeChecker.
 *
 * Cobre cada badge verificável automaticamente.
 * Badges manuais (velocista, criativo, pyodide_master) são testadas
 * via concederBadgeEspecial().
 */
declare(strict_types=1);

namespace FetecPy\Tests\Backend;

use FetecPy\Database;
use FetecPy\Services\BadgeChecker;
use PHPUnit\Framework\TestCase;

class BadgeCheckerTest extends TestCase
{
    private BadgeChecker $checker;
    private \PDO $pdo;
    private int $userId;

    protected function setUp(): void
    {
        $this->checker = new BadgeChecker();
        $this->pdo     = Database::getConnection();

        $stmt = $this->pdo->prepare(
            "INSERT INTO users (nome, sobrenome, chave, pin_hash, xp_total, streak_dias)
             VALUES ('Badge', 'Teste', 'badge_teste_user', 'hash', 0, 0)"
        );
        $stmt->execute();
        $this->userId = (int) $this->pdo->lastInsertId();
    }

    protected function tearDown(): void
    {
        $this->pdo->exec("DELETE FROM progress   WHERE user_id = {$this->userId}");
        $this->pdo->exec("DELETE FROM user_badges WHERE user_id = {$this->userId}");
        $this->pdo->exec("DELETE FROM users       WHERE id = {$this->userId}");
    }

    // ----------------------------------------------------------------
    // Helper: insere um exercício concluído
    // ----------------------------------------------------------------

    private function concluirExercicio(string $modulo, string $exId, int $tentativas = 1): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT OR IGNORE INTO progress
               (user_id, modulo, item_tipo, item_id, status, tentativas, xp_ganho, concluido_em)
             VALUES (?, ?, 'exercicio', ?, 'concluido', ?, 10, CURRENT_TIMESTAMP)"
        );
        $stmt->execute([$this->userId, $modulo, $exId, $tentativas]);
    }

    private function concluirQuiz(string $modulo, string $qId, bool $correta = true): void
    {
        $status = $correta ? 'concluido' : 'tentado';
        $stmt   = $this->pdo->prepare(
            "INSERT OR REPLACE INTO progress
               (user_id, modulo, item_tipo, item_id, status, tentativas, xp_ganho, concluido_em)
             VALUES (?, ?, 'quiz', ?, ?, 1, 5, CURRENT_TIMESTAMP)"
        );
        $stmt->execute([$this->userId, $modulo, $qId, $status]);
    }

    private function verificar(int $tentativas = 1, string $status = 'concluido', int $streak = 0, ?string $hora = null): array
    {
        return $this->checker->verificar($this->userId, '01', $tentativas, $status, $streak, $hora);
    }

    // ----------------------------------------------------------------
    // primeiro_codigo
    // ----------------------------------------------------------------

    public function testPrimeiroCodigoAoConcluirPrimeiroExercicio(): void
    {
        $this->concluirExercicio('01', 'ex01');
        $novas = $this->verificar(1, 'concluido', 1);
        $this->assertContains('primeiro_codigo', $novas);
    }

    public function testPrimeiroCodigoNaoConcedeEmTentado(): void
    {
        $novas = $this->verificar(1, 'tentado', 0);
        $this->assertNotContains('primeiro_codigo', $novas);
    }

    public function testPrimeiroCodigoNaoDuplicado(): void
    {
        // Concede na primeira chamada; não repete na segunda
        $this->concluirExercicio('01', 'ex01');
        $this->verificar(1, 'concluido', 1);
        $this->concluirExercicio('01', 'ex02');
        $novas = $this->verificar(1, 'concluido', 1);
        $this->assertNotContains('primeiro_codigo', $novas);
    }

    // ----------------------------------------------------------------
    // streak_3, streak_7, streak_30
    // ----------------------------------------------------------------

    public function testStreak3Desbloqueia(): void
    {
        $this->concluirExercicio('01', 'ex01');
        $novas = $this->verificar(1, 'concluido', 3);
        $this->assertContains('streak_3', $novas);
    }

    public function testStreak7Desbloqueia(): void
    {
        $this->concluirExercicio('01', 'ex01');
        $novas = $this->verificar(1, 'concluido', 7);
        $this->assertContains('streak_7', $novas);
    }

    public function testStreak30Desbloqueia(): void
    {
        $this->concluirExercicio('01', 'ex01');
        $novas = $this->verificar(1, 'concluido', 30);
        $this->assertContains('streak_30', $novas);
    }

    public function testStreak7NaoConcedeStreak3NovamenteSeTiverMenos(): void
    {
        // Streak 7 deve incluir streak_7 mas não streak_3 se já foi concedido
        $this->concluirExercicio('01', 'ex01');
        $this->pdo->exec("INSERT OR IGNORE INTO user_badges (user_id, badge_id) VALUES ({$this->userId}, 'streak_3')");
        $novas = $this->verificar(1, 'concluido', 7);
        $this->assertContains('streak_7', $novas);
        $this->assertNotContains('streak_3', $novas); // já tinha
    }

    // ----------------------------------------------------------------
    // estudante_dedicado
    // ----------------------------------------------------------------

    public function testEstudanteDedicado10Exercicios(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->concluirExercicio('01', sprintf('ex%02d', $i));
        }
        $novas = $this->verificar(1, 'concluido', 0);
        $this->assertContains('estudante_dedicado', $novas);
    }

    public function testEstudanteDedicadoNaoComMenosDe10(): void
    {
        for ($i = 1; $i <= 9; $i++) {
            $this->concluirExercicio('01', sprintf('ex%02d', $i));
        }
        $novas = $this->verificar(1, 'concluido', 0);
        $this->assertNotContains('estudante_dedicado', $novas);
    }

    // ----------------------------------------------------------------
    // cacador_bugs
    // ----------------------------------------------------------------

    public function testCacadorBugsCom3Tentativas(): void
    {
        $this->concluirExercicio('01', 'ex01', 3);
        $novas = $this->verificar(3, 'concluido', 0);
        $this->assertContains('cacador_bugs', $novas);
    }

    public function testCacadorBugsNaoComMenosDe3(): void
    {
        $this->concluirExercicio('01', 'ex01', 2);
        $novas = $this->verificar(2, 'concluido', 0);
        $this->assertNotContains('cacador_bugs', $novas);
    }

    // ----------------------------------------------------------------
    // sniper (5 quizzes corretos consecutivos)
    // ----------------------------------------------------------------

    public function testSniperComCincoQuizzesCorretos(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->concluirQuiz('01', "q{$i}", true);
        }
        $novas = $this->verificar(1, 'concluido', 0);
        $this->assertContains('sniper', $novas);
    }

    public function testSniperNaoComErroIntercalado(): void
    {
        $this->concluirQuiz('01', 'q1', true);
        $this->concluirQuiz('01', 'q2', false); // errou
        $this->concluirQuiz('01', 'q3', true);
        $this->concluirQuiz('01', 'q4', true);
        $this->concluirQuiz('01', 'q5', true);
        $novas = $this->verificar(1, 'concluido', 0);
        $this->assertNotContains('sniper', $novas);
    }

    // ----------------------------------------------------------------
    // coruja e madrugador (verificações de horário)
    // ----------------------------------------------------------------

    public function testCorujaApos23h(): void
    {
        $this->assertTrue($this->checker->isCoruja('23:30'));
        $this->assertTrue($this->checker->isCoruja('00:15'));
        $this->assertTrue($this->checker->isCoruja('04:59'));
    }

    public function testCorujaNaoEntreHorario(): void
    {
        $this->assertFalse($this->checker->isCoruja('22:59'));
        $this->assertFalse($this->checker->isCoruja('07:00'));
        $this->assertFalse($this->checker->isCoruja('12:00'));
    }

    public function testMadrugadorAntes7h(): void
    {
        $this->assertTrue($this->checker->isMadrugador('05:00'));
        $this->assertTrue($this->checker->isMadrugador('06:59'));
    }

    public function testMadrugadorNaoForaDoHorario(): void
    {
        $this->assertFalse($this->checker->isMadrugador('07:00'));
        $this->assertFalse($this->checker->isMadrugador('04:59'));
        $this->assertFalse($this->checker->isMadrugador('12:00'));
    }

    public function testCorujaDisparaViaBadgeChecker(): void
    {
        $this->concluirExercicio('01', 'ex01');
        $novas = $this->verificar(1, 'concluido', 0, '23:45');
        $this->assertContains('coruja', $novas);
    }

    public function testMadrugadorDisparaViaBadgeChecker(): void
    {
        $this->concluirExercicio('01', 'ex01');
        $novas = $this->verificar(1, 'concluido', 0, '06:00');
        $this->assertContains('madrugador', $novas);
    }

    // ----------------------------------------------------------------
    // persistente (voltou após 7+ dias)
    // ----------------------------------------------------------------

    public function testPersistenteApos7Dias(): void
    {
        $antigo = date('Y-m-d', strtotime('-8 days'));
        $this->pdo->exec("UPDATE users SET ultimo_acesso = '{$antigo}' WHERE id = {$this->userId}");

        $this->assertTrue($this->checker->isPersistente($this->pdo, $this->userId));
    }

    public function testPersistenteNaoComMenosDe7Dias(): void
    {
        $recente = date('Y-m-d', strtotime('-3 days'));
        $this->pdo->exec("UPDATE users SET ultimo_acesso = '{$recente}' WHERE id = {$this->userId}");

        $this->assertFalse($this->checker->isPersistente($this->pdo, $this->userId));
    }

    // ----------------------------------------------------------------
    // Badges especiais (velocista, criativo, pyodide_master)
    // ----------------------------------------------------------------

    public function testConcederBadgeEspecialVelocista(): void
    {
        $result = $this->checker->concederBadgeEspecial($this->userId, 'velocista');
        $this->assertTrue($result);
    }

    public function testBadgeEspecialNaoAceitaIdInvalido(): void
    {
        $result = $this->checker->concederBadgeEspecial($this->userId, 'primeiro_codigo');
        $this->assertFalse($result); // 'primeiro_codigo' não é badge especial
    }

    public function testBadgeEspecialIdempotente(): void
    {
        $this->checker->concederBadgeEspecial($this->userId, 'velocista');
        $result = $this->checker->concederBadgeEspecial($this->userId, 'velocista');
        $this->assertFalse($result); // já concedida
    }
}
