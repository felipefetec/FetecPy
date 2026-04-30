/**
 * Service Worker do FetecPy.
 *
 * Estratégias de cache:
 *   - Assets locais (HTML, JS): cache-first com fallback de rede.
 *     Na próxima visita, tudo carrega instantaneamente.
 *   - Pyodide CDN (jsDelivr): cache-first permanente.
 *     Os arquivos do Pyodide somam ~30 MB; depois do primeiro carregamento
 *     ficam em cache e o aluno nunca mais precisa re-baixar.
 *   - Chamadas de API (/api/*): sempre via rede — dados precisam ser frescos.
 *   - Outros domínios externos (Alpine, Tailwind, Prism): cache-first.
 */

const CACHE = 'fetecpy-v1'

// Assets locais incluídos no cache já na instalação do SW
const PRECACHE = [
  '/index.html',
  '/app.html',
  '/module.html',
  '/404.html',
  '/assets/js/api.js',
  '/assets/js/auth.js',
  '/assets/js/editor.js',
  '/assets/js/gamification.js',
  '/assets/js/markdown.js',
  '/assets/js/progress.js',
  '/assets/js/pyodide.js',
  '/assets/js/validator.js',
]

// ----------------------------------------------------------------
// Instalação: pré-carrega os assets locais
// ----------------------------------------------------------------
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE).then(cache => cache.addAll(PRECACHE))
  )
  // Ativa imediatamente sem esperar a aba ser fechada
  self.skipWaiting()
})

// ----------------------------------------------------------------
// Ativação: remove caches de versões antigas
// ----------------------------------------------------------------
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys.filter(k => k !== CACHE).map(k => caches.delete(k))
      )
    )
  )
  self.clients.claim()
})

// ----------------------------------------------------------------
// Fetch: intercepta requisições e aplica a estratégia certa
// ----------------------------------------------------------------
self.addEventListener('fetch', event => {
  const { request } = event
  const url = new URL(request.url)

  // API → sempre rede (dados do aluno precisam ser frescos)
  if (url.pathname.startsWith('/api/')) return

  // Pyodide (jsDelivr) → cache-first permanente
  // Estes arquivos não mudam após o primeiro carregamento
  if (url.hostname === 'cdn.jsdelivr.net') {
    event.respondWith(cacheFirst(request))
    return
  }

  // CDNs externos (Alpine, Tailwind, Prism, canvas-confetti) → cache-first
  if (url.hostname !== location.hostname) {
    event.respondWith(cacheFirst(request))
    return
  }

  // Assets locais → cache-first com fallback de rede
  event.respondWith(cacheFirst(request))
})

// ----------------------------------------------------------------
// Helper: cache-first com fallback de rede e armazenamento automático
// ----------------------------------------------------------------
async function cacheFirst(request) {
  const cached = await caches.match(request)
  if (cached) return cached

  try {
    const response = await fetch(request)
    // Só armazena respostas bem-sucedidas e de tipos suportáveis
    if (response.ok && response.type !== 'opaque') {
      const cache = await caches.open(CACHE)
      cache.put(request, response.clone())
    }
    return response
  } catch {
    // Sem rede e sem cache → retorna resposta de erro legível
    return new Response('Offline — recurso não disponível em cache.', {
      status: 503,
      headers: { 'Content-Type': 'text/plain; charset=utf-8' },
    })
  }
}
