# Schema dos Exercícios — FetecPy

Todo exercício é um arquivo `.json` dentro de `exercises/<modulo>/exNN.json`.
O backend lê estes arquivos e os serve via API. A `solucao` **nunca** é enviada
ao frontend antes de o aluno solicitar — ela é removida na camada do controller.

---

## Campos obrigatórios

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | string | Identificador único. Formato: `"<modulo>-exNN"` (ex: `"02-ex03"`) |
| `modulo` | string | Código do módulo com dois dígitos (ex: `"02"`) |
| `ordem` | number | Posição dentro do módulo (1, 2, 3…) |
| `titulo` | string | Título curto exibido na listagem |
| `dificuldade` | string | `"facil"`, `"medio"` ou `"desafio"` |
| `xp` | number | XP base concedido ao concluir sem ajuda |
| `enunciado` | string | Texto do exercício em Markdown |
| `antes_de_codar` | array | Perguntas de reflexão exibidas antes do editor |
| `validacao` | object | Define como a resposta é avaliada (ver tipos abaixo) |

## Campos opcionais

| Campo | Tipo | Descrição |
|---|---|---|
| `conceitos` | array | Conceitos trabalhados (para filtros e revisão) |
| `dica` | string | Dica exibida ao clicar "Ver dica" |
| `codigo_inicial` | string | Código pré-preenchido no editor |
| `solucao` | string | Código de solução (removido da resposta da API antes de enviar ao aluno) |

---

## Tipos de validação (`validacao.tipo`)

### `texto_livre`
Usado nos exercícios sem código (módulo 1). O aluno escreve em linguagem natural
ou pseudocódigo. A aprovação acontece quando o texto atinge um comprimento mínimo.

```json
{
  "tipo": "texto_livre",
  "minimo_caracteres": 80,
  "instrucao": "Escreva seu pseudocódigo abaixo. Não há resposta certa única."
}
```

### `saida_exata`
Compara o `stdout` do código do aluno (via Pyodide) com o valor esperado.
Suporta múltiplos casos de teste com `input` simulado.

```json
{
  "tipo": "saida_exata",
  "casos": [
    { "input": "Maria\n",  "esperado": "Olá, Maria!\n" },
    { "input": "João\n",   "esperado": "Olá, João!\n" }
  ]
}
```

- `input`: string passada ao `stdin` do Pyodide (use `\n` para separar múltiplas entradas)
- `esperado`: saída esperada exata. O validador normaliza espaços ao final e `\r\n` → `\n`

### `funcao`
O aluno define uma função com nome específico. O sistema chama essa função
com cada conjunto de argumentos e compara o retorno.

```json
{
  "tipo": "funcao",
  "nome_funcao": "eh_par",
  "casos": [
    { "args": [4],  "esperado": true },
    { "args": [7],  "esperado": false },
    { "args": [0],  "esperado": true }
  ]
}
```

### `ast`
Verifica a estrutura do código usando `ast.parse` dentro do Pyodide.
Usado quando o exercício exige um conceito específico (ex: "use `for`, não `while`").

```json
{
  "tipo": "ast",
  "deve_conter":    ["For"],
  "nao_deve_conter": ["While"],
  "deve_chamar":    ["print"]
}
```

Nós AST válidos: `For`, `While`, `If`, `FunctionDef`, `Return`, `Import`,
`ListComp`, `DictComp`, `With`, `Try`, entre outros (qualquer `ast.NodeType`).

### `hibrido`
Combina dois tipos de validação. Ambos precisam passar.

```json
{
  "tipo": "hibrido",
  "validacoes": [
    { "tipo": "saida_exata", "casos": [...] },
    { "tipo": "ast", "deve_conter": ["For"] }
  ]
}
```

---

## Exemplo completo

```json
{
  "id": "02-ex01",
  "modulo": "02",
  "ordem": 1,
  "titulo": "Olá pessoal",
  "dificuldade": "facil",
  "xp": 10,
  "conceitos": ["input", "print", "f-string"],
  "antes_de_codar": [
    "Qual função usamos para ler o que o usuário digita?",
    "Qual função usamos para mostrar texto na tela?",
    "Como incluímos o valor de uma variável dentro de uma string?"
  ],
  "enunciado": "Pergunte o nome do usuário e cumprimente-o pelo nome.\n\nExemplo de entrada: `Maria`\nSaída esperada: `Olá, Maria!`",
  "dica": "Use `input()` para ler o nome e uma f-string para montar a saudação.",
  "codigo_inicial": "# Leia o nome do usuário\nnome = input('Digite seu nome: ')\n# Cumprimente pelo nome\n",
  "validacao": {
    "tipo": "saida_exata",
    "casos": [
      { "input": "Maria\n",  "esperado": "Olá, Maria!\n" },
      { "input": "João\n",   "esperado": "Olá, João!\n" },
      { "input": "Ana\n",    "esperado": "Olá, Ana!\n" }
    ]
  },
  "solucao": "nome = input('Digite seu nome: ')\nprint(f'Olá, {nome}!')"
}
```

---

## Convenções de nome de arquivo

```
exercises/
├── 01/
│   ├── ex01.json   ← módulo 01, exercício 1
│   ├── ex02.json
│   └── ...
├── 02/
│   ├── ex01.json
│   └── ...
└── SCHEMA.md       ← este arquivo
```
