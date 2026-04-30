---
modulo: "07"
titulo: "Arquivos, JSON e Tratamento de Erros"
duracao_estimada: "6-8h"
pre_requisito: "06"
---

# Módulo 7 — Arquivos, JSON e Tratamento de Erros

## Por que isso importa?

Até agora todos os dados do programa somem quando ele encerra. Para criar aplicações reais — agendas, jogos com save, sistemas de cadastro — você precisa persistir dados.

Neste módulo você aprende a ler e escrever arquivos, trabalhar com JSON (o formato de dados da web) e tratar erros de forma elegante em vez de deixar o programa travar.

## O que você vai aprender

- Ler e escrever arquivos com `open()`
- O bloco `with` para gerenciar recursos
- Serialização de dados com `json`
- `try/except/finally` para tratamento de erros
- Criar exceções personalizadas

---

## 7.1 Lendo e escrevendo arquivos

```python
# Escrevendo — modo 'w' cria ou sobrescreve
with open('notas.txt', 'w', encoding='utf-8') as f:
    f.write("Ana: 9.0\n")
    f.write("Bruno: 7.5\n")

# Lendo todo o conteúdo
with open('notas.txt', 'r', encoding='utf-8') as f:
    conteudo = f.read()
    print(conteudo)

# Lendo linha por linha
with open('notas.txt', 'r', encoding='utf-8') as f:
    for linha in f:
        print(linha.strip())  # strip() remove \n

# Adicionando ao final — modo 'a' (append)
with open('notas.txt', 'a', encoding='utf-8') as f:
    f.write("Carlos: 8.0\n")
```

:::dica
**Por que usar `with`?**
O bloco `with` garante que o arquivo seja fechado mesmo se ocorrer um erro. Sem `with`, se seu código levantar uma exceção antes de `f.close()`, o arquivo fica aberto — pode causar perda de dados e consumo desnecessário de recursos.
:::

---

## 7.2 JSON — O formato da web

JSON (JavaScript Object Notation) é o formato mais usado para trocar dados na web. Python dicionários se convertem naturalmente para/de JSON:

```python
import json

# Python → JSON string
aluno = {'nome': 'Felipe', 'nota': 9.5, 'ativo': True}
json_str = json.dumps(aluno, ensure_ascii=False, indent=2)
print(json_str)
# {
#   "nome": "Felipe",
#   "nota": 9.5,
#   "ativo": true
# }

# JSON string → Python
dados = json.loads('{"nome": "Ana", "nota": 8.0}')
print(dados['nome'])   # Ana

# Salvar em arquivo JSON
with open('alunos.json', 'w', encoding='utf-8') as f:
    json.dump(aluno, f, ensure_ascii=False, indent=2)

# Carregar de arquivo JSON
with open('alunos.json', 'r', encoding='utf-8') as f:
    aluno_carregado = json.load(f)
```

:::curiosidade
**`ensure_ascii=False`** garante que caracteres especiais como acentos sejam salvos corretamente. Sem isso, "ação" vira `"ção"` no arquivo.
:::

:::tente
```python
import json

# Simula um mini banco de dados em memória
cadastro = [
    {'nome': 'Ana',    'xp': 150},
    {'nome': 'Bruno',  'xp': 200},
    {'nome': 'Carlos', 'xp': 80},
]

# Ordena por XP e imprime
for aluno in sorted(cadastro, key=lambda a: a['xp'], reverse=True):
    print(f"{aluno['nome']}: {aluno['xp']} XP")
```
:::

---

## 7.3 Tratamento de erros com try/except

Erros em Python se chamam **exceções**. Em vez de deixar o programa travar, você pode capturá-las e tratar de forma adequada:

```python
# Sem tratamento — programa trava se entrada for inválida
numero = int(input("Digite um número: "))   # Trava com "abc"

# Com tratamento — mensagem amigável
try:
    numero = int(input("Digite um número: "))
    resultado = 100 / numero
    print(f"100 / {numero} = {resultado}")
except ValueError:
    print("Por favor, digite um número válido.")
except ZeroDivisionError:
    print("Não é possível dividir por zero.")
```

**Estrutura completa:**

```python
try:
    # código que pode falhar
    resultado = operacao_arriscada()
except TipoDeErroEspecifico as e:
    # trata erros do tipo específico
    print(f"Erro específico: {e}")
except (OutroErro, MaisUmErro):
    # trata múltiplos tipos
    print("Um desses erros ocorreu")
except Exception as e:
    # captura qualquer exceção (use com cuidado)
    print(f"Erro inesperado: {e}")
else:
    # executa APENAS se não houve exceção
    print("Tudo certo!")
finally:
    # SEMPRE executa, com ou sem erro
    print("Isso roda sempre")
```

:::aviso
**Não use `except Exception` como regra geral!**
Capturar qualquer exceção esconde bugs reais. Seja específico: `except ValueError`, `except FileNotFoundError`, etc. Use `except Exception` apenas em pontos de entrada da aplicação onde você quer logar erros inesperados.
:::

---

## 7.4 Exceções personalizadas

Crie suas próprias exceções herdando de `Exception` ou de um tipo mais específico:

```python
class SaldoInsuficienteError(ValueError):
    def __init__(self, saldo_atual, valor_solicitado):
        self.saldo_atual       = saldo_atual
        self.valor_solicitado  = valor_solicitado
        super().__init__(
            f"Saldo insuficiente: tem R${saldo_atual}, pediu R${valor_solicitado}"
        )

# Usando
try:
    raise SaldoInsuficienteError(100.0, 250.0)
except SaldoInsuficienteError as e:
    print(e)  # Saldo insuficiente: tem R$100.0, pediu R$250.0
```

---

## Exercícios

Os exercícios cobrem manipulação de strings como textos (simulando arquivos), JSON e tratamento de erros. Como o ambiente roda no navegador, usamos strings em vez de arquivos reais — o conceito é idêntico.

---

## Mini-projeto: Caderno de Notas Persistente

Crie um sistema de notas que simula persistência com JSON em string:

1. `Nota(titulo, conteudo, tags=[])` — classe de nota
2. `Caderno` — gerencia uma lista de notas
   - `adicionar(nota)`
   - `buscar(termo)` — busca em título e conteúdo
   - `por_tag(tag)` — filtra por tag
   - `exportar_json()` → string JSON com todas as notas
   - `importar_json(json_str)` → carrega notas de JSON
3. O sistema deve tratar erros de JSON malformado com mensagem amigável

---

## Quiz

Teste seus conhecimentos sobre arquivos, JSON e erros.

---

## Resumo

✔ `with open(arquivo, modo) as f:` — abertura segura  
✔ `json.dumps()` / `json.loads()` — serialização Python↔JSON  
✔ `json.dump()` / `json.load()` — para arquivos  
✔ `try/except/else/finally` — tratamento estruturado de erros  
✔ Seja específico nas exceções capturadas  
✔ Crie exceções próprias herdando de `Exception`  

## Próximos passos

No Módulo 8 você vai construir aplicações reais: consumir APIs, criar interfaces visuais com Streamlit e Flet, e servidores web com Flask e FastAPI.
