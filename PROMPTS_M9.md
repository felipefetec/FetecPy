# FetecPy — Prompts do Módulo 9 (Addendum)
## Sequência de Construção do Módulo 9

> ⚠️ **PRÉ-REQUISITO OBRIGATÓRIO:** Este arquivo só deve ser lido e executado **após o Prompt 7.4** do `PROMPTS.md` original estar concluído. Os módulos 1-8 precisam estar com testes de regressão funcionando antes de iniciar o Módulo 9.
>
> 📄 **Documento complementar:** `PLANO_ESTUDOS_M9.md` na raiz do projeto. Leia antes de executar qualquer prompt deste arquivo.
>
> 🔄 **Workflow:** segue as mesmas regras do `CLAUDE.md` (commits em português a cada prompt, push só ao finalizar sessão, marcar `TAREFAS.md` em tempo real, registrar decisões em `DECISIONS.md`).

---

## Verificação de Pré-requisitos

Antes de começar a Fase 7.5, confirme que:

- [ ] Prompts 1.1 a 7.4 do `PROMPTS.md` original foram concluídos
- [ ] `./scripts/test.sh` passa sem erro (todos os testes verdes)
- [ ] Conteúdo dos módulos 1 a 8 está completo (Markdown + exercícios + quizzes)
- [ ] Sistema de gamificação funcionando (XP, streak, badges)
- [ ] Validadores A, B, C funcionando
- [ ] Pelo menos 1 deploy de teste foi feito ou está validado localmente

Se algum item acima não estiver atendido, **pare e volte ao `PROMPTS.md` original** antes de prosseguir.

---

## Fase 7.5 — Construção do Módulo 9

### Prompt 7.5.1 — Estrutura e novos validadores
```
Leia CLAUDE.md, PLANO_ESTUDOS.md, PLANO_ESTUDOS_M9.md e este arquivo (PROMPTS_M9.md).

Confirme primeiro que ./scripts/test.sh passa. Se não passar, pare e avise.

Atualize TAREFAS.md adicionando seção "Prompt 7.5.1".

Tarefas:

1. Atualizar CLAUDE.md adicionando à seção "Decisões Importantes":
   | Módulo 9 | Boas Práticas (Git, venv, type hints, linters, pytest) |
   | Validação tipo D | Output do terminal (regex) |
   | Validação tipo E | GitHub URL (validação opcional via API) |

2. Criar exercises/09/ como estrutura de diretórios:
   - exercises/09/9.1/ (git/github)
   - exercises/09/9.2/ (venv)
   - exercises/09/9.3/ (type hints + docs)
   - exercises/09/9.4/ (linters)
   - exercises/09/9.5/ (pytest)

3. Atualizar exercises/SCHEMA.md documentando os novos tipos de validação:
   - tipo "terminal_output" com campo "regex_esperado" e "explicacao"
   - tipo "github_url" com campo "validacoes" (array de strings)
   - tipo "manual" para validações que precisam de revisão humana

4. Atualizar src/Models/Exercise.php (ou equivalente) para suportar os novos tipos.

5. Implementar no frontend (public/assets/js/validator.js):
   - validateTerminalOutput(output, regex) — valida saída colada pelo aluno
   - validateGithubUrl(url, validacoes) — busca repo via API pública GitHub e verifica
     - Validações suportadas inicialmente: tem_pelo_menos_N_commits, tem_arquivo_X, tem_arquivo_README
   - validateManual(resposta) — sempre retorna "pendente_revisao", marca como tentado

6. Implementar no backend (src/Services/):
   - Nada novo, validação é principalmente client-side
   - Apenas garantir que o tipo do exercício seja salvo corretamente

7. Adicionar testes em tests/Frontend/validator.test.js:
   - testTerminalOutputComPadraoCorreto
   - testTerminalOutputComPadraoIncorreto
   - testGithubUrlInvalida
   - testGithubUrlSemCommitsSuficientes
   - testManualSempreRetornaPendente

8. Rodar ./scripts/test.sh — todos devem passar.

Marcar tarefas concluídas em TAREFAS.md.

Commit: "feat: adiciona suporte ao Módulo 9 (validadores D e E) - Prompt 7.5.1"

Não fazer push.
```

---

### Prompt 7.5.2 — Conteúdo Markdown do Módulo 9
```
Leia PLANO_ESTUDOS_M9.md (especialmente as seções dos submódulos 9.1 a 9.5).

Atualize TAREFAS.md adicionando seção "Prompt 7.5.2".

Crie os arquivos de conteúdo em content/:

1. content/09-boas-praticas-intro.md
   - Front-matter completo (modulo: "09", titulo: "Boas Práticas e Profissionalização", duracao: "10-12h", pre_requisito: "08")
   - Apresentação do módulo
   - Aviso claro: "este módulo é diferente, requer terminal real"
   - Detector de mobile com sugestão de continuar em desktop
   - Visão geral dos 5 submódulos
   - Link para o mini-projeto final

2. content/09-1-git-github.md
   - Conteúdo completo conforme PLANO_ESTUDOS_M9.md seção 9.1
   - Tópicos 9.1.1 a 9.1.8
   - Pelo menos 5 blocos :::dica::: e :::aviso:::
   - Sem :::tente::: nesse submódulo (não roda Pyodide para git)
   - Inclua imagens conceituais (diagramas SVG inline) para:
     - Working directory → staging area → repository
     - Branches divergindo e merge
     - Fluxo local ↔ GitHub remoto

3. content/09-2-venv-deps.md
   - Conteúdo completo conforme seção 9.2
   - Tópicos 9.2.1 a 9.2.6
   - Diagrama mostrando isolamento entre venvs

4. content/09-3-type-hints-docs.md
   - Conteúdo conforme seção 9.3
   - Tópicos 9.3.1 a 9.3.6
   - Aqui pode ter :::tente::: pois código Python roda no Pyodide
   - Exemplos lado-a-lado: sem tipos vs com tipos

5. content/09-4-linters.md
   - Conteúdo conforme seção 9.4
   - Tópicos 9.4.1 a 9.4.5
   - Exemplos antes/depois de formatação

6. content/09-5-pytest.md
   - Conteúdo conforme seção 9.5
   - Tópicos 9.5.1 a 9.5.8
   - Pode ter :::tente::: para escrever asserts simples
   - Subseção dedicada ao TDD com exemplo passo-a-passo

Tom de escrita:
- Mais profissional que módulos anteriores (aluno tem maturidade)
- Ainda acolhedor, sem virar "manual técnico seco"
- Português brasileiro
- Exemplos práticos, idealmente do dia-a-dia de um dev brasileiro

Marcar tarefas concluídas em TAREFAS.md.

Commit: "docs: conteúdo Markdown completo do Módulo 9 - Prompt 7.5.2"
```

---

### Prompt 7.5.3 — Exercícios do Módulo 9
```
Leia PLANO_ESTUDOS_M9.md (seções de exercícios de cada submódulo).
Leia também content/09-*.md já criados.

Atualize TAREFAS.md adicionando seção "Prompt 7.5.3".

Crie os exercícios em formato JSON conforme schema atualizado:

Submódulo 9.1 — exercises/09/9.1/:
- ex01.json — Primeiro repositório (tipo: terminal_output)
- ex02.json — Restaurando passado (tipo: terminal_output)
- ex03.json — Branch e merge (tipo: terminal_output)
- ex04.json — Resolvendo conflito (tipo: terminal_output)
- ex05.json — Publicando no GitHub (tipo: github_url)

Submódulo 9.2 — exercises/09/9.2/:
- ex01.json — Primeiro venv (tipo: terminal_output)
- ex02.json — Instalar versão específica (tipo: terminal_output)
- ex03.json — Recriar ambiente (tipo: terminal_output)
- ex04.json — Comparar pip vs uv (tipo: manual)

Submódulo 9.3 — exercises/09/9.3/:
- ex01.json — Adicionar tipos (tipo: ast — verificar anotações)
- ex02.json — Encontrar bugs com mypy (tipo: quiz embutido)
- ex03.json — Escrever docstring Google (tipo: ast — verificar Args:/Returns:)
- ex04.json — README de projeto (tipo: manual)

Submódulo 9.4 — exercises/09/9.4/:
- ex01.json — Rodar ruff (tipo: quiz embutido)
- ex02.json — Reformatar com black (tipo: saida_exata via Pyodide)
- ex03.json — Configurar pyproject.toml (tipo: manual ou validação por chaves)
- ex04.json — Pre-commit funcionando (tipo: terminal_output)

Submódulo 9.5 — exercises/09/9.5/:
- ex01.json — Primeiro teste (tipo: funcao via Pyodide)
- ex02.json — Testar com fixture (tipo: ast — verificar @pytest.fixture)
- ex03.json — Parametrização (tipo: ast — verificar @pytest.mark.parametrize)
- ex04.json — Mock de I/O (tipo: ast — verificar monkeypatch)
- ex05.json — TDD na prática (tipo: manual)

Para cada exercício, incluir:
- id, modulo, submodulo, ordem
- titulo, dificuldade, xp
- conceitos (lista)
- antes_de_codar (mínimo 3 perguntas)
- enunciado claro
- dica (não a solução)
- codigo_inicial (quando aplicável)
- validacao (com tipo correto)
- solucao (sempre, mesmo para validação manual — para o gabarito)

Atenção especial:
- Exercícios tipo "terminal_output" devem ter exemplos da saída esperada no enunciado
- Exercícios tipo "github_url" devem explicar exatamente o que validar
- Exercícios tipo "manual" devem deixar claro que vão para "tentado" automaticamente

Adicionar testes em tests/Content/Modulo09Test.js:
- Cada exercício tem schema válido
- Soluções dos exercícios "ast" passam nas próprias regras
- Anti-exemplos falham apropriadamente

Adicionar quizzes:
- 5 questões por submódulo (total 25)
- Quiz final do módulo (5 questões integradoras) conforme PLANO_ESTUDOS_M9.md

Rodar ./scripts/test.sh — todos devem passar.

Marcar tarefas concluídas em TAREFAS.md.

Commit: "feat: exercícios e quizzes do Módulo 9 - Prompt 7.5.3"
```

---

### Prompt 7.5.4 — Mini-projeto final do curso
```
Leia PLANO_ESTUDOS_M9.md seção "Mini-Projeto Final do Curso".

Atualize TAREFAS.md adicionando seção "Prompt 7.5.4".

Tarefas:

1. Criar content/09-projeto-final.md
   - Apresentação do projeto "Profissionalize seu projeto"
   - Justificativa: por que esse é o "diploma de verdade"
   - Sugestões de qual projeto anterior usar (recomendação: API de Biblioteca do 8.5)
   - Checklist de 13 itens conforme PLANO_ESTUDOS_M9.md
   - Critérios de avaliação detalhados para cada item
   - Tutorial passo-a-passo para o aluno seguir

2. Criar exercises/09/projeto-final.json
   - Tipo especial "projeto_final" 
   - Validação do tipo "github_url" com TODAS as validações:
     - tem_pelo_menos_10_commits
     - tem_arquivo_requirements_txt (ou pyproject.toml)
     - tem_arquivo_README_md
     - tem_pasta_tests
     - tem_arquivo_gitignore
     - tem_pre_commit_config (opcional, marca como bônus)
   - XP: 200 (vs 50 dos mini-projetos anteriores)

3. Implementar no backend src/Services/GithubValidationService.php:
   - validarRepo(url, validacoes) → array de resultados
   - Usa API pública do GitHub (sem autenticação para repos públicos)
   - Cache de resposta por 5 minutos para evitar rate limit
   - Cada validação é um método separado:
     - validarMinimoCommits($repo, $minimo)
     - validarPresencaArquivo($repo, $caminho)
     - validarPresencaPasta($repo, $caminho)
   - Tratamento de erros: repo privado, repo inexistente, rate limit excedido

4. Adicionar testes em tests/Backend/GithubValidationServiceTest.php:
   - Mockar respostas da API GitHub
   - Testar cada validação isoladamente
   - Testar tratamento de erros

5. Frontend: tela especial para o projeto final
   - Visual diferenciado (mais celebratório)
   - Checklist visual com cada item destacado
   - Botão "Validar meu projeto" que dispara validação
   - Resultado: cada item verde/vermelho com explicação

6. Quando concluído:
   - Modal celebratório especial ("Você completou o curso!")
   - Badge especial 🎓 "Formado" desbloqueada
   - Sugestão de próximos passos (continuar estudando, contribuir open source)

Rodar ./scripts/test.sh — todos devem passar.

Marcar tarefas concluídas em TAREFAS.md.

Commit: "feat: mini-projeto final do curso (Módulo 9) - Prompt 7.5.4"
```

---

### Prompt 7.5.5 — Badges, XP e ajustes finais do Módulo 9
```
Leia PLANO_ESTUDOS_M9.md seções "XP do Módulo 9" e "Badges novas para o Módulo 9".

Atualize TAREFAS.md adicionando seção "Prompt 7.5.5".

Tarefas:

1. Adicionar novas badges em src/Services/BadgeChecker.php:
   - profissional — completou todos os submódulos do Módulo 9
   - github_master — primeiro repo público criado (detectado via exercício 9.1.5)
   - testador — primeiro teste passando (exercício 9.5.1)
   - cobertura_100 — atingiu 100% de cobertura (validação manual ou input do aluno)
   - open_source — fez primeiro fork e PR (validação manual)

2. Implementar lógica de verificação de cada badge nova.

3. Atualizar sistema de XP:
   - Bônus de 75 XP ao completar cada submódulo do Módulo 9 (vs nada nos outros)
   - Bônus de 250 XP ao completar Módulo 9 inteiro (vs 100 dos outros módulos)
   - Mini-projeto final: 200 XP (já configurado em 7.5.4)

4. Atualizar dashboard (public/app.html):
   - Card especial para o Módulo 9 (visual diferenciado, ícone 🎩)
   - Indicador de "módulo final" — bloqueia até 100% do Módulo 8 estar completo
   - Mensagem motivacional quando aluno desbloqueia o Módulo 9

5. Adicionar testes em tests/Backend/Modules/Module09Test.php:
   - testBadgeProfissionalDesbloquaaApos9_5
   - testXpExtraQuandoCompletaSubmodulo9
   - testM9BloqueadoSeM8Incompleto

6. Atualizar tests/Content/Modulo09Test.js para validar:
   - Cada exercício tem campos esperados conforme novo tipo
   - Quiz final tem exatamente 5 perguntas
   - Mini-projeto tem checklist de 13 itens

7. Atualizar README.md do projeto adicionando seção "Módulo 9":
   - Mencionar que é um módulo "extra" focado em profissionalização
   - Pré-requisitos de software local (git, python instalado)

8. Atualizar DECISIONS.md registrando:
   - Decisão de adicionar Módulo 9 como addendum (data, motivação, alternativas consideradas)

9. Rodar ./scripts/test.sh — todos devem passar.

10. Verificar manualmente:
    - Conseguir navegar todo o Módulo 9 do início ao fim
    - Resolver pelo menos 1 exercício de cada submódulo
    - Disparar validação do projeto final com URL fictícia (deve falhar de forma graciosa)
    - Conferir que badges aparecem corretamente

Marcar tarefas concluídas em TAREFAS.md.

Commit: "feat: badges, XP e ajustes finais do Módulo 9 - Prompt 7.5.5"

🎉 Ao concluir este prompt, o currículo do FetecPy está 100% completo (módulos 1-9).
Sugerir ao usuário próximos passos: deploy de produção (Prompt 8.x do PROMPTS.md original), divulgação, ou começar a coletar feedback de alunos reais.
```

---

## Considerações Especiais para o Módulo 9

### Limitações conhecidas

**Validação de exercícios de terminal não é à prova de fraude.**
O aluno pode "inventar" uma saída do terminal e o sistema aceitará se passar no regex. Isso é aceitável porque:
- Plataforma é educacional, não certificadora oficial
- O objetivo é o aluno aprender, não burlar
- O projeto final no GitHub é a prova real e auditável

**Validação via API do GitHub tem rate limit.**
API pública do GitHub permite 60 requisições/hora por IP não autenticado. Mitigações:
- Cache de 5 minutos por URL validada
- Mensagem clara quando rate limit é excedido ("tente novamente em X minutos")
- Em produção, considerar autenticação OAuth via app GitHub para aumentar para 5000/hora

**Custo de validação manual.**
Alguns exercícios são marcados como "manual" e ficam como "tentado" sem progressão automática. Em uma plataforma educacional formal, isso exigiria revisão humana. Para o FetecPy, fica como "concluído honesto" — o aluno marca quando achar que terminou.

### Pontos de atenção pedagógicos

- **Mobile não funciona aqui.** Avisar logo no início e não tentar "forçar" experiência mobile no Módulo 9.
- **Pode haver frustrações.** Configurar git/venv/pre-commit costuma dar erros específicos por sistema operacional. Inclua troubleshooting comum em cada submódulo.
- **Tempo é maior.** Alunos podem demorar mais nesse módulo. Não pressione com gamificação agressiva.

---

## Ao Finalizar o Módulo 9

Quando o Prompt 7.5.5 estiver concluído:

1. Currículo completo do FetecPy: 9 módulos, ~70-92 horas de conteúdo
2. Suíte de testes cobrindo: backend, frontend, conteúdo, integração GitHub
3. Aluno termina com repositório profissional no GitHub (entregável real)

Próximos passos sugeridos (do `PROMPTS.md` original):
- **Prompt 8.1** — UX final
- **Prompt 8.2** — Mobile e performance
- **Prompt 8.3** — Deploy e docs

E depois disso, **divulgação e iteração com feedback real de alunos**. Boa sorte! 🐍🎓
