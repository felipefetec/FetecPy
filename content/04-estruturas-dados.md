---
modulo: "04"
titulo: "Estruturas de Dados"
duracao_estimada: "7-9h"
pre_requisito: "03"
---

# Módulo 4 — Estruturas de Dados

## Quando uma variável não basta

Imagine o histórico de preços de uma ação nos últimos 365 dias. Ou os pedidos de um restaurante num sábado à noite. Gerenciar isso com variáveis individuais é inviável — você precisaria saber de antemão exatamente quantas terá, e nomear cada uma.

Estruturas de dados resolvem isso: agrupam valores relacionados, crescem conforme necessário e permitem buscar, ordenar e filtrar com poucas linhas de código.

## O que você vai aprender

- **Listas** — sequências mutáveis de elementos
- **Tuplas** — sequências imutáveis
- **Dicionários** — mapeamento de chaves para valores
- **Sets** — coleções de elementos únicos
- Como iterar, buscar e modificar cada estrutura
- Estruturas aninhadas — o formato real dos dados na web

---

## 4.1 Listas

A estrutura de dados mais usada em Python. Uma lista é uma sequência ordenada de elementos que pode ser modificada depois de criada.

```python
frutas = ['banana', 'manga', 'uva']
notas  = [7.5, 8.0, 9.5, 6.0]
mistura = [1, 'Python', True, 3.14]  # pode misturar tipos
```

### Acessando elementos e slicing

```python
frutas = ['banana', 'manga', 'uva', 'abacaxi', 'kiwi']

print(frutas[0])    # 'banana'  — índice começa em 0
print(frutas[-1])   # 'kiwi'   — índice negativo conta do fim
print(frutas[1:3])  # ['manga', 'uva']  — slice: do índice 1 até (não incluindo) 3
print(frutas[:2])   # ['banana', 'manga']  — do início até 2
print(frutas[2:])   # ['uva', 'abacaxi', 'kiwi']  — do 2 até o fim
print(frutas[::2])  # ['banana', 'uva', 'kiwi']   — de 2 em 2
print(frutas[::-1]) # ['kiwi', 'abacaxi', 'uva', 'manga', 'banana']  — invertida
```

### Operações comuns

```python
frutas.append('acerola')       # adiciona no fim
frutas.insert(0, 'abacate')    # insere na posição 0
frutas.extend(['limão', 'pera']) # adiciona todos os itens de outra lista
frutas.remove('manga')         # remove pelo valor (primeiro encontrado)
frutas.pop()                   # remove e retorna o último elemento
frutas.pop(1)                  # remove e retorna o elemento do índice 1
frutas.sort()                  # ordena in-place (modifica a lista)
sorted(frutas)                 # retorna nova lista ordenada (original intacta)
frutas.reverse()               # inverte in-place
len(frutas)                    # tamanho
frutas.count('banana')         # quantas vezes 'banana' aparece
frutas.index('uva')            # índice da primeira ocorrência de 'uva'
'manga' in frutas              # True ou False — verifica pertencimento
```

### Cuidado: cópia vs. alias

Um erro muito comum em Python:

```python
a = [1, 2, 3]
b = a          # b NÃO é uma cópia — ambos apontam para a mesma lista!
b.append(4)
print(a)       # [1, 2, 3, 4] — a também mudou!

# Para criar uma cópia independente:
c = a.copy()   # ou: c = a[:]
c.append(99)
print(a)       # [1, 2, 3, 4] — a não mudou
print(c)       # [1, 2, 3, 4, 99]
```

:::tente
```python
numeros = [5, 2, 8, 1, 9, 3]
numeros.sort()
print(numeros)                       # [1, 2, 3, 5, 8, 9]
print(numeros[-1])                   # 9  — maior elemento
print(numeros[::-1])                 # [9, 8, 5, 3, 2, 1]  — invertida
print(sum(numeros) / len(numeros))   # média
```
:::

---

## 4.2 Iterando sobre listas

```python
notas = [7.5, 8.0, 9.5, 6.0]

# Por valor
for nota in notas:
    print(nota)

# Com índice
for i, nota in enumerate(notas):
    print(f"Aluno {i+1}: {nota}")

# Duas listas em paralelo com zip()
nomes = ['Ana', 'Bruno', 'Carlos']
notas = [9.0, 7.5, 8.0]
for nome, nota in zip(nomes, notas):
    print(f"{nome}: {nota}")
```

### List comprehension

Forma concisa de criar listas a partir de outras:

```python
notas = [7.5, 8.0, 9.5, 6.0, 5.5]

aprovados = [n for n in notas if n >= 7]       # filtra
dobros     = [n * 2 for n in notas]            # transforma
situacao   = ['ok' if n >= 7 else 'rec' for n in notas]  # transforma com condição
```

:::dica
`[expressão for item in lista if condição]` — a condição é opcional. Não é obrigatório usar comprehension: o `for` tradicional faz o mesmo. Use comprehension quando o código ficar mais claro, não mais confuso.
:::

---

## 4.3 Tuplas

Tuplas são como listas, mas **imutáveis** — depois de criadas, não podem ser modificadas. Use para dados que não devem mudar:

```python
coordenadas = (23.5, -46.6)           # latitude, longitude
dias_semana = ('seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom')

print(coordenadas[0])    # 23.5
# coordenadas[0] = 10   # ERRO — tupla é imutável

# Desempacotamento — muito útil para funções que retornam múltiplos valores
latitude, longitude = coordenadas
print(latitude)    # 23.5

# Tuplas como chaves de dicionário (listas não podem ser chaves — são mutáveis)
mapa = {(0, 0): 'origem', (1, 0): 'direita', (0, 1): 'cima'}
print(mapa[(0, 0)])   # 'origem'
```

:::curiosidade
**Por que usar tuplas se as listas fazem mais coisas?**
Imutabilidade é uma garantia. Ao passar uma tupla para uma função, você sabe que ela não pode ser alterada por acidente. Além disso, tuplas podem ser usadas como chaves de dicionário — listas não, porque são mutáveis.
:::

---

## 4.4 Dicionários

Dicionários mapeiam **chaves** a **valores**. O acesso por chave é O(1) — não importa se tem 10 ou 10 milhões de itens, a busca é igualmente rápida.

```python
aluno = {
    'nome':  'Felipe',
    'nota':  8.5,
    'turma': 'A'
}

# Acesso
print(aluno['nome'])           # 'Felipe' — KeyError se não existir
print(aluno.get('email', ''))  # '' — .get() retorna default se chave não existir

# Modificação
aluno['nota'] = 9.0            # atualiza valor existente
aluno['email'] = 'f@mail.com'  # adiciona nova chave
del aluno['turma']             # remove

# Verificar existência
if 'nome' in aluno:
    print("tem nome")

# Métodos de acesso
print(aluno.keys())    # dict_keys(['nome', 'nota', 'email'])
print(aluno.values())  # dict_values(['Felipe', 9.0, 'f@mail.com'])
print(aluno.items())   # dict_items([('nome', 'Felipe'), ...])

# Mesclar dois dicionários (Python 3.9+)
extra = {'cidade': 'SP', 'ativo': True}
completo = aluno | extra   # novo dicionário com todos os campos
# ou: aluno.update(extra)  # modifica aluno in-place
```

### Iterando sobre dicionários

```python
turma = {'Ana': 9.0, 'Bruno': 7.5, 'Carlos': 8.0}

for nome in turma:               # itera sobre chaves
    print(nome)

for nome, nota in turma.items():  # itera sobre pares chave-valor
    print(f"{nome}: {nota}")
```

### Dictionary comprehension

```python
notas = {'Ana': 9.0, 'Bruno': 5.5, 'Carlos': 7.0}

# Filtra só aprovados (nota >= 7)
aprovados = {nome: nota for nome, nota in notas.items() if nota >= 7}
# {'Ana': 9.0, 'Carlos': 7.0}

# Transforma valores
arredondadas = {nome: round(nota) for nome, nota in notas.items()}
# {'Ana': 9, 'Bruno': 6, 'Carlos': 7}
```

:::tente
```python
# Conte a frequência de cada letra em uma palavra
palavra = "abracadabra"
frequencia = {}
for letra in palavra:
    frequencia[letra] = frequencia.get(letra, 0) + 1
for letra, qtd in sorted(frequencia.items()):
    print(f"{letra}: {qtd}")
```
:::

---

## 4.5 Sets (Conjuntos)

Sets são coleções de elementos **únicos e não ordenados**. Úteis para eliminar duplicatas e para operações de conjunto:

```python
numeros = {1, 2, 3, 2, 1, 4}
print(numeros)   # {1, 2, 3, 4} — duplicatas removidas automaticamente

vazio = set()    # set vazio — {} cria dicionário vazio, não set!
```

### Adicionando e removendo elementos

```python
tags = {'python', 'backend', 'api'}
tags.add('flask')          # adiciona um elemento
tags.discard('api')        # remove sem erro se não existir
tags.remove('backend')     # remove — KeyError se não existir
```

### Operações de conjunto

```python
a = {1, 2, 3, 4}
b = {3, 4, 5, 6}

print(a | b)          # União:        {1, 2, 3, 4, 5, 6}
print(a & b)          # Interseção:   {3, 4}
print(a - b)          # Diferença:    {1, 2}  — em a mas não em b
print(a ^ b)          # Diferença sim.: {1, 2, 5, 6}  — em um mas não em ambos

print({1, 2}.issubset({1, 2, 3}))    # True — {1,2} está contido em {1,2,3}
print({1, 2, 3}.issuperset({1, 2}))  # True
```

:::dica
**Verificação de pertencimento é muito rápida em sets.**
`item in lista` percorre a lista inteira (O(n)). `item in meu_set` é quase instantâneo (O(1)), mesmo com milhões de elementos. Use sets quando precisar verificar pertencimento com frequência.
:::

---

## 4.6 Estruturas aninhadas

Na prática, dados raramente vêm em listas simples. APIs web retornam listas de dicionários — e você precisa saber navegar por elas:

```python
# Lista de dicionários — o formato mais comum em dados reais
alunos = [
    {'nome': 'Ana',    'nota': 9.0, 'turma': 'A'},
    {'nome': 'Bruno',  'nota': 7.5, 'turma': 'B'},
    {'nome': 'Carlos', 'nota': 8.0, 'turma': 'A'},
]

# Acessar um aluno específico
print(alunos[0]['nome'])   # 'Ana'

# Iterar e filtrar
turma_a = [a for a in alunos if a['turma'] == 'A']
media    = sum(a['nota'] for a in alunos) / len(alunos)

# Ordenar por nota
por_nota = sorted(alunos, key=lambda a: a['nota'], reverse=True)
for a in por_nota:
    print(f"{a['nome']}: {a['nota']}")
```

```python
# Dicionário de listas — agrupar dados por categoria
por_turma = {}
for aluno in alunos:
    turma = aluno['turma']
    if turma not in por_turma:
        por_turma[turma] = []
    por_turma[turma].append(aluno['nome'])

print(por_turma)
# {'A': ['Ana', 'Carlos'], 'B': ['Bruno']}
```

:::tente
```python
produtos = [
    {'nome': 'Camiseta', 'preco': 49.90, 'estoque': 15},
    {'nome': 'Tênis',    'preco': 199.0, 'estoque': 3},
    {'nome': 'Boné',     'preco': 35.0,  'estoque': 0},
]

# Apenas disponíveis, ordenados por preço
disponiveis = sorted(
    [p for p in produtos if p['estoque'] > 0],
    key=lambda p: p['preco']
)
for p in disponiveis:
    print(f"{p['nome']}: R${p['preco']:.2f}")
```
:::

---

## Quando usar cada estrutura

| Situação | Estrutura ideal |
|----------|-----------------|
| Sequência que vai crescer ou mudar | Lista `[]` |
| Coordenadas, RGB, dados fixos | Tupla `()` |
| Associar rótulos a valores | Dicionário `{}` |
| Eliminar duplicatas / verificar pertencimento | Set `{}` |
| Dados de API / banco de dados | Lista de dicionários |
| Agrupar itens por categoria | Dicionário de listas |

---

## Exercícios

Antes de codar, identifique qual estrutura é mais adequada para cada problema e por quê — isso é parte do exercício.

---

## Mini-projeto: Agenda de Contatos

Crie uma agenda de contatos em memória que permita:

1. **Adicionar** contato (nome e telefone)
2. **Buscar** contato pelo nome
3. **Remover** contato
4. **Listar** todos os contatos em ordem alfabética
5. Sair do programa

Use um dicionário `{nome: telefone}` como estrutura principal. O menu deve se repetir até o usuário escolher sair.

---

## Quiz

Teste seus conhecimentos sobre estruturas de dados.

---

## Resumo

✔ **Lista** `[]` — ordenada, mutável, aceita duplicatas; `a = b` cria alias, não cópia  
✔ **Tupla** `()` — ordenada, imutável; pode ser chave de dicionário  
✔ **Dicionário** `{}` — chave→valor, acesso O(1); `.get()` evita KeyError  
✔ **Set** `{}` — elementos únicos, busca O(1); `set()` para criar vazio  
✔ `zip()` — itera duas listas em paralelo  
✔ `[expr for x in lista if cond]` — list comprehension  
✔ `{k: v for k, v in d.items()}` — dict comprehension  
✔ Lista de dicionários — formato padrão de dados reais  

## Próximos passos

No Módulo 5 você vai aprender a criar suas próprias funções — blocos de código reutilizáveis com nome, parâmetros e retorno de valores.
