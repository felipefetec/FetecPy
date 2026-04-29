<?php
/**
 * Classe Database — gerencia a conexão única com o SQLite via PDO.
 *
 * Usa o padrão Singleton: apenas uma instância de PDO é criada
 * por requisição, evitando abrir múltiplas conexões com o banco.
 */
declare(strict_types=1);

namespace FetecPy;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    // Instância única compartilhada em toda a requisição
    private static ?PDO $instance = null;

    // Impede instanciação direta — use Database::getConnection()
    private function __construct() {}
    private function __clone() {}

    /**
     * Retorna a conexão PDO, criando-a na primeira chamada.
     *
     * Configurações aplicadas:
     * - ERRMODE_EXCEPTION: qualquer erro SQL lança PDOException (facilita debug)
     * - FETCH_ASSOC: resultados sempre como array associativo (sem índices numéricos)
     * - foreign_keys ON: SQLite não ativa chaves estrangeiras por padrão
     * - journal_mode WAL: melhora concorrência em leitura simultânea
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dbPath = self::resolverCaminhoBanco();

            try {
                self::$instance = new PDO('sqlite:' . $dbPath, null, null, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);

                // Ativa suporte a chaves estrangeiras (desativado por padrão no SQLite)
                self::$instance->exec('PRAGMA foreign_keys = ON;');
                // WAL permite leituras simultâneas enquanto uma escrita acontece
                self::$instance->exec('PRAGMA journal_mode = WAL;');
            } catch (PDOException $e) {
                // Não expor detalhes internos em produção — loga e lança mensagem genérica
                error_log('[FetecPy] Erro ao conectar ao banco: ' . $e->getMessage());
                throw new RuntimeException('Não foi possível conectar ao banco de dados.');
            }
        }

        return self::$instance;
    }

    /**
     * Executa todos os arquivos de migration em ordem numérica.
     *
     * Chamado pelo install.php na primeira configuração do sistema.
     * Assume que os arquivos estão em {raiz}/migrations/*.sql.
     */
    public static function executarMigrations(): void
    {
        $pdo = self::getConnection();
        $migrationsDir = dirname(__DIR__) . '/migrations';

        $arquivos = glob($migrationsDir . '/*.sql');
        if (empty($arquivos)) {
            return;
        }

        // Ordena por nome para garantir execução na sequência correta (001_, 002_, ...)
        sort($arquivos);

        foreach ($arquivos as $arquivo) {
            $sql = file_get_contents($arquivo);
            if ($sql === false) {
                throw new RuntimeException('Não foi possível ler a migration: ' . basename($arquivo));
            }
            $pdo->exec($sql);
        }
    }

    /**
     * Resolve o caminho absoluto do arquivo de banco de dados.
     *
     * Permite sobrescrever o caminho via variável de ambiente DB_PATH,
     * útil para testes (banco em memória) ou ambientes de staging.
     */
    private static function resolverCaminhoBanco(): string
    {
        // Variável de ambiente permite apontar para banco em memória nos testes:
        // putenv('DB_PATH=:memory:')
        $envPath = getenv('DB_PATH');
        if ($envPath !== false && $envPath !== '') {
            return $envPath;
        }

        // Caminho padrão em produção: pasta data/ na raiz do projeto
        return dirname(__DIR__) . '/data/fetecpy.db';
    }

    /**
     * Reseta a instância singleton.
     * Usado exclusivamente nos testes para garantir banco limpo a cada teste.
     */
    public static function resetar(): void
    {
        self::$instance = null;
    }
}
