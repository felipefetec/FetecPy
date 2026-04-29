# FetecPy — Python para Iniciantes
## Plano de Estudos Completo

> **Princípio pedagógico central:** *Pense antes de codar.* Em todos os módulos, antes de qualquer exercício prático, o aluno é guiado a entender o problema, identificar entradas, saídas e passos lógicos. A programação é consequência do raciocínio, não o ponto de partida.

---

## Visão Geral

**Público-alvo:** iniciantes absolutos em programação. Não exige conhecimento prévio.

**Duração estimada:** 60-80 horas de estudo (varia conforme o aluno).

**Formato:** plataforma web com execução de Python no navegador (Pyodide), exercícios interativos, progressão salva.

**Filosofia:** algoritmos e raciocínio computacional vêm antes da sintaxe. Cada conceito de Python é introduzido depois que o aluno já entendeu o problema que ele resolve.

---

## Estrutura de Cada Módulo

Todos os módulos seguem a mesma estrutura, exceto o Módulo 1 (puramente teórico/algorítmico):

```
1. Introdução          — Por que isso importa? O que vou aprender?
2. Pensando no problema — Casos reais onde esse conceito aparece
3. Teoria              — Conceitos novos (curtos, com exemplos)
4. Exemplo guiado      — Código comentado linha a linha
5. Exercícios (3-5)    — Cada um com "Antes de codar" + editor + testes
6. Mini-projeto        — Integra tudo do módulo
7. Quiz conceitual     — 3-5 perguntas de múltipla escolha
8. Resumo e próximos passos
```

### Seção "Antes de Codar" (presente em todo exercício)

Antes de o editor de código aparecer, o aluno responde mentalmente (ou em campos de texto opcionais):

1. **Quais são as entradas do problema?**
2. **Quais são as saídas esperadas?**
3. **Quais os passos para resolver?** (em português, não em código)
4. **Quais casos extremos preciso considerar?** (zero, negativo, vazio, etc.)

Só depois disso o editor é destravado. Essa seção é **obrigatória** nos módulos 1-5 e **opcional** dos módulos 6+.

---

# Currículo Detalhado

## Módulo 1 — Lógica e Algoritmos
**Duração estimada:** 4-6 horas
**Pré-requisito:** nenhum

### Objetivos
- Entender o que é um algoritmo
- Decompor problemas em passos
- Representar fluxos com pseudocódigo e fluxogramas
- Pensar em condições, repetições e variáveis sem código

### Tópicos

**1.1 O que é programar?**
- Programação como sequência de instruções
- Computador como executor literal (não pensa, só obedece)
- Analogia: receita de bolo

**1.2 Algoritmos no dia a dia**
- Trocar uma lâmpada
- Fazer café
- Atravessar a rua
- Decompor cada um em passos numerados

**1.3 Variáveis (conceito puro, sem Python ainda)**
- "Caixinha com nome" que guarda um valor
- Exemplo: `idade = 25`, `nome = "Maria"`
- Mudar o valor da caixinha

**1.4 Condicionais (lógica)**
- "Se... então... senão"
- Exemplo: "Se choveu, levo guarda-chuva, senão não levo"
- Operadores de comparação: maior, menor, igual

**1.5 Repetições (lógica)**
- "Enquanto não chegar, continue andando"
- "Para cada item da lista, faça X"
- Diferença conceitual entre as duas formas

**1.6 Pseudocódigo e fluxogramas**
- Como representar algoritmos antes de codar
- Símbolos básicos de fluxograma (início, fim, decisão, processo)
- Pseudocódigo em português estruturado

### Exercícios (todos sem código real)

**Ex 1.1 — Algoritmo da troca de pneu** *(fácil)*
Liste em pseudocódigo os passos para trocar um pneu furado.

**Ex 1.2 — Maior de dois números** *(fácil)*
Escreva em pseudocódigo: dado dois números, qual é o maior?

**Ex 1.3 — Soma de 1 a 10** *(médio)*
Escreva em pseudocódigo: somar todos os números de 1 a 10.

**Ex 1.4 — Adivinha o número** *(médio)*
Pseudocódigo: o computador "pensa" num número e o usuário tenta adivinhar, recebendo dicas "maior" ou "menor" até acertar.

**Ex 1.5 — Classificador de idades** *(desafio)*
Pseudocódigo: ler 5 idades e classificar cada uma como criança (<12), adolescente (12-17), adulto (18-59) ou idoso (60+).

### Mini-projeto
Construir o pseudocódigo completo de um caixa eletrônico simples (login com senha, ver saldo, sacar, depositar, sair). Fluxograma desenhado num canvas SVG interativo na própria plataforma.

### Quiz
- O que é um algoritmo?
- Qual a diferença entre "se" e "enquanto"?
- O que é uma variável?
- O que acontece se a condição de uma repetição nunca for falsa?

---

## Módulo 2 — Primeiros Passos em Python
**Duração estimada:** 5-7 horas
**Pré-requisito:** Módulo 1

### Objetivos
- Escrever e rodar o primeiro código Python
- Entender variáveis, tipos e operadores
- Trabalhar com entrada e saída

### Tópicos

**2.1 Olá, mundo**
- O que é o Python e por que escolher
- `print()` — primeira função
- Como rodar o código (no editor da plataforma)

**2.2 Comentários**
- `#` para comentários de linha
- Quando comentar e quando o código fala por si

**2.3 Variáveis e tipos básicos**
- `int`, `float`, `str`, `bool`
- Atribuição com `=`
- Convenção de nomes (snake_case)
- `type()` para descobrir o tipo

**2.4 Operadores aritméticos**
- `+`, `-`, `*`, `/`, `//` (divisão inteira), `%` (resto), `**` (potência)
- Precedência e parênteses

**2.5 Strings — primeiro contato**
- Aspas simples e duplas
- Concatenação com `+`
- F-strings: `f"Olá, {nome}!"`

**2.6 Entrada do usuário**
- `input()` — sempre retorna string
- Conversão com `int()`, `float()`

### Exercícios

**Ex 2.1 — Olá pessoal** *(fácil)*
Pergunte o nome do usuário e cumprimente-o pelo nome.

**Ex 2.2 — Calculadora de IMC** *(fácil)*
Pergunte peso e altura, calcule e imprima o IMC.

**Ex 2.3 — Conversor de temperatura** *(médio)*
Pergunte uma temperatura em Celsius e converta para Fahrenheit (`F = C * 9/5 + 32`).

**Ex 2.4 — Troco** *(médio)*
Pergunte o valor da compra e o valor pago. Imprima o troco.

**Ex 2.5 — Conversor de tempo** *(desafio)*
Pergunte um número de segundos e imprima quantas horas, minutos e segundos isso representa.

### Mini-projeto: Calculadora de Quatro Operações
Programa que pergunta dois números e uma operação (+, -, *, /), e imprime o resultado. Não usa `if` ainda — desafio é resolver com criatividade (ex: dicionário, ou pedir a operação como string e concatenar).

### Quiz
- Qual a diferença entre `5/2` e `5//2`?
- O que `input()` sempre retorna?
- O que é uma f-string?
- `'3' + '4'` resulta em quê?

---

## Módulo 3 — Estruturas de Controle
**Duração estimada:** 7-9 horas
**Pré-requisito:** Módulo 2

### Objetivos
- Tomar decisões no código com `if/elif/else`
- Repetir ações com `while` e `for`
- Combinar condições com operadores lógicos

### Tópicos

**3.1 Operadores de comparação**
- `==`, `!=`, `<`, `>`, `<=`, `>=`
- Resultado é sempre `bool`

**3.2 Condicionais**
- `if`, `elif`, `else`
- Indentação como sintaxe (não decoração)
- Aninhamento

**3.3 Operadores lógicos**
- `and`, `or`, `not`
- Tabela-verdade
- Curto-circuito

**3.4 Laço `while`**
- Quando usar
- Cuidado com loops infinitos
- `break` e `continue`

**3.5 Laço `for` com `range`**
- `range(n)`, `range(a, b)`, `range(a, b, passo)`
- Diferença entre `for` e `while`

**3.6 Booleanos e valores "truthy"**
- `True`, `False`
- Valores que são considerados "falsos": `0`, `""`, `None`, `[]`

### Exercícios

**Ex 3.1 — Par ou ímpar** *(fácil)*
Pergunte um número e diga se é par ou ímpar.

**Ex 3.2 — Maior de três** *(fácil)*
Pergunte três números e imprima o maior.

**Ex 3.3 — Tabuada interativa** *(médio)*
Pergunte um número e imprima sua tabuada de 1 a 10.

**Ex 3.4 — Validador de senha** *(médio)*
Peça uma senha. Continue pedindo até o usuário digitar "python123". Limite a 5 tentativas.

**Ex 3.5 — FizzBuzz** *(desafio)*
Imprima de 1 a 50. Para múltiplos de 3, imprima "Fizz". Múltiplos de 5: "Buzz". Múltiplos de ambos: "FizzBuzz".

### Mini-projeto: Jogo de Adivinhação
Computador "pensa" num número entre 1 e 100 (use `random.randint`). Usuário tenta adivinhar. A cada tentativa errada, sistema diz "maior" ou "menor". Conta tentativas e exibe ao final. Limite 10 tentativas.

### Quiz
- Como Python sabe onde termina um bloco `if`?
- `True and False or True` resulta em quê?
- Diferença entre `break` e `continue`?
- Quando preferir `for` em vez de `while`?

---

## Módulo 4 — Estruturas de Dados
**Duração estimada:** 8-10 horas
**Pré-requisito:** Módulo 3

### Objetivos
- Armazenar coleções de valores em listas, tuplas, dicionários e sets
- Iterar e manipular essas coleções
- Escolher a estrutura certa para cada problema

### Tópicos

**4.1 Listas**
- Criar: `[]`, `[1, 2, 3]`
- Acessar por índice (positivo e negativo)
- Modificar: `lista[0] = ...`
- Métodos: `append`, `remove`, `pop`, `sort`, `len`
- Slicing: `lista[1:3]`

**4.2 Iterar listas**
- `for item in lista:`
- `for i, item in enumerate(lista):`
- `for a, b in zip(lista1, lista2):`

**4.3 Tuplas**
- Criar: `(1, 2, 3)` ou `1, 2, 3`
- Imutáveis — quando usar
- Desempacotamento: `a, b = 10, 20`

**4.4 Dicionários**
- Criar: `{"nome": "Ana", "idade": 30}`
- Acessar e modificar: `pessoa["nome"]`
- Métodos: `keys`, `values`, `items`, `get`
- Iterar: `for chave, valor in d.items():`

**4.5 Sets**
- Criar: `{1, 2, 3}`
- Sem duplicatas
- Operações: união, interseção, diferença
- Quando usar (verificação rápida de pertencimento)

**4.6 Quando usar cada uma**
- Ordem importa? → lista ou tupla
- Vai mudar? → lista (não tupla)
- Buscar por chave? → dicionário
- Só quero saber se pertence? → set

### Exercícios

**Ex 4.1 — Soma da lista** *(fácil)*
Dada uma lista de números, calcule a soma sem usar `sum()`.

**Ex 4.2 — Maior e menor** *(fácil)*
Dada uma lista, encontre o maior e o menor sem usar `max()` ou `min()`.

**Ex 4.3 — Contador de palavras** *(médio)*
Dada uma frase, conte quantas vezes cada palavra aparece. Retorne um dicionário.

**Ex 4.4 — Removendo duplicatas** *(médio)*
Dada uma lista com duplicatas, retorne uma nova lista sem repetições, mantendo a ordem original.

**Ex 4.5 — Inversão de dicionário** *(desafio)*
Dado um dicionário `{"a": 1, "b": 2}`, retorne `{1: "a", 2: "b"}`.

### Mini-projeto: Agenda de Contatos
Programa em loop que permite: adicionar contato (nome → telefone), buscar contato pelo nome, listar todos, remover. Tudo em memória (sem arquivos ainda). Use dicionário como estrutura principal.

### Quiz
- Diferença entre lista e tupla?
- Quando usar dicionário em vez de lista?
- O que faz `lista[-1]`?
- Por que sets não permitem duplicatas?

---

## Módulo 5 — Funções e Modularização
**Duração estimada:** 7-9 horas
**Pré-requisito:** Módulo 4

### Objetivos
- Criar funções para reutilizar código
- Entender parâmetros, retorno e escopo
- Organizar código em módulos

### Tópicos

**5.1 Por que funções?**
- DRY (Don't Repeat Yourself)
- Funções como abstrações: você usa sem saber como funcionam por dentro

**5.2 Definir e chamar funções**
- `def nome(parametros):`
- `return`
- Função sem `return` retorna `None`

**5.3 Parâmetros**
- Posicionais
- Nomeados (keyword arguments)
- Valores padrão: `def saudar(nome, saudacao="Olá"):`
- `*args` e `**kwargs` (introdução leve)

**5.4 Escopo**
- Variáveis locais vs globais
- O que acontece dentro da função, fica na função
- `global` (e por que evitar)

**5.5 Funções como primeira classe**
- Funções podem ser passadas como argumento
- Funções podem retornar funções (introdução leve)
- `lambda` (introdução, sem aprofundar)

**5.6 Módulos**
- `import math`, `from math import sqrt`
- Criar e importar seus próprios arquivos `.py`
- Convenção: um arquivo = um módulo

### Exercícios

**Ex 5.1 — Função saudação** *(fácil)*
Crie uma função `saudar(nome)` que retorna "Olá, NOME!".

**Ex 5.2 — Função fatorial** *(médio)*
Crie uma função `fatorial(n)` que calcula n! (use loop, não recursão ainda).

**Ex 5.3 — Função primo** *(médio)*
Crie `eh_primo(n)` que retorna `True` se n é primo, `False` caso contrário.

**Ex 5.4 — Calculadora flexível** *(médio)*
Crie `calcular(a, b, operacao="soma")`. `operacao` pode ser "soma", "sub", "mult", "div".

**Ex 5.5 — Validador de CPF (sem dígito verificador ainda)** *(desafio)*
Crie `formatar_cpf(cpf)` que recebe `"12345678900"` e retorna `"123.456.789-00"`.

### Mini-projeto: Validador de CPF Completo
Funções: `limpar_cpf`, `validar_tamanho`, `calcular_digito_verificador`, `validar_cpf`. Cada função faz uma coisa só. Mostra como decompor um problema em funções pequenas.

### Quiz
- O que `return` faz?
- Diferença entre parâmetro e argumento?
- O que acontece com variáveis criadas dentro de uma função?
- Por que módulos são úteis?

---

## Módulo 6 — Programação Orientada a Objetos
**Duração estimada:** 8-10 horas
**Pré-requisito:** Módulo 5

### Objetivos
- Modelar entidades do mundo real como classes
- Entender atributos, métodos e instâncias
- Aplicar herança e encapsulamento

### Tópicos

**6.1 Por que POO?**
- Quando dicionários e funções não bastam
- Modelar entidades com comportamento
- Exemplo: conta bancária (estado + ações)

**6.2 Classes e objetos**
- `class Pessoa:`
- `__init__` (construtor)
- `self` — o que é, por que existe
- Criar instâncias: `p = Pessoa("Ana", 30)`

**6.3 Atributos e métodos**
- Atributos de instância vs de classe
- Métodos = funções dentro de classe
- `__str__` e `__repr__`

**6.4 Encapsulamento**
- Convenção `_atributo` (privado)
- Properties: `@property`, `@x.setter`
- Por que esconder detalhes

**6.5 Herança**
- `class Cachorro(Animal):`
- `super()` para chamar o pai
- Sobrescrever métodos

**6.6 Polimorfismo (introdução leve)**
- Mesma interface, comportamentos diferentes
- Duck typing em Python

### Exercícios

**Ex 6.1 — Classe Pessoa** *(fácil)*
Crie `Pessoa` com `nome`, `idade` e método `apresentar()`.

**Ex 6.2 — Classe Retângulo** *(fácil)*
Crie `Retangulo` com `largura`, `altura` e métodos `area()`, `perimetro()`.

**Ex 6.3 — Conta bancária** *(médio)*
Crie `Conta` com `saldo` (privado), métodos `depositar`, `sacar`, `extrato`.

**Ex 6.4 — Herança: Animal → Cachorro/Gato** *(médio)*
Classe base `Animal` com método `falar()` (genérico). `Cachorro` e `Gato` sobrescrevem.

**Ex 6.5 — Sistema de biblioteca** *(desafio)*
Classes `Livro`, `Usuario`, `Biblioteca`. Métodos para emprestar, devolver, listar disponíveis.

### Mini-projeto: Sistema Bancário
Classes: `Cliente`, `Conta` (base), `ContaCorrente` (com cheque especial), `ContaPoupanca` (com rendimento), `Banco` (gerencia clientes e contas). Loop interativo para operações.

### Quiz
- O que é `self`?
- Diferença entre classe e instância?
- Para que serve `super()`?
- Por que usar properties em vez de acesso direto ao atributo?

---

## Módulo 7 — Arquivos, JSON e Tratamento de Erros
**Duração estimada:** 6-8 horas
**Pré-requisito:** Módulo 6

### Objetivos
- Ler e escrever arquivos de texto e JSON
- Tratar erros graciosamente
- Persistir dados entre execuções

### Tópicos

**7.1 Arquivos de texto**
- `open(caminho, modo)`
- Modos: `"r"`, `"w"`, `"a"`
- `with open(...) as f:` (gerenciador de contexto)
- `read`, `readline`, `readlines`, `write`

**7.2 JSON**
- O que é JSON e por que usar
- `json.load`, `json.dump`
- `json.loads`, `json.dumps`
- Mapeamento Python ↔ JSON

**7.3 Tratamento de erros**
- `try / except / else / finally`
- Tipos comuns: `ValueError`, `TypeError`, `FileNotFoundError`, `KeyError`
- Capturar tipos específicos vs `except:`
- `raise` para lançar erros

**7.4 Erros customizados**
- Criar classes que herdam de `Exception`
- Quando vale a pena

### Exercícios

**Ex 7.1 — Contador de linhas** *(fácil)*
Receba um caminho de arquivo e conte quantas linhas tem.

**Ex 7.2 — Salvador de notas** *(fácil)*
Pergunte uma nota ao usuário, salve em `notas.txt` (uma por linha).

**Ex 7.3 — Calculadora robusta** *(médio)*
Calculadora que trata divisão por zero e entradas inválidas com `try/except`.

**Ex 7.4 — Cadastro em JSON** *(médio)*
Cadastrar pessoas (nome, idade) e salvar em `pessoas.json`. Carregar ao iniciar.

**Ex 7.5 — Migrador de formato** *(desafio)*
Ler um CSV simples e converter para JSON.

### Mini-projeto: Caderno de Notas Persistente
App de notas em terminal: criar, listar, editar, deletar notas. Cada nota tem título, conteúdo, data. Salva tudo em JSON. Recupera ao iniciar. Trata todos os erros possíveis (arquivo corrompido, não existe, etc).

### Quiz
- Por que usar `with open` em vez de `open` direto?
- Diferença entre `json.load` e `json.loads`?
- O que `finally` faz?
- Quando criar uma exceção customizada?

---

## Módulo 8 — Construindo Aplicações Reais
**Duração estimada:** 20-25 horas (5 submódulos)
**Pré-requisito:** Módulo 7

### Estrutura
Esse módulo é dividido em 5 submódulos. Cada um tem seu próprio mini-projeto e roda num ambiente diferente:

- **Plataforma:** validação via análise estática + execução parcial via Pyodide quando possível
- **Local:** aluno baixa repo template, configura Python local, roda o projeto

### Submódulo 8.1 — Consumindo APIs com `requests`
**Roda em:** Pyodide (via `pyodide.http`)
**Duração:** 3-4h

**Tópicos:**
- O que é uma API REST
- Métodos HTTP: GET, POST, PUT, DELETE
- Status codes (200, 404, 500)
- Trabalhar com JSON de resposta
- `pyodide.http.pyfetch` (no browser) e `requests` (local)

**Exercícios:**
- Buscar cotação do dólar (API Awesome API)
- Buscar dados de CEP (ViaCEP)
- Buscar repositórios do GitHub de um usuário
- Buscar piada aleatória (icanhazdadjoke)

**Mini-projeto:** Consultor de CEP completo — interface simples no browser que pega CEP e mostra endereço completo, formatado.

### Submódulo 8.2 — Streamlit
**Roda em:** local (repo template)
**Duração:** 4-5h

**Tópicos:**
- O que é Streamlit
- Componentes: `st.title`, `st.text_input`, `st.slider`, `st.button`
- Plotagem com `st.line_chart`, `st.bar_chart`
- Estado com `st.session_state`
- Deploy fácil (Streamlit Cloud)

**Exercícios:**
- Calculadora visual
- Conversor de moedas com gráfico histórico
- Visualizador de notas (ler CSV, mostrar gráfico)

**Mini-projeto:** Dashboard de Análise de Vendas — lê CSV de vendas fictício, mostra filtros, gráficos, totalizadores.

### Submódulo 8.3 — Flask
**Roda em:** local (repo template)
**Duração:** 4-5h

**Tópicos:**
- Servidores web e rotas
- Flask: setup mínimo
- Rotas: `@app.route("/")`
- Templates Jinja2 (introdução)
- Métodos GET vs POST
- Forms simples

**Exercícios:**
- Hello world em Flask
- Rota com parâmetro: `/oi/<nome>`
- Formulário de contato
- API simples retornando JSON

**Mini-projeto:** Mural de Recados — site Flask onde qualquer um deixa recado, vê todos os recados anteriores. Persistência em JSON.

### Submódulo 8.4 — Flet
**Roda em:** local (repo template)
**Duração:** 4-5h

**Tópicos:**
- O que é Flet (Flutter + Python)
- Componentes: Text, TextField, ElevatedButton, Column, Row
- Eventos (on_click)
- Estado em Flet
- Roda como app desktop ou web

**Exercícios:**
- Calculadora visual
- Lista de tarefas (to-do)
- Cronômetro

**Mini-projeto:** App de Lista de Tarefas (To-Do) — tarefas com prazos, marcação de concluído, persistência em JSON local. Funciona como desktop.

### Submódulo 8.5 — FastAPI
**Roda em:** local (repo template)
**Duração:** 5-6h

**Tópicos:**
- O que é uma API moderna
- FastAPI vs Flask
- Tipagem com Pydantic
- Documentação automática (Swagger)
- Métodos: GET, POST, PUT, DELETE
- Async (introdução leve)

**Exercícios:**
- API "Hello World"
- API de cadastro de usuários (em memória)
- Validação com Pydantic
- API com SQLite

**Mini-projeto Final do Curso:** API de Biblioteca + Frontend Flet — backend FastAPI gerencia livros (CRUD), frontend Flet consome a API. Integra tudo do curso. Aluno termina com um app real que ele mesmo construiu.

---

## Sistema de Validação de Exercícios

### Tipo A — Saída exata
Compara stdout do código do aluno com saída esperada. Usado em ~70% dos exercícios dos módulos 2-3.

```json
{
  "tipo": "saida_exata",
  "input_simulado": "5\n",
  "saida_esperada": "Você digitou: 5\n"
}
```

### Tipo B — Testes unitários
Aluno implementa função, sistema testa com vários inputs. Usado em módulos 5+.

```json
{
  "tipo": "funcao",
  "nome_funcao": "eh_par",
  "casos": [
    { "args": [2], "esperado": true },
    { "args": [7], "esperado": false },
    { "args": [0], "esperado": true }
  ]
}
```

### Tipo C — Análise estática (AST)
Verifica se código contém estruturas específicas. Usado quando o exercício força um conceito.

```json
{
  "tipo": "ast",
  "regras": [
    { "deve_conter": "for" },
    { "nao_deve_conter": "while" },
    { "deve_chamar": "range" }
  ]
}
```

### Modelo híbrido para encerrar exercício

- Aluno tenta resolver
- Após **3 tentativas inválidas**, sistema oferece "Ver dica" (não a solução, só um empurrão)
- Após **5 tentativas inválidas** ou clique explícito, oferece "Ver solução"
- Ver solução marca como "concluído com ajuda" (XP reduzido em 50%)
- Aluno pode pular sem ver solução: marca como "tentado", não dá XP

---

## Sistema de Gamificação

### XP (Pontos de Experiência)

| Atividade | XP |
|---|---|
| Exercício fácil resolvido | 10 |
| Exercício médio resolvido | 20 |
| Exercício difícil/desafio resolvido | 30 |
| Exercício resolvido com ajuda | 50% do valor |
| Quiz acertado de primeira | 15 |
| Mini-projeto concluído | 50 |
| Módulo completo (todos os itens) | 100 (bônus) |
| Streak diário (1 exercício no dia) | 5 (multiplicador acumulativo até 7 dias) |

### Streak

- Conta dias consecutivos com **pelo menos 1 exercício resolvido**
- Streak de 7 dias = multiplicador de XP +20%
- Streak de 30 dias = +50%
- Quebrou 1 dia → reseta

### Badges (15 no total)

| Badge | Critério |
|---|---|
| 🐍 Primeiro código | Primeiro `print` rodando |
| 🔥 Streak 3 dias | 3 dias consecutivos |
| 🔥🔥 Streak 7 dias | 7 dias consecutivos |
| 🔥🔥🔥 Streak 30 dias | 30 dias consecutivos |
| 🎯 Sniper | 5 quizzes seguidos sem errar |
| 📚 Estudante dedicado | 10 exercícios resolvidos |
| 🏆 Mestre do módulo | Primeiro módulo completo |
| 🎓 Formado | Todos os módulos concluídos |
| 🚀 Pyodide Master | Usou todas as bibliotecas dos exemplos |
| 🐛 Caçador de bugs | Resolveu exercício após 3+ tentativas |
| ⚡ Velocista | Exercício resolvido em menos de 1 minuto |
| 🌙 Coruja | Estudou depois das 23h |
| ☀️ Madrugador | Estudou antes das 7h |
| 💎 Persistente | Voltou após 7 dias sem estudar |
| 🎨 Criativo | Solução com estrutura diferente da padrão (validação por AST) |

---

## Estrutura de Dados (Banco)

### Tabela `users`
```sql
CREATE TABLE users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  nome TEXT NOT NULL,
  sobrenome TEXT NOT NULL,
  chave TEXT UNIQUE NOT NULL,  -- "felipe_silva" (normalizado)
  pin_hash TEXT NOT NULL,       -- bcrypt
  xp_total INTEGER DEFAULT 0,
  streak_dias INTEGER DEFAULT 0,
  ultimo_acesso DATE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### Tabela `progress`
```sql
CREATE TABLE progress (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  modulo TEXT NOT NULL,         -- "03"
  item_tipo TEXT NOT NULL,      -- "secao" | "exercicio" | "quiz" | "projeto"
  item_id TEXT NOT NULL,        -- "ex02"
  status TEXT NOT NULL,         -- "concluido" | "concluido_com_ajuda" | "tentado"
  tentativas INTEGER DEFAULT 0,
  codigo_salvo TEXT,            -- código do aluno
  xp_ganho INTEGER DEFAULT 0,
  concluido_em DATETIME,
  UNIQUE(user_id, modulo, item_tipo, item_id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Tabela `badges`
```sql
CREATE TABLE user_badges (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  badge_id TEXT NOT NULL,       -- "primeiro_codigo"
  conquistado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(user_id, badge_id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Tabela `sessions` (opcional, simplifica auth)
```sql
CREATE TABLE sessions (
  token TEXT PRIMARY KEY,
  user_id INTEGER NOT NULL,
  expires_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## Endpoints da API (PHP)

```
POST   /api/auth/login      — { nome, sobrenome, pin } → { token, user }
GET    /api/me              — retorna dados do usuário logado
GET    /api/modules         — lista de módulos
GET    /api/modules/:id     — conteúdo de um módulo
GET    /api/progress        — progresso do usuário
POST   /api/progress        — { modulo, item_tipo, item_id, status, codigo, tentativas }
GET    /api/badges          — badges do usuário + lista de todas disponíveis
GET    /api/leaderboard     — top 10 por XP (opcional)
```

---

## Resumo de Entregáveis

### Para o aluno (UX)
- Tela de login simples (nome, sobrenome, PIN)
- Dashboard com módulos, progresso, XP, badges, streak
- Página do módulo: conteúdo + editor + execução + testes
- Editor de código com syntax highlighting (CodeMirror 6)
- Console de saída
- Botões "Antes de codar", "Rodar", "Testar", "Ver dica", "Ver solução"
- Notificações de XP ganho, badge desbloqueada, streak

### Para o desenvolvedor (você)
- Conteúdo dos módulos em arquivos `.md` versionados
- Exercícios em arquivos `.json` (1 por exercício)
- Backend PHP minimalista (~500 linhas)
- Frontend SPA leve (~1500 linhas HTML/JS)
- Banco SQLite com schema migrável
- Deploy: copiar pasta pro host, rodar uma vez `php install.php` pra criar tabelas

---

## Princípios Pedagógicos Transversais

1. **Pensar antes de codar** — sempre, em todos os módulos
2. **Algoritmos primeiro** — sintaxe é consequência
3. **Repetição espaçada** — conceitos antigos reaparecem em exercícios novos
4. **Feedback imediato** — testes rodam no browser instantaneamente
5. **Erro é parte do processo** — sistema não pune, ensina
6. **Mini-projetos integradores** — cada módulo termina com algo "que funciona"
7. **Recompensa visível** — XP, badges, streak motivam continuidade
8. **Progressão própria** — cada aluno no seu ritmo, sem prazos
