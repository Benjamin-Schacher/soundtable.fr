<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/login');
    exit;
}

// Lister les articles
$articleDir = __DIR__ . '/../../article/';
$articles = [];
if (is_dir($articleDir)) {
    $files = glob($articleDir . '*.json');
    foreach ($files as $file) {
        $content = json_decode(file_get_contents($file), true);
        if ($content) {
            $articles[] = [
                'filename' => basename($file),
                'title' => $content['titre'] ?? 'Sans titre',
                'date' => $content['date'] ?? 'Date inconnue'
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SoundTable</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #1a1a1a;
            color: #f0f0f0;
            margin: 0;
            padding: 2rem;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        h1 { margin: 0; }
        .btn {
            padding: 0.5rem 1rem;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        .btn:hover { background-color: #0056b3; }
        .btn-danger { background-color: #dc3545; }
        .btn-danger:hover { background-color: #c82333; }
        
        .article-list {
            background-color: #2a2a2a;
            border-radius: 8px;
            padding: 1rem;
        }
        .article-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #444;
        }
        .article-item:last-child { border-bottom: none; }
        .article-title { font-weight: bold; }
        .article-date { color: #888; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tableau de bord</h1>
        <div>
            <a href="/admin/stats" class="btn" style="background-color: #28a745; margin-right: 10px;">Statistiques</a>
            <a href="/admin/create" class="btn">Nouvel Article</a>
            <a href="/api/admin/logout" class="btn btn-danger">Déconnexion</a>
        </div>
    </div>

    <div class="article-list">
        <h2>Articles existants</h2>
        <?php if (empty($articles)): ?>
            <p>Aucun article trouvé.</p>
        <?php else: ?>
            <?php foreach ($articles as $article): ?>
                <div class="article-item">
                    <div>
                        <div class="article-title"><?php echo htmlspecialchars($article['title']); ?></div>
                        <div class="article-date"><?php echo htmlspecialchars($article['date']); ?></div>
                    </div>
                    <a href="/article/<?php echo str_replace('.json', '', $article['filename']); ?>" target="_blank" class="btn" style="background-color: #444;">Voir</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
