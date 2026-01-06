<?php

function generateSitemap() {
    $baseUrl = 'https://soundtable.fr';
    $sitemapFile = __DIR__ . '/../../sitemap.xml';
    $articleDir = __DIR__ . '/../../page/article/';

    $urls = [];

    // Routes statiques (basées sur route.php et sitemap.xml existant)
    $staticRoutes = [
        '/' => ['priority' => '1', 'changefreq' => 'monthly'],
        '/en' => ['priority' => '1', 'changefreq' => 'monthly'],
        '/chroniques-oubliees' => ['priority' => '0.8', 'changefreq' => 'monthly'],
        '/starwars' => ['priority' => '0.8', 'changefreq' => 'monthly'],
        '/warhammeurArmise' => ['priority' => '0.8', 'changefreq' => 'monthly'],
        '/en/warhammeurArmise' => ['priority' => '0.8', 'changefreq' => 'monthly'],
        '/en/MTG-life-counter' => ['priority' => '0.8', 'changefreq' => 'monthly'],
        '/MTG-compteur-de-point-de-vie' => ['priority' => '0.8', 'changefreq' => 'monthly'],
    ];

    foreach ($staticRoutes as $path => $meta) {
        $urls[] = [
            'loc' => $baseUrl . $path,
            'lastmod' => date('Y-m-d'), // On met à jour la date à chaque génération pour simplifier
            'changefreq' => $meta['changefreq'],
            'priority' => $meta['priority']
        ];
    }

    // Articles dynamiques
    if (is_dir($articleDir)) {
        $files = glob($articleDir . '*.json');
        foreach ($files as $file) {
            $content = json_decode(file_get_contents($file), true);
            if ($content) {
                $slug = basename($file, '.json');
                $date = $content['date'] ?? date('Y-m-d');
                
                $urls[] = [
                    'loc' => $baseUrl . '/article/' . $slug,
                    'lastmod' => $date,
                    'changefreq' => 'weekly',
                    'priority' => '0.8'
                ];
            }
        }
    }

    // Génération du XML
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($urls as $url) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
        $xml .= "    <lastmod>" . $url['lastmod'] . "</lastmod>\n";
        $xml .= "    <changefreq>" . $url['changefreq'] . "</changefreq>\n";
        $xml .= "    <priority>" . $url['priority'] . "</priority>\n";
        $xml .= "  </url>\n";
    }

    $xml .= '</urlset>';

    file_put_contents($sitemapFile, $xml);
}
?>
