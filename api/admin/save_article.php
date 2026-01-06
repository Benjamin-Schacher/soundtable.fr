<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $titre = trim($input['titre'] ?? '');
    $date = $input['date'] ?? date('Y-m-d');
    $description = trim($input['description'] ?? '');
    $contenu = $input['contenu'] ?? '';
    
    // Traitement des catégories
    $categoriesInput = $input['categorie'] ?? '';
    $categories = array_map('trim', explode(',', $categoriesInput));
    $categories = array_filter($categories); // Supprimer les entrées vides
    if (empty($categories)) {
        $categories = ['Non classé'];
    }

    if (empty($titre) || empty($contenu)) {
        http_response_code(400);
        echo json_encode(['error' => 'Titre et contenu requis']);
        exit;
    }

    // Générer le slug
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titre)));
    
    // Construire le JSON
    $articleData = [
        'titre' => $titre,
        'date' => $date,
        'categorie' => $categories,
        'description' => $description,
        'contenu' => $contenu
    ];

    $filePath = __DIR__ . '/../../page/article/' . $slug . '.json';
    
    if (file_exists($filePath)) {
        http_response_code(409);
        echo json_encode(['error' => 'Un article avec ce titre existe déjà']);
        exit;
    }

    if (file_put_contents($filePath, json_encode($articleData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        // Générer le sitemap automatiquement
        require_once 'sitemap_utils.php';
        generateSitemap();
        
        echo json_encode(['success' => true, 'slug' => $slug]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de l\'écriture du fichier']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
