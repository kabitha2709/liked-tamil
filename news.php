<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get news ID from URL
$news_id = isset($_GET['id']) ? $_GET['id'] : die('News ID not specified');

// Fetch news details
$newsQuery = "SELECT n.*, 
              (SELECT GROUP_CONCAT(name SEPARATOR ', ') 
               FROM categories 
               WHERE FIND_IN_SET(id, n.categories) > 0) as category_names
              FROM news n 
              WHERE n.id = ?";
$newsStmt = $db->prepare($newsQuery);
$newsStmt->execute([$news_id]);
$news = $newsStmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
    die('News not found');
}

// Get category IDs
$categoryIds = !empty($news['categories']) ? explode(',', $news['categories']) : [];

// Fetch related news (same categories)
if (!empty($categoryIds)) {
    $placeholders = str_repeat('?,', count($categoryIds) - 1) . '?';
    $relatedQuery = "SELECT n.* FROM news n 
                     WHERE n.id != ? 
                     AND n.status = 'published' 
                     AND (";
    
    $conditions = [];
    foreach ($categoryIds as $index => $catId) {
        $conditions[] = "FIND_IN_SET(?, n.categories) > 0";
    }
    
    $relatedQuery .= implode(' OR ', $conditions) . ") 
                     ORDER BY n.created_at DESC 
                     LIMIT 4";
    
    $relatedStmt = $db->prepare($relatedQuery);
    $params = array_merge([$news_id], $categoryIds);
    $relatedStmt->execute($params);
    $relatedNews = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $relatedNews = [];
}
?>

<!DOCTYPE html>
<html lang="ta">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo htmlspecialchars($news['title']); ?> - Liked தமிழ்</title>
  
  <!-- Same CSS as index.php -->
</head>
<body>

  <!-- App bar - same as index.php -->

  <!-- Article Content -->
  <main class="article-container">
    <div class="article-header">
      <?php if (!empty($news['category_names'])): ?>
        <div class="article-category"><?php echo htmlspecialchars($news['category_names']); ?></div>
      <?php endif; ?>
      
      <h1 class="article-title"><?php echo htmlspecialchars($news['title']); ?></h1>
      
      <div class="article-meta">
        <span>📅 <?php echo date('d/m/Y', strtotime($news['created_at'])); ?></span>
        <span>🕒 <?php echo date('H:i', strtotime($news['created_at'])); ?></span>
        <span>⏱️ 
          <?php 
          $wordCount = str_word_count(strip_tags($news['content']));
          $readingTime = ceil($wordCount / 200);
          echo max(1, $readingTime); 
          ?> நிமிடம்
        </span>
      </div>
      
      <?php if (!empty($news['image'])): ?>
        <img src="<?php echo htmlspecialchars($news['image']); ?>" alt="<?php echo htmlspecialchars($news['title']); ?>" class="article-image">
      <?php endif; ?>
    </div>
    
    <div class="article-content">
      <?php echo nl2br(htmlspecialchars($news['content'])); ?>
    </div>
  </main>

  <!-- Related News -->
  <?php if (!empty($relatedNews)): ?>
    <section class="related-section">
      <h2 class="related-title">தொடர்புடைய செய்திகள்</h2>
      <div class="related-grid">
        <?php foreach ($relatedNews as $related): ?>
          <a href="news.php?id=<?php echo $related['id']; ?>" class="related-card">
            <div class="related-card-title"><?php echo htmlspecialchars($related['title']); ?></div>
            <div class="related-card-meta">
              <?php echo date('d/m/Y', strtotime($related['created_at'])); ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

</body>
</html>