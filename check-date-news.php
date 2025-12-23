<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config/database.php';

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if date has published news - use COALESCE to handle both published_at and created_at
    $query = "SELECT COUNT(*) as count FROM news 
              WHERE DATE(COALESCE(published_at, created_at)) = ? 
              AND status = 'published'";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$date]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(['hasNews' => ($result['count'] > 0)]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to check date', 'hasNews' => false]);
}
?>
