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
    $chapitreNB = isset($input['chapitreNB']) ? (int)$input['chapitreNB'] : 1;
    
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

    // Générer le slug (URL identifier)
    $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titre)));
    // Ajouter le numéro de chapitre au slug pour éviter les conflits de nom de fichier
    $slug = $baseSlug . '-chapitre-' . $chapitreNB;

    // Statut
    $status = isset($input['status']) && in_array($input['status'], ['published', 'draft']) ? $input['status'] : 'published';

    // Construire le JSON
    $livreData = [
        'titre' => $titre,
        'date' => $date,
        'categorie' => $categories,
        'chapitreNB' => $chapitreNB,
        'description' => $description,
        'contenu' => $contenu,
        'status' => $status
    ];

    $filePath = __DIR__ . '/../../page/livre/' . $slug . '.json';
    
    // Si ce n'est pas une édition, vérifier si le fichier existe déjà
    if (!isset($input['is_edit']) && file_exists($filePath)) {
        http_response_code(409);
        echo json_encode(['error' => 'Un livre/chapitre avec ce titre existe déjà']);
        exit;
    }

    // Si c'est une édition et que le slug a changé, on supprime l'ancien fichier
    if (isset($input['is_edit']) && isset($input['original_slug']) && $input['original_slug'] !== $slug) {
        $oldFilePath = __DIR__ . '/../../page/livre/' . $input['original_slug'] . '.json';
        if (file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }
    }

    if (file_put_contents($filePath, json_encode($livreData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        // Envelopper l'exécution de sitemap_utils.php dans une fonction (si elle existe) ou l'inclure si applicable.
        // A noter: il est peut-être nécessaire de l'adapter pour qu'il trouve les livres. (optionnel par défaut)
        
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
