---
modulo: "03"
titulo: "Estruturas de Controle"
duracao_estimada: "6-8h"
pre_requisito: "02"
---

# Módulo 3 — Estruturas de Controle

## Por que isso importa?

Imagine um programa de caixa no supermercado. Ele precisa decidir: "se o cliente tem cartão fidelidade, aplica desconto; senão, cobra o preço normal". E repetir esse processo para cada produto no carrinho.

Decisões e repetições são o coração de qualquer programa real. Sem elas, o código executa sempre as mesmas instruções na mesma ordem — e não serve para quase nada útil.

## O que você vai aprender

- Operadores de comparação e lógicos
- Valores truthy e falsy — o que Python considera verdadeiro
- Tomar decisões com `if`, `elif` e `else`
- Expressão ternária — `if` em uma linha
- Repetir ações com `for` e `while`
- Loops aninhados
- Controlar loops com `break`, `continue` e `else`
- `match/case` — o switch do Python moderno

---

## 3.1 Operadores de comparação e lógicos

Todas as comparações retornam `True` ou `False`:

```python
print(5 > 3)    # True
print(5 == 5)   # True  — igual (dois sinais de =)
print(5 != 3)   # True  — diferente
print(5 >= 10)  # False
print(5 < 10)   # True

# Python suporta encadeamento de comparações
x = 15
print(10 < x < 20)   # True — mais limpo que x > 10 and x < 20
print(1 == 1 == 1)   # True
```

:::aviso
**`=` é atribuição, `==` é comparação!**
`x = 5` guarda 5 em x. `x == 5` verifica se x vale 5. Confundir os dois é um dos erros mais comuns.
:::

Para combinar condições:

```python
idade   = 20
tem_cnh = True

if idade >= 18 and tem_cnh:     # and: ambas precisam ser True
    print("Pode dirigir")

if idade < 12 or idade >= 65:   # or: basta uma ser True
    print("Meia-entrada")

if not tem_cnh:                 # not: inverte
    print("Sem habilitação")
```

---

## 3.2 Valores truthy e falsy

Em Python, qualquer valor pode ser usado como condição. São **falsy** (equivalem a `False`):

| Valor | Tipo |
|-------|------|
| `0` | int |
| `0.0` | float |
| `""` | str vazia |
| `[]` | lista vazia |
| `{}` | dict/set vazio |
| `None` | NoneType |
| `False` | bool |

Todo o resto é **truthy** (equivale a `True`).

```python
nome  = ""
lista = [1, 2, 3]
zero  = 0

if not nome:
    print("Nome vazio!")      # imprime

if lista:
    print("Lista tem itens")  # imprime

if zero:
    print("não imprime")      # não imprime — 0 é falsy
```

Isso permite escrever condições mais limpas:

```python
# Em vez de: if len(nome) > 0:
if nome:
    print(f"Olá, {nome}!")

# Em vez de: if lista != []:
if lista:
    processar(lista)
```

:::tente
```python
valores = [0, 1, "", "texto", None, [], [1]]
for v in valores:
    print(f"{repr(v):15} → {'truthy' if v else 'falsy'}")
```
:::

---

## 3.3 Decisões com `if` / `elif` / `else`

```python
if condição:
    # executa se condição for True
elif outra_condição:
    # executa se a primeira for False e esta for True
else:
    # executa se todas as condições acima forem False
```

**Exemplo — classificar IMC:**

```python
imc = float(input("Seu IMC: "))

if imc < 18.5:
    print("Abaixo do peso")
elif imc < 25:
    print("Peso normal")
elif imc < 30:
    print("Sobrepeso")
else:
    print("Obesidade")
```

:::dica
**A indentação é obrigatória em Python.** O bloco dentro do `if` precisa ter 4 espaços. Sem indentação, Python não sabe onde o `if` termina.
:::

### Expressão ternária — `if` em uma linha

Quando você precisa atribuir um valor com base em uma condição, a expressão ternária é mais concisa:

```python
# Forma longa
if nota >= 7:
    situacao = "aprovado"
else:
    situacao = "reprovado"

# Forma ternária — equivalente
situacao = "aprovado" if nota >= 7 else "reprovado"

# Funciona em qualquer expressão
print("par" if numero % 2 == 0 else "ímpar")
abs_valor = x if x >= 0 else -x
```

---

## 3.4 `match/case` — Python 3.10+

Para comparar um valor com múltiplos casos fixos, `match/case` é mais legível que uma cadeia de `if/elif`:

```python
comando = input("Comando: ").lower()

match comando:
    case "iniciar":
        print("Iniciando...")
    case "parar" | "sair":          # múltiplos valores com |
        print("Encerrando.")
    case "ajuda":
        print("Comandos: iniciar, parar, ajuda")
    case _:                          # _ é o caso padrão (como else)
        print(f"Comando desconhecido: {comando}")
```

:::dica
`match/case` não substitui todos os `if/elif` — use quando você está comparando **um único valor contra constantes fixas**. Para condições mais complexas (ranges, combinações), `if/elif` continua sendo a escolha certa.
:::

---

## 3.5 Loop `for` — repetição controlada

Use `for` quando sabe de antemão quantas vezes repetir, ou está iterando sobre uma coleção:

```python
# range(início, fim, passo) — fim é exclusivo
for i in range(5):           # 0, 1, 2, 3, 4
    print(i)

for i in range(1, 6):        # 1, 2, 3, 4, 5
    print(i)

for i in range(0, 10, 2):   # 0, 2, 4, 6, 8
    print(i)

for i in range(10, 0, -1):  # 10, 9, 8, ..., 1
    print(i)
```

**Iterando sobre coleções:**

```python
for letra in "Python":
    print(letra)              # P, y, t, h, o, n

frutas = ["banana", "manga", "uva"]
for fruta in frutas:
    print(fruta.upper())
```

:::tente
```python
total = 0
for i in range(1, 101):
    total += i
print(f"Soma de 1 a 100: {total}")   # 5050
```
:::

---

## 3.6 Loop `while` — repetição com condição

Use `while` quando não sabe de antemão quantas vezes repetir:

```python
numero = -1
while numero <= 0:
    numero = int(input("Digite um número positivo: "))
print(f"Você digitou: {numero}")
```

:::aviso
**Loop infinito!** Se a condição nunca ficar False, o programa trava. Sempre garanta que o corpo do loop modifique algo que a condição verifica.
:::

---

## 3.7 `break`, `continue` e `else` em loops

```python
# break — sai do loop imediatamente
for i in range(10):
    if i == 5:
        break
    print(i)   # 0, 1, 2, 3, 4

# continue — pula para a próxima iteração
for i in range(10):
    if i % 2 == 0:
        continue
    print(i)   # 1, 3, 5, 7, 9
```

### `else` em loops — executado quando o loop termina sem `break`

```python
# Procurar um número em uma lista
numeros = [2, 4, 6, 8, 10]
busca   = 7

for n in numeros:
    if n == busca:
        print(f"Encontrado: {busca}")
        break
else:
    # Só executa se o for terminou sem break
    print(f"{busca} não está na lista")

# Com while — mesmo comportamento
tentativas = 3
while tentativas > 0:
    senha = input("Senha: ")
    if senha == "1234":
        print("Acesso liberado")
        break
    tentativas -= 1
else:
    print("Conta bloqueada")
```

---

## 3.8 Loops aninhados

Um loop dentro de outro — o loop interno executa completamente para cada iteração do externo:

```python
# Tabuada de multiplicação 1 a 3
for i in range(1, 4):
    for j in range(1, 11):
        print(f"{i} x {j:2} = {i*j:2}", end="   ")
    print()   # quebra de linha após cada linha da tabuada
```

```python
# Matriz — lista de listas
matriz = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9],
]

for linha in matriz:
    for elemento in linha:
        print(elemento, end=" ")
    print()
# 1 2 3
# 4 5 6
# 7 8 9
```

:::aviso
**`break` em loop aninhado só sai do loop mais interno.** Para sair de múltiplos loops, use uma variável de controle ou encapsule o loop interno em uma função.
:::

---

## Exercícios

Pense nos casos extremos antes de codar: o que acontece com 0? Com número negativo? Com o valor exato no limite da condição?

---

## Mini-projeto: Jogo de Adivinhação

Escreva um jogo completo:

1. O programa define um número secreto (use `secreto = 42`)
2. O usuário tem **8 tentativas** para adivinhar
3. A cada tentativa: informe se o chute foi maior, menor ou correto
4. Mostre se ganhou ou perdeu e quantas tentativas usou
5. **Bônus:** dê dicas de "muito longe" (diferença > 30) e "quente" (diferença < 10)

---

## Quiz

Teste seus conhecimentos sobre estruturas de controle.

---

## Resumo

✔ `==` compara, `=` atribui — não confunda  
✔ `0`, `""`, `[]`, `None` são falsy — `if lista:` funciona  
✔ `10 < x < 20` — encadeamento de comparações  
✔ Expressão ternária: `valor = a if cond else b`  
✔ `match/case` — comparação de valor contra constantes  
✔ `for range(n)` — repete n vezes; `while cond` — enquanto for True  
✔ Loops aninhados: interno executa completo a cada iteração do externo  
✔ `break` sai do loop; `continue` pula iteração; `else` roda se não houve `break`  

## Próximos passos

No Módulo 4 você vai aprender a guardar coleções de dados — listas, tuplas e dicionários. Com eles você poderá, por exemplo, guardar as notas de todos os alunos de uma turma em uma só variável.
