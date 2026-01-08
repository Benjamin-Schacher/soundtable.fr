<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/webp" href="/asset/favicon2.webp">
    <meta name="msapplication-TileImage" content="/asset/favicon2.webp">
    <link rel="apple-touch-icon" href="/asset/favicon2.webp">
    <title>Soundtable - Soundboard JDR et Outils de Jeux de Société</title>
    <meta name="description" content="Soundtable propose des soundboards gratuits pour vos jeux de rôle (JDR) et des outils pour améliorer vos parties de jeux de société. Plongez dans des ambiances immersives avec nos bruitages et effets sonores adaptés à chaque univers !">
    <meta name="keywords" content="JDR, soundboard gratuit, ambiance jeux de rôle, outils jeux de société, bruitage, RPG, sons immersifs, effets sonores, Chroniques Oubliées, Star Wars, Warhammer">
    <link href="https://fonts.googleapis.com/css2?family=MedievalSharp&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: url('https://www.transparenttextures.com/patterns/old-wall.png');
            font-family: 'MedievalSharp', cursive;
            color: #f5f5dc;
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
            max-width: 100vw;
        }

        .main-wrapper {
            width: 100%;
        }

        /* Navbar latérale */
        #navbarre {
            width: 70px;
            background-color: #b77c17;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            top: 0;
        }

        #sitename {
            margin-top: 15px;
            display: flex;
            flex-direction: column;
            color: white;
            align-items: center;
        }

        #sitename p {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            opacity: 50%;
            font-size: 59px;
            font-weight: 900;
            margin: -15px 0;
        }

        #navbarreimg img {
            width: 100%;
        }

        /* Conteneur principal */
        #container {
            width: calc(100% - 70px);
            margin-left: 70px;
            padding: 20px 0 20px 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Section Intro */
        .intro-section {
            text-align: center;
            margin-bottom: 60px;
        }

        section {
            width: 100%;
        }

        h1 {
            font-size: 3em;
            color: #b08968;
            text-shadow: 1px 1px 5px #000;
        }

        .intro-text {
            font-size: 1.3em;
            color: #d4c7b4;
            margin: 20px 0;
            line-height: 1.5;
            color: black;
        }

        .nav-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .nav-buttons a {
            padding: 12px 24px;
            background-color: #755139;
            color: #f5f5dc;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 1.2em;
        }

        .nav-buttons a:hover {
            background-color: #a0724e;
        }

        .card-categorie {
            font-size: 1em;
            color: #f5e0b7;
            margin: 5px 10px;
            text-shadow: 1px 1px 2px #000;
            font-weight: normal;
            text-transform: capitalize; /* Met la première lettre de chaque mot en majuscule */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap; /* Empêche le retour à la ligne */
        }

        /* Section Articles et Outils */
        .articles-section, .tools-section {
            margin-bottom: 60px;
        }

        .section-title {
            font-size: 2.5em;
            color: #b08968;
            text-shadow: 1px 1px 5px #000;
            text-align: center;
            margin-bottom: 20px;
        }


        .scroll-container #articles-container {
            padding: 20px 0 20px 20px;
            overflow-y: auto;
        }
        #tools-container {
            padding: 20px 0 20px 20px;
            overflow-y: auto;
            display: flex;
            align-content: center;
            justify-content: center;
            -webkit-overflow-scrolling: touch;
            gap: 30px;
            scroll-snap-type: x mandatory;
            box-sizing: border-box;
        }

        .scroll-container {
            display: flex;
            gap: 20px;
            flex-wrap: nowrap;
            -webkit-overflow-scrolling: touch;
            width: 100%;
            margin: 0 auto;
            padding: 20px 0 20px 20px;
            scroll-snap-type: x mandatory;
            box-sizing: border-box;
            padding-left: 0;
            justify-content: flex-start
        }

        /* Centrage si pas de scroll (largeur totale des cartes < largeur du conteneur) */
        .scroll-container:not(:has(*:nth-child(5))), #tools-container:not(:has(*:nth-child(5))) {
            justify-content: center;
            padding-right: 0; /* Remove padding-right if centered */
        }


        .scroll-container::-webkit-scrollbar, #tools-container::-webkit-scrollbar  {
            height: 8px;
        }

        .scroll-container::-webkit-scrollbar-thumb, #tools-container::-webkit-scrollbar-thumb {
            background-color: #555;
            border-radius: 10px;
        }

        .scroll-container::-webkit-scrollbar-track, #tools-container::-webkit-scrollbar-track {
            background-color: #f1f1f1;
        }

        /* Responsive adjustments */
        @media (max-width: 900px) {
            .scroll-container {
                padding: 20px 0 20px 20px;
                padding-right: 15px; /* Space at the end on mobile */
            }

            .scroll-container:not(:has(*:nth-child(5))) {
                justify-content: center;
                padding-right: 0; /* Centrage sur mobile si pas de scroll */
            }

            /* Adjust margin for the first card on mobile */
            .scroll-container .tool-card:first-child {
                margin-left: 350px; /* Reduced space before the first card on mobile */
            }
        }
        @media (max-width: 500px) {
            .scroll-container .tool-card:first-child {
                margin-left: 500px; /* Reduced space before the first card on mobile */
            }
        }


        /* Centrage si pas de scroll (largeur totale des cartes < largeur du conteneur) */
        .scroll-container:not(:has(*:nth-child(5))) {
            justify-content: center;
            padding-left: 0; /* Supprime la marge initiale si centré */
        }

        .scroll-container::-webkit-scrollbar {
            height: 8px;
        }

        .scroll-container::-webkit-scrollbar-thumb {
            background-color: #555;
            border-radius: 10px;
        }

        .scroll-container::-webkit-scrollbar-track {
            background-color: #f1f1f1;
        }

        .article-card, .tool-card {
            scroll-snap-align: start;
            flex: 0 0 auto;
            width: 250px;
            background-color: rgba(0, 0, 0, 0.7);
            border: 2px solid #755139;
            border-radius: 15px;
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.5);
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .article-card img, .tool-card img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-top-left-radius: 13px;
            border-top-right-radius: 13px;
        }

        .card-title {
            font-size: 1.5em;
            color: #f5e0b7;
            margin: 10px 0;
            text-shadow: 1px 1px 3px #000;
        }

        html {
            scroll-behavior: smooth; /* Ajout pour un défilement fluide */
        }

        .card-description {
            font-size: 1em;
            color: #d4c7b4;
            padding: 0 10px;
            flex-grow: 1;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .article-card a, .tool-card a {
            display: block;
            padding: 12px;
            background-color: #755139;
            color: #f5f5dc;
            font-weight: bold;
            text-decoration: none;
            border-bottom-left-radius: 13px;
            border-bottom-right-radius: 13px;
            font-size: 1.2em;
        }

        .article-card a:hover, .tool-card a:hover {
            background-color: #a0724e;
        }

        .langBtn {
            display: flex;
            justify-content: center;
            gap: 5px;
            padding: 5px 10px;
            background-color: #755139;
            border-radius: 5px;
            scale: 83%;
            align-items: center;
            margin-bottom: 15px;
        }

        .langBtn a, .langBtn span {
            color: #f5f5dc;
            font-size: 1em;
            font-weight: bold;
            text-decoration: none;
        }

        .langBtn a:hover {
            color: #e0c9a6;
        }

        /* Responsivité */
        @media (max-width: 900px) {

            #navbtn {
                display: flex;
                align-items: center;    
            }

            .langBtn {
                margin: 0;
            }

            #navbarre {
                width: 100%;
                height: 70px;
                position: fixed;
                top: 0;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }

            #sitename {
                flex-direction: row;
                margin-top: 0;
                margin-left: 15px;
            }

            #sitename p {
                font-size: 20px;
                margin: 0 2px;
            }

            #navbarreimg img {
                width: 50px;
                margin-right: 15px;
            }

            #container {
                margin-left: 0;
                margin-top: 70px;
                width: 100%;
            }

            h1 {
                font-size: 2.5em;
            }

            .intro-text {
                font-size: 1.2em;
                line-height: 1.6;
            }

            .section-title {
                font-size: 2em;
            }

            .card-title {
                font-size: 1.4em;
            }

            .card-description {
                font-size: 1em;
            }

            .article-card a, .tool-card a {
                font-size: 1.1em;
            }

            .langBtn {
                padding: 8px 12px;
            }

            .langBtn a, .langBtn span {
                font-size: 1.1em;
            }

            .scroll-container {
                padding: 20px 0 20px 20px;
                padding-left: 15px; 
            }

            .scroll-container:not(:has(*:nth-child(5))) {
                justify-content: center;
                padding-left: 0; /* Centrage sur mobile si pas de scroll */
            }
        }

        @media (max-width: 600px) {
            #sitename p {
                font-size: 18px;
            }

            #navbarreimg img {
                width: 40px;
            }

            h1 {
                font-size: 2.3em;
            }

            .intro-text {
                font-size: 1.1em;
            }

            .nav-buttons a {
                font-size: 1em;
                padding: 10px 20px;
            }

            .section-title {
                font-size: 1.8em;
            }

            .card-title {
                font-size: 1.3em;
            }

            .card-description {
                font-size: 0.95em;
            }

            .article-card a, .tool-card a {
                font-size: 1em;
                padding: 10px;
            }

            .langBtn {
                padding: 6px 10px;
            }

            .langBtn a, .langBtn span {
                font-size: 1em;
            }
        }
        /* Category Filter Styles */
        .filter-container {
            text-align: center;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .category-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
        }

        .category-checkbox {
            display: none; /* Hide actual checkbox */
        }

        .category-label {
            background-color: #755139;
            color: #f5f5dc;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9em;
            border: 2px solid #755139;
            transition: all 0.3s ease;
            user-select: none;
        }

        .category-label:hover {
            background-color: #a0724e;
            border-color: #a0724e;
        }

        .category-checkbox:checked + .category-label {
            background-color: #f5e0b7;
            color: #755139;
            border-color: #755139;
            font-weight: bold;
        }

        #search-name {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #755139;
            width: 250px;
            background: rgba(0, 0, 0, 0.5);
            color: #f5f5dc;
        }
        
        #search-name::placeholder {
            color: #d4c7b4;
        }
    </style>
    <script>
        $(document).ready(function () {
            //Module de popup
            function createPopUp(message) {
                const face = [
                    'https://media.moddb.com/images/mods/1/28/27126/anigif.5.gif', // Zerg animation
                    'https://media.tenor.com/9l3uOtgRho8AAAAM/archon-starcraft2.gif', // Archon Protoss
                    'https://media.tenor.com/Xk4HKwVJhUwAAAAM/marine-starcraft.gif' // Marine Terran
                ];
            
                // Sélection aléatoire d'un GIF
                const randomFace = face[Math.floor(Math.random() * face.length)];
            
                // Création du HTML de la popup
                const popupHTML = `
                    <div style="z-index: 10000; height: 120px; display: flex; position: fixed; top: 10px; right: 10px; width: 340px; padding: 15px; background: linear-gradient(135deg,rgb(23, 160, 206), #5a6266); color: #66ccff; border: 3px solid #66ccff; border-radius: 0; box-shadow: 0 0 20px rgba(255, 204, 0, 0.4), inset 0 0 10px rgba(102, 204, 255, 0.2); font-family: 'Arial', sans-serif; overflow: hidden; clip-path: polygon(0 15%, 5% 0, 85% 0, 100% 20%, 95% 100%, 80% 95%, 15% 100%, 0 85%);">
                        <!-- Bouton de fermeture -->
                        <div style="z-index: 10000; cursor: pointer; margin-right: 10px; color: #ff3333; font-weight: bold; text-shadow: 0 0 5px #ff3333; font-size: 18px; line-height: 20px;" onclick="this.parentElement.remove();" aria-label="Bouton pour fermer la popup">X</div>
                        <div style="flex: 1; display: flex; gap: 15px; position: relative;">
                            <!-- Zone de texte -->
                            <div style="height: 100px; width: 180px; overflow-y: auto; margin: 0; direction: rtl; padding: 5px 5px 5px 10px; background: rgba(40, 50, 60, 0.8); border: 1px solid #66ccff; border-radius: 0; box-shadow: inset 0 0 8px rgba(102, 204, 255, 0.3); position: relative; clip-path: polygon(0 10%, 5% 0, 90% 0, 95% 15%, 100% 90%, 90% 100%, 10% 95%, 0 85%);">
                                <p style="margin: 0; word-wrap: break-word; width: 100%; direction: ltr; text-shadow: 0 0 3px #66ccff; font-size: 14px; line-height: 16px;">
                                    ${message}
                                </p>
                            </div>
                            <!-- GIF -->
                            <img src="${randomFace}" alt="GIF illustration" style="width: 110px; height: 110px; object-fit: cover; border: 2px solid rgb(248 156 255); border-radius: 0; box-shadow: 0 0 15px rgba(204, 51, 255, 0.5); background: rgba(40, 50, 60, 0.8); clip-path: polygon(0 5%, 10% 0, 90% 0, 100% 10%, 95% 100%, 85% 95%, 5% 100%, 0 90%);">
                        </div>
                        <!-- Éléments décoratifs externes -->
                        <div style="position: absolute; top: -3px; left: -3px; width: 30px; height: 30px; border-top: 3px solid #66ccff; border-left: 3px solid #66ccff;"></div>
                        <div style="position: absolute; bottom: -3px; right: -3px; width: 30px; height: 30px; border-bottom: 3px solidrgb(193, 251, 255); border-right: 3px solid #66ccff;"></div>
                        <div style="position: absolute; top: 10px; left: 10px; width: 50px; height: 2px; background: #66ccff; opacity: 0.7;"></div>
                        <div style="position: absolute; bottom: 10px; right: 10px; width: 50px; height: 2px; background:rgb(248 156 255); opacity: 0.7;"></div>
                        <!-- Motif biseauté -->
                        <div style="position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 100px; height: 12px; background: #3a4042; clip-path: polygon(0 0, 15% 100%, 85% 100%, 100% 0);"></div>
                    </div>
                `;
            
                // Ajout des styles pour la scrollbar
                const style = document.createElement('style');
                style.textContent = `
                    div::-webkit-scrollbar {
                        width: 6px;
                    }
                    div::-webkit-scrollbar-track {
                        background: #3a4042;
                        border-radius: 0;
                        box-shadow: inset 0 0 5px rgba(102, 204, 255, 0.2);
                    }
                    div::-webkit-scrollbar-thumb {
                        background: #66ccff;
                        border-radius: 0;
                        box-shadow: 0 0 5px #66ccff;
                    }
                    div::-webkit-scrollbar-thumb:hover {
                        background: #99ddff;
                    }
                `;
                document.head.appendChild(style);
            
                // Injection de la popup dans le DOM
                document.body.insertAdjacentHTML('beforeend', popupHTML);
            }

            // Fonction pour suivre une page
            function trackPageView(pageName) {
                $.ajax({
                    url: 'https://soundtable.fr/api/armies.php', // Remplacez par l'URL de votre API
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        action: 'track-page-view',
                        pageName: pageName
                    }),
                    success: function(response) {
                        console.log('Réponse du serveur :', response);
                    },
                    error: function(xhr, status, error) {
                        console.error('Erreur AJAX :', status, error);
                    }
                });
            }

            // Récupérer le chemin de l'URL actuelle
            var currentPage = window.location.pathname || 'unknown';
            // Nettoyer le chemin (supprimer le "/" initial et l'extension .html ou .php si présente)
            currentPage = currentPage.replace(/^\/|(\.html|\.php)$/g, '') || 'index'; // Si vide, utiliser "index"
            
            // Suivre la page actuelle au chargement
            trackPageView(currentPage);
        });
    </script>
</head>
<body>
    <div class="main-wrapper">
        <div id="navbarre">
            <div id="sitename">
                <p>S</p><p>O</p><p>U</p><p>N</p><p>D</p><p>T</p><p>A</p><p>B</p><p>L</p><p>E</p>
            </div>
            <div id="navbtn">
                <div class="langBtn">
                    <a aria-label="aller à la version" href="https://soundtable.fr"><b>FR</b></a>
                    <span>/</span>
                    <a aria-label="aller à la version anglaise" href="https://soundtable.fr/en">EN</a>
                </div>
                <div id="navbarreimg">
                    <img src="/asset/favicon.webp" alt="Favicon">
                </div>
            </div>
        </div>

        <div id="container">
            <!-- Section Introduction -->
            <section class="intro-section">
                <h1>Soundtable - Soundboard et Outils gratuits pour Jeux de Rôle</h1>
                <p class="intro-text">
                    Bienvenue sur Soundtable, votre destination pour des soundboards gratuits et des outils immersifs dédiés aux jeux de rôle (JDR) et jeux de société. Plongez dans des univers uniques avec nos bruitages, effets sonores, et outils pratiques pour enrichir vos parties. Explorez nos sections pour découvrir nos ressources !
                </p>
                <div class="nav-buttons">
                    <a href="#articles">Articles</a>
                    <a href="#tools">Outils</a>
                </div>
            </section>

            <!-- Section Outils -->
            <section id="tools" class="tools-section">
                <h2 class="section-title">Outils pour vos parties</h2>
                <div id="tools-container">
                    <div class="tool-card">
                        <img src="/asset/soundtable.png" alt="Soundboard Chroniques Oubliées">
                        <h3 class="card-title">Chroniques Oubliées</h3>
                        <p class="card-description">Plongez dans l’univers des Chroniques Oubliées avec notre soundboard JDR et ambiance immersive.</p>
                        <a href="/chroniques-oubliees">Explorer</a>
                    </div>
                    <div class="tool-card">
                        <img src="/asset/starwars-preview.png" alt="Soundboard Star Wars">
                        <h3 class="card-title">Star Wars</h3>
                        <p class="card-description">Vivez des aventures galactiques avec des effets sonores immersifs pour l’univers Star Wars.</p>
                        <a href="/starwars">Explorer</a>
                    </div>
                    <div class="tool-card">
                        <img src="/asset/image-warhammer.png" alt="Outils Warhammer">
                        <h3 class="card-title">Outils Warhammer</h3>
                        <p class="card-description">Préparez vos batailles avec nos outils pour Warhammer et jeux de figurines.</p>
                        <a href="/warhammeurArmise">Explorer</a>
                    </div>
                </div>
            </section>

            <!-- Section Articles -->
            <section id="articles" class="articles-section">
                <h2 class="section-title">Articles à lire</h2>
                <div class="scroll-container">
                <?php
                $articleDir = __DIR__ . '/../article/'; 
                $defaultImage = '/asset/default-article.png';
                $articlesData = [];
                $allCategories = [];

                if (is_dir($articleDir)) {
                    $files = glob($articleDir . '*.json');
                    echo "<!-- DEBUG: Article Directory: " . htmlspecialchars($articleDir) . " -->";
                    echo "<!-- DEBUG: Files found: " . count($files) . " -->";
                    
                    foreach ($files as $file) {
                        $rawContent = file_get_contents($file);
                        $content = json_decode($rawContent, true);
                        
                        if ($content === null) {
                            echo "<!-- DEBUG: JSON Decode Error for " . basename($file) . ": " . json_last_error_msg() . " -->";
                            continue;
                        }

                        if (isset($content['titre'])) {
                            $slug = basename($file, '.json');
                            $description = isset($content['description']) ? $content['description'] : '';
                            if (empty($description) && isset($content['contenu'])) {
                                $description = substr(strip_tags(str_replace(['#', '**', '*'], '', $content['contenu'])), 0, 80) . '...';
                            }
                            
                            $categories = isset($content['categorie']) && is_array($content['categorie']) ? $content['categorie'] : ['Non classé'];
                            
                            // Collect categories
                            foreach ($categories as $cat) {
                                $cat = trim($cat);
                                if (!in_array($cat, $allCategories)) {
                                    $allCategories[] = $cat;
                                }
                            }

                            // Force UTF-8 encoding for all strings to ensure json_encode works
                            $articlesData[] = [
                                'title' => mb_convert_encoding($content['titre'], 'UTF-8', 'auto'),
                                'url' => '/article/' . $slug,
                                'image' => $content['image'] ?? $defaultImage,
                                'description' => mb_convert_encoding($description, 'UTF-8', 'auto'),
                                'date' => $content['date'] ?? '0000-00-00',
                                'categories' => array_map(function($c) { return mb_convert_encoding($c, 'UTF-8', 'auto'); }, $categories)
                            ];
                        } else {
                             echo "<!-- DEBUG: Missing 'titre' in " . basename($file) . " -->";
                        }
                    }
                } else {
                    echo "<!-- DEBUG: Directory not found: " . htmlspecialchars($articleDir) . " -->";
                }
                
                // Trier par date décroissante (plus récent en premier)
                usort($articlesData, function($a, $b) {
                    return strtotime($b['date']) - strtotime($a['date']);
                });
                sort($allCategories); // Sort categories alphabetically
                ?>

                <div class="filter-container">
                    <input type="text" id="search-name" placeholder="Rechercher par nom...">
                    <div class="category-filters" id="category-filters">
                        <?php foreach ($allCategories as $category): ?>
                            <label>
                                <input type="checkbox" class="category-checkbox" value="<?php echo htmlspecialchars($category); ?>">
                                <span class="category-label"><?php echo htmlspecialchars($category); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="scroll-container" id="articles-container">
                    <!-- Les articles seront injectés ici par JS -->
                </div>

                <script>
                    const articles = <?php 
                        $json = json_encode($articlesData);
                        if ($json === false) {
                            echo '[]';
                            echo '; console.error("JSON Encode Error: ' . json_last_error_msg() . '");';
                        } else {
                            echo $json;
                        }
                    ?>;
                    const container = document.getElementById('articles-container');
                    const searchNameInput = document.getElementById('search-name');
                    const categoryCheckboxes = document.querySelectorAll('.category-checkbox');

                    function renderArticles(filteredArticles) {
                        container.innerHTML = '';
                        // Limiter à 10 articles si aucune recherche n'est active
                        const isFiltering = searchNameInput.value.trim() !== '' || Array.from(categoryCheckboxes).some(cb => cb.checked);
                        const displayList = isFiltering ? filteredArticles : filteredArticles.slice(0, 10);

                        if (displayList.length === 0) {
                            container.innerHTML = '<p style="text-align:center; width:100%;">Aucun article trouvé.</p>';
                            return;
                        }

                        displayList.forEach(article => {
                            const card = document.createElement('div');
                            card.className = 'article-card';
                            
                            const categoriesStr = article.categories.join(', ');

                            card.innerHTML = `
                                <img src="${article.image}" alt="${article.title}">
                                <h3 class="card-title">${article.title}</h3>
                                <h4 class="card-categorie">${categoriesStr}</h4>
                                <p class="card-description">${article.description}</p>
                                <a href="${article.url}">Lire</a>
                            `;
                            container.appendChild(card);
                        });
                    }

                    function filterArticles() {
                        const nameTerm = searchNameInput.value.toLowerCase();
                        
                        // Get selected categories
                        const selectedCategories = Array.from(categoryCheckboxes)
                            .filter(cb => cb.checked)
                            .map(cb => cb.value.toLowerCase());

                        const filtered = articles.filter(article => {
                            const matchName = article.title.toLowerCase().includes(nameTerm);
                            
                            // Category match: 
                            // If no category selected, return true (ignore category filter)
                            // If categories selected, article must have AT LEAST ONE of the selected categories
                            let matchCategory = true;
                            if (selectedCategories.length > 0) {
                                matchCategory = article.categories.some(cat => selectedCategories.includes(cat.toLowerCase()));
                            }

                            return matchName && matchCategory;
                        });

                        renderArticles(filtered);
                    }

                    searchNameInput.addEventListener('input', filterArticles);
                    categoryCheckboxes.forEach(cb => cb.addEventListener('change', filterArticles));

                    // Initial render
                    renderArticles(articles);
                </script>
            </section>
        </div>
    </div>

</body>
</html>
