<?php
/**
 * Serviço de autenticação do FetecPy.
 *
 * Responsável por toda a lógica de identidade: normalização de chave,
 * cadastro implícito, validação de PIN, criação/validação de sessão e
 * rate limiting de tentativas de login.
 *
 * Não deve depender de Request/Response — é camada de serviço pura,
 * testável sem HTTP.
 */
declare(strict_types=1);

namespace FetecPy\Services;

use FetecPy\Database;
use FetecPy\Exceptions\AuthException;
use PDO;

class AuthService
{
    // Limite de tentativas erradas antes de bloquear por 60 segundos
    private const MAX_TENTATIVAS = 5;
    private const JANELA_SEGUNDOS = 60;

    // Duração da sessão: 30 dias em segundos
    private const SESSAO_DURACAO = 30 * 24 * 60 * 60;

    // ----------------------------------------------------------------
    // Normalização de chave de login
    // ----------------------------------------------------------------

    /**
     * Gera a chave de login a partir do nome e sobrenome do aluno.
     *
     * Exemplos:
     *   "Felipe", "Tavares"  → "felipe_tavares"
     *   "João",   "da Silva" → "joao_da_silva"
     *   "María",  "López"    → "maria_lopez"
     *
     * A chave é usada como identificador no login no lugar de e-mail.
     * Deve ser única por aluno — colisões são resolvidas pelo UNIQUE do banco.
     */
    public function normalizarChave(string $nome, string $sobrenome): string
    {
        $texto = trim($nome) . '_' . trim($sobrenome);

        // Remove acentos transliterando para ASCII (ex: ã → a, é → e)
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);

        // Converte para lowercase
        $texto = strtolower($texto);

        // Substitui espaços e hifens por underscores
        $texto = preg_replace('/[\s\-]+/', '_', $texto);

        // Remove qualquer caractere que não seja letra, número ou underscore
        $texto = preg_replace('/[^a-z0-9_]/', '', $texto);

        // Colapsa múltiplos underscores consecutivos em um só
        $texto = preg_replace('/_+/', '_', $texto);

        return trim($texto, '_');
    }

    // ----------------------------------------------------------------
    // Cadastro e login
    // ----------------------------------------------------------------

    /**
     * Cadastra um novo aluno ou autentica um existente.
     *
     * - Se a chave NÃO existe no banco: cria o usuário e retorna token.
     * - Se a chave JÁ existe: valida o PIN e retorna token se correto.
     *
     * Essa abordagem de "cadastro implícito" elimina a etapa separada
     * de registro — o aluno simplesmente digita nome/sobrenome/PIN na
     * primeira vez e já fica logado.
     *
     * @return array{token: string, usuario: array, novo: bool}
     * @throws AuthException se PIN inválido ou rate limit atingido
     */
    public function cadastrarOuLogin(string $nome, string $sobrenome, string $pin): array
    {
        $chave = $this->normalizarChave($nome, $sobrenome);
        $pdo   = Database::getConnection();

        // Busca usuário existente pela chave normalizada
        $stmt = $pdo->prepare('SELECT * FROM users WHERE chave = ?');
        $stmt->execute([$chave]);
        $usuario = $stmt->fetch();

        if ($usuario === false) {
            // Usuário não existe — cadastra e já autentica
            $userId = $this->criarUsuario($pdo, $nome, $sobrenome, $chave, $pin);
            $usuario = $this->buscarPorId($pdo, $userId);
            $token   = $this->criarSessao($userId);

            return ['token' => $token, 'usuario' => $this->sanitizarUsuario($usuario), 'novo' => true];
        }

        // Usuário existe — verifica rate limit antes de checar PIN
        $this->verificarRateLimit($pdo, $chave);

        // Valida o PIN com bcrypt
        if (!password_verify($pin, $usuario['pin_hash'])) {
            $this->registrarTentativaFalha($pdo, $chave);
            throw new AuthException('PIN incorreto.', AuthException::PIN_INVALIDO);
        }

        // PIN correto — limpa rate limit e cria sessão
        $this->limparRateLimit($pdo, $chave);
        $token = $this->criarSessao((int) $usuario['id']);

        return ['token' => $token, 'usuario' => $this->sanitizarUsuario($usuario), 'novo' => false];
    }

    // ----------------------------------------------------------------
    // Sessão
    // ----------------------------------------------------------------

    /**
     * Cria uma nova sessão para o usuário e retorna o token gerado.
     *
     * O token é um hex de 64 caracteres (32 bytes aleatórios).
     * Expira em 30 dias a partir da criação.
     */
    public function criarSessao(int $userId): string
    {
        // random_bytes é criptograficamente seguro — adequado para tokens de auth
        $token     = bin2hex(random_bytes(32));
        $expiraEm  = date('Y-m-d H:i:s', time() + self::SESSAO_DURACAO);

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT INTO sessions (token, user_id, expires_at) VALUES (?, ?, ?)');
        $stmt->execute([$token, $userId, $expiraEm]);

        return $token;
    }

    /**
     * Valida um token de sessão e retorna os dados do usuário associado.
     *
     * Retorna null se o token não existir ou estiver expirado.
     * Sessões expiradas são removidas automaticamente na verificação.
     */
    public function validarToken(string $token): ?array
    {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare(
            'SELECT u.* FROM sessions s
             JOIN users u ON u.id = s.user_id
             WHERE s.token = ? AND s.expires_at > CURRENT_TIMESTAMP'
        );
        $stmt->execute([$token]);
        $usuario = $stmt->fetch();

        if ($usuario === false) {
            // Remove sessões expiradas para não acumular lixo no banco
            $pdo->prepare('DELETE FROM sessions WHERE expires_at <= CURRENT_TIMESTAMP')->execute();
            return null;
        }

        return $this->sanitizarUsuario($usuario);
    }

    /**
     * Encerra a sessão removendo o token do banco.
     * Operação idempotente — não falha se o token já não existir.
     */
    public function logout(string $token): void
    {
        $pdo = Database::getConnection();
        $pdo->prepare('DELETE FROM sessions WHERE token = ?')->execute([$token]);
    }

    // ----------------------------------------------------------------
    // Rate limiting
    // ----------------------------------------------------------------

    /**
     * Verifica se a chave atingiu o limite de tentativas erradas.
     * Lança AuthException::RATE_LIMIT se estiver bloqueada.
     */
    private function verificarRateLimit(PDO $pdo, string $chave): void
    {
        $stmt = $pdo->prepare(
            'SELECT tentativas, janela_inicio FROM rate_limits WHERE chave = ?'
        );
        $stmt->execute([$chave]);
        $registro = $stmt->fetch();

        if ($registro === false) {
            return; // Nenhuma tentativa falha registrada — livre para prosseguir
        }

        $segundosDecorridos = time() - strtotime($registro['janela_inicio']);

        if ($segundosDecorridos > self::JANELA_SEGUNDOS) {
            // A janela de bloqueio expirou — limpa e libera
            $this->limparRateLimit($pdo, $chave);
            return;
        }

        if ((int) $registro['tentativas'] >= self::MAX_TENTATIVAS) {
            $restam = self::JANELA_SEGUNDOS - $segundosDecorridos;
            throw new AuthException(
                "Muitas tentativas incorretas. Aguarde {$restam} segundos.",
                AuthException::RATE_LIMIT
            );
        }
    }

    /**
     * Incrementa o contador de tentativas falhas para a chave.
     * Se não existir registro, cria um novo com janela iniciando agora.
     */
    private function registrarTentativaFalha(PDO $pdo, string $chave): void
    {
        // INSERT OR REPLACE: cria ou atualiza o registro de rate limit
        $pdo->prepare(
            'INSERT INTO rate_limits (chave, tentativas, janela_inicio)
             VALUES (?, 1, CURRENT_TIMESTAMP)
             ON CONFLICT(chave) DO UPDATE SET tentativas = tentativas + 1'
        )->execute([$chave]);
    }

    /**
     * Remove o registro de rate limit para a chave (login bem-sucedido).
     */
    private function limparRateLimit(PDO $pdo, string $chave): void
    {
        $pdo->prepare('DELETE FROM rate_limits WHERE chave = ?')->execute([$chave]);
    }

    // ----------------------------------------------------------------
    // Helpers internos
    // ----------------------------------------------------------------

    /**
     * Cria um novo usuário no banco e retorna o ID gerado.
     */
    private function criarUsuario(PDO $pdo, string $nome, string $sobrenome, string $chave, string $pin): int
    {
        // bcrypt com custo padrão (10) — adequado para PIN de 4 dígitos
        $pinHash = password_hash($pin, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare(
            'INSERT INTO users (nome, sobrenome, chave, pin_hash) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$nome, $sobrenome, $chave, $pinHash]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Busca um usuário pelo ID — usado logo após INSERT para retornar dados completos.
     */
    private function buscarPorId(PDO $pdo, int $id): array
    {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Remove campos sensíveis antes de retornar dados do usuário para o controller.
     * O pin_hash nunca deve trafegar fora da camada de serviço.
     */
    private function sanitizarUsuario(array $usuario): array
    {
        unset($usuario['pin_hash']);
        return $usuario;
    }
}
