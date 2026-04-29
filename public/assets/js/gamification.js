/**
 * Módulo de gamificação — badges, XP, toasts e modais celebratórios.
 *
 * Centraliza o catálogo de badges, lógica de mesclagem e todos os
 * componentes visuais de recompensa (toast de XP, modal de badge).
 */

/**
 * Catálogo completo das 15 badges da plataforma.
 */
export const BADGES_CATALOGO = [
  { id: 'primeiro_codigo',    nome: 'Primeiro código',      descricao: 'Rodou o primeiro print',                 icone: '🐍' },
  { id: 'streak_3',           nome: 'Streak 3 dias',        descricao: '3 dias consecutivos estudando',          icone: '🔥' },
  { id: 'streak_7',           nome: 'Streak 7 dias',        descricao: '7 dias consecutivos estudando',          icone: '🔥' },
  { id: 'streak_30',          nome: 'Streak 30 dias',       descricao: '30 dias consecutivos estudando',         icone: '🔥' },
  { id: 'sniper',             nome: 'Sniper',                descricao: '5 quizzes seguidos sem errar',           icone: '🎯' },
  { id: 'estudante_dedicado', nome: 'Estudante dedicado',   descricao: '10 exercícios resolvidos',               icone: '📚' },
  { id: 'mestre_modulo',      nome: 'Mestre do módulo',     descricao: 'Primeiro módulo 100% concluído',         icone: '🏆' },
  { id: 'formado',            nome: 'Formado',               descricao: 'Todos os módulos concluídos',            icone: '🎓' },
  { id: 'pyodide_master',     nome: 'Pyodide Master',       descricao: 'Usou todas as bibliotecas dos exemplos', icone: '🚀' },
  { id: 'cacador_bugs',       nome: 'Caçador de bugs',      descricao: 'Resolveu após 3+ tentativas',            icone: '🐛' },
  { id: 'velocista',          nome: 'Velocista',             descricao: 'Exercício resolvido em menos de 1 min',  icone: '⚡' },
  { id: 'coruja',             nome: 'Coruja',                descricao: 'Estudou depois das 23h',                 icone: '🌙' },
  { id: 'madrugador',         nome: 'Madrugador',            descricao: 'Estudou antes das 7h',                   icone: '☀️' },
  { id: 'persistente',        nome: 'Persistente',           descricao: 'Voltou após 7 dias sem estudar',         icone: '💎' },
  { id: 'criativo',           nome: 'Criativo',              descricao: 'Solução com estrutura diferente do padrão', icone: '🎨' },
]

/**
 * Busca os metadados de uma badge pelo ID.
 * @param {string} id
 * @returns {{id,nome,descricao,icone}|null}
 */
export function infoBadge(id) {
  return BADGES_CATALOGO.find(b => b.id === id) ?? null
}

/**
 * Mescla o catálogo com as badges conquistadas pelo aluno.
 * Retorna o catálogo completo com campo "conquistada" preenchido.
 *
 * @param {Array<{badge_id, conquistado_em}>} badgesAluno  Vindo de GET /api/me
 * @returns {Array}
 */
export function mesclarBadges(badgesAluno) {
  const conquistadas = new Set(badgesAluno.map(b => b.badge_id))
  const datas = Object.fromEntries(badgesAluno.map(b => [b.badge_id, b.conquistado_em]))

  return BADGES_CATALOGO.map(badge => ({
    ...badge,
    conquistada:    conquistadas.has(badge.id),
    conquistado_em: datas[badge.id] ?? null,
  }))
}

/**
 * Formata XP para exibição: 1500 → "1.500 XP"
 */
export function formatarXp(xp) {
  return xp.toLocaleString('pt-BR') + ' XP'
}

// ----------------------------------------------------------------
// Sistema de toasts — exibidos no canto inferior direito da tela
// ----------------------------------------------------------------

// Container de toasts (criado uma única vez ao carregar o módulo)
let _toastContainer = null

function obterContainerToasts() {
  if (!_toastContainer) {
    _toastContainer = document.createElement('div')
    // Posicionamento fixo no canto inferior direito, acima do fold
    _toastContainer.className = 'fixed bottom-6 right-6 z-50 flex flex-col gap-2 pointer-events-none'
    _toastContainer.id = 'toast-container'
    document.body.appendChild(_toastContainer)
  }
  return _toastContainer
}

/**
 * Exibe um toast animado com mensagem de texto.
 *
 * @param {string} mensagem   Texto principal (ex: "+20 XP")
 * @param {'xp'|'badge'|'info'|'erro'} tipo  Controla a cor
 * @param {number} duracao    Milissegundos antes do fade-out (padrão: 3000)
 */
export function mostrarToast(mensagem, tipo = 'info', duracao = 3000) {
  const cores = {
    xp:    'bg-emerald-600 text-white',
    badge: 'bg-violet-600 text-white',
    info:  'bg-slate-700 text-slate-100',
    erro:  'bg-red-700 text-white',
  }

  const container = obterContainerToasts()
  const el        = document.createElement('div')

  el.className = [
    'px-4 py-2 rounded-lg shadow-lg text-sm font-semibold pointer-events-auto',
    'transition-all duration-300 opacity-0 translate-y-2',
    cores[tipo] ?? cores.info,
  ].join(' ')
  el.textContent = mensagem

  container.appendChild(el)

  // Anima entrada com pequeno delay para a transição funcionar
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      el.classList.remove('opacity-0', 'translate-y-2')
    })
  })

  // Remove após duração
  setTimeout(() => {
    el.classList.add('opacity-0', 'translate-y-4')
    el.addEventListener('transitionend', () => el.remove(), { once: true })
  }, duracao)
}

/**
 * Exibe toast de XP ganho com multiplicador opcional.
 * Ex: toastXp(20, 1.2) → "+24 XP  🔥 ×1.2"
 *
 * @param {number} xpGanho       XP já calculado com multiplicador
 * @param {number} multiplicador Multiplicador de streak (1.0 se não há bônus)
 */
export function toastXp(xpGanho, multiplicador = 1.0) {
  if (xpGanho <= 0) return
  let msg = `+${xpGanho} XP`
  if (multiplicador > 1.0) {
    msg += `  🔥 ×${multiplicador.toFixed(1)}`
  }
  mostrarToast(msg, 'xp', 3500)
}

/**
 * Exibe toast simples de badge conquistada.
 * O modal completo é aberto por abrirModalBadge().
 */
export function toastBadge(badgeId) {
  const info = infoBadge(badgeId)
  if (!info) return
  mostrarToast(`${info.icone} Badge desbloqueada: ${info.nome}!`, 'badge', 4500)
}

// ----------------------------------------------------------------
// Modal celebratório de badge
// ----------------------------------------------------------------

/**
 * Abre um modal fullscreen celebrando a conquista de uma badge.
 * Se houver várias badges novas, exibe em fila (uma por vez).
 *
 * @param {string[]} badgeIds  IDs das badges recém-conquistadas
 */
export function celebrarBadges(badgeIds) {
  if (!badgeIds || badgeIds.length === 0) return

  // Exibe na fila: cada modal fecha e abre o próximo com pequeno delay
  const fila = [...badgeIds]

  function exibirProxima() {
    if (fila.length === 0) return
    const id   = fila.shift()
    const info = infoBadge(id)
    if (!info) { exibirProxima(); return }

    _abrirModalBadge(info, fila.length > 0 ? exibirProxima : null)
  }

  exibirProxima()
}

function _abrirModalBadge(info, onFechar) {
  // Remove modal anterior se existir (segurança)
  document.getElementById('badge-modal')?.remove()

  const overlay = document.createElement('div')
  overlay.id        = 'badge-modal'
  overlay.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm'

  overlay.innerHTML = `
    <div class="bg-slate-800 border border-slate-600 rounded-2xl p-8 max-w-sm w-full mx-4 text-center shadow-2xl transform scale-90 transition-transform duration-300" id="badge-modal-inner">
      <div class="text-6xl mb-4">${info.icone}</div>
      <div class="text-xs uppercase tracking-widest text-emerald-400 font-semibold mb-1">Badge desbloqueada!</div>
      <h2 class="text-2xl font-bold text-white mb-2">${info.nome}</h2>
      <p class="text-slate-400 text-sm mb-6">${info.descricao}</p>
      <button id="badge-modal-btn" class="bg-emerald-600 hover:bg-emerald-500 text-white font-semibold px-6 py-2 rounded-lg transition-colors w-full">
        ${onFechar ? 'Próxima' : 'Fechar'}
      </button>
    </div>
  `

  document.body.appendChild(overlay)

  // Anima entrada
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      overlay.querySelector('#badge-modal-inner')?.classList.replace('scale-90', 'scale-100')
    })
  })

  function fechar() {
    overlay.remove()
    if (onFechar) onFechar()
  }

  document.getElementById('badge-modal-btn').addEventListener('click', fechar)
  // Clique fora fecha
  overlay.addEventListener('click', e => { if (e.target === overlay) fechar() })
  // ESC fecha
  document.addEventListener('keydown', function esc(e) {
    if (e.key === 'Escape') { fechar(); document.removeEventListener('keydown', esc) }
  })
}

// ----------------------------------------------------------------
// Notificação de streak
// ----------------------------------------------------------------

/**
 * Exibe toast de streak quando o valor aumenta.
 * @param {number} streakAtual  Streak após a atualização
 */
export function toastStreak(streakAtual) {
  if (streakAtual < 2) return  // Primeiro dia não é conquista ainda

  const marcos = { 3: '🔥 3 dias seguidos!', 7: '🔥🔥 Uma semana!', 30: '🔥🔥🔥 30 dias! Incrível!' }
  if (marcos[streakAtual]) {
    mostrarToast(marcos[streakAtual], 'badge', 4000)
    return
  }

  // Para outros valores, toast simples
  mostrarToast(`🔥 ${streakAtual} dias seguidos`, 'info', 2500)
}

// ----------------------------------------------------------------
// Utilitário: processa resposta de submit e dispara notificações
// ----------------------------------------------------------------

/**
 * Recebe a resposta de POST /submit ou /quiz/submit e
 * dispara os toasts + modal de badge adequados.
 *
 * @param {object} data  Resposta do backend
 * @param {number} streakAnterior  Valor do streak antes desta submissão (para detectar aumento)
 */
export function processarRespostaGamificacao(data, streakAnterior = 0) {
  // XP
  toastXp(data.xp_ganho ?? 0, data.multiplicador ?? 1.0)

  // Streak (só notifica se aumentou)
  if ((data.streak_dias ?? 0) > streakAnterior) {
    toastStreak(data.streak_dias)
  }

  // Badges novas
  const novas = data.novas_badges ?? []
  if (novas.length > 0) {
    // Toast rápido para cada badge
    novas.forEach(id => toastBadge(id))
    // Modal celebratório em seguida (com pequeno delay para o toast aparecer primeiro)
    setTimeout(() => celebrarBadges(novas), 500)
  }
}
