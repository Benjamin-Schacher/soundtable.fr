<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/login');
    exit;
}

// Récupérer le slug depuis l'URL (via route.php)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/admin/edit/([a-zA-Z0-9-]+)$#', $uri, $matches)) {
    $slug = $matches[1];
} else {
    die("Article non spécifié.");
}

$filePath = __DIR__ . '/../../page/article/' . $slug . '.json';

if (!file_exists($filePath)) {
    die("Article introuvable.");
}

$article = json_decode(file_get_contents($filePath), true);
if (!$article) {
    die("Erreur de lecture de l'article.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Article - SoundTable</title>
    <script src="https://cdn.tiny.cloud/1/51pfyg5lw4p4z1r6cz0g0i0d9swi2979bil18hwosqamze9f/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #1a1a1a;
            color: #f0f0f0;
            margin: 0;
            padding: 2rem;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #2a2a2a;
            padding: 2rem;
            border-radius: 8px;
        }
        h1 { margin-top: 0; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        input, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #444;
            border-radius: 4px;
            background-color: #333;
            color: #fff;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
        }
        .btn:hover { background-color: #0056b3; }
        .btn-secondary { background-color: #6c757d; }
        .btn-secondary:hover { background-color: #5a6268; }
    </style>
    <script>
        tinymce.init({
            selector: '#contenu',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            skin: 'oxide-dark',
            content_css: 'dark',
            height: 500
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>Modifier l'article : <?php echo htmlspecialchars($article['titre']); ?></h1>
        <form id="article-form">
            <input type="hidden" name="original_slug" value="<?php echo htmlspecialchars($slug); ?>">
            <input type="hidden" name="is_edit" value="1">
            
            <div class="form-group">
                <label>Titre</label>
                <input type="text" name="titre" value="<?php echo htmlspecialchars($article['titre']); ?>" required>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" value="<?php echo htmlspecialchars($article['date']); ?>" required>
            </div>
            <div class="form-group">
                <label>Catégories (séparées par des virgules)</label>
                <input type="text" name="categorie" value="<?php echo htmlspecialchars(implode(', ', $article['categorie'] ?? [])); ?>">
            </div>
            <div class="form-group">
                <label>Description (Meta)</label>
                <input type="text" name="description" value="<?php echo htmlspecialchars($article['description'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Contenu</label>
                <textarea id="contenu" name="contenu"><?php echo htmlspecialchars($article['contenu']); ?></textarea>
            </div>
            <div class="actions">
                <a href="/admin/dashboard" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn">Enregistrer les modifications</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('article-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Trigger save for TinyMCE
            tinymce.triggerSave();

            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            try {
                const response = await fetch('/api/admin/save-article', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Article modifié avec succès !');
                    window.location.href = '/admin/dashboard';
                } else {
                    alert('Erreur : ' + (result.error || 'Inconnue'));
                }
            } catch (err) {
                alert('Erreur réseau');
            }
        });
    </script>
</body>
</html>
