<?php
/**
 * Controller de progresso do aluno.
 *
 * Retorna um resumo do progresso por módulo: quantos itens existem,
 * quantos foram concluídos e o percentual calculado.
 * Usado pelo dashboard para renderizar as barras de progresso.
 */
declare(strict_types=1);

namespace FetecPy\Controllers;

use FetecPy\Database;
use FetecPy\Http\AuthMiddleware;
use FetecPy\Http\JsonResponse;
use FetecPy\Http\Request;

class ProgressController
{
    /**
     * GET /api/progress
     *
     * Retorna o progresso do aluno agrupado por módulo.
     * Cada módulo traz: total de itens concluídos, XP ganho nele,
     * e o status calculado (bloqueado / em_andamento / concluido).
     *
     * A lógica de "bloqueado" segue a sequência dos módulos:
     * um módulo só fica disponível quando o anterior atingir 100%.
     * O módulo 1 está sempre disponível.
     */
    public function index(Request $request): void
    {
        AuthMiddleware::exigir($request);

        $userId = (int) $request->user['id'];
        $pdo    = Database::getConnection();

        // Busca todos os registros de progresso do aluno de uma vez
        $stmt = $pdo->prepare(
            'SELECT modulo, item_tipo, status, xp_ganho
             FROM progress
             WHERE user_id = ?
             ORDER BY modulo ASC'
        );
        $stmt->execute([$userId]);
        $registros = $stmt->fetchAll();

        // Agrupa registros por módulo para calcular percentuais
        $porModulo = [];
        foreach ($registros as $reg) {
            $mod = $reg['modulo'];
            if (!isset($porModulo[$mod])) {
                $porModulo[$mod] = ['concluidos' => 0, 'xp' => 0];
            }
            // Conta qualquer status como "tentado" — concluídos têm peso maior
            if (in_array($reg['status'], ['concluido', 'concluido_com_ajuda'])) {
                $porModulo[$mod]['concluidos']++;
            }
            $porModulo[$mod]['xp'] += (int) $reg['xp_ganho'];
        }

        // Definição de quantos itens cada módulo tem no total
        // (exercícios + quiz + mini-projeto). Atualizar conforme conteúdo for adicionado.
        $totalPorModulo = [
            '01' => 6,  // 5 exercícios + 1 mini-projeto
            '02' => 6,
            '03' => 6,
            '04' => 6,
            '05' => 6,
            '06' => 6,
            '07' => 6,
            '08' => 15, // módulo 8 tem 5 submódulos com mais itens
        ];

        // Monta a resposta com status calculado para cada módulo
        $resultado   = [];
        $anteriorCompleto = true; // módulo 1 sempre disponível

        foreach ($totalPorModulo as $modulo => $totalItens) {
            $concluidos = $porModulo[$modulo]['concluidos'] ?? 0;
            $xp         = $porModulo[$modulo]['xp'] ?? 0;
            $percentual = $totalItens > 0 ? round(($concluidos / $totalItens) * 100) : 0;

            // Determina o status de acesso ao módulo
            if (!$anteriorCompleto) {
                $status = 'bloqueado';
            } elseif ($percentual >= 100) {
                $status = 'concluido';
            } elseif ($concluidos > 0) {
                $status = 'em_andamento';
            } else {
                $status = 'disponivel';
            }

            $resultado[] = [
                'modulo'     => $modulo,
                'concluidos' => $concluidos,
                'total'      => $totalItens,
                'percentual' => $percentual,
                'xp'         => $xp,
                'status'     => $status,
            ];

            // O próximo módulo só fica disponível se este estiver 100% completo
            $anteriorCompleto = ($percentual >= 100);
        }

        JsonResponse::enviar($resultado);
    }
}
