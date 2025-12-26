<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get parameters
$date = $_GET['date'] ?? date('Y-m-d');
$filter = $_GET['filter'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 60;
$offset = ($page - 1) * $limit;

// Build WHERE clause
$whereConditions = ["n.status = 'published'"];
$params = [];

// Date filter
if ($date == date('Y-m-d')) {
    // Today's news
    $whereConditions[] = "DATE(n.published_at) = CURDATE()";
} else {
    $whereConditions[] = "DATE(n.published_at) = ?";
    $params[] = $date;
}

// Category filter
if (!empty($filter) && is_numeric($filter)) {
    $whereConditions[] = "FIND_IN_SET(?, n.categories) > 0";
    $params[] = $filter;
}

// Prepare WHERE clause
$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Count query
$countQuery = "SELECT COUNT(*) as total FROM news n $whereClause";
$countStmt = $db->prepare($countQuery);
$countStmt->execute($params);
$totalNews = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

// News query with images
$newsQuery = "SELECT 
    n.*,
    COALESCE(
        (SELECT image_path FROM news_images WHERE news_id = n.id ORDER BY display_order LIMIT 1),
        n.image
    ) as image_path,
    (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') FROM categories c WHERE FIND_IN_SET(c.id, n.categories) > 0) as category_names
    FROM news n 
    $whereClause
    ORDER BY n.published_at DESC 
    LIMIT $limit OFFSET $offset";

$newsStmt = $db->prepare($newsQuery);
$newsStmt->execute($params);
$news = $newsStmt->fetchAll(PDO::FETCH_ASSOC);

// Format date for display
$dateObj = new DateTime($date);
$today = new DateTime();
$dateDisplay = '';
if ($date == $today->format('Y-m-d')) {
    $dateDisplay = 'இன்றைய செய்திகள்';
} else {
    $dateDisplay = $dateObj->format('d/m/Y') . ' செய்திகள்';
}

// Prepare response
$response = [
    'success' => true,
    'date' => $date,
    'dateDisplay' => $dateDisplay,
    'totalNews' => $totalNews,
    'news' => $news
];

header('Content-Type: application/json');
echo json_encode($response);
?>