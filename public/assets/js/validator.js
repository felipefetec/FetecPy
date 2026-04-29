/**
 * Validadores de exercícios do FetecPy.
 *
 * Três validadores disponíveis, cada um adequado a um tipo de exercício:
 *
 *   A — validateOutput:   compara stdout do código com saída esperada (módulos 2-5)
 *   B — validateFunction: executa função definida pelo aluno e testa retornos (módulos 3-8)
 *   C — validateAst:      verifica estrutura do código via AST Python (todos os módulos)
 *
 * Todos são assíncronos e dependem do Pyodide (exceto validateTextlivre).
 * Importar runPython do pyodide.js antes de usar.
 */

import { runPython, carregarPyodide } from './pyodide.js'

// ----------------------------------------------------------------
// Tipos de resultado — usados pelos 3 validadores
// ----------------------------------------------------------------

/**
 * @typedef {Object} ResultadoCaso
 * @property {boolean} passou
 * @property {string}  input     - stdin usado no caso (ou args serializados)
 * @property {string}  esperado  - saída/retorno esperado
 * @property {string}  obtido    - saída/retorno real do código
 */

/**
 * @typedef {Object} ResultadoValidacao
 * @property {boolean}           passou   - true se TODOS os casos passaram
 * @property {ResultadoCaso[]}   casos    - resultado individual de cada caso
 * @property {string|null}       erro     - mensagem de erro de execução (null se ok)
 */

// ----------------------------------------------------------------
// Validador A — saida_exata
// ----------------------------------------------------------------

/**
 * Executa o código para cada caso de teste e compara o stdout com o esperado.
 *
 * Normalização aplicada antes de comparar:
 *   - Quebras de linha Windows (\r\n) → Unix (\n)
 *   - Espaços em branco no FINAL da string removidos
 *
 * Isso evita falhas por diferença de OS e trailing newline acidental.
 *
 * @param {string}   codigo  Código Python do aluno
 * @param {Array<{input: string, esperado: string}>} casos
 * @returns {Promise<ResultadoValidacao>}
 */
export async function validateOutput(codigo, casos) {
  const resultadosCasos = []
  let algumErro = null

  for (const caso of casos) {
    const { stdout, stderr } = await runPython(codigo, caso.input ?? '')

    // Normaliza ambos os lados antes de comparar
    const obtidoNorm   = normalizar(stdout)
    const esperadoNorm = normalizar(caso.esperado)
    const passou       = obtidoNorm === esperadoNorm

    if (stderr && !algumErro) {
      // Guarda só o primeiro erro de execução para exibir ao aluno
      algumErro = stderr
    }

    resultadosCasos.push({
      passou,
      input:    caso.input ?? '',
      esperado: caso.esperado,
      obtido:   stdout,
    })
  }

  const todos = resultadosCasos.every(c => c.passou)

  return {
    passou: todos,
    casos:  resultadosCasos,
    erro:   todos ? null : (algumErro ?? null),
  }
}

// ----------------------------------------------------------------
// Validador B — funcao
// ----------------------------------------------------------------

/**
 * Executa o código do aluno para definir a função, depois chama cada
 * caso com os argumentos fornecidos e compara o retorno.
 *
 * O aluno precisa definir uma função com o nome exato passado em `nomeFuncao`.
 * O sistema chama via pyodide.globals e captura o retorno.
 *
 * @param {string}   codigo       Código Python que define a função
 * @param {string}   nomeFuncao   Nome da função a ser chamada
 * @param {Array<{args: any[], esperado: any}>} casos
 * @returns {Promise<ResultadoValidacao>}
 */
export async function validateFunction(codigo, nomeFuncao, casos) {
  // Precisa da instância diretamente para acessar globals e chamar a função
  const py = await carregarPyodide()

  // Executa o código do aluno para definir a função no namespace global do Pyodide
  try {
    await py.runPythonAsync(codigo)
  } catch (e) {
    // Erro de sintaxe ou exceção na definição da função
    return {
      passou: false,
      casos:  [],
      erro:   `Erro ao carregar o código: ${e.message}`,
    }
  }

  // Verifica se a função foi de fato definida
  const funcaoExiste = py.globals.has(nomeFuncao)
  if (!funcaoExiste) {
    return {
      passou: false,
      casos:  [],
      erro:   `Função '${nomeFuncao}' não encontrada. Você definiu a função com esse nome exato?`,
    }
  }

  const funcao          = py.globals.get(nomeFuncao)
  const resultadosCasos = []

  for (const caso of casos) {
    let obtido    = null
    let erroExec  = null
    let passou    = false

    try {
      // Chama a função com os argumentos do caso
      const resultado = funcao(...caso.args)
      // Pyodide pode retornar tipos Python — converte para JS
      obtido = resultado?.toJs ? resultado.toJs() : resultado
      passou = JSON.stringify(obtido) === JSON.stringify(caso.esperado)
    } catch (e) {
      erroExec = e.message
      obtido   = `ERRO: ${e.message}`
    }

    resultadosCasos.push({
      passou,
      input:    `f(${caso.args.map(a => JSON.stringify(a)).join(', ')})`,
      esperado: JSON.stringify(caso.esperado),
      obtido:   JSON.stringify(obtido),
      erro:     erroExec,
    })
  }

  const todos = resultadosCasos.every(c => c.passou)

  return {
    passou: todos,
    casos:  resultadosCasos,
    erro:   todos ? null : (resultadosCasos.find(c => c.erro)?.erro ?? null),
  }
}

// ----------------------------------------------------------------
// Validador C — ast
// ----------------------------------------------------------------

/**
 * Verifica a estrutura do código Python via `ast.parse` rodando dentro do Pyodide.
 *
 * Regras suportadas:
 *   deve_conter:    array de nomes de nós AST que DEVEM estar presentes
 *   nao_deve_conter: array de nomes de nós AST que NÃO podem estar presentes
 *   deve_chamar:    array de nomes de funções que devem ser chamadas
 *
 * Cada violação gera uma entrada em `casos` com passou=false e a descrição do problema.
 *
 * @param {string} codigo
 * @param {{deve_conter?: string[], nao_deve_conter?: string[], deve_chamar?: string[]}} regras
 * @returns {Promise<ResultadoValidacao>}
 */
export async function validateAst(codigo, regras) {
  // Serializa as regras para passar ao Python via JSON
  const regrasJson = JSON.stringify({
    deve_conter:     regras.deve_conter     ?? [],
    nao_deve_conter: regras.nao_deve_conter ?? [],
    deve_chamar:     regras.deve_chamar     ?? [],
  })

  // Escapa o código do aluno para passar como string Python
  // Usamos base64 para evitar problemas com aspas e caracteres especiais
  const codigoB64 = btoa(unescape(encodeURIComponent(codigo)))

  // Script Python que roda dentro do Pyodide para analisar o AST
  const scriptAst = `
import ast
import json
import base64

# Decodifica o código do aluno (base64 para evitar escapes)
_codigo = base64.b64decode('${codigoB64}').decode('utf-8')
_regras = json.loads('${regrasJson.replace(/'/g, "\\'")}')

_violacoes = []
_passou    = True

try:
    _tree = ast.parse(_codigo)
except SyntaxError as e:
    _violacoes.append({'passou': False, 'descricao': f'Erro de sintaxe: {e}'})
    _passou = False
    _tree   = None

if _tree is not None:
    # Coleta todos os tipos de nós e chamadas de função presentes
    _nos_presentes    = {type(n).__name__ for n in ast.walk(_tree)}
    _chamadas         = {
        n.func.id
        for n in ast.walk(_tree)
        if isinstance(n, ast.Call) and isinstance(n.func, ast.Name)
    }

    for no in _regras['deve_conter']:
        if no not in _nos_presentes:
            _violacoes.append({'passou': False, 'descricao': f"O código deve usar '{no}', mas não foi encontrado."})
            _passou = False
        else:
            _violacoes.append({'passou': True, 'descricao': f"'{no}' encontrado. ✓"})

    for no in _regras['nao_deve_conter']:
        if no in _nos_presentes:
            _violacoes.append({'passou': False, 'descricao': f"O código não deve usar '{no}', mas foi encontrado."})
            _passou = False
        else:
            _violacoes.append({'passou': True, 'descricao': f"'{no}' não está presente. ✓"})

    for fn in _regras['deve_chamar']:
        if fn not in _chamadas:
            _violacoes.append({'passou': False, 'descricao': f"O código deve chamar a função '{fn}()', mas não chamou."})
            _passou = False
        else:
            _violacoes.append({'passou': True, 'descricao': f"Chamada a '{fn}()' encontrada. ✓"})

json.dumps({'passou': _passou, 'violacoes': _violacoes})
`

  const py = await carregarPyodide()

  let resultadoJson
  try {
    resultadoJson = await py.runPythonAsync(scriptAst)
  } catch (e) {
    return {
      passou: false,
      casos:  [],
      erro:   `Erro ao analisar o código: ${e.message}`,
    }
  }

  const resultado = JSON.parse(resultadoJson)

  // Converte violações para o formato ResultadoCaso
  const casos = resultado.violacoes.map(v => ({
    passou:   v.passou,
    input:    '',
    esperado: '',
    obtido:   v.descricao,
  }))

  return {
    passou: resultado.passou,
    casos,
    erro: resultado.passou ? null : 'O código não atende aos requisitos estruturais.',
  }
}

// ----------------------------------------------------------------
// Validador texto_livre — sem Pyodide
// ----------------------------------------------------------------

/**
 * Valida exercícios de pseudocódigo/texto livre (módulo 1).
 * Aprovado quando o texto tem ao menos `minimoCaracteres` caracteres não-brancos.
 *
 * @param {string} texto
 * @param {number} minimoCaracteres
 * @returns {ResultadoValidacao}  (síncrono — sem Pyodide)
 */
export function validateTextLivre(texto, minimoCaracteres = 80) {
  const trimado = texto.trim()
  const passou  = trimado.length >= minimoCaracteres

  return {
    passou,
    casos: [{
      passou,
      input:    '',
      esperado: `Mínimo de ${minimoCaracteres} caracteres`,
      obtido:   `${trimado.length} caracteres escritos`,
    }],
    erro: passou ? null : `Escreva pelo menos ${minimoCaracteres} caracteres para concluir.`,
  }
}

// ----------------------------------------------------------------
// Validador híbrido — combina múltiplos validadores
// ----------------------------------------------------------------

/**
 * Executa múltiplos validadores em sequência e retorna falha no primeiro erro.
 * Todos precisam passar para o resultado ser "passou: true".
 *
 * @param {string} codigo
 * @param {Array<{tipo: string, ...}>} validacoes  Lista de configurações de validação
 * @returns {Promise<ResultadoValidacao>}
 */
export async function validateHibrido(codigo, validacoes) {
  const todosResultados = []

  for (const v of validacoes) {
    let resultado

    switch (v.tipo) {
      case 'saida_exata':
        resultado = await validateOutput(codigo, v.casos)
        break
      case 'funcao':
        resultado = await validateFunction(codigo, v.nome_funcao, v.casos)
        break
      case 'ast':
        resultado = await validateAst(codigo, v)
        break
      default:
        continue
    }

    todosResultados.push(resultado)

    // Interrompe no primeiro validador que falhar para mostrar erro focado
    if (!resultado.passou) {
      return {
        passou: false,
        casos:  resultado.casos,
        erro:   resultado.erro,
      }
    }
  }

  // Todos passaram — retorna o último resultado (com seus casos)
  const ultimo = todosResultados[todosResultados.length - 1]
  return ultimo ?? { passou: true, casos: [], erro: null }
}

// ----------------------------------------------------------------
// Dispatcher principal — escolhe o validador pelo tipo do exercício
// ----------------------------------------------------------------

/**
 * Ponto de entrada único: recebe o exercício e o código, chama o validador correto.
 *
 * @param {Object} exercicio  JSON completo do exercício (com campo "validacao")
 * @param {string} codigo     Código/texto do aluno
 * @param {string} [texto]    Para tipo texto_livre — passa o texto aqui
 * @returns {Promise<ResultadoValidacao>}
 */
export async function validar(exercicio, codigo, texto = '') {
  const v = exercicio.validacao ?? {}

  switch (v.tipo) {
    case 'texto_livre':
      return validateTextLivre(texto || codigo, v.minimo_caracteres ?? 80)

    case 'saida_exata':
      return validateOutput(codigo, v.casos ?? [])

    case 'funcao':
      return validateFunction(codigo, v.nome_funcao, v.casos ?? [])

    case 'ast':
      return validateAst(codigo, v)

    case 'hibrido':
      return validateHibrido(codigo, v.validacoes ?? [])

    default:
      return { passou: false, casos: [], erro: `Tipo de validação desconhecido: '${v.tipo}'` }
  }
}

// ----------------------------------------------------------------
// Utilitário
// ----------------------------------------------------------------

/**
 * Normaliza uma string de saída para comparação:
 * - CRLF → LF
 * - Remove espaços/tabs no final da string inteira
 */
function normalizar(str) {
  return (str ?? '').replace(/\r\n/g, '\n').trimEnd()
}
