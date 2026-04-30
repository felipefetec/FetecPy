# Tarefas em Andamento

Este arquivo é atualizado em tempo real durante o desenvolvimento.
Cada prompt do PROMPTS.md tem sua seção aqui com checklist.

---

## Fase 0 — Setup

### Prompt 0.1 — Setup do repositório ✅
- [x] Git inicializado na branch main, .gitignore, TAREFAS.md, DECISIONS.md
- [x] Commit inicial

---

## Fase 1 — Fundação ✅

### Prompt 1.1 — Estrutura inicial ✅
- [x] Diretórios: public/, src/, content/, exercises/, data/, migrations/, scripts/, tests/
- [x] composer.json, install.php, .htaccess

### Prompt 1.2 — Migrations e Database ✅
- [x] migrations/001_initial.sql: users, sessions, progress, user_badges, rate_limits + 6 índices
- [x] src/Database.php: singleton PDO, ERRMODE_EXCEPTION, FETCH_ASSOC, FK ON, WAL, suporte DB_PATH

### Prompt 1.3 — Roteamento ✅
- [x] src/Http/Request.php, JsonResponse.php, Router.php, ResponseException.php
- [x] public/api/index.php: front controller com tratamento global de exceções
- [x] GET /api/health → {status: ok, version: 0.1.0}
- [x] scripts/dev.sh + scripts/router.php: servidor local com roteamento correto

### Prompt 1.4 — Testes automatizados ✅
- [x] PHPUnit 12.5 + Vitest 3.x configurados
- [x] phpunit.xml, tests/bootstrap.php (banco :memory: + JsonResponse modo teste)
- [x] scripts/test.sh: 26 testes PHP + 3 JS passando

---

## Fase 2 — Autenticação ✅

### Prompt 2.1 — Auth service e controller ✅
- [x] AuthService: normalizarChave (iconv), cadastrarOuLogin, bcrypt PIN, tokens 64 chars, 30 dias
- [x] Rate limiting: tabela rate_limits, 5 tentativas/min por chave
- [x] AuthController: POST /api/auth/login (201 novo / 200 existente), POST /api/auth/logout
- [x] AuthMiddleware, AuthException

### Prompt 2.2 — Perfil do usuário ✅
- [x] UserController: GET /api/me com XP, streak, badges (sem pin_hash)

### Prompt 2.3 — Testes de auth ✅
- [x] Request::simular() para testes sem servidor HTTP
- [x] AuthServiceTest (10), AuthControllerTest (8), UserControllerTest (5) — 26 testes PHP

---

## Fase 3 — Frontend inicial ✅

### Prompt 3.1 — Tela de login ✅
- [x] api.js: BASE_URL via import.meta.url, token Bearer automático
- [x] auth.js: login/logout/verificarSessao
- [x] index.html: dark (slate-950), verde Python, Tailwind CDN + Alpine.js, mobile-first
- [x] Fix: autofill browser, classes Tailwind em linha única

### Prompt 3.2 — Dashboard ✅
- [x] ProgressController: GET /api/progress (8 módulos com status bloqueado/disponível/em_andamento/concluído)
- [x] progress.js, gamification.js (15 badges)
- [x] app.html: header XP/streak, grid de módulos, sidebar "Conquistas" desktop, drawer mobile, modal badge

---

## Fase 4 — Conteúdo e Pyodide ✅

### Prompt 4.1 — Página de módulo ✅
- [x] ModuleController: GET /api/modules, GET /api/modules/:id (sem solução/respostas)
- [x] content/01-algoritmos.md: módulo 1 completo (seções 1.1–1.6, mini-projeto, quiz)
- [x] markdown.js: markdown-it ESM, blocos :::dica/aviso/curiosidade/tente/reflexao, TOC
- [x] module.html: sidebar TOC, x-html, Prism.js, navegação Anterior/Próximo
- [x] Ajustes: fontes aumentadas (1.125rem base), espaçamento generoso, blocos com fundo colorido

### Prompt 4.2 — Pyodide e editor ✅
- [x] pyodide.js: singleton lazy, stdout/stderr via StringIO Python, stdin, overlay de progresso
- [x] editor.js: CodeMirror 6 via esm.sh, tema dark, Python, Tab=4 espaços, Ctrl+Enter
- [x] :::tente: slot .tente-editor-area com CodeMirror + Rodar + saída
- [x] :::reflexao (EXTRA): textarea livre + contador + gabarito base64 + "Marquei como feito"
- [x] Testado: print("Hello, FetecPy!") executando com saída correta

---

## Fase 5 — Exercícios

### Prompt 5.1 — Schema, exercícios e validadores ✅
- [x] exercises/SCHEMA.md
- [x] exercises/01/ (5 exercícios — pseudocódigo, validação por texto livre)
- [x] exercises/02/ (5 exercícios — Python, saida_exata)
- [x] ExerciseController: GET /api/modules/:id/exercises/:exId, POST .../submit
- [x] validator.js: validateOutput, validateFunction, validateAst, validateTextLivre, validar()
- [x] Modal de exercício no module.html (3 fases: reflexão → editor → conclusão)
- [x] Rotas registradas no index.php
- [x] 26 testes PHP + 3 JS passando

### Prompt 5.2 — Validadores B e C ✅
- [x] validateFunction reescrito: execução de args/retorno 100% Python-side via JSON (sem bugs JS↔Python)
- [x] validateAst melhorado: nao_deve_chamar adicionado, detecção de chamadas diretas e de método
- [x] exercises/05/ex01.json — saudar(nome): validação B (funcao)
- [x] exercises/05/ex02.json — contagem 1-5 sem while: validação A+C (hibrido saida_exata + ast)
- [x] exercises/05/ex03.json — fatorial(n) com for: validação B+C (hibrido funcao + ast)
- [x] 26 testes PHP + 3 JS passando

### Prompt 5.3 — Testes dos validadores e exercícios ✅
- [x] tests/Frontend/validator.test.js (29 testes: textLivre, Output, Function, Ast, dispatcher)
- [x] tests/Content/exercises.test.js (124 testes: schema 13 JSONs + soluções via python3)
- [x] scripts/test.sh: validação de schema JSON em bash antes do Vitest
- [x] 26 PHP + 156 JS = 182 testes passando

---

## Extras implementados na Fase 5

- [x] Timer visual de análise para texto_livre (barra de progresso 2,5s)
- [x] Confetes + serpentinas ao concluir exercício sem ajuda (canvas-confetti)
- [x] Medalha SVG no canto do card (círculo dourado + fitas azuis em V)
- [x] ModuleController injeta status_aluno por exercício (medalha persiste entre sessões)
- [x] _marcarConcluido() desacoplado de enviarSubmissao (medalha garante aparição)

---

## Fase 6 — Gamificação + Conteúdo ✅

### Bloco A — Gamificação (Prompts 6.1, 6.2, 6.3) ✅
- [x] ProgressService.php: XP, streak, multiplicadores 7d (1.2x) e 30d (1.5x)
- [x] BadgeChecker.php: 15 badges (streak_3/7/30, sniper, coruja, madrugador, persistente, velocista, criativo, pyodide_master + 5 originais)
- [x] QuizController.php: GET quiz + POST quiz/submit
- [x] ModuleController: marcarSecao + listarSecoes
- [x] gamification.js: toastXp, toastBadge, celebrarBadges, processarRespostaGamificacao
- [x] module.html: componente quiz, barra progresso de seções, marcar seção como lida
- [x] ProgressServiceTest.php + BadgeCheckerTest.php (48 testes novos)
- [x] ExerciseController refatorado para usar os dois serviços
- [x] 72 PHP + 395 JS = 467 testes passando

### Bloco B — Conteúdo módulos 2–8 ✅
- [x] content/02-python-basico.md + quiz 02.json (5 perguntas)
- [x] exercises/03/ (5) + content/03-estruturas-controle.md + quiz 03.json
- [x] exercises/04/ (5) + content/04-estruturas-dados.md + quiz 04.json
- [x] exercises/05/ completado (ex04, ex05) + content/05-funcoes.md + quiz 05.json
- [x] exercises/06/ (5) + content/06-poo.md + quiz 06.json
- [x] exercises/07/ (5) + content/07-arquivos-json.md + quiz 07.json
- [x] exercises/08/ (5) + content/08-aplicacoes-reais.md + quiz 08.json
- [x] Total: 43 exercícios + 7 quizzes (35 perguntas), todos testados

---

## ⚠️ Módulo 9 — Boas Práticas e Profissionalização (DESBLOQUEADO)

> Arquivos `PLANO_ESTUDOS_M9.md` e `PROMPTS_M9.md` já estão na raiz do projeto.

Pré-requisitos atendidos:
- [x] Prompts 1.1 a 7.x concluídos (conteúdo 1–8 completo)
- [x] `./scripts/test.sh` passando (467 testes)
- [x] Conteúdo dos módulos 1–8 completo
- [x] Sistema de gamificação funcionando (15 badges, XP, streak)
- [x] Validadores A, B, C funcionando
- [ ] Deploy de teste validado (próxima sessão)

Quando o deploy for validado, iniciar pelo `PROMPTS_M9.md` → Prompt 7.5.1.
