// app.js

// Fonction pour gérer les cookies (liste noire)
function getBlacklist() {
    const cookies = document.cookie.split(';').find(cookie => cookie.trim().startsWith('blacklist='));
    return cookies ? JSON.parse(decodeURIComponent(cookies.split('=')[1])) : [];
}

function setBlacklist(blacklist) {
    document.cookie = `blacklist=${encodeURIComponent(JSON.stringify(blacklist))}; path=/; max-age=31536000`;
}

// Fonction pour récupérer le nom de la page (soundtable)
function getCurrentPage() {
    let currentPage = window.location.pathname || 'unknown';
    currentPage = currentPage.replace(/^\/|(\.html|\.php)$/g, '') || 'index';
    return currentPage;
}

// Fonction pour récupérer une idée via l'API (GET maintenant)
async function fetchIdea(length) {
    const blacklist = getBlacklist();
    let soundtable = getCurrentPage();
    console.log('Page détectée (soundtable) :', soundtable);

    const buildUrl = (soundtableParam) => {
        const params = new URLSearchParams({ soundtable: soundtableParam });
        if (length) params.append('length', length);
        const blacklist = getBlacklist();
        if (blacklist.length) params.append('blacklist', blacklist.join(','));
        return `https://soundtable.fr/api/ideabox.php?${params.toString()}`;
    };

    const fetchWithSoundtable = async (table) => {
        try {
            const response = await fetch(buildUrl(table));
            console.log(`Requête GET (${table}) → Statut :`, response.status);
            const text = await response.text();
            console.log(`Réponse brute (${table}) :`, text);

            if (!text) throw new Error('Réponse vide');

            const data = JSON.parse(text);
            return data.idea || null;
        } catch (e) {
            console.error(`Erreur lors de la récupération pour soundtable "${table}" :`, e);
            return null;
        }
    };

    let idea = await fetchWithSoundtable(soundtable);

    if (!idea) {
        console.log('Aucune idée avec soundtable spécifique, essai avec "index"...');
        idea = await fetchWithSoundtable('index');
    }

    if (idea && !blacklist.includes(idea.id)) {
        return idea;
    } else {
        return { id: 0, text: 'Aucune idée disponible ou déjà affichée', author: '' };
    }
}

// Fonction pour soumettre une nouvelle idée
async function submitIdea(text, author, soundtable) {
    try {
        const response = await fetch('https://soundtable.fr/api/ideabox.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ text, author, soundtable })
        });

        const data = await response.json();
        if (response.ok) {
            alert('Idée ajoutée avec succès !');
        } else {
            alert('Erreur : ' + data.error);
        }
    } catch (error) {
        console.error('Erreur envoi idée :', error);
        alert('Une erreur s’est produite.');
    }
}

// Bouton pour ouvrir le formulaire d’ajout
const addButton = document.createElement('button');
addButton.textContent = '➕ Ajouter une idée';
addButton.style.cssText = `
    position: fixed; bottom: 90px; right: 20px; z-index: 10000;
    background: #d4a017; color: #2c1e1e; border: 2px solid #3c2f2f; border-radius: 5px;
    padding: 10px 15px; font-size: 16px; cursor: pointer;
    font-family: 'MedievalSharp', cursive; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
`;
document.body.appendChild(addButton);

// Formulaire d'ajout
const formPopup = document.createElement('div');
formPopup.className = 'form-popup';
formPopup.style.cssText = `
    display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
    background: url('/asset/parchment-image.webp') no-repeat center; background-size: cover;
    padding: 30px; z-index: 1001; text-align: center; color: #2c1e1e; height: 850px;
    font-family: 'MedievalSharp', cursive; width: 610px;
    align-content: center;
`;

formPopup.innerHTML = `
    <h3 style="text-align: center; font-size: 1.5em; color: #3c2f2f; text-shadow: 1px 1px 2px #d4a017;">Ajouter une idée</h3>
    <textarea id="ideaText" placeholder="Votre idée..." style="width:60%; height:80px; padding: 10px; border: 2px solid #3c2f2f; border-radius: 5px; background: rgba(245, 245, 220, 0.9); font-family: 'MedievalSharp', cursive; font-size: 1em;"></textarea><br><br>
    <input type="text" id="ideaAuthor" placeholder="Votre nom" style="width:60%; padding: 10px; border: 2px solid #3c2f2f; border-radius: 5px; background: rgba(245, 245, 220, 0.9); font-family: 'MedievalSharp', cursive; font-size: 1em;"><br><br>
    <button id="submitIdeaBtn" style="padding:8px 12px; background: #d4a017; color: #2c1e1e; border: 2px solid #3c2f2f; border-radius: 5px; font-family: 'MedievalSharp', cursive; cursor: pointer;">Envoyer</button>
    <button id="cancelIdeaBtn" style="padding:8px 12px; margin-left:10px; background: #3c2f2f; color: #f5f5dc; border: 2px solid #3c2f2f; border-radius: 5px; font-family: 'MedievalSharp', cursive; cursor: pointer;">Annuler</button>
    <button class="close-btn" style="
        position: absolute; top: 207px;
        right: 150px;
        width: 50px;
        height: 40px;
        background: url('../../asset/seal.webp') no-repeat center; background-size: cover;
        border: none; cursor: pointer; color: transparent;"></button>
`;

document.body.appendChild(formPopup);

// Événement ouverture formulaire
addButton.addEventListener('click', () => {
    formPopup.style.display = 'block';
    overlay.style.display = 'block';
});

// Événements formulaire
document.getElementById('submitIdeaBtn').addEventListener('click', async () => {
    const text = document.getElementById('ideaText').value.trim();
    const author = document.getElementById('ideaAuthor').value.trim();
    const soundtable = getCurrentPage();

    if (text.length < 5) return alert("Texte trop court.");
    if (!author) return alert("Nom requis.");

    await submitIdea(text, author, soundtable);
    formPopup.style.display = 'none';
    overlay.style.display = 'none';
});

document.getElementById('cancelIdeaBtn').addEventListener('click', () => {
    formPopup.style.display = 'none';
    overlay.style.display = 'none';
});

formPopup.querySelector('.close-btn').addEventListener('click', () => {
    formPopup.style.display = 'none';
    overlay.style.display = 'none';
});

// Fonction pour signaler une idée
async function reportIdea(ideaId) {
    try {
        const response = await fetch('https://soundtable.fr/api/ideabox.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ideaId, status: 'signaled' })
        });
        const data = await response.json();
        if (response.ok) {
            const blacklist = getBlacklist();
            blacklist.push(ideaId);
            setBlacklist(blacklist);
        } else {
            console.error('Erreur serveur:', data.error);
        }
    } catch (error) {
        console.error('Erreur signalement:', error);
    }
}

// Ajout d'une balise <style> pour l'animation de flottement et le style du popup
const styleElement = document.createElement('style');
styleElement.textContent = `
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
    }

    .idea-container {
        max-height: 35%;
        padding: 0px 100px;
    }
    
    .scrollabel-section {
        max-height: 265px;
        overflow-y: auto;
        padding: 0px 10px;
    }

     /* Personnalisation de la barre de défilement pour WebKit (Chrome, Safari) */
    .idea-container::-webkit-scrollbar {
        width: 8px; /* Largeur de la barre */
    }
    .idea-container::-webkit-scrollbar-track {
        background: #f5f5dc; /* Fond de la piste */
        margin-right: -55px; /* Décalage léger vers la gauche (expérimental) */
    }
    .idea-container::-webkit-scrollbar-thumb {
        background: #3c2f2f; /* Couleur de la barre */
        border-radius: 4px;
    }

    /* Personnalisation pour Firefox */
    .idea-container {
        scrollbar-width: thin;
        scrollbar-color: #3c2f2f #f5f5dc;
    }

    @media (max-width: 600px) {

        .idea-popup {
        
            top: 35% !important;
            left: 35% !important;  
            scale: 70%;
        }
    }

    @media (max-width: 400px) {

        .idea-popup {
            top: 24% !important;
            left: 13% !important;
            scale: 60%;
        }
    }

    .new-feature-popup {
        position: fixed;
        bottom: 45px; /* Centré verticalement par rapport à l'icône */
        right: 158px; /* À gauche de l'icône (170px de largeur + un petit espace) */
        background: #f5f5dc; /* Beige clair pour un look simple */
        border: 2px solid #3c2f2f;
        border-radius: 5px;
        padding: 10px 40px 10px 20px;
        z-index: 10001;
        color: #2c1e1e;
        font-family: 'MedievalSharp', cursive;
        font-size: 14px;
        width: 200px;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .new-feature-popup::after {
        content: '';
        position: absolute;
        top: 50%;
        right: -20px; /* Flèche à droite */
        transform: translateY(-50%);
        width: 0;
        height: 0;
        border-top: 10px solid transparent;
        border-bottom: 10px solid transparent;
        border-left: 20px solid #f5f5dc; /* Couleur correspondant au fond du popup */
    }

    .new-feature-close-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        width: 30px;
        height: 30px;
        background: url('../../asset/seal.webp') no-repeat center;
        background-size: cover;
        border: none;
        cursor: pointer;
        color: transparent;
    }
`;
document.head.appendChild(styleElement);

// Création des éléments HTML dynamiquement
document.addEventListener('DOMContentLoaded', () => {
    // Icône cliquable
    const icon = document.createElement('div');
    icon.innerHTML = `<img src="/asset/ideabox.webp" alt="D20 Box Icon" style="width: 100%; height: 100%;">`;
    icon.style.cssText = `
        z-index: 900;
        position: fixed; bottom: 11px; right: 4px;
        cursor: pointer;
        width: 170px; height: 170px; display: flex; align-items: center;
        justify-content: center;
        animation: float 2s ease-in-out infinite;
    `;
    document.body.appendChild(icon);

    // Popup de nouveauté
    const newFeaturePopup = document.createElement('div');
    newFeaturePopup.className = 'new-feature-popup';
    newFeaturePopup.innerHTML = `
        <p style="margin: 0;">Nouveau ! Cliquez ici pour découvrir des idées ou en partager !</p>
        <button class="new-feature-close-btn"></button>
    `;
    document.body.appendChild(newFeaturePopup);

    // Gestion de la fermeture du popup de nouveauté
    newFeaturePopup.querySelector('.new-feature-close-btn').addEventListener('click', () => {
        newFeaturePopup.style.display = 'none';
    });

    // Popup
    const popup = document.createElement('div');
    popup.className = 'idea-popup'
    popup.style.cssText = `
        display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
        background: url('/asset/parchment-image.webp') no-repeat center; background-size: cover;
        padding: 30px; z-index: 100000; text-align: center; color: #2c1e1e; height: 850px;
        font-family: 'MedievalSharp', cursive; width: 610px;
        align-content: center;
    `;
    document.body.appendChild(popup);

    // Contenu de la popup
    const buttonsContainer = document.createElement('div');
    buttonsContainer.style.cssText = `display: flex; gap: 10px; justify-content: center; margin-bottom: 20px; padding: 0px 90px; `;
    const lengths = ['très court', 'court', 'moyen', 'long', 'très long'];
    lengths.forEach(label => {
        const button = document.createElement('button');
        button.textContent = label;
        button.style.cssText = `
            padding: 8px 12px; border: 2px solid #3c2f2f; border-radius: 5px; background: #d4a017;
            color: #2c1e1e; cursor: pointer; font-size: 14px; font-family: 'MedievalSharp', cursive; padding: 8px 12px;
        `;
    
        const lengthValue = label
            .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
            .replace(/\s+/g, '-');
    
        button.addEventListener('click', async () => {
            ideaDisplay.textContent = 'Chargement...';
            authorDisplay.textContent = '';
            reportButton.style.display = 'none';
            const idea = await fetchIdea(lengthValue);
            currentIdeaId = idea.id;
            ideaDisplay.textContent = idea.text;
            authorDisplay.textContent = idea.author ? `– ${idea.author}` : '';
            reportButton.style.display = idea.id ? 'block' : 'none';
        });
    
        buttonsContainer.appendChild(button);
    });

    const ideaDisplay = document.createElement('div');
    ideaDisplay.textContent = 'Cliquez sur un bouton pour générer une idée';
    ideaDisplay.style.cssText = `font-size: 16px; min-height: 50px; color: #3c2f2f;`;
    ideaDisplay.className = 'scrollable-content';
    const ideaContainer = document.createElement('div');
    ideaContainer.className = 'idea-container'; 
    const scrollabelSection = document.createElement('div');
    scrollabelSection.className = 'scrollabel-section';
    const authorDisplay = document.createElement('p');
    authorDisplay.style.cssText = `font-style: italic; color: #555; margin-top: 10px;padding: 0px 100px;`;

    const reportButton = document.createElement('button');
    reportButton.textContent = 'Signaler l\'idée';
    reportButton.style.cssText = `
        display: none; padding: 8px 12px; border: 2px solid #3c2f2f; border-radius: 5px;
        background: #3c2f2f; color: #f5f5dc; cursor: pointer; font-size: 14px;
        font-family: 'MedievalSharp', cursive; padding: 10px 20px; margin: auto;
    `;
    let currentIdeaId = 0;
    reportButton.addEventListener('click', async () => {
        if (currentIdeaId) {
            await reportIdea(currentIdeaId);
            ideaDisplay.textContent = 'Idée signalée. Cliquez sur un bouton pour une nouvelle idée.';
            authorDisplay.textContent = '';
            reportButton.style.display = 'none';
        }
    });

    // Ajout du bouton "Ajouter une idée" dans la popup
    addButton.style.cssText = `
        padding: 8px 12px; border: 2px solid #3c2f2f; border-radius: 5px;
        background: #d4a017; color: #2c1e1e; cursor: pointer; font-size: 14px;
        font-family: 'MedievalSharp', cursive; margin-top: 10px;
    `;
    addButton.addEventListener('click', () => {
        formPopup.style.display = 'block';
        overlay.style.display = 'block';
    });

    // Bouton de fermeture pour la popup
    const closeButton = document.createElement('button');
    closeButton.className = 'close-btn';
    closeButton.style.cssText = `
        position: absolute; top: 207px;
        right: 150px;
        width: 50px;
        height: 40px;
        background: url('../../asset/seal.webp') no-repeat center; background-size: cover;
        border: none; cursor: pointer; color: transparent;
    `;

    // Ajout des éléments à la popup
    popup.appendChild(closeButton);
    popup.appendChild(buttonsContainer);
    popup.appendChild(ideaContainer);
    ideaContainer.appendChild(scrollabelSection);
    scrollabelSection.appendChild(ideaDisplay);
    ideaContainer.appendChild(authorDisplay);
    popup.appendChild(reportButton);
    popup.appendChild(addButton);

    // Overlay pour fond sombre
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); z-index: 99999;
    `;
    document.body.appendChild(overlay);

    // Gestion de l'affichage de la popup
    icon.addEventListener('click', () => {
        popup.style.display = 'block';
        overlay.style.display = 'block';
        newFeaturePopup.style.display = 'none'; // Ferme le popup de nouveauté si on clique sur l'icône
    });

    closeButton.addEventListener('click', () => {
        popup.style.display = 'none';
        overlay.style.display = 'none';
    });

    overlay.addEventListener('click', () => {
        popup.style.display = 'none';
        formPopup.style.display = 'none';
        overlay.style.display = 'none';
    });
});