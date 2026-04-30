---
modulo: "08"
titulo: "Construindo Aplicações Reais"
duracao_estimada: "15-20h"
pre_requisito: "07"
---

# Módulo 8 — Construindo Aplicações Reais

## Por que isso importa?

Você chegou ao último módulo. Até aqui aprendeu a base — variáveis, estruturas de controle, funções, classes, arquivos. Agora é hora de conectar tudo isso em aplicações que pessoas reais podem usar.

Este módulo é dividido em 5 submódulos independentes, cada um com sua biblioteca e caso de uso. Você não precisa dominar todos — escolha os que se encaixam no que você quer construir.

---

## Submódulo 8.1 — Consumindo APIs com requests

APIs (Application Programming Interfaces) são serviços web que expõem dados. Clima, cotações, mapas, redes sociais — tudo via API.

```python
import requests

# Requisição GET simples
resposta = requests.get("https://api.exemplo.com/dados")
print(resposta.status_code)   # 200 = sucesso

# Acessar o JSON da resposta
dados = resposta.json()
print(dados["nome"])

# Com parâmetros de query
params = {"cidade": "São Paulo", "unidade": "metric"}
r = requests.get("https://api.clima.com/weather", params=params)
```

:::aviso
**No FetecPy, use `pyodide.http.pyfetch` em vez de `requests`.**
O ambiente do navegador não permite chamadas HTTP diretas com `requests`. O Pyodide tem sua própria API assíncrona: `pyfetch`. A lógica é a mesma — a sintaxe difere ligeiramente.
:::

**Tratando erros de rede:**

```python
try:
    r = requests.get(url, timeout=5)
    r.raise_for_status()   # levanta exceção para 4xx/5xx
    return r.json()
except requests.Timeout:
    print("A requisição demorou demais")
except requests.HTTPError as e:
    print(f"Erro HTTP: {e.response.status_code}")
except requests.ConnectionError:
    print("Sem conexão com a internet")
```

:::tente
```python
import json

# Simula parsing de uma resposta de API de cep
resposta_simulada = '{"cep":"01310-100","logradouro":"Avenida Paulista","cidade":"São Paulo","uf":"SP"}'
dados = json.loads(resposta_simulada)
print(f"{dados['logradouro']}, {dados['cidade']} - {dados['uf']}")
```
:::

---

## Submódulo 8.2 — Streamlit: dashboards em Python puro

Streamlit transforma scripts Python em apps web interativos com zero HTML/CSS. Ideal para análise de dados e protótipos rápidos.

```python
import streamlit as st
import json

st.title("Dashboard de Notas")

notas_json = st.text_area("Cole o JSON de notas:")
if notas_json:
    try:
        dados = json.loads(notas_json)
        nomes = [d["nome"] for d in dados]
        notas = [d["nota"] for d in dados]

        st.write(f"Média: {sum(notas)/len(notas):.2f}")
        st.bar_chart(dict(zip(nomes, notas)))
    except json.JSONDecodeError:
        st.error("JSON inválido!")
```

**Para rodar:**
```bash
pip install streamlit
streamlit run app.py
```

:::dica
**Streamlit re-executa o script inteiro** a cada interação do usuário. Variáveis não persistem entre execuções — use `st.session_state` para guardar estado.
:::

**Elementos essenciais:**

| Elemento | Código |
|---------|--------|
| Texto | `st.write()`, `st.markdown()` |
| Entrada | `st.text_input()`, `st.number_input()`, `st.slider()` |
| Botão | `st.button("Clique")` |
| Tabela | `st.dataframe(df)` |
| Gráfico | `st.bar_chart()`, `st.line_chart()` |
| Upload | `st.file_uploader()` |

---

## Submódulo 8.3 — Flask: servidor web minimalista

Flask é um micro-framework web. Com ele você cria APIs e aplicações web sem a complexidade do Django.

```python
from flask import Flask, request, jsonify

app = Flask(__name__)

# Banco em memória (substitua por SQLite/Postgres em produção)
usuarios = []

@app.route("/usuarios", methods=["GET"])
def listar_usuarios():
    return jsonify(usuarios)

@app.route("/usuarios", methods=["POST"])
def criar_usuario():
    dados = request.get_json()
    if not dados.get("nome"):
        return jsonify({"erro": "nome obrigatorio"}), 400
    usuarios.append({"id": len(usuarios)+1, "nome": dados["nome"]})
    return jsonify(usuarios[-1]), 201

if __name__ == "__main__":
    app.run(debug=True)
```

**Para rodar:**
```bash
pip install flask
python app.py
# Acesse: http://localhost:5000
```

:::curiosidade
**debug=True** reinicia o servidor automaticamente quando você salva o arquivo. Nunca use em produção — habilita uma interface de debug que pode expor código interno.
:::

---

## Submódulo 8.4 — Flet: apps desktop/mobile em Python

Flet permite criar interfaces gráficas nativas (e web) usando Python puro, baseado em Flutter.

```python
import flet as ft

def main(page: ft.Page):
    page.title = "Meu App"

    campo_nome = ft.TextField(label="Seu nome")
    mensagem   = ft.Text("")

    def saudar(e):
        mensagem.value = f"Olá, {campo_nome.value}!"
        page.update()

    page.add(
        campo_nome,
        ft.ElevatedButton("Saudar", on_click=saudar),
        mensagem,
    )

ft.app(target=main)
```

**Para rodar:**
```bash
pip install flet
python app.py
```

:::dica
**Flet usa um modelo reativo:** quando você altera um componente e chama `page.update()`, a interface re-renderiza. Muito parecido com React — se você um dia aprender React, esse conceito já vai ser familiar.
:::

---

## Submódulo 8.5 — FastAPI: API moderna com validação automática

FastAPI é o framework Python de APIs mais usado atualmente. Usa type hints para validar dados automaticamente e gera documentação interativa.

```python
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel

app = FastAPI()

class Produto(BaseModel):
    nome:    str
    preco:   float
    estoque: int = 0

produtos = []

@app.get("/produtos")
def listar():
    return produtos

@app.post("/produtos", status_code=201)
def criar(produto: Produto):
    produtos.append(produto.dict())
    return produto

@app.get("/produtos/{produto_id}")
def buscar(produto_id: int):
    if produto_id >= len(produtos):
        raise HTTPException(status_code=404, detail="Produto não encontrado")
    return produtos[produto_id]
```

**Para rodar:**
```bash
pip install fastapi uvicorn
uvicorn app:app --reload
# Documentação automática em: http://localhost:8000/docs
```

:::curiosidade
**A documentação é gerada automaticamente!** Acesse `/docs` para ver a interface Swagger gerada pelo FastAPI com base nas suas type hints e modelos Pydantic. Você pode testar os endpoints direto pelo navegador.
:::

---

## Exercícios

Os exercícios deste módulo são de lógica de dados — o que você colocaria dentro das rotas e componentes. Como o ambiente roda no Pyodide, não instalamos bibliotecas externas nos exercícios: você implementa as funções de processamento puro, que seriam usadas junto com o framework.

---

## Mini-projeto Final: App de Tarefas Completo

Combine Flet (frontend) + FastAPI (backend):

1. **FastAPI** — API REST para tarefas:
   - `GET /tarefas` — lista todas
   - `POST /tarefas` — cria nova
   - `PATCH /tarefas/{id}` — marca como concluída
   - `DELETE /tarefas/{id}` — remove

2. **Flet** — Interface visual:
   - Campo de texto + botão "Adicionar"
   - Lista de tarefas com checkbox
   - Botão "Deletar" em cada item
   - Indicador de tarefas concluídas/total

---

## Quiz

Teste seus conhecimentos sobre aplicações reais.

---

## Resumo

✔ `requests.get()` / `pyfetch` — consumir APIs  
✔ `streamlit` — dashboards com zero HTML  
✔ `@app.route()` do Flask — criar endpoints  
✔ `ft.app()` do Flet — interfaces visuais  
✔ `fastapi` + Pydantic — APIs com validação automática  

## Parabéns!

Você concluiu o currículo FetecPy. Mas a jornada não para aqui — continua no **Módulo 9: Boas Práticas** onde você vai aprender Git, ambientes virtuais, type hints e testes automatizados — as ferramentas que separam projetos pessoais de projetos profissionais.
