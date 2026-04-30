/**
 * Testes de regressão da estrutura dos módulos (Prompt 7.4).
 *
 * Valida os arquivos Markdown de content/*.md:
 *   - Todos os módulos de 01 a 08 existem
 *   - Front-matter completo (modulo, titulo, duracao_estimada, pre_requisito)
 *   - campo modulo bate com o nome do arquivo
 *   - pre_requisito referencia módulo existente (sem ciclos)
 *   - Conteúdo tem seções ## obrigatórias (Resumo, Próximos passos)
 *   - Cada módulo tem pelo menos 3 seções H2
 */

import { describe, it, expect } from 'vitest'
import { readdirSync, readFileSync } from 'fs'
import { join, resolve } from 'path'

const CONTENT_DIR = resolve('content')

/** Faz parse simples do front-matter YAML delimitado por --- */
function parsearFrontMatter(texto) {
  const match = texto.match(/^---\r?\n(.*?)\r?\n---\r?\n/s)
  if (!match) return {}
  const fm = {}
  for (const linha of match[1].split('\n')) {
    const m = linha.match(/^([\w_]+)\s*:\s*"?([^"]*)"?\s*$/)
    if (m) fm[m[1].trim()] = m[2].trim()
  }
  return fm
}

/** Carrega todos os módulos da pasta content/ */
function listarModulos() {
  return readdirSync(CONTENT_DIR)
    .filter(f => /^\d{2}-.*\.md$/.test(f))
    .sort()
    .map(arq => {
      const conteudo = readFileSync(join(CONTENT_DIR, arq), 'utf8')
      const id = arq.slice(0, 2)
      return { arquivo: arq, id, conteudo, fm: parsearFrontMatter(conteudo) }
    })
}

// ----------------------------------------------------------------
// Grupo 1 — Cobertura: existem módulos para 01 a 08
// ----------------------------------------------------------------
describe('Cobertura dos módulos', () => {
  const modulos = listarModulos()
  const ids = modulos.map(m => m.id)

  it('existem 8 módulos (01 a 08)', () => {
    expect(modulos.length).toBe(8)
  })

  for (let i = 1; i <= 8; i++) {
    const id = String(i).padStart(2, '0')
    it(`módulo ${id} existe`, () => {
      expect(ids).toContain(id)
    })
  }
})

// ----------------------------------------------------------------
// Grupo 2 — Front-matter de cada módulo
// ----------------------------------------------------------------
describe('Front-matter dos módulos', () => {
  const modulos = listarModulos()
  const ids = modulos.map(m => m.id)

  for (const mod of modulos) {
    describe(`Módulo ${mod.id} (${mod.arquivo})`, () => {
      it('tem front-matter (bloco --- no início)', () => {
        expect(mod.conteudo).toMatch(/^---\r?\n/)
      })

      it('campo modulo presente e correto', () => {
        expect(mod.fm.modulo).toBe(mod.id)
      })

      it('campo titulo presente e não-vazio', () => {
        expect(typeof mod.fm.titulo).toBe('string')
        expect(mod.fm.titulo.trim().length).toBeGreaterThan(0)
      })

      it('campo duracao_estimada presente', () => {
        expect(mod.fm).toHaveProperty('duracao_estimada')
        expect(mod.fm.duracao_estimada.trim().length).toBeGreaterThan(0)
      })

      it('pre_requisito referencia módulo existente (se preenchido)', () => {
        const req = mod.fm.pre_requisito
        // Campo vazio ("") ou ausente é válido para o módulo 01
        if (!req || req.trim() === '') return
        expect(ids, `pre_requisito "${req}" não existe`).toContain(req)
      })

      it('não cria dependência cíclica (módulo não depende de si mesmo)', () => {
        expect(mod.fm.pre_requisito).not.toBe(mod.id)
      })

      it('pre_requisito não cria ciclo de dois nós (A→B→A)', () => {
        const req = mod.fm.pre_requisito
        if (!req || req.trim() === '') return
        const dependente = modulos.find(m => m.id === req)
        // O pré-requisito do módulo requisitado não pode apontar de volta para este
        expect(dependente?.fm?.pre_requisito).not.toBe(mod.id)
      })
    })
  }
})

// ----------------------------------------------------------------
// Grupo 3 — Estrutura de conteúdo de cada módulo
// ----------------------------------------------------------------
describe('Estrutura de conteúdo dos módulos', () => {
  const modulos = listarModulos()

  for (const mod of modulos) {
    describe(`Módulo ${mod.id} — conteúdo`, () => {
      it('tem pelo menos 3 seções H2 (##)', () => {
        const secoes = mod.conteudo.match(/^## .+/gm) ?? []
        expect(secoes.length).toBeGreaterThanOrEqual(3)
      })

      it('tem seção ## Resumo', () => {
        expect(mod.conteudo).toMatch(/^## Resumo/m)
      })

      it('tem seção ## Próximos passos', () => {
        expect(mod.conteudo).toMatch(/^## Próximos passos/m)
      })

      it('não tem seção ## Quiz embutida (quiz é JSON separado)', () => {
        // O quiz não deve aparecer como texto no markdown — está em content/quiz/
        expect(mod.conteudo).not.toMatch(/^## Quiz\s*$/m)
      })
    })
  }
})
