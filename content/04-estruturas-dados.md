---
modulo: "04"
titulo: "Estruturas de Dados"
duracao_estimada: "7-9h"
pre_requisito: "03"
---

# Módulo 4 — Estruturas de Dados

## Por que isso importa?

Imagine guardar as notas de 30 alunos. Com o que você aprendeu até agora, precisaria de 30 variáveis: `nota1`, `nota2`, ..., `nota30`. Impraticável.

Estruturas de dados permitem organizar múltiplos valores em uma única variável — e trabalhar com eles de forma eficiente. São a diferença entre código que escala e código que entra em colapso com a primeira turma de 100 alunos.

## O que você vai aprender

- **Listas** — sequências mutáveis de elementos
- **Tuplas** — sequências imutáveis
- **Dicionários** — mapeamento de chaves para valores
- **Sets** — coleções de elementos únicos
- Como iterar, buscar e modificar cada estrutura

---

## 4.1 Listas

A estrutura de dados mais usada em Python. Uma lista é uma sequência ordenada de elementos que pode ser modificada depois de criada.

```python
frutas = ['banana', 'manga', 'uva']
notas  = [7.5, 8.0, 9.5, 6.0]
mistura = [1, 'Python', True, 3.14]  # pode misturar tipos
```

**Acessando elementos:**

```python
frutas = ['banana', 'manga', 'uva']

print(frutas[0])   # 'banana' — índice começa em 0
print(frutas[-1])  # 'uva'    — índice negativo conta do fim
print(frutas[1:])  # ['manga', 'uva'] — fatiamento (slice)
```

**Operações comuns:**

```python
frutas.append('abacaxi')     # adiciona no fim
frutas.insert(0, 'acerola')  # insere na posição 0
frutas.remove('manga')       # remove pelo valor (primeiro encontrado)
frutas.pop()                 # remove e retorna o último elemento
frutas.sort()                # ordena in-place (modifica a lista)
sorted(frutas)               # retorna nova lista ordenada (original intacta)
len(frutas)                  # tamanho da lista
```

:::tente
```python
numeros = [5, 2, 8, 1, 9, 3]
numeros.sort()
print(numeros)           # [1, 2, 3, 5, 8, 9]
print(numeros[-1])       # 9 (maior elemento)
print(sum(numeros))      # 28
print(sum(numeros)/len(numeros))  # média
```
:::

---

## 4.2 Iterando sobre listas

```python
notas = [7.5, 8.0, 9.5, 6.0]

# Iterando por valor
for nota in notas:
    print(nota)

# Iterando com índice
for i, nota in enumerate(notas):
    print(f"Aluno {i+1}: {nota}")

# List comprehension — forma concisa de criar listas
aprovados = [n for n in notas if n >= 7]
dobros     = [n * 2 for n in notas]
```

:::dica
**List comprehension** é uma sintaxe elegante para criar listas a partir de outras. `[expressão for item in lista if condição]`. Não é obrigatório — o `for` tradicional faz o mesmo, mas comprehension é mais Pythônico.
:::

---

## 4.3 Tuplas

Tuplas são como listas, mas **imutáveis** — depois de criadas, não podem ser modificadas. Use-as para dados que não devem mudar:

```python
coordenadas = (23.5, -46.6)  # latitude, longitude
dias_semana = ('seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom')

print(coordenadas[0])     # 23.5
# coordenadas[0] = 10     # ERRO! Tupla é imutável

# Desempacotamento (muito útil!)
latitude, longitude = coordenadas
print(latitude)   # 23.5
```

:::curiosidade
**Por que usar tuplas se as listas fazem mais coisas?**
Imutabilidade é uma garantia: ao passar uma tupla para uma função, você sabe que ela não pode ser alterada por acidente. Isso torna o código mais previsível e seguro.
:::

---

## 4.4 Dicionários

Dicionários mapeiam **chaves** a **valores**. Pense numa agenda telefônica: o nome é a chave, o número é o valor.

```python
aluno = {
    'nome':  'Felipe',
    'nota':  8.5,
    'turma': 'A'
}

# Acesso
print(aluno['nome'])          # 'Felipe'
print(aluno.get('email', '')) # '' — .get() retorna default se chave não existir

# Modificação
aluno['nota'] = 9.0           # atualiza valor existente
aluno['email'] = 'f@mail.com' # adiciona nova chave

# Remoção
del aluno['turma']

# Verificar existência
if 'nome' in aluno:
    print("tem nome")
```

**Iterando sobre dicionários:**

```python
turma = {'Ana': 9.0, 'Bruno': 7.5, 'Carlos': 8.0}

for nome in turma:              # itera sobre chaves
    print(nome)

for nome, nota in turma.items(): # itera sobre pares
    print(f"{nome}: {nota}")
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

Sets são coleções de elementos **únicos e não ordenados**. Úteis para eliminar duplicatas e fazer operações de conjunto (união, interseção):

```python
numeros = {1, 2, 3, 2, 1, 4}
print(numeros)   # {1, 2, 3, 4} — duplicatas removidas automaticamente

# Operações de conjunto
a = {1, 2, 3, 4}
b = {3, 4, 5, 6}

print(a | b)     # União:     {1, 2, 3, 4, 5, 6}
print(a & b)     # Interseção: {3, 4}
print(a - b)     # Diferença:  {1, 2}
```

:::dica
**Verificação de pertencimento é muito rápida em sets.**
`item in minha_lista` precisa percorrer a lista inteira (O(n)). `item in meu_set` é quase instantâneo (O(1)), mesmo com milhões de elementos. Use sets quando precisar verificar pertencimento com frequência.
:::

---

## Exercícios

Os exercícios deste módulo combinam listas, dicionários e sets. Antes de codar, identifique qual estrutura é mais adequada para cada problema e por quê.

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

✔ **Lista** `[]` — ordenada, mutável, aceita duplicatas  
✔ **Tupla** `()` — ordenada, imutável, mais segura  
✔ **Dicionário** `{}` — chave→valor, acesso O(1) pela chave  
✔ **Set** `{}` — elementos únicos, busca rápida, sem ordem  
✔ `append`, `remove`, `pop`, `sort` — métodos das listas  
✔ `.get()` — acesso seguro a dicionário sem KeyError  

## Próximos passos

No Módulo 5 você vai aprender a criar suas próprias funções — blocos de código reutilizáveis com nome, parâmetros e retorno de valores.
