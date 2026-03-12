<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


// Vérifier si $jsonFile est défini
if (!isset($jsonFile)) {
    die("Erreur : \$jsonFile non défini. Vérifiez route.php.");
}

// Convertir $jsonFile en chemin absolu
$jsonFileAbsolute = __DIR__ . '/' . ltrim($jsonFile, '/');

// Vérifier si le fichier JSON existe
if (!file_exists($jsonFileAbsolute)) {
    die("Erreur : Fichier JSON $jsonFileAbsolute introuvable.");
}

// Extraire l'ID de l'article à partir du chemin du fichier JSON
$articleId = basename($jsonFile, '.json'); // Ex. "taxandria"
$livreChapitre = isset($_GET['chapitre']) ? $_GET['chapitre'] : null;

// Définir le chemin des commentaires
$commentDir = __DIR__ . '/page/comments/';
$commentFile = $commentDir . $articleId . '_comments.json';

// Charger les commentaires (s'ils existent)
$comments = [];
if (file_exists($commentFile)) {
    $commentContent = file_get_contents($commentFile);
    $comments = json_decode($commentContent, true) ?: [];
}

// Gérer les requêtes de vote via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'vote') {
    $commentId = isset($_POST['comment_id']) ? $_POST['comment_id'] : null;
    $voteType = isset($_POST['vote_type']) ? $_POST['vote_type'] : null;
    $userIp = $_SERVER['REMOTE_ADDR'];

    if (!$commentId || !in_array($voteType, ['upvote', 'downvote'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Requête invalide']);
        exit;
    }

    function updateVotes(&$comments, $commentId, $voteType, $userIp) {
        foreach ($comments as &$comment) {
            if ($comment['id'] === $commentId) {
                if (!isset($comment['voters'])) {
                    $comment['voters'] = [];
                }
                $previousVote = isset($comment['voters'][$userIp]) ? $comment['voters'][$userIp] : null;
                
                // Si l'utilisateur a déjà voté, ajuster les compteurs
                if ($previousVote) {
                    if ($previousVote === 'upvote') {
                        $comment['upvotes']--;
                    } else {
                        $comment['downvotes']--;
                    }
                }
                
                // Si le nouveau vote est différent, appliquer le vote
                if (!$previousVote || $previousVote !== $voteType) {
                    $comment['voters'][$userIp] = $voteType;
                    if ($voteType === 'upvote') {
                        $comment['upvotes']++;
                    } else {
                        $comment['downvotes']++;
                    }
                } else {
                    // Si l'utilisateur clique sur le même type de vote, on annule
                    unset($comment['voters'][$userIp]);
                }
                
                return [
                    'success' => true,
                    'comment' => $comment,
                    'vote_type' => isset($comment['voters'][$userIp]) ? $voteType : null
                ];
            }
            if (!empty($comment['replies'])) {
                $result = updateVotes($comment['replies'], $commentId, $voteType, $userIp);
                if (isset($result['success'])) {
                    return $result;
                }
            }
        }
        return ['error' => 'Commentaire non trouvé'];
    }

    $result = updateVotes($comments, $commentId, $voteType, $userIp);
    if ($result['success']) {
        file_put_contents($commentFile, json_encode($comments, JSON_PRETTY_PRINT));
        echo json_encode([
            'success' => true,
            'upvotes' => $result['comment']['upvotes'],
            'downvotes' => $result['comment']['downvotes'],
            'vote_type' => $result['vote_type']
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => $result['error']]);
    }
    exit;
}

// Gérer la soumission de commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['message'])) {
    $name = trim($_POST['name']);
    $message = trim($_POST['message']);
    $parentId = isset($_POST['parent_id']) ? $_POST['parent_id'] : null;
    
    // Validation
    if (empty($name) || empty($message)) {
        die("Erreur : Le nom et le message sont requis.");
    } elseif (strlen($name) > 50 || strlen($message) > 1000) {
        die("Erreur : Le nom ou le message est trop long.");
    } else {
        // Nettoyer les entrées pour éviter les injections
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        
        // Créer le commentaire
        $newComment = [
            'id' => uniqid(),
            'name' => $name,
            'message' => $message,
            'date' => date('c'), // Format ISO 8601
            'upvotes' => 0,
            'downvotes' => 0,
            'voters' => [], // Initialiser le champ 'voters'
            'replies' => [] // Pour les réponses futures
        ];
        
        // Ajouter le commentaire (racine ou réponse)
        if ($parentId) {
            // Trouver le commentaire parent et ajouter la réponse
            function addReply(&$comments, $parentId, $newComment) {
                foreach ($comments as &$comment) {
                    if ($comment['id'] === $parentId) {
                        $comment['replies'][] = $newComment;
                        return true;
                    }
                    if (!empty($comment['replies'])) {
                        if (addReply($comment['replies'], $parentId, $newComment)) {
                            return true;
                        }
                    }
                }
                return false;
            }
            addReply($comments, $parentId, $newComment);
        } else {
            $comments[] = $newComment;
        }
        
        // Sauvegarder
        if (!is_dir($commentDir)) {
            mkdir($commentDir, 0755, true);
        }
        file_put_contents($commentFile, json_encode($comments, JSON_PRETTY_PRINT));
        
        // Rediriger avec ancre vers la section commentaires
        header('Location: /article/' . $articleId . '#commentaires');
        exit;
    }
}

// Charger le contenu de l'article
$content = file_get_contents($jsonFileAbsolute);
$article = json_decode($content, true);

// Vérification du statut Brouillon
if (isset($article['status']) && $article['status'] === 'draft') {
    session_start();
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        // Rediriger vers 404 ou afficher une erreur 403
        http_response_code(404);
        include 'page/404.php'; // Assurez-vous que ce chemin est correct par rapport à handle_article.php ou route.php
        exit;
    }
}

// Vérifier si le JSON est valide
if (json_last_error() === JSON_ERROR_NONE) {
    // Vérifier si les clés existent
    if (isset($article['titre'], $article['date'], $article['contenu'])) {
        // Convertir le contenu Markdown en HTML
        $articleContentHtml = $article['contenu'];

        // Générer la section des commentaires
        $commentSection = '<div class="comments-section" id="commentaires"><h2 class="section-title">Commentaires</h2>';

        if (empty($comments)) {
            $commentSection .= '<p style="color: #6b5b5b; text-align: center;">Aucun commentaire pour le moment. Soyez le premier !</p>';
        } else {
            // Fonction pour afficher les commentaires récursivement
            function renderComments($comments, $userIp, $depth = 0) {
                $output = '';
                foreach ($comments as $comment) {
                    $indent = $depth * 20; // Indentation de 20px par niveau
                    $hasReplies = !empty($comment['replies']);
                    $output .= '<div class="comment" style="margin-left: ' . $indent . 'px;">';
                    $output .= '<p>';
                    if ($hasReplies) {
                        $output .= '<span class="toggle-replies" style="cursor: pointer; margin-right: 5px;">[+]</span>';
                    }
                    $output .= '<strong style="color: #3c2f2f;">' . htmlspecialchars($comment['name']) . '</strong> ';
                    $output .= '<span style="color: #6b5b5b; font-size: 0.9em;">(' . date('d/m/Y H:i', strtotime($comment['date'])) . ')</span></p>';
                    $output .= '<p style="color: #2c2c2c;">' . nl2br(htmlspecialchars($comment['message'])) . '</p>';
                    $output .= '<div class="comment-actions">';
                    
                    // Vérifier si l'utilisateur a voté pour ce commentaire
                    $voteClassUpvote = '';
                    $voteClassDownvote = '';
                    if (isset($comment['voters']) && isset($comment['voters'][$userIp])) {
                        if ($comment['voters'][$userIp] === 'upvote') {
                            $voteClassUpvote = ' active';
                        } elseif ($comment['voters'][$userIp] === 'downvote') {
                            $voteClassDownvote = ' active';
                        }
                    }
                    
                    $output .= '<button class="vote-btn upvote-btn' . $voteClassUpvote . '" data-id="' . htmlspecialchars($comment['id']) . '" data-type="upvote">👍 <span class="vote-count">' . $comment['upvotes'] . '</span></button>';
                    $output .= '<button class="vote-btn downvote-btn' . $voteClassDownvote . '" data-id="' . htmlspecialchars($comment['id']) . '" data-type="downvote">👎 <span class="vote-count">' . $comment['downvotes'] . '</span></button>';
                    $output .= '<button class="reply-btn">Répondre</button>';
                    $output .= '</div>';
                    
                    // Formulaire de réponse (caché par défaut)
                    $output .= '<div class="reply-form" style="display: none; margin-top: 10px;">';
                    $output .= '<form method="POST">';
                    $output .= '<input type="hidden" name="parent_id" value="' . htmlspecialchars($comment['id']) . '">';
                    $output .= '<input type="text" name="name" placeholder="Votre nom" required>';
                    $output .= '<textarea name="message" placeholder="Votre réponse" required></textarea>';
                    $output .= '<button type="submit">Envoyer</button>';
                    $output .= '</form>';
                    $output .= '</div>';
                    
                    // Afficher les réponses récursivement (masquées par défaut)
                    if ($hasReplies) {
                        $output .= '<div class="replies" style="display: none;">';
                        $output .= renderComments($comment['replies'], $userIp, $depth + 1);
                        $output .= '</div>';
                    }
                    $output .= '</div>';
                }
                return $output;
            }
            
            $commentSection .= renderComments($comments, $_SERVER['REMOTE_ADDR']);
        }

        // Ajouter le formulaire pour un nouveau commentaire
        $commentSection .= '<div class="comment-form">';
        $commentSection .= '<h3>Laisser un commentaire</h3>';
        $commentSection .= '<form method="POST">';
        $commentSection .= '<input type="text" name="name" placeholder="Votre nom" required>';
        $commentSection .= '<textarea name="message" placeholder="Votre message" required></textarea>';
        $commentSection .= '<button type="submit">Envoyer</button>';
        $commentSection .= '</form>';
        $commentSection .= '</div>';
        $commentSection .= '</div>';

        $isLivre = strpos(str_replace('\\', '/', $jsonFileAbsolute), '/page/livre/') !== false;

        // Utiliser le chemin correct du template
        $templatePath = $isLivre ? 'page/template/livreTemplate.html' : 'page/template/articleTemplate.html';
        if (file_exists($templatePath)) {
            $template = file_get_contents($templatePath);
            $template = str_replace('{TITRE_ARTICLE}', $article['titre'], $template);
            $template = str_replace('{DATE_PUBLICATION}', htmlspecialchars($article['date']), $template);
            $template = str_replace('{CONTENU_ARTICLE}', $articleContentHtml, $template);
            $template = str_replace('{META_DESCRIPTION}', htmlspecialchars($article['description'] ?? 'Découvrez cet article sur Soundtable !'), $template);
            
            $urlPrefix = $isLivre ? '/livre/' : '/article/';
            $template = str_replace('{TAXANDRIA_URL}', htmlspecialchars($urlPrefix . $articleId), $template);
            $template = str_replace('{COMMENTAIRES}', $commentSection, $template);

            if ($isLivre) {
                // Trouver le chapitre précédent et suivant
                $currentChap = isset($article['chapitreNB']) ? (int)$article['chapitreNB'] : 1;
                $currentLivre = $article['titre'] ?? [];
                $template = str_replace('{NB_CHAPITRE}', $currentChap, $template);
                $prevUrl = '';
                $nextUrl = '';
                
                if (!empty($currentLivre)) {
                    $livreDir = __DIR__ . '/page/livre/';
                    if (is_dir($livreDir)) {
                        $files = glob($livreDir . '*.json');
                        foreach ($files as $f) {
                            $fContent = file_get_contents($f);
                            $fData = json_decode($fContent, true);
                            if ($fData && isset($fData['titre']) && $fData['titre'] === $currentLivre) {
                                $fChap = isset($fData['chapitreNB']) ? (int)$fData['chapitreNB'] : 1;
                                $fSlug = basename($f, '.json');
                                if ($fChap === $currentChap - 1) {
                                    $prevUrl = '/livre/' . $fSlug;
                                }
                                if ($fChap === $currentChap + 1) {
                                    $nextUrl = '/livre/' . $fSlug;
                                }
                            }
                        }
                    }
                }
                
                $btnPrev = $prevUrl ? '<a href="' . $prevUrl . '" class="chapter-btn">← Chapitre précédent</a>' : '<div></div>';
                $btnNext = $nextUrl ? '<a href="' . $nextUrl . '" class="chapter-btn">Chapitre suivant →</a>' : '<div></div>';
                
                $template = str_replace('{BOUTON_PRECEDENT}', $btnPrev, $template);
                $template = str_replace('{BOUTON_SUIVANT}', $btnNext, $template);
            }

            echo $template;
        } else {
            die("Erreur : $templatePath introuvable.");
        }
    } else {
        die("Erreur : Clés manquantes dans le JSON (titre, date, contenu).");
    }
} else {
    die("Erreur : JSON invalide dans $jsonFileAbsolute. Erreur : " . json_last_error_msg());
}
?>