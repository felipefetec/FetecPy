# FetecPy — Instruções para Claude Code

> Este arquivo orienta o Claude Code durante o desenvolvimento do projeto FetecPy. Ele deve ser consultado antes de qualquer alteração estrutural.

---

## Sobre o Projeto

**FetecPy** é uma plataforma web de ensino de Python para iniciantes absolutos. Aluno aprende algoritmos primeiro, depois Python progressivamente, com execução de código no próprio navegador via Pyodide.

**Princípio pedagógico central:** *Pense antes de codar.* Toda interação deve reforçar raciocínio antes da sintaxe.

---

## Workflow de Desenvolvimento (regras permanentes)

Estas regras se aplicam a **todas as sessões de desenvolvimento**, independente de qual prompt está sendo executado. São obrigatórias e devem ser seguidas sem exceção.

### 1. Idioma das respostas no terminal

- **Todas as respostas, mensagens e logs no terminal devem ser em português brasileiro.**
- Mensagens de commit em português.
- Comentários no código em português (exceto termos técnicos universais como `function`, `class`, `null`).
- Mensagens de erro do sistema, exibidas ao usuário, em português.
- Documentação inline (PHPDoc, JSDoc) em português.
- Exceção: nomes de variáveis, funções e classes ficam em inglês quando for convenção do framework/linguagem (ex: `getUserById`), mas em português quando forem específicos do domínio (ex: `validarPin`, `calcularXp`).

### 2. Commit a cada prompt concluído

- **Ao final de cada prompt do `PROMPTS.md`, fazer commit imediatamente.**
- Mensagem de commit no formato Conventional Commits, em português:
  - `feat: adiciona sistema de autenticação com PIN`
  - `fix: corrige cálculo de streak quando aluno volta após 30+ dias`
  - `docs: atualiza schema de exercícios`
  - `refactor: extrai BadgeChecker para serviço dedicado`
  - `test: adiciona testes do módulo 3`
- Incluir referência ao prompt na mensagem quando aplicável: `feat: estrutura inicial do projeto (Prompt 1.1)`
- **Nunca fazer commit com código quebrado.** Se um prompt foi parcialmente implementado, finalizar antes de comitar, ou fazer commit em partes coerentes.
- Cada commit deve passar nos testes existentes.

### 3. Memória persistente das decisões

- **Toda decisão tomada durante o desenvolvimento deve ser registrada.**
- Decisões arquiteturais, mudanças de escopo, ou desvios do plano original vão para `DECISIONS.md` (criar na raiz do projeto se não existir).
- Formato de cada entrada:
  ```markdown
  ## YYYY-MM-DD — Título da decisão
  **Contexto:** o que motivou a decisão
  **Decisão:** o que foi decidido
  **Consequências:** o que muda no projeto
  **Alternativas consideradas:** o que foi descartado e por quê
  ```
- Atualizar `CLAUDE.md` quando a decisão afetar regras permanentes do projeto.
- Atualizar `PROMPTS.md` quando a decisão afetar prompts futuros ainda não executados.

### 4. Checklist de tarefas marcado em tempo real

- Cada prompt do `PROMPTS.md` deve ter sua checklist de subtarefas extraída e mantida.
- Antes de começar um prompt: ler a lista de subtarefas e criar/abrir um arquivo `TAREFAS.md` com todas elas.
- À medida que cada subtarefa é concluída, **marcar imediatamente** com `[x]`.
- Subtarefas em progresso: marcar com `[~]`.
- Subtarefas bloqueadas: marcar com `[!]` e adicionar comentário explicando o motivo.
- Ao final do prompt, todas devem estar `[x]` ou `[!]` (com justificativa).
- Exemplo de `TAREFAS.md`:
  ```markdown
  ## Prompt 2.1 — Cadastro e login
  - [x] Criar AuthService com normalizarChave
  - [x] Implementar cadastrarOuLogin com bcrypt
  - [x] Criar AuthController com endpoints
  - [~] Implementar rate limiting
  - [ ] Testar com curl
  - [ ] Commit final
  ```

### 5. Push no GitHub apenas ao finalizar sessão

- **Não fazer `git push` automaticamente** após cada commit.
- Os commits ficam locais durante toda a sessão.
- Push acontece **apenas quando o usuário sinalizar explicitamente** que vai finalizar a sessão, com frases como: "vou encerrar", "encerrar sessão", "finalizar", "push agora", "subir tudo".
- Antes do push, fazer um resumo do que foi feito na sessão (lista de commits, tarefas concluídas, decisões registradas).
- Confirmar com o usuário antes de executar o `git push`.
- Push é sempre na branch atual (não criar branches sem pedir).

### 6. Testes automatizados — política de não-regressão

- **Cada módulo (1-8) deve ter sua suíte de testes.**
- Os testes verificam:
  - **Backend:** lógica de validação, serviços, endpoints (PHPUnit ou Pest)
  - **Frontend:** validadores de exercícios (A, B, C) com casos representativos (Vitest ou Jest)
  - **Conteúdo:** cada exercício tem solução que passa nos próprios testes (validação automática do conteúdo)
- Estrutura sugerida:
  ```
  tests/
  ├── Backend/
  │   ├── AuthServiceTest.php
  │   ├── ProgressServiceTest.php
  │   ├── BadgeCheckerTest.php
  │   └── Modules/
  │       ├── Module01Test.php   # testes específicos do módulo 1
  │       ├── Module02Test.php
  │       └── ...
  ├── Frontend/
  │   ├── validator.test.js
  │   └── pyodide.test.js
  └── Content/
      └── exercises.test.js  # roda a solução de cada exercício e verifica que passa
  ```
- **Política de não-regressão:**
  - Nenhum commit pode quebrar testes existentes
  - Antes de cada commit, rodar `composer test` (ou equivalente) e `npm test`
  - Se um teste quebrar, **investigar antes de "consertar"** — pode ser regressão real
  - Adicionar teste novo a cada bug encontrado (TDD reverso)
- **Política por módulo:**
  - Ao concluir um módulo no `PROMPTS.md`, criar a suíte de testes correspondente antes de seguir para o próximo
  - Cobertura mínima: 80% dos serviços de backend, 100% dos validadores de exercício
- Script de CI local: `scripts/test.sh` que roda toda a suíte. Deve passar antes de qualquer push.

### 7. Comportamento esperado em cada turno

Em toda interação durante o desenvolvimento, o Claude Code deve:

1. **Confirmar entendimento** antes de começar tarefas grandes
2. **Mostrar a checklist** do que vai fazer antes de executar
3. **Marcar progresso** em `TAREFAS.md` ao longo do caminho
4. **Avisar sobre decisões importantes** antes de tomá-las (não fazer escolhas arquiteturais silenciosamente)
5. **Resumir o que foi feito** ao final
6. **Sugerir próximos passos** quando o prompt for concluído
7. **Lembrar do push** se a sessão estiver encerrando

---

## Stack Tecnológica (não alterar sem discussão)

### Backend
- **PHP 8+** com PDO_SQLITE
- Sem frameworks pesados (Laravel/Symfony seriam overkill)
- Pode usar uma micro-lib de roteamento (ex: Bramus Router) ou roteamento manual
- Senhas/PINs com `password_hash()` nativo do PHP (bcrypt)
- JSON via `json_encode`/`json_decode`

### Frontend
- HTML estático (sem framework SSR)
- **Tailwind CSS** — via CDN durante dev, build em produção
- **Alpine.js** — para reatividade local (sem React/Vue)
- **CodeMirror 6** — editor de código
- **Pyodide** — Python no navegador (carregado de CDN jsDelivr)
- **Prism.js** — syntax highlighting de blocos Markdown
- **markdown-it** — renderização de Markdown

### Banco
- **SQLite** (arquivo `data/fetecpy.db`)
- Migrations simples em SQL puro (`migrations/001_initial.sql`)
- Script `install.php` cria as tabelas na primeira execução

### Deploy
- Hospedagem PHP compartilhada (sem SSH garantido)
- Upload via FTP ou painel do host
- Diretório `data/` precisa de permissão de escrita

---

## Estrutura de Pastas

```
fetecpy/
├── public/                    # Webroot (apontar DocumentRoot aqui)
│   ├── index.html             # Login
│   ├── app.html               # Dashboard
│   ├── module.html            # Página de módulo
│   ├── api/
│   │   ├── index.php          # Front controller
│   │   └── .htaccess          # Reescreve para index.php
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   │   ├── api.js         # Cliente da API
│   │   │   ├── auth.js        # Login/logout
│   │   │   ├── editor.js      # CodeMirror setup
│   │   │   ├── pyodide.js     # Wrapper de execução
│   │   │   ├── validator.js   # Validação de exercícios (A, B, C)
│   │   │   ├── markdown.js    # Renderização de conteúdo
│   │   │   ├── progress.js    # Atualização de progresso/XP
│   │   │   └── gamification.js
│   │   └── img/
├── src/                       # Código PHP
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   ├── Database.php
│   └── Auth.php
├── content/                   # Markdown dos módulos
│   ├── 01-algoritmos.md
│   ├── 02-python-basico.md
│   └── ...
├── exercises/                 # JSON dos exercícios
│   ├── 01/
│   │   ├── ex01.json
│   │   └── ...
│   └── ...
├── data/                      # SQLite (gitignored)
│   └── fetecpy.db
├── migrations/
│   └── 001_initial.sql
├── tests/                     # PHPUnit (opcional)
├── install.php                # Setup inicial
├── PLANO_ESTUDOS.md           # Currículo completo
├── PROMPTS.md                 # Sequência de prompts para construção
├── CLAUDE.md                  # Este arquivo
└── README.md
```

---

## Convenções de Código

### PHP
- PSR-12
- Namespaces: `FetecPy\Controllers`, `FetecPy\Models`, etc
- Type hints sempre que possível (PHP 8 tem ótimo suporte)
- `declare(strict_types=1);` em todos os arquivos PHP
- Sem `echo` direto — sempre via `JsonResponse::send($data)`

### JavaScript
- ES Modules (`import` / `export`)
- Vanilla JS + Alpine.js — sem build step
- Nomes em camelCase
- `const` por padrão, `let` quando necessário, nunca `var`

### Conteúdo (Markdown)
- Front-matter YAML no topo de cada arquivo:
  ```yaml
  ---
  modulo: "03"
  titulo: "Estruturas de Controle"
  duracao_estimada: "7-9h"
  pre_requisito: "02"
  ---
  ```
- Blocos especiais customizados:
  - `:::dica` ... `:::`
  - `:::aviso` ... `:::`
  - `:::curiosidade` ... `:::`
  - `:::tente`code aqui`:::` (editor inline)

### Exercícios (JSON)
Schema em `exercises/SCHEMA.md`. Exemplo mínimo:

```json
{
  "id": "03-ex01",
  "modulo": "03",
  "ordem": 1,
  "titulo": "Par ou ímpar",
  "dificuldade": "facil",
  "xp": 10,
  "conceitos": ["if", "operador modulo"],
  "antes_de_codar": [
    "Quais as entradas do problema?",
    "Como identificar se um número é par usando matemática?",
    "Qual operador retorna o resto da divisão em Python?"
  ],
  "enunciado": "Pergunte um número ao usuário e imprima 'par' ou 'ímpar'.",
  "dica": "Use o operador `%` para descobrir o resto da divisão por 2.",
  "codigo_inicial": "numero = int(input('Digite um número: '))\n# seu código aqui",
  "validacao": {
    "tipo": "saida_exata",
    "casos": [
      { "input": "4\n", "esperado": "par\n" },
      { "input": "7\n", "esperado": "ímpar\n" },
      { "input": "0\n", "esperado": "par\n" }
    ]
  },
  "solucao": "numero = int(input('Digite um número: '))\nif numero % 2 == 0:\n    print('par')\nelse:\n    print('ímpar')"
}
```

---

## Princípios de UI/UX

1. **Mobile-first** — muitos alunos vão estudar no celular
2. **Carregamento progressivo** — Pyodide só carrega quando o aluno abre exercício
3. **Tela de "carregando ambiente Python"** com barra de progresso na primeira vez
4. **Tipografia legível** — código em fonte mono (JetBrains Mono ou Fira Code)
5. **Modo escuro por padrão** — opção pra modo claro
6. **Feedback imediato** — XP/badges aparecem como toast quando conquistados
7. **Sem distrações** — nada de banners, anúncios, popups

---

## Princípios de Backend

1. **Simplicidade > Performance** — projeto educacional, tráfego baixo
2. **PDO sempre** — nunca `mysqli_*` ou funções diretas de SQLite
3. **Prepared statements sempre** — nunca concatenar SQL
4. **Validar entrada em todo endpoint** — não confiar no frontend
5. **Logs de erro em arquivo** — `data/error.log`, fora do webroot ideal
6. **Rate limiting básico** — proteção contra brute force no PIN (5 tentativas/min)

---

## Segurança

### PIN
- Mínimo 4 dígitos numéricos
- Hash com `password_hash($pin, PASSWORD_BCRYPT)`
- Verificação com `password_verify`
- Rate limit: 5 tentativas erradas por minuto por chave de usuário

### Sessão
- Token aleatório (`bin2hex(random_bytes(32))`)
- Armazenado em tabela `sessions`
- Expiração: 30 dias
- Frontend guarda em `localStorage` como `fetecpy_token`
- Header `Authorization: Bearer <token>` em todas as chamadas autenticadas

### XSS / Injeção
- Conteúdo do aluno (código salvo) **nunca renderizado como HTML**
- Sempre `<pre><code>` com escape ou via CodeMirror
- Markdown dos módulos é dos próprios desenvolvedores, não de usuários — sem risco de injeção

### CORS
- Mesma origem (frontend e backend no mesmo domínio) → CORS não necessário
- Se separar, configurar headers explicitamente

---

## Sistema de Validação de Exercícios

Implementar três validadores no frontend:

### Validador A — Saída exata
1. Capturar `stdout` do `pyodide.runPython`
2. Normalizar (trim final, normalizar `\r\n` → `\n`)
3. Comparar com `esperado`
4. Suportar `input_simulado` via `pyodide.setStdin`

### Validador B — Função
1. Aluno define função `nome_funcao`
2. Sistema chama `pyodide.runPython(codigo)` para definir
3. Para cada caso, executa `nome_funcao(*args)` e compara com `esperado`
4. Captura exceções e mostra ao aluno

### Validador C — AST
1. Usar `ast.parse(codigo)` no próprio Pyodide
2. Verificar regras: `deve_conter`, `nao_deve_conter`, `deve_chamar`
3. Retornar lista de violações ao aluno

Combinação possível em um exercício (ex: testes unitários **+** AST que exige `for`).

---

## Sistema de Gamificação — Implementação

### Cálculo de XP no backend (não confiar no frontend)
- Frontend envia: `{exercise_id, status, tentativas}`
- Backend calcula XP baseado em metadados do exercício e tentativas
- Backend retorna: `{xp_ganho, total_xp, novas_badges, streak}`

### Verificação de badges
Após cada update de progresso, rodar `BadgeChecker::checkAll($userId)`:
- Itera badges não conquistadas
- Verifica condição de cada uma
- Insere em `user_badges` se atende

### Streak
- Sempre que um exercício é resolvido, comparar `ultimo_acesso` com hoje:
  - Mesmo dia: nada muda
  - Ontem: `streak += 1`
  - Mais de 1 dia: `streak = 1`
- Atualizar `ultimo_acesso = CURRENT_DATE`

---

## Conteúdo Pedagógico — Diretrizes

### Tom
- **Você** (não "vocês") — conversa direta com o aluno
- **Casual mas correto** — "vamos descobrir juntos" sem infantilizar
- **Sem jargão desnecessário** — quando usar termo técnico, definir antes
- **Português brasileiro** — vírgula em decimais quando coloquial, ponto em código

### Estrutura típica de módulo (em Markdown)
```markdown
---
modulo: "03"
titulo: "Estruturas de Controle"
---

# Módulo 3 — Estruturas de Controle

## Por que isso importa?

(motivação curta — situação real onde o conceito aparece)

## O que você vai aprender

- Tomar decisões no código
- Repetir ações automaticamente
- ...

## Pensando no problema

(antes de qualquer Python, pensa-se no problema com pseudocódigo)

## 3.1 Operadores de comparação

(teoria + exemplo + :::tente bloco interativo:::)

## 3.2 ...

## Exercícios

[Lista de cards linkando pra cada exercício]

## Mini-projeto: Jogo de Adivinhação

(descrição completa do projeto)

## Quiz

(perguntas de múltipla escolha)

## Resumo

(checklist do que foi aprendido)

## Próximos passos

(prepara o próximo módulo)
```

---

## Decisões Importantes (Já Tomadas)

| Pergunta | Resposta |
|---|---|
| Qual stack? | PHP 8 + SQLite + HTML/Tailwind/Alpine + Pyodide |
| Auth? | Nome + sobrenome + PIN obrigatório (bcrypt) |
| Como rodar Python? | Pyodide no navegador |
| Frameworks dos módulos finais? | requests, Streamlit, Flask, Flet, FastAPI |
| Estrutura módulo 8? | Submodular (8.1 a 8.5) |
| Quantos exercícios por módulo? | 3-5 (escala 1-5) |
| Quiz? | Sim, no final de cada módulo |
| Mini-projeto? | Sim, em todos os módulos |
| Encerrar exercício? | Híbrido + opção de ver solução a cada 3 tentativas inválidas |
| Salvar código do aluno? | Sim, no banco |
| Gamificação? | XP + streak + badges (15 badges) |
| Streak conta como? | Pelo menos 1 exercício resolvido por dia |

---

## O que NÃO fazer

- ❌ Adicionar React/Vue/Next.js — frontend é simples por design
- ❌ Adicionar MySQL/Postgres — host é PHP+SQLite
- ❌ Tentar rodar Python no servidor — Pyodide resolve, sem risco
- ❌ Pedir email do aluno — só nome/sobrenome/PIN
- ❌ Implementar recuperação de PIN — não tem como, é por design
- ❌ Adicionar tracking/analytics invasivos — projeto educacional, respeito ao aluno
- ❌ Sobre-engenharia — começar simples, escalar quando necessário

---

## Checklist de Pronto pra Deploy

- [ ] `install.php` cria todas as tabelas
- [ ] Conteúdo de pelo menos os módulos 1-3 escrito
- [ ] Pelo menos 5 exercícios funcionando com validação completa
- [ ] Auth funcionando (cadastro implícito + login)
- [ ] Editor com Pyodide rodando código
- [ ] Persistência de código testada
- [ ] Sistema de XP atualizando
- [ ] Pelo menos 5 badges implementadas
- [ ] Funciona em mobile
- [ ] Carrega em < 5s na primeira visita
- [ ] Pyodide cacheado nas próximas visitas
- [ ] `.htaccess` configurado pra rotas de API
- [ ] Permissões corretas em `data/`
- [ ] Backup automático do `fetecpy.db` (cron ou script manual)
