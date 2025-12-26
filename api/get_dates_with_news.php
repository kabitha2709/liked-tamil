<?php
// get-news-for-date.php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

header('Content-Type: application/json');

$date = $_GET['date'] ?? date('Y-m-d');
$filter = $_GET['filter'] ?? '';
$page = $_GET['page'] ?? 1;
$per_page = 60;
$offset = ($page - 1) * $per_page;

// Get base_url from config
require 'config/config.php';

// Build WHERE clause for filter
$filterWhere = '';
if (!empty($filter)) {
    // Add filter logic if needed
    // $filterWhere = " AND n.categories LIKE '%$filter%'";
}

// Query to get news with images from both tables
$query = "SELECT n.*, 
          (SELECT GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') 
           FROM categories c 
           WHERE FIND_IN_SET(c.id, n.categories) > 0) as category_names,
          (SELECT image_path FROM news_images 
           WHERE news_id = n.id 
           ORDER BY display_order LIMIT 1) as image_path
          FROM news n
          WHERE n.status = 'published' 
          AND DATE(n.published_at) = :date
          $filterWhere
          ORDER BY n.published_at DESC 
          LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
$stmt->bindValue(':date', $date, PDO::PARAM_STR);
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$news = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total
$countQuery = "SELECT COUNT(*) as total FROM news 
               WHERE status = 'published' 
               AND DATE(published_at) = :date
               $filterWhere";
$countStmt = $db->prepare($countQuery);
$countStmt->bindValue(':date', $date, PDO::PARAM_STR);
$countStmt->execute();
$totalResult = $countStmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'news' => $news,
    'totalNews' => $totalResult['total'] ?? 0,
    'currentPage' => $page,
    'totalPages' => ceil(($totalResult['total'] ?? 0) / $per_page)
]);
?>