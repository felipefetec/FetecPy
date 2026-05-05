---
modulo: "08"
titulo: "Construindo Aplicações Reais"
duracao_estimada: "15-20h"
pre_requisito: "07"
---

# Módulo 8 — Construindo Aplicações Reais

## Por que isso importa?

Você chegou ao último módulo. Até aqui aprendeu a base — variáveis, estruturas de controle, funções, classes, arquivos e JSON. Agora é hora de conectar tudo isso em aplicações que pessoas reais podem usar.

Este módulo apresenta 5 bibliotecas do ecossistema Python moderno. Cada submódulo é independente — você não precisa dominar todos, mas entender o que cada um faz te permite escolher a ferramenta certa para cada problema.

:::aviso
**Este conteúdo é introdutório — e isso é intencional.**

O objetivo aqui não é esgotar nenhuma das bibliotecas, mas te dar o suficiente para começar e entender o que cada uma resolve. Frameworks e bibliotecas evoluem rápido: APIs mudam, métodos ficam depreciados, novas versões alteram comportamentos.

O hábito mais valioso que você pode desenvolver como programador é **ler a documentação oficial**. Todo framework mostrado aqui tem uma documentação excelente, gratuita e sempre atualizada:

- **requests** → [docs.python-requests.org](https://docs.python-requests.org)
- **Streamlit** → [docs.streamlit.io](https://docs.streamlit.io)
- **Flask** → [flask.palletsprojects.com](https://flask.palletsprojects.com)
- **Flet** → [flet.dev/docs](https://flet.dev/docs)
- **FastAPI** → [fastapi.tiangolo.com](https://fastapi.tiangolo.com)

Quando travar em algo, a sequência recomendada é: **documentação oficial → exemplos oficiais → busca específica**. Não o contrário.
:::

---

## Submódulo 8.1 — Consumindo APIs com `requests`

### O que é uma API?

Uma API (Application Programming Interface) é um serviço web que expõe dados e funcionalidades via HTTP. Quando você abre o clima no celular, o app faz uma requisição a uma API meteorológica e exibe o resultado.

O protocolo HTTP define **métodos** para diferentes operações:

| Método | Uso | Exemplo |
|--------|-----|---------|
| `GET`  | Buscar dados | Listar produtos |
| `POST` | Criar recurso | Cadastrar usuário |
| `PUT`  | Substituir recurso | Atualizar perfil completo |
| `PATCH`| Atualizar parcialmente | Mudar só o e-mail |
| `DELETE` | Remover | Deletar comentário |

### Instalação e uso básico

```bash
pip install requests
```

```python
import requests

# GET — buscar dados
r = requests.get("https://viacep.com.br/ws/01310100/json/")
print(r.status_code)   # 200 = sucesso, 404 = não encontrado, 500 = erro servidor
dados = r.json()
print(dados["logradouro"])  # Avenida Paulista
```

### Headers e autenticação

A maioria das APIs exige autenticação via chave (API key) ou token:

```python
import requests

# Autenticação por header (padrão Bearer token — usado em JWTs)
headers = {
    "Authorization": "Bearer seu_token_aqui",
    "Content-Type": "application/json",
}
r = requests.get("https://api.exemplo.com/me", headers=headers)

# Autenticação por parâmetro de query (comum em APIs gratuitas)
params = {"apikey": "sua_chave", "cidade": "São Paulo"}
r = requests.get("https://api.clima.com/weather", params=params)
```

### POST — enviando dados

```python
import requests

payload = {
    "nome": "Felipe",
    "email": "felipe@email.com",
    "senha": "segura123"
}

r = requests.post(
    "https://api.exemplo.com/usuarios",
    json=payload,           # serializa dict como JSON automaticamente
    headers={"Authorization": "Bearer token"},
    timeout=10              # nunca omita timeout em produção
)

if r.status_code == 201:
    novo_usuario = r.json()
    print(f"Usuário criado: id={novo_usuario['id']}")
```

### Tratando erros de rede corretamente

```python
import requests

def buscar_usuario(user_id: int) -> dict | None:
    url = f"https://api.exemplo.com/usuarios/{user_id}"
    try:
        r = requests.get(url, timeout=5)
        r.raise_for_status()   # lança HTTPError para 4xx e 5xx
        return r.json()
    except requests.Timeout:
        print("A requisição demorou demais — servidor pode estar sobrecarregado")
        return None
    except requests.HTTPError as e:
        status = e.response.status_code
        if status == 404:
            print(f"Usuário {user_id} não encontrado")
        elif status == 401:
            print("Token inválido ou expirado")
        elif status == 429:
            print("Rate limit atingido — aguarde antes de tentar novamente")
        else:
            print(f"Erro HTTP {status}: {e.response.text}")
        return None
    except requests.ConnectionError:
        print("Sem conexão — verifique sua internet")
        return None
```

### Session — reutilizando conexões e headers

Para múltiplas requisições à mesma API, use `Session` para reutilizar a conexão TCP e definir headers uma vez só:

```python
import requests

session = requests.Session()
session.headers.update({
    "Authorization": "Bearer meu_token",
    "Accept": "application/json",
})

# Todos os requests usam os headers acima automaticamente
r1 = session.get("https://api.exemplo.com/produtos")
r2 = session.get("https://api.exemplo.com/clientes")
session.close()
```

:::aviso
**No FetecPy, use `pyodide.http.pyfetch` em vez de `requests`.** O ambiente do navegador não permite chamadas HTTP diretas com `requests`. A lógica é a mesma — só a sintaxe difere.
:::

:::tente
```python
import json

# Simula parsing de resposta de API de CEP (ViaCEP)
resposta_simulada = '{"cep":"01310-100","logradouro":"Avenida Paulista","bairro":"Bela Vista","localidade":"São Paulo","uf":"SP"}'
dados = json.loads(resposta_simulada)
print(f"Rua: {dados['logradouro']}")
print(f"Cidade: {dados['localidade']} - {dados['uf']}")
```
:::

---

## Submódulo 8.2 — Streamlit: dashboards em Python puro

### Quando usar Streamlit

Streamlit é ideal para análise de dados, protótipos e ferramentas internas. Em 30 linhas você tem um dashboard com gráficos e inputs — sem HTML, CSS ou JavaScript.

**Não use Streamlit para:** aplicações com múltiplas páginas complexas, alta customização de UI, ou quando performance for crítica.

### Estrutura básica

```python
import streamlit as st

st.set_page_config(page_title="Meu App", page_icon="🐍", layout="wide")
st.title("Dashboard de Notas")
st.markdown("Analise o desempenho dos alunos por módulo.")
```

### Inputs e widgets

```python
import streamlit as st

# Text inputs
nome  = st.text_input("Seu nome", placeholder="Felipe")
notas = st.text_area("Cole as notas (uma por linha):")

# Numérico
limite = st.slider("Nota mínima para aprovação", min_value=0.0, max_value=10.0, value=7.0)

# Seleção
modulo = st.selectbox("Módulo", ["01", "02", "03", "04"])

# Botão
if st.button("Calcular"):
    linhas = [float(n) for n in notas.strip().split("\n") if n.strip()]
    if linhas:
        media = sum(linhas) / len(linhas)
        st.metric("Média da turma", f"{media:.2f}", delta=f"{media - limite:+.2f} vs mínimo")
```

### Gráficos e layout

```python
import streamlit as st
import json

st.title("Análise de Alunos")

# Colunas lado a lado
col1, col2 = st.columns(2)
with col1:
    st.metric("Total de alunos", 42)
with col2:
    st.metric("Média geral", "7.8", delta="+0.3")

# Upload de arquivo
arquivo = st.file_uploader("Carregar JSON de alunos", type="json")
if arquivo:
    dados = json.load(arquivo)
    nomes = [a["nome"] for a in dados]
    notas = [a["nota"] for a in dados]
    st.bar_chart(dict(zip(nomes, notas)))
    st.dataframe(dados)
```

### `st.session_state` — estado entre interações

**Importante:** Streamlit re-executa o script inteiro a cada interação. Variáveis normais são resetadas. Use `st.session_state` para persistir dados:

```python
import streamlit as st

# Inicializa estado na primeira execução
if "contador" not in st.session_state:
    st.session_state.contador = 0
if "historico" not in st.session_state:
    st.session_state.historico = []

col1, col2 = st.columns(2)
with col1:
    if st.button("➕ Incrementar"):
        st.session_state.contador += 1
        st.session_state.historico.append(st.session_state.contador)
with col2:
    if st.button("🔄 Resetar"):
        st.session_state.contador = 0
        st.session_state.historico = []

st.write(f"Contador: **{st.session_state.contador}**")
st.line_chart(st.session_state.historico)
```

### Cache — evitando reprocessamento

```python
import streamlit as st
import requests

@st.cache_data(ttl=300)   # cache por 5 minutos
def buscar_dados_api(url: str) -> dict:
    r = requests.get(url, timeout=10)
    r.raise_for_status()
    return r.json()

dados = buscar_dados_api("https://api.exemplo.com/relatorio")
st.dataframe(dados)
```

**Para rodar:**
```bash
pip install streamlit
streamlit run app.py
# Acessa: http://localhost:8501
```

---

## Submódulo 8.3 — Flask: servidor web minimalista

### Quando usar Flask

Flask é ideal quando você precisa de uma API HTTP customizada, ou de um servidor web leve. É simples de aprender mas poderoso o suficiente para produção com as extensões certas.

**Use Flask para:** APIs REST, backends de apps, webhooks, automações web.
**Use Django para:** projetos grandes com admin, ORM, autenticação pronta — quando precisar de tudo junto.

### Rotas e métodos HTTP

```python
from flask import Flask, request, jsonify, abort

app = Flask(__name__)

# GET sem parâmetros
@app.route("/health")
def health():
    return jsonify({"status": "ok"})

# Parâmetro na URL
@app.route("/usuarios/<int:user_id>")
def buscar_usuario(user_id: int):
    usuario = db.buscar(user_id)
    if not usuario:
        abort(404, description="Usuário não encontrado")
    return jsonify(usuario)

# POST com corpo JSON
@app.route("/usuarios", methods=["POST"])
def criar_usuario():
    dados = request.get_json()

    # Validação manual
    if not dados or not dados.get("nome"):
        return jsonify({"erro": "campo 'nome' é obrigatório"}), 400
    if "@" not in dados.get("email", ""):
        return jsonify({"erro": "e-mail inválido"}), 400

    novo = {"id": len(usuarios) + 1, "nome": dados["nome"], "email": dados["email"]}
    usuarios.append(novo)
    return jsonify(novo), 201
```

### Tratamento de erros centralizado

```python
from flask import Flask, jsonify

app = Flask(__name__)

@app.errorhandler(400)
def bad_request(e):
    return jsonify({"erro": str(e.description)}), 400

@app.errorhandler(404)
def not_found(e):
    return jsonify({"erro": "recurso não encontrado"}), 404

@app.errorhandler(500)
def server_error(e):
    app.logger.error(f"Erro interno: {e}")
    return jsonify({"erro": "erro interno do servidor"}), 500
```

### Variáveis de ambiente e configuração

Nunca coloque senhas ou chaves diretamente no código:

```python
import os
from flask import Flask

app = Flask(__name__)

# Lê do ambiente — use python-dotenv para .env em dev
app.config["SECRET_KEY"]    = os.environ.get("SECRET_KEY", "dev-inseguro")
app.config["DATABASE_URL"]  = os.environ.get("DATABASE_URL", "sqlite:///dev.db")
app.config["DEBUG"]         = os.environ.get("FLASK_DEBUG", "false").lower() == "true"
```

### Blueprint — organizando rotas em módulos

Para projetos maiores, agrupe rotas por domínio:

```python
# routes/produtos.py
from flask import Blueprint, jsonify

bp = Blueprint("produtos", __name__, url_prefix="/produtos")

@bp.route("/")
def listar():
    return jsonify([...])

@bp.route("/<int:id>")
def buscar(id):
    return jsonify({...})
```

```python
# app.py
from flask import Flask
from routes.produtos import bp as produtos_bp

app = Flask(__name__)
app.register_blueprint(produtos_bp)
```

**Para rodar:**
```bash
pip install flask
flask run --debug
# Acessa: http://localhost:5000
```

:::aviso
**`debug=True` nunca em produção.** Habilita um interpretador Python interativo no navegador que permite executar código arbitrário no servidor.
:::

---

## Submódulo 8.4 — Flet: apps desktop e mobile em Python

### Quando usar Flet

Flet cria interfaces gráficas nativas (Windows, Mac, Linux) e web usando Python puro, baseado no Flutter do Google. É ideal quando você quer uma UI real sem aprender JavaScript.

**Use Flet para:** ferramentas desktop, apps internos, protótipos de interfaces.
**Use Streamlit para:** dashboards de dados onde a análise importa mais que a UI.

### Componentes e layout

```python
import flet as ft

def main(page: ft.Page):
    page.title = "Lista de Tarefas"
    page.theme_mode = ft.ThemeMode.DARK
    page.padding = 20

    # Componentes
    campo_tarefa = ft.TextField(
        label="Nova tarefa",
        hint_text="O que precisa ser feito?",
        expand=True,
    )
    lista_tarefas = ft.Column(spacing=8)

    def adicionar(e):
        if not campo_tarefa.value.strip():
            return
        tarefa = campo_tarefa.value.strip()

        # Cria um item de tarefa com checkbox e botão deletar
        def deletar(ev, item_ref):
            lista_tarefas.controls.remove(item_ref)
            page.update()

        item = ft.Row()   # referência antes de criar para usar no lambda
        item.controls = [
            ft.Checkbox(label=tarefa, expand=True),
            ft.IconButton(
                icon=ft.icons.DELETE_OUTLINE,
                on_click=lambda ev: deletar(ev, item),
                icon_color=ft.colors.RED_400,
            ),
        ]
        lista_tarefas.controls.append(item)
        campo_tarefa.value = ""
        page.update()

    # Layout principal
    page.add(
        ft.Text("Minhas Tarefas", size=24, weight=ft.FontWeight.BOLD),
        ft.Row([campo_tarefa, ft.ElevatedButton("Adicionar", on_click=adicionar)]),
        ft.Divider(),
        lista_tarefas,
    )

ft.app(target=main)
```

### Navegação entre telas

```python
import flet as ft

def main(page: ft.Page):
    page.title = "App com navegação"

    def ir_para_detalhes(e):
        page.views.append(
            ft.View(
                "/detalhes",
                [
                    ft.AppBar(title=ft.Text("Detalhes")),
                    ft.Text("Conteúdo da tela de detalhes"),
                    ft.ElevatedButton("Voltar", on_click=lambda _: page.views.pop() or page.update()),
                ],
            )
        )
        page.update()

    page.add(
        ft.Text("Tela principal"),
        ft.ElevatedButton("Ver detalhes", on_click=ir_para_detalhes),
    )

ft.app(target=main)
```

### Estado e reatividade

Flet usa um modelo **imperativo**: você altera o componente e chama `page.update()` para re-renderizar. Diferente do Streamlit (que re-executa tudo), só o que você marcou como atualizado re-renderiza:

```python
import flet as ft

def main(page: ft.Page):
    contador = ft.Text("0", size=40, weight=ft.FontWeight.BOLD)

    def incrementar(e):
        contador.value = str(int(contador.value) + 1)
        contador.update()   # atualiza só esse componente — mais eficiente que page.update()

    page.add(
        contador,
        ft.ElevatedButton("Incrementar", on_click=incrementar),
    )

ft.app(target=main)
```

**Para rodar:**
```bash
pip install flet
python app.py
```

---

## Submódulo 8.5 — FastAPI: API moderna com validação automática

### Quando usar FastAPI

FastAPI é o framework Python de APIs mais usado atualmente. Combina alta performance (async/await nativo), validação automática via Pydantic e documentação interativa gerada automaticamente.

**Use FastAPI para:** APIs REST modernas, microserviços, backends de SPAs e apps mobile.

### Modelos Pydantic — validação automática

```python
from fastapi import FastAPI, HTTPException, status
from pydantic import BaseModel, EmailStr, field_validator
from typing import Optional
import uuid

app = FastAPI(title="API de Produtos", version="1.0.0")

class ProdutoBase(BaseModel):
    nome:     str
    preco:    float
    estoque:  int = 0
    ativo:    bool = True

    @field_validator("preco")
    @classmethod
    def preco_positivo(cls, v: float) -> float:
        if v <= 0:
            raise ValueError("Preço deve ser positivo")
        return round(v, 2)

    @field_validator("nome")
    @classmethod
    def nome_nao_vazio(cls, v: str) -> str:
        v = v.strip()
        if not v:
            raise ValueError("Nome não pode ser vazio")
        return v

class ProdutoCreate(ProdutoBase):
    pass   # campos de criação (sem id)

class ProdutoResponse(ProdutoBase):
    id: str   # id só existe na resposta — não aceito na criação
```

### Rotas com validação automática e códigos HTTP corretos

```python
produtos_db: dict[str, dict] = {}

@app.get("/produtos", response_model=list[ProdutoResponse])
def listar_produtos(ativo: Optional[bool] = None):
    """Lista todos os produtos. Filtra por status se `ativo` for informado."""
    todos = list(produtos_db.values())
    if ativo is not None:
        todos = [p for p in todos if p["ativo"] == ativo]
    return todos

@app.post("/produtos", response_model=ProdutoResponse, status_code=status.HTTP_201_CREATED)
def criar_produto(produto: ProdutoCreate):
    """Cria produto. Pydantic valida automaticamente — erro 422 se inválido."""
    novo_id = str(uuid.uuid4())
    dados = produto.model_dump()
    dados["id"] = novo_id
    produtos_db[novo_id] = dados
    return dados

@app.get("/produtos/{produto_id}", response_model=ProdutoResponse)
def buscar_produto(produto_id: str):
    if produto_id not in produtos_db:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail=f"Produto '{produto_id}' não encontrado"
        )
    return produtos_db[produto_id]

@app.patch("/produtos/{produto_id}", response_model=ProdutoResponse)
def atualizar_produto(produto_id: str, dados: dict):
    if produto_id not in produtos_db:
        raise HTTPException(status_code=404, detail="Produto não encontrado")
    produtos_db[produto_id].update(dados)
    return produtos_db[produto_id]

@app.delete("/produtos/{produto_id}", status_code=status.HTTP_204_NO_CONTENT)
def deletar_produto(produto_id: str):
    if produto_id not in produtos_db:
        raise HTTPException(status_code=404, detail="Produto não encontrado")
    del produtos_db[produto_id]
```

### Dependências — injeção e reuso

```python
from fastapi import Depends, Header

def verificar_api_key(x_api_key: str = Header(...)):
    """Dependência reutilizável — injeta em qualquer rota."""
    if x_api_key != "minha-chave-secreta":
        raise HTTPException(status_code=401, detail="API key inválida")
    return x_api_key

# Aplica em uma rota
@app.post("/admin/produtos", dependencies=[Depends(verificar_api_key)])
def criar_produto_admin(produto: ProdutoCreate):
    ...

# Aplica em todas as rotas do router
from fastapi import APIRouter
router_admin = APIRouter(prefix="/admin", dependencies=[Depends(verificar_api_key)])
```

### Async/await — quando usar

FastAPI suporta funções assíncronas nativamente. Use `async def` quando a rota fizer operações de I/O (banco de dados, chamadas HTTP):

```python
import httpx   # cliente HTTP assíncrono

@app.get("/clima/{cidade}")
async def buscar_clima(cidade: str):
    async with httpx.AsyncClient() as client:
        r = await client.get(f"https://api.clima.com/weather?q={cidade}")
        r.raise_for_status()
        return r.json()
```

**Para rodar:**
```bash
pip install fastapi uvicorn[standard]
uvicorn app:app --reload
# Documentação automática: http://localhost:8000/docs
# Schema OpenAPI:          http://localhost:8000/openapi.json
```

:::curiosidade
A documentação em `/docs` é gerada automaticamente a partir dos seus type hints e modelos Pydantic. Você pode testar cada endpoint direto pelo navegador — sem precisar de Postman ou curl.
:::

---

## Exercícios

Os exercícios deste módulo são de lógica de dados — o que você colocaria dentro das rotas e componentes. Como o ambiente roda no Pyodide, não instalamos bibliotecas externas: você implementa as funções de processamento puro que seriam usadas junto com cada framework.

---

## Mini-projeto Final: App de Tarefas Completo

Combine **FastAPI** (backend) + **Flet** (frontend):

**FastAPI — API REST:**
```
GET    /tarefas           → lista todas
POST   /tarefas           → cria nova (body: {titulo, descricao?})
PATCH  /tarefas/{id}      → marca como concluída
DELETE /tarefas/{id}      → remove
```

**Flet — Interface:**
- Campo de texto + botão "Adicionar"
- Lista de tarefas com `Checkbox` (marcar como concluída)
- Botão "Deletar" em cada item
- `ft.Text` com contador "X concluídas / Y total"
- Comunica com a API via `httpx.AsyncClient`

**Desafio extra:** adicione filtro por status (todas / pendentes / concluídas) usando `ft.RadioGroup`.

---

## Quiz

Teste seus conhecimentos sobre APIs, frameworks e aplicações reais.

---

## Resumo

✔ `requests.get/post/put/delete` — verbos HTTP corretos para cada operação  
✔ `Session` — reutilize conexões e headers em múltiplas requisições  
✔ Trate `Timeout`, `HTTPError` e `ConnectionError` separadamente  
✔ Streamlit: `st.session_state` para persistir dados entre interações; `@st.cache_data` para evitar reprocessamento  
✔ Flask: `Blueprint` para organizar rotas; erros centralizados com `@app.errorhandler`; config via variáveis de ambiente  
✔ Flet: modelo imperativo — altere o componente e chame `.update()`; use `page.views` para navegação  
✔ FastAPI: Pydantic valida automaticamente; `Depends` para injeção de dependências; `async def` para I/O  

## Parabéns!

Você concluiu o currículo FetecPy. Mas a jornada não para aqui — continue no **Módulo 9: Boas Práticas**, onde você vai aprender Git, ambientes virtuais, type hints e testes automatizados: as ferramentas que separam projetos pessoais de projetos profissionais.
