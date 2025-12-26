<?php
// Database connection
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Fetch all active categories for navigation
$categoryQuery = "SELECT * FROM categories WHERE status = 'active' ORDER BY id";
$categoryStmt = $db->prepare($categoryQuery);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected date from URL or use today's date
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$dateObj = new DateTime($selectedDate);
$formattedDate = $dateObj->format('Y-m-d');

// Build WHERE clause based on filter
$filterWhere = '';
if (!empty($filter) && isset($categoryMap[$filter])) {
    $categoryName = $categoryMap[$filter];
    $filterWhere = " AND FIND_IN_SET('" . $categoryName . "', n.categories) > 0";
}

// Get page number for pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 60; // News per page
$offset = ($page - 1) * $limit;

// Fetch total news count for selected date with filter
if ($selectedDate == date('Y-m-d')) {
    // Today's news count with filter
    $countQuery = "SELECT COUNT(*) as total FROM news WHERE status = 'published'" . $filterWhere;
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute();
} else {
    // Specific date news count with filter
    $countQuery = "SELECT COUNT(*) as total FROM news WHERE DATE(published_at) = ? AND status = 'published'" . $filterWhere;
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute([$formattedDate]);
}
$totalResult = $countStmt->fetch(PDO::FETCH_ASSOC);
$totalNews = $totalResult['total'];
$totalPages = ceil($totalNews / $limit);

// Fetch news for the selected date with subcategories and filter
if ($selectedDate == date('Y-m-d')) {
    // Today's news with filter
    $newsQuery = "SELECT n.*, 
                  (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') FROM categories c WHERE FIND_IN_SET(c.id, n.categories) > 0) as category_names,
                  (SELECT GROUP_CONCAT(c.subcategories SEPARATOR ', ') FROM categories c WHERE FIND_IN_SET(c.id, n.categories) > 0) as subcategories_list
                  FROM news n 
                  WHERE n.status = 'published'" . $filterWhere . "
                  ORDER BY n.published_at DESC 
                  LIMIT $limit OFFSET $offset";
    $newsStmt = $db->prepare($newsQuery);
    $newsStmt->execute();
} else {
    // Specific date news with filter
    $newsQuery = "SELECT n.*, 
                  (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') FROM categories c WHERE FIND_IN_SET(c.id, n.categories) > 0) as category_names,
                  (SELECT GROUP_CONCAT(c.subcategories SEPARATOR ', ') FROM categories c WHERE FIND_IN_SET(c.id, n.categories) > 0) as subcategories_list
                  FROM news n 
                  WHERE DATE(n.published_at) = ? AND n.status = 'published'" . $filterWhere . "
                  ORDER BY n.published_at DESC 
                  LIMIT $limit OFFSET $offset";
    $newsStmt = $db->prepare($newsQuery);
    $newsStmt->execute([$formattedDate]);
}
$news = $newsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch featured news for slider (first 3 published news)
$featuredQuery = "SELECT n.*, 
                  (SELECT name FROM categories WHERE FIND_IN_SET(id, n.categories) > 0 LIMIT 1) as category_name
                  FROM news n 
                  WHERE n.status = 'published' 
                  ORDER BY n.published_at DESC 
                  LIMIT 3";
$featuredStmt = $db->prepare($featuredQuery);
$featuredStmt->execute();
$featuredNews = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch today's highlights (news from today) with filter
$highlightsQuery = "SELECT n.*, 
                    (SELECT name FROM categories WHERE FIND_IN_SET(id, n.categories) > 0 LIMIT 1) as category_name
                    FROM news n 
                    WHERE DATE(n.published_at) = ? AND n.status = 'published'" . $filterWhere . "
                    ORDER BY n.published_at DESC 
                    LIMIT 2";
$highlightsStmt = $db->prepare($highlightsQuery);
$highlightsStmt->execute([$formattedDate]);
$highlights = $highlightsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch breaking news for ticker (recent 4 news)
$tickerQuery = "SELECT * FROM news 
                WHERE status = 'published' 
                ORDER BY published_at DESC 
                LIMIT 4";
$tickerStmt = $db->prepare($tickerQuery);
$tickerStmt->execute();
$tickerNews = $tickerStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle subscription form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe'])) {
    $email = $_POST['email'] ?? '';
    
    // Save to database (create subscribers table first)
    // CREATE TABLE subscribers (id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
    
    $subscribeQuery = "INSERT INTO subscribers (email) VALUES (?)";
    $subscribeStmt = $db->prepare($subscribeQuery);
    $subscribeStmt->execute([$email]);
    
    $subscriptionSuccess = true;
}

// Function to check if a date has news
function hasNewsForDate($db, $date) {
    $query = "SELECT COUNT(*) as count FROM news WHERE DATE(published_at) = ? AND status = 'published'";
    $stmt = $db->prepare($query);
    $stmt->execute([$date]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] > 0;
}

require 'config/config.php'; // To get $base_url
?>

<!DOCTYPE html>
<html lang="ta">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Liked தமிழ்</title>

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
    html, body { height: 100%; width:100% }
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
    .badge {
      width: 40px; height: 40px; border-radius: 12px; overflow:hidden; position:relative;
      box-shadow: var(--shadow);
      background:
        conic-gradient(from 220deg, var(--red), var(--yellow), var(--red));
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
    .actions { display:flex; gap: 10px }
    .btn {
      display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:12px;
      background: var(--card); border: var(--border); color: var(--text); cursor:pointer;
      transition: transform var(--trans), box-shadow var(--trans), background var(--trans);
      text-decoration: none;
    }
    .btn:hover { transform: translateY(-2px); box-shadow: var(--shadow) }
    .btn.primary {
      background: linear-gradient(180deg, var(--red), #cc0f0f);
      color: #fff; border: 0;
    }
    .icon { width: 20px; height: 20px }

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
    }
    .chip:hover { transform: translateY(-2px); background: rgba(255,17,17,.18) }
    .chip.active { background: linear-gradient(180deg, var(--red), #d10f0f); color: #fff; border: 0 }

    /* Ticker */
    .ticker {
      background: var(--yellow); color: var(--black);
      border-bottom: 2px solid rgba(0,0,0,.25);
    }
    .ticker-wrap {
      max-width: 1200px; margin: 0 auto; padding: 8px clamp(14px, 3vw, 24px);
      display:grid; grid-template-columns: auto 1fr auto; gap: 12px; align-items:center;
    }
    .tag-chip {
      background: var(--black); color: var(--yellow);
      border-radius: 999px; padding:6px 10px; font-weight: 700; border: 1px solid rgba(255,255,255,.08)
    }
    .marquee { overflow: hidden; height: 28px; }
    .marquee-track {
      display:inline-flex; gap: 28px; white-space: nowrap;
      animation: track 24s linear infinite;
    }
    .marquee:hover .marquee-track { animation-play-state: paused }
    @keyframes track { from { transform: translateX(0) } to { transform: translateX(-50%) } }
    .dot { width:6px; height:6px; border-radius:50%; display:inline-block; background: rgba(0,0,0,.5); margin: 0 10px }

    /* Hero slider */
    .hero {
      max-width: 1200px; margin: 18px auto; padding: 0 clamp(14px, 3vw, 24px);
      display:grid; grid-template-columns: 1.2fr .8fr; gap: 16px;
    }
    @media (max-width: 980px) { .hero { grid-template-columns: 1fr } }

    .slider {
      position: relative; border-radius: var(--radius); overflow: hidden;
      background: var(--card); border: var(--border); box-shadow: var(--shadow);
    }
    .slide { position:absolute; inset:0; opacity:0; transform: scale(1.02); transition: opacity .6s ease, transform .8s ease }
    .slide.active { opacity:1; transform: scale(1) }
    .slide img { width:100%; height: 420px; object-fit: cover; display:block; filter: contrast(1.02) saturate(1.05) }
    .slide-grad { position:absolute; inset:0; background: linear-gradient(180deg, rgba(0,0,0,.05), rgba(0,0,0,.65) 65%) }
    .slide-info { position:absolute; left: 18px; right: 18px; bottom: 16px; color:#fff; display:flex; flex-direction:column; gap:8px }
    .pill { padding:6px 10px; border-radius:999px; font-size:12px; background: rgba(0,0,0,.45); border: 1px solid rgba(255,255,255,.22) }
    .slide-title { font-size: clamp(20px, 2.2vw, 28px); font-weight:800; line-height:1.25 }
    .slide-meta { font-size: 13px; opacity:.9 }
    .slider-nav { position:absolute; right: 12px; bottom: 12px; display:flex; gap:8px }
    .nav-btn { width:38px; height:38px; border-radius:12px; background: rgba(0,0,0,.5); color:#fff; border: 1px solid rgba(255,255,255,.25); display:grid; place-items:center; cursor:pointer; transition: transform var(--trans), background var(--trans) }
    .nav-btn:hover { transform: translateY(-1px); background: rgba(0,0,0,.65) }

    /* Side panel: calendar + highlights */
    .panel { display:flex; flex-direction: column; gap: 16px }
    .card {
      background: var(--card); border: var(--border);
      border-radius: var(--radius); box-shadow: var(--shadow);
      padding: 14px;
    }

    /* Calendar */
    .calendar { display:grid; gap: 10px }
    .cal-head { display:flex; justify-content: space-between; align-items: center }
    .cal-title { font-weight:800; color: var(--yellow) }
    .cal-grid { display:grid; grid-template-columns: repeat(7, 1fr); gap: 6px }
    .cal-day { text-align:center; font-size: 12px; color: var(--muted) }
    .cal-date {
      text-align:center; font-size: 13px; padding: 8px 0; border-radius: 10px; color: var(--text);
      background: var(--glass); border: var(--border); cursor: pointer;
      transition: background var(--trans), transform var(--trans), box-shadow var(--trans);
      text-decoration: none;
      display: block;
      position: relative;
    }
    .cal-date:hover { background: rgba(255,252,0,.14); transform: translateY(-2px); box-shadow: var(--shadow) }
    .cal-date.today { outline: 0 0 0 2px var(--yellow); font-weight: 700 }
    .cal-date.selected { background: linear-gradient(180deg, var(--red), #cc0f0f); color: #fff; border: 0 }
    .cal-date.has-news::after {
      content: '';
      position: absolute;
      bottom: 2px;
      left: 50%;
      transform: translateX(-50%);
      width: 4px;
      height: 4px;
      background-color: var(--yellow);
      border-radius: 50%;
    }

    /* Sections */
    .section { max-width: 1200px; margin: 8px auto 20px; padding: 0 clamp(14px, 3vw, 24px) }
    .section-head { display:flex; justify-content: space-between; align-items: baseline; margin-bottom: 12px }
    .section-title { font-weight:800; font-size: clamp(18px, 2vw, 22px) }

    /* Grid (desktop 3+, mobile 2-per-row) */
    .grid-news {
      display:grid; grid-template-columns: repeat(4, 1fr); gap: 14px;
    }
    @media (max-width: 1120px) { .grid-news { grid-template-columns: repeat(3, 1fr) } }
    @media (max-width: 980px)  { .grid-news { grid-template-columns: repeat(2, 1fr) } } /* two per row on tablets */
    @media (max-width: 640px)  { .grid-news { grid-template-columns: 1fr 1fr } }       /* strictly 2 per row on mobile */

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
      padding:6px 10px; border-radius:999px; background: rgba(0,0,0,.55); color:#fff; font-size:12px; border: 1px solid rgba(255,255,255,.25)
    }
    .news-content { padding: 12px 12px 14px }
    .news-title { font-weight:700; font-size: 16px; line-height:1.4; margin: 4px 0 6px }
    .news-meta { font-size: 12px; color: var(--muted); display:flex; gap:8px; align-items:center }
    .readmore { margin-top: 10px; display:inline-flex; align-items:center; gap:8px; color: var(--yellow); font-weight:700; text-decoration:none }
    .readmore::after { content:'→'; transition: transform var(--trans) }
    .news-card:hover .readmore::after { transform: translateX(3px) }

    .page[style*="background: transparent"] {
  background: transparent !important;
  border: none !important;
  color: var(--muted) !important;
  cursor: default !important;
}

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

    /* Facebook Feed Section */
    .facebook-feed-main {
      margin: 40px 0 30px;
      width: 100%;
    }
    
    .fb-feed-header {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 14px 16px;
      background: linear-gradient(90deg, rgba(24,119,242,0.12), rgba(24,119,242,0.03));
      border-radius: var(--radius) var(--radius) 0 0;
      border-bottom: var(--border);
    }
    
    .fb-logo {
      width: 36px;
      height: 36px;
      background: linear-gradient(135deg, var(--fb-blue), #0A5BC4);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 700;
      font-size: 16px;
      box-shadow: 0 4px 12px rgba(24, 119, 242, 0.3);
    }
    
    .fb-header-info {
      flex: 1;
    }
    
    .fb-page-name {
      font-weight: 700;
      font-size: 16px;
      margin-bottom: 2px;
      color: var(--text);
    }
    
    .fb-follower-count {
      font-size: 13px;
      color: var(--muted);
    }
    
    .follow-btn {
      background: var(--fb-blue);
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 6px;
      font-weight: 600;
      font-size: 13px;
      cursor: pointer;
      transition: transform var(--trans), opacity var(--trans), box-shadow var(--trans);
      text-decoration: none;
      display: inline-block;
    }
    
    .follow-btn:hover {
      opacity: 0.9;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(24, 119, 242, 0.3);
    }
    
    .fb-feed-content {
      padding: 16px;
      max-height: 400px;
      overflow-y: auto;
      scrollbar-width: thin;
      scrollbar-color: var(--glass) transparent;
    }
    
    .fb-feed-content::-webkit-scrollbar {
      width: 6px;
    }
    
    .fb-feed-content::-webkit-scrollbar-track {
      background: transparent;
    }
    
    .fb-feed-content::-webkit-scrollbar-thumb {
      background-color: var(--glass);
      border-radius: 20px;
    }
    
    .fb-post {
      background: var(--card);
      border-radius: var(--radius-sm);
      border: var(--border);
      padding: 14px;
      margin-bottom: 14px;
      transition: transform var(--trans), box-shadow var(--trans), background var(--trans);
    }
    
    .fb-post:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow);
      background: var(--card-hi);
    }
    
    .fb-post-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 12px;
    }
    
    .fb-avatar {
      width: 36px;
      height: 36px;
      background: linear-gradient(135deg, var(--red), var(--yellow));
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--black);
      font-weight: 700;
      font-size: 14px;
    }
    
    .fb-post-info {
      flex: 1;
    }
    
    .fb-post-author {
      font-weight: 600;
      font-size: 14px;
      margin-bottom: 2px;
    }
    
    .fb-post-time {
      font-size: 11px;
      color: var(--muted);
    }
    
    .fb-post-text {
      font-size: 14px;
      line-height: 1.5;
      margin-bottom: 12px;
      color: var(--text);
    }
    
    .fb-post-image {
      width: 100%;
      border-radius: 8px;
      margin-bottom: 12px;
      overflow: hidden;
    }
    
    .fb-post-image img {
      width: 100%;
      height: auto;
      border-radius: 8px;
      transition: transform .5s ease;
    }
    
    .fb-post:hover .fb-post-image img {
      transform: scale(1.03);
    }
    
    .fb-post-stats {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 10px;
      border-top: var(--border);
    }
    
    .fb-likes {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      color: var(--muted);
    }
    
    .fb-like-icon {
      width: 16px;
      height: 16px;
      background: linear-gradient(135deg, var(--fb-blue), #0A5BC4);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 10px;
    }
    
    .fb-view-link {
      font-size: 12px;
      color: var(--fb-blue);
      text-decoration: none;
      font-weight: 600;
      transition: color var(--trans);
    }
    
    .fb-view-link:hover {
      color: var(--yellow);
    }
    
    .fb-no-posts {
      text-align: center;
      padding: 20px;
      color: var(--muted);
      font-style: italic;
    }
    
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
    }
    .foot-wrap { max-width: 1200px; margin: 0 auto; padding: 10px clamp(12px, 4vw, 18px); display:flex; justify-content: space-between; gap: 6px }
    .foot-item {
      flex:1; display:flex; flex-direction: column; align-items:center; gap: 6px;
      color: #fff; text-decoration:none; padding:8px; border-radius: 12px;
      transition: transform var(--trans), background var(--trans)
    }
    .foot-item:hover, .foot-item.active { background: rgba(0,0,0,.18); transform: translateY(-2px) }
    .foot-icon { width: 22px; height: 22px }
    .foot-label { font-size: 12px; font-weight:700 }

    /* Subtle entrance */
    .fade-in-up { opacity:0; transform: translateY(12px); animation: in .6s var(--trans) forwards }
    @keyframes in { to { opacity:1; transform: translateY(0) } }
    
    /* News Count Badge */
    .news-count {
      background: var(--yellow);
      color: var(--black);
      padding: 2px 6px;
      border-radius: 10px;
      font-size: 11px;
      font-weight: 700;
      margin-left: 5px;
    }
    
    /* No News Message */
    .no-news {
      text-align: center;
      padding: 40px 20px;
      color: var(--muted);
      grid-column: 1 / -1;
    }
    
    /* Loading spinner */
    .loading {
      text-align: center;
      padding: 20px;
      color: var(--muted);
    }

    /* Pagination */
    .pagination { display:flex; justify-content:center; gap:8px; margin:32px 0 }
    .page { padding:8px 14px; border-radius:10px; background: var(--card); border: var(--border); color: var(--text); cursor:pointer; transition: transform var(--trans), background var(--trans) }
    .page:hover { transform: translateY(-2px); background: var(--card-hi) }
    .page.active { background: linear-gradient(180deg, var(--red), #cc0f0f); color: #fff; border: 0 }

    /* Pagination */
.pagination {
  display: flex;
  gap: 8px;
  justify-content: center;
  margin: 18px 0 100px;
  flex-wrap: wrap; /* Responsive */
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
  font-family: "Noto Sans Tamil", Inter, sans-serif;
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

/* Mobile pagination */
@media (max-width: 640px) {
  .pagination {
    gap: 6px;
    margin: 18px 0 80px;
  }
  .page {
    padding: 8px 12px;
    font-size: 14px;
    min-width: 36px;
  }
}
/* Mobile responsiveness */
@media (max-width: 640px) {
  .appbar-wrap {
    grid-template-columns: auto 1fr;
    gap: 12px;
  }
  .search {
    display: none; /* Hide search on mobile - use icon in footer */
  }
  .actions {
    display: none; /* Hide subscribe button on mobile */
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
  
  .hero {
    margin: 12px auto;
    gap: 12px;
  }
  
  .slide img {
    height: 300px;
  }
  
  .slide-info {
    left: 12px;
    right: 12px;
    bottom: 12px;
  }
  .slide-title {
    font-size: 18px;
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
}

.subscription-content h3 {
  color: var(--yellow);
  margin-bottom: 20px;
  text-align: center;
}

.subscription-form {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.subscription-form input {
  flex: 1;
  padding: 12px;
  border-radius: var(--radius-sm);
  background: var(--glass);
  border: var(--border);
  color: var(--text);
  outline: none;
}

.subscription-form button {
  background: linear-gradient(180deg, var(--red), #cc0f0f);
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: var(--radius-sm);
  cursor: pointer;
  font-weight: 600;
}

.close-modal {
  background: transparent;
  border: none;
  color: var(--muted);
  cursor: pointer;
  float: right;
  font-size: 20px;
}

.subscription-success {
  color: var(--yellow);
  text-align: center;
  padding: 10px;
  display: none;
}
    .section-head { display:flex; justify-content: space-between; align-items: baseline; margin-bottom: 12px }

    /* Filter buttons active state */
    .filter-btn.active {
      background: linear-gradient(180deg, var(--red), #cc0f0f);
      color: #fff;
      border: 0;
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
      <input type="search" name="q" placeholder="தேடல்…" aria-label="தேடல்" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" />
    </form>
  </div>
</header>

  <!-- Category Navigation -->
  <nav class="catbar" aria-label="Categories">
    <div class="catbar-wrap">
      <a href="index.php" class="chip <?php echo (!isset($_GET['category'])) ? 'active' : ''; ?>">முகப்பு</a>
      <?php foreach ($categories as $category): ?>
        <?php 
        // Count news in this category using FIND_IN_SET
        $countQuery = "SELECT COUNT(*) as count FROM news 
                       WHERE FIND_IN_SET(?, categories) > 0 
                       AND status = 'published'";
        $countStmt = $db->prepare($countQuery);
        $countStmt->execute([$category['id']]);
        $count = $countStmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <a href="categories.php?id=<?php echo $category['id']; ?>" class="chip <?php echo (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'active' : ''; ?>">
          <?php echo htmlspecialchars($category['name']); ?>
          <?php if ($count['count'] > 0): ?>
            <span class="news-count"><?php echo $count['count']; ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  </nav>

  <!-- Breaking news ticker -->
  <section class="ticker">
    <div class="ticker-wrap">
      <span class="tag-chip">Breaking</span>
      <div class="marquee" aria-label="Breaking news">
        <div class="marquee-track" id="tickerTrack">
          <span>சிறப்பு: புதிய திட்டம் அறிவிப்பு வெளியீடு<span class="dot"></span></span>
          <span>விளையாட்டு: கடைசி ஓவரில் த்ரில்லர் வெற்றி<span class="dot"></span></span>
          <span>தொழில்நுட்பம்: AI கருவி வெளியீடு<span class="dot"></span></span>
          <span>உலக செய்திகள்: வர்த்தக உடன்பாடு கையெழுத்து<span class="dot"></span></span>
          <!-- duplicate for seamless loop -->
          <span>சிறப்பு: புதிய திட்டம் அறிவிப்பு வெளியீடு<span class="dot"></span></span>
          <span>விளையாட்டு: கடைசி ஓவரில் த்ரில்லர் வெற்றி<span class="dot"></span></span>
          <span>தொழில்நுட்பம்: AI கருவி வெளியீடு<span class="dot"></span></span>
          <span>உலக செய்திகள்: வர்த்தக உடன்பாடு கையெழுத்து<span class="dot"></span></span>
        </div>
      </div>
      <span class="tag-chip">Live • 24/7</span>
    </div>
  </section>

  <!-- Hero: slider + side panel -->
  <section class="hero">
    <!-- Slider -->
    <div class="slider fade-in-up" id="slider">
      <?php if (!empty($featuredNews)): ?>
        <?php foreach ($featuredNews as $index => $featured): ?>
          <article class="slide <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
            <img src="<?php echo !empty($featured['image']) ? $base_url . 'uploads/news/' . htmlspecialchars($featured['image']) : 'https://images.unsplash.com/photo-1504711434964-1e0193031639?q=80&w=1600&auto=format&fit=crop'; ?>" alt="<?php echo htmlspecialchars($featured['title']); ?>" />
            <div class="slide-grad"></div>
            <div class="slide-info">
              <div class="slide-cat">
              </div>
              <h2 class="slide-title"><?php echo htmlspecialchars($featured['title']); ?></h2>
              <div class="slide-meta">
                <?php 
                // CHANGED: Use published_at instead of created_at
                $publishTime = new DateTime($featured['published_at'] ?: $featured['created_at']);
                $now = new DateTime();
                $interval = $now->diff($publishTime);
                
                if ($interval->days > 0) {
                  echo $interval->days . ' நாட்கள் முன்';
                } elseif ($interval->h > 0) {
                  echo $interval->h . ' மணி முன்';
                } else {
                  echo $interval->i . ' நி முன்';
                }
                ?> • 
                <?php 
                $wordCount = str_word_count(strip_tags($featured['content']));
                $readingTime = ceil($wordCount / 200);
                echo max(1, $readingTime); 
                ?> நிமிடம் வாசிப்பு
              </div>
            </div>
            <div class="slider-nav">
              <button class="nav-btn" data-dir="-1" aria-label="முந்தைய ஸ்லைடு">‹</button>
              <button class="nav-btn" data-dir="1" aria-label="அடுத்த ஸ்லைடு">›</button>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- Default slides if no featured news -->
        <article class="slide active" data-index="0">
          <img src="https://images.unsplash.com/photo-1504711434964-1e0193031639?q=80&w=1600&auto=format&fit=crop" alt="உலக நகரங்கள்" />
          <div class="slide-grad"></div>
          <div class="slide-info">
            
            <h2 class="slide-title">Liked தமிழ் - உங்கள் நம்பகமான செய்தி மூலம்</h2>
            <div class="slide-meta">இப்போது • 3 நிமிடம் வாசிப்பு</div>
          </div>
        </article>
      <?php endif; ?>
    </div>

    <!-- Panel -->
    <aside class="panel">
      <!-- Calendar -->
      <div class="card calendar" id="calendar">
        <div class="cal-head">
          <div class="cal-title" id="calendarTitle">செய்தி காலண்டர்</div>
          <div style="display:flex; gap:6px">
            <button class="btn" id="prevMonth" aria-label="முந்தைய மாதம்">‹</button>
            <button class="btn" id="nextMonth" aria-label="அடுத்த மாதம்">›</button>
          </div>
        </div>
        <div class="cal-grid">
          <div class="cal-day">திங்கள்</div>
          <div class="cal-day">செவ்வாய்</div>
          <div class="cal-day">புதன்</div>
          <div class="cal-day">வியாழன்</div>
          <div class="cal-day">வெள்ளி</div>
          <div class="cal-day">சனி</div>
          <div class="cal-day">ஞாயிறு</div>
        </div>
        <div class="cal-grid" id="calDates" aria-label="காலண்டர் தேதிகள்"></div>
        <div style="color: var(--muted); font-size: 12px">தேதியைத் தட்டவும் — தலைப்புகளை வடிகட்டி</div>
      </div>
    </aside>
  </section>

  <!-- Main News Grid -->
  <section class="section">
    <div class="section-head">
      <div class="section-title">
        <?php 
        if ($selectedDate == date('Y-m-d')) {
          echo 'இன்றைய செய்திகள்';
        } else {
          echo date('d/m/Y', strtotime($selectedDate)) . ' செய்திகள்';
        }
        ?>
        <span style="font-size: 14px; color: var(--muted); margin-left: 10px;">
          (<?php echo $totalNews; ?> செய்திகள்)
        </span>
      </div>
      

    </div>

    <div class="grid-news" id="newsGrid">
      <?php if (!empty($news)): ?>
        <?php foreach ($news as $item): ?>
          <article class="news-card">
            <a href="news-detail.php?id=<?php echo $item['id']; ?>" style="text-decoration: none; color: inherit;">
              <div class="news-thumb">
                <?php
                // Get image from news_images table if exists
                $imageQuery = "SELECT image_path FROM news_images WHERE news_id = ? ORDER BY display_order LIMIT 1";
                $imageStmt = $db->prepare($imageQuery);
                $imageStmt->execute([$item['id']]);
                $newsImage = $imageStmt->fetch(PDO::FETCH_ASSOC);
                
                require 'config/config.php'; // To get $base_url
                $imageSrc = '';
                if (!empty($item['image'])) {
                  $imageSrc = $base_url . 'uploads/news/' . htmlspecialchars($item['image']);
                } elseif ($newsImage && !empty($newsImage['image_path'])) {
                  $imageSrc =  $base_url  . htmlspecialchars($newsImage['image_path']);
                } else {
                  $imageSrc = 'https://picsum.photos/id/' . rand(1000, 1100) . '/800/500';
                }
                ?>
                <img src="<?php echo $imageSrc; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" />
                <?php if (!empty($item['category_names'])): ?>
                  <span class="badge"><?php 
                    // Get first category name
                    $categories = explode(', ', $item['category_names']);
                    echo htmlspecialchars(trim($categories[0])); 
                  ?></span>
                <?php else: ?>
                  
                <?php endif; ?>
              </div>
              <div class="news-content">
                <h3 class="news-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                <?php if (!empty($item['subcategories_list'])): ?>
                  <div style="font-size: 11px; color: var(--muted); margin-bottom: 5px;">
                    <?php 
                    // Process subcategories
                    $subcategories = explode(', ', $item['subcategories_list']);
                    // Remove empty values and duplicates
                    $filteredSubs = array_filter(array_unique($subcategories));
                    if (!empty($filteredSubs)) {
                      echo implode(' • ', $filteredSubs);
                    }
                    ?>
                  </div>
                <?php endif; ?>
                <div class="news-meta">
                  <?php 
                  // CHANGED: Use published_at instead of created_at
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
                  
                </div>
                <div class="readmore">மேலும் படிக்க</div>
              </div>
            </a>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="no-news">
          <h3>செய்திகள் இல்லை</h3>
          <p><?php echo date('d/m/Y', strtotime($selectedDate)); ?> தேதிக்கு செய்திகள் இல்லை.</p>
          <a href="index.php" class="btn primary">இன்றைய செய்திகளைப் பார்க்க</a>
        </div>
      <?php endif; ?>
    </div>

    <!-- Facebook Feed Section -->
<div class="facebook-feed-main">
  <div class="card">
    <div class="fb-feed-header">
      <div class="fb-logo">f</div>
      <div class="fb-header-info">
        <div class="fb-page-name">Liked தமிழ்</div>
        <div class="fb-follower-count">12.5K பின்தொடர்பவர்கள் • 1,234 பதிவுகள்</div>
      </div>
      <a href="https://www.facebook.com/liked.tamil/" target="_blank" class="follow-btn">Follow</a>
    </div>
    
    
  </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
  <nav class="pagination" aria-label="பக்கமாற்றம்">
    <?php if ($page > 1): ?>
      <a href="index.php?date=<?php echo $selectedDate; ?>&filter=<?php echo $filter; ?>&page=<?php echo $page - 1; ?>" class="page" aria-label="முந்தைய பக்கம்">
        ‹ முந்தைய
      </a>
    <?php endif; ?>
    
    <?php 
    // Show limited page numbers
    $startPage = max(1, $page - 2);
    $endPage = min($totalPages, $page + 2);
    
    // Show first page if not in range
    if ($startPage > 1): ?>
      <a href="index.php?date=<?php echo $selectedDate; ?>&filter=<?php echo $filter; ?>&page=1" class="page">1</a>
      <?php if ($startPage > 2): ?>
        <span class="page" style="background: transparent; border: none; color: var(--muted); cursor: default;">...</span>
      <?php endif; ?>
    <?php endif; ?>
    
    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
      <a href="index.php?date=<?php echo $selectedDate; ?>&filter=<?php echo $filter; ?>&page=<?php echo $i; ?>" 
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
      <a href="index.php?date=<?php echo $selectedDate; ?>&filter=<?php echo $filter; ?>&page=<?php echo $totalPages; ?>" class="page">
        <?php echo $totalPages; ?>
      </a>
    <?php endif; ?>
    
    <?php if ($page < $totalPages): ?>
      <a href="index.php?date=<?php echo $selectedDate; ?>&filter=<?php echo $filter; ?>&page=<?php echo $page + 1; ?>" class="page" aria-label="அடுத்த பக்கம்">
        அடுத்த ›
      </a>
    <?php endif; ?>
  </nav>
<?php endif; ?>
  </section>

  <!-- Subscription Modal -->
  <div class="subscription-modal" id="subscriptionModal">
    <div class="subscription-content">
      <button class="close-modal" onclick="closeSubscription()">&times;</button>
      <h3>Subscribe to Liked தமிழ்</h3>
      <form method="POST" class="subscription-form">
        <input type="email" name="email" placeholder="உங்கள் மின்னஞ்சல்" required>
        <input type="hidden" name="subscribe" value="1">
        <button type="submit">Subscribe</button>
      </form>
      <div class="subscription-success" id="subscriptionSuccess">
        நன்றி! உங்கள் சந்தா வெற்றிகரமாக பதிவு செய்யப்பட்டது.
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
      <a href="index.php" class="foot-item active">
        <svg class="foot-icon" viewBox="0 0 24 24" fill="none"><path d="M3 10l9-7 9 7v9a2 2 0 01-2 2H5a2 2 0 01-2-2v-9z" stroke="#fff" stroke-width="1.6"/></svg>
        <span class="foot-label">முகப்பு</span>
      </a>
      <a href="categories.php" class="foot-item">
        <svg class="foot-icon" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 12h16M4 18h16" stroke="#fff" stroke-width="1.6"/></svg>
        <span class="foot-label">பிரிவுகள்</span>
      </a>
      <a href="search.php" class="foot-item">
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
    // Slider autoplay
    const slider = document.getElementById('slider');
    const slides = Array.from(slider?.querySelectorAll('.slide') || []);
    const navButtons = slider?.querySelectorAll('.nav-btn') || [];
    let current = 0, timer = null;

    if (slides.length > 0) {
      function showSlide(i) {
        slides.forEach(s => s.classList.remove('active'));
        slides[i].classList.add('active');
        current = i;
      }
      
      function next(dir=1) {
        const i = (current + dir + slides.length) % slides.length;
        showSlide(i);
      }
      
      function startAuto() { 
        if (slides.length > 1) {
          timer = setInterval(()=>next(1), 5500); 
        }
      }
      
      function stopAuto() { clearInterval(timer) }
      
      if (navButtons.length > 0) {
        navButtons.forEach(b => b.addEventListener('click', e => { 
          next(parseInt(e.currentTarget.dataset.dir,10)); 
          stopAuto(); 
          startAuto(); 
        }));
      }
      
      if (slider) {
        slider.addEventListener('mouseenter', stopAuto);
        slider.addEventListener('mouseleave', startAuto);
        startAuto();
      }
    }

    // Calendar render (Monday-first)
    const calDates = document.getElementById('calDates');
    const prevMonth = document.getElementById('prevMonth');
    const nextMonth = document.getElementById('nextMonth');
    const calTitleEl = document.getElementById('calendarTitle');

    let viewDate = new Date('<?php echo $selectedDate; ?>');
    let selectedDateObj = new Date('<?php echo $selectedDate; ?>');
    const today = new Date();

    // Function to get current filter from URL
    function getCurrentFilter() {
      const urlParams = new URLSearchParams(window.location.search);
      return urlParams.get('filter') || '';
    }

    // Function to load highlights for selected date
    function loadHighlightsForDate(date, filter = '') {
      const highlightsContainer = document.getElementById('highlightsContainer');
      const highlightsTitle = document.getElementById('highlightsTitle');
      
      // Show loading state
      highlightsContainer.innerHTML = '<div style="text-align:center; padding:20px; color:var(--muted);">ஏற்றுகிறது...</div>';
      
      // Update title
      const todayStr = new Date().toISOString().split('T')[0];
      const selectedDate = new Date(date);
      const dateFormatted = selectedDate.toLocaleDateString('ta-IN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      });
      
      if (date === todayStr) {
        highlightsTitle.textContent = 'இன்றைய முக்கியங்கள்';
      } else {
        highlightsTitle.textContent = dateFormatted + ' முக்கியங்கள்';
      }
      
      // AJAX call to fetch highlights for selected date
      fetch(`get-highlights.php?date=${date}&filter=${filter}`)
        .then(response => response.json())
        .then(data => {
          if (data.success && data.news.length > 0) {
            let html = '';
            data.news.forEach(item => {
              // Format time ago - using published_at
              const publishTime = new Date(item.published_at || item.created_at);
              const now = new Date();
              const diffMs = now - publishTime;
              const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
              const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
              
              let timeAgo = '';
              if (diffDays > 0) {
                timeAgo = diffDays + ' நாட்கள் முன்';
              } else if (diffHours > 0) {
                timeAgo = diffHours + ' மணி முன்';
              } else {
                timeAgo = Math.floor(diffMs / (1000 * 60)) + ' நி முன்';
              }
              
              // Calculate reading time
              const wordCount = item.content ? item.content.split(/\s+/).length : 0;
              const readingTime = Math.max(1, Math.ceil(wordCount / 200));
              
              // Get image
              const imageSrc = item.image || `https://picsum.photos/id/${Math.floor(Math.random() * 1000) + 1000}/800/500`;
              
              html += `
                <a class="news-card" href="news-detail.php?id=${item.id}">
                  <div class="news-thumb">
                    <img src="${imageSrc}" alt="${item.title}" />
                    
                  </div>
                  <div class="news-content">
                    <div class="news-title">${item.title}</div>
                    <div class="news-meta">
                      <span>${timeAgo}</span>
                      
                    </div>
                  </div>
                </a>
              `;
            });
            highlightsContainer.innerHTML = html;
          } else {
            const todayStr = new Date().toISOString().split('T')[0];
            const displayDate = date === todayStr ? 'இன்றைய' : dateFormatted;
            highlightsContainer.innerHTML = `<div style="text-align:center; padding:20px; color:var(--muted);">
              ${displayDate} முக்கிய செய்திகள் இல்லை
            </div>`;
          }
        })
        .catch(error => {
          console.error('Error loading highlights:', error);
          highlightsContainer.innerHTML = '<div style="text-align:center; padding:20px; color:var(--muted);">பிழை ஏற்பட்டது. மீண்டும் முயற்சிக்கவும்.</div>';
        });
    }

    // Function to load news grid for selected date
    function loadNewsForDate(date, filter = '') {
        const newsGrid = document.getElementById('newsGrid');
        const sectionTitle = document.querySelector('.section .section-title');
        
        // Show loading state
        newsGrid.innerHTML = '<div style="text-align:center; padding:40px; color:var(--muted); grid-column: 1/-1;">ஏற்றுகிறது...</div>';
        
        // AJAX call to fetch news for selected date
        fetch(`get-news-for-date.php?date=${date}&filter=${filter}&page=1`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update section title
                    if (sectionTitle) {
                        const dateObj = new Date(date);
                        const todayStr = new Date().toISOString().split('T')[0];
                        
                        let titleText = '';
                        if (date === todayStr) {
                            titleText = 'இன்றைய செய்திகள்';
                        } else {
                            const day = dateObj.getDate().toString().padStart(2, '0');
                            const month = (dateObj.getMonth() + 1).toString().padStart(2, '0');
                            const year = dateObj.getFullYear();
                            titleText = `${day}/${month}/${year} செய்திகள்`;
                        }
                        
                        sectionTitle.innerHTML = `${titleText} <span style="font-size: 14px; color: var(--muted); margin-left: 10px;">(${data.totalNews} செய்திகள்)</span>`;
                    }
                    
                    if (data.news.length > 0) {
                        let html = '';
                        data.news.forEach(item => {
                            // Format time ago - using published_at
                            const publishTime = new Date(item.published_at || item.created_at);
                            const now = new Date();
                            const diffMs = now - publishTime;
                            const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                            const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
                            
                            let timeAgo = '';
                            if (diffDays > 0) {
                                timeAgo = diffDays + ' நாட்கள் முன்';
                            } else if (diffHours > 0) {
                                timeAgo = diffHours + ' மணி முன்';
                            } else {
                                timeAgo = Math.floor(diffMs / (1000 * 60)) + ' நி முன்';
                            }
                            
                            // Calculate reading time
                            const wordCount = item.content ? item.content.split(/\s+/).length : 0;
                            const readingTime = Math.max(1, Math.ceil(wordCount / 200));
                            
                            // Get categories
                            const categories = item.category_names ? item.category_names.split(', ') : [];
                            const firstCategory = categories.length > 0 ? categories[0] : 'செய்தி';
                            
                            // Get proper image URL with fallback
                            let imageUrl = '';
                            if (item.image_path) {
                              if (item.image_path.startsWith('http')) {
                                imageUrl = item.image_path;
                              } else {
                                // normalize leading slash
                                const path = item.image_path.startsWith('/') ? item.image_path.slice(1) : item.image_path;
                                imageUrl = '<?php echo $base_url; ?>' + path;
                              }
                            } else if (item.image) {
                              if (item.image.startsWith('http')) {
                                imageUrl = item.image;
                              } else if (item.image.indexOf('uploads/news/') !== -1) {
                                const path = item.image.startsWith('/') ? item.image.slice(1) : item.image;
                                imageUrl = '<?php echo $base_url; ?>' + path;
                              } else {
                                imageUrl = '<?php echo $base_url; ?>uploads/news/' + item.image;
                              }
                            } else {
                              imageUrl = 'https://picsum.photos/800/500?random=' + Math.floor(Math.random() * 10000);
                            }
                            
                            html += `
                                <article class="news-card">
                                    <a href="news-detail.php?id=${item.id}" style="text-decoration: none; color: inherit;">
                                        <div class="news-thumb">
                                            <img src="${imageUrl}" alt="${item.title}" />
                                            <span class="badge">${firstCategory}</span>
                                        </div>
                                        <div class="news-content">
                                            <h3 class="news-title">${item.title}</h3>
                                            <div class="news-meta">
                                                <span>${timeAgo}</span>
                                                
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            `;
                        });
                        newsGrid.innerHTML = html;
                    } else {
                        const dateObj = new Date(date);
                        const day = dateObj.getDate().toString().padStart(2, '0');
                        const month = (dateObj.getMonth() + 1).toString().padStart(2, '0');
                        const year = dateObj.getFullYear();
                        
                        newsGrid.innerHTML = `<div style="text-align:center; padding:40px; color:var(--muted); grid-column: 1/-1;">
                            <h3>செய்திகள் இல்லை</h3>
                            <p>${day}/${month}/${year} தேதிக்கு செய்திகள் இல்லை.</p>
                            <a href="index.php" class="btn primary" style="margin-top: 10px;">இன்றைய செய்திகளைப் பார்க்க</a>
                        </div>`;
                    }
                } else {
                    newsGrid.innerHTML = '<div style="text-align:center; padding:40px; color:var(--muted); grid-column: 1/-1;">செய்திகளைப் பதிவிறக்க முடியவில்லை</div>';
                }
            })
            .catch(error => {
                console.error('Error loading news:', error);
                newsGrid.innerHTML = '<div style="text-align:center; padding:40px; color:var(--muted); grid-column: 1/-1;">பிழை ஏற்பட்டது. மீண்டும் முயற்சிக்கவும்.</div>';
            });
    }

    async function renderCalendar() {
      const year = viewDate.getFullYear();
      const month = viewDate.getMonth();
      
      // Format month name in Tamil
      const monthNames = [
        'ஜனவரி', 'பிப்ரவரி', 'மார்ச்', 'ஏப்ரல்', 
        'மே', 'ஜூன்', 'ஜூலை', 'ஆகஸ்ட்', 
        'செப்டம்பர்', 'அக்டோபர்', 'நவம்பர்', 'டிசம்பர்'
      ];
      
      calTitleEl.textContent = `${monthNames[month]} ${year}`;

      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const startIndex = (firstDay.getDay() + 6) % 7; // Monday start
      
      // Clear previous dates
      calDates.innerHTML = '';

      // Padding for days before month start
      for (let i = 0; i < startIndex; i++) {
        const pad = document.createElement('div');
        pad.className = 'cal-date';
        pad.style.visibility = 'hidden';
        pad.textContent = '';
        calDates.appendChild(pad);
      }
      
      // Create date cells - ONLY for current month dates (1 to lastDay.getDate())
      for (let d = 1; d <= lastDay.getDate(); d++) {
        const cell = document.createElement('a');
        cell.className = 'cal-date';
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        
        // Get current filter
        const currentFilter = getCurrentFilter();
        
        // Regular link for normal navigation
        cell.href = `index.php?date=${dateStr}&filter=${currentFilter}`;
        cell.textContent = d;
        cell.setAttribute('aria-label', `${d} ${monthNames[month]} ${year}`);
        
        // Check if this is today
        const isToday = d === today.getDate() && month === today.getMonth() && year === today.getFullYear();
        if (isToday) {
          cell.classList.add('today');
        }
        
        // Check if this is selected date
        const isSelected = d === selectedDateObj.getDate() && 
                          month === selectedDateObj.getMonth() && 
                          year === selectedDateObj.getFullYear();
        if (isSelected) {
          cell.classList.add('selected');
        }
        
        // Add click event to load news via AJAX and update URL
        cell.addEventListener('click', async function(e) {
          e.preventDefault();
          
          // Update selected date
          selectedDateObj = new Date(dateStr);
          
          // Update URL without reload
          const url = new URL(window.location);
          url.searchParams.set('date', dateStr);
          window.history.pushState({}, '', url);
          
          // Load news grid for this date first
          loadNewsForDate(dateStr, currentFilter);
          
          // Update main grid pagination links
          updatePaginationLinks(dateStr, currentFilter);
          
          // Reload calendar to update selected state
          await renderCalendar();
          
          // Scroll to main news section
          document.querySelector('.section').scrollIntoView({ behavior: 'smooth' });
        });
        
        calDates.appendChild(cell);
      }
      
      // Check if dates have news (after calendar is rendered)
      checkDatesWithNews(year, month);
    }

    // Function to check which dates have news (non-blocking, runs after calendar render)
    async function checkDatesWithNews(year, month) {
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      
      for (let d = 1; d <= lastDay.getDate(); d++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        
        try {
          const response = await fetch('check-date-news.php?date=' + dateStr);
          const data = await response.json();
          if (data.hasNews) {
            // Find the cell and add class
            const cells = document.querySelectorAll('.cal-date');
            cells.forEach(cell => {
              if (cell.textContent === String(d) && !cell.style.visibility) {
                cell.classList.add('has-news');
              }
            });
          }
        } catch (error) {
          console.error('Error checking date news:', error);
        }
      }
    }

    // Function to update pagination links
    function updatePaginationLinks(date, filter) {
      document.querySelectorAll('.pagination .page').forEach(link => {
        const href = link.getAttribute('href');
        if (href && href.includes('index.php')) {
          const url = new URL(href, window.location.origin);
          url.searchParams.set('date', date);
          url.searchParams.set('filter', filter);
          link.href = url.toString();
        }
      });
    }

    if (prevMonth && nextMonth) {
      prevMonth.addEventListener('click', () => { 
        viewDate.setMonth(viewDate.getMonth() - 1); 
        renderCalendar(); 
      });
      
      nextMonth.addEventListener('click', () => { 
        viewDate.setMonth(viewDate.getMonth() + 1); 
        renderCalendar(); 
      });
      
      renderCalendar();
    }

    // Ticker width check
    const track = document.getElementById('tickerTrack');
    function ensureTickerLoop() {
      if (!track) return;
      const width = track.scrollWidth;
      const container = track.parentElement.clientWidth;
      if (width < container * 2) track.innerHTML = track.innerHTML + track.innerHTML;
    }
    window.addEventListener('load', ensureTickerLoop);
    window.addEventListener('resize', ensureTickerLoop);

    // Filter button functionality
    document.querySelectorAll('.filter-btn').forEach(button => {
      button.addEventListener('click', function() {
        const filter = this.getAttribute('data-filter');
        const section = this.getAttribute('data-section');
        
        // Update URL with filter parameter
        const url = new URL(window.location);
        url.searchParams.set('filter', filter);
        window.history.pushState({}, '', url);
        
        // Update button active states in the same section
        const sectionButtons = document.querySelectorAll(`.filter-btn[data-section="${section}"]`);
        sectionButtons.forEach(btn => {
          btn.classList.remove('active');
        });
        this.classList.add('active');
        
        // If this is in main section, reload news with filter
        if (section === 'main') {
          const urlParams = new URLSearchParams(window.location.search);
          const date = urlParams.get('date') || new Date().toISOString().split('T')[0];
          loadNewsForDate(date, filter);
        }
      });
    });

    // Facebook Feed Simulation
    const facebookFeed = document.getElementById('facebookFeed');
    
    // Sample Facebook posts data
    const fbPosts = [
      {
        id: 1,
        author: "Liked தமிழ்",
        time: "2 hours ago",
        text: "இன்றைய சிறப்பு செய்தி: புதிய பொருளாதார திட்டம் அறிவிப்பு. முழு விவரங்களை எங்கள் வலைத்தளத்தில் படிக்கவும்.",
        image: "https://images.unsplash.com/photo-1588681664899-f142ff2dc9b1?q=80&w=800&auto=format&fit=crop",
        likes: 245,
        comments: 42,
        shares: 18
      },
      {
        id: 2,
        author: "Liked தமிழ்",
        time: "5 hours ago",
        text: "கடைசி ஓவரில் த்ரில்லர் வெற்றி! விளையாட்டு வீரர்களின் சாதனையைப் பாராட்டுகிறோம். #விளையாட்டு #கிரிக்கெட்",
        image: "https://images.unsplash.com/photo-1461896836934-ffe607ba8211?q=80&w=800&auto=format&fit=crop",
        likes: 189,
        comments: 31,
        shares: 12
      },
      {
        id: 3,
        author: "Liked தமிழ்",
        time: "1 day ago",
        text: "தொழில்நுட்ப உலகில் புதிய முன்னேற்றம்: AI கருவி வெளியீடு. இது எவ்வாறு உங்கள் அன்றாட வாழ்க்கையை மாற்றும்?",
        image: "https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?q=80&w=800&auto=format&fit=crop",
        likes: 312,
        comments: 56,
        shares: 24
      }
    ];

    // Function to render Facebook posts
    function renderFacebookPosts() {
      if (!facebookFeed) return;
      
      facebookFeed.innerHTML = '';
      
      if (fbPosts.length === 0) {
        facebookFeed.innerHTML = '<div class="fb-no-posts">No Facebook posts available</div>';
        return;
      }
      
      fbPosts.forEach(post => {
        const postElement = document.createElement('div');
        postElement.className = 'fb-post';
        postElement.innerHTML = `
          <div class="fb-post-header">
            <div class="fb-avatar">LT</div>
            <div class="fb-post-info">
              <div class="fb-post-author">${post.author}</div>
              <div class="fb-post-time">${post.time}</div>
            </div>
          </div>
          <div class="fb-post-text">${post.text}</div>
          ${post.image ? `<div class="fb-post-image">
            <img src="${post.image}" alt="Facebook post image">
          </div>` : ''}
          <div class="fb-post-stats">
            <div class="fb-likes">
              <span class="fb-like-icon">👍</span>
              <span>${post.likes}</span>
            </div>
            <a href="https://www.facebook.com/liked.tamil/" target="_blank" class="fb-view-link">
              View on Facebook →
            </a>
          </div>
        `;
        facebookFeed.appendChild(postElement);
      });
    }

    // Subscription modal functions
    function openSubscription() {
      document.getElementById('subscriptionModal').style.display = 'flex';
    }
    
    function closeSubscription() {
      document.getElementById('subscriptionModal').style.display = 'none';
    }
    
    // Show success message if subscription was successful
    <?php if (isset($subscriptionSuccess) && $subscriptionSuccess): ?>
      document.addEventListener('DOMContentLoaded', function() {
        openSubscription();
        document.getElementById('subscriptionSuccess').style.display = 'block';
      });
    <?php endif; ?>


    // Search functionality
    function handleSearch(event) {
        event.preventDefault();
        const searchInput = document.querySelector('.search input[name="q"]');
        const searchTerm = searchInput.value.trim();
        
        if (searchTerm) {
            // Redirect to search results page
            window.location.href = `search.php?q=${encodeURIComponent(searchTerm)}`;
        }
    }

    // Add event listener to search form
    document.querySelector('.search').addEventListener('submit', handleSearch);

    // Mobile search functionality
    document.querySelectorAll('.foot-item').forEach(item => {
        if (item.querySelector('.foot-label')?.textContent === 'தேடல்') {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                // Create search modal for mobile
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
            });
        }
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      renderFacebookPosts();
      ensureTickerLoop();
      renderCalendar();
    });
  </script>
</body>
</html>