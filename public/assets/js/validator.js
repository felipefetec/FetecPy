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

    // Se houve erro de execução (stderr), o caso falha imediatamente —
    // código com erros nunca deve ser considerado correto, mesmo que o
    // stdout coincida com o esperado por acidente
    if (stderr) {
      if (!algumErro) algumErro = stderr
      resultadosCasos.push({
        passou:   false,
        input:    caso.input ?? '',
        esperado: caso.esperado,
        obtido:   stdout,
      })
      continue
    }

    // Normaliza ambos os lados antes de comparar
    const obtidoNorm   = normalizar(stdout)
    const esperadoNorm = normalizar(caso.esperado)
    const passou       = obtidoNorm === esperadoNorm

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
 * Executa o código do aluno para definir a função e testa cada caso
 * inteiramente dentro do Pyodide (sem cruzar a fronteira JS↔Python nos args).
 *
 * Por que Python-side? Chamar funcao(...args) via proxy JS tem problemas:
 *   - int/float do Python são convertidos para number JS (perde precisão)
 *   - listas/dicionários retornados precisam de .toJs() mas perdem tipo
 *   - comparação com JSON.stringify pode falhar para None, True, False
 *
 * Solução: passamos args e esperado como JSON, executamos tudo em Python
 * e recebemos o resultado como JSON. Zero conversão de tipo.
 *
 * @param {string}   codigo       Código Python que define a função
 * @param {string}   nomeFuncao   Nome da função a ser chamada
 * @param {Array<{args: any[], esperado: any}>} casos
 * @returns {Promise<ResultadoValidacao>}
 */
export async function validateFunction(codigo, nomeFuncao, casos) {
  const py = await carregarPyodide()

  // Carrega o código do aluno para definir a função no namespace Python
  try {
    await py.runPythonAsync(codigo)
  } catch (e) {
    return {
      passou: false,
      casos:  [],
      erro:   `Erro de sintaxe ou execução ao carregar o código: ${e.message}`,
    }
  }

  // Verifica se a função foi definida com o nome correto
  const nomeEscapado = nomeFuncao.replace(/'/g, "\\'")
  const existeStr    = await py.runPythonAsync(`'${nomeEscapado}' in dir()`)
  if (!existeStr) {
    return {
      passou: false,
      casos:  [],
      erro:   `Função '${nomeFuncao}' não encontrada. Você definiu a função com esse nome exato?`,
    }
  }

  const resultadosCasos = []

  for (const caso of casos) {
    // Serializa args e esperado em JSON para passar ao Python sem risco de tipo
    const argsJson     = JSON.stringify(caso.args)
    const esperadoJson = JSON.stringify(caso.esperado)

    // Script que executa a chamada e serializa o retorno para JSON.
    // Tudo em Python: sem conversão JS↔Python nos valores.
    const script = `
import json as _json

_args     = _json.loads(${JSON.stringify(argsJson)})
_esperado = _json.loads(${JSON.stringify(esperadoJson)})
_erro_msg = None
_obtido   = None
_passou   = False

try:
    _ret    = ${nomeFuncao}(*_args)
    _obtido = _ret
    # Compara via JSON para tratar None/True/False/int vs float igualmente
    _passou = _json.dumps(_ret, ensure_ascii=False) == _json.dumps(_esperado, ensure_ascii=False)
except Exception as _e:
    _erro_msg = str(_e)

_json.dumps({
    'passou':   _passou,
    'obtido':   _json.dumps(_obtido, ensure_ascii=False) if _erro_msg is None else None,
    'erro':     _erro_msg,
})
`

    let resultStr
    try {
      resultStr = await py.runPythonAsync(script)
    } catch (e) {
      // Erro inesperado no próprio script de teste (não no código do aluno)
      resultadosCasos.push({
        passou:   false,
        input:    `${nomeFuncao}(${caso.args.map(a => JSON.stringify(a)).join(', ')})`,
        esperado: JSON.stringify(caso.esperado),
        obtido:   '',
        erro:     `Erro interno: ${e.message}`,
      })
      continue
    }

    const res = JSON.parse(resultStr)

    resultadosCasos.push({
      passou:   res.passou,
      input:    `${nomeFuncao}(${caso.args.map(a => JSON.stringify(a)).join(', ')})`,
      esperado: JSON.stringify(caso.esperado),
      obtido:   res.obtido ?? `ERRO: ${res.erro}`,
      erro:     res.erro ?? null,
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
 *   deve_conter:     nós AST que DEVEM estar presentes (ex: "For", "FunctionDef")
 *   nao_deve_conter: nós AST que NÃO podem aparecer (ex: "While", "Recursion")
 *   deve_chamar:     funções que devem ser chamadas (ex: "print", "append")
 *   nao_deve_chamar: funções que NÃO devem ser chamadas (ex: "sum", "max", "min")
 *
 * Detecta chamadas diretas (`sum(x)`) e chamadas de método (`lista.sort()`).
 * Cada regra gera uma entrada em `casos` — viola → passou=false, ok → passou=true.
 *
 * @param {string} codigo
 * @param {{
 *   deve_conter?:     string[],
 *   nao_deve_conter?: string[],
 *   deve_chamar?:     string[],
 *   nao_deve_chamar?: string[]
 * }} regras
 * @returns {Promise<ResultadoValidacao>}
 */
export async function validateAst(codigo, regras) {
  // Serializa regras via JSON para passar ao Python sem risco de escapes quebrados
  const regrasJson = JSON.stringify({
    deve_conter:     regras.deve_conter     ?? [],
    nao_deve_conter: regras.nao_deve_conter ?? [],
    deve_chamar:     regras.deve_chamar     ?? [],
    nao_deve_chamar: regras.nao_deve_chamar ?? [],
  })

  // Código do aluno em base64 para evitar qualquer problema com aspas/escapes
  const codigoB64 = btoa(unescape(encodeURIComponent(codigo)))

  // Script Python que analisa o AST e retorna resultado JSON
  const scriptAst = `
import ast
import json
import base64

_codigo = base64.b64decode('${codigoB64}').decode('utf-8')
_regras = json.loads(${JSON.stringify(regrasJson)})

_violacoes = []
_passou    = True

try:
    _tree = ast.parse(_codigo)
except SyntaxError as e:
    _violacoes.append({'passou': False, 'descricao': f'Erro de sintaxe: {e}'})
    _passou = False
    _tree   = None

if _tree is not None:
    # Coleta todos os tipos de nós AST presentes na árvore
    _nos_presentes = {type(n).__name__ for n in ast.walk(_tree)}

    # Coleta chamadas diretas (sum(...)) e de método (lista.sort())
    # — ambas são ast.Call, mas com func diferente
    _chamadas = set()
    for _n in ast.walk(_tree):
        if not isinstance(_n, ast.Call):
            continue
        if isinstance(_n.func, ast.Name):
            # Chamada direta: sum(), print(), min()
            _chamadas.add(_n.func.id)
        elif isinstance(_n.func, ast.Attribute):
            # Chamada de método: lista.append(), obj.sort()
            _chamadas.add(_n.func.attr)

    # ── Verifica cada regra ──────────────────────────────────────────

    for _no in _regras['deve_conter']:
        if _no not in _nos_presentes:
            _violacoes.append({'passou': False, 'descricao': f"O código deve usar '{_no}', mas não foi encontrado."})
            _passou = False
        else:
            _violacoes.append({'passou': True, 'descricao': f"'{_no}' encontrado no código. ✓"})

    for _no in _regras['nao_deve_conter']:
        if _no in _nos_presentes:
            _violacoes.append({'passou': False, 'descricao': f"O código não deve usar '{_no}', mas foi encontrado."})
            _passou = False
        else:
            _violacoes.append({'passou': True, 'descricao': f"'{_no}' não utilizado. ✓"})

    for _fn in _regras['deve_chamar']:
        if _fn not in _chamadas:
            _violacoes.append({'passou': False, 'descricao': f"O código deve chamar '{_fn}()', mas não chamou."})
            _passou = False
        else:
            _violacoes.append({'passou': True, 'descricao': f"Chamada a '{_fn}()' encontrada. ✓"})

    for _fn in _regras['nao_deve_chamar']:
        if _fn in _chamadas:
            _violacoes.append({'passou': False, 'descricao': f"O código não deve chamar '{_fn}()'. Implemente você mesmo."})
            _passou = False
        else:
            _violacoes.append({'passou': True, 'descricao': f"'{_fn}()' não foi chamado (ótimo!). ✓"})

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
      erro:   `Erro ao analisar a estrutura do código: ${e.message}`,
    }
  }

  const resultado = JSON.parse(resultadoJson)

  // Converte cada violação/aprovação para o formato ResultadoCaso
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
