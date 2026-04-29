/**
 * Módulo de progresso do aluno.
 *
 * Carrega o progresso via GET /api/progress e expõe funções
 * para o dashboard calcular status e percentual de cada módulo.
 */

import { apiFetch } from './api.js'

/**
 * Carrega o progresso do aluno autenticado.
 * Retorna array com um objeto por módulo contendo percentual e status.
 *
 * @returns {Promise<Array<{modulo, concluidos, total, percentual, xp, status}>>}
 */
export async function carregarProgresso() {
  return await apiFetch('/progress')
}

/**
 * Encontra o último módulo em andamento para o card "continue de onde parou".
 * Retorna null se nenhum módulo foi iniciado.
 *
 * @param {Array} progresso  Array retornado por carregarProgresso()
 * @returns {object|null}
 */
export function ultimoModuloEmAndamento(progresso) {
  // Percorre de trás para frente para pegar o módulo mais avançado em andamento
  for (let i = progresso.length - 1; i >= 0; i--) {
    if (progresso[i].status === 'em_andamento') {
      return progresso[i]
    }
  }
  return null
}
