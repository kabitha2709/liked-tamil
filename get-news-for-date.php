<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config/database.php';

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 60;
$offset = ($page - 1) * $limit;

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
        $filterWhere = " AND FIND_IN_SET('" . addslashes($categoryName) . "', n.categories) > 0";
    }
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM news n 
                   WHERE DATE(n.created_at) = ? AND n.status = 'published'" . $filterWhere;
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute([$date]);
    $totalResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    $totalNews = $totalResult['total'];
    $totalPages = ceil($totalNews / $limit);
    
    // Fetch news for the selected date with categories
    $newsQuery = "SELECT n.*, 
                  (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') FROM categories c WHERE FIND_IN_SET(c.id, n.categories) > 0) as category_names,
                  (SELECT GROUP_CONCAT(c.subcategories SEPARATOR ', ') FROM categories c WHERE FIND_IN_SET(c.id, n.categories) > 0) as subcategories_list
                  FROM news n 
                  WHERE DATE(n.created_at) = ? AND n.status = 'published'" . $filterWhere . "
                  ORDER BY n.created_at DESC 
                  LIMIT $limit OFFSET $offset";
    
    $newsStmt = $db->prepare($newsQuery);
    $newsStmt->execute([$date]);
    $news = $newsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add image paths
    foreach ($news as &$item) {
        if (!empty($item['image'])) {
            $item['image'] = 'uploads/news/' . $item['image'];
        } else {
            $item['image'] = 'https://picsum.photos/id/' . rand(1000, 1100) . '/800/500';
        }
    }
    
    // Format date display
    $selectedDate = new DateTime($date);
    $todayStr = date('Y-m-d');
    if ($date === $todayStr) {
        $dateDisplay = 'இன்றைய செய்திகள்';
    } else {
        $dateDisplay = $selectedDate->format('d/m/Y') . ' செய்திகள்';
    }
    
    echo json_encode([
        'success' => true,
        'news' => $news,
        'totalNews' => $totalNews,
        'totalPages' => $totalPages,
        'currentPage' => $page,
        'dateDisplay' => $dateDisplay
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load news',
        'news' => []
    ]);
}
?>
