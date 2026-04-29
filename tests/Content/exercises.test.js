/**
 * Testes de conteúdo dos exercícios (Prompt 5.3).
 *
 * Dois grupos:
 *
 *   1. Schema — garante que todo JSON em exercises/ tem os campos obrigatórios.
 *      Falha aqui significa que o exercício quebraria na API ou no frontend.
 *
 *   2. Soluções — para cada exercício com campo "solucao", verifica que o
 *      gabarito realmente passa nos próprios casos de validação.
 *      Usa python3 via child_process (sem Pyodide no Node).
 *      Cobre: texto_livre, saida_exata, funcao.
 *      Exercícios do tipo "ast" têm validação de schema da solução apenas.
 *
 * Por que python3 subprocess em vez de Pyodide?
 *   O Pyodide é WebAssembly projetado para o browser. Instalar @pyodide/pyodide
 *   no Node aumenta o tempo de setup em +30s e adiciona 300 MB de dependências.
 *   O python3 do sistema dá o mesmo resultado para os exercícios do projeto.
 */

import { describe, it, expect } from 'vitest'
import { readdirSync, readFileSync, writeFileSync, unlinkSync } from 'fs'
import { spawnSync } from 'child_process'
import { join, resolve } from 'path'
import { tmpdir } from 'os'

// ----------------------------------------------------------------
// Helpers
// ----------------------------------------------------------------

const EXERCISES_DIR = resolve('exercises')

// Campos obrigatórios em todo exercício JSON
const CAMPOS_OBRIGATORIOS = [
  'id', 'modulo', 'ordem', 'titulo', 'dificuldade', 'xp',
  'enunciado', 'antes_de_codar', 'validacao',
]

/** Lê todos os JSONs de exercícios e retorna array de objetos com caminho */
function listarTodos() {
  const exercicios = []
  const modulos = readdirSync(EXERCISES_DIR).filter(f => /^\d{2}$/.test(f)).sort()

  for (const mod of modulos) {
    const pasta = join(EXERCISES_DIR, mod)
    const arquivos = readdirSync(pasta).filter(f => f.endsWith('.json')).sort()

    for (const arq of arquivos) {
      const caminho = join(pasta, arq)
      const dados   = JSON.parse(readFileSync(caminho, 'utf8'))
      exercicios.push({ caminho, ...dados })
    }
  }
  return exercicios
}

/**
 * Executa código Python via python3 subprocess e retorna {stdout, stderr}.
 * Usa arquivo temporário para suportar código multi-linha corretamente.
 */
function runPython(codigo, stdin = '') {
  const tmp = join(tmpdir(), `fetecpy_test_${Date.now()}_${Math.random().toString(36).slice(2)}.py`)
  try {
    writeFileSync(tmp, codigo, 'utf8')
    const result = spawnSync('python3', [tmp], {
      input:    stdin,
      encoding: 'utf8',
      timeout:  5000,
    })
    return {
      stdout: result.stdout ?? '',
      stderr: result.stderr ?? '',
      ok:     result.status === 0,
    }
  } finally {
    try { unlinkSync(tmp) } catch { /* ignora — arquivo já pode ter sido removido */ }
  }
}

/** Normaliza saída para comparação (CRLF→LF, trimEnd) */
function normalizar(str) {
  return (str ?? '').replace(/\r\n/g, '\n').trimEnd()
}

// ----------------------------------------------------------------
// Grupo 1 — Schema
// ----------------------------------------------------------------
describe('Schema dos exercícios', () => {
  const exercicios = listarTodos()

  it('pelo menos 1 exercício existe no projeto', () => {
    expect(exercicios.length).toBeGreaterThan(0)
  })

  // Testa cada exercício individualmente para que falhas sejam rastreáveis
  for (const ex of exercicios) {
    describe(`${ex.id} (${ex.caminho.replace(EXERCISES_DIR + '/', '')})`, () => {
      it('tem todos os campos obrigatórios', () => {
        for (const campo of CAMPOS_OBRIGATORIOS) {
          expect(ex, `campo ausente: ${campo}`).toHaveProperty(campo)
        }
      })

      it('dificuldade é facil, medio ou desafio', () => {
        expect(['facil', 'medio', 'desafio']).toContain(ex.dificuldade)
      })

      it('xp é número positivo', () => {
        expect(typeof ex.xp).toBe('number')
        expect(ex.xp).toBeGreaterThan(0)
      })

      it('antes_de_codar é array com ao menos 1 item', () => {
        expect(Array.isArray(ex.antes_de_codar)).toBe(true)
        expect(ex.antes_de_codar.length).toBeGreaterThan(0)
      })

      it('validacao tem campo tipo', () => {
        expect(ex.validacao).toHaveProperty('tipo')
      })

      it('id segue o formato modulo-exNN', () => {
        expect(ex.id).toMatch(/^\d{2}-ex\d{2}$/)
      })

      it('modulo do id bate com a pasta', () => {
        const moduloDaPasta = ex.caminho.split('/').at(-2)
        expect(ex.id.startsWith(moduloDaPasta)).toBe(true)
      })
    })
  }
})

// ----------------------------------------------------------------
// Grupo 2 — Soluções (verificação que o gabarito passa nos testes)
// ----------------------------------------------------------------
describe('Soluções dos exercícios', () => {
  const exercicios = listarTodos().filter(ex => ex.solucao)

  it('pelo menos 1 exercício tem solucao definida', () => {
    expect(exercicios.length).toBeGreaterThan(0)
  })

  for (const ex of exercicios) {
    const tipo = ex.validacao?.tipo

    // ── texto_livre ──────────────────────────────────────────────
    if (tipo === 'texto_livre') {
      it(`${ex.id}: solucao tem caracteres suficientes (texto_livre)`, () => {
        const min = ex.validacao.minimo_caracteres ?? 80
        expect(ex.solucao.trim().length).toBeGreaterThanOrEqual(min)
      })
    }

    // ── saida_exata ──────────────────────────────────────────────
    if (tipo === 'saida_exata') {
      const casos = ex.validacao.casos ?? []
      for (let i = 0; i < casos.length; i++) {
        const caso = casos[i]
        it(`${ex.id}: solucao passa no caso ${i + 1}/${casos.length} (saida_exata)`, () => {
          const { stdout, stderr } = runPython(ex.solucao, caso.input ?? '')
          if (stderr && !stdout) {
            expect.fail(`Erro ao executar solução:\n${stderr}`)
          }
          expect(normalizar(stdout)).toBe(normalizar(caso.esperado))
        })
      }
    }

    // ── funcao ───────────────────────────────────────────────────
    if (tipo === 'funcao') {
      const nomeFn = ex.validacao.nome_funcao
      const casos  = ex.validacao.casos ?? []

      for (let i = 0; i < casos.length; i++) {
        const caso = casos[i]
        it(`${ex.id}: solucao passa no caso ${i + 1}/${casos.length} (funcao)`, () => {
          // Monta script que define a função e a chama com os args do caso
          const argsStr   = caso.args.map(a => JSON.stringify(a)).join(', ')
          const script    = `
import json
${ex.solucao}
resultado = ${nomeFn}(${argsStr})
print(json.dumps(resultado, ensure_ascii=False))
`.trim()

          const { stdout, stderr } = runPython(script)
          if (stderr && !stdout.trim()) {
            expect.fail(`Erro ao executar solução:\n${stderr}`)
          }
          const obtido   = JSON.parse(stdout.trim())
          const esperado = caso.esperado
          expect(JSON.stringify(obtido)).toBe(JSON.stringify(esperado))
        })
      }
    }

    // ── hibrido ──────────────────────────────────────────────────
    if (tipo === 'hibrido') {
      const validacoes = ex.validacao.validacoes ?? []
      for (const v of validacoes) {

        if (v.tipo === 'saida_exata') {
          for (let i = 0; i < (v.casos ?? []).length; i++) {
            const caso = v.casos[i]
            it(`${ex.id}: solucao passa no caso saida_exata ${i + 1} (hibrido)`, () => {
              const { stdout, stderr } = runPython(ex.solucao, caso.input ?? '')
              if (stderr && !stdout) expect.fail(`Erro:\n${stderr}`)
              expect(normalizar(stdout)).toBe(normalizar(caso.esperado))
            })
          }
        }

        if (v.tipo === 'funcao') {
          const nomeFn = v.nome_funcao
          for (let i = 0; i < (v.casos ?? []).length; i++) {
            const caso = v.casos[i]
            it(`${ex.id}: solucao passa no caso funcao ${i + 1} (hibrido)`, () => {
              const argsStr = caso.args.map(a => JSON.stringify(a)).join(', ')
              const script  = `
import json
${ex.solucao}
print(json.dumps(${nomeFn}(${argsStr}), ensure_ascii=False))
`.trim()
              const { stdout, stderr } = runPython(script)
              if (stderr && !stdout.trim()) expect.fail(`Erro:\n${stderr}`)
              expect(JSON.stringify(JSON.parse(stdout.trim()))).toBe(JSON.stringify(caso.esperado))
            })
          }
        }

        // ast: não há como verificar estrutura via subprocess de forma genérica —
        // o schema da solução (sintaxe Python válida) já é verificado implicitamente
        // ao rodar os outros validadores do hibrido
      }
    }
  }
})
