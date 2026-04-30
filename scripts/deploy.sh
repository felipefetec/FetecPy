#!/usr/bin/env bash
# ============================================================
# FetecPy — Script de deploy via FTP
#
# Pré-requisitos: lftp instalado (apt install lftp)
#
# Uso:
#   ./scripts/deploy.sh
#
# O script lê as credenciais de FTP_HOST, FTP_USER, FTP_PASS
# e FTP_DIR das variáveis de ambiente ou do arquivo .env.deploy
# (na raiz do projeto, ignorado pelo git).
#
# Exemplo de .env.deploy:
#   FTP_HOST=ftp.seudominio.com
#   FTP_USER=usuario@seudominio.com
#   FTP_PASS=senha_aqui
#   FTP_DIR=/public_html/fetecpy   # ou /httpdocs/fetecpy
# ============================================================

set -euo pipefail
cd "$(dirname "$0")/.."

# ----------------------------------------------------------------
# Carrega credenciais do arquivo .env.deploy (se existir)
# ----------------------------------------------------------------
ENV_FILE=".env.deploy"
if [[ -f "$ENV_FILE" ]]; then
  # shellcheck disable=SC1090
  set -o allexport && source "$ENV_FILE" && set +o allexport
fi

# Valida que as variáveis necessárias estão definidas
: "${FTP_HOST:?Defina FTP_HOST em .env.deploy ou como variável de ambiente}"
: "${FTP_USER:?Defina FTP_USER em .env.deploy ou como variável de ambiente}"
: "${FTP_PASS:?Defina FTP_PASS em .env.deploy ou como variável de ambiente}"
: "${FTP_DIR:?Defina FTP_DIR em .env.deploy ou como variável de ambiente}"

echo ""
echo "=============================="
echo "  FetecPy — Deploy via FTP"
echo "=============================="
echo "  Host:   $FTP_HOST"
echo "  Destino: $FTP_DIR"
echo ""

# ----------------------------------------------------------------
# Passo 1 — Otimiza o autoloader para produção (sem devDependencies)
# ----------------------------------------------------------------
echo "[ 1/4 ] Instalando dependências de produção..."
composer install --no-dev --optimize-autoloader --quiet
echo "        OK — vendor/ otimizado."

# ----------------------------------------------------------------
# Passo 2 — Roda os testes antes de subir
# ----------------------------------------------------------------
echo "[ 2/4 ] Rodando testes..."
if npm test --silent 2>/dev/null && php vendor/bin/phpunit --no-coverage -q 2>/dev/null; then
  echo "        OK — todos os testes passaram."
else
  echo "        AVISO — testes falharam. Pressione Enter para continuar mesmo assim, ou Ctrl+C para cancelar."
  read -r
fi

# ----------------------------------------------------------------
# Passo 3 — Upload via lftp (espelho com exclusões)
# ----------------------------------------------------------------
echo "[ 3/4 ] Enviando arquivos via FTP..."

lftp -c "
  open -u '$FTP_USER','$FTP_PASS' '$FTP_HOST'
  set ssl:verify-certificate no
  mirror \
    --reverse \
    --delete \
    --verbose \
    --exclude-glob .git/ \
    --exclude-glob .gitignore \
    --exclude-glob node_modules/ \
    --exclude-glob tests/ \
    --exclude-glob coverage/ \
    --exclude-glob .githooks/ \
    --exclude-glob scripts/ \
    --exclude-glob '*.md' \
    --exclude-glob 'DECISIONS.md' \
    --exclude-glob 'TAREFAS.md' \
    --exclude-glob 'PROMPTS*.md' \
    --exclude-glob 'PLANO*.md' \
    --exclude-glob '.env*' \
    --exclude-glob 'vitest.config*' \
    --exclude-glob 'package*.json' \
    --exclude-glob 'phpunit.xml' \
    --exclude-glob 'composer.lock' \
    . '$FTP_DIR'
  bye
"

echo "        OK — arquivos enviados."

# ----------------------------------------------------------------
# Passo 4 — Restaura vendor com devDependencies para desenvolvimento
# ----------------------------------------------------------------
echo "[ 4/4 ] Restaurando vendor de desenvolvimento..."
composer install --quiet
echo "        OK."

echo ""
echo "=============================="
echo "  Deploy concluído!"
echo "=============================="
echo ""
echo "  Próximos passos (primeira vez):"
echo "  1. Acesse https://seudominio.com/php-check.php"
echo "  2. Confirme que todos os requisitos estão OK"
echo "  3. Acesse https://seudominio.com/install.php"
echo "  4. Remova php-check.php e install.php do servidor"
echo ""
