<?php
/**
 * Front controller da API FetecPy.
 *
 * Toda requisição para /api/* chega aqui via .htaccess.
 * Responsabilidades:
 *   1. Carregar o autoloader do Composer
 *   2. Montar o objeto Request com os dados da requisição atual
 *   3. Registrar todas as rotas da aplicação no Router
 *   4. Despachar a requisição para o controller correto
 *
 * Controllers não devem saber de rotas; rotas não devem conter lógica.
 */
declare(strict_types=1);

// Carrega o autoloader PSR-4 gerado pelo Composer (mapeia FetecPy\ → src/)
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use FetecPy\Http\JsonResponse;
use FetecPy\Http\Request;
use FetecPy\Http\Router;
use FetecPy\Controllers\HealthController;
use FetecPy\Controllers\AuthController;
use FetecPy\Controllers\UserController;
use FetecPy\Controllers\ProgressController;
use FetecPy\Controllers\ModuleController;
use FetecPy\Controllers\ExerciseController;

// Captura qualquer exceção não tratada e retorna JSON em vez de HTML de erro
set_exception_handler(function (Throwable $e): void {
    // Em produção não expomos detalhes internos — apenas logamos
    error_log('[FetecPy] Exceção não tratada: ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
    JsonResponse::erro('Erro interno do servidor.', 500);
});

// ----------------------------------------------------------------
// Monta a requisição e o roteador
// ----------------------------------------------------------------
$request = new Request();
$router  = new Router();

// ----------------------------------------------------------------
// Registro de rotas
// Formato: $router->método('padrão', [Controller::class, 'action'])
//
// Padrões com parâmetros dinâmicos usam :nomeParam, ex: 'modules/:id'
// Os valores capturados ficam disponíveis em $request->params
// ----------------------------------------------------------------

// -- Saúde da API (sem autenticação) --
$router->get('health', [HealthController::class, 'index']);

// -- Autenticação --
$router->post('auth/login',  [AuthController::class, 'login']);
$router->post('auth/logout', [AuthController::class, 'logout']);

// -- Perfil do usuário --
$router->get('me', [UserController::class, 'me']);

// -- Módulos --
$router->get('modules',     [ModuleController::class, 'index']);
$router->get('modules/:id', [ModuleController::class, 'show']);

// -- Exercícios (Prompt 5.1) --
$router->get('modules/:moduloId/exercises/:exId',         [ExerciseController::class, 'show']);
$router->post('modules/:moduloId/exercises/:exId/submit', [ExerciseController::class, 'submit']);

// -- Progresso --
$router->get('progress', [ProgressController::class, 'index']);

// -- Badges (Prompt 6.2) --
// $router->get('badges', [BadgeController::class, 'index']);

// ----------------------------------------------------------------
// Despacha a requisição — encontra a rota e chama o controller
// ----------------------------------------------------------------
$router->despachar($request);
