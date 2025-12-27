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
  
  <style>
    :root {
      --red: #ff1111;
      --yellow: #fffc00;
      --black: #000000;
      --bg: #0a0a0a;
      --text: #f5f7fa;
      --muted: #b8bfc8;
      --card: #121314;
      --card-hi: #16181a;
      --border: 1px solid rgba(255,255,255,.06);
      --glass: rgba(255,255,255,.06);
      --shadow: 0 12px 32px rgba(0,0,0,.45);
      --radius: 16px;
      --radius-sm: 12px;
      --radius-xs: 10px;
      --trans: 240ms cubic-bezier(.2,.8,.2,1);
    }
    
    /* Light mode variables */
    .light-mode {
      --red: #ff1111;
      --yellow: #ffaa00;
      --black: #000000;
      --bg: #f8f9fa;
      --text: #1a1a1a;
      --muted: #6c757d;
      --card: #ffffff;
      --card-hi: #f8f9fa;
      --border: 1px solid rgba(0,0,0,.12);
      --glass: rgba(0,0,0,.04);
      --shadow: 0 12px 32px rgba(0,0,0,.08);
    }
    
    body {
      margin: 0;
      font-family: "Noto Sans Tamil", Inter, sans-serif;
      color: var(--text);
      background: var(--bg);
      line-height: 1.6;
      transition: background-color var(--trans), color var(--trans);
    }
    
    /* Dark mode specific background */
    body:not(.light-mode) {
      background:
        radial-gradient(800px 420px at 10% -10%, rgba(255,17,17,.12), transparent 42%),
        radial-gradient(600px 380px at 95% 0%, rgba(255,252,0,.10), transparent 52%),
        var(--bg);
    }
    
    /* Theme toggle button */
    .theme-toggle {
      background: var(--card);
      border: var(--border);
      border-radius: 12px;
      width: 44px;
      height: 44px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all var(--trans);
      color: var(--text);
    }
    
    .theme-toggle:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow);
      background: var(--card-hi);
    }
    
    .theme-toggle svg {
      width: 20px;
      height: 20px;
    }
    
    .theme-toggle .sun-icon {
      display: none;
    }
    
    .light-mode .theme-toggle .moon-icon {
      display: none;
    }
    
    .light-mode .theme-toggle .sun-icon {
      display: block;
    }
  </style>
</head>
<body>

  <!-- App bar - same as index.php -->
  <header>
    <div>
      <a href="index.php">Liked தமிழ்</a>
      <!-- Theme Toggle -->
      <button class="theme-toggle" id="themeToggle" aria-label="Change theme">
        <svg class="moon-icon" viewBox="0 0 24 24" fill="none">
          <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" stroke="currentColor" stroke-width="1.6"/>
        </svg>
        <svg class="sun-icon" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="1.6"/>
          <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" stroke="currentColor" stroke-width="1.6"/>
        </svg>
      </button>
    </div>
  </header>

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

<script>
// Theme toggle functionality
const themeToggle = document.getElementById('themeToggle');
const body = document.body;

// Check for saved theme or prefer color scheme
const savedTheme = localStorage.getItem('theme');
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

if (savedTheme === 'light' || (!savedTheme && !prefersDark)) {
    body.classList.add('light-mode');
}

themeToggle.addEventListener('click', () => {
    body.classList.toggle('light-mode');
    
    // Save preference
    if (body.classList.contains('light-mode')) {
        localStorage.setItem('theme', 'light');
    } else {
        localStorage.setItem('theme', 'dark');
    }
});
</script>

</body>
</html>