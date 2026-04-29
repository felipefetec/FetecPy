/**
 * Wrapper de fetch para a API do FetecPy.
 *
 * Centraliza:
 *   - Detecção automática do caminho base (funciona em localhost e em subpastas)
 *   - Inclusão do token Bearer em toda requisição autenticada
 *   - Decodificação de JSON e tratamento de erros de rede/HTTP
 *
 * Todas as funções que precisam falar com a API importam daqui —
 * nunca usam fetch diretamente.
 */

/**
 * Detecta o caminho base da aplicação usando a URL do próprio módulo.
 *
 * Exemplo em localhost:
 *   api.js em http://localhost:8000/assets/js/api.js
 *   → BASE_URL = http://localhost:8000/api
 *
 *   api.js em https://kelvinglab.com/fetecpy/assets/js/api.js
 *   → BASE_URL = https://kelvinglab.com/fetecpy/api
 *
 * Assim o código funciona sem alterações em qualquer subdiretório.
 */
const BASE_URL = new URL('../../api', import.meta.url).href.replace(/\/$/, '')

/**
 * Faz uma requisição à API e retorna os dados JSON.
 *
 * Adiciona automaticamente:
 *   - Content-Type: application/json
 *   - Authorization: Bearer <token> (se o token estiver no localStorage)
 *
 * Em caso de erro HTTP, lança um objeto com os campos da resposta JSON
 * (normalmente { erro: "mensagem" }) mais o status HTTP.
 *
 * @param {string} endpoint  Caminho relativo sem /api, ex: '/auth/login'
 * @param {RequestInit} opcoes  Opções do fetch (method, body, headers extras)
 * @returns {Promise<any>}  Dados JSON da resposta
 * @throws {{ status: number, erro: string }}  Em caso de erro HTTP
 */
export async function apiFetch(endpoint, opcoes = {}) {
  const token = localStorage.getItem('fetecpy_token')

  const cabecalhos = {
    'Content-Type': 'application/json',
    ...opcoes.headers,
  }

  // Só inclui o Authorization se houver token — evita header "Bearer null"
  if (token) {
    cabecalhos['Authorization'] = `Bearer ${token}`
  }

  let resposta
  try {
    resposta = await fetch(`${BASE_URL}${endpoint}`, {
      ...opcoes,
      headers: cabecalhos,
    })
  } catch {
    // Erro de rede (sem conexão, servidor fora do ar etc.)
    throw { status: 0, erro: 'Sem conexão com o servidor. Verifique sua internet.' }
  }

  let dados
  try {
    dados = await resposta.json()
  } catch {
    // Resposta não é JSON válido (ex: erro 500 retornando HTML)
    throw { status: resposta.status, erro: 'Resposta inesperada do servidor.' }
  }

  if (!resposta.ok) {
    // Lança o objeto JSON de erro enriquecido com o status HTTP
    throw { status: resposta.status, ...dados }
  }

  return dados
}
