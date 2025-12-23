<?php
// Database connection
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get search query
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 60;
$offset = ($page - 1) * $limit;

// Fetch categories
$categoryQuery = "SELECT * FROM categories WHERE status = 'active' ORDER BY id";
$categoryStmt = $db->prepare($categoryQuery);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch total search results
if (!empty($searchQuery)) {
    $countQuery = "SELECT COUNT(*) as total FROM news 
                   WHERE (title LIKE :query OR content LIKE :query) 
                   AND status = 'published'";
    $countStmt = $db->prepare($countQuery);
    $searchTerm = "%$searchQuery%";
    $countStmt->bindParam(':query', $searchTerm, PDO::PARAM_STR);
    $countStmt->execute();
    $totalResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    $totalNews = $totalResult['total'];
} else {
    $totalNews = 0;
}

$totalPages = ceil($totalNews / $limit);

// Fetch search results
$news = [];
if (!empty($searchQuery) && $totalNews > 0) {
    $newsQuery = "SELECT n.*, 
                  (SELECT name FROM categories WHERE FIND_IN_SET(id, n.categories) > 0 LIMIT 1) as category_name
                  FROM news n 
                  WHERE (n.title LIKE :query OR n.content LIKE :query) 
                  AND n.status = 'published' 
                  ORDER BY n.created_at DESC 
                  LIMIT $limit OFFSET $offset";
    $newsStmt = $db->prepare($newsQuery);
    $searchTerm = "%$searchQuery%";
    $newsStmt->bindParam(':query', $searchTerm, PDO::PARAM_STR);
    $newsStmt->execute();
    $news = $newsStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ta">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>தேடல் முடிவுகள் - Liked தமிழ்</title>
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Noto+Sans+Tamil:wght@400;700&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --red: #ff1111;        /* primary red */
      --yellow: #fffc00;     /* accent yellow */
      --black: #000000;      /* base black */
      --fb-blue: #1877f2;    /* Facebook blue */

      --bg: #0a0a0a;         /* deep black for background */
      --text: #f5f7fa;       /* white-ish text */
      --muted: #b8bfc8;      /* muted text */
      --card: #121314;       /* card surface */
      --card-hi: #16181a;    /* hover surface */
      --border: 1px solid rgba(255,255,255,.06);
      --glass: rgba(255,255,255,.06);
      --shadow: 0 12px 32px rgba(0,0,0,.45);

      --radius: 16px;
      --radius-sm: 12px;
      --radius-xs: 10px;
      --trans: 240ms cubic-bezier(.2,.8,.2,1);
    }

    * { box-sizing: border-box }
    html, body { height: 100% }
    body {
      margin: 0;
      font-family: "Noto Sans Tamil", Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      color: var(--text);
      background:
        radial-gradient(800px 420px at 10% -10%, rgba(255,17,17,.12), transparent 42%),
        radial-gradient(600px 380px at 95% 0%, rgba(255,252,0,.10), transparent 52%),
        var(--bg);
      background-attachment: fixed;
      line-height: 1.6;
    }

    /* App bar */
    .logo {
      width: 20%;       /* adjust size */
      height: 20%;
      border-radius: 8px; /* optional rounded corners */
      object-fit: contain; /* keeps aspect ratio */
    }

    .appbar {
      position: sticky; top: 0; z-index: 90;
      backdrop-filter: saturate(1.25) blur(12px);
      background: linear-gradient(180deg, rgba(0,0,0,.55), rgba(0,0,0,.25));
      border-bottom: var(--border);
    }
    .appbar-wrap {
      display: grid; grid-template-columns: auto 1fr auto; gap: 16px;
      align-items: center; padding: 12px clamp(14px, 3vw, 24px);
      max-width: 1200px; margin: 0 auto;
    }
    .brand {
      display:flex; align-items:center; gap: 12px; text-decoration:none; color: var(--text);
    }
    .title {
      font-weight: 800; font-size: clamp(18px, 2.4vw, 28px); letter-spacing: .2px;
    }
    .search {
      display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:12px;
      background: var(--glass); border: var(--border);
    }
    .search input {
      flex:1; background:transparent; border:0; color: var(--text); outline:none;
      font-family: "Noto Sans Tamil", Inter, sans-serif;
    }
    .actions { display:flex; gap: 10px }
    .btn {
      display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:12px;
      background: var(--card); border: var(--border); color: var(--text); cursor:pointer;
      transition: transform var(--trans), box-shadow var(--trans), background var(--trans);
      text-decoration: none;
      font-family: "Noto Sans Tamil", Inter, sans-serif;
      border: none;
    }
    .btn:hover { transform: translateY(-2px); box-shadow: var(--shadow) }
    .btn.primary {
      background: linear-gradient(180deg, var(--red), #cc0f0f);
      color: #fff; border: 0;
    }
    .icon { width: 20px; height: 20px; stroke-width: 1.5; }

    /* Category bar (chips) */
    .catbar {
      background: linear-gradient(180deg, rgba(255,252,0,.08), transparent);
      border-top: var(--border);
      border-bottom: var(--border);
    }
    .catbar-wrap {
      max-width: 1200px; margin: 0 auto; padding: 10px clamp(14px, 3vw, 24px);
      display:flex; gap: 8px; overflow-x: auto; scrollbar-width: thin;
    }
    .chip {
      flex: 0 0 auto;
      display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px;
      background: var(--glass); border: var(--border); color: var(--text); font-weight:600; font-size: 13px;
      transition: background var(--trans), transform var(--trans), color var(--trans);
      cursor: pointer;
      text-decoration: none;
      white-space: nowrap;
      font-family: "Noto Sans Tamil", Inter, sans-serif;
    }
    .chip:hover { transform: translateY(-2px); background: rgba(255,17,17,.18) }
    .chip.active { background: linear-gradient(180deg, var(--red), #d10f0f); color: #fff; border: 0 }

    /* Sections */
    .section { max-width: 1200px; margin: 8px auto 20px; padding: 0 clamp(14px, 3vw, 24px) }
    .section-head { display:flex; justify-content: space-between; align-items: baseline; margin-bottom: 12px }
    .section-title { font-weight:800; font-size: clamp(18px, 2vw, 22px); font-family: "Noto Sans Tamil", Inter, sans-serif; }

    /* Grid (desktop 3+, mobile 2-per-row) */
    .grid-news {
      display:grid; grid-template-columns: repeat(4, 1fr); gap: 14px;
    }
    @media (max-width: 1120px) { .grid-news { grid-template-columns: repeat(3, 1fr) } }
    @media (max-width: 980px)  { .grid-news { grid-template-columns: repeat(2, 1fr) } }
    @media (max-width: 640px)  { .grid-news { grid-template-columns: 1fr 1fr } }

    .news-card {
      display:flex; flex-direction: column; overflow:hidden;
      border-radius: var(--radius-sm); background: var(--card); border: var(--border);
      box-shadow: var(--shadow);
      transition: transform var(--trans), box-shadow var(--trans), background var(--trans);
      text-decoration: none;
      color: inherit;
    }
    .news-card:hover { transform: translateY(-4px); box-shadow: 0 14px 40px rgba(0,0,0,.50); background: var(--card-hi) }
    .news-thumb { position:relative; aspect-ratio: 16/10; overflow:hidden }
    .news-thumb img { width:100%; height:100%; object-fit:cover; transform: scale(1.02); transition: transform .7s ease }
    .news-card:hover .news-thumb img { transform: scale(1.06) }
    .badge {
      position:absolute; top:10px; left:10px; display:inline-flex; gap:6px; align-items:center;
      padding:6px 10px; border-radius:999px; background: rgba(0,0,0,.55); color:#fff; font-size:12px; border: 1px solid rgba(255,255,255,.25);
      font-family: "Noto Sans Tamil", Inter, sans-serif;
    }
    .news-content { padding: 12px 12px 14px }
    .news-title { font-weight:700; font-size: 16px; line-height:1.4; margin: 4px 0 6px; font-family: "Noto Sans Tamil", Inter, sans-serif; }
    .news-meta { font-size: 12px; color: var(--muted); display:flex; gap:8px; align-items:center; font-family: "Noto Sans Tamil", Inter, sans-serif; }
    .readmore { margin-top: 10px; display:inline-flex; align-items:center; gap:8px; color: var(--yellow); font-weight:700; text-decoration:none; font-family: "Noto Sans Tamil", Inter, sans-serif; }
    .readmore::after { content:'→'; transition: transform var(--trans) }
    .news-card:hover .readmore::after { transform: translateX(3px) }

    /* Pagination */
    .pagination {
      display:flex; gap: 8px; justify-content: center; margin: 18px 0 100px;
      flex-wrap: wrap;
    }
    .page {
      padding:10px 14px; border-radius:999px; background: var(--glass); border: var(--border); cursor:pointer;
      transition: background var(--trans), transform var(--trans);
      text-decoration: none;
      color: var(--text);
      min-width: 40px;
      text-align: center;
      font-family: "Noto Sans Tamil", Inter, sans-serif;
    }
    .page.active { background: linear-gradient(180deg, var(--red), #cc0f0f); color: #fff; border: 0 }
    .page:hover { transform: translateY(-2px); background: rgba(255,17,17,.18) }

    /* Desktop Footer */
    .likedtamil-footer {
      display:  block;
      background: #000000;
      color: #fffc00;
      text-align: center;
      padding: 8px 10px;
      font-size: 13px;
      border-top: 2px solid #ff1111;
      font-family: "Noto Sans Tamil", Inter, sans-serif;
      margin-top: 20px;
    }
    
    .likedtamil-footer-wrap {
      max-width: 1200px;
      margin: 0 auto;
    }
    
    .likedtamil-footer a {
      color: #ff1111;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.2s ease;
    }
    
    .likedtamil-footer a:hover {
      color: #fffc00;
    }
    
    @media (max-width: 740px) {
      .likedtamil-footer {
        display: none;
      }
    }

    /* Sticky mobile footer */
    .mobile-footer {
      position: fixed; bottom: 0; left: 0; right: 0; z-index: 99;
      backdrop-filter: blur(12px) saturate(1.1);
      background: linear-gradient(180deg, rgba(255,17,17,.85), rgba(255,17,17,.98));
      border-top: 2px solid rgba(255,252,0,.55);
      display:none;
    }
    @media (max-width: 740px) {
      .mobile-footer { display:block }
      body { padding-bottom: 82px }
      .search { display:none }
      .actions { display:none }
    }
    .foot-wrap { max-width: 1200px; margin: 0 auto; padding: 10px clamp(12px, 4vw, 18px); display:flex; justify-content: space-between; gap: 6px }
    .foot-item {
      flex:1; display:flex; flex-direction: column; align-items:center; gap: 6px;
      color: #fff; text-decoration:none; padding:8px; border-radius: 12px;
      transition: transform var(--trans), background var(--trans)
    }
    .foot-item:hover, .foot-item.active { background: rgba(0,0,0,.18); transform: translateY(-2px) }
    .foot-icon { width: 22px; height: 22px; stroke-width: 1.6; }
    .foot-label { font-size: 12px; font-weight:700; font-family: "Noto Sans Tamil", Inter, sans-serif; }

    /* Subtle entrance */
    .fade-in-up { opacity:0; transform: translateY(12px); animation: in .6s var(--trans) forwards }
    @keyframes in { to { opacity:1; transform: translateY(0) } }
    
    /* No News Message */
    .no-news {
      text-align: center;
      padding: 40px 20px;
      color: var(--muted);
      grid-column: 1 / -1;
    }
    
    /* Search results specific styles */
    .search-results-header {
      max-width: 1200px;
      margin: 20px auto;
      padding: 0 clamp(14px, 3vw, 24px);
    }
    
    .search-info {
      background: var(--card);
      border: var(--border);
      border-radius: var(--radius);
      padding: 20px;
      margin-bottom: 20px;
    }
    
    .search-query {
      color: var(--yellow);
      font-weight: 700;
    }
    
    .no-results {
      text-align: center;
      padding: 60px 20px;
      color: var(--muted);
      grid-column: 1 / -1;
    }
    
    .no-results h3 {
      font-size: 24px;
      margin-bottom: 10px;
      color: var(--text);
    }
    
    .suggestions {
      margin-top: 20px;
      font-size: 14px;
      color: var(--muted);
    }
    
    .suggestions ul {
      list-style: none;
      padding: 0;
    }
    
    .suggestions li {
      margin: 5px 0;
    }
    
    /* Mobile responsiveness */
    @media (max-width: 640px) {
      .appbar-wrap {
        grid-template-columns: auto 1fr;
        gap: 12px;
      }
      .title {
        font-size: 18px;
      }
      
      .catbar-wrap {
        padding: 8px 12px;
      }
      .chip {
        padding: 6px 10px;
        font-size: 12px;
      }
      
      .grid-news {
        gap: 12px;
      }
      .news-title {
        font-size: 15px;
      }
      
      .pagination {
        gap: 6px;
        margin: 18px 0 80px;
      }
      .page {
        padding: 8px 12px;
        font-size: 14px;
        min-width: 36px;
      }
      
      .search-info {
        padding: 15px;
      }
      
      .no-results {
        padding: 40px 20px;
      }
    }
    
    /* Ellipsis for pagination */
    .page[style*="background: transparent"] {
      background: transparent !important;
      border: none !important;
      color: var(--muted) !important;
      cursor: default !important;
    }
    /* Search bar mobile responsive */
@media (max-width: 740px) {
  .mobile-footer { display:block }
  body { padding-bottom: 82px }
  
  /* Hide search bar in header on mobile */
  .appbar .search { 
    display: none; 
  }
  
  /* Show search in mobile footer instead */
  .mobile-search {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: var(--glass);
    border: var(--border);
    border-radius: 12px;
    margin: 10px;
  }
  
  .mobile-search input {
    flex: 1;
    background: transparent;
    border: 0;
    color: var(--text);
    outline: none;
    font-family: "Noto Sans Tamil", Inter, sans-serif;
  }
  
  .actions { display: none; }
  
  /* Adjust header for mobile */
  .appbar-wrap {
    grid-template-columns: auto 1fr;
    gap: 12px;
  }
}
  </style>
</head>
<body>

  <!-- App bar - Same as index.php -->
  <header class="appbar">
    <div class="appbar-wrap">
      <a href="index.php" class="brand">
        <img src="Liked-tamil-news-logo-1 (2).png" alt="Portal Logo" class="logo" />
        <span class="title">Liked தமிழ்</span>
      </a>
      <form method="GET" action="search.php" class="search" role="search">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M11 5a6 6 0 016 6c0 1.3-.41 2.5-1.11 3.48l4.32 4.32-1.41 1.41-4.32-4.32A6 6 0 1111 5z" stroke="currentColor" stroke-width="1.5"/>
        </svg>
        <input type="search" name="q" placeholder="தேடல்…" aria-label="தேடல்" value="<?php echo htmlspecialchars($searchQuery); ?>" />
      </form>
      <div class="actions">
        <a href="index.php#subscriptionModal" class="btn primary">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M12 3l9 6-9 6-9-6 9-6zM3 15l9 6 9-6" stroke="currentColor" stroke-width="1.5"/>
          </svg>
          Subscribe
        </a>
      </div>
    </div>
  </header>

  <!-- Category Navigation - Same as index.php -->
  <nav class="catbar" aria-label="Categories">
    <div class="catbar-wrap">
      <a href="index.php" class="chip">முகப்பு</a>
      <?php foreach ($categories as $category): ?>
        <a href="category.php?id=<?php echo $category['id']; ?>" class="chip">
          <?php echo htmlspecialchars($category['name']); ?>
        </a>
      <?php endforeach; ?>
    </div>
  </nav>

  <!-- Search Results -->
  <div class="search-results-header">
    <div class="search-info">
      <h2 style="margin: 0 0 10px 0; font-family: 'Noto Sans Tamil', Inter, sans-serif;">
        <?php if (!empty($searchQuery)): ?>
          "<?php echo htmlspecialchars($searchQuery); ?>" - தேடல் முடிவுகள்
        <?php else: ?>
          தேடல்
        <?php endif; ?>
      </h2>
      <p style="margin: 0; color: var(--muted); font-family: 'Noto Sans Tamil', Inter, sans-serif;">
        <?php if (!empty($searchQuery)): ?>
          <?php echo $totalNews; ?> முடிவுகள் காணப்பட்டன
        <?php else: ?>
          தேடல் வார்த்தையை உள்ளிடவும்
        <?php endif; ?>
      </p>
    </div>
  </div>

  <!-- News Grid -->
  <section class="section">
    <?php if (!empty($searchQuery) && $totalNews > 0): ?>
      <div class="grid-news">
        <?php foreach ($news as $item): ?>
          <article class="news-card">
            <a href="news.php?id=<?php echo $item['id']; ?>" style="text-decoration: none; color: inherit;">
              <div class="news-thumb">
                <img src="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : 'https://picsum.photos/800/500'; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" />
                <span class="badge"><?php echo htmlspecialchars($item['category_name'] ?? 'செய்தி'); ?></span>
              </div>
              <div class="news-content">
                <h3 class="news-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                <div class="news-meta">
                  <?php 
                  $publishTime = new DateTime($item['created_at']);
                  $now = new DateTime();
                  $interval = $now->diff($publishTime);
                  
                  if ($interval->days > 0) {
                    echo $interval->days . ' நாட்கள்';
                  } elseif ($interval->h > 0) {
                    echo $interval->h . ' மணி';
                  } else {
                    echo $interval->i . ' நி';
                  }
                  ?>
                  <span>•</span>
                  <?php 
                  $wordCount = str_word_count(strip_tags($item['content']));
                  $readingTime = ceil($wordCount / 200);
                  echo max(1, $readingTime); 
                  ?> நிமிடம்
                </div>
                <div class="readmore">மேலும் படிக்க</div>
              </div>
            </a>
          </article>
        <?php endforeach; ?>
      </div>
      
      <!-- Pagination for search results -->
      <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="தேடல் பக்கமாற்றம்">
          <?php if ($page > 1): ?>
            <a href="search.php?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $page - 1; ?>" class="page">
              ‹ முந்தைய
            </a>
          <?php endif; ?>
          
          <?php 
          $startPage = max(1, $page - 2);
          $endPage = min($totalPages, $page + 2);
          
          // Show first page if not in range
          if ($startPage > 1): ?>
            <a href="search.php?q=<?php echo urlencode($searchQuery); ?>&page=1" class="page">1</a>
            <?php if ($startPage > 2): ?>
              <span class="page" style="background: transparent; border: none; color: var(--muted); cursor: default;">...</span>
            <?php endif; ?>
          <?php endif; ?>
          
          <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="search.php?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $i; ?>" 
               class="page <?php echo ($i == $page) ? 'active' : ''; ?>"
               aria-label="பக்கம் <?php echo $i; ?>"
               aria-current="<?php echo ($i == $page) ? 'page' : 'false'; ?>">
              <?php echo $i; ?>
            </a>
          <?php endfor; ?>
          
          <?php if ($endPage < $totalPages): ?>
            <?php if ($endPage < $totalPages - 1): ?>
              <span class="page" style="background: transparent; border: none; color: var(--muted); cursor: default;">...</span>
            <?php endif; ?>
            <a href="search.php?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $totalPages; ?>" class="page">
              <?php echo $totalPages; ?>
            </a>
          <?php endif; ?>
          
          <?php if ($page < $totalPages): ?>
            <a href="search.php?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $page + 1; ?>" class="page">
              அடுத்த ›
            </a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
      
    <?php elseif (!empty($searchQuery)): ?>
      <div class="no-results">
        <h3>முடிவுகள் இல்லை</h3>
        <p>"<?php echo htmlspecialchars($searchQuery); ?>" க்கு எந்த முடிவுகளும் கிடைக்கவில்லை.</p>
        <div class="suggestions">
          <p>பரிந்துரைகள்:</p>
          <ul>
            <li>மீண்டும் சரிபார்க்கவும்</li>
            <li>வேறு வார்த்தைகளைப் பயன்படுத்தவும்</li>
            <li>பிரிவுகள் மூலம் உலாவவும்</li>
          </ul>
          <a href="index.php" class="btn primary" style="margin-top: 20px; display: inline-block;">முகப்புக்குத் திரும்பு</a>
        </div>
      </div>
    <?php else: ?>
      <div class="no-results">
        <h3>தேடல்</h3>
        <p>தேடல் வார்த்தையை உள்ளிடவும்</p>
        <a href="index.php" class="btn primary" style="margin-top: 20px; display: inline-block;">முகப்புக்குத் திரும்பு</a>
      </div>
    <?php endif; ?>
  </section>

  <!-- Footer - Same as index.php -->
  <footer class="likedtamil-footer">
    <div class="likedtamil-footer-wrap">
      © <?php echo date('Y'); ?> All Rights Reserved by <a href="https://likedtamil.lk" target="_blank">Likedtamil.lk</a> | Developed by <a href="https://webbuilders.lk" target="_blank">Webbuilders.lk</a>
    </div>
  </footer>

 <!-- Mobile Footer - Same as index.php -->
<footer class="mobile-footer" role="navigation" aria-label="மொபைல் அடிக்குறிப்பு">
  <!-- Mobile Search Bar -->
  <div class="mobile-search" style="display: none;">
    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="#fff">
      <path d="M11 5a6 6 0 016 6c0 1.3-.41 2.5-1.11 3.48l4.32 4.32-1.41 1.41-4.32-4.32A6 6 0 1111 5z" stroke="#fff" stroke-width="1.5"/>
    </svg>
    <form method="GET" action="search.php" style="flex: 1; display: flex;">
      <input type="search" name="q" placeholder="தேடல்…" aria-label="தேடல்" value="<?php echo htmlspecialchars($searchQuery); ?>" />
    </form>
  </div>
  
  <div class="foot-wrap">
    <a href="index.php" class="foot-item">
      <svg class="foot-icon" viewBox="0 0 24 24" fill="none" stroke="#fff">
        <path d="M3 10l9-7 9 7v9a2 2 0 01-2 2H5a2 2 0 01-2-2v-9z" stroke="#fff" stroke-width="1.6"/>
      </svg>
      <span class="foot-label">முகப்பு</span>
    </a>
    <a href="categories.php" class="foot-item">
      <svg class="foot-icon" viewBox="0 0 24 24" fill="none" stroke="#fff">
        <path d="M4 6h16M4 12h16M4 18h16" stroke="#fff" stroke-width="1.6"/>
      </svg>
      <span class="foot-label">பிரிவுகள்</span>
    </a>
    <a href="search.php" class="foot-item active" id="mobileSearchBtn">
      <svg class="foot-icon" viewBox="0 0 24 24" fill="none" stroke="#fff">
        <path d="M11 5a6 6 0 016 6c0 1.3-.41 2.5-1.11 3.48l4.32 4.32-1.41 1.41-4.32-4.32A6 6 0 1111 5z" stroke="#fff" stroke-width="1.6"/>
      </svg>
      <span class="foot-label">தேடல்</span>
    </a>
    <a href="about.php" class="foot-item">
      <svg class="foot-icon" viewBox="0 0 24 24" fill="none" stroke="#fff">
        <circle cx="12" cy="12" r="6" stroke="#fff" stroke-width="1.6"/>
      </svg>
      <span class="foot-label">சுயவிவரம்</span>
    </a>
    <a href="video.php" class="foot-item">
      <svg class="foot-icon" viewBox="0 0 24 24" fill="none" stroke="#fff">
        <path d="M6 19l6-6 6 6M6 12l6-6 6 6" stroke="#fff" stroke-width="1.6"/>
      </svg>
      <span class="foot-label">வீடியோ</span>
    </a>
  </div>
</footer>

<script>
// Mobile search functionality
document.addEventListener('DOMContentLoaded', function() {
  const mobileSearchBtn = document.getElementById('mobileSearchBtn');
  const mobileSearchBar = document.querySelector('.mobile-search');
  const mobileSearchInput = document.querySelector('.mobile-search input');
  
  if (mobileSearchBtn && mobileSearchBar) {
    // Toggle search bar visibility
    mobileSearchBtn.addEventListener('click', function(e) {
      e.preventDefault();
      
      if (mobileSearchBar.style.display === 'flex') {
        mobileSearchBar.style.display = 'none';
      } else {
        mobileSearchBar.style.display = 'flex';
        mobileSearchInput.focus();
      }
    });
    
    // Submit search when Enter key is pressed
    mobileSearchInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        const form = this.closest('form');
        if (form) {
          form.submit();
        }
      }
    });
  }
  
  // Auto-hide search bar when clicking outside
  document.addEventListener('click', function(e) {
    if (mobileSearchBar && mobileSearchBar.style.display === 'flex' &&
        !mobileSearchBar.contains(e.target) && 
        !mobileSearchBtn.contains(e.target)) {
      mobileSearchBar.style.display = 'none';
    }
  });
});
</script>

</body>
</html>