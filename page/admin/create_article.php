<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Article - SoundTable</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #1a1a1a;
            color: #f0f0f0;
            margin: 0;
            padding: 2rem;
        }
        .container {
            max-width: 800px;
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
        textarea { height: 300px; font-family: monospace; }
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
        .help-text { font-size: 0.85rem; color: #aaa; margin-top: 0.25rem; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Créer un nouvel article</h1>
        <form id="article-form">
            <div class="form-group">
                <label>Titre</label>
                <input type="text" name="titre" required>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label>Catégories (séparées par des virgules)</label>
                <input type="text" name="categorie" placeholder="Ex: JDR, Fantasy, News">
            </div>
            <div class="form-group">
                <label>Description (Meta)</label>
                <input type="text" name="description" required>
            </div>
            <div class="form-group">
                <label>Contenu (HTML/Markdown)</label>
                <textarea name="contenu" required></textarea>
                <div class="help-text">Vous pouvez utiliser du HTML directement.</div>
            </div>
            <div class="actions">
                <a href="/admin/dashboard" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn">Publier</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('article-form').addEventListener('submit', async (e) => {
            e.preventDefault();
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
                    alert('Article publié avec succès !');
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
