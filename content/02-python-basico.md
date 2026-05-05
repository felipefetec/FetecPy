---
modulo: "02"
titulo: "Primeiros Passos em Python"
duracao_estimada: "5-7h"
pre_requisito: "01"
---

# Módulo 2 — Primeiros Passos em Python

## De algoritmo para código

Você já sabe pensar em algoritmos — no Módulo 1 escreveu passos, tomou decisões e identificou padrões. Agora vamos traduzir esse raciocínio para uma linguagem que o computador entende de verdade: Python.

Python foi escolhido por três razões: é fácil de ler, domina áreas como análise de dados e automação, e tem uma das maiores comunidades do mundo. Daqui para frente, tudo que você escreveu em pseudocódigo vai começar a ter uma forma real.

## O que você vai aprender

- Como Python lê e executa seu código
- Comentários — documentando o raciocínio
- Guardar informações em variáveis
- Tipos de dados: `str`, `int`, `float`, `bool`, `None`
- Operações com números e atribuição composta
- Trabalhar com texto — strings e seus métodos
- Pedir informação ao usuário com `input()`
- Converter tipos e formatar saída

---

## 2.1 O que é Python?

Python é uma linguagem de programação de *alto nível* — mais próxima do inglês do que da linguagem da máquina. Você escreve instruções simples; Python se encarrega de transformá-las em instruções que o processador entende.

Quando você clica "Rodar" em um exercício aqui, o Pyodide (um Python que roda direto no navegador) lê seu código linha por linha, de cima para baixo, e executa cada instrução. Se der erro na linha 3, as linhas 4 em diante não rodam.

---

## 2.2 Comentários

Comentários são linhas que o Python ignora completamente — existem só para quem lê o código:

```python
# Isso é um comentário — o Python não executa essa linha

preco = 49.90    # comentário no fim da linha também funciona

# Você pode comentar código temporariamente para testar:
# print("isso não vai rodar")
print("isso vai rodar")
```

Comente o **porquê** de uma decisão, não o **o quê** — o código já mostra o quê:

```python
# ❌ Comentário inútil — o código já diz isso
total = preco * quantidade  # multiplica preco por quantidade

# ✅ Comentário útil — explica a razão
desconto = 0.1   # 10% para clientes com mais de 5 compras no mês
```

---

## 2.3 Variáveis

Uma variável é um nome que você dá a um valor para poder usá-lo depois:

```python
nome   = "Felipe"
idade  = 25
altura = 1.78
```

**Regras de nomenclatura:**
- Use letras, números e `_` — não comece com número
- Python diferencia maiúsculas: `Nome` e `nome` são variáveis diferentes
- Use nomes descritivos: `preco_produto` é melhor que `p`
- Convenção Python: `snake_case` (palavras separadas por `_`)

**Atribuição composta** — atalho para atualizar o valor de uma variável:

```python
contador = 0
contador += 1    # equivale a: contador = contador + 1
contador += 1    # agora contador = 2

saldo = 100.0
saldo -= 30      # saldo = 70.0
saldo *= 1.05    # saldo = 73.5 (rendimento de 5%)
saldo //= 1      # saldo = 73.0 (divisão inteira)
```

:::tente
```python
pontos = 0
pontos += 10   # acertou uma questão
pontos += 20   # acertou outra
pontos -= 5    # penalidade
print(pontos)  # 25
```
:::

---

## 2.4 Tipos de dados

Cada valor tem um tipo que determina o que você pode fazer com ele:

| Tipo | Nome em Python | Exemplos |
|------|---------------|---------|
| Texto | `str` | `"Olá"`, `'Python'` |
| Inteiro | `int` | `42`, `-7`, `0` |
| Decimal | `float` | `3.14`, `-0.5`, `1.0` |
| Verdadeiro/falso | `bool` | `True`, `False` |
| Ausência de valor | `None` | `None` |

```python
print(type("texto"))   # <class 'str'>
print(type(42))        # <class 'int'>
print(type(3.14))      # <class 'float'>
print(type(True))      # <class 'bool'>
print(type(None))      # <class 'NoneType'>
```

### `None` — ausência de valor

`None` representa "nenhum valor" — diferente de zero ou string vazia:

```python
resultado = None   # ainda não temos resultado

# Aparece quando uma função não retorna nada explicitamente
def sem_retorno():
    print("oi")

r = sem_retorno()
print(r)   # None
```

### Valores truthy e falsy

Em Python, qualquer valor pode ser testado como `True` ou `False` num `if`. São **falsy** (equivalem a False): `0`, `0.0`, `""`, `[]`, `{}`, `None`. Todo o resto é **truthy**:

```python
if 0:
    print("não imprime — 0 é falsy")

if "texto":
    print("imprime — string não-vazia é truthy")

lista = []
if not lista:
    print("lista vazia!")   # imprime
```

Isso permite escrever condições mais limpas:

```python
# Em vez de: if len(nome) > 0:
if nome:
    print(f"Olá, {nome}!")
```

---

## 2.5 Operações com números

```python
a = 10
b = 3

print(a + b)   # 13   — soma
print(a - b)   # 7    — subtração
print(a * b)   # 30   — multiplicação
print(a / b)   # 3.333...  — divisão (resultado sempre float)
print(a // b)  # 3    — divisão inteira (descarta decimal)
print(a % b)   # 1    — resto da divisão (módulo)
print(a ** b)  # 1000 — potência (10³)
```

:::aviso
`a / b` retorna `float` mesmo quando a divisão é exata: `10 / 2` retorna `5.0`. Use `//` se precisar de inteiro.
:::

:::tente
```python
# Calcule semanas e dias em 100 dias
dias    = 100
semanas = dias // 7
sobram  = dias % 7
print(semanas, "semanas e", sobram, "dias")
```
:::

---

## 2.6 Trabalhando com texto (strings)

### Criando strings

```python
simples   = "Olá, mundo!"
aspas     = 'também funciona'
multiline = """Texto
em várias
linhas"""
```

### f-strings — a forma preferida de montar texto

```python
nome  = "Felipe"
nota  = 8.567

print(f"Olá, {nome}!")                  # Olá, Felipe!
print(f"Nota: {nota:.2f}")              # Nota: 8.57
print(f"Dobro: {nota * 2:.1f}")         # Dobro: 17.1
print(f"Tipo: {type(nome).__name__}")   # Tipo: str
```

### Métodos de string mais usados

```python
texto = "  Olá, Mundo!  "

print(texto.strip())          # "Olá, Mundo!"  — remove espaços das bordas
print(texto.lower())          # "  olá, mundo!  "
print(texto.upper())          # "  OLÁ, MUNDO!  "
print(texto.replace("Mundo", "Python"))  # "  Olá, Python!  "

frase = "Python é incrível"
print(frase.startswith("Python"))  # True
print(frase.endswith("incrível"))  # True
print(frase.count("i"))            # 2
print(frase.find("é"))             # 7 — índice da primeira ocorrência
```

### `split()` e `join()` — separar e juntar

```python
# split: divide a string em lista
frase = "Ana Bruno Carlos"
nomes = frase.split()           # ['Ana', 'Bruno', 'Carlos'] — divide por espaço
partes = "a,b,c".split(",")    # ['a', 'b', 'c'] — divide por vírgula

# join: junta lista em string
print(", ".join(nomes))         # "Ana, Bruno, Carlos"
print(" | ".join(partes))       # "a | b | c"
print("".join(["P","y","t"]))   # "Pyt"
```

:::curiosidade
`split()` e `join()` são complementares. `split()` transforma string em lista; `join()` transforma lista em string. Juntos, permitem reformatar texto com facilidade: `" ".join(frase.split())` remove espaços duplos de uma vez.
:::

---

## 2.7 Entrada do usuário com `input()`

`input()` pausa a execução e espera o usuário digitar, devolvendo sempre uma **string**:

```python
nome = input("Digite seu nome: ")
print(f"Olá, {nome}!")
```

:::aviso
**`input()` sempre retorna string!** Se precisar de número, converta:
```python
idade = int(input("Sua idade: "))    # converte para int
preco = float(input("Preço: "))      # converte para float
```
Esquecer a conversão é o erro mais comum em exercícios de Python para iniciantes.
:::

---

## 2.8 Conversão de tipos

Python não converte tipos automaticamente — você precisa ser explícito:

```python
nota = float(input("Nota: "))   # "8.5" → 8.5
print(nota + 0.5)               # 9.0
```

| Função | Converte para | Exemplo |
|--------|--------------|---------|
| `int(x)` | Inteiro | `int("42")` → `42` |
| `float(x)` | Decimal | `float("3.14")` → `3.14` |
| `str(x)` | Texto | `str(100)` → `"100"` |
| `bool(x)` | Bool | `bool(0)` → `False`, `bool("a")` → `True` |

:::dica
`int("3.14")` dá erro — `"3.14"` não é inteiro. Converta em dois passos se necessário: `int(float("3.14"))` → `3`.
:::

---

## 2.9 Formatando números na saída

```python
pi    = 3.141592653589793
preco = 1500.5

print(f"{pi:.2f}")       # 3.14      — 2 casas decimais
print(f"{pi:.4f}")       # 3.1416    — 4 casas decimais
print(f"{preco:,.2f}")   # 1,500.50  — separador de milhar
print(f"{0.875:.0%}")    # 88%       — percentual sem casas decimais
print(f"{'texto':>10}")  # "    texto" — alinhado à direita em 10 caracteres
```

:::tente
```python
peso   = float(input("Peso (kg): "))
altura = float(input("Altura (m): "))
imc    = peso / (altura ** 2)
print(f"Seu IMC é {imc:.1f}")
```
:::

---

## Exercícios

Antes de abrir o editor, responda mentalmente:
1. Quais são as entradas? Que tipo cada uma tem?
2. Que cálculos preciso fazer?
3. Como formatar a saída?

---

## Mini-projeto: Calculadora de Quatro Operações

Escreva um programa que pede dois números ao usuário e mostra o resultado das quatro operações. Trate divisão por zero com mensagem amigável.

---

## Resumo

✔ `#` — comentário; explique o porquê, não o quê  
✔ `+=`, `-=`, `*=` — atribuição composta  
✔ `None` — ausência de valor; `0`, `""`, `[]`, `None` são falsy  
✔ `str`, `int`, `float`, `bool` — tipos básicos  
✔ `input()` sempre devolve string — converta antes de calcular  
✔ f-strings: `f"{valor:.2f}"` para formatar números  
✔ `strip()`, `split()`, `join()` — os métodos de string mais usados  
✔ `//` divisão inteira, `%` resto, `**` potência  

## Próximos passos

No Módulo 3 você vai aprender a fazer o código tomar decisões — coisas como "se o usuário errou a senha, mostre um aviso". Para isso vamos usar `if`, `elif` e `else`.
