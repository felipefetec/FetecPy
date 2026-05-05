/**
 * Wrapper do Pyodide para o FetecPy.
 *
 * Gerencia o ciclo de vida do Pyodide:
 *   - Carregamento lazy (só quando o aluno clica "Rodar" pela primeira vez)
 *   - Singleton: uma única instância compartilhada por toda a página
 *   - Fila de espera: múltiplas chamadas simultâneas aguardam o mesmo carregamento
 *   - Captura de stdout/stderr via StringIO Python
 *   - Simulação de stdin via StringIO Python
 */

// URL do Pyodide — v0.26.4 é estável e bem suportada
const PYODIDE_URL = 'https://cdn.jsdelivr.net/pyodide/v0.26.4/full/pyodide.js'
const PYODIDE_INDEX = 'https://cdn.jsdelivr.net/pyodide/v0.26.4/full/'

// Estado global do carregamento — compartilhado entre todos os editores da página
let instancia   = null   // Instância do Pyodide após carregamento
let carregando  = false  // true durante o download/inicialização
let promessa    = null   // Promise única para evitar carregamentos paralelos

/**
 * Carrega o Pyodide uma única vez e retorna a instância.
 *
 * Se já estiver carregado, retorna imediatamente.
 * Se estiver carregando, aguarda a mesma Promise (não baixa duas vezes).
 *
 * @param {function(number): void} onProgresso  Callback recebe 0–100 durante o load
 * @returns {Promise<PyodideInterface>}
 */
export async function carregarPyodide(onProgresso = () => {}) {
  if (instancia) return instancia

  if (promessa) {
    // Já está carregando — aguarda a mesma promise sem disparar novo download
    return promessa
  }

  carregando = true

  promessa = new Promise((resolve, reject) => {
    onProgresso(5)

    // Injeta o script do Pyodide dinamicamente para não bloquear o carregamento inicial
    const script = document.createElement('script')
    script.src   = PYODIDE_URL

    script.onerror = () => reject(new Error('Falha ao carregar o Pyodide. Verifique sua conexão.'))

    script.onload = async () => {
      try {
        onProgresso(30)

        // window.loadPyodide é exposto pelo script carregado acima
        instancia = await window.loadPyodide({ indexURL: PYODIDE_INDEX })

        onProgresso(100)
        carregando = false
        resolve(instancia)
      } catch (e) {
        carregando = false
        reject(e)
      }
    }

    document.head.appendChild(script)
  })

  return promessa
}

/**
 * Executa código Python e retorna stdout, stderr e erro de execução.
 *
 * A captura de saída é feita dentro do próprio Python com StringIO —
 * assim o código do aluno pode usar print() normalmente.
 *
 * Para simular input(), passa `stdin` como string; cada linha vira uma
 * chamada ao input() do aluno via sys.stdin.
 *
 * @param {string} codigo   Código Python do aluno
 * @param {string} stdin    Dados simulados para input(), um valor por linha
 * @returns {Promise<{stdout: string, stderr: string, erro: string|null}>}
 */
export async function runPython(codigo, stdin = '') {
  const py = await carregarPyodide()

  // Passa o código e o stdin para o namespace Python antes de executar
  py.globals.set('_fetecpy_code',  codigo)
  py.globals.set('_fetecpy_stdin', stdin)

  // Executa o wrapper que captura toda a saída
  await py.runPythonAsync(`
import sys
from io import StringIO
import traceback

# Redireciona stdout/stderr para buffers em memória
_out = StringIO()
_err = StringIO()
_old_stdout = sys.stdout
_old_stderr = sys.stderr
_old_stdin  = sys.stdin

sys.stdout = _out
sys.stderr = _err
sys.stdin  = StringIO(_fetecpy_stdin)

# Intercepta input() para descartar o prompt — em ambiente de testes o stdin é
# simulado via StringIO e não há terminal interativo. O prompt não deve ir para
# nenhum buffer capturado (stdout ou stderr), pois quebraria a comparação do validador.
# O aluno ainda pode escrever input('Digite: ') livremente no código.
import builtins as _builtins
_orig_input = _builtins.input

def _input_wrapper(prompt=''):
    return _orig_input('')  # descarta o prompt, lê do sys.stdin simulado

_builtins.input = _input_wrapper

try:
    exec(_fetecpy_code, {})
except SystemExit:
    pass   # exit() / quit() não deve crashar o ambiente
except BaseException:
    # Imprime o traceback completo no stderr para ajudar o aluno a debugar
    traceback.print_exc()
finally:
    _builtins.input = _orig_input  # restaura o input original
    sys.stdout = _old_stdout
    sys.stderr = _old_stderr
    sys.stdin  = _old_stdin
`)

  const stdout = py.runPython('_out.getvalue()')
  const stderr = py.runPython('_err.getvalue()')

  return {
    stdout: stdout ?? '',
    stderr: stderr ?? '',
    erro:   stderr ? stderr : null,
  }
}

/** Retorna true se o Pyodide já foi carregado e está pronto. */
export function pyodidePronto() {
  return instancia !== null
}
