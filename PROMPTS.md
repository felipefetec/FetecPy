# FetecPy — Sequência de Prompts para Claude Code

> Este arquivo lista a sequência recomendada de prompts para construir o FetecPy no Claude Code. Cada prompt é um passo isolado, com escopo bem definido. Execute na ordem.

**Antes de começar:** garanta que `PLANO_ESTUDOS.md`, `CLAUDE.md` e este arquivo estão na raiz do projeto. O Claude Code deve consultá-los ao executar cada prompt.

**Workflow de cada prompt** (ver detalhes em `CLAUDE.md` seção "Workflow de Desenvolvimento"):
1. Leia `CLAUDE.md` e `PLANO_ESTUDOS.md` antes de começar
2. Extraia a checklist do prompt para `TAREFAS.md`
3. Execute as tarefas marcando progresso em tempo real
4. Rode os testes existentes
5. Commit em português com mensagem clara
6. **Não fazer push** — só ao final da sessão quando o usuário pedir
7. Avise quando o prompt estiver completo

---

## Fase 0 — Setup do Repositório

### Prompt 0.1 — Inicializar git e arquivos de controle
```
Leia CLAUDE.md (atenção especial à seção "Workflow de Desenvolvimento") e PLANO_ESTUDOS.md.

Faça setup inicial do repositório:

1. Inicializar git (se ainda não foi)
2. Criar .gitignore com:
   - data/*.db
   - data/*.log
   - vendor/
   - node_modules/
   - .env
   - .DS_Store
   - *.swp
   - .idea/
   - .vscode/

3. Criar TAREFAS.md vazio com cabeçalho:
   ```
   # Tarefas em Andamento
   
   Este arquivo é atualizado em tempo real durante o desenvolvimento.
   Cada prompt do PROMPTS.md tem sua seção aqui com checklist.
   ```

4. Criar DECISIONS.md vazio com cabeçalho:
   ```
   # Registro de Decisões Arquiteturais
   
   Decisões tomadas durante o desenvolvimento que afetam o rumo do projeto.
   Formato: data — título — contexto — decisão — consequências.
   ```

5. Verificar se há remote configurado. Se não, perguntar ao usuário a URL do repositório no GitHub e configurar:
   git remote add origin <url>

6. Fazer commit inicial:
   git add .
   git commit -m "chore: setup inicial do repositório FetecPy"

Não fazer push ainda. Confirmar conclusão e listar próximo prompt sugerido (1.1).

Lembrete: todas as mensagens no terminal devem estar em português.
```

---

## Fase 1 — Fundação

### Prompt 1.1 — Estrutura inicial e setup
```
Leia PLANO_ESTUDOS.md e CLAUDE.md.

Crie a estrutura de pastas do projeto conforme especificado em CLAUDE.md.
Inclua:
- Estrutura de diretórios completa (public/, src/, content/, exercises/, data/, migrations/)
- .gitignore (ignorando data/*.db, data/error.log, vendor/, node_modules/)
- README.md inicial com instruções de instalação local
- composer.json mínimo (não precisamos de dependências externas ainda, mas deixe pronto pra adicionar)
- install.php que: cria a pasta data/ se não existir, executa migrations/001_initial.sql, define permissões

Não crie ainda código de aplicação. Só estrutura.
```

### Prompt 1.2 — Migrations e modelo de dados
```
Crie migrations/001_initial.sql com todas as tabelas definidas em PLANO_ESTUDOS.md (users, progress, user_badges, sessions).

Inclua índices apropriados (chave de usuário, user_id em progress, etc).

Crie src/Database.php — classe singleton que retorna PDO conectado ao SQLite com:
- PDO::ATTR_ERRMODE = ERRMODE_EXCEPTION
- PDO::ATTR_DEFAULT_FETCH_MODE = FETCH_ASSOC
- foreign_keys = ON

Adicione método estático para executar migrations.

Teste rodando install.php e verificando que as tabelas foram criadas (com sqlite3 CLI ou um script PHP de verificação).
```

### Prompt 1.3 — Sistema de roteamento básico
```
Crie public/api/index.php como front controller. Implemente roteamento manual simples:

- Detecte método HTTP e path
- Path pattern: /api/{recurso}/{id?}
- Carregue o controller correspondente em src/Controllers/
- Retorne sempre JSON com helper JsonResponse

Crie:
- src/Http/Request.php (parser do request body, query, headers)
- src/Http/JsonResponse.php (envia JSON com status code)
- src/Http/Router.php (mapeamento básico)

Configure .htaccess em public/api/ para reescrever URLs para index.php.

Adicione um endpoint de teste GET /api/health que retorna {"status": "ok", "version": "0.1.0"}.

Teste com curl.
```

### Prompt 1.4 — Setup de testes automatizados
```
Configure a infraestrutura de testes do projeto.

Backend (PHPUnit):
1. Adicionar phpunit/phpunit ao composer.json (require-dev)
2. Rodar composer install (ou composer update)
3. Criar phpunit.xml na raiz com configuração:
   - bootstrap: tests/bootstrap.php
   - testsuites: Backend, Content
   - coverage: src/
4. Criar tests/bootstrap.php que carrega autoload + configura ambiente de teste (banco SQLite em memória)
5. Criar tests/Backend/HealthCheckTest.php com 1 teste sanity (verifica que GET /api/health retorna 200)

Frontend (Vitest):
1. Criar package.json mínimo com vitest como devDependency
2. Rodar npm install (ou yarn)
3. Criar vitest.config.js
4. Criar tests/Frontend/sanity.test.js com 1 teste sanity (1+1 === 2)

Scripts:
1. Criar scripts/test.sh que roda:
   - composer test (PHP)
   - npm test (JS)
   - Sai com erro se qualquer um falhar
2. Tornar executável: chmod +x scripts/test.sh

Adicionar ao composer.json:
"scripts": {
  "test": "phpunit",
  "test:coverage": "phpunit --coverage-html coverage/"
}

Verificar que ./scripts/test.sh roda sem erro.

Atualizar CLAUDE.md se necessário com qualquer ajuste no caminho de testes.

Commit: "test: configura infraestrutura de testes (PHPUnit + Vitest) - Prompt 1.4"
```

---

## Fase 2 — Autenticação

### Prompt 2.1 — Cadastro e login
```
Implemente o sistema de autenticação conforme CLAUDE.md.

Crie:
- src/Services/AuthService.php
  - normalizarChave($nome, $sobrenome) → "felipe_silva" (lowercase, sem acento, espaços viram _)
  - cadastrarOuLogin($nome, $sobrenome, $pin) → cria se não existe, valida se existe
  - validarPin($chave, $pin) → bool
  - criarSessao($userId) → token
  - validarToken($token) → user ou null
  - logout($token)

- src/Controllers/AuthController.php
  - POST /api/auth/login
  - POST /api/auth/logout

Use password_hash com PASSWORD_BCRYPT.
Tokens: bin2hex(random_bytes(32)).
Sessões expiram em 30 dias.

Implemente middleware src/Http/AuthMiddleware.php que valida o header Authorization: Bearer <token> e popula $request->user.

Implemente rate limiting básico para POST /api/auth/login (5 tentativas erradas por minuto por chave de usuário). Use uma tabela rate_limits ou contador em memória/arquivo.

Teste com curl: cadastrar, logar, validar token.
```

### Prompt 2.2 — Endpoint de perfil
```
Implemente:
- GET /api/me — retorna dados do usuário logado (sem pin_hash)
- src/Controllers/UserController.php

Inclua: id, nome, sobrenome, xp_total, streak_dias, ultimo_acesso, badges desbloqueadas (lista).

Proteja com AuthMiddleware.

Teste.
```

### Prompt 2.3 — Testes da camada de autenticação
```
Crie suíte de testes para tudo que foi implementado na Fase 2.

Em tests/Backend/:

1. AuthServiceTest.php
   - testNormalizarChaveRemoveAcentos
   - testNormalizarChaveTrocaEspacosPorUnderline
   - testNormalizarChaveLowercase
   - testCadastraNovoUsuario
   - testValidaPinCorreto
   - testRejeitaPinIncorreto
   - testRejeitaUsuarioInexistente
   - testCriaSessaoComToken
   - testValidaToken
   - testTokenExpiradoEhInvalido

2. AuthControllerTest.php
   - testLoginRetornaToken
   - testLoginInvalidoRetorna401
   - testRateLimitBloqueiaApos5Tentativas
   - testLogoutInvalidaToken

3. UserControllerTest.php
   - testMeRetornaUsuarioLogado
   - testMeSemTokenRetorna401
   - testMeNaoExpoeHashPin

Use SQLite em memória nos testes (bootstrap.php).
Cobertura mínima: 80% em AuthService.

Rodar ./scripts/test.sh — todos devem passar.

Commit: "test: testes da camada de autenticação (Prompt 2.3)"
```

---

## Fase 3 — Frontend Inicial

### Prompt 3.1 — Tela de login
```
Crie public/index.html — tela de login do FetecPy.

Requisitos:
- Tailwind CSS via CDN
- Alpine.js via CDN
- Formulário com 3 campos: nome, sobrenome, PIN (4 dígitos numéricos)
- Validação client-side: campos não vazios, PIN só números
- Aviso claro: "Não há recuperação de PIN. Anote em local seguro."
- Mobile-first
- Modo escuro por padrão (paleta dark slate + accent verde Python)
- Logo "FetecPy" + tagline "Python para iniciantes"
- Animação suave de entrada

JS:
- public/assets/js/api.js — wrapper fetch com Authorization header automático
- public/assets/js/auth.js — chama /api/auth/login, salva token em localStorage
- Em caso de sucesso, redireciona pra /app.html

Visual: limpo, minimalista, profissional. Não infantil. Inspiração: Linear, Vercel.
```

### Prompt 3.2 — Dashboard
```
Crie public/app.html — dashboard principal.

Estrutura:
- Header: nome do aluno, XP total, streak (com ícone de fogo), botão sair
- Seção "Continuar de onde parou" (se houver progresso): card com último módulo, link "Continuar"
- Grid de módulos: 9 cards (1 a 8 + 8 dividido em 5 submódulos = mostrar 8 cards iniciais, expandir o último)
- Cada card de módulo:
  - Número, título
  - Barra de progresso (% concluído)
  - Status: bloqueado (precisa terminar anterior), em andamento, concluído
  - Click → vai pra /module.html?id=XX
- Sidebar (ou drawer no mobile): lista de badges conquistadas + bloqueadas (visíveis com cadeado)

JS:
- progress.js — carrega /api/progress, calcula % por módulo, decide bloqueado/disponível
- gamification.js — renderiza badges

Use Alpine.js para reatividade (estado dos módulos, modal de badge ao clicar).
```

---

## Fase 4 — Conteúdo e Pyodide

### Prompt 4.1 — Renderização de conteúdo dos módulos
```
Crie public/module.html — página de um módulo.

Layout:
- Sidebar esquerda: índice do módulo (seções clicáveis), barra de progresso
- Área principal: conteúdo Markdown renderizado
- Botões "Anterior" / "Próximo" no final

JS:
- markdown.js — usa markdown-it (CDN) + custom plugin para blocos especiais (:::dica, :::aviso, :::curiosidade, :::tente)
- syntax highlighting com Prism.js (Python)
- Carrega conteúdo via /api/modules/:id (a ser implementado abaixo)

PHP:
- src/Controllers/ModuleController.php
  - GET /api/modules — lista módulos (lê pasta content/, parse front-matter)
  - GET /api/modules/:id — retorna conteúdo Markdown + lista de exercícios + quiz

Schema do módulo retornado:
{
  "id": "01",
  "titulo": "Lógica e Algoritmos",
  "conteudo_md": "...",
  "exercicios": [{ id, titulo, dificuldade, status }],
  "quiz": [{ id, pergunta, opcoes, resposta_correta }]
}

Cuide para que perguntas do quiz não vazem a resposta correta para o frontend até o aluno responder. Backend valida.

Crie content/01-algoritmos.md com pelo menos a introdução e seção 1.1, conforme PLANO_ESTUDOS.md.
```

### Prompt 4.2 — Pyodide e editor
```
Implemente o editor de código com Pyodide.

Crie:
- public/assets/js/pyodide.js
  - Carrega Pyodide do CDN jsDelivr (https://cdn.jsdelivr.net/pyodide/v0.26.0/full/pyodide.js)
  - Mostra barra de progresso enquanto carrega
  - Cacheia em service worker (se possível, ou pelo menos via cache de browser)
  - Função runPython(codigo, stdin) → { stdout, stderr, error }
  - Captura stdout/stderr corretamente
  - Suporta input simulado via stdin

- public/assets/js/editor.js
  - CodeMirror 6 setup com tema dark
  - Modo Python
  - Auto-indent, line numbers, syntax highlighting
  - Função createEditor(elementId, codigo_inicial) → { getValue, setValue }

Crie um componente "tente este código" que aparece no Markdown via :::tente:::.
Quando o aluno clica "Rodar", abre um editor pequeno com Pyodide e console abaixo.

Teste: rode print("hello") via interface.
```

---

## Fase 5 — Exercícios

### Prompt 5.1 — Schema e validador A (saída exata)
```
Crie exercises/SCHEMA.md documentando o formato JSON de exercícios.

Crie exercises/01/ex01.json a ex05.json conforme PLANO_ESTUDOS.md módulo 1.
Atenção: módulo 1 é teórico (sem código), os exercícios pedem pseudocódigo. Valide via campo de texto livre, sem teste automático. Marque como "concluído" quando aluno escrever resposta com mínimo de caracteres.

Crie exercises/02/ex01.json a ex05.json (Python básico).
Use validação tipo "saida_exata" com input simulado.

Crie:
- src/Controllers/ExerciseController.php
  - GET /api/modules/:moduloId/exercises/:exId — retorna o exercício (sem solução)
  - POST /api/modules/:moduloId/exercises/:exId/submit — recebe { codigo, status, tentativas }, atualiza progresso, calcula XP, retorna { xp_ganho, novas_badges, total_xp }

- public/assets/js/validator.js — implementa os 3 validadores no frontend
  - validateOutput(codigo, casos) — usa Pyodide, captura stdout, compara
  - validateFunction(codigo, nome, casos) — define a função, executa cada caso
  - validateAst(codigo, regras) — parseia com ast no próprio Pyodide

Crie a página de exercício dentro de module.html ou em modal:
- Enunciado
- Seção "Antes de codar" — campos de texto opcionais (módulos 1-5: obrigatório responder antes de mostrar editor)
- Editor com codigo_inicial
- Botão "Rodar" — roda código
- Botão "Testar" — valida com casos
- Botão "Ver dica" — mostra dica
- Após 3 tentativas inválidas: aparece botão "Ver solução"
- Após 5 tentativas: insistência maior em ver solução
- Solução marca como "concluido_com_ajuda" (50% XP)

Salva código do aluno automaticamente após cada tentativa.
```

### Prompt 5.2 — Validadores B (função) e C (AST)
```
Complete os validadores B e C em validator.js.

Implementação em PYTHON dentro do Pyodide para validar:
- Validador B: aluno define função, sistema chama via pyodide.globals.get(nome_funcao)(*args)
- Validador C: aluno escreve código, sistema usa import ast; tree = ast.parse(codigo); checa se contém certos nós

Crie 3 exercícios de teste:
- Um exercício com validação B (ex: implementar eh_par)
- Um exercício com validação C (ex: "use for, não while")
- Um exercício combinado B+C

Adicione esses exercícios ao módulo 5.
```

### Prompt 5.3 — Testes dos validadores e exercícios
```
Crie suíte de testes para os validadores de exercício.

Em tests/Frontend/validator.test.js:

1. validateOutput
   - testSaidaIgual
   - testSaidaComEspacosTrimmed
   - testSaidaDiferente
   - testInputSimuladoFunciona
   - testCapturaErroPython

2. validateFunction
   - testFuncaoCorreta
   - testFuncaoIncorreta
   - testFuncaoNaoDefinida
   - testFuncaoComExcecao
   - testMultiplosCasos

3. validateAst
   - testDeveConterFor
   - testNaoDeveConterWhile
   - testDeveChamarRange
   - testCombinacaoDeRegras

Use Pyodide em ambiente Node (via @pyodide/pyodide) ou mock se for muito pesado.

Em tests/Content/exercises.test.js:
- Para cada exercício existente: rodar a "solucao" do JSON contra os "casos" de validação e garantir que a solução passa.
- Isso é validação automática do conteúdo: garante que o gabarito está correto.

Adicionar ao scripts/test.sh:
- Validação de schema dos exercícios: cada JSON em exercises/ deve ter campos obrigatórios

Commit: "test: validadores e validação automática de exercícios (Prompt 5.3)"
```

---

## Fase 6 — Gamificação

### Prompt 6.1 — XP e streak
```
Implemente o sistema completo de XP e streak no backend.

Em src/Services/ProgressService.php:
- registrarConclusao($userId, $moduloId, $itemTipo, $itemId, $status, $tentativas) — atualiza progress, calcula XP, atualiza streak

XP por exercício:
- Calcular baseado em metadados do exercício (campo "xp")
- Se status é "concluido_com_ajuda": 50% do XP
- Aplicar multiplicador de streak: 7+ dias = 1.2x, 30+ dias = 1.5x

Streak:
- Se ultimo_acesso = hoje: nada
- Se ultimo_acesso = ontem: streak += 1
- Se mais antigo: streak = 1
- Atualizar ultimo_acesso = hoje

Retornar { xp_ganho, total_xp, streak, novas_badges }.

Frontend:
- Toast notification quando XP é ganho ("+20 XP")
- Toast especial quando badge é conquistada
- Atualizar barra de XP no header do dashboard
- Atualizar contador de streak
```

### Prompt 6.2 — Badges
```
Implemente o sistema de badges conforme PLANO_ESTUDOS.md (15 badges).

Crie:
- src/Services/BadgeChecker.php
  - checkAll($userId) → array de badges recém-conquistadas
  - Cada badge é uma classe ou closure com método isUnlocked($userId)
  - Insere em user_badges se desbloqueada e ainda não tinha

Implemente cada badge:
- primeiro_codigo — primeiro exercício com print rodando (verifica codigo_salvo contém "print")
- streak_3, streak_7, streak_30 — checa streak_dias
- sniper — 5 quizzes seguidos sem errar (precisa rastrear histórico de quiz)
- estudante_dedicado — count(progress WHERE status="concluido" AND item_tipo="exercicio") >= 10
- mestre_modulo — primeiro módulo 100% concluído
- formado — todos os módulos concluídos
- pyodide_master — heurística (deixe a fazer, pode ser manual)
- cacador_bugs — exercício com tentativas >= 3 e status="concluido"
- velocista — diferença entre criação e conclusão < 60s (precisa rastrear)
- coruja — concluido_em entre 23h e 5h
- madrugador — concluido_em entre 5h e 7h
- persistente — voltou após 7+ dias sem registrar progresso
- criativo — manual (validação por AST especial)

Chamar checkAll após cada update de progresso.

Frontend:
- Modal celebratório quando badge é desbloqueada
- Página de badges (em app.html ou rota separada) mostrando todas, com cadeado nas bloqueadas
- Tooltip explicando como conquistar
```

### Prompt 6.3 — Testes do sistema de gamificação
```
Crie suíte de testes para XP, streak e badges.

Em tests/Backend/:

1. ProgressServiceTest.php
   - testRegistraConclusaoComSucesso
   - testCalculaXpFacilMedioDificil
   - testXpReduzidoQuandoConcluidoComAjuda
   - testStreakIncrementaQuandoOntem
   - testStreakReseteQuandoMaisDeUmDia
   - testStreakNaoMudaNoMesmoDia
   - testMultiplicadorXpPara7Dias
   - testMultiplicadorXpPara30Dias

2. BadgeCheckerTest.php (uma seção por badge)
   - testPrimeiroCodigoDesbloqueiaCornPrintCorrecto
   - testStreak3Desbloqueia
   - testStreak7Desbloqueia
   - testEstudanteDedicado10Exercicios
   - testCacadorBugsCom3Tentativas
   - testCorujaPosMeiaNoite
   - testMadrugadorAntes7h
   - testFormadoTodosModulos
   - testPersistenteApos7Dias
   - ... (uma para cada badge)

Mock de tempo: usar Carbon ou similar para testar coruja/madrugador sem depender da hora real.

Cobertura: 100% nos métodos públicos de ProgressService e BadgeChecker.

Commit: "test: gamificação (XP, streak, badges) (Prompt 6.3)"
```

---

## Fase 7 — Conteúdo dos Módulos

### Prompt 7.1 — Módulos 1 a 3 completos
```
Escreva o conteúdo Markdown completo dos módulos 1, 2 e 3 conforme PLANO_ESTUDOS.md.

Para cada módulo:
- Tom direto, casual mas correto
- Exemplos do dia a dia brasileiro (não traduzir exemplos genéricos americanos)
- Use blocos especiais (:::dica, :::aviso, :::tente)
- Inclua pelo menos 3 blocos :::tente::: por módulo
- Cada seção tem 200-500 palavras de teoria + exemplo

Crie todos os exercícios desses módulos (5 por módulo = 15 exercícios).
Crie o quiz de cada módulo (5 perguntas).
Crie a especificação dos mini-projetos.

Reforce em todos: pensar antes de codar.
```

### Prompt 7.2 — Módulos 4 a 7 completos
```
Mesmo procedimento dos módulos 1-3, mas para os módulos 4 (Estruturas de Dados), 5 (Funções), 6 (POO), 7 (Arquivos/JSON/Erros).

Adicione complexidade gradualmente.
A partir do módulo 5, validações tipo B (função) ficam mais comuns.
Mini-projetos ficam mais elaborados.
```

### Prompt 7.3 — Módulo 8 (5 submódulos)
```
Escreva o conteúdo dos 5 submódulos do módulo 8.

Para cada submódulo (8.1 a 8.5):
- Conteúdo Markdown
- Exercícios (3-5 cada)
- Mini-projeto

Para 8.2 (Streamlit), 8.3 (Flask), 8.4 (Flet), 8.5 (FastAPI):
- Crie repos template em templates/
- Cada template: README claro com setup local, requirements.txt, código inicial
- Validação na plataforma é via análise estática (verificar se aluno usa @app.route, ft.app, etc)

Para 8.1 (requests), use pyodide.http.pyfetch para rodar no browser.

O mini-projeto final do curso (8.5) integra Flet + FastAPI: aluno deve ter o repo de 8.4 + 8.5 conectados.
```

### Prompt 7.4 — Testes de regressão de conteúdo (por módulo)
```
Esta é a etapa crítica de não-regressão.

Para CADA módulo (1 a 8 + submódulos), crie tests/Content/Modulo{XX}Test.js (ou .php conforme onde validamos):

Para cada exercício do módulo, o teste verifica:
1. Schema do JSON está correto (campos obrigatórios presentes)
2. A solução official passa em todos os casos de teste
3. Soluções "incorretas" propositais falham (criar 1-2 anti-exemplos por exercício)
4. Validação AST funciona se aplicável
5. Conceitos listados batem com o que está sendo testado

Adicionar tests/Content/quizTest.js:
- Cada quiz tem 3-5 perguntas
- Cada pergunta tem exatamente 1 resposta correta
- Resposta correta está dentro do array de opções
- Não há respostas duplicadas

Adicionar tests/Content/moduleStructureTest.js:
- Cada módulo tem front-matter completo
- Pré-requisito existe (se declarado)
- Não há ciclos de dependência entre módulos

Atualizar scripts/test.sh para:
1. Rodar testes de backend (PHPUnit)
2. Rodar testes de frontend (Vitest)
3. Rodar testes de conteúdo
4. Falhar se qualquer um falhar

Adicionar pre-commit hook (opcional, mas recomendado):
- Criar .githooks/pre-commit que roda scripts/test.sh
- Documentar no README como ativar: git config core.hooksPath .githooks

Commit: "test: regressão completa de conteúdo de todos os módulos (Prompt 7.4)"

A partir deste ponto, qualquer alteração em conteúdo ou validadores quebra os testes — exatamente o comportamento desejado.
```

---

## Fase 8 — Polimento e Deploy

### Prompt 8.1 — UX final
```
Polimento geral:
- Loading states em todas as ações
- Mensagens de erro amigáveis (não stack traces)
- Animações sutis (fade in/out, slide)
- Atalhos de teclado:
  - Ctrl+Enter: rodar código
  - Ctrl+S: salvar (já é automático, mas dá feedback visual)
  - Esc: fecha modais
- Tooltip em badges/elementos importantes
- Confirmação antes de logout
- Página 404 customizada
- Acessibilidade: aria-labels, contraste adequado, navegação por teclado
```

### Prompt 8.2 — Mobile e performance
```
Otimizações:
- Pyodide: lazy loading (só carrega quando aluno abre primeiro exercício)
- Cache agressivo: service worker para assets estáticos e Pyodide
- Imagens: SVG inline ou WebP
- CSS: build do Tailwind em produção (purge classes não usadas)
- JS: minify (sem build complexo, usar terser via CLI se necessário)

Mobile:
- Editor CodeMirror funciona em touch
- Modal de exercício em fullscreen no mobile
- Teclado virtual: ajustar layout quando aparece
- Tab/indent: botão dedicado no mobile

Teste em: iPhone SE (tela pequena), Android médio, desktop.
```

### Prompt 8.3 — Deploy e docs
```
Prepare para deploy:
- Script deploy.sh (ou instruções no README) para upload via FTP
- Verificar se host suporta PHP 8 e PDO_SQLITE (script de verificação: php-info.php temporário)
- Configurar .htaccess principal para HTTPS forçado e proteção de pastas sensíveis
- Documentar permissões necessárias em data/

README final:
- Visão geral do projeto
- Stack
- Como rodar localmente (php -S localhost:8000 -t public/)
- Como adicionar conteúdo (formato Markdown + JSON)
- Como adicionar exercícios
- Como fazer deploy
- Como fazer backup
- Troubleshooting comum

CONTRIBUTING.md (opcional) com diretrizes para conteúdo.
```

---

## Fase 9 — Iteração

### Prompt 9.1 — Testar com usuário real
```
Após primeiro deploy, faça um pequeno round de testes:
- Convide 2-3 iniciantes pra usar
- Observe onde travam
- Anote feedback
- Volte pra ajustar
```

### Prompt 9.2 — Adicionar features (opcional)
Ideias para v2:
- Importar/exportar progresso (JSON)
- Modo offline completo (PWA)
- Compartilhar conquistas em redes
- Trilhas alternativas (ex: foco em web, foco em dados)
- Comentários do professor em código (manual)
- Modo "professor" (visualizar progresso de turma)

---

## Notas Finais

### Workflow obrigatório em cada prompt

1. **Antes de começar:** ler `CLAUDE.md` (especialmente "Workflow de Desenvolvimento") e o prompt completo
2. **Extrair checklist** do prompt para `TAREFAS.md`
3. **Executar** marcando progresso em tempo real (`[x]`, `[~]`, `[!]`)
4. **Rodar testes** existentes antes de commitar (`./scripts/test.sh`)
5. **Commit** com mensagem descritiva em português, citando o prompt
6. **Não fazer push** automaticamente
7. **Avisar usuário** que o prompt foi concluído + sugerir próximo
8. **Registrar decisões** em `DECISIONS.md` se houver

### Política de testes

- A partir do **Prompt 1.4**, todos os commits devem passar nos testes
- A partir do **Prompt 2.3**, há testes de regressão de auth
- A partir do **Prompt 5.3**, há testes de validadores
- A partir do **Prompt 6.3**, há testes de gamificação
- A partir do **Prompt 7.4**, qualquer mudança de conteúdo dispara testes de regressão
- **Nenhum commit pode quebrar testes existentes**

### Fim de sessão

Quando o usuário disser "vou encerrar", "finalizar sessão", "push agora", ou similar:

1. Resumir o que foi feito (commits da sessão, tarefas concluídas, decisões registradas)
2. Confirmar com o usuário se pode fazer push
3. Após confirmação: `git push origin <branch-atual>`
4. Confirmar sucesso do push
5. Listar próximos prompts sugeridos para a próxima sessão

### Outras notas

- Cada prompt deve ser executado isoladamente
- Após cada prompt, teste antes de seguir
- Não pule prompts — a ordem importa para o aprendizado incremental do projeto
- Se algum prompt gerar muito código de uma vez, peça pro Claude Code dividir em partes menores
- Mantenha CLAUDE.md atualizado conforme decisões surgirem durante o desenvolvimento
