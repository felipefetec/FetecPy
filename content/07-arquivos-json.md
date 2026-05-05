---
modulo: "07"
titulo: "Arquivos, JSON e Tratamento de Erros"
duracao_estimada: "8-10h"
pre_requisito: "06"
---

# Módulo 7 — Arquivos, JSON e Tratamento de Erros

## O que acontece quando o programa fecha?

Até agora todos os dados do programa somem quando ele encerra. Para criar aplicações reais — agendas, jogos com save, sistemas de cadastro, integrações com APIs — você precisa de três habilidades:

1. **Persistir dados** — ler e escrever arquivos
2. **Falar com a web** — JSON é o idioma universal de APIs
3. **Sobreviver a erros** — programas reais encontram situações inesperadas

Neste módulo você aprende as três. A partir daqui, seus programas podem sobreviver ao mundo real.

## O que você vai aprender

- Ler e escrever arquivos com `open()` e `pathlib`
- Todos os modos de abertura de arquivo e quando usar cada um
- Serialização de dados com o módulo `json`
- Como JSON mapeia para tipos Python e vice-versa
- `try/except/else/finally` — tratamento estruturado de erros
- As exceções built-in mais comuns e quando cada uma aparece
- `raise` e `raise from` para propagar erros com contexto
- Criar hierarquias de exceções personalizadas

---

## 7.1 Lendo e escrevendo arquivos

### Abrindo arquivos com `open()`

A função `open()` retorna um objeto de arquivo. Sempre use com `with` — ele garante que o arquivo seja fechado mesmo que ocorra um erro:

```python
# Escrevendo — modo 'w' cria ou sobrescreve
with open('notas.txt', 'w', encoding='utf-8') as f:
    f.write("Ana: 9.0\n")
    f.write("Bruno: 7.5\n")

# Lendo todo o conteúdo de uma vez
with open('notas.txt', 'r', encoding='utf-8') as f:
    conteudo = f.read()
    print(conteudo)

# Lendo linha por linha (econômico para arquivos grandes)
with open('notas.txt', 'r', encoding='utf-8') as f:
    for linha in f:
        print(linha.strip())  # strip() remove o \n do final

# Lendo todas as linhas como lista
with open('notas.txt', 'r', encoding='utf-8') as f:
    linhas = f.readlines()   # ['Ana: 9.0\n', 'Bruno: 7.5\n']

# Adicionando ao final sem apagar o que já existe
with open('notas.txt', 'a', encoding='utf-8') as f:
    f.write("Carlos: 8.0\n")
```

:::dica
**Por que `encoding='utf-8'`?**
Sem especificar, Python usa o encoding padrão do sistema operacional — que varia entre Windows, Mac e Linux. Especificar `'utf-8'` garante que acentos e caracteres especiais funcionem igual em qualquer máquina.
:::

### Tabela de modos de abertura

| Modo | Descrição | Arquivo existe? | Cria se não existir? |
|------|-----------|-----------------|----------------------|
| `'r'` | Leitura (padrão) | Lê do início | ❌ Erro |
| `'w'` | Escrita | **Apaga tudo** e escreve | ✅ Cria |
| `'a'` | Append | Adiciona ao final | ✅ Cria |
| `'x'` | Criação exclusiva | ❌ Erro se já existe | ✅ Cria |
| `'r+'` | Leitura e escrita | Lê e escreve | ❌ Erro |
| `'b'` | Modo binário | Usado junto: `'rb'`, `'wb'` | — |

:::aviso
**Cuidado com `'w'`!** Ele apaga o conteúdo existente sem aviso. Se você quer preservar o arquivo e só adicionar linhas novas, use `'a'`.
:::

### Tratando arquivo inexistente

```python
try:
    with open('dados.txt', 'r', encoding='utf-8') as f:
        conteudo = f.read()
except FileNotFoundError:
    print("Arquivo não encontrado — criando vazio.")
    conteudo = ''
```

---

## 7.2 pathlib — O jeito moderno de lidar com caminhos

O módulo `pathlib` (Python 3.4+) representa caminhos como objetos, não strings. É mais legível e portável entre sistemas operacionais:

```python
from pathlib import Path

# Cria um objeto Path — funciona em Windows, Mac e Linux
arquivo = Path('dados') / 'notas.txt'   # dados/notas.txt

# Ler e escrever com uma linha
Path('config.txt').write_text("versao=1.0\n", encoding='utf-8')
conteudo = Path('config.txt').read_text(encoding='utf-8')

# Informações sobre o caminho
p = Path('relatorios/janeiro.csv')
print(p.name)     # janeiro.csv
print(p.stem)     # janeiro
print(p.suffix)   # .csv
print(p.parent)   # relatorios
print(p.exists()) # True ou False

# Listar arquivos de uma pasta
for arquivo in Path('.').glob('*.txt'):
    print(arquivo)

# Criar pasta (e subpastas) se não existir
Path('relatorios/2024').mkdir(parents=True, exist_ok=True)
```

:::dica
Para scripts simples, `open()` é suficiente. Para projetos maiores onde você manipula muitos caminhos, `pathlib` evita erros clássicos de concatenar strings como `pasta + '/' + arquivo` (que quebra no Windows).
:::

---

## 7.3 JSON — O formato da web

JSON (JavaScript Object Notation) é o formato mais usado para trocar dados na web. Qualquer API que você consumir provavelmente fala JSON.

### Tipos Python ↔ JSON

| Python | JSON | Python (ao carregar) |
|--------|------|----------------------|
| `dict` | `object` `{...}` | `dict` |
| `list`, `tuple` | `array` `[...]` | `list` |
| `str` | `string` `"..."` | `str` |
| `int`, `float` | `number` | `int` ou `float` |
| `True` / `False` | `true` / `false` | `bool` |
| `None` | `null` | `None` |

:::aviso
**Tuplas viram listas!** Ao fazer `json.loads(json.dumps((1, 2, 3)))` você obtém `[1, 2, 3]` — JSON não tem tipo tupla.
:::

### Operações básicas

```python
import json

# Python → JSON string (serialização)
aluno = {'nome': 'Felipe', 'nota': 9.5, 'ativo': True, 'tags': ['python', 'iniciante']}
json_str = json.dumps(aluno, ensure_ascii=False, indent=2)
print(json_str)
# {
#   "nome": "Felipe",
#   "nota": 9.5,
#   "ativo": true,
#   "tags": ["python", "iniciante"]
# }

# JSON string → Python (deserialização)
dados = json.loads('{"nome": "Ana", "nota": 8.0, "ativo": false}')
print(dados['nome'])   # Ana
print(type(dados))     # <class 'dict'>

# Salvar em arquivo JSON
with open('alunos.json', 'w', encoding='utf-8') as f:
    json.dump(aluno, f, ensure_ascii=False, indent=2)

# Carregar de arquivo JSON
with open('alunos.json', 'r', encoding='utf-8') as f:
    aluno_carregado = json.load(f)
```

### Tratando JSON malformado

Qualquer dado vindo de fora — usuário, arquivo, API — pode estar corrompido. Sempre trate `json.JSONDecodeError`:

```python
import json

def carregar_config(json_str):
    try:
        return json.loads(json_str)
    except json.JSONDecodeError as e:
        print(f"JSON inválido na linha {e.lineno}, coluna {e.colno}: {e.msg}")
        return {}

config = carregar_config('{"nome": "Felipe"')   # falta fechar }
# JSON inválido na linha 1, coluna 18: Expecting ',' or '}'
```

### Parâmetros úteis do `json.dumps`

```python
import json

dados = {'z': 3, 'a': 1, 'b': 2}

# sort_keys — ordena as chaves alfabeticamente
print(json.dumps(dados, sort_keys=True))
# {"a": 1, "b": 2, "z": 3}

# separators — compacto para transmissão (sem espaços)
print(json.dumps(dados, separators=(',', ':')))
# {"z":3,"a":1,"b":2}
```

:::tente
```python
import json

# Simula um mini banco de dados em memória
cadastro = [
    {'nome': 'Ana',    'xp': 150},
    {'nome': 'Bruno',  'xp': 200},
    {'nome': 'Carlos', 'xp': 80},
]

# Serializa, desserializa e ordena por XP
texto = json.dumps(cadastro, ensure_ascii=False)
carregado = json.loads(texto)

for aluno in sorted(carregado, key=lambda a: a['xp'], reverse=True):
    print(f"{aluno['nome']}: {aluno['xp']} XP")
```
:::

---

## 7.4 Tratamento de erros com try/except

Erros em Python se chamam **exceções**. Em vez de deixar o programa travar, você pode capturá-las e tratar de forma adequada.

### Estrutura completa

```python
try:
    numero = int(input("Digite um número: "))
    resultado = 100 / numero
except ValueError:
    # Captura erros de conversão — ex: 'abc' não vira int
    print("Por favor, digite um número válido.")
except ZeroDivisionError:
    # Captura divisão por zero
    print("Não é possível dividir por zero.")
except (TypeError, OverflowError):
    # Captura múltiplos tipos de uma vez
    print("Tipo ou valor inválido.")
else:
    # Executa APENAS se nenhuma exceção ocorreu
    print(f"100 / {numero} = {resultado}")
finally:
    # SEMPRE executa — com ou sem exceção
    # Bom para fechar conexões, liberar recursos
    print("Operação encerrada.")
```

### Exceções built-in mais comuns

| Exceção | Quando ocorre |
|---------|---------------|
| `ValueError` | Valor com tipo correto mas conteúdo inválido: `int('abc')` |
| `TypeError` | Operação com tipo errado: `'texto' + 42` |
| `KeyError` | Chave inexistente em dicionário: `d['chave']` |
| `IndexError` | Índice fora do alcance: `lista[99]` em lista de 3 elementos |
| `AttributeError` | Atributo ou método inexistente: `None.split()` |
| `FileNotFoundError` | Arquivo não existe ao tentar abrir |
| `ZeroDivisionError` | Divisão por zero |
| `ImportError` | Módulo não encontrado: `import modulo_inexistente` |
| `PermissionError` | Sem permissão para ler/escrever arquivo |
| `json.JSONDecodeError` | JSON malformado ao parsear |

### Acessando a mensagem de erro

```python
try:
    valor = int('não é número')
except ValueError as e:
    print(f"Erro capturado: {e}")
    # Erro capturado: invalid literal for int() with base 10: 'não é número'
```

### `raise` — levantando exceções

Use `raise` quando o código detecta uma condição inválida:

```python
def sacar(saldo, valor):
    if valor <= 0:
        raise ValueError(f"Valor de saque deve ser positivo, recebeu {valor}")
    if valor > saldo:
        raise ValueError(f"Saldo insuficiente: saldo={saldo}, saque={valor}")
    return saldo - valor

try:
    sacar(100, -50)
except ValueError as e:
    print(e)
```

### `raise from` — encadeando erros

Quando você captura um erro e lança outro, use `raise from` para preservar a causa original. Isso facilita muito o diagnóstico em logs:

```python
import json

def carregar_usuario(json_str):
    try:
        dados = json.loads(json_str)
    except json.JSONDecodeError as e:
        # O novo erro "sabe" que foi causado pelo JSONDecodeError
        raise ValueError("Dados de usuário inválidos") from e

try:
    carregar_usuario("{nome: sem aspas}")
except ValueError as e:
    print(e)           # Dados de usuário inválidos
    print(e.__cause__) # Expecting property name: line 1 column 2 (char 1)
```

:::aviso
**Não use `except Exception` como regra geral.** Capturar qualquer exceção esconde bugs reais. Seja específico: `except ValueError`, `except FileNotFoundError`, etc. Reserve `except Exception` para pontos de entrada da aplicação onde você quer logar erros inesperados sem travar tudo.
:::

---

## 7.5 Exceções personalizadas

Crie suas próprias exceções quando os tipos built-in não descrevem bem o problema do seu domínio.

### Exceção simples

```python
class SenhaFracaError(ValueError):
    pass

def validar_senha(senha):
    if len(senha) < 8:
        raise SenhaFracaError("A senha deve ter ao menos 8 caracteres")

try:
    validar_senha("abc")
except SenhaFracaError as e:
    print(e)
```

### Exceção com dados extras

Adicionar atributos à exceção permite que quem captura acesse detalhes sem parsear a mensagem:

```python
class SaldoInsuficienteError(ValueError):
    def __init__(self, saldo_atual, valor_solicitado):
        self.saldo_atual      = saldo_atual
        self.valor_solicitado = valor_solicitado
        super().__init__(
            f"Saldo insuficiente: tem R${saldo_atual:.2f}, "
            f"tentou sacar R${valor_solicitado:.2f}"
        )

try:
    raise SaldoInsuficienteError(100.0, 250.0)
except SaldoInsuficienteError as e:
    print(e)
    print(f"Faltam R${e.valor_solicitado - e.saldo_atual:.2f}")
```

### Hierarquia de exceções

Para domínios maiores, crie uma exceção base e especialize:

```python
# Exceção raiz do sistema — captura qualquer erro do domínio
class BancoError(Exception):
    pass

# Especializações
class SaldoInsuficienteError(BancoError):
    pass

class ContaBloqueadaError(BancoError):
    pass

class LimiteDiarioAtingidoError(BancoError):
    pass

# Quem quiser capturar qualquer erro do banco usa BancoError
try:
    raise ContaBloqueadaError("Conta bloqueada por suspeita de fraude")
except BancoError as e:
    print(f"Erro bancário: {e}")

# Quem precisar de granularidade captura o tipo específico
try:
    raise SaldoInsuficienteError("Saldo: R$10, tentativa: R$100")
except SaldoInsuficienteError as e:
    print(f"Saldo insuficiente: {e}")
except BancoError as e:
    print(f"Outro erro bancário: {e}")
```

:::dica
**Quando criar exceção personalizada?** Quando o erro pertence ao seu domínio e o código que o captura precisa tomar decisões diferentes dependendo do motivo. Se basta uma mensagem de texto, `ValueError` ou `RuntimeError` genérico podem ser suficientes.
:::

---

## Exercícios

Os exercícios cobrem manipulação de strings como textos (simulando arquivos), JSON e tratamento de erros. Como o ambiente roda no navegador, usamos strings em vez de arquivos reais — o conceito é idêntico ao trabalhar com `open()`.

---

## Mini-projeto: Caderno de Notas com JSON

Crie um sistema de notas que usa JSON como formato de persistência:

1. **`Nota(titulo, conteudo, tags=[])`** — classe com atributos e método `para_dict()` que retorna um dicionário serializável
2. **`Caderno`** — gerencia uma coleção de notas:
   - `adicionar(nota)` — adiciona à lista interna
   - `buscar(termo)` — busca em título e conteúdo (case-insensitive)
   - `por_tag(tag)` — filtra notas que contenham a tag
   - `exportar_json()` → string JSON com todas as notas, formatada com `indent=2`
   - `importar_json(json_str)` → carrega notas; levanta `ValueError` com mensagem amigável se o JSON for inválido
3. Trate `json.JSONDecodeError` dentro de `importar_json` usando `raise from`

---

## Quiz

Teste seus conhecimentos sobre arquivos, JSON e tratamento de erros.

---

## Resumo

✔ `with open(arquivo, modo, encoding='utf-8') as f:` — abertura segura e sempre fechada  
✔ Modos: `'r'` lê, `'w'` sobrescreve, `'a'` adiciona, `'x'` cria exclusivo, `'b'` binário  
✔ `pathlib.Path` — forma moderna e portável de lidar com caminhos  
✔ `json.dumps()` / `json.loads()` — serialização em string  
✔ `json.dump()` / `json.load()` — serialização em arquivo  
✔ Trate `json.JSONDecodeError` para dados externos  
✔ `try/except/else/finally` — tratamento estruturado; seja específico nas exceções  
✔ `raise` levanta exceções; `raise from` preserva a causa original  
✔ Crie exceções próprias herdando de `Exception` e organize em hierarquias

## Próximos passos

No Módulo 8 você vai construir aplicações reais: consumir APIs externas com `requests`, criar interfaces visuais com Streamlit e Flet, e servidores web com Flask e FastAPI. Tudo o que você aprendeu aqui — JSON, tratamento de erros — vai ser usado o tempo todo.
