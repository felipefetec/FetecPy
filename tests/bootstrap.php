<?php
/**
 * Bootstrap do PHPUnit — executado uma vez antes de todos os testes.
 *
 * Responsabilidades:
 *   1. Carregar o autoloader do Composer
 *   2. Apontar o banco para SQLite em memória (isolado por execução)
 *   3. Aplicar as migrations para criar o schema completo
 *   4. Ativar o modo de teste do JsonResponse (evita exit() no PHPUnit)
 */
declare(strict_types=1);

// Carrega o autoloader PSR-4 gerado pelo Composer
require_once dirname(__DIR__) . '/vendor/autoload.php';

use FetecPy\Database;
use FetecPy\Http\JsonResponse;

// Banco em memória: cada execução da suíte começa com banco limpo.
// Não há risco de testes alterarem dados persistentes.
putenv('DB_PATH=:memory:');

// Aplica as migrations para criar todas as tabelas no banco em memória
Database::executarMigrations();

// Ativa modo de teste: JsonResponse lança ResponseException em vez de exit()
// Sem isso, qualquer resposta JSON encerraria o processo do PHPUnit
JsonResponse::$modoTeste = true;
