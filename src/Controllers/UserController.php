<?php
/**
 * Controller de dados do usuário autenticado.
 *
 * Expõe o perfil do aluno logado, incluindo XP, streak e badges
 * conquistadas. Todas as rotas aqui exigem autenticação.
 */
declare(strict_types=1);

namespace FetecPy\Controllers;

use FetecPy\Database;
use FetecPy\Http\AuthMiddleware;
use FetecPy\Http\JsonResponse;
use FetecPy\Http\Request;

class UserController
{
    /**
     * GET /api/me
     *
     * Retorna os dados do aluno autenticado:
     *   - id, nome, sobrenome, chave
     *   - xp_total, streak_dias, ultimo_acesso, created_at
     *   - badges: lista de badges conquistadas (id + data de conquista)
     *
     * O pin_hash nunca é incluído — foi removido pelo AuthService
     * antes de popular $request->user.
     */
    public function me(Request $request): void
    {
        // Garante que só usuários autenticados chegam aqui
        AuthMiddleware::exigir($request);

        $usuario = $request->user;
        $userId  = (int) $usuario['id'];

        // Busca as badges conquistadas pelo aluno
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT badge_id, conquistado_em
             FROM user_badges
             WHERE user_id = ?
             ORDER BY conquistado_em ASC'
        );
        $stmt->execute([$userId]);
        $badges = $stmt->fetchAll();

        JsonResponse::enviar([
            'id'            => $userId,
            'nome'          => $usuario['nome'],
            'sobrenome'     => $usuario['sobrenome'],
            'chave'         => $usuario['chave'],
            'xp_total'      => (int) $usuario['xp_total'],
            'streak_dias'   => (int) $usuario['streak_dias'],
            'ultimo_acesso' => $usuario['ultimo_acesso'],
            'created_at'    => $usuario['created_at'],
            // Lista de badges: [{badge_id: "streak_3", conquistado_em: "..."}]
            'badges'        => $badges,
        ]);
    }
}
