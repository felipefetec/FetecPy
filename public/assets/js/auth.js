/**
 * Funções de autenticação do FetecPy.
 *
 * Camada entre a UI e a API:
 *   - login()          → chama POST /api/auth/login, salva token
 *   - logout()         → chama POST /api/auth/logout, limpa token
 *   - verificarSessao() → tenta GET /api/me para validar token existente
 *
 * O token é guardado em localStorage sob a chave 'fetecpy_token'.
 * Toda requisição autenticada passa por api.js, que lê essa chave.
 */

import { apiFetch } from './api.js'

/**
 * Autentica o aluno (ou cria a conta se for o primeiro acesso).
 *
 * Em sucesso, salva o token no localStorage e retorna os dados
 * do usuário e se é um cadastro novo (para exibir mensagem adequada).
 *
 * @param {string} nome
 * @param {string} sobrenome
 * @param {string} pin  PIN numérico de 4-8 dígitos
 * @returns {Promise<{ token: string, usuario: object, novo: boolean }>}
 * @throws {{ status: number, erro: string }}
 */
export async function login(nome, sobrenome, pin) {
  const dados = await apiFetch('/auth/login', {
    method: 'POST',
    body: JSON.stringify({ nome, sobrenome, pin }),
  })

  // Persiste o token para que apiFetch o inclua nas próximas requisições
  localStorage.setItem('fetecpy_token', dados.token)

  return dados
}

/**
 * Encerra a sessão atual.
 *
 * Chama o backend para invalidar o token e depois remove do localStorage.
 * Falha silenciosa: se o servidor estiver fora do ar, o localStorage
 * é limpo mesmo assim — o token expirado não causa dano.
 */
export async function logout() {
  try {
    await apiFetch('/auth/logout', { method: 'POST' })
  } catch {
    // Ignora erros de rede no logout — o token local será limpo de qualquer forma
  } finally {
    localStorage.removeItem('fetecpy_token')
  }
}

/**
 * Verifica se o aluno já tem uma sessão válida.
 *
 * Tenta chamar GET /api/me com o token armazenado.
 * Retorna true se a sessão estiver ativa, false se o token estiver
 * ausente, expirado ou inválido (o token inválido é removido do storage).
 *
 * @returns {Promise<boolean>}
 */
export async function verificarSessao() {
  const token = localStorage.getItem('fetecpy_token')

  // Sem token armazenado — definitivamente não está logado
  if (!token) return false

  try {
    await apiFetch('/me')
    return true
  } catch {
    // Token inválido ou expirado — limpa para não tentar de novo desnecessariamente
    localStorage.removeItem('fetecpy_token')
    return false
  }
}
