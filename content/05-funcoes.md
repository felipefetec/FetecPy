---
modulo: "05"
titulo: "Funções e Modularização"
duracao_estimada: "7-9h"
pre_requisito: "04"
---

# Módulo 5 — Funções e Modularização

## Por que isso importa?

Imagine escrever o código de cálculo de média 10 vezes, uma para cada turma. Se descobrir um bug, precisa corrigir 10 lugares. Funções resolvem isso: você escreve o código uma vez, dá um nome a ele, e chama esse nome quantas vezes precisar.

Funções são o primeiro grande passo rumo a código que *escala* — que cresce sem virar um labirinto impossível de manter.

## O que você vai aprender

- Criar funções com `def`
- Parâmetros, valores padrão e argumentos nomeados
- Retornar valores com `return`
- Escopo de variáveis
- Documentação com docstrings
- Funções como "cidadãos de primeira classe" em Python

---

## 5.1 Criando funções com def

```python
def saudar(nome):
    mensagem = f"Olá, {nome}!"
    return mensagem

# Chamando a função
print(saudar("Felipe"))   # Olá, Felipe!
print(saudar("Ana"))      # Olá, Ana!
```

A estrutura: `def nome_funcao(parametros):` seguida do corpo indentado. `return` devolve o valor ao chamador.

**Função sem retorno explícito devolve `None`:**

```python
def exibir(texto):
    print(texto)        # sem return

resultado = exibir("Oi")
print(resultado)        # None
```

:::dica
**Função = caixa preta.**
Quem chama a função só precisa saber: "o que eu dou pra ela e o que ela devolve". O que acontece dentro é detalhe de implementação. Isso é **abstração** — um dos princípios mais importantes em programação.
:::

---

## 5.2 Parâmetros e argumentos

```python
# Parâmetro padrão (default): usado se o argumento não for passado
def saudar(nome, saudacao="Olá"):
    return f"{saudacao}, {nome}!"

print(saudar("Ana"))             # Olá, Ana!
print(saudar("Carlos", "Oi"))    # Oi, Carlos!

# Argumentos nomeados (keyword arguments) — ordem não importa
print(saudar(saudacao="E aí", nome="Pedro"))  # E aí, Pedro!
```

**Parâmetros arbitrários `*args` e `**kwargs`:**

```python
def somar(*numeros):
    return sum(numeros)

print(somar(1, 2, 3))          # 6
print(somar(1, 2, 3, 4, 5))    # 15
```

:::tente
```python
def potencia(base, expoente=2):
    return base ** expoente

print(potencia(3))      # 9  (3²)
print(potencia(2, 10))  # 1024 (2¹⁰)
```
:::

---

## 5.3 Retorno de múltiplos valores

Python permite retornar mais de um valor como uma tupla:

```python
def estatisticas(numeros):
    menor = min(numeros)
    maior = max(numeros)
    media = sum(numeros) / len(numeros)
    return menor, maior, media   # retorna tupla

mn, mx, med = estatisticas([5, 2, 8, 1, 9])  # desempacotamento
print(f"Min: {mn}, Max: {mx}, Média: {med:.1f}")
```

---

## 5.4 Escopo de variáveis

Variáveis criadas dentro de uma função são **locais** — não existem fora dela:

```python
def calcular():
    resultado = 42   # variável local
    return resultado

calcular()
# print(resultado)  # ERRO: NameError — resultado não existe aqui
```

Variáveis definidas fora das funções são **globais** — podem ser lidas de dentro, mas não modificadas sem a palavra-chave `global` (evite usar `global` — é considerado má prática).

:::aviso
**Evite variáveis globais mutáveis.**
Código que depende de estado global é difícil de testar e de entender. Prefira passar valores como parâmetros e retorná-los com `return`.
:::

---

## 5.5 Docstrings

Docstrings são strings de documentação que ficam na primeira linha de uma função:

```python
def calcular_imc(peso, altura):
    """
    Calcula o Índice de Massa Corporal.

    Parâmetros:
        peso   (float): Peso em kg
        altura (float): Altura em metros

    Retorna:
        float: O valor do IMC
    """
    return peso / altura ** 2
```

`help(calcular_imc)` mostra a docstring. IDEs usam docstrings para autocompletar. Escreva-as para funções não óbvias.

---

## 5.6 Funções como valores

Em Python, funções são objetos — você pode passá-las como argumento:

```python
def aplicar(funcao, valor):
    return funcao(valor)

def dobrar(x):
    return x * 2

print(aplicar(dobrar, 5))   # 10

# Funções anônimas (lambda) — para casos simples
triplicar = lambda x: x * 3
print(aplicar(triplicar, 5))  # 15
```

:::tente
```python
numeros = [5, 2, 8, 1, 9, 3]
ordenado = sorted(numeros, key=lambda x: -x)  # ordem decrescente
print(ordenado)  # [9, 8, 5, 3, 2, 1]
```
:::

---

## Exercícios

Os exercícios deste módulo pedem funções com comportamento bem definido. Use os validadores B (função) e C (AST) — a plataforma vai chamar sua função com vários argumentos e verificar os retornos.

---

## Mini-projeto: Validador de CPF Completo

Escreva um conjunto de funções para validar um CPF brasileiro:

1. `limpar_cpf(cpf)` — remove pontos e traços: `"123.456.789-09"` → `"12345678909"`
2. `eh_sequencia_repetida(cpf)` — retorna True se todos os dígitos são iguais (CPFs inválidos por definição)
3. `calcular_digito(cpf, posicao)` — calcula o dígito verificador (posicao = 10 ou 11)
4. `validar_cpf(cpf)` — função principal que usa as anteriores e retorna True/False

O algoritmo de validação do CPF está bem documentado online — parte do desafio é entender e implementá-lo.

---

## Quiz

Teste seus conhecimentos sobre funções.

---

## Resumo

✔ `def nome(params):` — define uma função  
✔ `return valor` — devolve resultado ao chamador  
✔ Parâmetros com default: `def f(x, y=10):`  
✔ Variáveis locais não existem fora da função  
✔ Funções podem retornar tuplas (múltiplos valores)  
✔ Funções são objetos — podem ser passadas como argumento  

## Próximos passos

No Módulo 6 você vai aprender Programação Orientada a Objetos — como criar seus próprios tipos de dados com `class`, agrupando dados e comportamentos relacionados.
