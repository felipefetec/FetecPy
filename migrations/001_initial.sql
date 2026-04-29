-- =============================================================
-- Migration 001 — Schema inicial do FetecPy
-- Cria todas as tabelas do sistema e seus índices.
-- Executado automaticamente pelo install.php na primeira vez.
-- =============================================================

PRAGMA foreign_keys = ON;

-- -------------------------------------------------------------
-- Tabela: users
-- Armazena os dados de cada aluno. A autenticação é feita pela
-- combinação de "chave" (nome_sobrenome normalizado) + PIN bcrypt.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    nome         TEXT    NOT NULL,
    sobrenome    TEXT    NOT NULL,
    -- Chave derivada: "Felipe Silva" → "felipe_silva" (sem acento, lowercase)
    -- Usada no login no lugar de e-mail/usuário
    chave        TEXT    UNIQUE NOT NULL,
    pin_hash     TEXT    NOT NULL,           -- hash bcrypt do PIN numérico
    xp_total     INTEGER NOT NULL DEFAULT 0,
    streak_dias  INTEGER NOT NULL DEFAULT 0,
    ultimo_acesso DATE,                      -- data do último exercício resolvido
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Busca de usuário pelo login é sempre pela chave normalizada
CREATE INDEX IF NOT EXISTS idx_users_chave ON users(chave);

-- -------------------------------------------------------------
-- Tabela: sessions
-- Tokens de sessão gerados no login. Expiram em 30 dias.
-- O frontend guarda o token em localStorage e o envia como
-- "Authorization: Bearer <token>" em toda requisição autenticada.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sessions (
    token      TEXT    PRIMARY KEY,          -- bin2hex(random_bytes(32))
    user_id    INTEGER NOT NULL,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Toda requisição autenticada precisa buscar a sessão pelo token
CREATE INDEX IF NOT EXISTS idx_sessions_user_id ON sessions(user_id);
CREATE INDEX IF NOT EXISTS idx_sessions_expires  ON sessions(expires_at);

-- -------------------------------------------------------------
-- Tabela: progress
-- Registra o progresso de cada aluno em cada item do curso.
-- Um "item" pode ser: exercício, seção, quiz ou mini-projeto.
-- A constraint UNIQUE garante 1 registro por aluno por item.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS progress (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id     INTEGER NOT NULL,
    modulo      TEXT    NOT NULL,   -- ex: "03"
    -- Tipo do item para separar exercícios, seções, quizzes e projetos
    item_tipo   TEXT    NOT NULL,   -- "exercicio" | "secao" | "quiz" | "projeto"
    item_id     TEXT    NOT NULL,   -- ex: "ex02", "3.1", "mini-projeto"
    -- Status final do item após interação do aluno
    status      TEXT    NOT NULL,   -- "concluido" | "concluido_com_ajuda" | "tentado"
    tentativas  INTEGER NOT NULL DEFAULT 0,
    codigo_salvo TEXT,              -- último código submetido pelo aluno
    xp_ganho    INTEGER NOT NULL DEFAULT 0,
    -- Timestamp de quando o item foi concluído pela última vez
    concluido_em DATETIME,

    -- Impede duplicatas: um aluno só tem um registro por item
    UNIQUE(user_id, modulo, item_tipo, item_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Consultas frequentes: tudo de um aluno, tudo de um módulo específico
CREATE INDEX IF NOT EXISTS idx_progress_user_id     ON progress(user_id);
CREATE INDEX IF NOT EXISTS idx_progress_user_modulo ON progress(user_id, modulo);

-- -------------------------------------------------------------
-- Tabela: user_badges
-- Guarda as badges conquistadas por cada aluno.
-- A constraint UNIQUE evita conceder a mesma badge duas vezes.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS user_badges (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id         INTEGER NOT NULL,
    badge_id        TEXT    NOT NULL,   -- ex: "primeiro_codigo", "streak_7"
    conquistado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE(user_id, badge_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Listar badges de um aluno é operação frequente (dashboard e pós-exercício)
CREATE INDEX IF NOT EXISTS idx_user_badges_user_id ON user_badges(user_id);

-- -------------------------------------------------------------
-- Tabela: rate_limits
-- Controla tentativas de login por chave de usuário para evitar
-- ataques de força bruta. Limite: 5 tentativas erradas por minuto.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS rate_limits (
    chave        TEXT    NOT NULL,   -- mesma chave da tabela users
    tentativas   INTEGER NOT NULL DEFAULT 1,
    -- Janela de 60 segundos a partir da primeira tentativa falha
    janela_inicio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (chave)
);
