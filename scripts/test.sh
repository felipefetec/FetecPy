#!/usr/bin/env bash
# =============================================================
# Script de testes do FetecPy
# Roda a suíte completa: backend (PHPUnit) + frontend (Vitest)
# Deve ser executado da raiz do projeto: ./scripts/test.sh
#
# Retorna código de saída 0 se todos passaram, 1 se algum falhou.
# Usado como gate obrigatório antes de qualquer push.
# =============================================================

set -euo pipefail

# Garante que o script roda sempre a partir da raiz do projeto,
# independente de onde for chamado
cd "$(dirname "$0")/.."

PASSOU=true

echo ""
echo "=============================="
echo "  FetecPy — Suíte de Testes"
echo "=============================="

# --------------------------------------------------------------
# Backend: PHPUnit
# --------------------------------------------------------------
echo ""
echo "[ PHP ] Rodando PHPUnit..."
echo "------------------------------"

if php vendor/bin/phpunit --testdox; then
    echo "[ PHP ] Todos os testes passaram."
else
    echo "[ PHP ] FALHOU — verifique os erros acima."
    PASSOU=false
fi

# --------------------------------------------------------------
# Schema dos exercícios JSON (validação rápida em bash)
# Garante que nenhum campo obrigatório está faltando antes de rodar
# os testes mais lentos do Vitest
# --------------------------------------------------------------
echo ""
echo "[ JSON] Validando schema dos exercícios..."
echo "------------------------------"

SCHEMA_OK=true
CAMPOS_OBRIGATORIOS=("id" "modulo" "ordem" "titulo" "dificuldade" "xp" "enunciado" "antes_de_codar" "validacao")

for json in exercises/**/*.json; do
    for campo in "${CAMPOS_OBRIGATORIOS[@]}"; do
        if ! grep -q "\"$campo\"" "$json"; then
            echo "  FALTANDO campo '$campo' em: $json"
            SCHEMA_OK=false
        fi
    done
done

if [ "$SCHEMA_OK" = true ]; then
    echo "[ JSON] Schema de todos os exercícios válido."
else
    echo "[ JSON] FALHOU — campos obrigatórios ausentes (ver acima)."
    PASSOU=false
fi

# --------------------------------------------------------------
# Frontend: Vitest (validators + exercises + sanity)
# --------------------------------------------------------------
echo ""
echo "[ JS  ] Rodando Vitest..."
echo "------------------------------"

if npm test --silent; then
    echo "[ JS  ] Todos os testes passaram."
else
    echo "[ JS  ] FALHOU — verifique os erros acima."
    PASSOU=false
fi

# --------------------------------------------------------------
# Resultado final
# --------------------------------------------------------------
echo ""
echo "=============================="
if [ "$PASSOU" = true ]; then
    echo "  RESULTADO: TODOS PASSARAM ✓"
    echo "=============================="
    exit 0
else
    echo "  RESULTADO: FALHA ✗"
    echo "=============================="
    exit 1
fi
