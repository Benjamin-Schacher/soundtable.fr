<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

function verifyPassword($pdo, $username, $password) {
    $stmt = $pdo->prepare("SELECT password FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $user && password_verify($password, $user['password']);
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST': 
        $data = json_decode(file_get_contents('php://input'), true);

        if (isset($data['action']) && $data['action'] === 'track-page-view') {
            if (!isset($data['pageName'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Nom de la page requis']);
                exit;
            }

            $pageName = $data['pageName'];
            $currentDate = date('Y-m-d');
            $cookieName = 'visited_pages';
            $cookieDuration = 24 * 60 * 60; // 24 heures 

            $visitedPages = isset($_COOKIE[$cookieName]) ? json_decode($_COOKIE[$cookieName], true) : [];

            try {
                if (!in_array($pageName, $visitedPages)) {
                    $stmt = $pdo->prepare("
                        INSERT INTO page_views (page_name, view_date, unique_views)
                        VALUES (:page_name, :view_date, 1)
                        ON DUPLICATE KEY UPDATE unique_views = unique_views + 1
                    ");
                    $stmt->execute([
                        'page_name' => $pageName,
                        'view_date' => $currentDate
                    ]);

                    $visitedPages[] = $pageName;
                    setcookie($cookieName, json_encode($visitedPages), time() + $cookieDuration, '/');
                }

                http_response_code(200);
                echo json_encode(['message' => 'Vue enregistrée']);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de l\'enregistrement : ' . $e->getMessage()]);
            }
            exit;
        }

        if (!isset($data['username']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nom d\'utilisateur et mot de passe requis']);
            exit;
        }

        $username = $data['username'];
        $password = $data['password'];

        if (isset($data['action'])) {
            if ($data['action'] === 'register') {
            } elseif ($data['action'] === 'login') {
            }
        }

        if (!isset($data['armyName']) || !isset($data['units'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Données manquantes pour sauvegarder l\'armée']);
            exit;
        }

        $armyName = $data['armyName'];
        $units = $data['units'];

        if (!verifyPassword($pdo, $username, $password)) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentification échouée']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT id FROM armies WHERE username = :username AND army_name = :army_name");
            $stmt->execute(['username' => $username, 'army_name' => $armyName]);
            $existingArmy = $stmt->fetch(PDO::FETCH_ASSOC);

            $pdo->beginTransaction();

            if ($existingArmy) {
                $armyId = $existingArmy['id'];

                $stmt = $pdo->prepare("DELETE FROM army_units WHERE army_id = :army_id");
                $stmt->execute(['army_id' => $armyId]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO armies (username, army_name) VALUES (:username, :army_name)");
                $stmt->execute(['username' => $username, 'army_name' => $armyName]);
                $armyId = $pdo->lastInsertId();
            }

            // Insert new units
            $stmt = $pdo->prepare("
                INSERT INTO army_units (
                    army_id, unit_name, unit_id, characteristics, selected_weapons, selected_abilities, image_url,
                    model_count, min_models, max_models, cost_steps
                ) VALUES (
                    :army_id, :unit_name, :unit_id, :characteristics, :selected_weapons, :selected_abilities, :image_url,
                    :model_count, :min_models, :max_models, :cost_steps
                )
            ");
            foreach ($units as $unit) {
                file_put_contents('debug.log', "Données insérées pour unité : " . json_encode($unit, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
                $stmt->execute([
                    'army_id' => $armyId,
                    'unit_name' => $unit['name'],
                    'unit_id' => $unit['id'],
                    'characteristics' => json_encode($unit['characteristics']),
                    'selected_weapons' => json_encode($unit['selectedWeapons']),
                    'selected_abilities' => json_encode($unit['selectedAbilities']),
                    'image_url' => $unit['imageUrl'],
                    'model_count' => $unit['modelCount'] ?? 1,
                    'min_models' => $unit['minModels'] ?? 1,
                    'max_models' => $unit['maxModels'] ?? 1,
                    'cost_steps' => json_encode($unit['costSteps'] ?? [])
                ]);
            }

            $pdo->commit();
            http_response_code(201);
            echo json_encode(['id' => $armyId, 'message' => 'Armée sauvegardée avec succès']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la sauvegarde : ' . $e->getMessage()]);
        }
        break;
    case 'GET':
        $username = $_GET['username'] ?? '';

        if (isset($_GET['id'])) {
            $armyId = $_GET['id'];

            try {
                $stmt = $pdo->prepare("SELECT * FROM armies WHERE id = :id");
                $stmt->execute(['id' => $armyId]);
                $army = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$army) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Armée non trouvée']);
                    exit;
                }

                $stmt = $pdo->prepare("SELECT * FROM army_units WHERE army_id = :army_id");
                $stmt->execute(['army_id' => $armyId]);
                $units = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($units as &$unit) {
                    $unit['characteristics'] = json_decode($unit['characteristics'], true);
                    $unit['selected_weapons'] = json_decode($unit['selected_weapons'], true);
                    $unit['selected_abilities'] = json_decode($unit['selected_abilities'], true);
                    $unit['cost_steps'] = json_decode($unit['cost_steps'], true);
                }

                $army['units'] = $units;
                echo json_encode($army);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la récupération : ' . $e->getMessage()]);
            }
        } else {
            try {
                if ($username) {
                    $stmt = $pdo->prepare("SELECT id, army_name, username FROM armies WHERE username = :username");
                    $stmt->execute(['username' => $username]);
                } else {
                    $stmt = $pdo->prepare("SELECT id, army_name, username FROM armies");
                    $stmt->execute();
                }
                
                $armies = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($armies ?: []);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Erreur lors de la récupération : ' . $e->getMessage()]);
            }
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['username']) || !isset($data['password']) || !isset($data['armyId'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Données manquantes']);
            exit;
        }

        $username = $data['username'];
        $password = $data['password'];
        $armyId = $data['armyId'];

        if (!verifyPassword($pdo, $username, $password)) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentification échouée']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT id FROM armies WHERE id = :id AND username = :username");
            $stmt->execute(['id' => $armyId, 'username' => $username]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Armée non trouvée ou non autorisée']);
                exit;
            }

            $pdo->beginTransaction();
            $stmt = $pdo->prepare("DELETE FROM army_units WHERE army_id = :army_id");
            $stmt->execute(['army_id' => $armyId]);
            
            $stmt = $pdo->prepare("DELETE FROM armies WHERE id = :id");
            $stmt->execute(['id' => $armyId]);
            
            $pdo->commit();
            
            echo json_encode(['message' => 'Armée supprimée avec succès']);
        } catch (PDOException $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la suppression : ' . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
        break;
}