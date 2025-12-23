<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : date('n');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Fetch dates that have news in the given month
    // Use COALESCE to handle both published_at and created_at
    $query = "SELECT DATE(COALESCE(published_at, created_at)) as news_date 
              FROM news 
              WHERE YEAR(COALESCE(published_at, created_at)) = ? 
              AND MONTH(COALESCE(published_at, created_at)) = ? 
              AND status = 'published' 
              GROUP BY DATE(COALESCE(published_at, created_at))
              ORDER BY news_date ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$year, $month]);
    
    $dates = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dates[] = $row['news_date'];
    }
    
    // Remove duplicates and sort (ORDER BY should handle it, but ensure clean data)
    $dates = array_unique($dates);
    sort($dates);
    
    echo json_encode($dates);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load dates']);
}
?>
