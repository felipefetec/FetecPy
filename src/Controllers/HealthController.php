<?php
/**
 * Controller de saúde da API.
 *
 * Endpoint simples usado para verificar se a API está respondendo.
 * Útil para monitoramento e para confirmar que o deploy funcionou.
 */
declare(strict_types=1);

namespace FetecPy\Controllers;

use FetecPy\Http\JsonResponse;
use FetecPy\Http\Request;

class HealthController
{
    /**
     * GET /api/health
     *
     * Retorna o status da API e a versão atual.
     * Não requer autenticação — deve ser sempre acessível.
     */
    public function index(Request $request): void
    {
        JsonResponse::enviar([
            'status'  => 'ok',
            'version' => '0.1.0',
        ]);
    }
}
