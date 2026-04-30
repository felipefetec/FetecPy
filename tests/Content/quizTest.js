/**
 * Testes de regressão dos quizzes (Prompt 7.4).
 *
 * Valida a integridade estrutural de todos os arquivos content/quiz/*.json:
 *   - Todos os módulos têm um quiz
 *   - Cada quiz tem entre 3 e 5 perguntas
 *   - Cada pergunta tem campos obrigatórios (id, pergunta, opcoes, resposta_correta, explicacao)
 *   - resposta_correta é um índice válido dentro de opcoes
 *   - Não há opções duplicadas dentro da mesma pergunta
 */

import { describe, it, expect } from 'vitest'
import { readdirSync, readFileSync } from 'fs'
import { join, resolve } from 'path'

const QUIZ_DIR = resolve('content/quiz')

/** Carrega todos os arquivos de quiz e retorna array com metadados */
function listarQuizzes() {
  return readdirSync(QUIZ_DIR)
    .filter(f => f.endsWith('.json'))
    .sort()
    .map(arq => {
      const dados = JSON.parse(readFileSync(join(QUIZ_DIR, arq), 'utf8'))
      return { arquivo: arq, moduloId: arq.replace('.json', ''), ...dados }
    })
}

// ----------------------------------------------------------------
// Grupo 1 — Cobertura: existe quiz para cada módulo
// ----------------------------------------------------------------
describe('Cobertura dos quizzes', () => {
  const quizzes = listarQuizzes()
  const arquivos = quizzes.map(q => q.arquivo)

  it('existem 8 quizzes (módulos 01 a 08)', () => {
    expect(quizzes.length).toBe(8)
  })

  for (let i = 1; i <= 8; i++) {
    const arq = `${String(i).padStart(2, '0')}.json`
    it(`quiz ${arq} existe`, () => {
      expect(arquivos).toContain(arq)
    })
  }
})

// ----------------------------------------------------------------
// Grupo 2 — Estrutura de cada quiz
// ----------------------------------------------------------------
describe('Estrutura dos quizzes', () => {
  const quizzes = listarQuizzes()

  for (const quiz of quizzes) {
    describe(`Quiz ${quiz.arquivo}`, () => {
      it('tem campo modulo', () => {
        expect(quiz).toHaveProperty('modulo')
      })

      it('campo modulo bate com o nome do arquivo', () => {
        expect(quiz.modulo).toBe(quiz.moduloId)
      })

      it('tem campo perguntas (array)', () => {
        expect(Array.isArray(quiz.perguntas)).toBe(true)
      })

      it('tem entre 3 e 5 perguntas', () => {
        expect(quiz.perguntas.length).toBeGreaterThanOrEqual(3)
        expect(quiz.perguntas.length).toBeLessThanOrEqual(5)
      })

      // ── Valida cada pergunta individualmente ──────────────────

      for (const p of quiz.perguntas ?? []) {
        describe(`pergunta ${p.id ?? '?'}`, () => {
          it('tem campo id (string)', () => {
            expect(typeof p.id).toBe('string')
            expect(p.id.trim().length).toBeGreaterThan(0)
          })

          it('tem campo pergunta (texto não-vazio)', () => {
            expect(typeof p.pergunta).toBe('string')
            expect(p.pergunta.trim().length).toBeGreaterThan(5)
          })

          it('tem opcoes (array com 3 a 5 itens)', () => {
            expect(Array.isArray(p.opcoes)).toBe(true)
            expect(p.opcoes.length).toBeGreaterThanOrEqual(3)
            expect(p.opcoes.length).toBeLessThanOrEqual(5)
          })

          it('todas as opções são strings não-vazias', () => {
            for (const op of p.opcoes ?? []) {
              expect(typeof op).toBe('string')
              expect(op.trim().length).toBeGreaterThan(0)
            }
          })

          it('resposta_correta é número inteiro', () => {
            expect(typeof p.resposta_correta).toBe('number')
            expect(Number.isInteger(p.resposta_correta)).toBe(true)
          })

          it('resposta_correta é índice válido dentro de opcoes', () => {
            expect(p.resposta_correta).toBeGreaterThanOrEqual(0)
            expect(p.resposta_correta).toBeLessThan(p.opcoes?.length ?? 0)
          })

          it('não tem opções duplicadas', () => {
            const unicas = new Set(p.opcoes)
            expect(unicas.size).toBe(p.opcoes?.length ?? 0)
          })

          it('tem campo explicacao (texto não-vazio)', () => {
            expect(typeof p.explicacao).toBe('string')
            expect(p.explicacao.trim().length).toBeGreaterThan(10)
          })
        })
      }
    })
  }
})
