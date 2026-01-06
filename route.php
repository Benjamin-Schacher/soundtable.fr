<?php
function route($uri) {
    // Normaliser l'URI en supprimant les slashes finaux
    $uri = rtrim($uri, '/');
    if ($uri === '') {
        $uri = '/'; // Assurer que la racine est '/'
    }

    $routes = [
        '/' => 'page/acceuil/acceuil.php',
        '/en' => 'page/acceuil/en/acceuilEn.php',
        '/chroniques-oubliees' => 'page/chronique/chroniques-oubliees.php',
        '/starwars' => 'page/starwars/front-page.php',
        '/warhammeurArmise' => 'page/warhammeur/warhammeur.php',
        '/en/warhammeurArmise' => 'page/warhammeur/en/warhammeurEn.php',
        '/en/MTG-life-counter' => 'page/mtg-life-counter/lifeCounter.php',
        '/MTG-compteur-de-point-de-vie' => 'page/mtg-life-counter/lifeCounter.php',
        '/admin/generate-sitemap' => 'generate_sitemap.php',
        '/article/([a-zA-Z0-9-]+)' => 'page/article/$1.json',
        '/api/armies' => 'api/armies.php',
        '/api/ideabox' => 'api/ideabox.php',

        '/admin/login' => 'page/admin/login.php',
        '/admin/dashboard' => 'page/admin/dashboard.php',
        '/admin/create' => 'page/admin/create_article.php',
        '/api/admin/login' => 'api/admin/login.php',
        '/api/admin/save-article' => 'api/admin/save_article.php',
        '/api/admin/logout' => 'api/admin/logout.php',
    ];

    // Vérifier les routes statiques
    if (array_key_exists($uri, $routes)) {
        $file = $routes[$uri];
        if (file_exists($file)) {
            include $file;
            return;
        }
    }

    // Vérifier les routes dynamiques
    foreach ($routes as $route => $file) {
        $pattern = '#^' . str_replace('$1', '([a-zA-Z0-9-]+)', $route) . '$#';
        if (preg_match($pattern, $uri, $matches)) {
            if (preg_match('/\.json$/', $file)) {
                $jsonFile = str_replace('$1', $matches[1], $file);
                if (file_exists($jsonFile)) {
                    require 'handle_article.php';
                    return;
                }
            }
        }
    }

    // Si aucune route ne correspond
    include 'page/404.php';
}
?>