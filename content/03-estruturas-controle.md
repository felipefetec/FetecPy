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

- Tomar decisões com `if`, `elif` e `else`
- Repetir ações com `for` e `while`
- Usar operadores de comparação e lógicos
- Controlar loops com `break` e `continue`

---

## 3.1 Operadores de comparação e lógicos

Antes de aprender `if`, você precisa saber como Python avalia condições. Todas as comparações retornam `True` ou `False`:

```python
print(5 > 3)    # True
print(5 == 5)   # True  (igual — dois sinais de =)
print(5 != 3)   # True  (diferente)
print(5 >= 10)  # False
print(5 < 10)   # True
```

:::aviso
**`=` é atribuição, `==` é comparação!**
`x = 5` guarda 5 em x. `x == 5` verifica se x vale 5. Confundir os dois é um erro muito comum.
:::

Para combinar condições, use os operadores lógicos:

```python
idade = 20
tem_cnh = True

# and: ambas precisam ser True
if idade >= 18 and tem_cnh:
    print("Pode dirigir")

# or: basta uma ser True
if idade < 12 or idade >= 65:
    print("Meia-entrada")

# not: inverte
if not tem_cnh:
    print("Sem habilitação")
```

:::tente
```python
# Teste os operadores lógicos
x = 15
print(x > 10 and x < 20)  # True
print(x < 5 or x > 10)    # True
print(not (x == 15))       # False
```
:::

---

## 3.2 Decisões com if / elif / else

A estrutura básica de decisão em Python:

```python
if condição:
    # executa se condição for True
elif outra_condição:
    # executa se a primeira for False e esta for True
else:
    # executa se todas as condições acima forem False
```

**Exemplo real: classificar IMC**

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
**A indentação é obrigatória em Python.**
O bloco dentro do `if` precisa ter 4 espaços de recuo. Sem indentação, o Python não sabe onde o `if` termina e o código seguinte começa. Se usar TAB, configure o editor para converter TAB em 4 espaços.
:::

:::reflexao
Antes de fazer o próximo exercício, pense: se você tivesse que verificar se um número está entre 10 e 20 (inclusive), como escreveria a condição?

---resposta
numero >= 10 and numero <= 20

Ou de forma mais pitônica: 10 <= numero <= 20 (Python suporta encadeamento de comparações!)
:::

---

## 3.3 Loop for — repetição com contador

Use `for` quando você sabe de antemão quantas vezes quer repetir (ou está iterando sobre uma coleção):

```python
# range(início, fim, passo) — fim é exclusivo
for i in range(5):          # 0, 1, 2, 3, 4
    print(i)

for i in range(1, 6):       # 1, 2, 3, 4, 5
    print(i)

for i in range(0, 10, 2):   # 0, 2, 4, 6, 8 (passo 2)
    print(i)

for i in range(10, 0, -1):  # 10, 9, 8, ..., 1 (contagem regressiva)
    print(i)
```

**Iterando sobre texto:**

```python
for letra in "Python":
    print(letra)   # P, y, t, h, o, n (um por linha)
```

:::tente
```python
# Somar os números de 1 a 100
total = 0
for i in range(1, 101):
    total += i
print(f"Soma de 1 a 100: {total}")
```
:::

---

## 3.4 Loop while — repetição com condição

Use `while` quando você não sabe de antemão quantas vezes vai repetir. O loop continua **enquanto** a condição for True:

```python
# Conta até o usuário digitar um número positivo
numero = -1
while numero <= 0:
    numero = int(input("Digite um número positivo: "))

print(f"Você digitou: {numero}")
```

:::aviso
**Loop infinito!**
Se a condição do `while` nunca se tornar False, o programa trava. Sempre garanta que o corpo do loop modifique algo que a condição verifica.
```python
# PERIGO: loop infinito
while True:
    print("Isso nunca para!")  # Evite isso sem um break controlado
```
:::

---

## 3.5 break e continue

Duas instruções especiais para controlar o fluxo dentro de loops:

```python
# break — sai do loop imediatamente
for i in range(10):
    if i == 5:
        break           # para quando i chega em 5
    print(i)            # imprime 0, 1, 2, 3, 4

# continue — pula para a próxima iteração
for i in range(10):
    if i % 2 == 0:
        continue        # pula números pares
    print(i)            # imprime 1, 3, 5, 7, 9
```

:::dica
**Quando usar `break` vs `while`?**
Prefira `while condicao:` quando a condição é clara desde o início. Use `break` quando você só descobre a condição de parada dentro do loop — por exemplo, ao validar entrada do usuário com menu interativo.
:::

:::tente
```python
# Jogo simples: adivinhe o número (versão simplificada)
secreto = 7
while True:
    chute = int(input("Chute: "))
    if chute == secreto:
        print("Acertou!")
        break
    elif chute < secreto:
        print("Maior!")
    else:
        print("Menor!")
```
:::

---

## Exercícios

Estes exercícios cobrem `if/elif/else`, `for` e `while`. Pense nos casos extremos: o que acontece com 0? Com número negativo? Com o valor exato no limite da condição?

---

## Mini-projeto: Jogo de Adivinhação

Escreva um jogo de adivinhação completo:

1. O programa "sorteia" um número de 1 a 100 (use `numero = 42` por enquanto — o `random` vem no próximo módulo)
2. O usuário tem **8 tentativas** para adivinhar
3. A cada tentativa, informe se o chute foi maior ou menor
4. Ao fim, mostre se ganhou ou perdeu e qual era o número secreto
5. Mostre o número de tentativas usadas

**Desafio bônus:** adapte para dar dicas de "muito longe" (diferença > 30), "quente" (diferença < 10) e "acertou".

---

## Quiz

Teste seus conhecimentos sobre estruturas de controle.

---

## Resumo

✔ `if/elif/else` — decisões com base em condições  
✔ `==` compara, `=` atribui — não confunda!  
✔ `for range(n)` — repete n vezes contando  
✔ `while condicao` — repete enquanto a condição for True  
✔ `break` sai do loop, `continue` pula para a próxima iteração  

## Próximos passos

No Módulo 4 você vai aprender a guardar coleções de dados — listas, tuplas e dicionários. Com eles você poderá, por exemplo, guardar as notas de todos os alunos de uma turma em uma só variável.
