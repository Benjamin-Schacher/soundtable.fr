<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

require_once __DIR__ . '/../../api/config.php';

try {
    // Récupérer les vues totales par page
    $stmt = $pdo->query("
        SELECT page_name, SUM(unique_views) as total_views 
        FROM page_views 
        GROUP BY page_name 
        ORDER BY total_views DESC
    ");
    $totalViews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les vues des 30 derniers jours
    $stmt = $pdo->query("
        SELECT view_date, SUM(unique_views) as daily_views 
        FROM page_views 
        WHERE view_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY view_date 
        ORDER BY view_date ASC
    ");
    $dailyViews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'total_views' => $totalViews,
        'daily_views' => $dailyViews
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur base de données: ' . $e->getMessage()]);
}
?>
