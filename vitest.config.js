import { defineConfig } from 'vitest/config'

export default defineConfig({
  test: {
    // Diretório raiz onde o Vitest procura arquivos de teste
    root: '.',

    // Padrão de arquivos de teste: qualquer *.test.js dentro de tests/
    include: ['tests/**/*.test.js'],

    // Ambiente de execução: node (sem DOM — para testar validadores puros)
    // Testes que precisarem de DOM podem sobrescrever com @vitest-environment jsdom
    environment: 'node',

    // Exibe nome de cada teste individual (mais fácil de debugar falhas)
    reporter: 'verbose',
  },
})
