#!/usr/bin/env bash
# =============================================================
# Servidor de desenvolvimento do FetecPy
#
# O servidor embutido do PHP não lê .htaccess, então as rotas
# /api/* não chegam ao front controller automaticamente.
# Este script usa um router.php que encaminha as requisições
# corretamente, replicando o comportamento do Apache em produção.
#
# Uso:
#   ./scripts/dev.sh          → inicia na porta 8000
#   ./scripts/dev.sh 8080     → inicia na porta especificada
# =============================================================

cd "$(dirname "$0")/.."

PORTA=${1:-8000}

echo ""
echo "=============================="
echo "  FetecPy — Servidor Dev"
echo "=============================="
echo ""
echo "  URL: http://localhost:${PORTA}"
echo "  API: http://localhost:${PORTA}/api/health"
echo ""
echo "  Ctrl+C para encerrar"
echo "=============================="
echo ""

php -S "localhost:${PORTA}" -t public/ scripts/router.php
