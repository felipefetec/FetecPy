---
modulo: "02"
titulo: "Primeiros Passos em Python"
duracao_estimada: "5-7h"
pre_requisito: "01"
---

# Módulo 2 — Primeiros Passos em Python

## Por que isso importa?

Você já sabe pensar em algoritmos — no Módulo 1 escreveu passos, tomou decisões e identificou padrões. Agora vamos traduzir esse raciocínio para uma linguagem que o computador entende de verdade: Python.

Python foi escolhido por ser **simples de ler**, muito usado no mercado (análise de dados, inteligência artificial, web, automação) e incrivelmente poderoso. A maioria das pessoas que usa Python hoje começou exatamente como você: do zero.

## O que você vai aprender

- Como Python "lê" e executa seu código
- Guardar informações em variáveis
- Trabalhar com texto (`str`) e números (`int`, `float`)
- Pedir informação ao usuário com `input()`
- Mostrar resultados com `print()`
- Fazer contas e converter tipos

---

## 2.1 O que é Python?

Python é uma linguagem de programação de *alto nível* — isso significa que ela é mais próxima do inglês do que da linguagem da máquina. Você escreve instruções simples; Python se encarrega de transformá-las em sinais elétricos dentro do processador.

Quando você clica "Rodar" em um exercício aqui, o Pyodide (um Python que roda direto no navegador) lê o seu código linha por linha, de cima para baixo, e executa cada instrução.

:::dica
**Por que o código roda linha por linha?**
Porque o interpretador Python lê uma instrução, executa, lê a próxima e assim por diante. Se der erro na linha 3, as linhas 4 em diante não rodam. Isso é diferente de compilar um programa inteiro antes de rodar.
:::

---

## 2.2 Variáveis

Uma variável é um nome que você dá a um valor para poder usá-lo depois. Pense nela como uma caixinha com um rótulo.

```python
nome = "Felipe"
idade = 25
altura = 1.78
```

Aqui criamos três variáveis: `nome` guarda o texto "Felipe", `idade` guarda o número 25 e `altura` guarda 1.78.

**Regras de nomenclatura:**
- Use letras, números e `_` (underline)
- Não comece com número (`2variavel` é inválido)
- Python diferencia maiúsculas de minúsculas: `Nome` e `nome` são variáveis diferentes
- Use nomes descritivos: `preco_produto` é melhor que `p`

:::tente
```python
# Crie uma variável com o seu nome e imprima ela
seu_nome = "Ana"
print(seu_nome)
```
:::

---

## 2.3 Tipos de dados básicos

Cada variável tem um **tipo** que determina o que ela pode guardar e o que você pode fazer com ela.

| Tipo  | Nome em Python | Exemplos |
|-------|---------------|---------|
| Texto | `str`  | `"Olá"`, `'Python'` |
| Inteiro | `int` | `42`, `-7`, `0` |
| Decimal | `float` | `3.14`, `-0.5`, `1.0` |
| Verdadeiro/falso | `bool` | `True`, `False` |

Para descobrir o tipo de uma variável, use `type()`:

```python
x = 3.14
print(type(x))   # <class 'float'>
```

:::dica
**Str com aspas simples ou duplas?**
Tanto faz — `"texto"` e `'texto'` são equivalentes. A convenção é ser consistente no mesmo arquivo. Aqui usaremos aspas duplas.
:::

---

## 2.4 Operações com números

Python faz contas com os operadores que você já conhece — e mais alguns:

```python
a = 10
b = 3

print(a + b)   # 13  — soma
print(a - b)   # 7   — subtração
print(a * b)   # 30  — multiplicação
print(a / b)   # 3.333...  — divisão (resultado sempre float)
print(a // b)  # 3   — divisão inteira (descarta decimal)
print(a % b)   # 1   — resto da divisão (módulo)
print(a ** b)  # 1000 — potência (10³)
```

:::aviso
`a / b` retorna um `float` mesmo quando a divisão é exata. `10 / 2` retorna `5.0`, não `5`. Use `//` se precisar de inteiro.
:::

:::tente
```python
# Calcule quantas semanas e dias sobrando há em 100 dias
dias = 100
semanas = dias // 7
sobram  = dias % 7
print(semanas, "semanas e", sobram, "dias")
```
:::

---

## 2.5 Trabalhando com texto (strings)

Strings têm superpoderes em Python. As operações mais usadas:

```python
nome       = "Felipe"
sobrenome  = "Tavares"

# Concatenação: junta strings com +
nome_completo = nome + " " + sobrenome
print(nome_completo)           # Felipe Tavares

# f-string: insere variáveis dentro do texto (preferida!)
print(f"Olá, {nome}!")         # Olá, Felipe!
print(f"Nome: {nome_completo}, letras: {len(nome_completo)}")

# Alguns métodos úteis
print(nome.upper())            # FELIPE
print(nome.lower())            # felipe
print(nome_completo.replace("Tavares", "Silva"))  # Felipe Silva
```

:::curiosidade
**O que é f-string?**
O `f` antes das aspas (ex: `f"Olá {nome}"`) diz ao Python: "qualquer coisa dentro de `{}` é uma expressão, avalie ela". Você pode colocar contas, chamadas de função, tudo: `f"2 + 2 = {2+2}"` imprime `2 + 2 = 4`.
:::

---

## 2.6 Entrada do usuário com `input()`

Para que o programa interaja com quem está usando, `input()` pausa a execução e espera o usuário digitar algo, devolvendo sempre uma **string**:

```python
nome = input("Digite seu nome: ")
print(f"Olá, {nome}!")
```

:::aviso
**`input()` sempre retorna string!**
Se você precisa de um número, precisa converter explicitamente:
```python
idade = int(input("Sua idade: "))   # converte para int
preco = float(input("Preço: "))     # converte para float
```
Esquecer a conversão é o erro mais comum em exercícios de Python para iniciantes.
:::

---

## 2.7 Conversão de tipos

Python não converte tipos automaticamente para evitar surpresas. Você precisa ser explícito:

```python
# Problema: tentar somar string com número
nota = input("Nota: ")  # retorna "8.5" (string!)
# print(nota + 0.5)     # ERRO: não pode somar str com float

# Solução: converter antes de usar
nota = float(input("Nota: "))
print(nota + 0.5)       # 9.0 — agora funciona
```

| Função | Converte para | Exemplo |
|--------|-------------|---------|
| `int(x)` | Inteiro | `int("42")` → `42` |
| `float(x)` | Decimal | `float("3.14")` → `3.14` |
| `str(x)` | Texto | `str(100)` → `"100"` |
| `bool(x)` | Bool | `bool(0)` → `False` |

:::dica
**Quando `int()` falha?**
`int("3.14")` dá erro porque `"3.14"` não é um inteiro. Converta primeiro para float se necessário: `int(float("3.14"))` → `3`.
:::

:::tente
```python
# Peça o peso e a altura do usuário e calcule o IMC
peso   = float(input("Peso (kg): "))
altura = float(input("Altura (m): "))
imc    = peso / (altura ** 2)
print(f"Seu IMC é {imc:.1f}")
```
:::

---

## 2.8 Formatando números na saída

Quando você imprime floats, Python pode mostrar muitas casas decimais. Use f-strings com formatadores:

```python
pi = 3.141592653589793

print(f"{pi:.2f}")    # 3.14   — 2 casas decimais
print(f"{pi:.4f}")    # 3.1416 — 4 casas decimais
print(f"{1500:.0f}")  # 1500   — sem casas decimais
```

---

## Exercícios

Os exercícios deste módulo são de Python básico — variáveis, `input()`, operações e `print()`. Antes de abrir o editor, responda mentalmente:

1. Quais são as entradas do problema?
2. Que tipo de dado cada uma tem (`int`, `float`, `str`)?
3. Quais cálculos preciso fazer?
4. Como vou formatar a saída?

---

## Mini-projeto: Calculadora de Quatro Operações

Escreva um programa que:
1. Pede dois números ao usuário
2. Mostra o resultado das quatro operações (soma, subtração, multiplicação, divisão)
3. Trata o caso de divisão por zero mostrando uma mensagem amigável

Exemplo de saída esperada:
```
Número 1: 10
Número 2: 4
Soma:          14.0
Subtração:     6.0
Multiplicação: 40.0
Divisão:       2.5
```

---

## Resumo

✔ Variáveis guardam valores com um nome  
✔ Tipos básicos: `str`, `int`, `float`, `bool`  
✔ `input()` sempre devolve string — converta antes de calcular  
✔ f-strings facilitam montar mensagens com variáveis  
✔ `//` divisão inteira, `%` resto, `**` potência  

## Próximos passos

No Módulo 3 você vai aprender a fazer o código *tomar decisões* — coisas como "se o usuário errou a senha, mostre um aviso". Para isso vamos usar `if`, `elif` e `else`.
