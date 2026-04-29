/**
 * Módulo de gamificação — badges e XP.
 *
 * Define o catálogo completo de badges da plataforma e
 * expõe funções para mesclar com as badges conquistadas pelo aluno.
 */

/**
 * Catálogo de todas as badges disponíveis na plataforma.
 * Cada badge tem: id, nome, descrição e ícone emoji.
 * O campo "conquistada" é preenchido pela função mesclarBadges().
 */
export const BADGES_CATALOGO = [
  { id: 'primeiro_codigo',    nome: 'Primeiro código',     descricao: 'Rodou o primeiro print',              icone: '🐍' },
  { id: 'streak_3',           nome: 'Streak 3 dias',       descricao: '3 dias consecutivos estudando',       icone: '🔥' },
  { id: 'streak_7',           nome: 'Streak 7 dias',       descricao: '7 dias consecutivos estudando',       icone: '🔥' },
  { id: 'streak_30',          nome: 'Streak 30 dias',      descricao: '30 dias consecutivos estudando',      icone: '🔥' },
  { id: 'sniper',             nome: 'Sniper',               descricao: '5 quizzes seguidos sem errar',        icone: '🎯' },
  { id: 'estudante_dedicado', nome: 'Estudante dedicado',  descricao: '10 exercícios resolvidos',            icone: '📚' },
  { id: 'mestre_modulo',      nome: 'Mestre do módulo',    descricao: 'Primeiro módulo 100% concluído',      icone: '🏆' },
  { id: 'formado',            nome: 'Formado',              descricao: 'Todos os módulos concluídos',         icone: '🎓' },
  { id: 'pyodide_master',     nome: 'Pyodide Master',      descricao: 'Usou todas as bibliotecas dos exemplos', icone: '🚀' },
  { id: 'cacador_bugs',       nome: 'Caçador de bugs',     descricao: 'Resolveu após 3+ tentativas',         icone: '🐛' },
  { id: 'velocista',          nome: 'Velocista',            descricao: 'Exercício resolvido em menos de 1 min', icone: '⚡' },
  { id: 'coruja',             nome: 'Coruja',               descricao: 'Estudou depois das 23h',              icone: '🌙' },
  { id: 'madrugador',         nome: 'Madrugador',           descricao: 'Estudou antes das 7h',                icone: '☀️' },
  { id: 'persistente',        nome: 'Persistente',          descricao: 'Voltou após 7 dias sem estudar',      icone: '💎' },
  { id: 'criativo',           nome: 'Criativo',             descricao: 'Solução com estrutura diferente da padrão', icone: '🎨' },
]

/**
 * Mescla o catálogo com as badges conquistadas pelo aluno.
 * Retorna o catálogo completo com o campo "conquistada" preenchido.
 *
 * @param {Array<{badge_id, conquistado_em}>} badgesAluno  Vindo de GET /api/me
 * @returns {Array}  Catálogo completo com conquest info
 */
export function mesclarBadges(badgesAluno) {
  // Cria um Set dos IDs conquistados para busca O(1)
  const conquistadas = new Set(badgesAluno.map(b => b.badge_id))
  const datas = Object.fromEntries(badgesAluno.map(b => [b.badge_id, b.conquistado_em]))

  return BADGES_CATALOGO.map(badge => ({
    ...badge,
    conquistada:    conquistadas.has(badge.id),
    conquistado_em: datas[badge.id] ?? null,
  }))
}

/**
 * Formata o número de XP para exibição amigável.
 * Ex: 1500 → "1.500 XP"
 *
 * @param {number} xp
 * @returns {string}
 */
export function formatarXp(xp) {
  return xp.toLocaleString('pt-BR') + ' XP'
}
