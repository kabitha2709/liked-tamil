<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config/database.php';

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Category mapping for filters
    $categoryMap = [
        'top' => 'சிறப்பு செய்திகள்',
        'local' => 'உள்ளூர் செய்திகள்',
        'global' => 'உலக செய்திகள்'
    ];
    
    // Build WHERE clause based on filter
    $filterWhere = '';
    if (!empty($filter) && isset($categoryMap[$filter])) {
        $categoryName = $categoryMap[$filter];
        $filterWhere = " AND FIND_IN_SET('" . $db->quote($categoryName) . "', n.categories) > 0";
    }
    
    // Fetch today's highlights (news from selected date) with filter
    $highlightsQuery = "SELECT n.id, n.title, n.content, n.image, n.created_at,
                    (SELECT name FROM categories WHERE FIND_IN_SET(id, n.categories) > 0 LIMIT 1) as category_name
                    FROM news n 
                    WHERE DATE(n.created_at) = ? AND n.status = 'published'" . $filterWhere . "
                    ORDER BY n.created_at DESC 
                    LIMIT 2";
    
    $highlightsStmt = $db->prepare($highlightsQuery);
    $highlightsStmt->execute([$date]);
    $highlights = $highlightsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add image paths
    foreach ($highlights as &$item) {
        if (!empty($item['image'])) {
            $item['image'] = 'uploads/news/' . $item['image'];
        } else {
            $item['image'] = 'https://picsum.photos/id/' . rand(1000, 1100) . '/800/500';
        }
    }
    
    echo json_encode([
        'success' => true,
        'news' => $highlights
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load highlights',
        'news' => []
    ]);
}
?>
