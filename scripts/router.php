<?php
/**
 * Router para o servidor embutido do PHP (php -S).
 *
 * O servidor embutido não processa .htaccess, então as regras de
 * rewrite do Apache não funcionam. Este arquivo replica o comportamento:
 *
 *   /api/*         → public/api/index.php (front controller da API)
 *   /assets/*      → serve o arquivo estático normalmente
 *   /*             → public/index.html (SPA)
 *
 * Usado exclusivamente em desenvolvimento local via scripts/dev.sh.
 * Em produção (Apache) o .htaccess cuida de tudo isso.
 */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Rotas da API → front controller
if (str_starts_with($uri, '/api/')) {
    // Simula o ambiente que o Apache criaria com o RewriteRule
    require __DIR__ . '/../public/api/index.php';
    return true;
}

// Arquivos estáticos existentes (JS, CSS, imagens) → serve diretamente
$arquivo = __DIR__ . '/../public' . $uri;
if ($uri !== '/' && file_exists($arquivo) && !is_dir($arquivo)) {
    return false; // false = servidor embutido serve o arquivo normalmente
}

// Qualquer outra rota → SPA (index.html)
require __DIR__ . '/../public/index.html';
return true;
