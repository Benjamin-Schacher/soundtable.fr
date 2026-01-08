<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/login');
    exit;
}

// Lister les articles
$articleDir = __DIR__ . '/../article/';
$articles = [];
if (is_dir($articleDir)) {
    $files = glob($articleDir . '*.json');
    foreach ($files as $file) {
        $content = json_decode(file_get_contents($file), true);
        if ($content) {
            $articles[] = [
                'filename' => basename($file),
                'title' => $content['titre'] ?? 'Sans titre',
                'date' => $content['date'] ?? 'Date inconnue',
                'categories' => $content['categorie'] ?? []
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
            <a href="/admin/create" class="btn">Nouvel Article</a>
            <a href="/api/admin/logout" class="btn btn-danger">Déconnexion</a>
        </div>
    </div>

    <?php
    // Connexion à la base de données
    require_once __DIR__ . '/../../api/config.php';

    // Récupérer les vues
    $viewsByPage = [];
    $totalViews = 0;
    try {
        $stmt = $pdo->query("SELECT page_name, SUM(unique_views) as total_views FROM page_views GROUP BY page_name");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $viewsByPage[$row['page_name']] = $row['total_views'];
            $totalViews += $row['total_views'];
        }
    } catch (PDOException $e) {
        // En cas d'erreur (ex: table inexistante), on continue sans les vues
        error_log("Erreur récupération vues: " . $e->getMessage());
    }

    // Calcul des statistiques
    $totalArticles = count($articles);
    $totalComments = 0;
    $categoriesCount = [];

    // Fonction récursive pour compter les commentaires
    function countCommentsRecursive($comments) {
        $count = 0;
        foreach ($comments as $comment) {
            $count++;
            if (!empty($comment['replies'])) {
                $count += countCommentsRecursive($comment['replies']);
            }
        }
        return $count;
    }

    // Parcourir les articles pour les stats
    foreach ($articles as &$article) { // Passage par référence pour ajouter les vues
        // Catégories
        if (isset($article['categories']) && is_array($article['categories'])) {
            foreach ($article['categories'] as $cat) {
                if (!isset($categoriesCount[$cat])) {
                    $categoriesCount[$cat] = 0;
                }
                $categoriesCount[$cat]++;
            }
        }

        // Commentaires
        $articleId = str_replace('.json', '', $article['filename']);
        $commentFile = __DIR__ . '/../comments/' . $articleId . '_comments.json';
        if (file_exists($commentFile)) {
            $commentsData = json_decode(file_get_contents($commentFile), true);
            if ($commentsData) {
                $totalComments += countCommentsRecursive($commentsData);
            }
        }

        // Vues
        // On vérifie plusieurs formats possibles pour le nom de la page
        $possibleKeys = [
            $articleId,                     // ex: taxandria
            '/article/' . $articleId,       // ex: /article/taxandria
            'article/' . $articleId         // ex: article/taxandria (format probable du JS)
        ];
        
        $views = 0;
        foreach ($possibleKeys as $key) {
            if (isset($viewsByPage[$key])) {
                $views += $viewsByPage[$key];
            }
        }
        $article['views'] = $views;
    }
    unset($article); // Rompre la référence
    ?>

    <div class="stats-container" style="display: flex; gap: 20px; margin-bottom: 2rem; flex-wrap: wrap;">
        <div class="stat-card" style="background: #2a2a2a; padding: 1.5rem; border-radius: 8px; flex: 1; min-width: 200px;">
            <h3 style="margin: 0 0 0.5rem 0; color: #888;">Total Articles</h3>
            <div style="font-size: 2rem; font-weight: bold;"><?php echo $totalArticles; ?></div>
        </div>
        <div class="stat-card" style="background: #2a2a2a; padding: 1.5rem; border-radius: 8px; flex: 1; min-width: 200px;">
            <h3 style="margin: 0 0 0.5rem 0; color: #888;">Total Commentaires</h3>
            <div style="font-size: 2rem; font-weight: bold;"><?php echo $totalComments; ?></div>
        </div>
        <div class="stat-card" style="background: #2a2a2a; padding: 1.5rem; border-radius: 8px; flex: 1; min-width: 200px;">
            <h3 style="margin: 0 0 0.5rem 0; color: #888;">Total Vues</h3>
            <div style="font-size: 2rem; font-weight: bold;"><?php echo $totalViews; ?></div>
        </div>
        <div class="stat-card" style="background: #2a2a2a; padding: 1.5rem; border-radius: 8px; flex: 1; min-width: 200px;">
            <h3 style="margin: 0 0 0.5rem 0; color: #888;">Catégories</h3>
            <ul style="margin: 0; padding-left: 1.2rem;">
                <?php foreach ($categoriesCount as $cat => $count): ?>
                    <li><?php echo htmlspecialchars($cat); ?>: <strong><?php echo $count; ?></strong></li>
                <?php endforeach; ?>
                <?php if (empty($categoriesCount)): ?>
                    <li style="color: #666;">Aucune catégorie</li>
                <?php endif; ?>
            </ul>
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
                        <div class="article-date">
                            <?php echo htmlspecialchars($article['date']); ?> • 
                            <span style="color: #28a745;"><?php echo $article['views']; ?> vues</span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="/admin/edit/<?php echo str_replace('.json', '', $article['filename']); ?>" class="btn" style="background-color: #ffc107; color: #000;">Modifier</a>
                        <a href="/article/<?php echo str_replace('.json', '', $article['filename']); ?>" target="_blank" class="btn" style="background-color: #444;">Voir</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
