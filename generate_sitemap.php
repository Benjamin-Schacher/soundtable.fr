

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Autoriser l'accès en CLI ou via HTTP avec la bonne IP et URI
if (php_sapi_name() !== 'cli' && ($_SERVER['REQUEST_URI'] !== '/admin/generate-sitemap' || $_SERVER['REMOTE_ADDR'] !== '90.66.36.240')) {
    http_response_code(403);
    exit('Accès interdit');
}

$xml = '<?xml version="1.0" encoding="UTF-8"?>';
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

$staticRoutes = [
    '/' => 'page/acceuil/acceuil.php',
    '/en' => 'page/acceuil/en/acceuilEn.php',
    '/chroniques-oubliees' => 'page/chronique/chroniques-oubliees.php',
    '/starwars' => 'page/starwars/front-page.php',
    '/warhammeurArmise' => 'page/warhammeur/warhammeur.php',
    '/en/warhammeurArmise' => 'page/warhammeur/en/warhammeurEn.php',
    '/en/MTG-life-counter' => 'page/mtg-life-counter/lifeCounter.php',
    '/MTG-compteur-de-point-de-vie' => 'page/mtg-life-counter/mtg.php',
];

foreach ($staticRoutes as $url => $file) {
    $lastmod = file_exists($file) ? date('Y-m-d', filemtime($file)) : '2025-06-03';
    $xml .= '<url>';
    $xml .= '<loc>https://soundtable.fr' . $url . '</loc>';
    $xml .= '<lastmod>' . $lastmod . '</lastmod>';
    $xml .= '<changefreq>monthly</changefreq>';
    $xml .= '<priority>0.8</priority>';
    $xml .= '</url>';
}

$articleDir = 'page/article/';
foreach (glob($articleDir . '*.json') as $file) {
    $article = json_decode(file_get_contents($file), true);
    if (json_last_error() === JSON_ERROR_NONE && isset($article['url'])) {
        $xml .= '<url>';
        $xml .= '<loc>https://soundtable.fr' . $article['url'] . '</loc>';
        $xml .= '<lastmod>' . $article['date'] . '</lastmod>';
        $xml .= '<changefreq>weekly</changefreq>';
        $xml .= '<priority>0.8</priority>';
        $xml .= '</url>';
    }
}

$xml .= '</urlset>';

file_put_contents('sitemap.xml', $xml);

echo 'Sitemap généré avec succès !';
?>