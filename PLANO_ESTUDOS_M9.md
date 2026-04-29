# FetecPy — Módulo 9 (Addendum)
## Boas Práticas e Profissionalização

> **Este documento é um addendum ao `PLANO_ESTUDOS.md`.** Ele adiciona o Módulo 9 ao currículo original, sem modificar os módulos 1-8. Deve ser lido como continuação natural após o Módulo 8.5.

---

## Filosofia do Módulo 9

Aqui o aluno **já sabe Python**. O foco muda completamente: sair de "código que funciona" para "código profissional". Esse é o módulo que transforma um aluno em **alguém empregável** ou **alguém que consegue colaborar em projetos reais**.

A natureza do módulo é diferente dos anteriores:

- **Roda inteiramente local** — Pyodide não faz sentido para git, virtualenv, pre-commit hooks. O aluno usa o terminal de verdade.
- **Validação adaptada** — não dá pra rodar `git commit` no browser. Combina quizzes conceituais, validação de output do terminal (aluno cola o resultado), e exercícios de código tradicionais.
- **Tom mais profissional** — assume que o aluno passou pelos 8 módulos anteriores e já tem maturidade.

---

## Visão Geral

**Pré-requisito:** Módulo 8 completo
**Duração estimada:** 10-12 horas
**Submódulos:** 5 (9.1 a 9.5)
**Mini-projeto final:** "Profissionalize seu projeto" — integra tudo

---

## Submódulo 9.1 — Git e GitHub
**Roda em:** local (terminal)
**Duração:** 3-4h

### Objetivos
- Entender o que é controle de versão e por que importa
- Dominar o ciclo básico: modificar → adicionar → commitar
- Trabalhar com branches
- Publicar e colaborar via GitHub

### Tópicos

**9.1.1 — Por que controle de versão?**
- Problema do "código_final_v3_FINAL_DEFINITIVO.py"
- Histórico, rollback, colaboração
- Git vs outras ferramentas (mencionar Mercurial, SVN só pra contexto)

**9.1.2 — Instalação e configuração**
- Instalar git no Windows (Git for Windows), Mac (homebrew), Linux (apt)
- `git config --global user.name` e `user.email`
- Configurar editor padrão
- Verificar instalação: `git --version`

**9.1.3 — Ciclo básico**
- `git init` — criar repo
- `git status` — entender o estado
- `git add` — preparar para commit (staging area)
- `git commit -m "mensagem"` — registrar mudanças
- `git log` — ver histórico
- `git diff` — ver diferenças

**9.1.4 — `.gitignore`**
- O que nunca commitar: senhas, ambientes, builds, arquivos temporários
- Padrões comuns para projetos Python: `__pycache__/`, `*.pyc`, `.venv/`, `.env`
- Templates do GitHub (`gitignore.io`)

**9.1.5 — Branches**
- O que são branches e quando usar
- `git branch` — listar
- `git checkout -b nova-feature` ou `git switch -c nova-feature`
- `git merge feature` (estando na main)
- Conflitos básicos e como resolver

**9.1.6 — GitHub**
- Criar conta
- Criar repositório vazio
- Conectar repo local: `git remote add origin URL`
- `git push -u origin main`
- `git pull` para puxar mudanças
- `git clone` para baixar repo existente

**9.1.7 — Pull Requests (introdução conceitual)**
- O que é um PR
- Fluxo básico: fork → clone → branch → push → abrir PR
- Code review (visão geral)

**9.1.8 — Conventional Commits**
- Por que padronizar mensagens
- Formato: `tipo(escopo opcional): descrição`
- Tipos comuns: `feat`, `fix`, `docs`, `refactor`, `test`, `chore`
- Conexão com versionamento semântico (introdução leve)

### Exercícios

**Ex 9.1.1 — Primeiro repositório** *(fácil)*
Inicialize um repo, faça 3 commits sequenciais alterando um arquivo. Veja o histórico com `git log`.

**Ex 9.1.2 — Restaurando passado** *(fácil)*
Faça commits, depois "volte no tempo" usando `git checkout <hash>`. Volte para `main` depois.

**Ex 9.1.3 — Branch e merge** *(médio)*
Crie uma branch `experimento`, faça mudanças, volte pra main, faça merge. Verifique o histórico.

**Ex 9.1.4 — Resolvendo conflito** *(médio)*
Crie um conflito proposital (alterar mesma linha em duas branches), resolva manualmente, complete o merge.

**Ex 9.1.5 — Publicando no GitHub** *(desafio)*
Crie um repo no GitHub, conecte com seu repo local, faça push. Convide outro usuário (pode ser fictício) como colaborador.

### Validação
- Quizzes conceituais (qual comando faz X)
- Exercícios de código onde o aluno cola a saída do `git log` ou `git status` e o sistema verifica padrões esperados (ex: presença de hashes, contagem de commits, mensagens no formato Conventional Commits)
- Mini-prática validada manualmente via link do GitHub (aluno cola URL do repo)

### Mini-prática do submódulo
Versione um dos projetos anteriores (sugestão: o sistema bancário do Módulo 6 ou o caderno de notas do Módulo 7). Crie repo no GitHub com pelo menos 10 commits significativos seguindo Conventional Commits.

---

## Submódulo 9.2 — Ambientes Virtuais e Dependências
**Roda em:** local (terminal)
**Duração:** 2h

### Objetivos
- Entender o problema "funciona na minha máquina"
- Criar e usar ambientes virtuais
- Gerenciar dependências de projeto
- Conhecer ferramentas modernas (uv)

### Tópicos

**9.2.1 — O problema das dependências globais**
- Cenário: dois projetos, versões incompatíveis da mesma biblioteca
- Por que instalar tudo global é receita pra desastre

**9.2.2 — Virtualenv**
- `python -m venv .venv`
- Estrutura criada (Lib, Scripts/bin)
- Ativação:
  - Linux/Mac: `source .venv/bin/activate`
  - Windows CMD: `.venv\Scripts\activate.bat`
  - Windows PowerShell: `.venv\Scripts\Activate.ps1`
- `deactivate`
- Como saber se está ativo (prompt do shell)

**9.2.3 — pip básico**
- `pip install pacote`
- `pip install pacote==1.2.3` (versão específica)
- `pip list` — listar instalados
- `pip uninstall pacote`
- `pip show pacote` — detalhes

**9.2.4 — requirements.txt**
- `pip freeze > requirements.txt`
- Ler e entender o arquivo
- `pip install -r requirements.txt`
- Versionamento: `==`, `>=`, `~=`, sem versão
- Boas práticas: pinning vs flexibilidade

**9.2.5 — `pyproject.toml` (introdução)**
- Padrão moderno para configuração de projeto
- Diferença prática para requirements.txt
- Quando usar cada um

**9.2.6 — uv (introdução leve)**
- Gerenciador moderno em Rust, ~10-100x mais rápido
- `uv venv`, `uv pip install`
- Quando vale a pena migrar

### Exercícios

**Ex 9.2.1 — Primeiro venv** *(fácil)*
Crie um venv, ative, instale `requests`, verifique que está isolado do Python global.

**Ex 9.2.2 — Instalar versão específica** *(fácil)*
Instale `flask==2.0.0`, depois atualize para a última versão. Veja a diferença com `pip show`.

**Ex 9.2.3 — Recriar ambiente** *(médio)*
Em uma pasta nova, recrie o ambiente do exercício anterior usando apenas o requirements.txt.

**Ex 9.2.4 — Comparar pip vs uv** *(desafio)*
Crie dois venvs idênticos, um instalado com pip e outro com uv. Compare o tempo de instalação. Comente o resultado.

### Validação
Aluno cola saída de `pip list`, `pip freeze`, ou prompt mostrando ambiente ativado. Sistema verifica padrões.

### Mini-prática do submódulo
Configure ambiente virtual profissional para o projeto do submódulo 9.1. Adicione `requirements.txt` com dependências reais. Adicione `.venv/` ao `.gitignore`. Faça commit das mudanças.

---

## Submódulo 9.3 — Type Hints e Documentação
**Roda em:** Pyodide (código) + local (mypy)
**Duração:** 2h

### Objetivos
- Adicionar type hints a código Python
- Validar tipos com mypy
- Escrever documentação clara (docstrings, README)

### Tópicos

**9.3.1 — Por que tipos em linguagem dinâmica?**
- Documentação que não envelhece
- IDEs mais inteligentes
- Bugs detectados antes da execução
- "Custo" mínimo, ganho grande

**9.3.2 — Sintaxe básica**
- `def soma(a: int, b: int) -> int:`
- Variáveis: `idade: int = 30`
- Funções sem retorno: `-> None`

**9.3.3 — Tipos compostos**
- `list[int]`, `dict[str, float]`, `tuple[int, str]`
- `Optional[int]` ou `int | None` (Python 3.10+)
- `Union[int, str]` ou `int | str`
- `Any` (e por que evitar)
- `Callable`

**9.3.4 — mypy**
- Instalação: `pip install mypy`
- `mypy arquivo.py`
- Interpretar erros
- Configuração mínima em `pyproject.toml`

**9.3.5 — Docstrings**
- Sintaxe básica: `"""..."""`
- Padrão Google:
  ```python
  def soma(a: int, b: int) -> int:
      """Soma dois números inteiros.
      
      Args:
          a: Primeiro número.
          b: Segundo número.
      
      Returns:
          A soma de a e b.
      """
  ```
- Padrão NumPy (alternativa)
- Ferramentas que usam: Sphinx, MkDocs, IDEs

**9.3.6 — README.md profissional**
- Estrutura mínima:
  - Título + descrição curta
  - Instalação
  - Uso (com exemplo)
  - Tecnologias
  - Licença
- Badges (introdução, sem aprofundar)
- Markdown avançado: tabelas, blocos de código, links

### Exercícios

**Ex 9.3.1 — Adicionar tipos** *(fácil)*
Pegue uma função sem tipos do Módulo 5 (validador de CPF) e adicione type hints completos.

**Ex 9.3.2 — Encontrar bugs com mypy** *(médio)*
Sistema fornece código com erro de tipo "escondido". Aluno roda mypy mentalmente (ou de verdade) e identifica o problema.

**Ex 9.3.3 — Escrever docstring Google** *(médio)*
Receba 3 funções sem documentação. Escreva docstring no padrão Google para cada.

**Ex 9.3.4 — README de projeto** *(desafio)*
Escreva um README profissional para um dos projetos anteriores. Mínimo: 6 seções, exemplo de uso funcional, instruções de instalação.

### Validação
- Code: validação AST verificando presença de anotações
- Docstrings: validação por presença de seções `Args:`, `Returns:`
- README: validação manual ou contagem de seções/caracteres

### Mini-prática do submódulo
Adicione type hints e docstrings a todas as funções públicas do projeto do 9.2. Escreva README profissional. Commit.

---

## Submódulo 9.4 — Linters e Formatadores
**Roda em:** local
**Duração:** 1-2h

### Objetivos
- Entender o que linters fazem
- Configurar ruff e black em projeto
- Automatizar com pre-commit hooks

### Tópicos

**9.4.1 — O que é um linter?**
- Análise estática de código
- Encontra erros, más práticas, inconsistências de estilo
- Diferença entre linter (analisa) e formatter (corrige)

**9.4.2 — ruff**
- Linter moderno em Rust, super rápido
- Substitui flake8, isort, e até parcialmente o black
- Instalação: `pip install ruff`
- Uso básico: `ruff check .`
- Auto-fix: `ruff check --fix .`
- Configuração em `pyproject.toml`

**9.4.3 — black**
- "The uncompromising code formatter"
- Sem configuração — opinião forte sobre estilo
- `pip install black`
- `black arquivo.py`
- Por que isso é bom (acaba com debate de estilo no time)

**9.4.4 — Configuração mínima**
- `pyproject.toml` com config de ruff e black coexistindo
- Linha máxima, regras ignoradas, paths excluídos

**9.4.5 — Pre-commit hooks**
- O que são git hooks
- pre-commit como ferramenta (`pip install pre-commit`)
- `.pre-commit-config.yaml` mínimo com ruff + black
- `pre-commit install`
- Demonstração: tentar commitar código mal formatado e ver hook bloqueando

### Exercícios

**Ex 9.4.1 — Rodar ruff** *(fácil)*
Sistema fornece código com erros de lint. Aluno explica cada erro encontrado.

**Ex 9.4.2 — Reformatar com black** *(fácil)*
Código mal formatado vira código black-style. Comparar antes/depois.

**Ex 9.4.3 — Configurar pyproject.toml** *(médio)*
Escreva um `pyproject.toml` que configure ruff (linha 100, ignorar regra E501 em testes) e black (linha 100).

**Ex 9.4.4 — Pre-commit funcionando** *(desafio)*
Configure pre-commit no projeto do 9.3. Tente commitar código mal formatado. Mostre que o hook bloqueia. Cole saída do terminal.

### Validação
- Quiz conceitual
- Validação de saída de comandos (ruff/black)
- Validação de `pyproject.toml` por estrutura/chaves

### Mini-prática do submódulo
Configure ruff + black + pre-commit no projeto. Reformate todo o código existente. Commit dos arquivos de configuração + commit separado da reformatação.

---

## Submódulo 9.5 — Testes Automatizados com pytest
**Roda em:** Pyodide (código) + local (pytest real)
**Duração:** 3-4h

### Objetivos
- Entender por que testar
- Escrever testes com pytest
- Usar fixtures e parametrização
- Medir cobertura
- Conhecer TDD (introdução)

### Tópicos

**9.5.1 — Por que testar?**
- Confiança em refatorar
- Documentação executável
- Detectar regressões
- Exemplo concreto: bug que poderia ter sido evitado por teste no validador de CPF do Módulo 5

**9.5.2 — pytest básico**
- Instalação: `pip install pytest`
- Estrutura: arquivo `test_*.py`, função `test_*`
- `assert` simples
- Rodar: `pytest`, `pytest -v`, `pytest arquivo.py::test_funcao`
- Ver falhas e entender mensagens

**9.5.3 — Organizando testes**
- Pasta `tests/` separada
- Estrutura espelha código fonte
- `conftest.py` (introdução)

**9.5.4 — Fixtures**
- O que são (setup compartilhado entre testes)
- `@pytest.fixture`
- Escopo: function, module, session
- Fixtures que retornam dados de teste

**9.5.5 — Parametrização**
- `@pytest.mark.parametrize`
- Testar múltiplos casos sem repetir código
- IDs descritivos

**9.5.6 — Mocking (introdução leve)**
- Quando precisamos
- `unittest.mock` básico
- `monkeypatch` do pytest

**9.5.7 — Cobertura de código**
- `pip install pytest-cov`
- `pytest --cov=src`
- Interpretar relatório
- Cobertura como métrica (e suas armadilhas)

**9.5.8 — TDD (introdução)**
- Red → Green → Refactor
- Quando faz sentido, quando não faz
- Demonstração com exemplo simples

### Exercícios

**Ex 9.5.1 — Primeiro teste** *(fácil)*
Função `eh_par(n)` (do Módulo 5). Escreva 3 testes: número par, número ímpar, zero.

**Ex 9.5.2 — Testar com fixture** *(médio)*
Função que opera em uma lista. Crie fixture com a lista de teste e use em 3 testes diferentes.

**Ex 9.5.3 — Parametrização** *(médio)*
Refatore o teste do `eh_par` usando `@pytest.mark.parametrize` para testar 10 casos.

**Ex 9.5.4 — Mock de I/O** *(desafio)*
Função que lê arquivo. Escreva teste usando monkeypatch para mockar a leitura.

**Ex 9.5.5 — TDD na prática** *(desafio)*
Implemente uma função `validar_email(email)` usando TDD: escreva o teste primeiro, faça falhar, implemente, faça passar.

### Validação
- Código no Pyodide quando possível (com pytest mockado ou modo simples)
- Validação por output do terminal: aluno cola saída do `pytest`, sistema confere
- AST checa presença de `assert`, `@pytest.fixture`, etc

### Mini-prática do submódulo
Adicione suíte de testes ao projeto do 9.4. Mínimo: 5 testes, 1 fixture, 1 parametrização. Atinja 70% de cobertura.

---

## Mini-Projeto Final do Curso

### "Profissionalize seu projeto"

O aluno escolhe **um** dos mini-projetos anteriores (sugestão: a API de Biblioteca do 8.5, que já está em fase mais avançada) e aplica **tudo** do Módulo 9.

### Checklist do projeto final

- [ ] Repositório criado no GitHub (público)
- [ ] Pelo menos 10 commits seguindo Conventional Commits
- [ ] Pelo menos 1 branch criada e mesclada
- [ ] `requirements.txt` ou `pyproject.toml` configurado
- [ ] `.venv/` no `.gitignore`
- [ ] Type hints em **todas** as funções públicas
- [ ] Docstrings (padrão Google) em **todas** as funções públicas
- [ ] `pyproject.toml` com configuração de ruff + black
- [ ] Pre-commit hook configurado e funcionando
- [ ] Suíte de testes com pelo menos 5 testes
- [ ] Pelo menos 1 fixture e 1 parametrização
- [ ] Cobertura mínima de 70%
- [ ] README profissional com mínimo de 6 seções
- [ ] CI mínimo no GitHub Actions (opcional, bônus)

### Resultado

Aluno termina o curso com um repositório público no GitHub que ele pode mostrar para:
- Empregadores em entrevistas
- Professores em apresentações
- Comunidade open source para contribuições
- Si mesmo, como prova de competência

**Esse é o "diploma de verdade"** — não um certificado em PDF, mas um artefato profissional que demonstra capacidade real.

---

## Quiz Final do Módulo 9

(5 perguntas conceituais)

1. Qual a principal diferença entre `pip install pacote` em ambiente global vs em virtualenv?
2. O que `git add` faz exatamente?
3. Para que serve uma fixture do pytest?
4. Por que usar `Optional[int]` em vez de `int` quando uma função pode retornar `None`?
5. Qual a vantagem de pre-commit hook em comparação a rodar ruff/black manualmente?

---

## Sistema de Validação para o Módulo 9

Como o Módulo 9 não roda no Pyodide para a maior parte dos exercícios (git, venv, ferramentas locais), a validação tem 4 modalidades:

### Tipo D — Output do terminal
Aluno executa comando localmente e cola a saída. Sistema usa regex para validar padrões esperados.

```json
{
  "tipo": "terminal_output",
  "regex_esperado": "^On branch main\\s*\\nYour branch is up to date",
  "explicacao": "Saída de git status quando tudo está sincronizado"
}
```

### Tipo E — URL do GitHub
Aluno cola URL do repositório. Sistema (opcionalmente, via API do GitHub) valida estrutura básica.

```json
{
  "tipo": "github_url",
  "validacoes": [
    "tem_pelo_menos_10_commits",
    "tem_arquivo_requirements_txt",
    "tem_arquivo_readme_md"
  ]
}
```

### Tipo F — Quiz conceitual
Como nos outros módulos.

### Tipo C (já existente) — AST
Reusado para validar type hints e estrutura de código.

---

## XP do Módulo 9

Por ser um módulo "pesado" e o último, ganha XP especial:

- Cada exercício: mesmo valor dos módulos anteriores
- Cada submódulo concluído: bônus de 75 XP
- Mini-projeto final: 200 XP (vs 50 dos anteriores)
- Conclusão do módulo: bônus de 250 XP

### Badges novas para o Módulo 9

- 🎩 **Profissional** — completou o Módulo 9
- 🐙 **GitHub Master** — primeiro repo público criado
- 🧪 **Testador** — primeiro teste passando
- 🎯 **Cobertura 100%** — atingiu 100% de cobertura em algum projeto
- 🚀 **Open Source** — fez primeiro fork e PR (validação manual ou opcional)

---

## Notas para Implementação

### Ajustes no `CLAUDE.md` original
Adicionar Módulo 9 à seção "Decisões Importantes":
- Submódulos: 9.1 Git/GitHub, 9.2 venv, 9.3 type hints/docs, 9.4 linters, 9.5 pytest
- Validação: tipos D (terminal output) e E (GitHub URL) são novos

### Ajustes no `PROMPTS.md` original
Adicionar prompts da Fase 7.5 (entre conteúdo dos módulos e polimento):

- **Prompt 7.5.1** — Conteúdo Markdown do Módulo 9 (todos submódulos)
- **Prompt 7.5.2** — Exercícios do Módulo 9 (formato JSON, com tipos D e E)
- **Prompt 7.5.3** — Implementar validadores D (terminal output) e E (GitHub URL)
- **Prompt 7.5.4** — Testes de regressão do Módulo 9
- **Prompt 7.5.5** — Badges novas do Módulo 9 e ajustes de XP

### Ordem de execução recomendada
Os prompts do Módulo 9 só devem ser executados **depois** que o Prompt 7.4 (regressão de conteúdo) estiver completo. Ou seja, Módulos 1-8 devem estar 100% testados antes de começar o conteúdo do 9.

### Considerações de UX

- Avisar o aluno no início do Módulo 9: "este é diferente, você vai precisar do terminal"
- Detectar se aluno está em mobile e sugerir continuar em desktop para este módulo
- Se possível, integrar com tutorial interativo de git (ex: link para learngitbranching.js.org)
