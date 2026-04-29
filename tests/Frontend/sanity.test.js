/**
 * Teste de sanidade do ambiente Vitest.
 *
 * Verifica que a infraestrutura de testes JavaScript está funcionando.
 * Não testa lógica real — é apenas um smoke test do setup.
 */
import { describe, it, expect } from 'vitest'

describe('Sanidade do ambiente de testes', () => {
  it('operações matemáticas básicas funcionam', () => {
    // Se este teste falhar, o problema é no setup do Vitest, não no código
    expect(1 + 1).toBe(2)
    expect(10 - 3).toBe(7)
    expect(2 * 5).toBe(10)
  })

  it('comparações de string funcionam', () => {
    expect('FetecPy').toBe('FetecPy')
    expect('python'.toUpperCase()).toBe('PYTHON')
  })

  it('arrays funcionam corretamente', () => {
    const modulos = ['algoritmos', 'python-basico', 'controle']
    expect(modulos).toHaveLength(3)
    expect(modulos).toContain('python-basico')
  })
})
