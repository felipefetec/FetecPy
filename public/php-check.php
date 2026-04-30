<?php
/**
 * FetecPy — Verificação de requisitos do servidor.
 *
 * Acesse este arquivo UMA VEZ após o upload para confirmar que o host
 * tem tudo que o FetecPy precisa. REMOVA em seguida por segurança.
 *
 * URL de acesso: https://seudominio.com/php-check.php
 */

$requisitos = [
    'PHP >= 8.0'       => version_compare(PHP_VERSION, '8.0.0', '>='),
    'PDO'              => extension_loaded('pdo'),
    'PDO SQLite'       => extension_loaded('pdo_sqlite'),
    'mbstring'         => extension_loaded('mbstring'),
    'json'             => extension_loaded('json'),
    'data/ gravável'   => is_writable(dirname(__DIR__) . '/data') || is_writable(__DIR__),
    'mod_rewrite'      => function_exists('apache_get_modules')
                          ? in_array('mod_rewrite', apache_get_modules())
                          : true, // assume OK em nginx/outros
];

$tudo_ok = array_sum($requisitos) === count($requisitos);

header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>FetecPy — Verificação do servidor</title>
  <style>
    body { font-family: monospace; background: #0f172a; color: #e2e8f0; padding: 2rem; }
    h1   { color: #22c55e; }
    table { border-collapse: collapse; margin-top: 1rem; }
    td, th { padding: 0.5rem 1.5rem 0.5rem 0; text-align: left; }
    .ok  { color: #4ade80; }
    .err { color: #f87171; }
    .resultado { margin-top: 1.5rem; padding: 1rem; border-radius: 8px; }
    .ok-box  { background: #14532d; color: #86efac; }
    .err-box { background: #7f1d1d; color: #fca5a5; }
    .aviso { color: #fbbf24; margin-top: 1rem; font-size: 0.9rem; }
  </style>
</head>
<body>
<h1>FetecPy — Verificação do servidor</h1>
<p>PHP <?= PHP_VERSION ?> | <?= PHP_OS ?></p>

<table>
  <tr><th>Requisito</th><th>Status</th></tr>
  <?php foreach ($requisitos as $nome => $ok): ?>
  <tr>
    <td><?= htmlspecialchars($nome) ?></td>
    <td class="<?= $ok ? 'ok' : 'err' ?>"><?= $ok ? '✓ OK' : '✗ FALTANDO' ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<div class="resultado <?= $tudo_ok ? 'ok-box' : 'err-box' ?>">
  <?= $tudo_ok
      ? '✓ Servidor compatível com o FetecPy.'
      : '✗ Alguns requisitos não foram atendidos. Contate o suporte do host.' ?>
</div>

<p class="aviso">
  ⚠️ Remova este arquivo após a verificação:<br>
  <code>rm php-check.php</code>
</p>
</body>
</html>
