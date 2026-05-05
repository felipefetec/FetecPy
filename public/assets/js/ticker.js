/**
 * Ticker de alunos — scroll contínuo de direita para esquerda.
 *
 * Usa requestAnimationFrame em vez de CSS animation para evitar
 * o gap/flash que ocorre entre iterações da animação CSS.
 *
 * A cada ciclo os nomes são embaralhados garantindo que nenhum
 * nome fique ao lado do mesmo nome do ciclo anterior.
 */

// Velocidade em px por frame (60fps ≈ 72px/s — um pouco mais lento que antes)
const VELOCIDADE = 1.2

/**
 * Fisher-Yates shuffle + garante que arr[0] != ultimoNome.
 * Se a lista tiver só 1 elemento, retorna sem alterar.
 */
function embaralhar(lista, ultimoNome) {
  const arr = [...lista]
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[arr[i], arr[j]] = [arr[j], arr[i]]
  }
  // Troca o primeiro com o segundo se ele for igual ao último do ciclo anterior
  if (ultimoNome !== null && arr.length > 1 && arr[0].nome === ultimoNome) {
    ;[arr[0], arr[1]] = [arr[1], arr[0]]
  }
  return arr
}

/** Cria o elemento <span> de um item do ticker */
function criarEl(nome, xp) {
  const el = document.createElement('span')
  el.className = 'ticker-item'
  el.innerHTML =
    `<span class="t-nome">${nome}</span>` +
    `<span class="t-sep">·</span>` +
    `<span class="t-xp">${xp} XP</span>`
  return el
}

/**
 * Inicializa o ticker.
 *
 * @param {string} apiUrl   - URL do endpoint /api/leaderboard
 * @param {string} idBar    - id do elemento wrapper do ticker
 * @param {string} idCount  - id do <strong> com o número de alunos
 * @param {string} idTrack  - id do elemento que recebe os itens
 */
export async function iniciarTicker(apiUrl, idBar, idCount, idTrack) {
  try {
    const res  = await fetch(apiUrl)
    const data = await res.json()

    const bar   = document.getElementById(idBar)
    const count = document.getElementById(idCount)
    const track = document.getElementById(idTrack)
    if (!bar || !count || !track || !data.alunos?.length) return

    count.textContent = data.total_exibido

    const alunos = data.alunos
    let ultimoNome = null

    // Adiciona um lote embaralhado de nomes ao final do track
    function adicionarLote() {
      const ordem = embaralhar(alunos, ultimoNome)
      ordem.forEach(a => track.appendChild(criarEl(a.nome, a.xp_total)))
      ultimoNome = ordem[ordem.length - 1].nome
    }

    // Exibe com visibility:hidden para medir scrollWidth sem mostrar ao usuário
    bar.style.display    = 'block'
    bar.style.visibility = 'hidden'

    const vw = window.innerWidth

    // Pré-carrega lotes suficientes para cobrir pelo menos 3× a largura da tela
    // Isso garante que sempre há conteúdo visível mesmo durante a remoção de itens
    adicionarLote()
    while (track.scrollWidth < vw * 3) {
      adicionarLote()
    }

    // Começa com o primeiro item na borda direita (fora da tela)
    let offsetX = vw
    track.style.transform = `translateX(${offsetX}px)`
    bar.style.visibility = 'visible'

    ;(function animar() {
      offsetX -= VELOCIDADE

      // Remove itens que saíram completamente pela borda esquerda da tela.
      // O primeiro elemento em inline-flex sempre tem offsetLeft === 0,
      // então a condição simplifica para: offsetX + largura <= 0.
      // Ao remover, somamos a largura ao offsetX para compensar o deslocamento
      // de todos os demais elementos — sem salto visual.
      while (track.firstElementChild) {
        const el = track.firstElementChild
        if (offsetX + el.offsetWidth <= 0) {
          offsetX += el.offsetWidth
          track.removeChild(el)
        } else {
          break
        }
      }

      // Garante que sempre há pelo menos 2× o viewport de conteúdo à frente
      if (offsetX + track.scrollWidth < vw * 2) {
        adicionarLote()
      }

      track.style.transform = `translateX(${offsetX}px)`
      requestAnimationFrame(animar)
    })()

  } catch (_) {
    // API indisponível: ticker não exibe, página segue funcionando normalmente
  }
}
