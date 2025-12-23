<?php
// Database connection
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Get news ID from URL
$newsId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch news details
$newsQuery = "SELECT n.*, 
              (SELECT name FROM categories WHERE FIND_IN_SET(id, n.categories) > 0 LIMIT 1) as category_name
              FROM news n 
              WHERE n.id = ? AND n.status = 'published'";
$newsStmt = $db->prepare($newsQuery);
$newsStmt->execute([$newsId]);
$news = $newsStmt->fetch(PDO::FETCH_ASSOC);

// If news not found, redirect to homepage
if (!$news) {
    header("Location: index.php");
    exit();
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $comment = $_POST['comment'] ?? '';
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
    
    if (!empty($name) && !empty($comment)) {
        $insertQuery = "INSERT INTO comments (news_id, parent_id, name, email, comment, status) 
                        VALUES (?, ?, ?, ?, ?, 'approved')";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->execute([$newsId, $parent_id, $name, $email, $comment]);
        
        // Redirect to prevent form resubmission
        header("Location: news-detail.php?id=" . $newsId);
        exit();
    }
}

// Handle comment like
if (isset($_GET['like_comment'])) {
    $commentId = intval($_GET['like_comment']);
    
    // Check if user already liked this comment (using session)
    session_start();
    $likedKey = 'liked_comment_' . $commentId;
    
    if (!isset($_SESSION[$likedKey])) {
        $likeQuery = "UPDATE comments SET likes = likes + 1 WHERE id = ?";
        $likeStmt = $db->prepare($likeQuery);
        $likeStmt->execute([$commentId]);
        
        $_SESSION[$likedKey] = true;
    }
    
    header("Location: news-detail.php?id=" . $newsId);
    exit();
}

// Fetch comments for this news
$commentsQuery = "SELECT * FROM comments 
                  WHERE news_id = ? AND status = 'approved' 
                  ORDER BY 
                    CASE WHEN parent_id = 0 THEN id ELSE parent_id END,
                    created_at ASC";
$commentsStmt = $db->prepare($commentsQuery);
$commentsStmt->execute([$newsId]);
$allComments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Organize comments into parent-child structure
$comments = [];
foreach ($allComments as $comment) {
    if ($comment['parent_id'] == 0) {
        $comments[$comment['id']] = $comment;
        $comments[$comment['id']]['replies'] = [];
    }
}

// Add replies to their parents
foreach ($allComments as $comment) {
    if ($comment['parent_id'] != 0 && isset($comments[$comment['parent_id']])) {
        $comments[$comment['parent_id']]['replies'][] = $comment;
    }
}

// Format time ago in Tamil
function getTamilTimeAgo($timestamp) {
    $currentTime = time();
    $timeDiff = $currentTime - strtotime($timestamp);
    
    if ($timeDiff < 60) {
        return $timeDiff . " வினாடிகள் முன்";
    } elseif ($timeDiff < 3600) {
        $minutes = floor($timeDiff / 60);
        return $minutes . " நிமிடம் முன்";
    } elseif ($timeDiff < 86400) {
        $hours = floor($timeDiff / 3600);
        return $hours . " மணி முன்";
    } elseif ($timeDiff < 2592000) {
        $days = floor($timeDiff / 86400);
        return $days . " நாட்கள் முன்";
    } elseif ($timeDiff < 31536000) {
        $months = floor($timeDiff / 2592000);
        return $months . " மாதங்கள் முன்";
    } else {
        $years = floor($timeDiff / 31536000);
        return $years . " ஆண்டுகள் முன்";
    }
}

// Get initials for avatar
function getInitials($name) {
    $initials = '';
    $words = explode(' ', $name);
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper($word[0]);
        }
    }
    return substr($initials, 0, 2);
}

// Handle image path
$imagePath = '';
if (!empty($news['image'])) {
    if (filter_var($news['image'], FILTER_VALIDATE_URL)) {
        $imagePath = $news['image'];
    } else {
        $imagePath = '/uploads/news/' . basename($news['image']);
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $imagePath)) {
            $imagePath = 'https://picsum.photos/id/1011/1200/600';
        }
    }
} else {
    $imagePath = 'https://picsum.photos/id/1011/1200/600';
}
?>

<!DOCTYPE html>
<html lang="ta">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($news['title']); ?> - Liked தமிழ்</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Noto+Sans+Tamil:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        /* Same CSS as before - kept exactly as in your single.html */
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
            padding-bottom: 82px;
        }
        
        .logo {
            width: 20%;
            height: 20%;
            border-radius: 8px;
            object-fit: contain;
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
            font-weight: 80%; font-size: clamp(18px, 2.4vw, 28px); letter-spacing: .2px;
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
        }
        
        .btn:hover { transform: translateY(-2px); box-shadow: var(--shadow) }
        .btn.primary {
            background: linear-gradient(180deg, var(--red), #cc0f0f);
            color: #fff; border: 0;
        }
        
        .icon { width: 20px; height: 20px }
        
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
        }
        
        .chip:hover { transform: translateY(-2px); background: rgba(255,17,17,.18) }
        .chip.active { background: linear-gradient(180deg, var(--red), #d10f0f); color: #fff; border: 0 }
        
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 10px clamp(12px, 4vw, 18px);
            display:flex;
            justify-content: space-between;
            gap: 6px;
        }
        
        .foot-item {
            flex:1;
            display:flex;
            flex-direction: column;
            align-items:center;
            gap: 6px;
            color: #fff;
            text-decoration:none;
            padding:8px;
            border-radius: 12px;
            transition: transform var(--trans), background var(--trans);
        }
        
        .foot-item:hover, .foot-item.active {
            background: rgba(0,0,0,.18);
            transform: translateY(-2px);
        }
        
        .foot-icon {
            width: 22px;
            height: 22px;
        }
        
        .foot-label {
            font-size: 12px;
            font-weight:700;
        }
        
        .article-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .article-header {
            margin-bottom: 30px;
        }
        
        .article-category {
            display: inline-block;
            padding: 8px 16px;
            background: linear-gradient(180deg, var(--red), #cc0f0f);
            color: white;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .article-title {
            font-size: 32px;
            font-weight: 800;
            line-height: 1.3;
            margin-bottom: 20px;
            color: #fff;
        }
        
        .article-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 30px;
        }
        
        .article-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .article-content {
            font-size: 18px;
            line-height: 1.8;
            color: var(--text);
        }
        
        .article-content p {
            margin-bottom: 20px;
        }
        
        .article-content h2 {
            font-size: 24px;
            margin: 30px 0 20px;
            color: var(--yellow);
        }
        
        .comments-section {
            margin-top: 60px;
            padding-top: 30px;
            border-top: var(--border);
        }
        
        .comments-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 30px;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .comments-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
            margin-left: 15px;
        }
        
        .comment-count {
            color: var(--red);
            font-weight: 800;
        }
        
        .comment-form {
            background: var(--card);
            border-radius: var(--radius);
            padding: 24px;
            margin-bottom: 40px;
            border: var(--border);
        }
        
        .comment-form-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: var(--muted);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: var(--card-hi);
            border: var(--border);
            border-radius: var(--radius-xs);
            color: var(--text);
            font-family: inherit;
            font-size: 15px;
            transition: border-color var(--trans);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--red);
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .submit-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(180deg, var(--red), #cc0f0f);
            color: white;
            border: none;
            border-radius: var(--radius-xs);
            font-weight: 600;
            cursor: pointer;
            transition: transform var(--trans), opacity var(--trans);
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }
        
        .comments-list {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        
        .comment-item {
            background: var(--card);
            border-radius: var(--radius);
            padding: 20px;
            border: var(--border);
            position: relative;
        }
        
        .comment-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }
        
        .comment-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--red), var(--yellow));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: var(--black);
            font-size: 18px;
        }
        
        .comment-user {
            flex: 1;
        }
        
        .comment-name {
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .comment-time {
            font-size: 13px;
            color: var(--muted);
        }
        
        .comment-actions {
            display: flex;
            gap: 12px;
        }
        
        .comment-action {
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 6px;
            transition: all var(--trans);
        }
        
        .comment-action:hover {
            color: var(--text);
            background: var(--glass);
        }
        
        .comment-action.liked {
            color: var(--red);
        }
        
        .comment-content {
            line-height: 1.6;
            color: var(--text);
        }
        
        .comment-replies {
            margin-top: 20px;
            margin-left: 20px;
            padding-left: 20px;
            border-left: 2px solid var(--glass);
        }
        
        .reply-form {
            margin-top: 16px;
            padding: 16px;
            background: var(--card-hi);
            border-radius: var(--radius-xs);
        }
        
        .show-replies {
            margin-top: 12px;
            color: var(--red);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .no-comments {
            text-align: center;
            padding: 40px 20px;
            color: var(--muted);
        }
        
        .no-comments-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
        
        @media (max-width: 768px) {
            .article-title {
                font-size: 24px;
            }
            
            .article-image {
                height: 250px;
            }
            
            .article-content {
                font-size: 16px;
            }
            
            .comment-form {
                padding: 16px;
            }
            
            .comment-item {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header class="appbar">
        <div class="appbar-wrap">
            <a href="index.php" class="brand">
                <img src="Liked-tamil-news-logo-1 (2).png" alt="Portal Logo" class="logo" />
            </a>
            <div class="search" role="search">
                <svg class="icon" viewBox="0 0 24 24" fill="none">
                    <path d="M11 5a6 6 0 016 6c0 1.3-.41 2.5-1.11 3.48l4.32 4.32-1.41 1.41-4.32-4.32A6 6 0 1111 5z" stroke="currentColor" stroke-width="1.5"/>
                </svg>
                <input type="search" placeholder="தேடல்…" aria-label="தேடல்" />
            </div>
            <div class="actions">
                <button class="btn primary">
                    <svg class="icon" viewBox="0 0 24 24" fill="none">
                        <path d="M12 3l9 6-9 6-9-6 9-6zM3 15l9 6 9-6" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    Subscribe
                </button>
            </div>
        </div>
    </header>

    <!-- CATEGORY BAR -->
    <nav class="catbar" aria-label="Categories">
        <div class="catbar-wrap">
            <a href="index.php" class="chip">முகப்பு</a>
            <a href="arts.html" class="chip">கலைகள்</a>
            <a href="poems.html" class="chip">கவிதைகள்</a>
            <a href="series.html" class="chip">தொடர் கட்டுரைகள்</a>
            <button class="chip">புகைப்பட தொகுப்பு</button>
            <button class="chip">அந்தரங்கம்</button>
            <button class="chip">வினோதம்</button>
            <button class="chip">வீடியோ</button>
            <button class="chip">செய்திகள்</button>
            <button class="chip">சிறப்பு செய்திகள்</button>
            <button class="chip">உள்ளூர் செய்திகள்</button>
            <button class="chip">இந்திய செய்திகள்</button>
            <button class="chip">உலக செய்திகள்</button>
            <button class="chip">அரசியல்</button>
            <button class="chip">சினிமா</button>
            <button class="chip">தொழில்நுட்பம்</button>
            <button class="chip">விளையாட்டு</button>
            <button class="chip">ஆன்மீகம்</button>
            <button class="chip">கட்டுரைகள்</button>
        </div>
    </nav>

    <!-- ARTICLE CONTENT -->
    <main class="article-container">
        <article>
            <div class="article-header">
                <span class="article-category"><?php echo htmlspecialchars($news['category_name'] ?? 'செய்திகள்'); ?></span>
                <h1 class="article-title"><?php echo htmlspecialchars($news['title']); ?></h1>
                <div class="article-meta">
                    <span>
                        <?php 
                        if (!empty($news['published_at'])) {
                            echo date('F d, Y', strtotime($news['published_at']));
                        } else {
                            echo date('F d, Y', strtotime($news['created_at']));
                        }
                        ?>
                    </span>
                    <span>•</span>
                    <span>
                        <?php 
                        if (!empty($news['published_at'])) {
                            echo getTamilTimeAgo($news['published_at']);
                        } else {
                            echo getTamilTimeAgo($news['created_at']);
                        }
                        ?>
                    </span>
                    <span>•</span>
                    <span id="commentCount">
                        <?php 
                        // Count total comments
                        $totalComments = count($allComments);
                        echo $totalComments . ' கருத்துகள்';
                        ?>
                    </span>
                </div>
            </div>
            
            <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                 alt="<?php echo htmlspecialchars($news['title']); ?>" 
                 class="article-image">
            
            <div class="article-content">
                <?php echo nl2br(htmlspecialchars($news['content'])); ?>
            </div>
        </article>

        <!-- COMMENT SECTION -->
        <section class="comments-section" id="comments">
            <h2 class="comments-title">
                <span>கருத்துகள்</span>
                <span class="comment-count" id="totalComments"><?php echo $totalComments; ?></span>
            </h2>

            <!-- Comment Form -->
            <div class="comment-form">
                <h3 class="comment-form-title">கருத்தைப் பதிவிடுக</h3>
                <form method="POST" action="" id="commentForm">
                    <input type="hidden" name="parent_id" value="0" id="parentId">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">பெயர் *</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email">மின்னஞ்சல் (வெளிப்படையாக்கப்படாது) *</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="comment">கருத்து *</label>
                        <textarea id="comment" name="comment" class="form-control" required></textarea>
                    </div>
                    <button type="submit" name="submit_comment" class="submit-btn">
                        <svg class="icon" viewBox="0 0 24 24" fill="none">
                            <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                        கருத்தை சமர்ப்பிக்கவும்
                    </button>
                </form>
            </div>

            <!-- Comments List -->
            <div class="comments-list" id="commentsList">
                <?php if (empty($comments)): ?>
                    <div class="no-comments" id="noComments">
                        <div class="no-comments-icon">💬</div>
                        <p>இதுவரை கருத்துகள் எதுவும் இல்லை. முதலாவதாக கருத்து தெரிவியுங்கள்!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item" id="comment-<?php echo $comment['id']; ?>">
                            <div class="comment-header">
                                <div class="comment-avatar"><?php echo getInitials($comment['name']); ?></div>
                                <div class="comment-user">
                                    <div class="comment-name"><?php echo htmlspecialchars($comment['name']); ?></div>
                                    <div class="comment-time"><?php echo getTamilTimeAgo($comment['created_at']); ?></div>
                                </div>
                                <div class="comment-actions">
                                    <a href="?id=<?php echo $newsId; ?>&like_comment=<?php echo $comment['id']; ?>" 
                                       class="comment-action <?php echo isset($_SESSION['liked_comment_' . $comment['id']]) ? 'liked' : ''; ?>">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="<?php echo isset($_SESSION['liked_comment_' . $comment['id']]) ? 'currentColor' : 'none'; ?>" stroke="currentColor">
                                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                        </svg>
                                        <span class="like-count"><?php echo $comment['likes']; ?></span>
                                    </a>
                                    <button class="comment-action reply-btn" data-id="<?php echo $comment['id']; ?>">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/>
                                        </svg>
                                        பதில்
                                    </button>
                                </div>
                            </div>
                            <div class="comment-content"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></div>
                            
                            <!-- Reply Form (Initially Hidden) -->
                            <div class="reply-form" id="reply-form-<?php echo $comment['id']; ?>" style="display: none;">
                                <form method="POST" action="">
                                    <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                                    <div class="form-group">
                                        <label for="reply-name-<?php echo $comment['id']; ?>">பெயர் *</label>
                                        <input type="text" id="reply-name-<?php echo $comment['id']; ?>" name="name" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="reply-comment-<?php echo $comment['id']; ?>">பதில் *</label>
                                        <textarea id="reply-comment-<?php echo $comment['id']; ?>" name="comment" class="form-control" required></textarea>
                                    </div>
                                    <input type="email" name="email" style="display:none;" value="reply@example.com">
                                    <div style="display: flex; gap: 10px;">
                                        <button type="submit" name="submit_comment" class="submit-btn" style="padding: 8px 16px;">
                                            பதிலிடுக
                                        </button>
                                        <button type="button" class="cancel-reply-btn" data-id="<?php echo $comment['id']; ?>" style="padding: 8px 16px; background: var(--card); border: var(--border); color: var(--text); border-radius: var(--radius-xs);">
                                            ரத்துசெய்
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Replies -->
                            <?php if (!empty($comment['replies'])): ?>
                                <div class="comment-replies">
                                    <?php foreach ($comment['replies'] as $reply): ?>
                                        <div class="comment-item comment-reply" id="comment-<?php echo $reply['id']; ?>">
                                            <div class="comment-header">
                                                <div class="comment-avatar"><?php echo getInitials($reply['name']); ?></div>
                                                <div class="comment-user">
                                                    <div class="comment-name"><?php echo htmlspecialchars($reply['name']); ?></div>
                                                    <div class="comment-time"><?php echo getTamilTimeAgo($reply['created_at']); ?></div>
                                                </div>
                                                <div class="comment-actions">
                                                    <a href="?id=<?php echo $newsId; ?>&like_comment=<?php echo $reply['id']; ?>" 
                                                       class="comment-action <?php echo isset($_SESSION['liked_comment_' . $reply['id']]) ? 'liked' : ''; ?>">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="<?php echo isset($_SESSION['liked_comment_' . $reply['id']]) ? 'currentColor' : 'none'; ?>" stroke="currentColor">
                                                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                                        </svg>
                                                        <span class="like-count"><?php echo $reply['likes']; ?></span>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="comment-content"><?php echo nl2br(htmlspecialchars($reply['comment'])); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <footer class="likedtamil-footer">
        <div class="likedtamil-footer-wrap">
            © 2026 All Rights Reserved by <a href="https://likedtamil.lk" target="_blank">Likedtamil.lk</a> | Developed by <a href="https://webbuilders.lk" target="_blank">Webbuilders.lk</a>
        </div>
    </footer>

    <!-- MOBILE FOOTER -->
    <footer class="mobile-footer" role="navigation" aria-label="மொபைல் அடிக்குறிப்பு">
        <div class="foot-wrap">
            <a href="index.php" class="foot-item">
                <svg class="foot-icon" viewBox="0 0 24 24" fill="none"><path d="M3 10l9-7 9 7v9a2 2 0 01-2 2H5a2 2 0 01-2-2v-9z" stroke="#fff" stroke-width="1.6"/></svg>
                <span class="foot-label">முகப்பு</span>
            </a>
            <a href="#" class="foot-item">
                <svg class="foot-icon" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 12h16M4 18h16" stroke="#fff" stroke-width="1.6"/></svg>
                <span class="foot-label">பிரிவுகள்</span>
            </a>
            <a href="#" class="foot-item">
                <svg class="foot-icon" viewBox="0 0 24 24" fill="none"><path d="M11 5a6 6 0 016 6c0 1.3-.41 2.5-1.11 3.48l4.32 4.32-1.41 1.41-4.32-4.32A6 6 0 1111 5z" stroke="#fff" stroke-width="1.6"/></svg>
                <span class="foot-label">தேடல்</span>
            </a>
            <a href="#" class="foot-item">
                <svg class="foot-icon" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="6" stroke="#fff" stroke-width="1.6"/></svg>
                <span class="foot-label">சுயவிவரம்</span>
            </a>
            <a href="#" class="foot-item">
                <svg class="foot-icon" viewBox="0 0 24 24" fill="none"><path d="M6 19l6-6 6 6M6 12l6-6 6 6" stroke="#fff" stroke-width="1.6"/></svg>
                <span class="foot-label">வீடியோ</span>
            </a>
        </div>
    </footer>

    <script>
        // JavaScript for comment reply functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Reply button functionality
            document.querySelectorAll('.reply-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const commentId = this.getAttribute('data-id');
                    const replyForm = document.getElementById('reply-form-' + commentId);
                    
                    // Toggle reply form visibility
                    if (replyForm.style.display === 'none') {
                        replyForm.style.display = 'block';
                    } else {
                        replyForm.style.display = 'none';
                    }
                });
            });
            
            // Cancel reply button functionality
            document.querySelectorAll('.cancel-reply-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const commentId = this.getAttribute('data-id');
                    const replyForm = document.getElementById('reply-form-' + commentId);
                    replyForm.style.display = 'none';
                });
            });
            
            // Auto-hide success messages after 5 seconds
            setTimeout(function() {
                const successMessages = document.querySelectorAll('.success-message');
                successMessages.forEach(msg => {
                    msg.style.display = 'none';
                });
            }, 5000);
        });
    </script>
</body>
</html>