/**
 * Wrapper do CodeMirror 6 para o FetecPy.
 *
 * Cria editores de código Python com tema dark, syntax highlighting,
 * números de linha e auto-indent.
 *
 * Usa esm.sh para garantir que todos os pacotes CodeMirror compartilham
 * a mesma instância de @codemirror/state (evita erro "multiple instances").
 * basicSetup já inclui keymaps padrão; Tab e Ctrl+Enter via domEventHandlers.
 */

import { EditorView, basicSetup } from 'https://esm.sh/codemirror@6.0.1'
import { python }  from 'https://esm.sh/@codemirror/lang-python@6.0.1'
import { oneDark } from 'https://esm.sh/@codemirror/theme-one-dark@6.0.0'

/**
 * Cria um editor CodeMirror em um elemento DOM.
 *
 * @param {HTMLElement} elemento      Elemento onde o editor será montado
 * @param {string}      codigoInicial Código pré-carregado no editor
 * @param {function}    onRun         Callback chamado com Ctrl+Enter (opcional)
 * @param {function}    onUpdate      Callback chamado com (novoConteudo: string) a cada mudança (opcional)
 * @returns {{ getValue(): string, setValue(code: string): void, foco(): void }}
 */
export function criarEditor(elemento, codigoInicial = '', onRun = null, onUpdate = null) {
  const extensoes = [
    basicSetup,          // números de linha, highlight, undo/redo, etc.
    python(),            // syntax highlighting Python
    oneDark,             // tema dark consistente com a plataforma
    EditorView.lineWrapping,

    // Notifica a cada mudança de conteúdo — usado para contador de chars, autosave etc.
    ...(onUpdate ? [EditorView.updateListener.of(upd => {
      if (upd.docChanged) onUpdate(upd.view.state.doc.toString())
    })] : []),

    // Tab insere 4 espaços (padrão Python) e Ctrl+Enter executa o código
    // Usamos domEventHandlers para não depender de @codemirror/commands extra
    EditorView.domEventHandlers({
      keydown(event, view) {
        // Tab → 4 espaços
        if (event.key === 'Tab' && !event.shiftKey) {
          event.preventDefault()
          view.dispatch(view.state.replaceSelection('    '))
          return true
        }
        // Ctrl+Enter ou Cmd+Enter → executa o código
        if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
          event.preventDefault()
          if (onRun) onRun()
          return true
        }
      },
    }),

    // Integração visual com o design dark da plataforma
    EditorView.theme({
      '&': {
        backgroundColor: '#0f172a',  // slate-950
        borderRadius: '0.5rem',
        fontSize: '0.95rem',
      },
      '.cm-scroller': {
        fontFamily: "'JetBrains Mono', 'Fira Code', monospace",
        lineHeight: '1.7',
        minHeight: '80px',
      },
      '.cm-gutters': {
        backgroundColor: '#0f172a',
        borderRight: '1px solid #1e293b',
        color: '#475569',
      },
      '.cm-activeLineGutter': { backgroundColor: '#1e293b' },
      '.cm-activeLine':       { backgroundColor: '#1e293b80' },
      '.cm-cursor':           { borderLeftColor: '#22c55e' },  // cursor verde Python
    }),
  ]

  const view = new EditorView({
    doc:        codigoInicial,
    extensions: extensoes,
    parent:     elemento,
  })

  return {
    /** Retorna o conteúdo atual do editor */
    getValue: () => view.state.doc.toString(),

    /** Substitui o conteúdo do editor */
    setValue: (codigo) => {
      view.dispatch({
        changes: { from: 0, to: view.state.doc.length, insert: codigo },
      })
    },

    /** Move o foco para o editor */
    foco: () => view.focus(),
  }
}
