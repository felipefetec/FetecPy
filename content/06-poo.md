---
modulo: "06"
titulo: "Programação Orientada a Objetos"
duracao_estimada: "8-10h"
pre_requisito: "05"
---

# Módulo 6 — Programação Orientada a Objetos

## Dados e comportamento juntos

Funções organizam *comportamentos*. Mas e quando você precisa organizar *dados e comportamentos juntos*? Uma conta bancária tem saldo, titular e operações como sacar e depositar — tudo isso pertence a um mesmo conceito.

Classes permitem criar seus próprios tipos de dados. Quando você entender como elas funcionam, vai começar a enxergar o que está por baixo de tudo que usou até agora — listas, strings, dicionários são classes também.

## O que você vai aprender

- Criar classes com `class`, construtores e `self`
- Atributos de instância vs atributos de classe
- Encapsulamento, `@property` e setter
- `@classmethod` e `@staticmethod`
- Herança, `super()` e polimorfismo
- `isinstance()` e `issubclass()`
- Métodos especiais (`__str__`, `__repr__`, `__len__`, `__eq__`, `__lt__`)
- Composição vs Herança

---

## 6.1 O que é uma classe?

Uma classe é um molde para criar objetos. Define quais dados eles guardam (atributos) e o que eles sabem fazer (métodos):

```python
class Cachorro:
    def __init__(self, nome: str, raca: str):
        self.nome = nome   # atributo de instância
        self.raca = raca

    def latir(self) -> str:
        return f"{self.nome} diz: Au au!"

    def __str__(self) -> str:
        return f"Cachorro({self.nome}, {self.raca})"

rex     = Cachorro("Rex", "Labrador")
bolinha = Cachorro("Bolinha", "Poodle")

print(rex.latir())    # Rex diz: Au au!
print(bolinha)        # Cachorro(Bolinha, Poodle)
print(rex.nome)       # Rex
```

:::dica
**O que é `self`?**
`self` é uma referência ao próprio objeto. Quando você chama `rex.latir()`, o Python passa `rex` como primeiro argumento automaticamente. Por convenção, esse parâmetro sempre se chama `self`.
:::

---

## 6.2 Atributos de instância vs atributos de classe

```python
class Contador:
    total_criados = 0   # atributo de CLASSE — compartilhado entre todas as instâncias

    def __init__(self, inicio: int = 0):
        self.valor = inicio          # atributo de INSTÂNCIA — único por objeto
        Contador.total_criados += 1

    def incrementar(self, passo: int = 1) -> None:
        self.valor += passo

    def resetar(self) -> None:
        self.valor = 0

c1 = Contador(10)
c2 = Contador()
c1.incrementar(5)

print(c1.valor)              # 15  — valor de c1
print(c2.valor)              # 0   — valor de c2, independente
print(Contador.total_criados) # 2  — compartilhado por todos
```

:::tente
```python
class Ponto:
    def __init__(self, x: float, y: float):
        self.x = x
        self.y = y

    def distancia_origem(self) -> float:
        return (self.x**2 + self.y**2) ** 0.5

p = Ponto(3, 4)
print(p.distancia_origem())  # 5.0
```
:::

---

## 6.3 Encapsulamento e `@property`

Encapsulamento significa esconder detalhes internos e expor apenas uma interface limpa:

```python
class ContaBancaria:
    def __init__(self, titular: str, saldo_inicial: float = 0):
        self.titular = titular
        self._saldo  = saldo_inicial  # _ = "interno por convenção"

    def depositar(self, valor: float) -> None:
        if valor <= 0:
            raise ValueError("Valor deve ser positivo")
        self._saldo += valor

    def sacar(self, valor: float) -> None:
        if valor <= 0:
            raise ValueError("Valor deve ser positivo")
        if valor > self._saldo:
            raise ValueError("Saldo insuficiente")
        self._saldo -= valor

    @property
    def saldo(self) -> float:
        return self._saldo

    @saldo.setter
    def saldo(self, valor: float) -> None:
        if valor < 0:
            raise ValueError("Saldo não pode ser negativo")
        self._saldo = valor
```

```python
conta = ContaBancaria("Ana", 100)
print(conta.saldo)   # 100 — usa o getter
conta.depositar(50)
print(conta.saldo)   # 150
# conta._saldo = -999  # tecnicamente funciona, mas quebra o encapsulamento
```

:::aviso
`_atributo` (um underline) sinaliza "interno — use com cuidado". `__atributo` (dois underlines) aplica *name mangling*: Python renomeia para `_NomeDaClasse__atributo`, dificultando acesso externo acidental.
:::

---

## 6.4 `@classmethod` e `@staticmethod`

Além de métodos de instância (que recebem `self`), classes podem ter dois outros tipos:

### `@classmethod` — recebe a própria classe como primeiro argumento

Útil para criar construtores alternativos ou operar sobre atributos de classe:

```python
class Data:
    def __init__(self, dia: int, mes: int, ano: int):
        self.dia = dia
        self.mes = mes
        self.ano = ano

    @classmethod
    def de_string(cls, texto: str) -> "Data":
        """Construtor alternativo: '25/12/2024' → Data(25, 12, 2024)"""
        dia, mes, ano = map(int, texto.split("/"))
        return cls(dia, mes, ano)   # cls é a própria classe Data

    def __str__(self) -> str:
        return f"{self.dia:02d}/{self.mes:02d}/{self.ano}"

natal = Data.de_string("25/12/2024")
print(natal)   # 25/12/2024
```

### `@staticmethod` — não recebe `self` nem `cls`

Funções relacionadas à classe mas que não precisam acessar instância ou classe:

```python
class Temperatura:
    def __init__(self, celsius: float):
        self.celsius = celsius

    @staticmethod
    def celsius_para_fahrenheit(c: float) -> float:
        return c * 9 / 5 + 32

    @staticmethod
    def fahrenheit_para_celsius(f: float) -> float:
        return (f - 32) * 5 / 9

print(Temperatura.celsius_para_fahrenheit(100))   # 212.0
print(Temperatura.fahrenheit_para_celsius(32))    # 0.0
```

---

## 6.5 Herança e polimorfismo

Herança permite criar uma classe com base em outra, reutilizando e estendendo o comportamento:

```python
class Animal:
    def __init__(self, nome: str):
        self.nome = nome

    def falar(self) -> str:
        return "..."

    def apresentar(self) -> str:
        return f"Sou {self.nome} e digo: {self.falar()}"

class Cachorro(Animal):
    def falar(self) -> str:        # sobrescreve o método pai
        return "Au au!"

class Gato(Animal):
    def falar(self) -> str:
        return "Miau!"

class Papagaio(Animal):
    def __init__(self, nome: str, frase: str):
        super().__init__(nome)     # chama __init__ do pai
        self.frase = frase

    def falar(self) -> str:
        return self.frase

animais = [Cachorro("Rex"), Gato("Mimi"), Papagaio("Louro", "Quero biscoito!")]
for animal in animais:
    print(animal.apresentar())   # polimorfismo: mesmo método, comportamentos diferentes
```

### `isinstance()` e `issubclass()`

```python
rex = Cachorro("Rex")

print(isinstance(rex, Cachorro))   # True
print(isinstance(rex, Animal))     # True — Cachorro herda de Animal
print(isinstance(rex, Gato))       # False

print(issubclass(Cachorro, Animal))  # True
print(issubclass(Animal, Cachorro))  # False
```

`isinstance()` é preferível a `type(obj) == Tipo` porque respeita a hierarquia de herança.

---

## 6.6 Composição vs Herança

Herança modela relação **"é um"**: `Cachorro` *é um* `Animal`.
Composição modela relação **"tem um"**: `Carro` *tem um* `Motor`.

Nem toda relação deve ser herança. Prefira composição quando a classe-filha não for verdadeiramente um subtipo da classe-pai:

```python
# ❌ Herança forçada — Motor não é um Carro
class Motor:
    def __init__(self, cavalos: int):
        self.cavalos = cavalos

    def ligar(self) -> str:
        return f"Motor de {self.cavalos}cv ligado"

class Carro:
    def __init__(self, modelo: str, cavalos: int):
        self.modelo = modelo
        self.motor  = Motor(cavalos)   # ✅ Composição — Carro TEM um Motor

    def ligar(self) -> str:
        return f"{self.modelo}: {self.motor.ligar()}"

fusca = Carro("Fusca", 50)
print(fusca.ligar())   # Fusca: Motor de 50cv ligado
```

:::dica
**Regra prática:** antes de usar herança, pergunte: "X *é um* Y em todos os contextos?" Se a resposta for sim, herança faz sentido. Se for "X *usa* Y" ou "X *tem* Y", prefira composição.
:::

---

## 6.7 Métodos especiais (dunder methods)

Python usa métodos com dois underlines de cada lado para comportamentos especiais — operadores, conversões, comparações:

| Método | Quando é chamado |
|--------|-----------------|
| `__init__` | ao criar o objeto (`Classe()`) |
| `__str__` | `str(obj)` e `print(obj)` — legível para humanos |
| `__repr__` | representação no terminal/debug — deve ser precisa |
| `__len__` | `len(obj)` |
| `__eq__` | `obj1 == obj2` |
| `__lt__` | `obj1 < obj2` |
| `__add__` | `obj1 + obj2` |

### `__str__` vs `__repr__`

```python
class Produto:
    def __init__(self, nome: str, preco: float):
        self.nome  = nome
        self.preco = preco

    def __str__(self) -> str:
        # Para o usuário — formatado e legível
        return f"{self.nome} — R${self.preco:.2f}"

    def __repr__(self) -> str:
        # Para o desenvolvedor — preciso, idealmente reproduzível
        return f"Produto(nome={self.nome!r}, preco={self.preco})"

p = Produto("Camiseta", 49.9)
print(p)        # Camiseta — R$49.90       (usa __str__)
print(repr(p))  # Produto(nome='Camiseta', preco=49.9)  (usa __repr__)
```

### Comparação e ordenação

```python
class Aluno:
    def __init__(self, nome: str, nota: float):
        self.nome  = nome
        self.nota  = nota

    def __str__(self) -> str:
        return f"{self.nome}: {self.nota}"

    def __eq__(self, outro: "Aluno") -> bool:
        return self.nota == outro.nota

    def __lt__(self, outro: "Aluno") -> bool:
        return self.nota < outro.nota

turma = [Aluno("Bruno", 7.5), Aluno("Ana", 9.0), Aluno("Carlos", 8.0)]
turma.sort()   # usa __lt__ para ordenar
for a in turma:
    print(a)
# Bruno: 7.5
# Carlos: 8.0
# Ana: 9.0
```

### `__len__` e outros

```python
class Playlist:
    def __init__(self, nome: str):
        self.nome   = nome
        self._musicas: list[str] = []

    def adicionar(self, musica: str) -> None:
        self._musicas.append(musica)

    def __len__(self) -> int:
        return len(self._musicas)

    def __str__(self) -> str:
        return f"Playlist '{self.nome}' ({len(self)} músicas)"

p = Playlist("Favoritas")
p.adicionar("Bohemian Rhapsody")
p.adicionar("Stairway to Heaven")
print(len(p))   # 2
print(p)        # Playlist 'Favoritas' (2 músicas)
```

---

## Exercícios

Os exercícios deste módulo pedem classes funcionais. A plataforma vai criar objetos e chamar métodos — garanta que os atributos e retornos seguem o enunciado exatamente.

---

## Mini-projeto: Sistema Bancário

Implemente um mini-banco com as classes:

1. `Conta(titular, saldo_inicial)` — depositar, sacar, saldo (property), `__str__`
2. `ContaPoupanca(Conta, taxa_juros)` — herda de `Conta`, adiciona `render_juros()`
3. `Banco(nome)` — cria contas, busca por titular, lista em ordem alfabética

O sistema deve validar saques, calcular rendimentos e mostrar extrato formatado.

---

## Quiz

Teste seus conhecimentos sobre POO.

---

## Resumo

✔ `class Nome:` — define um novo tipo; `__init__` inicializa os atributos  
✔ `self` referencia a instância; atributos de classe são compartilhados  
✔ `_atributo` — convenção de privacidade; `@property` expõe com controle  
✔ `@classmethod` recebe a classe (`cls`); `@staticmethod` não recebe nenhum  
✔ Herança `class Filho(Pai)` + `super()` para chamar o pai  
✔ `isinstance(obj, Tipo)` — verifica tipo respeitando herança  
✔ Composição ("tem um") é muitas vezes melhor que herança ("é um")  
✔ `__str__` para humanos, `__repr__` para desenvolvedores  
✔ `__eq__`, `__lt__`, `__len__` habilitam `==`, `<` e `len()` nos seus objetos  

## Próximos passos

No Módulo 7 você vai aprender a trabalhar com arquivos, JSON e tratar erros — habilidades essenciais para criar programas que persistem dados e se comunicam com o mundo externo.
