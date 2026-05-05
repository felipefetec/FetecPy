---
modulo: "05"
titulo: "Funções e Modularização"
duracao_estimada: "7-9h"
pre_requisito: "04"
---

# Módulo 5 — Funções e Modularização

## Escrever uma vez, usar sempre

Imagine escrever o código de cálculo de média 10 vezes, uma para cada turma. Se descobrir um bug, precisa corrigir 10 lugares. Funções resolvem isso: você escreve o código uma vez, dá um nome a ele, e chama esse nome quantas vezes precisar.

Funções são o primeiro grande passo para não repetir código. Escreva uma vez, chame quantas vezes precisar — e quando precisar corrigir algo, corrige em um lugar só.

## O que você vai aprender

- Criar funções com `def` e documentá-las com docstrings
- Parâmetros posicionais, com default, `*args` e `**kwargs`
- Retornar múltiplos valores
- Escopo de variáveis — local, global e `nonlocal`
- Type hints — como anotar tipos em Python moderno
- Recursão — funções que chamam a si mesmas
- Closures e funções aninhadas
- Funções como valores: `lambda`, `map()`, `filter()`

---

## 5.1 Criando funções com `def`

```python
def saudar(nome):
    mensagem = f"Olá, {nome}!"
    return mensagem

print(saudar("Felipe"))   # Olá, Felipe!
print(saudar("Ana"))      # Olá, Ana!
```

`def nome_funcao(parametros):` seguida do corpo indentado. `return` devolve o valor ao chamador — sem `return`, a função devolve `None`:

```python
def exibir(texto):
    print(texto)        # sem return explícito

resultado = exibir("Oi")
print(resultado)        # None
```

:::dica
**Função = caixa preta.**
Quem chama a função só precisa saber: "o que eu dou pra ela e o que ela devolve". O que acontece dentro é detalhe de implementação. Isso é **abstração** — um dos princípios mais importantes em programação.
:::

---

## 5.2 Parâmetros e argumentos

### Parâmetros com valor padrão

```python
def saudar(nome, saudacao="Olá"):
    return f"{saudacao}, {nome}!"

print(saudar("Ana"))                        # Olá, Ana!
print(saudar("Carlos", "Oi"))               # Oi, Carlos!
print(saudar(saudacao="E aí", nome="Pedro")) # E aí, Pedro! — argumentos nomeados
```

### `*args` — número variável de argumentos posicionais

```python
def somar(*numeros):
    # numeros é uma tupla com todos os argumentos recebidos
    return sum(numeros)

print(somar(1, 2, 3))       # 6
print(somar(1, 2, 3, 4, 5)) # 15
```

### `**kwargs` — número variável de argumentos nomeados

```python
def criar_perfil(nome, **extras):
    # extras é um dicionário com todos os argumentos nomeados extras
    perfil = {"nome": nome}
    perfil.update(extras)
    return perfil

print(criar_perfil("Felipe", idade=30, cidade="SP"))
# {'nome': 'Felipe', 'idade': 30, 'cidade': 'SP'}

print(criar_perfil("Ana"))
# {'nome': 'Ana'}
```

### Combinando tudo

A ordem importa: `posicionais`, `*args`, `keyword-only`, `**kwargs`:

```python
def registrar(nivel, *mensagens, separador="\n", **contexto):
    texto = separador.join(mensagens)
    print(f"[{nivel}] {texto}")
    if contexto:
        print(f"Contexto: {contexto}")

registrar("INFO", "Servidor iniciado", "Porta: 8080", separador=" | ")
registrar("ERRO", "Falha na conexão", usuario="felipe", modulo="auth")
```

:::tente
```python
def potencia(base, expoente=2):
    return base ** expoente

print(potencia(3))      # 9  (3²)
print(potencia(2, 10))  # 1024 (2¹⁰)
print(potencia(expoente=3, base=4))  # 64
```
:::

---

## 5.3 Type hints — anotações de tipo

Type hints documentam o que a função espera receber e retornar. Python não os força em tempo de execução — são para você, para a IDE e para ferramentas de análise:

```python
def calcular_imc(peso: float, altura: float) -> float:
    return peso / altura ** 2

def saudar(nome: str, vezes: int = 1) -> str:
    return (f"Olá, {nome}! " * vezes).strip()

def buscar_usuario(user_id: int) -> dict | None:
    # retorna um dict se encontrado, None se não
    ...
```

Para tipos mais complexos, use `list`, `dict`, `tuple` com colchetes (Python 3.9+):

```python
def calcular_medias(turmas: dict[str, list[float]]) -> dict[str, float]:
    return {turma: sum(notas) / len(notas) for turma, notas in turmas.items()}
```

:::dica
Você não precisa anotar tudo, mas anotar parâmetros e retornos de funções públicas é boa prática. A IDE passa a autocompletar corretamente e avisar sobre erros de tipo antes de rodar.
:::

---

## 5.4 Retorno de múltiplos valores

Python retorna múltiplos valores como uma tupla — que pode ser desempacotada:

```python
def estatisticas(numeros: list[float]) -> tuple[float, float, float]:
    menor = min(numeros)
    maior = max(numeros)
    media = sum(numeros) / len(numeros)
    return menor, maior, media   # retorna tupla implicitamente

mn, mx, med = estatisticas([5, 2, 8, 1, 9])
print(f"Min: {mn}, Max: {mx}, Média: {med:.1f}")

# Ignorar valores que não interessam com _
mn, _, med = estatisticas([5, 2, 8, 1, 9])
```

---

## 5.5 Escopo de variáveis

Variáveis criadas dentro de uma função são **locais** — não existem fora dela:

```python
def calcular():
    resultado = 42   # local
    return resultado

# print(resultado)  # NameError — não existe aqui
```

Variáveis de fora podem ser **lidas** dentro da função, mas não modificadas sem `global` (evite `global` — é má prática):

```python
contador = 0

def incrementar():
    global contador    # necessário para modificar variável global
    contador += 1      # sem global, isso causaria UnboundLocalError

incrementar()
print(contador)   # 1
```

---

## 5.6 Recursão

Uma função pode chamar a si mesma — isso é **recursão**. Toda função recursiva precisa de um caso base que encerra a recursão:

```python
def fatorial(n: int) -> int:
    if n <= 1:          # caso base — encerra a recursão
        return 1
    return n * fatorial(n - 1)   # chamada recursiva

print(fatorial(5))   # 120  → 5 * 4 * 3 * 2 * 1
```

```python
def contagem_regressiva(n: int) -> None:
    if n <= 0:
        print("Fogo!")
        return
    print(n)
    contagem_regressiva(n - 1)

contagem_regressiva(3)   # 3, 2, 1, Fogo!
```

:::aviso
**Python tem limite de recursão** (padrão: 1000 chamadas). Para problemas com muitas iterações, use loops. Recursão brilha em problemas naturalmente recursivos: árvores, diretórios, expressões matemáticas.
:::

---

## 5.7 Docstrings

Docstrings documentam o comportamento de uma função — ficam na primeira linha do corpo:

```python
def calcular_imc(peso: float, altura: float) -> float:
    """
    Calcula o Índice de Massa Corporal.

    Args:
        peso:   Peso em quilogramas.
        altura: Altura em metros.

    Returns:
        Valor do IMC arredondado a 2 casas decimais.

    Raises:
        ValueError: Se peso ou altura forem não-positivos.
    """
    if peso <= 0 or altura <= 0:
        raise ValueError("Peso e altura devem ser positivos")
    return round(peso / altura ** 2, 2)
```

`help(calcular_imc)` mostra a docstring. IDEs a usam para autocompletar. Escreva docstrings para funções não óbvias — funções simples não precisam.

---

## 5.8 Closures — funções que lembram

Uma **closure** é uma função que captura variáveis do escopo onde foi criada, mesmo depois desse escopo ter encerrado:

```python
def criar_multiplicador(fator: int):
    def multiplicar(numero: int) -> int:
        return numero * fator   # captura 'fator' do escopo externo
    return multiplicar          # retorna a função interna

dobrar    = criar_multiplicador(2)
triplicar = criar_multiplicador(3)

print(dobrar(5))      # 10
print(triplicar(5))   # 15
print(dobrar(10))     # 20
```

`dobrar` é uma closure: é uma função que "lembra" que `fator = 2`, mesmo que `criar_multiplicador` já tenha terminado.

**`nonlocal`** — para modificar variável do escopo externo dentro de uma closure:

```python
def criar_contador():
    total = 0
    def incrementar(passo: int = 1) -> int:
        nonlocal total   # modifica 'total' do escopo externo
        total += passo
        return total
    return incrementar

contador = criar_contador()
print(contador())    # 1
print(contador())    # 2
print(contador(5))   # 7
```

---

## 5.9 Funções como valores: `lambda`, `map()` e `filter()`

### `lambda` — funções anônimas

```python
# Equivalente a def dobrar(x): return x * 2
dobrar = lambda x: x * 2
print(dobrar(5))   # 10

# Mais útil como argumento de outra função
nomes = ['Carlos', 'Ana', 'Bruno', 'Diana']
print(sorted(nomes, key=lambda n: n[-1]))    # ordena pela última letra
print(sorted(nomes, key=lambda n: len(n)))   # ordena pelo tamanho
```

### `map()` — transforma cada elemento

```python
numeros = [1, 2, 3, 4, 5]

# Equivalente a [n * 2 for n in numeros]
dobrados = list(map(lambda n: n * 2, numeros))
print(dobrados)   # [2, 4, 6, 8, 10]

# Com função nomeada
def formatar(nota: float) -> str:
    return f"{nota:.1f}"

notas = [7.567, 8.123, 9.0]
print(list(map(formatar, notas)))   # ['7.6', '8.1', '9.0']
```

### `filter()` — filtra elementos

```python
notas = [4.5, 7.0, 8.5, 5.5, 9.0, 6.0]

# Equivalente a [n for n in notas if n >= 7]
aprovados = list(filter(lambda n: n >= 7, notas))
print(aprovados)   # [7.0, 8.5, 9.0]
```

:::dica
**Quando usar `map`/`filter` vs list comprehension?**
Não há regra absoluta. Comprehensions tendem a ser mais legíveis para casos simples. `map`/`filter` brilham quando a função já existe e tem nome — `list(map(formatar, notas))` é mais claro que `[formatar(n) for n in notas]`.
:::

---

## Exercícios

Os exercícios pedem funções com comportamento bem definido. Use os validadores B (função) e C (AST) — a plataforma vai chamar sua função com vários argumentos e verificar os retornos.

---

## Mini-projeto: Validador de CPF Completo

Escreva um conjunto de funções para validar um CPF brasileiro:

1. `limpar_cpf(cpf: str) -> str` — remove pontos e traços: `"123.456.789-09"` → `"12345678909"`
2. `eh_sequencia_repetida(cpf: str) -> bool` — True se todos os dígitos são iguais
3. `calcular_digito(cpf: str, posicao: int) -> int` — calcula o dígito verificador (posicao = 10 ou 11)
4. `validar_cpf(cpf: str) -> bool` — função principal que usa as anteriores

O algoritmo de validação do CPF está bem documentado online — parte do desafio é entender e implementá-lo.

---

## Quiz

Teste seus conhecimentos sobre funções.

---

## Resumo

✔ `def nome(params):` — define uma função; sem `return` devolve `None`  
✔ `*args` recebe argumentos posicionais extras como tupla  
✔ `**kwargs` recebe argumentos nomeados extras como dicionário  
✔ Type hints: `def f(x: int) -> str:` — documentação e suporte a IDE  
✔ Recursão: função que chama a si mesma — sempre defina o caso base  
✔ Closure: função que captura variáveis do escopo externo  
✔ `nonlocal` para modificar variável do escopo externo em closure  
✔ `lambda x: expr` — função anônima para uso inline  
✔ `map(func, lista)` — transforma; `filter(func, lista)` — filtra  

## Próximos passos

No Módulo 6 você vai aprender Programação Orientada a Objetos — como criar seus próprios tipos de dados com `class`, agrupando dados e comportamentos relacionados.
