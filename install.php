<?php
/**
 * Script de instalação do FetecPy.
 * Deve ser executado uma vez antes de usar o sistema.
 * Cria a pasta data/, aplica as migrations e define permissões.
 */
declare(strict_types=1);

// Diretório raiz do projeto
define('ROOT_DIR', __DIR__);
define('DATA_DIR', ROOT_DIR . '/data');
define('MIGRATIONS_DIR', ROOT_DIR . '/migrations');
define('DB_FILE', DATA_DIR . '/fetecpy.db');

// Exibe mensagem com timestamp para facilitar o rastreamento
function log_msg(string $msg): void
{
    echo '[' . date('H:i:s') . '] ' . $msg . PHP_EOL;
}

// Verifica a versão mínima do PHP
if (PHP_VERSION_ID < 80000) {
    log_msg('ERRO: PHP 8.0 ou superior é obrigatório. Versão atual: ' . PHP_VERSION);
    exit(1);
}

// Verifica se a extensão PDO_SQLite está disponível
if (!extension_loaded('pdo_sqlite')) {
    log_msg('ERRO: Extensão pdo_sqlite não está habilitada. Verifique o php.ini.');
    exit(1);
}

log_msg('Iniciando instalação do FetecPy...');

// Cria a pasta data/ se não existir
if (!is_dir(DATA_DIR)) {
    if (!mkdir(DATA_DIR, 0755, true)) {
        log_msg('ERRO: Não foi possível criar a pasta data/. Verifique as permissões.');
        exit(1);
    }
    log_msg('Pasta data/ criada com sucesso.');
} else {
    log_msg('Pasta data/ já existe — OK.');
}

// Tenta conectar ao banco SQLite (cria o arquivo automaticamente)
try {
    $pdo = new PDO('sqlite:' . DB_FILE, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    // Habilita chaves estrangeiras (desativado por padrão no SQLite)
    $pdo->exec('PRAGMA foreign_keys = ON;');
    log_msg('Conexão com o banco de dados estabelecida: ' . DB_FILE);
} catch (PDOException $e) {
    log_msg('ERRO ao conectar ao banco: ' . $e->getMessage());
    exit(1);
}

// Aplica todas as migrations em ordem numérica
$migrationFiles = glob(MIGRATIONS_DIR . '/*.sql');
if (empty($migrationFiles)) {
    log_msg('Nenhum arquivo de migration encontrado em migrations/.');
} else {
    // Ordena pelos nomes dos arquivos (001_, 002_, ...)
    sort($migrationFiles);
    foreach ($migrationFiles as $file) {
        $filename = basename($file);
        log_msg("Aplicando migration: $filename ...");
        $sql = file_get_contents($file);
        if ($sql === false) {
            log_msg("ERRO: Não foi possível ler o arquivo $filename.");
            exit(1);
        }
        try {
            $pdo->exec($sql);
            log_msg("Migration $filename aplicada com sucesso.");
        } catch (PDOException $e) {
            log_msg("ERRO ao aplicar $filename: " . $e->getMessage());
            exit(1);
        }
    }
}

// Ajusta permissões da pasta data/ e do arquivo de banco
// (necessário em hospedagens compartilhadas onde o webserver roda como usuário diferente)
chmod(DATA_DIR, 0755);
if (file_exists(DB_FILE)) {
    chmod(DB_FILE, 0644);
    log_msg('Permissões ajustadas: data/fetecpy.db (0644).');
}

log_msg('');
log_msg('Instalação concluída com sucesso!');
log_msg('Próximo passo: aponte o DocumentRoot do seu servidor para a pasta public/');
log_msg('Para iniciar localmente: php -S localhost:8000 -t public/');
