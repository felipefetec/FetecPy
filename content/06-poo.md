---
modulo: "06"
titulo: "Programação Orientada a Objetos"
duracao_estimada: "8-10h"
pre_requisito: "05"
---

# Módulo 6 — Programação Orientada a Objetos

## Por que isso importa?

Funções organizam *comportamentos*. Mas e quando você precisa organizar *dados e comportamentos juntos*? Uma conta bancária tem saldo, titular e operações como sacar e depositar — tudo isso pertence a um mesmo conceito.

Classes permitem criar seus próprios tipos de dados. Depois de aprender OOP (Programação Orientada a Objetos), você entenderá como funcionam as bibliotecas que usa — porque elas são feitas de classes.

## O que você vai aprender

- Criar classes com `class`
- Construtores `__init__` e o `self`
- Atributos e métodos de instância
- Encapsulamento e convenções de visibilidade
- Herança e polimorfismo
- Métodos especiais (`__str__`, `__repr__`)

---

## 6.1 O que é uma classe?

Uma classe é um molde para criar objetos. Pense em "Carro" como classe: define que todo carro tem marca, modelo, velocidade e pode acelerar e frear. Um carro específico ("Fusca 1975 vermelho") é um **objeto** (instância) da classe Carro.

```python
class Cachorro:
    # Construtor: chamado automaticamente ao criar o objeto
    def __init__(self, nome, raca):
        self.nome = nome   # atributo de instância
        self.raca = raca

    def latir(self):
        return f"{self.nome} diz: Au au!"

    def __str__(self):
        return f"Cachorro({self.nome}, {self.raca})"

# Criando instâncias (objetos)
rex   = Cachorro("Rex", "Labrador")
bolinha = Cachorro("Bolinha", "Poodle")

print(rex.latir())       # Rex diz: Au au!
print(str(bolinha))      # Cachorro(Bolinha, Poodle)
print(rex.nome)          # Rex
```

:::dica
**O que é `self`?**
`self` é uma referência ao próprio objeto. Quando você chama `rex.latir()`, o Python passa `rex` como primeiro argumento automaticamente. Por convenção, esse parâmetro sempre se chama `self`.
:::

---

## 6.2 Atributos e métodos

```python
class Contador:
    # Atributo de classe: compartilhado entre todas as instâncias
    total_criados = 0

    def __init__(self, inicio=0):
        self.valor = inicio           # atributo de instância
        Contador.total_criados += 1   # modifica o atributo de classe

    def incrementar(self, passo=1):
        self.valor += passo

    def resetar(self):
        self.valor = 0

c1 = Contador(10)
c2 = Contador()
c1.incrementar(5)
print(c1.valor)            # 15
print(Contador.total_criados)  # 2
```

:::tente
```python
class Ponto:
    def __init__(self, x, y):
        self.x = x
        self.y = y

    def distancia_origem(self):
        return (self.x**2 + self.y**2) ** 0.5

p = Ponto(3, 4)
print(p.distancia_origem())  # 5.0
```
:::

---

## 6.3 Encapsulamento

Encapsulamento significa esconder detalhes internos e expor apenas uma interface limpa. Em Python, usamos convenções de nomenclatura:

```python
class ContaBancaria:
    def __init__(self, titular, saldo_inicial=0):
        self.titular = titular
        self._saldo  = saldo_inicial  # _ = "privado por convenção"

    def depositar(self, valor):
        if valor <= 0:
            raise ValueError("Valor deve ser positivo")
        self._saldo += valor

    def sacar(self, valor):
        if valor > self._saldo:
            raise ValueError("Saldo insuficiente")
        self._saldo -= valor

    @property
    def saldo(self):
        return self._saldo   # leitura permitida, escrita controlada
```

:::aviso
`_atributo` (um underline) sinaliza "interno — use com cuidado". `__atributo` (dois underlines) aplica name mangling — Python renomeia para `_NomeDaClasse__atributo`, tornando mais difícil acesso externo acidental.
:::

---

## 6.4 Herança

Herança permite criar uma classe com base em outra, reutilizando e estendendo o comportamento:

```python
class Animal:
    def __init__(self, nome):
        self.nome = nome

    def falar(self):
        return "..."

class Cachorro(Animal):
    def falar(self):           # sobrescreve o método da classe pai
        return f"{self.nome}: Au au!"

class Gato(Animal):
    def falar(self):
        return f"{self.nome}: Miau!"

animais = [Cachorro("Rex"), Gato("Mimi"), Cachorro("Bolinha")]
for animal in animais:
    print(animal.falar())   # polimorfismo em ação!
```

**`super()` — acessando a classe pai:**

```python
class PetDomestico(Animal):
    def __init__(self, nome, dono):
        super().__init__(nome)  # chama __init__ de Animal
        self.dono = dono

    def apresentar(self):
        return f"{self.nome} é de {self.dono}"
```

---

## 6.5 Métodos especiais (dunder methods)

Python usa métodos com dois underlines de cada lado para comportamentos especiais:

| Método | Quando é chamado |
|--------|----------------|
| `__init__` | ao criar o objeto |
| `__str__` | ao fazer `str(obj)` ou `print(obj)` |
| `__repr__` | ao inspecionar no terminal |
| `__len__` | ao fazer `len(obj)` |
| `__eq__` | ao comparar com `==` |
| `__lt__` | ao comparar com `<` |
| `__add__` | ao usar `+` |

```python
class Vetor:
    def __init__(self, x, y):
        self.x, self.y = x, y

    def __str__(self):
        return f"({self.x}, {self.y})"

    def __add__(self, outro):
        return Vetor(self.x + outro.x, self.y + outro.y)

    def __eq__(self, outro):
        return self.x == outro.x and self.y == outro.y

v1 = Vetor(1, 2)
v2 = Vetor(3, 4)
print(v1 + v2)   # (4, 6)
```

---

## Exercícios

Os exercícios deste módulo pedem classes funcionais. A plataforma vai criar objetos e chamar métodos — garanta que os atributos e retornos seguem o enunciado exatamente.

---

## Mini-projeto: Sistema Bancário

Implemente um mini-banco com as classes:

1. `Conta(titular, saldo_inicial)` — operações básicas
2. `ContaPoupanca(Conta, taxa_juros)` — herda de Conta, adiciona `render_juros()`
3. `Banco(nome)` — gerencia uma lista de contas, permite criar, buscar e listar

O sistema deve:
- Validar saques (saldo insuficiente)
- Calcular rendimento de contas poupança
- Listar clientes em ordem alfabética
- Mostrar extrato formatado de cada conta

---

## Quiz

Teste seus conhecimentos sobre POO.

---

## Resumo

✔ `class Nome:` define um novo tipo  
✔ `__init__` é o construtor — inicializa os atributos  
✔ `self` referencia a própria instância  
✔ `_atributo` — convenção de privacidade  
✔ Herança: `class Filho(Pai)` + `super().__init__()`  
✔ Polimorfismo: mesmo método, comportamentos diferentes  

## Próximos passos

No Módulo 7 você vai aprender a trabalhar com arquivos, JSON e tratar erros — habilidades essenciais para criar programas que persistem dados entre execuções.
