# FetecPy

> **Python para iniciantes.** Aprenda algoritmos primeiro, Python depois — com execução no navegador, progresso salvo e sem cobranças.

---

## O que é

FetecPy é uma plataforma web de ensino de Python para iniciantes absolutos. O foco é **raciocínio antes de sintaxe**: o aluno aprende a decompor problemas, pensar em passos, e só então escreve código.

**Características:**
- 🧠 Algoritmos primeiro (módulo 1 sem código nenhum)
- 🐍 Python progressivo (módulos 2–8 cobrem fundamentos até frameworks)
- 💻 Execução no navegador (Pyodide — sem servidor de código)
- 💾 Progresso salvo (XP, streak, badges, medalhas por exercício)
- 🎮 Gamificação leve (15 badges, multiplicadores de streak)
- 📱 Mobile-first com modal fullscreen e botão TAB
- 🌙 Modo escuro
- ⚡ Service worker (Pyodide em cache após primeiro uso)

---

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8 + SQLite (PDO) |
| Frontend | HTML + Tailwind CDN + Alpine.js |
| Editor | CodeMirror 6 (via esm.sh) |
| Python no browser | Pyodide v0.26.4 (WebAssembly) |
| Testes backend | PHPUnit 12 |
| Testes frontend | Vitest 3 |

Stack escolhida para rodar em **hospedagem PHP compartilhada** — sem Node, sem Docker, sem banco externo.

---

## Pré-requisitos

- PHP 8.0+
- Extensões: `pdo_sqlite`, `mbstring`, `json`
- Composer
- Node.js + npm (apenas para testes)

---

## Rodar localmente

```bash
git clone https://github.com/felipefetec/FetecPy.git
cd FetecPy
composer install
php install.php
./scripts/dev.sh
```

Acesse `http://localhost:8000`.

Para rodar os testes:

```bash
./scripts/test.sh
```

---

## Estrutura do projeto

```
FetecPy/
├── public/          # Webroot (DocumentRoot aponta aqui)
│   ├── index.html   # Login
│   ├── app.html     # Dashboard
│   ├── module.html  # Página de módulo + exercícios
│   ├── 404.html     # Página de erro customizada
│   ├── sw.js        # Service worker (cache Pyodide + assets)
│   ├── api/         # Front controller PHP
│   └── assets/js/   # JavaScript do frontend
├── src/             # Código PHP (controllers, services)
├── content/         # Markdown dos módulos (01–08)
│   └── quiz/        # JSONs dos quizzes por módulo
├── exercises/       # JSONs dos exercícios por módulo
├── data/            # SQLite (gitignored, permissão de escrita)
├── migrations/      # SQL de setup do banco
├── tests/           # PHPUnit + Vitest
├── scripts/         # dev.sh, test.sh, deploy.sh
└── install.php      # Setup inicial (rodar uma vez)
```

---

## Como adicionar conteúdo

### Novo módulo (Markdown)

Criar `content/XX-nome.md` com front-matter:

```yaml
---
modulo: "09"
titulo: "Boas Práticas"
duracao_estimada: "4h"
pre_requisito: "08"
---
```

Blocos especiais suportados:

```markdown
:::dica
Texto da dica
:::

:::aviso
Texto do aviso
:::

:::tente
print("Hello!")
:::
```

### Quiz do módulo

Criar `content/quiz/XX.json`:

```json
{
  "modulo": "09",
  "titulo": "Quiz do módulo 9",
  "perguntas": [
    {
      "id": "q1",
      "pergunta": "O que é um commit?",
      "opcoes": ["A", "B", "C", "D"],
      "resposta_correta": 0,
      "explicacao": "Explicação da resposta."
    }
  ]
}
```

### Novo exercício

Criar `exercises/XX/exYY.json` seguindo o schema em `exercises/SCHEMA.md`.

Tipos de validação:
- `texto_livre` — pseudocódigo/texto (sem Python)
- `saida_exata` — compara stdout com esperado
- `funcao` — testa retorno de função específica
- `ast` — verifica estrutura do código (ex: deve usar `for`)
- `hibrido` — combina múltiplos validadores

Após criar, rodar `./scripts/test.sh` para validar o schema e a solução.

---

## Deploy em hospedagem compartilhada

### Passo a passo

**1. Criar `.env.deploy`** (na raiz, não vai para o git):

```bash
FTP_HOST=ftp.seudominio.com
FTP_USER=usuario@seudominio.com
FTP_PASS=senha_aqui
FTP_DIR=/public_html/fetecpy
```

**2. Rodar o script:**

```bash
./scripts/deploy.sh
```

O script: otimiza o autoloader, roda os testes, envia por FTP, restaura o ambiente de dev.

**3. Primeira vez no servidor:**

```
https://seudominio.com/php-check.php   → verifica requisitos
https://seudominio.com/install.php     → cria tabelas no banco
```

**4. Remover após configurar:**

```bash
# No painel FTP ou SSH:
rm php-check.php install.php
```

### Apontar DocumentRoot para public/

No cPanel: **Domínios → Subdomínios → Document Root** → `public/`.

Se o host não permitir, o `.htaccess` raiz já redireciona tudo para `public/` automaticamente.

### Permissões necessárias

| Diretório | Permissão |
|---|---|
| `data/` | `755` ou `775` (escrita do PHP) |
| `data/fetecpy.db` | criado automaticamente pelo `install.php` |
| Demais | `644` (leitura) |

### HTTPS

Descomentar no `public/.htaccess`:

```apache
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]
```

---

## Backup

```bash
# Manual
cp data/fetecpy.db data/backup-$(date +%Y%m%d).db

# Cron diário (adicionar ao crontab)
0 3 * * * cp /caminho/data/fetecpy.db /caminho/data/backup-$(date +\%Y\%m\%d).db
```

---

## Ativar o pre-commit hook (bloqueia commit se testes falharem)

```bash
git config core.hooksPath .githooks
```

---

## Troubleshooting

| Problema | Solução |
|---|---|
| Página em branco / 500 | Verificar `data/error.log`, permissões de `data/` |
| "Token inválido" após deploy | Banco não foi criado — rodar `install.php` |
| Pyodide não carrega | Verificar conexão (carrega do jsDelivr na primeira vez) |
| `pdo_sqlite not found` | Instalar: `apt install php8.x-sqlite3` ou pedir ao host |
| CodeMirror em branco | Verificar console do browser — pode ser CSP bloqueando esm.sh |
| Exercício não salva | Abrir DevTools → Console — erros aparecem como `[FetecPy] ...` |

---

## Licença

Uso livre para estudos e projetos pessoais. Uso comercial não permitido.

---

## Créditos

Projeto de **Felipe Tavares**.
