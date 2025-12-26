<?php
// Database connection
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();
require 'config/config.php'; 

// Base URL from config
$base_url = "http://localhost/WebBuilders/news_admin/";

// Get news ID from URL
$newsId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch all categories for navigation
$categoriesQuery = "SELECT * FROM categories WHERE status = 'active'";
$categoriesStmt = $db->prepare($categoriesQuery);
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Clean category names
foreach ($categories as &$category) {
    $category['name'] = cleanTamilText($category['name']);
}

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

// Fetch images from news_images table
$imagesQuery = "SELECT * FROM news_images 
                WHERE news_id = ? 
                ORDER BY position, display_order";
$imagesStmt = $db->prepare($imagesQuery);
$imagesStmt->execute([$newsId]);
$allImages = $imagesStmt->fetchAll(PDO::FETCH_ASSOC);

// Organize images by position
$imagesByPosition = [
    'top' => [],
    'center' => [],
    'bottom' => []
];

foreach ($allImages as $image) {
    if (isset($imagesByPosition[$image['position']])) {
        $imagesByPosition[$image['position']][] = $image;
    }
}

// Function to clean text - keep only Tamil characters and basic punctuation
function cleanTamilText($text) {
    // Remove HTML tags
    $text = strip_tags($text);
    
    // Remove non-Tamil characters (keeping basic Tamil Unicode range and basic punctuation)
    // Tamil Unicode range: \u0B80-\u0BFF
    // Allowed punctuation: spaces, commas, periods, question marks, exclamation marks, Tamil specific punctuation
    $text = preg_replace('/[^\x{0B80}-\x{0BFF}\x{0020}-\x{002F}\x{003A}-\x{0040}\x{005B}-\x{0060}\x{007B}-\x{007E}\s,.\?!\-\'\"\\(\\)]/u', '', $text);
    
    // Remove multiple spaces
    $text = preg_replace('/\s+/', ' ', $text);
    
    return trim($text);
}

// Function to preserve HTML tags and embed videos
function processNewsContent($content) {
    // Decode HTML entities
    $content = html_entity_decode($content);
    
    // Check for YouTube links and convert to embed
    $youtubePattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
    $content = preg_replace_callback($youtubePattern, function($matches) {
        $videoId = $matches[1];
        return '<div class="video-container"><iframe width="560" height="315" src="https://www.youtube.com/embed/' . $videoId . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
    }, $content);
    
    // Check for Vimeo links and convert to embed
    $vimeoPattern = '/(?:https?:\/\/)?(?:www\.)?vimeo\.com\/(\d+)/';
    $content = preg_replace_callback($vimeoPattern, function($matches) {
        $videoId = $matches[1];
        return '<div class="video-container"><iframe src="https://player.vimeo.com/video/' . $videoId . '" width="640" height="360" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div>';
    }, $content);
    
    // Check for Facebook video links and convert to embed
    $facebookPattern = '/(?:https?:\/\/)?(?:www\.)?facebook\.com\/(?:[^\/]+\/)?videos\/(\d+)/';
    $content = preg_replace_callback($facebookPattern, function($matches) {
        $videoId = $matches[1];
        return '<div class="video-container"><iframe src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Ffacebook%2Fvideos%2F' . $videoId . '%2F&show_text=0&width=560" width="560" height="315" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe></div>';
    }, $content);
    
    return $content;
}

// Clean the news content before displaying
if ($news) {
    $news['title'] = cleanTamilText($news['title']);
    $news['content'] = processNewsContent($news['content']);
    
    // Clean category name if exists
    if (isset($news['category_name'])) {
        $news['category_name'] = cleanTamilText($news['category_name']);
    }
}

// Split content into paragraphs for image insertion
$contentParagraphs = [];
$contentWithImages = '';
if (!empty($news['content'])) {
    // Split content by paragraphs (using double newlines as paragraph separator)
    $contentParagraphs = preg_split('/\n\s*\n/', $news['content']);
    
    // Calculate content length for image placement decisions
    $contentLength = mb_strlen($news['content']);
    $paragraphCount = count($contentParagraphs);
    
    // Initialize content with images
    $contentWithImages = '';
    
    // Add top images before content if they exist
    if (!empty($imagesByPosition['top'])) {
        foreach ($imagesByPosition['top'] as $image) {
            $imagePath = getImagePath($image['image_path']);
            $caption = !empty($image['caption']) ? htmlspecialchars($image['caption']) : '';
            $contentWithImages .= '<div class="position-image top-position">';
            $contentWithImages .= '<img src="' . $imagePath . '" alt="' . htmlspecialchars($news['title']) . '">';
            if ($caption) {
                $contentWithImages .= '<div class="image-caption">' . $caption . '</div>';
            }
            $contentWithImages .= '</div>';
        }
    }
    
    // Process content paragraphs and insert center images at appropriate positions
    if ($paragraphCount > 0) {
        // Determine where to insert center images based on content length
        $centerInsertionPoint = floor($paragraphCount / 2);
        
        for ($i = 0; $i < $paragraphCount; $i++) {
            // Add the paragraph (preserve HTML)
            $contentWithImages .= '<p>' . trim($contentParagraphs[$i]) . '</p>';
            
            // Insert center images after the middle paragraph
            if ($i == $centerInsertionPoint && !empty($imagesByPosition['center'])) {
                foreach ($imagesByPosition['center'] as $image) {
                    $imagePath = getImagePath($image['image_path']);
                    $caption = !empty($image['caption']) ? htmlspecialchars($image['caption']) : '';
                    $contentWithImages .= '<div class="position-image center-position">';
                    $contentWithImages .= '<img src="' . $imagePath . '" alt="' . htmlspecialchars($news['title']) . '">';
                    if ($caption) {
                        $contentWithImages .= '<div class="image-caption">' . $caption . '</div>';
                    }
                    $contentWithImages .= '</div>';
                }
            }
        }
    }
    
    // Add bottom images after content if they exist
    if (!empty($imagesByPosition['bottom'])) {
        $contentWithImages .= '<div class="bottom-images-container">';
        foreach ($imagesByPosition['bottom'] as $image) {
            $imagePath = getImagePath($image['image_path']);
            $caption = !empty($image['caption']) ? htmlspecialchars($image['caption']) : '';
            $contentWithImages .= '<div class="position-image bottom-position">';
            $contentWithImages .= '<img src="' . $imagePath . '" alt="' . htmlspecialchars($news['title']) . '">';
            if ($caption) {
                $contentWithImages .= '<div class="image-caption">' . $caption . '</div>';
            }
            $contentWithImages .= '</div>';
        }
        $contentWithImages .= '</div>';
    }
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $name = cleanTamilText($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $comment = cleanTamilText($_POST['comment'] ?? '');
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
    
    if (!empty($name) && !empty($comment)) {
        // Also clean before storing in database
        $cleanName = cleanTamilText($name);
        $cleanComment = cleanTamilText($comment);
        
        $insertQuery = "INSERT INTO comments (news_id, parent_id, name, email, comment, status) 
                        VALUES (?, ?, ?, ?, ?, 'pending')";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->execute([$newsId, $parent_id, $cleanName, $email, $cleanComment]);
        
        // Redirect to prevent form resubmission
        header("Location: news-detail.php?id=" . $newsId . "#comments");
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
    
    header("Location: news-detail.php?id=" . $newsId . "#comments");
    exit();
}

// Fetch only approved comments for this news
$commentsQuery = "SELECT * FROM comments 
                  WHERE news_id = ? AND status = 'approved' 
                  ORDER BY 
                    CASE WHEN parent_id = 0 THEN id ELSE parent_id END,
                    created_at ASC";
$commentsStmt = $db->prepare($commentsQuery);
$commentsStmt->execute([$newsId]);
$allComments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Clean comment data before displaying
foreach ($allComments as &$comment) {
    $comment['name'] = cleanTamilText($comment['name']);
    $comment['comment'] = cleanTamilText($comment['comment']);
}

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
            // Get first character of each word (for Tamil text)
            preg_match('/./u', $word, $matches);
            if (!empty($matches[0])) {
                $initials .= $matches[0];
            }
        }
    }
    return mb_substr($initials, 0, 2, 'UTF-8');
}

// Function to get image path with base_url
function getImagePath($imagePath) {
    global $base_url;
    
    if (empty($imagePath)) {
        return 'https://picsum.photos/id/1011/1200/600';
    }
    
    // Check if it's already a full URL
    if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
        return $imagePath;
    }
    
    // Prepend base_url for relative paths
    return $base_url . ltrim($imagePath, '/');
}

// Get main article image (from news table if exists, otherwise from images)
$mainImagePath = '';
if (!empty($news['image'])) {
    $mainImagePath = getImagePath($news['image']);
} else {
    // Try to use top image as fallback
    if (!empty($imagesByPosition['top'][0]['image_path'])) {
        $mainImagePath = getImagePath($imagesByPosition['top'][0]['image_path']);
    } else {
        $mainImagePath = 'https://picsum.photos/id/1011/1200/600';
    }
}

// Get current URL for sharing
$currentUrl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$shareTitle = urlencode($news['title']);
$shareUrl = urlencode($currentUrl);
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
    
    <!-- Social sharing meta tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($news['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars(substr(strip_tags($news['content']), 0, 150)); ?>...">
    <meta property="og:image" content="<?php echo $mainImagePath; ?>">
    <meta property="og:url" content="<?php echo $currentUrl; ?>">
    <meta property="og:type" content="article">
    <meta name="twitter:card" content="summary_large_image">
    
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
            text-decoration: none;
        }
        
        .chip:hover { transform: translateY(-2px); background: rgba(255,17,17,.18) }
        .chip.active { background: linear-gradient(180deg, var(--red), #d10f0f); color: #fff; border: 0 }
        
        .news-count {
            background: var(--red);
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            font-weight: 700;
        }
        
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
            max-width: 1200px;
            color:  #fff;
            margin: 40px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
        }
        
        @media (max-width: 768px) {
            .article-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                order: 2;
                margin-top: 40px;
            }
            
            .article-main {
                order: 1;
            }
        }
        
        .article-main {
            max-width: 800px;
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
        
        .article-content h3 {
            font-size: 20px;
            margin: 25px 0 15px;
            color: var(--yellow);
        }
        
        .article-content ul,
        .article-content ol {
            margin: 15px 0;
            padding-left: 20px;
        }
        
        .article-content li {
            margin-bottom: 8px;
        }
        
        .article-content strong {
            color: var(--yellow);
        }
        
        .article-content em {
            color: var(--muted);
        }
        
        .article-content a {
            color: var(--red);
            text-decoration: none;
        }
        
        .article-content a:hover {
            text-decoration: underline;
        }
        
        .video-container {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            height: 0;
            margin: 30px 0;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }
        
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        
        /* Image Position Styles */
        .top-images-container,
        .center-images-container,
        .bottom-images-container {
            margin: 30px 0;
        }
        
        .position-images {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .position-image {
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            margin: 20px 0;
        }
        
        .position-image.top-position {
            margin-top: 0;
            margin-bottom: 30px;
        }
        
        .position-image.center-position {
            margin: 30px 0;
        }
        
        .position-image.bottom-position {
            margin-top: 30px;
            margin-bottom: 0;
        }
        
        .position-image img {
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: cover;
            display: block;
        }

        /* Make first positioned image slightly larger,
           second and third images smaller and allow text wrapping */
        .article-content .position-image:nth-of-type(1) img,
        .position-image.top-position img {
            max-height: 520px; /* slightly bigger */
        }

        /* second positioned image: float the container so text wraps around it */
        .article-content .position-image:nth-of-type(2),
        .position-image.center-position {
            float: left;
            width: 42%;
            margin: 0 24px 18px 0;
        }

        .article-content .position-image:nth-of-type(2) img,
        .position-image.center-position img {
            max-height: 240px;
            width: 100%;
            display: block;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.45);
        }

        /* Try to vertically center the center image with the adjacent paragraph on desktop */
        @media (min-width: 769px) {
            .position-image.center-position {
                display: inline-block;
                vertical-align: middle;
            }

            .position-image.center-position + p {
                display: inline-block;
                vertical-align: middle;
                width: calc(58% - 24px);
                margin-top: 0;
            }

            /* If there is a preceding paragraph, allow it to sit above so the image centers against the following paragraph block */
            p + .position-image.center-position {
                margin-top: 8px;
            }
        }

        /* third positioned image: also floated and smaller */
        .article-content .position-image:nth-of-type(3),
        .position-image.bottom-position {
            float: left;
            width: 36%;
            margin: 0 24px 18px 0;
        }

        .article-content .position-image:nth-of-type(3) img,
        .position-image.bottom-position img {
            max-height: 180px;
            width: 100%;
            display: block;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 8px 22px rgba(0,0,0,0.38);
        }

        /* Clear floats after content blocks so subsequent blocks don't wrap */
        .article-content:after {
            content: "";
            display: block;
            clear: both;
        }
        
        .image-caption {
            text-align: center;
            font-size: 14px;
            color: var(--muted);
            margin-top: 8px;
            font-style: italic;
            padding: 0 10px;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: sticky;
            top: 100px;
            height: fit-content;
        }
        
        .share-section {
            background: var(--card);
            border-radius: var(--radius);
            padding: 24px;
            margin-bottom: 30px;
            border: var(--border);
        }
        
        .share-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .share-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .share-button {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: var(--card-hi);
            border: var(--border);
            border-radius: var(--radius-xs);
            color: var(--text);
            text-decoration: none;
            transition: transform var(--trans), background var(--trans);
        }
        
        .share-button:hover {
            transform: translateY(-2px);
            background: var(--glass);
        }
        
        .share-button.whatsapp {
            color: #25D366;
        }
        
        .share-button.facebook {
            color: #1877F2;
        }
        
        .share-button.twitter {
            color: #1DA1F2;
        }
        
        .share-button.linkedin {
            color: #0A66C2;
        }
        
        .share-button.email {
            color: #EA4335;
        }
        
        .share-button.pinterest {
            color: #E60023;
        }
        
        .share-icon {
            width: 24px;
            height: 24px;
        }
        
        .share-label {
            flex: 1;
            font-weight: 600;
        }
        
        /* Related News Section */
        .related-news {
            background: var(--card);
            border-radius: var(--radius);
            padding: 24px;
            border: var(--border);
        }
        
        .related-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .related-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }
        
        .related-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .related-item {
            display: block;
            padding: 12px;
            background: var(--card-hi);
            border-radius: var(--radius-xs);
            border: var(--border);
            text-decoration: none;
            transition: transform var(--trans), background var(--trans);
        }
        
        .related-item:hover {
            transform: translateY(-2px);
            background: var(--glass);
        }
        
        .related-item-title {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text);
        }
        
        .related-item-time {
            font-size: 12px;
            color: var(--muted);
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
            white-space: pre-line; /* Preserve line breaks */
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
        
        .success-message {
            background: rgba(0, 255, 0, 0.1);
            border: 1px solid rgba(0, 255, 0, 0.3);
            color: #00ff00;
            padding: 12px;
            border-radius: var(--radius-xs);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .pending-message {
            background: rgba(255, 255, 0, 0.1);
            border: 1px solid rgba(255, 255, 0, 0.3);
            color: #ffff00;
            padding: 12px;
            border-radius: var(--radius-xs);
            margin-bottom: 20px;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .article-title {
                font-size: 24px;
            }
            
            .article-image {
                height: 250px;
            }
            
            .position-image img {
                height: 250px;
                object-fit: cover;
                width: 100%;
                float: none;
                margin: 20px 0;
            }

            /* On small screens, don't float small images; stack them */
            .position-image.center-position img,
            .position-image.bottom-position img {
                width: 100%;
                max-height: 320px;
                float: none;
                margin: 16px 0;
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
            
            .video-container {
                margin: 20px 0;
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

    <!-- Category Navigation -->
    <nav class="catbar" aria-label="Categories">
        <div class="catbar-wrap">
            <a href="index.php" class="chip">முகப்பு</a>
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
                <a href="categories.php?id=<?php echo $category['id']; ?>" class="chip">
                    <?php echo htmlspecialchars($category['name']); ?>
                    <?php if ($count['count'] > 0): ?>
                        <span class="news-count"><?php echo $count['count']; ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>

    <!-- ARTICLE CONTENT -->
    <main class="article-container">
        <div class="article-main">
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
                
                <!-- Main Article Image -->
                <img src="<?php echo $mainImagePath; ?>" 
                     alt="<?php echo htmlspecialchars($news['title']); ?>" 
                     class="article-image">
                
                <!-- Article Content with Positioned Images -->
                <div class="article-content">
                    <?php echo $contentWithImages; ?>
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
                    <?php if (isset($_GET['success']) && $_GET['success'] == 'true'): ?>
                        <div class="pending-message">
                            உங்கள் கருத்து வெற்றிகரமாக சமர்ப்பிக்கப்பட்டது. நிர்வாகியால் அனுமதிக்கப்பட்ட பிறகு காட்டப்படும்.
                        </div>
                    <?php endif; ?>
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
        </div>

        <!-- SIDEBAR -->
        <div class="sidebar">
            <!-- Share Buttons -->
            <div class="share-section">
                <h3 class="share-title">பகிர்</h3>
                <div class="share-buttons">
                    <a href="https://api.whatsapp.com/send?text=<?php echo $shareTitle . ' ' . $shareUrl; ?>" 
                       target="_blank" class="share-button whatsapp">
                        <svg class="share-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.76.982.998-3.677-.236-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.826 9.826 0 012.9 6.994c-.004 5.45-4.438 9.88-9.888 9.88m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.333.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.333 11.893-11.893 0-3.18-1.24-6.162-3.495-8.411"/>
                        </svg>
                        <span class="share-label">WhatsApp</span>
                    </a>
                    
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $shareUrl; ?>" 
                       target="_blank" class="share-button facebook">
                        <svg class="share-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18.77 7.46H14.5v-1.9c0-.9.6-1.1 1-1.1h3V.5h-4.33C10.24.5 9.5 3.44 9.5 5.32v2.15h-3v4h3v12h5v-12h3.85l.42-4z"/>
                        </svg>
                        <span class="share-label">Facebook</span>
                    </a>
                    
                    <a href="https://twitter.com/intent/tweet?text=<?php echo $shareTitle; ?>&url=<?php echo $shareUrl; ?>" 
                       target="_blank" class="share-button twitter">
                        <svg class="share-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M23.44 4.83c-.8.37-1.5.38-2.22.02.93-.56.98-.96 1.32-2.02-.88.52-1.86.9-2.9 1.1-.82-.88-2-1.43-3.3-1.43-2.5 0-4.55 2.04-4.55 4.54 0 .36.03.7.1 1.04-3.77-.2-7.12-2-9.36-4.75-.4.67-.6 1.45-.6 2.3 0 1.56.8 2.95 2 3.77-.74-.03-1.44-.23-2.05-.57v.06c0 2.2 1.56 4.03 3.64 4.44-.67.2-1.37.2-2.06.08.58 1.8 2.26 3.12 4.25 3.16C5.78 18.1 3.37 18.74 1 18.46c2 1.3 4.4 2.04 6.97 2.04 8.35 0 12.92-6.92 12.92-12.93 0-.2 0-.4-.02-.6.9-.63 1.96-1.22 2.56-2.14z"/>
                        </svg>
                        <span class="share-label">Twitter</span>
                    </a>
                    
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $shareUrl; ?>&title=<?php echo $shareTitle; ?>" 
                       target="_blank" class="share-button linkedin">
                        <svg class="share-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                        <span class="share-label">LinkedIn</span>
                    </a>
                    
                    <a href="mailto:?subject=<?php echo $shareTitle; ?>&body=<?php echo $shareUrl; ?>" 
                       class="share-button email">
                        <svg class="share-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                        </svg>
                        <span class="share-label">Email</span>
                    </a>
                    
                    <a href="https://pinterest.com/pin/create/button/?url=<?php echo $shareUrl; ?>&media=<?php echo $mainImagePath; ?>&description=<?php echo $shareTitle; ?>" 
                       target="_blank" class="share-button pinterest">
                        <svg class="share-icon" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12.14.5C5.86.5 2.7 5 2.7 8.75c0 2.27.86 4.3 2.7 5.05.3.12.57 0 .66-.33l.27-1.06c.1-.32.06-.44-.2-.73-.52-.62-.86-1.44-.86-2.6 0-3.33 2.5-6.32 6.5-6.32 3.55 0 5.5 2.17 5.5 5.07 0 3.8-1.7 7.02-4.2 7.02-1.37 0-2.4-1.14-2.07-2.54.4-1.68 1.16-3.48 1.16-4.7 0-1.07-.58-1.98-1.78-1.98-1.4 0-2.55 1.47-2.55 3.42 0 1.25.43 2.1.43 2.1l-1.7 7.2c-.5 2.13-.08 4.75-.04 5 .02.17.22.2.3.1.14-.18 1.82-2.26 2.4-4.33.16-.58.93-3.63.93-3.63.45.88 1.8 1.65 3.22 1.65 4.25 0 7.13-3.87 7.13-9.05C20.5 4.15 17.18.5 12.14.5z"/>
                        </svg>
                        <span class="share-label">Pinterest</span>
                    </a>
                </div>
            </div>

            <!-- Related News -->
            <?php
            // Fetch related news from the same category
            $relatedQuery = "SELECT n.id, n.title, n.created_at 
                            FROM news n 
                            WHERE FIND_IN_SET(?, n.categories) > 0 
                            AND n.id != ? 
                            AND n.status = 'published' 
                            ORDER BY n.created_at DESC 
                            LIMIT 5";
            $relatedStmt = $db->prepare($relatedQuery);
            $relatedStmt->execute([$news['categories'], $newsId]);
            $relatedNews = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($relatedNews)): ?>
                <div class="related-news">
                    <h3 class="related-title">தொடர்புடைய செய்திகள்</h3>
                    <div class="related-list">
                        <?php foreach ($relatedNews as $related): ?>
                            <a href="news-detail.php?id=<?php echo $related['id']; ?>" class="related-item">
                                <div class="related-item-title"><?php echo htmlspecialchars(cleanTamilText($related['title'])); ?></div>
                                <div class="related-item-time"><?php echo getTamilTimeAgo($related['created_at']); ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
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
            
            // Copy URL to clipboard functionality
            const copyUrlBtn = document.getElementById('copyUrlBtn');
            if (copyUrlBtn) {
                copyUrlBtn.addEventListener('click', function() {
                    const url = window.location.href;
                    navigator.clipboard.writeText(url).then(function() {
                        const originalText = copyUrlBtn.textContent;
                        copyUrlBtn.textContent = 'URL Copied!';
                        setTimeout(function() {
                            copyUrlBtn.textContent = originalText;
                        }, 2000);
                    });
                });
            }
        });
    </script>
</body>
</html>