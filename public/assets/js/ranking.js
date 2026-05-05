/**
 * Modal de Ranking Top 10 — reutilizável em app.html e module.html.
 *
 * Cria o modal no DOM na primeira chamada, depois só abre/fecha.
 * Destaca o usuário logado se ele estiver no top 10.
 */

const MEDALHAS = ['🥇', '🥈', '🥉']

/**
 * Inicializa o botão de ranking e o modal.
 *
 * @param {string} apiUrl         - URL do endpoint /api/leaderboard
 * @param {string} nomeUsuario    - Nome completo do usuário logado (para destaque)
 * @param {HTMLElement} btnEl     - Elemento <button> que abre o modal
 */
/**
 * @param {string}          apiUrl      - URL do endpoint /api/leaderboard
 * @param {string|Function} nomeUsuario - Nome completo ou função que retorna o nome (lazy)
 * @param {HTMLElement}     btnEl       - Botão que abre o modal
 */
export function iniciarRanking(apiUrl, nomeUsuario, btnEl) {
  if (!document.getElementById('ranking-modal')) {
    _criarModal()
  }

  // Suporte a nome lazy (função) para quando o usuário ainda não carregou no init
  const getNome = typeof nomeUsuario === 'function' ? nomeUsuario : () => nomeUsuario

  btnEl.addEventListener('click', () => _abrirModal(apiUrl, getNome()))
}

// ─── Privado ─────────────────────────────────────────────────────────────────

function _criarModal() {
  const el = document.createElement('div')
  el.id = 'ranking-modal'
  el.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm'
  el.style.display = 'none'
  el.setAttribute('role', 'dialog')
  el.setAttribute('aria-modal', 'true')
  el.setAttribute('aria-label', 'Ranking Top 10')

  el.innerHTML = `
    <div id="ranking-inner" class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-sm mx-4 shadow-2xl overflow-hidden">
      <!-- Cabeçalho -->
      <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
        <h2 class="font-semibold text-slate-100 text-base">🏆 Ranking Top 10</h2>
        <button id="ranking-fechar" class="text-slate-500 hover:text-slate-300 p-1 rounded transition-colors" aria-label="Fechar ranking">✕</button>
      </div>

      <!-- Lista -->
      <div id="ranking-lista" class="divide-y divide-slate-800 max-h-[420px] overflow-y-auto">
        <!-- preenchido via JS -->
      </div>

      <!-- Posição do usuário fora do top 10 -->
      <div id="ranking-fora" class="hidden px-5 py-3 border-t border-slate-800 text-xs text-slate-500 text-center"></div>
    </div>
  `

  document.body.appendChild(el)

  // Fecha ao clicar fora ou no X
  el.addEventListener('click', e => { if (e.target === el) _fecharModal() })
  document.getElementById('ranking-fechar').addEventListener('click', _fecharModal)
  document.addEventListener('keydown', e => { if (e.key === 'Escape') _fecharModal() })
}

function _fecharModal() {
  const el = document.getElementById('ranking-modal')
  if (el) el.style.display = 'none'
}

async function _abrirModal(apiUrl, nomeUsuario) {
  const modal = document.getElementById('ranking-modal')
  const lista = document.getElementById('ranking-lista')
  const fora  = document.getElementById('ranking-fora')

  // Mostra carregando
  lista.innerHTML = `<div class="px-5 py-6 text-center text-slate-500 text-sm">Carregando...</div>`
  fora.classList.add('hidden')
  modal.style.display = 'flex'

  try {
    const res  = await fetch(apiUrl)
    const data = await res.json()
    const todos = data.alunos ?? []

    // Top 10
    const top10 = todos.slice(0, 10)

    lista.innerHTML = top10.map((a, i) => {
      const pos      = i + 1
      const medalha  = MEDALHAS[i] ?? `${pos}º`
      const ehEu     = a.nome === nomeUsuario
      const destaque = ehEu ? 'bg-python-500/10 border-l-2 border-python-500' : ''

      return `
        <div class="flex items-center gap-3 px-5 py-3 ${destaque}">
          <span class="w-8 text-center text-base shrink-0">${medalha}</span>
          <span class="flex-1 text-sm ${ehEu ? 'text-python-400 font-semibold' : 'text-slate-300'} truncate">
            ${a.nome}${ehEu ? ' <span class="text-xs text-python-500/70">(você)</span>' : ''}
          </span>
          <span class="text-xs font-mono tabular-nums ${ehEu ? 'text-python-400' : 'text-slate-400'}">${a.xp_total} XP</span>
        </div>
      `
    }).join('')

    // Usuário fora do top 10
    const posUsuario = todos.findIndex(a => a.nome === nomeUsuario)
    if (posUsuario >= 10) {
      const u = todos[posUsuario]
      fora.textContent = `Você está na ${posUsuario + 1}ª posição com ${u.xp_total} XP`
      fora.classList.remove('hidden')
    }

    if (top10.length === 0) {
      lista.innerHTML = `<div class="px-5 py-6 text-center text-slate-500 text-sm">Nenhum aluno ainda.</div>`
    }
  } catch {
    lista.innerHTML = `<div class="px-5 py-6 text-center text-red-400 text-sm">Não foi possível carregar o ranking.</div>`
  }
}
