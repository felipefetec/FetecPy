/**
 * Módulo de renderização de Markdown para o FetecPy.
 *
 * Usa markdown-it (via CDN ESM) para renderizar o conteúdo dos módulos.
 * Suporta blocos customizados do FetecPy:
 *   :::dica        → caixa verde de dica
 *   :::aviso       → caixa laranja de aviso
 *   :::curiosidade → caixa azul de curiosidade
 *   :::tente       → editor interativo (placeholder até Prompt 4.2)
 *
 * Fluxo: pré-processamento dos blocos → markdown-it → pós-processamento
 */

import markdownit from 'https://cdn.jsdelivr.net/npm/markdown-it@14.1.0/+esm'

// Instância do markdown-it: html desativado por segurança (conteúdo vem do servidor,
// mas boa prática manter desativado para evitar XSS se algum dado do aluno vazar)
// html: true — necessário para que os blocos customizados pré-processados
// (:::dica, :::aviso etc.) passem como HTML pelo markdown-it sem serem escapados.
// Seguro aqui pois o conteúdo é de autoria do próprio desenvolvedor, não do aluno.
const md = markdownit({
  html:        true,
  linkify:     true,
  typographer: true,
  highlight(codigo, linguagem) {
    // Prism.js é carregado via <script> no HTML
    if (typeof Prism !== 'undefined' && linguagem && Prism.languages[linguagem]) {
      try {
        const destacado = Prism.highlight(codigo, Prism.languages[linguagem], linguagem)
        return `<pre class="language-${linguagem} rounded-lg"><code class="language-${linguagem}">${destacado}</code></pre>`
      } catch { /* fallback abaixo */ }
    }
    return `<pre class="language-text rounded-lg"><code>${md.utils.escapeHtml(codigo)}</code></pre>`
  },
})

// Definições visuais de cada tipo de bloco customizado
// borda-esq: barra colorida grossa na lateral esquerda para maior destaque visual
const BLOCOS = {
  dica:        { titulo: 'Dica',              icone: '💡', borda: 'border-l-4 border-emerald-400', fundo: 'bg-emerald-500/20',  texto: 'text-emerald-300' },
  aviso:       { titulo: 'Atenção',           icone: '⚠️', borda: 'border-l-4 border-amber-400',   fundo: 'bg-amber-500/20',    texto: 'text-amber-300'   },
  curiosidade: { titulo: 'Curiosidade',       icone: '🔍', borda: 'border-l-4 border-blue-400',    fundo: 'bg-blue-500/20',     texto: 'text-blue-300'    },
  tente:       { titulo: 'Tente você mesmo',  icone: '⌨️', borda: 'border-l-4 border-violet-400',  fundo: 'bg-violet-500/20',   texto: 'text-violet-300'  },
}

/**
 * Substitui blocos :::tipo....::: por HTML estilizado antes do markdown-it.
 * Feito no pré-processamento porque markdown-it não tem suporte nativo
 * a containers customizados sem plugin externo.
 */
function preprocessarBlocos(markdown) {
  return markdown.replace(
    /^:::(dica|aviso|curiosidade|tente)\n([\s\S]*?)^:::/gm,
    (_, tipo, conteudo) => {
      const cfg = BLOCOS[tipo]
      if (!cfg) return _
      const htmlInterno = md.render(conteudo.trim())
      // Bloco tente ganha slot extra para o editor CodeMirror (ativado por module.html)
      const slotEditor = tipo === 'tente'
        ? `<div class="tente-editor-area mt-4" data-ready="false"></div>`
        : ''

      return `<div class="rounded-xl pl-6 pr-6 pt-5 pb-5 my-8 ${cfg.borda} ${cfg.fundo}"><div class="flex items-center gap-3 mb-3 ${cfg.texto} font-bold text-xl"><span style="font-size:1.4rem">${cfg.icone}</span><span>${cfg.titulo}</span></div><div class="text-slate-200 text-[1.05rem] leading-relaxed [&>p]:mb-3 [&>p:last-child]:mb-0">${htmlInterno}</div>${slotEditor}</div>`
    }
  )
}

/**
 * Renderiza Markdown para HTML, incluindo blocos customizados.
 * @param {string} markdown
 * @returns {string} HTML pronto para inserção no DOM
 */
export function renderizar(markdown) {
  return md.render(preprocessarBlocos(markdown))
}

/**
 * Extrai índice (TOC) dos títulos H2 e H3 do Markdown.
 * Usado pelo sidebar de navegação da página do módulo.
 *
 * @param {string} markdown
 * @returns {Array<{nivel:number, texto:string, ancora:string}>}
 */
export function extrairTOC(markdown) {
  const toc   = []
  const regex = /^(#{2,3})\s+(.+)$/gm
  let match
  while ((match = regex.exec(markdown)) !== null) {
    toc.push({
      nivel:  match[1].length,
      texto:  match[2].trim(),
      ancora: slugify(match[2].trim()),
    })
  }
  return toc
}

/**
 * Adiciona IDs nos elementos h2/h3 do HTML renderizado para permitir
 * a navegação interna pelos links do TOC.
 *
 * @param {string} html
 * @returns {string}
 */
export function adicionarAncorasTitulos(html) {
  return html.replace(/<(h[23])>(.*?)<\/\1>/g, (_, tag, conteudo) => {
    const ancora = slugify(conteudo.replace(/<[^>]+>/g, ''))
    return `<${tag} id="${ancora}">${conteudo}</${tag}>`
  })
}

/** Transforma texto em slug para âncoras: "Por quê?" → "por-que" */
function slugify(texto) {
  return texto
    .toLowerCase()
    .normalize('NFD').replace(/[̀-ͯ]/g, '')
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .trim()
}
