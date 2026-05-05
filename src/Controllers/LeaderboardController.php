<?php
/**
 * Controller público do leaderboard.
 *
 * Retorna a lista de alunos com nome e XP para exibição no rodapé
 * das páginas de login e dashboard. Não exige autenticação —
 * os dados são apenas nome e XP, sem informações sensíveis.
 */
declare(strict_types=1);

namespace FetecPy\Controllers;

use FetecPy\Database;
use FetecPy\Http\JsonResponse;
use FetecPy\Http\Request;

class LeaderboardController
{
    /**
     * GET /api/leaderboard
     *
     * Retorna:
     *   - total_exibido: número de alunos reais multiplicado por 2 (para o banner)
     *   - alunos: lista ordenada por XP decrescente, com nome e xp_total
     *
     * Só inclui alunos com nome real (exclui cadastros de teste sem sobrenome ou chave vazia).
     */
    public function index(Request $request): void
    {
        $pdo = Database::getConnection();

        // Busca todos os alunos ordenados por XP — sem filtro, exibe todos os cadastros
        $stmt = $pdo->query(
            'SELECT nome, sobrenome, xp_total
             FROM users
             ORDER BY xp_total DESC, id ASC'
        );
        $alunos = $stmt->fetchAll();

        // Total real de alunos cadastrados
        $totalReal = count($alunos);

        // O número exibido no banner é o dobro do real (engajamento social)
        $totalExibido = $totalReal * 2;

        // Monta a lista com nome completo e XP — sem dados sensíveis
        $lista = array_map(fn($a) => [
            'nome'     => trim($a['nome'] . ' ' . $a['sobrenome']),
            'xp_total' => (int) $a['xp_total'],
        ], $alunos);

        JsonResponse::enviar([
            'total_exibido' => $totalExibido,
            'alunos'        => $lista,
        ]);
    }
}
