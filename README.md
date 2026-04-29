# FetecPy

> **Python para iniciantes.** Aprenda algoritmos primeiro, Python depois — com execução no navegador, progresso salvo e sem cobranças.

---

## O que é

FetecPy é uma plataforma web de ensino de Python para iniciantes absolutos. O foco é **raciocínio antes de sintaxe**: o aluno aprende a decompor problemas, pensar em passos, e só então escreve código.

**Características:**
- 🧠 Algoritmos primeiro (módulo 1 sem código nenhum)
- 🐍 Python progressivo (módulos 2-7 cobrem fundamentos)
- 🚀 Frameworks modernos (módulo 8: requests, Streamlit, Flask, Flet, FastAPI)
- 💻 Execução no navegador (Pyodide — sem servidor de código)
- 💾 Progresso salvo (qualquer navegador, qualquer dispositivo)
- 🎮 Gamificação leve (XP, streak, 15 badges)
- 📱 Mobile-first
- 🌙 Modo escuro

---

## Para quem é

- **Iniciantes absolutos** que nunca programaram
- **Estudantes** que querem complementar aulas teóricas com prática
- **Professores** que querem indicar uma plataforma simples pra alunos

---

## Stack

- **Backend:** PHP 8 + SQLite (PDO)
- **Frontend:** HTML + Tailwind + Alpine.js
- **Editor:** CodeMirror 6
- **Python no browser:** Pyodide (CPython em WebAssembly)

Stack escolhida pra rodar em **hospedagem PHP compartilhada** (sem Node, sem Docker, sem painel admin sofisticado).

---

## Documentos importantes

- [`PLANO_ESTUDOS.md`](./PLANO_ESTUDOS.md) — currículo completo, estrutura de módulos, exercícios

---

## Como rodar localmente

**Pré-requisitos:**
- PHP 8.0+
- Extensões: `pdo_sqlite`, `mbstring`, `json`

**Setup:**

```bash
git clone <url-do-repo>
cd fetecpy
php install.php
php -S localhost:8000 -t public/
```

Abra `http://localhost:8000` no navegador.

---

## Como adicionar conteúdo

### Adicionar um módulo

1. Criar `content/XX-nome-do-modulo.md` com front-matter:
   ```yaml
   ---
   modulo: "09"
   titulo: "Título do Módulo"
   duracao_estimada: "5h"
   pre_requisito: "08"
   ---
   ```
2. Escrever conteúdo em Markdown (suporta blocos `:::dica`, `:::aviso`, `:::tente`)

### Adicionar um exercício

Criar `exercises/XX/exYY.json` seguindo o schema em `exercises/SCHEMA.md`.

---

## Deploy

### Opção 1: FTP em host compartilhado
1. Subir todo o conteúdo do projeto para o host
2. Apontar o `DocumentRoot` para `public/` (ou usar `.htaccess` se não der)
3. Acessar `instalar.php` uma vez para criar tabelas
4. Remover `instalar.php` por segurança

### Opção 2: VPS com nginx
Veja [`docs/deploy.md`](./docs/deploy.md) (se existir).

---

## Backup

```bash
cp data/fetecpy.db data/backup-$(date +%Y%m%d).db
```

Cron diário recomendado.

---

## Princípios

1. **Pense antes de codar** — sempre
2. **Algoritmos primeiro, sintaxe depois**
3. **Erro é parte do aprendizado** — sistema não pune
4. **Cada aluno no seu ritmo** — sem prazos, sem turmas
5. **Privacidade respeitada** — só nome/sobrenome/PIN, sem email, sem tracking invasivo
6. **Simplicidade** — se cabe num arquivo PHP, não vira microsserviço

---

## Licença

A definir.

---

## Créditos

Projeto pessoal de **Felipe Tavares** — [kelvinglab.com](https://kelvinglab.com)
