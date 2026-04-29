/**
 * Testes dos validadores de exercício (Prompt 5.3).
 *
 * Estratégia de mock:
 *   - validateTextLivre: puro JS, sem mock — testado diretamente.
 *   - validateOutput:    mock de runPython (simula stdout/stderr do Pyodide).
 *   - validateFunction:  mock de carregarPyodide (simula py.runPythonAsync).
 *   - validateAst:       mock de carregarPyodide (simula py.runPythonAsync).
 *   - validar():         verifica que o dispatcher chama o validador correto.
 *
 * O Pyodide não roda em Node — o mock garante que testamos a LÓGICA dos
 * validadores sem depender do ambiente de browser.
 */

import { vi, describe, it, expect, beforeEach } from 'vitest'

// Mock declarado antes de qualquer import de módulo que dependa de pyodide.js.
// Vitest hissa vi.mock() ao topo do arquivo automaticamente.
vi.mock('../../public/assets/js/pyodide.js', () => ({
  runPython:       vi.fn(),
  carregarPyodide: vi.fn(),
  pyodidePronto:   vi.fn(() => true),
}))

import { runPython, carregarPyodide } from '../../public/assets/js/pyodide.js'
import {
  validateTextLivre,
  validateOutput,
  validateFunction,
  validateAst,
  validar,
} from '../../public/assets/js/validator.js'

// ----------------------------------------------------------------
// Helpers de mock
// ----------------------------------------------------------------

/**
 * Cria um mock de instância Pyodide para validateFunction.
 *
 * validateFunction chama py.runPythonAsync() três vezes por execução:
 *   1ª: executa o código do aluno (define a função)
 *   2ª: verifica se o nome existe em dir()
 *   3ª+: script de cada caso de teste (retorna JSON)
 */
function mockPySessao({ execOk = true, fnExiste = true, casos = [] } = {}) {
  let chamadas = 0
  const py = {
    runPythonAsync: vi.fn(async () => {
      const n = chamadas++
      if (n === 0) {
        // Executar código do aluno
        if (!execOk) throw new Error('SyntaxError: sintaxe inválida')
        return undefined
      }
      if (n === 1) {
        // Checar se função existe em dir()
        return fnExiste
      }
      // Resultado de cada caso (JSON serializado como Python retornaria)
      const caso = casos[n - 2]
      return caso
        ? JSON.stringify(caso)
        : JSON.stringify({ passou: false, obtido: 'null', erro: 'caso não encontrado' })
    }),
  }
  carregarPyodide.mockResolvedValue(py)
  return py
}

/**
 * Cria mock de instância Pyodide para validateAst.
 * Recebe diretamente o objeto {passou, violacoes} que o script Python retornaria.
 */
function mockAstResultado(resultado) {
  carregarPyodide.mockResolvedValue({
    runPythonAsync: vi.fn().mockResolvedValue(JSON.stringify(resultado)),
  })
}

// ================================================================
// validateTextLivre — puro JS, sem Pyodide
// ================================================================
describe('validateTextLivre', () => {
  it('aprova texto com comprimento exato no mínimo', () => {
    const r = validateTextLivre('a'.repeat(80), 80)
    expect(r.passou).toBe(true)
    expect(r.erro).toBeNull()
  })

  it('rejeita texto abaixo do mínimo', () => {
    const r = validateTextLivre('curto demais', 80)
    expect(r.passou).toBe(false)
    expect(r.erro).toContain('80')
  })

  it('aplica trim antes de contar (espaços não contam)', () => {
    // 79 chars reais + espaços → deve rejeitar
    const r = validateTextLivre('   ' + 'x'.repeat(79) + '   ', 80)
    expect(r.passou).toBe(false)
  })

  it('usa 80 como padrão quando mínimo não é passado', () => {
    expect(validateTextLivre('a'.repeat(79)).passou).toBe(false)
    expect(validateTextLivre('a'.repeat(80)).passou).toBe(true)
  })

  it('retorna array casos com 1 elemento', () => {
    const r = validateTextLivre('texto', 80)
    expect(r.casos).toHaveLength(1)
    expect(r.casos[0].passou).toBe(false)
  })

  it('informa quantos caracteres foram escritos no campo obtido', () => {
    const r = validateTextLivre('abc', 80)
    expect(r.casos[0].obtido).toContain('3')
  })
})

// ================================================================
// validateOutput (Validador A) — mock de runPython
// ================================================================
describe('validateOutput', () => {
  beforeEach(() => vi.clearAllMocks())

  it('aprova quando stdout bate exatamente com esperado', async () => {
    runPython.mockResolvedValue({ stdout: 'Olá, Maria!\n', stderr: '' })
    const r = await validateOutput('...', [{ input: 'Maria\n', esperado: 'Olá, Maria!\n' }])
    expect(r.passou).toBe(true)
  })

  it('rejeita quando stdout é diferente do esperado', async () => {
    runPython.mockResolvedValue({ stdout: 'Oi, Maria!\n', stderr: '' })
    const r = await validateOutput('...', [{ input: '', esperado: 'Olá, Maria!\n' }])
    expect(r.passou).toBe(false)
    expect(r.casos[0].passou).toBe(false)
  })

  it('normaliza CRLF (\\r\\n → \\n) antes de comparar', async () => {
    runPython.mockResolvedValue({ stdout: 'linha\r\n', stderr: '' })
    const r = await validateOutput('...', [{ input: '', esperado: 'linha\n' }])
    expect(r.passou).toBe(true)
  })

  it('normaliza espaços no final da string (trimEnd)', async () => {
    runPython.mockResolvedValue({ stdout: 'texto   ', stderr: '' })
    const r = await validateOutput('...', [{ input: '', esperado: 'texto' }])
    expect(r.passou).toBe(true)
  })

  it('passa stdin correto para cada caso', async () => {
    runPython
      .mockResolvedValueOnce({ stdout: '4\n', stderr: '' })  // caso 0
      .mockResolvedValueOnce({ stdout: '9\n', stderr: '' })  // caso 1
    const r = await validateOutput('...', [
      { input: '2\n', esperado: '4\n' },
      { input: '3\n', esperado: '9\n' },
    ])
    expect(runPython).toHaveBeenCalledTimes(2)
    expect(runPython).toHaveBeenNthCalledWith(1, '...', '2\n')
    expect(runPython).toHaveBeenNthCalledWith(2, '...', '3\n')
    expect(r.passou).toBe(true)
  })

  it('todos os casos precisam passar para passou=true', async () => {
    runPython
      .mockResolvedValueOnce({ stdout: 'certo\n', stderr: '' })
      .mockResolvedValueOnce({ stdout: 'errado\n', stderr: '' })
    const r = await validateOutput('...', [
      { input: '', esperado: 'certo\n' },
      { input: '', esperado: 'certo\n' },
    ])
    expect(r.passou).toBe(false)
  })

  it('captura stderr como erro quando saída está errada', async () => {
    runPython.mockResolvedValue({ stdout: '', stderr: 'NameError: name x is not defined' })
    const r = await validateOutput('...', [{ input: '', esperado: 'ok\n' }])
    expect(r.passou).toBe(false)
    expect(r.erro).toContain('NameError')
  })
})

// ================================================================
// validateFunction (Validador B) — mock de carregarPyodide
// ================================================================
describe('validateFunction', () => {
  beforeEach(() => vi.clearAllMocks())

  it('aprova quando função retorna o valor correto', async () => {
    mockPySessao({ casos: [{ passou: true, obtido: '"Olá, Maria!"', erro: null }] })
    const r = await validateFunction('def saudar(n): return f"Olá, {n}!"', 'saudar', [
      { args: ['Maria'], esperado: 'Olá, Maria!' },
    ])
    expect(r.passou).toBe(true)
    expect(r.casos[0].passou).toBe(true)
  })

  it('rejeita quando função retorna valor diferente do esperado', async () => {
    mockPySessao({ casos: [{ passou: false, obtido: '"errado"', erro: null }] })
    const r = await validateFunction('def saudar(n): return "errado"', 'saudar', [
      { args: ['Maria'], esperado: 'Olá, Maria!' },
    ])
    expect(r.passou).toBe(false)
  })

  it('retorna erro de sintaxe quando código do aluno é inválido', async () => {
    mockPySessao({ execOk: false })
    const r = await validateFunction('def x(: pass', 'x', [])
    expect(r.passou).toBe(false)
    expect(r.erro).toContain('SyntaxError')
  })

  it('retorna erro quando função não está definida', async () => {
    mockPySessao({ fnExiste: false })
    const r = await validateFunction('x = 1', 'saudar', [])
    expect(r.passou).toBe(false)
    expect(r.erro).toContain('saudar')
  })

  it('testa múltiplos casos e retorna todos os resultados', async () => {
    mockPySessao({
      casos: [
        { passou: true, obtido: '1',   erro: null },
        { passou: true, obtido: '120', erro: null },
      ],
    })
    const r = await validateFunction('def fat(n): ...', 'fat', [
      { args: [1], esperado: 1 },
      { args: [5], esperado: 120 },
    ])
    expect(r.passou).toBe(true)
    expect(r.casos).toHaveLength(2)
  })

  it('reporta erro de execução dentro de um caso', async () => {
    mockPySessao({ casos: [{ passou: false, obtido: null, erro: 'ZeroDivisionError' }] })
    const r = await validateFunction('def div(n): return 1/n', 'div', [{ args: [0], esperado: null }])
    expect(r.passou).toBe(false)
    expect(r.erro).toContain('ZeroDivisionError')
  })
})

// ================================================================
// validateAst (Validador C) — mock de carregarPyodide
// ================================================================
describe('validateAst', () => {
  beforeEach(() => vi.clearAllMocks())

  it('aprova quando nó deve_conter está presente', async () => {
    mockAstResultado({ passou: true, violacoes: [{ passou: true, descricao: "'For' encontrado ✓" }] })
    const r = await validateAst('for i in range(5): pass', { deve_conter: ['For'] })
    expect(r.passou).toBe(true)
  })

  it('rejeita quando nó deve_conter está ausente', async () => {
    mockAstResultado({
      passou: false,
      violacoes: [{ passou: false, descricao: "deve usar 'For'" }],
    })
    const r = await validateAst('i = 0', { deve_conter: ['For'] })
    expect(r.passou).toBe(false)
    expect(r.casos[0].passou).toBe(false)
  })

  it('rejeita quando nó nao_deve_conter está presente', async () => {
    mockAstResultado({
      passou: false,
      violacoes: [{ passou: false, descricao: "não deve usar 'While'" }],
    })
    const r = await validateAst('while True: pass', { nao_deve_conter: ['While'] })
    expect(r.passou).toBe(false)
  })

  it('rejeita quando função nao_deve_chamar é chamada', async () => {
    mockAstResultado({
      passou: false,
      violacoes: [{ passou: false, descricao: "não deve chamar 'sum()'" }],
    })
    const r = await validateAst('print(sum([1,2]))', { nao_deve_chamar: ['sum'] })
    expect(r.passou).toBe(false)
  })

  it('aprova quando deve_chamar está presente', async () => {
    mockAstResultado({ passou: true, violacoes: [{ passou: true, descricao: "print() ✓" }] })
    const r = await validateAst('print("ok")', { deve_chamar: ['print'] })
    expect(r.passou).toBe(true)
  })

  it('combina múltiplas regras — retorna um caso por regra', async () => {
    mockAstResultado({
      passou: true,
      violacoes: [
        { passou: true, descricao: "'For' encontrado ✓" },
        { passou: true, descricao: "'While' não utilizado ✓" },
      ],
    })
    const r = await validateAst('for i in range(5): pass', {
      deve_conter:     ['For'],
      nao_deve_conter: ['While'],
    })
    expect(r.passou).toBe(true)
    expect(r.casos).toHaveLength(2)
  })

  it('retorna erro quando runPythonAsync lança exceção', async () => {
    carregarPyodide.mockResolvedValue({
      runPythonAsync: vi.fn().mockRejectedValue(new Error('falha interna')),
    })
    const r = await validateAst('código', {})
    expect(r.passou).toBe(false)
    expect(r.erro).toContain('falha interna')
  })
})

// ================================================================
// validar() — dispatcher principal
// ================================================================
describe('validar (dispatcher)', () => {
  beforeEach(() => vi.clearAllMocks())

  it('chama validateTextLivre sem usar Pyodide', async () => {
    const ex = { validacao: { tipo: 'texto_livre', minimo_caracteres: 5 } }
    const r = await validar(ex, '', 'abcde')
    expect(r.passou).toBe(true)
    expect(runPython).not.toHaveBeenCalled()
    expect(carregarPyodide).not.toHaveBeenCalled()
  })

  it('chama validateOutput para tipo saida_exata', async () => {
    runPython.mockResolvedValue({ stdout: 'ok\n', stderr: '' })
    const ex = { validacao: { tipo: 'saida_exata', casos: [{ input: '', esperado: 'ok\n' }] } }
    const r = await validar(ex, 'print("ok")')
    expect(runPython).toHaveBeenCalledOnce()
    expect(r.passou).toBe(true)
  })

  it('retorna erro com passou=false para tipo desconhecido', async () => {
    const ex = { validacao: { tipo: 'nao_existe' } }
    const r = await validar(ex, '')
    expect(r.passou).toBe(false)
    expect(r.erro).toContain('nao_existe')
  })
})
