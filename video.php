<?php
// Database connection
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Fetch all videos from news table where video column is not empty
$query = "SELECT id, title, video, published_at, image FROM news 
          WHERE video IS NOT NULL AND video != '' AND status = 'published' 
          ORDER BY published_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories for navigation
$categoryQuery = "SELECT * FROM categories WHERE status = 'active' ORDER BY id";
$categoryStmt = $db->prepare($categoryQuery);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

require 'config/config.php'; // To get $base_url
?>

<!DOCTYPE html>
<html lang="ta">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>வீடியோக்கள் - Liked தமிழ்</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    html, body { height: 100%; width:100%; margin: 0; padding: 0 }
    body {
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

    /* Video Page Header */
    .video-header {
      max-width: 1200px;
      margin: 30px auto 20px;
      padding: 0 clamp(14px, 3vw, 24px);
    }
    
    .video-header h1 {
      font-size: 2.5rem;
      margin-bottom: 10px;
      background: linear-gradient(90deg, var(--red), var(--yellow));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      letter-spacing: -0.5px;
    }
    
    .video-header p {
      color: var(--muted);
      font-size: 1.1rem;
      margin-bottom: 20px;
    }
    
    .video-stats {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: var(--card);
      padding: 15px 25px;
      border-radius: var(--radius-sm);
      margin-bottom: 30px;
      border: var(--border);
    }
    
    .total-videos {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .total-videos i {
      color: var(--red);
      font-size: 1.2rem;
    }
    
    .sort-options select {
      background-color: var(--card-hi);
      color: var(--text);
      border: var(--border);
      padding: 10px 20px;
      border-radius: var(--radius-xs);
      font-size: 0.95rem;
      cursor: pointer;
      transition: var(--trans);
      font-family: "Noto Sans Tamil", Inter, sans-serif;
    }
    
    .sort-options select:hover {
      border-color: var(--red);
    }
    
    /* Video Grid */
    .video-grid {
      max-width: 1200px;
      margin: 0 auto 40px;
      padding: 0 clamp(14px, 3vw, 24px);
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 25px;
    }
    
    .video-card {
      background-color: var(--card);
      border-radius: var(--radius);
      overflow: hidden;
      border: var(--border);
      transition: var(--trans);
      box-shadow: var(--shadow);
    }
    
    .video-card:hover {
      transform: translateY(-8px);
      background-color: var(--card-hi);
      border-color: rgba(255,255,255,.1);
    }
    
    .video-thumbnail {
      position: relative;
      height: 200px;
      overflow: hidden;
    }
    
    .video-thumbnail img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: var(--trans);
    }
    
    .video-card:hover .video-thumbnail img {
      transform: scale(1.05);
    }
    
    .play-btn {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: var(--red);
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: white;
      opacity: 0.9;
      transition: var(--trans);
      cursor: pointer;
      border: none;
      outline: none;
    }
    
    .video-card:hover .play-btn {
      opacity: 1;
      transform: translate(-50%, -50%) scale(1.1);
    }
    
    .video-info {
      padding: 20px;
    }
    
    .video-title {
      font-size: 1.2rem;
      font-weight: 600;
      margin-bottom: 10px;
      color: var(--text);
      line-height: 1.4;
      font-family: "Noto Sans Tamil", Inter, sans-serif;
    }
    
    .video-date {
      color: var(--muted);
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .video-date i {
      color: var(--yellow);
    }
    
    .no-videos {
      grid-column: 1 / -1;
      text-align: center;
      padding: 60px 20px;
      background-color: var(--card);
      border-radius: var(--radius);
      border: var(--border);
    }
    
    .no-videos i {
      font-size: 4rem;
      color: var(--muted);
      margin-bottom: 20px;
    }
    
    .no-videos h3 {
      font-size: 1.8rem;
      margin-bottom: 10px;
      color: var(--text);
      font-family: "Noto Sans Tamil", Inter, sans-serif;
    }
    
    .no-videos p {
      color: var(--muted);
      max-width: 500px;
      margin: 0 auto;
      font-family: "Noto Sans Tamil", Inter, sans-serif;
    }
    
    /* Modal for video player */
    .video-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.85);
      z-index: 1000;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    
    .modal-content {
      background-color: var(--card);
      border-radius: var(--radius);
      padding: 30px;
      max-width: 900px;
      width: 100%;
      position: relative;
      border: var(--border);
    }
    
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    
    .modal-title {
      font-size: 1.5rem;
      color: var(--text);
      font-family: "Noto Sans Tamil", Inter, sans-serif;
    }
    
    .close-modal {
      background: none;
      border: none;
      color: var(--muted);
      font-size: 1.8rem;
      cursor: pointer;
      transition: var(--trans);
    }
    
    .close-modal:hover {
      color: var(--red);
    }
    
    .modal-video-container {
      position: relative;
      padding-bottom: 56.25%; /* 16:9 aspect ratio */
      height: 0;
      overflow: hidden;
      border-radius: var(--radius-sm);
    }
    
    .modal-video-container iframe {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      border: none;
    }
    
    /* Loading */
    .loading {
      text-align: center;
      padding: 40px;
      font-size: 1.2rem;
      color: var(--muted);
    }
    
    .loading i {
      color: var(--yellow);
      margin-right: 10px;
    }
    
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
    
    /* Responsive styles */
    @media (max-width: 1100px) {
      .video-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      }
      
      .video-header h1 {
        font-size: 2.2rem;
      }
    }
    
    @media (max-width: 768px) {
      .video-header h1 {
        font-size: 1.8rem;
      }
      
      .video-stats {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
      }
      
      .video-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
      }
      
      .video-thumbnail {
        height: 180px;
      }
    }
    
    @media (max-width: 576px) {
      .video-grid {
        grid-template-columns: 1fr;
      }
      
      .modal-content {
        padding: 20px;
      }
      
      .video-header {
        margin: 15px auto 10px;
      }
    }
    
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
      <input type="search" name="q" placeholder="தேடல்…" aria-label="தேடல்" />
    </form>
  </div>
</header>

<!-- Category Navigation -->
<nav class="catbar" aria-label="Categories">
  <div class="catbar-wrap">
    <a href="index.php" class="chip">முகப்பு</a>
    <a href="video.php" class="chip active">வீடியோ</a>
    <?php foreach ($categories as $category): ?>
      <a href="categories.php?id=<?php echo $category['id']; ?>" class="chip">
        <?php echo htmlspecialchars($category['name']); ?>
      </a>
    <?php endforeach; ?>
  </div>
</nav>

<!-- Video Page Header -->
<div class="video-header">
    <h1>வீடியோ செய்திகள்</h1>
    <p>Liked தமிழில் வெளியான அனைத்து வீடியோ செய்திகளையும் இங்கே பார்க்கலாம்</p>
    
    <div class="video-stats">
        <div class="total-videos">
            <i class="fas fa-play-circle"></i>
            <span id="video-count"><?php echo count($videos); ?> வீடியோக்கள்</span>
        </div>
        <div class="sort-options">
            <select id="sort-by">
                <option value="newest">புதியது முதலில்</option>
                <option value="oldest">பழையது முதலில்</option>
                <option value="title">பெயர் வரிசை (அ-ஹ)</option>
            </select>
        </div>
    </div>
</div>

<!-- Video Grid -->
<div class="video-grid" id="video-container">
    <?php if (!empty($videos)): ?>
        <?php foreach ($videos as $video): ?>
            <div class="video-card" data-id="<?php echo $video['id']; ?>">
                <div class="video-thumbnail">
                    <?php
                    // Get image URL
                    $imageSrc = '';
                    if (!empty($video['image'])) {
                        if (filter_var($video['image'], FILTER_VALIDATE_URL)) {
                            $imageSrc = $video['image'];
                        } else {
                            $imageSrc = $base_url . 'uploads/news/' . htmlspecialchars($video['image']);
                        }
                    } else {
                        $imageSrc = 'https://images.unsplash.com/photo-1588681664899-f142ff2dc9b1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
                    }
                    ?>
                    <img src="<?php echo $imageSrc; ?>" alt="<?php echo htmlspecialchars($video['title']); ?>">
                    <button class="play-btn" data-video-url="<?php echo htmlspecialchars($video['video']); ?>">
                        <i class="fas fa-play"></i>
                    </button>
                </div>
                <div class="video-info">
                    <h3 class="video-title"><?php echo htmlspecialchars($video['title']); ?></h3>
                    <div class="video-date">
                        <i class="far fa-calendar-alt"></i>
                        <span>
                            <?php 
                            $publishTime = new DateTime($video['published_at']);
                            $now = new DateTime();
                            $interval = $now->diff($publishTime);
                            
                            if ($interval->days > 0) {
                                echo $interval->days . ' நாட்கள் முன்';
                            } elseif ($interval->h > 0) {
                                echo $interval->h . ' மணி முன்';
                            } else {
                                echo $interval->i . ' நிமிடங்கள் முன்';
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-videos">
            <i class="fas fa-video-slash"></i>
            <h3>வீடியோக்கள் இல்லை</h3>
            <p>தற்போது வீடியோ செய்திகள் எதுவும் இல்லை. பின்னர் சரிபார்க்கவும்.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Video Modal -->
<div class="video-modal" id="video-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-video-title">வீடியோ</h3>
            <button class="close-modal" id="close-modal">&times;</button>
        </div>
        <div class="modal-video-container">
            <iframe id="modal-video-player" allowfullscreen></iframe>
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
        <a href="search.php" class="foot-item">
            <svg class="foot-icon" viewBox="0 0 24 24" fill="none"><path d="M11 5a6 6 0 016 6c0 1.3-.41 2.5-1.11 3.48l4.32 4.32-1.41 1.41-4.32-4.32A6 6 0 1111 5z" stroke="#fff" stroke-width="1.6"/></svg>
            <span class="foot-label">தேடல்</span>
        </a>
        <a href="about.php" class="foot-item">
            <svg class="foot-icon" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="6" stroke="#fff" stroke-width="1.6"/></svg>
            <span class="foot-label">சுயவிவரம்</span>
        </a>
        <a href="video.php" class="foot-item active">
            <svg class="foot-icon" viewBox="0 0 24 24" fill="none"><path d="M6 19l6-6 6 6M6 12l6-6 6 6" stroke="#fff" stroke-width="1.6"/></svg>
            <span class="foot-label">வீடியோ</span>
        </a>
    </div>
</footer>

<script>
// Function to render video cards
function renderVideos(videos) {
    const container = document.getElementById('video-container');
    const countElement = document.getElementById('video-count');
    
    if (!videos || videos.length === 0) {
        container.innerHTML = `
            <div class="no-videos">
                <i class="fas fa-video-slash"></i>
                <h3>வீடியோக்கள் இல்லை</h3>
                <p>தற்போது வீடியோ செய்திகள் எதுவும் இல்லை. பின்னர் சரிபார்க்கவும்.</p>
            </div>
        `;
        countElement.textContent = "0 வீடியோக்கள்";
        return;
    }
    
    // Update video count
    countElement.textContent = `${videos.length} வீடியோ${videos.length !== 1 ? 'க்கள்' : ''}`;
    
    // Clear container
    container.innerHTML = '';
    
    // Render each video
    videos.forEach(video => {
        // Format date in Tamil
        const publishTime = new Date(video.published_at);
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
            timeAgo = Math.floor(diffMs / (1000 * 60)) + ' நிமிடங்கள் முன்';
        }
        
        // Get image URL
        let imageSrc = '';
        if (video.image) {
            if (video.image.startsWith('http')) {
                imageSrc = video.image;
            } else {
                imageSrc = '<?php echo $base_url; ?>uploads/news/' + video.image;
            }
        } else {
            imageSrc = 'https://images.unsplash.com/photo-1588681664899-f142ff2dc9b1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
        }
        
        const videoCard = document.createElement('div');
        videoCard.className = 'video-card';
        videoCard.setAttribute('data-id', video.id);
        videoCard.innerHTML = `
            <div class="video-thumbnail">
                <img src="${imageSrc}" alt="${video.title}">
                <button class="play-btn" data-video-url="${video.video}">
                    <i class="fas fa-play"></i>
                </button>
            </div>
            <div class="video-info">
                <h3 class="video-title">${video.title}</h3>
                <div class="video-date">
                    <i class="far fa-calendar-alt"></i>
                    <span>${timeAgo}</span>
                </div>
            </div>
        `;
        
        container.appendChild(videoCard);
    });
    
    // Add event listeners to play buttons
    document.querySelectorAll('.play-btn').forEach(button => {
        button.addEventListener('click', function() {
            const videoUrl = this.getAttribute('data-video-url');
            const videoCard = this.closest('.video-card');
            const videoTitle = videoCard.querySelector('.video-title').textContent;
            openVideoModal(videoUrl, videoTitle);
        });
    });
}

// Function to open video modal
function openVideoModal(videoUrl, videoTitle) {
    const modal = document.getElementById('video-modal');
    const videoPlayer = document.getElementById('modal-video-player');
    const modalTitle = document.getElementById('modal-video-title');
    const closeBtn = document.getElementById('close-modal');
    
    // Convert YouTube URL to embed URL if needed
    let embedUrl = videoUrl;
    if (videoUrl.includes('youtube.com/watch?v=')) {
        const videoId = videoUrl.split('v=')[1].split('&')[0];
        embedUrl = `https://www.youtube.com/embed/${videoId}`;
    } else if (videoUrl.includes('youtu.be/')) {
        const videoId = videoUrl.split('youtu.be/')[1].split('?')[0];
        embedUrl = `https://www.youtube.com/embed/${videoId}`;
    }
    
    videoPlayer.src = embedUrl;
    modalTitle.textContent = videoTitle;
    modal.style.display = 'flex';
    
    // Close modal when clicking X
    closeBtn.onclick = closeVideoModal;
    
    // Close modal when clicking outside the content
    modal.onclick = function(e) {
        if (e.target === modal) {
            closeVideoModal();
        }
    };
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeVideoModal();
        }
    });
}

// Function to close video modal
function closeVideoModal() {
    const modal = document.getElementById('video-modal');
    const videoPlayer = document.getElementById('modal-video-player');
    
    modal.style.display = 'none';
    videoPlayer.src = ''; // Stop video playback
}

// Function to sort videos
function sortVideos(videos, sortBy) {
    const sortedVideos = [...videos];
    
    switch(sortBy) {
        case 'newest':
            return sortedVideos.sort((a, b) => new Date(b.published_at) - new Date(a.published_at));
        case 'oldest':
            return sortedVideos.sort((a, b) => new Date(a.published_at) - new Date(b.published_at));
        case 'title':
            return sortedVideos.sort((a, b) => a.title.localeCompare(b.title, 'ta'));
        default:
            return sortedVideos;
    }
}

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Convert PHP videos to JavaScript array
    const videoData = <?php echo json_encode($videos ?: []); ?>;
    
    // Initial render
    renderVideos(sortVideos(videoData, 'newest'));
    
    // Sort functionality
    const sortSelect = document.getElementById('sort-by');
    sortSelect.addEventListener('change', function() {
        const sortedVideos = sortVideos(videoData, this.value);
        renderVideos(sortedVideos);
    });
    
    // Search functionality
    const searchForm = document.querySelector('.search');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="q"]');
            if (!searchInput.value.trim()) {
                e.preventDefault();
            }
        });
    }
    
    // Mobile search functionality
    document.querySelectorAll('.foot-item').forEach(item => {
        if (item.querySelector('.foot-label')?.textContent === 'தேடல்') {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                // Create search modal for mobile
                const searchModal = document.createElement('div');
                searchModal.className = 'video-modal';
                searchModal.style.display = 'flex';
                searchModal.style.zIndex = '1001';
                searchModal.innerHTML = `
                    <div class="modal-content" style="max-width: 90%;">
                        <button class="close-modal" onclick="this.parentElement.parentElement.remove()">&times;</button>
                        <h3 style="color: var(--yellow); text-align: center; margin-bottom: 20px;">தேடல்</h3>
                        <form method="GET" action="search.php" class="search" style="display: flex; gap: 10px; margin-bottom: 20px;">
                            <input type="search" name="q" placeholder="தேடல்..." style="flex: 1; padding: 12px; border-radius: var(--radius-sm); background: var(--glass); border: var(--border); color: var(--text);" autofocus>
                            <button type="submit" style="background: linear-gradient(180deg, var(--red), #cc0f0f); color: white; border: none; padding: 12px 20px; border-radius: var(--radius-sm); cursor: pointer;">தேடு</button>
                        </form>
                    </div>
                `;
                document.body.appendChild(searchModal);
                
                // Close modal when clicking outside
                searchModal.onclick = function(e) {
                    if (e.target === searchModal) {
                        searchModal.remove();
                    }
                };
            });
        }
    });
});
</script>
</body>
</html>