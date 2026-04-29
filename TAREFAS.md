# Tarefas em Andamento

Este arquivo é atualizado em tempo real durante o desenvolvimento.
Cada prompt do PROMPTS.md tem sua seção aqui com checklist.

---

## Prompt 0.1 — Setup do repositório
- [x] Inicializar git
- [x] Criar `.gitignore`
- [x] Criar `TAREFAS.md`
- [x] Criar `DECISIONS.md`
- [x] Commit inicial

---

## Prompt 1.1 — Estrutura inicial e setup
- [x] Criar estrutura de diretórios completa (public/, src/, content/, exercises/, data/, migrations/)
- [x] Criar `.gitignore` (já feito no Prompt 0.1)
- [x] Criar `composer.json` mínimo
- [x] Criar `install.php` (cria data/, executa migrations, ajusta permissões)
- [x] Testar `install.php` — PHP 8.3 instalado, install.php executado com sucesso
- [x] Commit

---

## Prompt 1.2 — Migrations e modelo de dados
- [x] Criar migrations/001_initial.sql com tabelas users, progress, user_badges, sessions, rate_limits e índices
- [x] Criar src/Database.php — singleton PDO com configurações corretas
- [x] Testar via install.php — 5 tabelas + 6 índices criados com sucesso
- [x] Commit

---

## Prompt 1.3 — Sistema de roteamento básico
- [x] Criar src/Http/Request.php
- [x] Criar src/Http/JsonResponse.php
- [x] Criar src/Http/Router.php
- [x] Atualizar public/api/index.php como front controller
- [x] Endpoint GET /api/health
- [x] Testar com curl — health 200, rota inexistente 404, método errado 405
- [x] Commit

---

## Prompt 4.1 — Renderização de conteúdo dos módulos
- [x] ModuleController: GET /api/modules (lista front-matter), GET /api/modules/:id (conteúdo + exercícios + quiz sem respostas)
- [x] content/01-algoritmos.md: introdução + seções 1.1-1.6 + mini-projeto + quiz
- [x] markdown.js: markdown-it ESM + pré-processador de blocos :::tipo + extrairTOC + adicionarAncorasTitulos
- [x] module.html: sidebar TOC sticky, x-html para conteúdo, navegação Anterior/Próximo, drawer mobile
- [x] Corrigido: html:true no markdown-it (blocos customizados), x-html em vez de getElementById (timing Alpine)
- [x] Testado: todos os blocos (aviso, tente, dica, curiosidade) renderizando, código com fonte mono, tabela estilizada
- [x] Commit

---

## Prompt 3.2 — Dashboard
- [x] ProgressController: GET /api/progress com status bloqueado/disponivel/em_andamento/concluido
- [x] progress.js: carrega progresso e encontra último módulo em andamento
- [x] gamification.js: catálogo de 15 badges, mesclarBadges(), formatarXp()
- [x] app.html: header sticky com XP/streak, card "continuar", grid de módulos com barra de progresso, sidebar de badges desktop, drawer mobile, modal de badge
- [x] Testado: API retorna 8 módulos corretamente, testes não-regressão passando
- [x] Commit

---

## Prompt 3.1 — Tela de login
- [x] api.js: wrapper fetch com base URL automática via import.meta.url + token Bearer
- [x] auth.js: login (salva token), logout, verificarSessao (redireciona se logado)
- [x] index.html: dark slate + verde Python, Tailwind CDN + Alpine.js, mobile-first, animação surgir
- [x] scripts/dev.sh + scripts/router.php: servidor local com API funcionando
- [x] Testado: HTML servido, API respondendo via router, testes passando
- [x] Commit

---

## Prompt 2.3 — Testes da camada de autenticação
- [x] Request::simular() adicionado (fábrica para testes sem servidor HTTP)
- [x] AuthServiceTest.php — 10 testes (normalização, cadastro, PIN, token, logout)
- [x] AuthControllerTest.php — 8 testes (login 201/200/401/422, rate limit 429, logout 401/200)
- [x] UserControllerTest.php — 5 testes (perfil, 401 sem token, 401 token inválido, sem pin_hash, usuário correto)
- [x] ./scripts/test.sh — 26 testes PHP + 3 JS, todos passando
- [x] Commit

---

## Prompt 2.2 — Endpoint de perfil
- [x] Criar src/Controllers/UserController.php (GET /api/me)
- [x] Registrar rota no index.php
- [x] Testado: autenticado 200 (sem pin_hash), sem token 401, token inválido 401
- [x] Commit

---

## Prompt 2.1 — Sistema de autenticação
- [x] Criar src/Services/AuthService.php (normalizarChave, cadastrarOuLogin, criarSessao, validarToken, logout, rate limiting)
- [x] Criar src/Controllers/AuthController.php (POST /api/auth/login, POST /api/auth/logout)
- [x] Criar src/Http/AuthMiddleware.php
- [x] Criar src/Exceptions/AuthException.php
- [x] Adicionar $user ao Request e registrar rotas no index.php
- [x] Testar com curl — cadastro 201, login 200, PIN errado 401, sem campo 422, logout 200, sem token 401
- [x] Testes anteriores passando (não-regressão)
- [x] Commit

---

## Prompt 1.4 — Setup de testes automatizados
- [x] Adicionar phpunit/phpunit ao composer.json (require-dev) e instalar
- [x] Criar phpunit.xml
- [x] Criar tests/bootstrap.php (banco em memória + modo teste JsonResponse)
- [x] Criar tests/Backend/HealthCheckTest.php (2 testes passando)
- [x] Criar package.json com vitest + npm install
- [x] Criar vitest.config.js
- [x] Criar tests/Frontend/sanity.test.js (3 testes passando)
- [x] Criar scripts/test.sh e tornar executável
- [x] ./scripts/test.sh — 5 testes passando (2 PHP + 3 JS)
- [x] Commit
