<?php
// Database connection
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Fetch categories for navigation
$categoryQuery = "SELECT * FROM categories WHERE status = 'active' ORDER BY id";
$categoryStmt = $db->prepare($categoryQuery);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch breaking news for ticker
$tickerQuery = "SELECT * FROM news 
                WHERE status = 'published' 
                ORDER BY created_at DESC 
                LIMIT 4";
$tickerStmt = $db->prepare($tickerQuery);
$tickerStmt->execute();
$tickerNews = $tickerStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ta">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>சுயவிவரம் - Liked தமிழ்</title>
  
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

    * { box-sizing: border-box }
    html, body { height: 100% }
    body {
      margin: 0;
      font-family: "Noto Sans Tamil", Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      color: var(--text);
      background: var(--bg);
      background-attachment: fixed;
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
    }
    .cal-date:hover { background: rgba(255,252,0,.14); transform: translateY(-2px); box-shadow: var(--shadow) }
    .cal-date.today { outline: 0 0 0 2px var(--yellow); font-weight: 700 }
    .cal-date.selected { background: linear-gradient(180deg, var(--red), #cc0f0f); color: #fff; border: 0 }

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
    
    /* Date has news indicator */
    .has-news {
      position: relative;
    }
    
    .has-news::after {
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
  body {
    padding-bottom: 90px; /* Add padding for mobile footer */
  }
  
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
    margin: 18px 0 100px; /* Extra margin for mobile footer */
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

    /* About Section Styling */
.about-section {
  max-width: 1200px;
  margin: 20px auto;
  padding: 0 clamp(14px, 3vw, 24px);
}

.about-card {
  background: var(--card);
  border: var(--border);
  border-radius: var(--radius);
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: var(--shadow);
}

.about-title {
  color: var(--yellow);
  text-align: center;
  font-size: clamp(22px, 2.5vw, 28px);
  margin-bottom: 20px;
  font-weight: 800;
}

.about-content {
  text-align: center;
  color: var(--text);
  line-height: 1.7;
}

.about-content p {
  margin-bottom: 16px;
  font-size: 16px;
  text-align: center;
}

.about-content ul {
  display: inline-block;
  text-align: left;
  margin: 16px auto;
  padding-left: 20px;
}

.about-content li {
  margin-bottom: 8px;
  text-align: left;
}

.about-features {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin: 30px 0;
}

.feature-item {
  background: var(--glass);
  border: var(--border);
  border-radius: var(--radius-sm);
  padding: 20px;
  text-align: center;
  transition: transform var(--trans), box-shadow var(--trans);
}

.feature-item:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow);
}

.feature-icon {
  font-size: 32px;
  margin-bottom: 12px;
}

/* Mobile responsiveness for about section */
@media (max-width: 768px) {
  .about-card {
    padding: 16px;
  }
  
  .about-content p {
    font-size: 15px;
    text-align: center;
  }
  
  .about-features {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  
  .feature-item {
    padding: 16px;
  }
}

@media (max-width: 640px) {
  .about-section {
    margin: 16px auto;
    padding-bottom: 20px; /* Extra padding for mobile */
  }
  
  .about-card {
    padding: 14px;
    margin-bottom: 16px;
  }
  
  .about-content p {
    font-size: 14px;
    text-align: center;
  }
  
  .about-features {
    margin: 20px 0;
  }
  
  .feature-item {
    padding: 14px;
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
        <input type="search" name="q" placeholder="தேடல்…" aria-label="தேடல்" />
      </form>
      <div class="actions">
        <a href="index.php#subscriptionModal" class="btn primary">
          <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M12 3l9 6-9 6-9-6 9-6zM3 15l9 6 9-6" stroke="currentColor" stroke-width="1.5"/>
          </svg>
          Subscribe
        </a>
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

  <!-- About Section -->
  <section class="about-section">
    <div class="about-card">
      <h2 class="about-title">Liked தமிழ் பற்றி</h2>
      <div class="about-content">
        <p>Liked தமிழ் என்பது தமிழ் மொழியில் சிறந்த செய்திகள், கட்டுரைகள், கலை மற்றும் கலாச்சார உள்ளடக்கங்களை வழங்கும் முன்னணி செய்தி வலைத்தளமாகும். 2024 ஆம் ஆண்டு தொடங்கப்பட்ட இந்த தளம், உலகளாவிய தமிழ் மக்களுக்கு நம்பகமான, சரியான நேரத்தில், சுவாரஸ்யமான செய்திகளை வழங்குவதை நோக்கமாகக் கொண்டுள்ளது.</p>
        
        <h3 style="color: var(--yellow); margin-top: 30px;">எங்கள் நோக்கம்</h3>
        <p>தமிழ் மொழியின் செழுமையைப் பாதுகாத்து, தகவல் தொழில்நுட்பத்தின் மூலம் உலகளாவிய தமிழ் சமூகத்தை இணைக்கும் வகையில் உயர்தர உள்ளடக்கங்களை வழங்குவது எங்கள் நோக்கமாகும்.</p>
        
        <h3 style="color: var(--yellow); margin-top: 30px;">சிறப்புக் கூறுகள்</h3>
        <div class="about-features">
          <div class="feature-item">
            <div class="feature-icon">📰</div>
            <h4 style="margin: 0 0 10px 0;">பல்துறை செய்திகள்</h4>
            <p style="margin: 0; font-size: 14px; color: var(--muted);">அரசியல், பொருளாதாரம், விளையாட்டு, தொழில்நுட்பம், கலை, கலாச்சாரம் உள்ளிட்ட பல்துறை செய்திகள்</p>
          </div>
          
          <div class="feature-item">
            <div class="feature-icon">⚡</div>
            <h4 style="margin: 0 0 10px 0;">விரைவான புதுப்பிப்பு</h4>
            <p style="margin: 0; font-size: 14px; color: var(--muted);">24/7 செய்தி புதுப்பிப்பு, உடனடி Breaking News, Live Updates</p>
          </div>
          
          <div class="feature-item">
            <div class="feature-icon">📱</div>
            <h4 style="margin: 0 0 10px 0;">மொபைல் இணக்கம்</h4>
            <p style="margin: 0; font-size: 14px; color: var(--muted);">அனைத்து சாதனங்களிலும் சிறப்பாக வேலை செய்யும் Responsive Design</p>
          </div>
          
          <div class="feature-item">
            <div class="feature-icon">🔍</div>
            <h4 style="margin: 0 0 10px 0;">ஆழ்ந்த பகுப்பாய்வு</h4>
            <p style="margin: 0; font-size: 14px; color: var(--muted);">மேலோட்டமான செய்திகள் மட்டுமல்ல, ஆழ்ந்த ஆய்வுகள் மற்றும் பகுப்பாய்வுகள்</p>
          </div>
        </div>
        
        <h3 style="color: var(--yellow); margin-top: 30px;">எங்கள் அணி</h3>
        <p>Liked தமிழ் அனுபவம் வாய்ந்த பத்திரிகையாளர்கள், எழுத்தாளர்கள் மற்றும் தொழில்நுட்ப வல்லுநர்களைக் கொண்ட ஒரு தகுதிவாய்ந்த அணியால் இயக்கப்படுகிறது. எங்கள் அனைத்து உள்ளடக்கங்களும் உண்மைத்தன்மை, நடுநிலை மற்றும் தரத்திற்காக கடுமையான சரிபார்ப்பு செய்யப்படுகின்றன.</p>
        
        <h3 style="color: var(--yellow); margin-top: 30px;">தொடர்பு கொள்ள</h3>
        <p>கருத்துகள், பரிந்துரைகள் அல்லது விளம்பரங்களுக்கு:</p>
        <ul style="color: var(--muted);">
          <li>மின்னஞ்சல்: info@likedtamil.lk</li>
          <li>வலைத்தளம்: <a href="https://likedtamil.lk" style="color: var(--yellow); text-decoration: none;">likedtamil.lk</a></li>
          <li>பேஸ்புக்: <a href="https://facebook.com/liked.tamil" style="color: var(--yellow); text-decoration: none;">facebook.com/liked.tamil</a></li>
        </ul>
      </div>
    </div>
    
    <div class="about-card">
      <h2 class="about-title">வளர்ச்சி குழு</h2>
      <div class="about-content">
        <p>இந்த வலைத்தளம் <a href="https://webbuilders.lk" style="color: var(--yellow); text-decoration: none;">Webbuilders.lk</a> நிறுவனத்தால் மேம்படுத்தப்பட்டு பராமரிக்கப்படுகிறது. தமிழ் வலைத்தளங்களின் வளர்ச்சி மற்றும் மேம்பாட்டில் முன்னணி நிறுவனமாக செயல்படும் Webbuilders.lk, நவீன தொழில்நுட்பங்களைப் பயன்படுத்தி தரமான தமிழ் உள்ளடக்கங்களை உருவாக்குவதில் நிபுணத்துவம் பெற்றுள்ளது.</p>
        
        <p style="margin-top: 20px; padding: 15px; background: rgba(255,252,0,0.1); border-radius: var(--radius-sm); border-left: 3px solid var(--yellow);">
          <strong>வலைத்தள மேம்பாட்டு சேவைகள்:</strong><br>
          வலைத்தள வடிவமைப்பு, உள்ளடக்க மேலாண்மை அமைப்பு (CMS), மொபைல் பயன்பாடுகள், SEO மேம்பாடு, இணையதள பாதுகாப்பு மற்றும் பராமரிப்பு.
        </p>
      </div>
    </div>
  </section>

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
      <a href="search.php" class="foot-item">
        <svg class="foot-icon" viewBox="0 0 24 24" fill="none" stroke="#fff">
          <path d="M11 5a6 6 0 016 6c0 1.3-.41 2.5-1.11 3.48l4.32 4.32-1.41 1.41-4.32-4.32A6 6 0 1111 5z" stroke="#fff" stroke-width="1.6"/>
        </svg>
        <span class="foot-label">தேடல்</span>
      </a>
      <a href="about.php" class="foot-item active">
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

</body>
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
</html>