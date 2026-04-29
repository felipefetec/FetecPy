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
