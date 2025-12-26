<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Handle subscription form in search.php
$subscriptionSuccess = false;
$subscriptionError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe'])) {
    $email = $_POST['email'] ?? '';
    
    // Validate email
    if (empty($email)) {
        $subscriptionError = 'மின்னஞ்சலை உள்ளிடவும்';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $subscriptionError = 'செல்லுபடியாகாத மின்னஞ்சல் முகவரி';
    } else {
        try {
            // Check if email already exists
            $checkQuery = "SELECT id FROM subscribers WHERE email = ?";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->execute([$email]);
            
            if ($checkStmt->fetch()) {
                $subscriptionError = 'இந்த மின்னஞ்சல் ஏற்கனவே சந்தாதாரராக உள்ளது';
            } else {
                // Save to database
                $subscribeQuery = "INSERT INTO subscribers (email, created_at) VALUES (?, NOW())";
                $subscribeStmt = $db->prepare($subscribeQuery);
                $subscribeStmt->execute([$email]);
                
                $subscriptionSuccess = true;
            }
        } catch (PDOException $e) {
            $subscriptionError = 'தொழில்நுட்ப பிழை. மீண்டும் முயற்சிக்கவும்';
        }
    }
}

// Get search term
$searchTerm = $_GET['q'] ?? '';
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Get page for pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 60;
$offset = ($page - 1) * $limit;

// Fetch search results
if (!empty($searchTerm)) {
    // Prepare search pattern
    $searchPattern = "%{$searchTerm}%";

    // Optional category filter
    $categoryFilterSqlCount = '';
    $categoryFilterSqlNews = '';
    $countParams = [$searchPattern, $searchPattern, $searchPattern];
    $newsParams = [$searchPattern, $searchPattern, $searchPattern];
    if ($category_id > 0) {
        $categoryPattern = '%' . $category_id . '%';
        // For the COUNT query the table is not aliased, so use plain column name
        $categoryFilterSqlCount = " AND (categories LIKE ? OR categories LIKE ? )";
        // For the main news query we alias the table as `n`, so use `n.categories`
        $categoryFilterSqlNews = " AND (n.categories LIKE ? OR n.categories LIKE ? )";
        // Append pattern for both param lists
        $countParams[] = $categoryPattern;
        $countParams[] = $categoryPattern;
        $newsParams[] = $categoryPattern;
        $newsParams[] = $categoryPattern;
    }

    // Count total results
    $countQuery = "SELECT COUNT(*) as total FROM news 
                   WHERE status = 'published' 
                   AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?)" . $categoryFilterSqlCount;
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute($countParams);
    $totalResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    $totalNews = $totalResult['total'];
    $totalPages = ceil($totalNews / $limit);

    // Get search results with images
    $newsQuery = "SELECT 
        n.*,
        COALESCE(
            (SELECT image_path FROM news_images WHERE news_id = n.id ORDER BY display_order LIMIT 1),
            n.image
        ) as image_path,
        (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') FROM categories c WHERE FIND_IN_SET(c.id, n.categories) > 0) as category_names
        FROM news n 
        WHERE n.status = 'published' 
        AND (n.title LIKE ? OR n.content LIKE ? OR n.excerpt LIKE ?)" . $categoryFilterSqlNews . "
        ORDER BY n.published_at DESC 
        LIMIT $limit OFFSET $offset";

    $newsStmt = $db->prepare($newsQuery);
    // execute with the assembled params
    $newsStmt->execute($newsParams);
    $news = $newsStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $news = [];
    $totalNews = 0;
    $totalPages = 0;
}

// Fetch categories for navigation
$categoryQuery = "SELECT * FROM categories WHERE status = 'active' ORDER BY id";
$categoryStmt = $db->prepare($categoryQuery);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

require 'config/config.php';
?>

<!DOCTYPE html>
<html lang="ta">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>தேடல் முடிவுகள் - Liked தமிழ்</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Noto+Sans+Tamil:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Reuse styles from index.php */
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
        
        * { box-sizing: border-box }
        body {
            margin: 0;
            font-family: "Noto Sans Tamil", Inter, sans-serif;
            color: var(--text);
            background:
                radial-gradient(800px 420px at 10% -10%, rgba(255,17,17,.12), transparent 42%),
                radial-gradient(600px 380px at 95% 0%, rgba(255,252,0,.10), transparent 52%),
                var(--bg);
            background-attachment: fixed;
        }
        
        /* Header and navigation styles from index.php */
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
        
        .logo {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: contain;
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
        }
        
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
        }
        
        .chip.active { 
            background: linear-gradient(180deg, var(--red), #d10f0f); 
            color: #fff; 
            border: 0 
        }
        
        /* Main content */
        .main-content {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 clamp(14px, 3vw, 24px);
        }
        
        .search-header {
            margin-bottom: 30px;
        }
        
        .search-title {
            font-size: clamp(24px, 3vw, 32px);
            font-weight: 800;
            color: var(--yellow);
            margin-bottom: 10px;
        }
        
        .search-info {
            color: var(--muted);
            font-size: 16px;
        }
        
        .search-term {
            color: var(--yellow);
            font-weight: 600;
        }
        
        .results-count {
            color: var(--muted);
            font-size: 14px;
            margin-top: 5px;
        }
        
        /* News grid */
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
        
        .news-card:hover { 
            transform: translateY(-4px); 
            box-shadow: 0 14px 40px rgba(0,0,0,.50); 
            background: var(--card-hi) 
        }
        
        .news-thumb {
            position:relative; aspect-ratio: 16/10; overflow:hidden
        }
        
        .news-thumb img {
            width:100%; height:100%; object-fit:cover; 
            transform: scale(1.02); transition: transform .7s ease
        }
        
        .news-card:hover .news-thumb img { 
            transform: scale(1.06) 
        }
        
        .badge {
            position:absolute; top:10px; left:10px; display:inline-flex; gap:6px; align-items:center;
            padding:6px 10px; border-radius:999px; background: rgba(0,0,0,.55); color:#fff; font-size:12px; border: 1px solid rgba(255,255,255,.25)
        }
        
        .news-content { 
            padding: 12px 12px 14px 
        }
        
        .news-title { 
            font-weight:700; font-size: 16px; line-height:1.4; margin: 4px 0 6px 
        }
        
        .news-meta { 
            font-size: 12px; color: var(--muted); display:flex; gap:8px; align-items:center 
        }
        
        .readmore { 
            margin-top: 10px; display:inline-flex; align-items:center; gap:8px; color: var(--yellow); font-weight:700; text-decoration:none 
        }
        
        .readmore::after { 
            content:'→'; transition: transform var(--trans) 
        }
        
        .news-card:hover .readmore::after { 
            transform: translateX(3px) 
        }
        
        /* No results */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            grid-column: 1 / -1;
        }
        
        .no-results h3 {
            color: var(--yellow);
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .no-results p {
            color: var(--muted);
            margin-bottom: 20px;
        }
        
        .btn {
            display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:12px;
            background: var(--card); border: var(--border); color: var(--text); cursor:pointer;
            transition: transform var(--trans), box-shadow var(--trans), background var(--trans);
            text-decoration: none;
        }
        
        .btn.primary {
            background: linear-gradient(180deg, var(--red), #cc0f0f);
            color: #fff;
            border: 0;
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin: 40px 0 100px;
            flex-wrap: wrap;
        }
        
        .page {
            padding: 10px 14px;
            border-radius: 999px;
            background: var(--glass);
            border: var(--border);
            cursor: pointer;
            transition: background var(--trans), transform var(--trans);
            text-decoration: none;
            color: var(--text);
            min-width: 40px;
            text-align: center;
        }
        
        .page.active {
            background: linear-gradient(180deg, var(--red), #cc0f0f);
            color: #fff;
            border: 0;
        }
        
        .page:hover {
            transform: translateY(-2px);
            background: rgba(255,17,17,.18);
        }
        
        /* Mobile footer */
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
        }
        
        .foot-wrap { 
            max-width: 1200px; margin: 0 auto; padding: 10px clamp(12px, 4vw, 18px); 
            display:flex; justify-content: space-between; gap: 6px 
        }
        
        .foot-item {
            flex:1; display:flex; flex-direction: column; align-items:center; gap: 6px;
            color: #fff; text-decoration:none; padding:8px; border-radius: 12px;
            transition: transform var(--trans), background var(--trans)
        }
        
        .foot-icon { width: 22px; height: 22px }
        .foot-label { font-size: 12px; font-weight:700 }
        
        /* Desktop Footer */
        .likedtamil-footer {
            display: block;
            background: #000000;
            color: #fffc00;
            text-align: center;
            padding: 8px 10px;
            font-size: 13px;
            border-top: 2px solid #ff1111;
            font-family: "Noto Sans Tamil", Inter, sans-serif;
            margin-top: 20px;
        }
        
        @media (max-width: 740px) {
            .likedtamil-footer {
                display: none;
            }
        }
        
        /* Subscription Modal */
        .subscription-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .subscription-content {
            background: var(--card);
            border-radius: var(--radius);
            padding: 30px;
            width: 90%;
            max-width: 500px;
            border: var(--border);
            box-shadow: var(--shadow);
            position: relative;
        }

        .subscription-content h3 {
            color: var(--yellow);
            margin-bottom: 20px;
            text-align: center;
            font-size: 24px;
        }

        .subscription-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .subscription-form input {
            flex: 1;
            padding: 12px 15px;
            border-radius: var(--radius-sm);
            background: var(--glass);
            border: var(--border);
            color: var(--text);
            outline: none;
            font-family: "Noto Sans Tamil", Inter, sans-serif;
            font-size: 14px;
        }

        .subscription-form input:focus {
            border-color: var(--yellow);
        }

        .subscription-form button {
            background: linear-gradient(180deg, var(--red), #cc0f0f);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            font-family: "Noto Sans Tamil", Inter, sans-serif;
            transition: transform var(--trans), box-shadow var(--trans);
        }

        .subscription-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 17, 17, 0.3);
        }

        .close-modal {
            background: transparent;
            border: none;
            color: var(--muted);
            cursor: pointer;
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background var(--trans), color var(--trans);
        }

        .close-modal:hover {
            background: rgba(255,255,255,0.1);
            color: var(--text);
        }

        .subscription-success {
            color: var(--yellow);
            text-align: center;
            padding: 12px;
            margin-bottom: 20px;
            background: rgba(255, 252, 0, 0.1);
            border-radius: 8px;
            border: 1px solid rgba(255, 252, 0, 0.3);
            font-weight: 600;
        }

        .subscription-error {
            color: var(--red);
            text-align: center;
            padding: 12px;
            margin-bottom: 20px;
            background: rgba(255, 17, 17, 0.1);
            border-radius: 8px;
            border: 1px solid rgba(255, 17, 17, 0.3);
            font-weight: 600;
        }
        
        .icon {
            width: 20px;
            height: 20px;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        .logo {
      width: 20%;       /* adjust size */
      height: 20%;
      border-radius: 8px; /* optional rounded corners */
      object-fit: contain; /* keeps aspect ratio */
    }
    </style>
</head>
<body>

<!-- App bar -->
<header class="appbar">
    <div class="appbar-wrap">
        <a href="index.php" class="brand">
            <img src="Liked-tamil-news-logo-1 (2).png" alt="Portal Logo" class="logo" />
            <span class="title">Liked தமிழ்</span>
        </a>
        <!-- Search Form -->
        <form method="GET" action="search.php" class="search" role="search">
            <svg class="icon" viewBox="0 0 24 24" fill="none">
                <path d="M11 5a6 6 0 016 6c0 1.3-.41 2.5-1.11 3.48l4.32 4.32-1.41 1.41-4.32-4.32A6 6 0 1111 5z" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            <input type="search" name="q" placeholder="தேடல்…" aria-label="தேடல்" value="<?php echo htmlspecialchars($searchTerm); ?>" />
        </form>
        <div class="actions">
            <button class="btn primary" onclick="openSubscription()">
                <svg class="icon" viewBox="0 0 24 24" fill="none">
                    <path d="M12 3l9 6-9 6-9-6 9-6zM3 15l9 6 9-6" stroke="currentColor" stroke-width="1.5"/>
                </svg>
                Subscribe
            </button>
        </div>
    </div>
</header>

<!-- Category Navigation -->
<nav class="catbar" aria-label="Categories">
    <div class="catbar-wrap">
        <a href="index.php" class="chip">முகப்பு</a>
        <?php foreach ($categories as $category): ?>
            <a href="categories.php?id=<?php echo $category['id']; ?>" class="chip">
                <?php echo htmlspecialchars($category['name']); ?>
            </a>
        <?php endforeach; ?>
    </div>
</nav>

<!-- Main Content -->
<main class="main-content">
    <div class="search-header">
        <h1 class="search-title">தேடல் முடிவுகள்</h1>
        <?php if (!empty($searchTerm)): ?>
            <div class="search-info">
                "<span class="search-term"><?php echo htmlspecialchars($searchTerm); ?></span>" என்பதற்கான தேடல் முடிவுகள்
            </div>
            <div class="results-count">
                <?php echo number_format($totalNews); ?> முடிவுகள் கிடைத்தன
            </div>
        <?php endif; ?>
    </div>

    <div class="grid-news">
        <?php if (!empty($searchTerm) && !empty($news)): ?>
            <?php foreach ($news as $item): ?>
                <article class="news-card">
                    <a href="news-detail.php?id=<?php echo $item['id']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="news-thumb">
                            <?php
                            // Get image URL
                            if (!empty($item['image_path'])) {
                                if (strpos($item['image_path'], 'http') === 0) {
                                    $imageSrc = $item['image_path'];
                                } else {
                                    $imageSrc = $base_url . $item['image_path'];
                                }
                            } elseif (!empty($item['image'])) {
                                $imageSrc = $base_url . 'uploads/news/' . htmlspecialchars($item['image']);
                            } else {
                                $imageSrc = 'https://picsum.photos/id/' . rand(1000, 1100) . '/800/500';
                            }
                            ?>
                            <img src="<?php echo $imageSrc; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" />
                            <?php if (!empty($item['category_names'])): ?>
                                <span class="badge">
                                    <?php 
                                    $categories = explode(', ', $item['category_names']);
                                    echo htmlspecialchars(trim($categories[0])); 
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="news-content">
                            <h3 class="news-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                            <div class="news-meta">
                                <?php 
                                $publishTime = new DateTime($item['published_at'] ?: $item['created_at']);
                                $now = new DateTime();
                                $interval = $now->diff($publishTime);
                                
                                if ($interval->days > 0) {
                                    echo $interval->days . ' நாட்கள்';
                                } elseif ($interval->h > 0) {
                                    echo $interval->h . ' மணி';
                                } else {
                                    echo $interval->i . ' நி';
                                }
                                ?> முன்
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
        <?php elseif (!empty($searchTerm)): ?>
            <div class="no-results">
                <h3>முடிவுகள் இல்லை</h3>
                <p>"<?php echo htmlspecialchars($searchTerm); ?>" என்பதற்கு எந்த முடிவுகளும் கிடைக்கவில்லை.</p>
                <a href="index.php" class="btn primary">முகப்புக்குத் திரும்பு</a>
            </div>
        <?php else: ?>
            <div class="no-results">
                <h3>தேடல் வார்த்தையை உள்ளிடவும்</h3>
                <p>தேடல் பட்டியில் வார்த்தையை உள்ளிட்டு முடிவுகளைப் பெறவும்.</p>
                <a href="index.php" class="btn primary">முகப்புக்குத் திரும்பு</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($searchTerm) && $totalPages > 1): ?>
        <nav class="pagination" aria-label="பக்கமாற்றம்">
            <?php if ($page > 1): ?>
                <a href="search.php?q=<?php echo urlencode($searchTerm); ?>&page=<?php echo $page - 1; ?>" class="page" aria-label="முந்தைய பக்கம்">
                    ‹ முந்தைய
                </a>
            <?php endif; ?>
            
            <?php 
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            
            if ($startPage > 1): ?>
                <a href="search.php?q=<?php echo urlencode($searchTerm); ?>&page=1" class="page">1</a>
                <?php if ($startPage > 2): ?>
                    <span class="page" style="background: transparent; border: none; color: var(--muted); cursor: default;">...</span>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="search.php?q=<?php echo urlencode($searchTerm); ?>&page=<?php echo $i; ?>" 
                   class="page <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($endPage < $totalPages): ?>
                <?php if ($endPage < $totalPages - 1): ?>
                    <span class="page" style="background: transparent; border: none; color: var(--muted); cursor: default;">...</span>
                <?php endif; ?>
                <a href="search.php?q=<?php echo urlencode($searchTerm); ?>&page=<?php echo $totalPages; ?>" class="page">
                    <?php echo $totalPages; ?>
                </a>
            <?php endif; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="search.php?q=<?php echo urlencode($searchTerm); ?>&page=<?php echo $page + 1; ?>" class="page" aria-label="அடுத்த பக்கம்">
                    அடுத்த ›
                </a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</main>

<!-- Subscription Modal -->
<div class="subscription-modal" id="subscriptionModal">
    <div class="subscription-content">
        <button class="close-modal" onclick="closeSubscription()">&times;</button>
        <h3>Subscribe to Liked தமிழ்</h3>
        
        <?php if ($subscriptionSuccess): ?>
            <div class="subscription-success">
                நன்றி! உங்கள் சந்தா வெற்றிகரமாக பதிவு செய்யப்பட்டது.
            </div>
        <?php elseif ($subscriptionError): ?>
            <div class="subscription-error">
                <?php echo htmlspecialchars($subscriptionError); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="subscription-form">
            <input type="email" name="email" placeholder="உங்கள் மின்னஞ்சல்" required 
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            <input type="hidden" name="subscribe" value="1">
            <button type="submit">Subscribe</button>
        </form>
        <div style="font-size: 12px; color: var(--muted); text-align: center; margin-top: 15px;">
            புதிய செய்திகள் மற்றும் புதுப்பிப்புகளை பெறவும்
        </div>
    </div>
</div>

<!-- Desktop Footer -->
<footer class="likedtamil-footer">
    <div class="likedtamil-footer-wrap">
        © <?php echo date('Y'); ?> All Rights Reserved by <a href="https://likedtamil.lk" target="_blank">Likedtamil.lk</a> | Developed by <a href="https://webbuilders.lk" target="_blank">Webbuilders.lk</a>
    </div>
</footer>

<!-- Mobile Footer -->
<footer class="mobile-footer" role="navigation" aria-label="மொபைல் அடிக்குறிப்பு">
    <div class="foot-wrap">
        <a href="index.php" class="foot-item">
            <svg class="foot-icon" viewBox="0 0 24 24" fill="none"><path d="M3 10l9-7 9 7v9a2 2 0 01-2 2H5a2 2 0 01-2-2v-9z" stroke="#fff" stroke-width="1.6"/></svg>
            <span class="foot-label">முகப்பு</span>
        </a>
        <a href="categories.php" class="foot-item">
            <svg class="foot-icon" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 12h16M4 18h16" stroke="#fff" stroke-width="1.6"/></svg>
            <span class="foot-label">பிரிவுகள்</span>
        </a>
        <a href="#" class="foot-item" onclick="event.preventDefault(); openMobileSearch();">
            <svg class="foot-icon" viewBox="0 0 24 24" fill="none"><path d="M11 5a6 6 0 016 6c0 1.3-.41 2.5-1.11 3.48l4.32 4.32-1.41 1.41-4.32-4.32A6 6 0 1111 5z" stroke="#fff" stroke-width="1.6"/></svg>
            <span class="foot-label">தேடல்</span>
        </a>
        <a href="about.php" class="foot-item">
            <svg class="foot-icon" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="6" stroke="#fff" stroke-width="1.6"/></svg>
            <span class="foot-label">சுயவிவரம்</span>
        </a>
        <a href="video.php" class="foot-item">
            <svg class="foot-icon" viewBox="0 0 24 24" fill="none"><path d="M6 19l6-6 6 6M6 12l6-6 6 6" stroke="#fff" stroke-width="1.6"/></svg>
            <span class="foot-label">வீடியோ</span>
        </a>
    </div>
</footer>

<script>
    // Mobile search functionality
    function openMobileSearch() {
        const searchModal = document.createElement('div');
        searchModal.className = 'subscription-modal';
        searchModal.style.display = 'flex';
        searchModal.style.zIndex = '1001';
        searchModal.innerHTML = `
            <div class="subscription-content" style="max-width: 90%;">
                <button class="close-modal" onclick="this.parentElement.parentElement.remove()">&times;</button>
                <h3 style="color: var(--yellow); text-align: center; margin-bottom: 20px;">தேடல்</h3>
                <form method="GET" action="search.php" class="search" style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <input type="search" name="q" placeholder="தேடல்..." style="flex: 1; padding: 12px; border-radius: var(--radius-sm); background: var(--glass); border: var(--border); color: var(--text);" autofocus>
                    <button type="submit" style="background: linear-gradient(180deg, var(--red), #cc0f0f); color: white; border: none; padding: 12px 20px; border-radius: var(--radius-sm); cursor: pointer;">தேடு</button>
                </form>
            </div>
        `;
        document.body.appendChild(searchModal);
    }

    // Subscription modal functions
    function openSubscription() {
        document.getElementById('subscriptionModal').style.display = 'flex';
        // Clear any previous messages
        const successMsg = document.querySelector('.subscription-success');
        const errorMsg = document.querySelector('.subscription-error');
        if (successMsg) successMsg.style.display = 'none';
        if (errorMsg) errorMsg.style.display = 'none';
    }
    
    function closeSubscription() {
        document.getElementById('subscriptionModal').style.display = 'none';
        // Reload page to clear form if subscription was successful
        <?php if ($subscriptionSuccess): ?>
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        <?php endif; ?>
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('subscriptionModal');
        if (event.target == modal) {
            closeSubscription();
        }
    }
    
    // Show modal if subscription was attempted
    <?php if ($subscriptionSuccess || $subscriptionError): ?>
        document.addEventListener('DOMContentLoaded', function() {
            openSubscription();
        });
    <?php endif; ?>

    // Search form submission
    document.querySelector('.search').addEventListener('submit', function(e) {
        const searchInput = this.querySelector('input[name="q"]');
        if (!searchInput.value.trim()) {
            e.preventDefault();
            alert('தேடல் வார்த்தையை உள்ளிடவும்');
            searchInput.focus();
        }
    });

    // Mobile footer search
    document.querySelectorAll('.foot-item').forEach(item => {
        if (item.querySelector('.foot-label')?.textContent === 'தேடல்') {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                openMobileSearch();
            });
        }
    });
</script>

</body>
</html>