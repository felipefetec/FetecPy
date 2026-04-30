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

        // Busca todos os itens relevantes — exercícios para progresso de conclusão,
        // quiz para XP. Seções (item_tipo = "secao") ficam fora: são progresso de
        // leitura e não devem contar para o desbloqueio do próximo módulo.
        $stmt = $pdo->prepare(
            'SELECT modulo, item_tipo, status, xp_ganho
             FROM progress
             WHERE user_id = ? AND item_tipo IN ("exercicio", "quiz")
             ORDER BY modulo ASC'
        );
        $stmt->execute([$userId]);
        $registros = $stmt->fetchAll();

        // Agrupa por módulo:
        //   concluidos → conta apenas exercícios (determinam o desbloqueio)
        //   xp         → soma tudo incluindo quiz (XP real do aluno)
        $porModulo = [];
        foreach ($registros as $reg) {
            $mod = $reg['modulo'];
            if (!isset($porModulo[$mod])) {
                $porModulo[$mod] = ['concluidos' => 0, 'xp' => 0];
            }
            // Só exercícios contam para avançar de módulo — quiz é bônus de XP
            if ($reg['item_tipo'] === 'exercicio'
                && in_array($reg['status'], ['concluido', 'concluido_com_ajuda'])) {
                $porModulo[$mod]['concluidos']++;
            }
            $porModulo[$mod]['xp'] += (int) $reg['xp_ganho'];
        }

        // Total de exercícios por módulo — 5 em todos.
        // Quiz não entra no total: é opcional e só contribui com XP.
        $totalPorModulo = [
            '01' => 5,
            '02' => 5,
            '03' => 5,
            '04' => 5,
            '05' => 5,
            '06' => 5,
            '07' => 5,
            '08' => 5,
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

            // O próximo módulo só fica disponível se ESTE estiver 100% completo
            // E não estava bloqueado — evita que itens registrados em módulos
            // inacessíveis desbloqueiem o módulo seguinte de forma indevida.
            $anteriorCompleto = ($percentual >= 100 && $status !== 'bloqueado');
        }

        JsonResponse::enviar($resultado);
    }
}
