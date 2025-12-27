<?php
// Start session at the very beginning
session_start();

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

// Function to preserve HTML tags and embed videos - PRESERVE FORMATTING
function processNewsContent($content) {
    // Decode HTML entities first
    $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
    
    // Preserve HTML tags (allow basic formatting tags)
    $allowed_tags = '<strong><b><em><i><u><br><p><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><div><span><a><img>';
    $content = strip_tags($content, $allowed_tags);
    
    // Convert line breaks to <br> for paragraphs without tags
    $content = preg_replace('/\r\n|\r|\n/', '<br>', $content);
    
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

// Determine main image - prefer top position, then feature image
$mainImagePath = '';
$skipTopInContent = false;

// Check for top-positioned images first
if (!empty($imagesByPosition['top'][0]['image_path'])) {
    // Use first top image as main image
    $mainImagePath = getImagePath($imagesByPosition['top'][0]['image_path']);
    $skipTopInContent = true;
} elseif (!empty($news['image'])) {
    // If no top image, use feature image
    $mainImagePath = getImagePath($news['image']);
} else {
    // Fallback to default image
    $mainImagePath = 'https://picsum.photos/id/1011/1200/600';
}

// Process content with positioned images
$contentWithImages = '';
if (!empty($news['content'])) {
    // Remove top image from content if it's being used as main image
    if ($skipTopInContent && !empty($imagesByPosition['top'][0])) {
        // Remove the first top image since it's already displayed as main
        array_shift($imagesByPosition['top']);
    }
    
    // Safely split content into two parts without breaking HTML tags.
    $paragraphs = [];

    // Capture full <p>...</p> blocks first
    if (preg_match_all('/<p\b[^>]*>.*?<\/p>/siu', $news['content'], $matches)) {
        $paragraphs = $matches[0];
    } else {
        // Fallback: split on <br> or treat whole content as single block
        $parts = preg_split('/(<br\s*\/?\s*>)/i', $news['content']);
        // Merge delimiters back with text so we keep markup in the pieces
        if ($parts && count($parts) > 1) {
            $recombined = [];
            $buf = '';
            foreach ($parts as $p) {
                if (preg_match('/^<br/i', trim($p))) {
                    $buf .= $p;
                    $recombined[] = $buf;
                    $buf = '';
                } else {
                    $buf .= $p;
                }
            }
            if ($buf !== '') $recombined[] = $buf;
            $paragraphs = $recombined ?: [$news['content']];
        } else {
            $paragraphs = [$news['content']];
        }
    }

    // Determine split index (near the middle paragraph)
    $count = count($paragraphs);
    if ($count <= 1) {
        $part1 = $news['content'];
        $part2 = '';
    } else {
        $mid = floor($count / 2);
        $part1 = implode('', array_slice($paragraphs, 0, $mid));
        $part2 = implode('', array_slice($paragraphs, $mid));
    }
    
    // Build content with images
    $contentWithImages = '';
    
    // Add remaining top images at the beginning
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
    
    // Add first part of content
    $contentWithImages .= '<div class="content-part">' . $part1 . '</div>';

    // Add center images between parts — place the first center image side-by-side
    if (!empty($imagesByPosition['center'])) {
        // Use the first center image inline with the following text
        $firstCenter = array_shift($imagesByPosition['center']);
        $imagePath = getImagePath($firstCenter['image_path']);
        $caption = !empty($firstCenter['caption']) ? htmlspecialchars($firstCenter['caption']) : '';

        $contentWithImages .= '<div class="img-text-row">';
        $contentWithImages .= '<div class="position-image center-position inline-center">';
        $contentWithImages .= '<img src="' . $imagePath . '" alt="' . htmlspecialchars($news['title']) . '">';
        if ($caption) {
            $contentWithImages .= '<div class="image-caption">' . $caption . '</div>';
        }
        $contentWithImages .= '</div>'; // .position-image

        $contentWithImages .= '<div class="img-text-content">' . $part2 . '</div>';
        $contentWithImages .= '</div>'; // .img-text-row

        // Render any remaining center images stacked after the inline block
        if (!empty($imagesByPosition['center'])) {
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

        // Prevent adding part2 again below
        $part2 = '';
    } else {
        // No center images, just add second part normally
        $contentWithImages .= '<div class="content-part">' . $part2 . '</div>';
    }
    
    // Add bottom images at the end
    if (!empty($imagesByPosition['bottom'])) {
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
    }

    // If there is an embedded video URL field in news, render it after the content
    if (!empty($news['embedded_video_url'])) {
        $embedUrl = trim($news['embedded_video_url']);
        // Basic server-side embed detection (YouTube, Vimeo, Facebook)
        $embedHtml = '';
        if (preg_match('/(?:youtube\.com|youtu\.be)/i', $embedUrl)) {
            // extract video id
            if (preg_match('/(?:v=|\/)([a-zA-Z0-9_-]{11})/i', $embedUrl, $m)) {
                $vid = $m[1];
                $embedHtml = '<div class="video-container"><iframe src="https://www.youtube.com/embed/' . htmlspecialchars($vid) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
            } else {
                $embedHtml = '<div class="video-container"><iframe src="' . htmlspecialchars($embedUrl) . '" frameborder="0" allowfullscreen></iframe></div>';
            }
        } elseif (preg_match('/vimeo\.com/i', $embedUrl)) {
            if (preg_match('/(\d+)/', $embedUrl, $m)) {
                $vid = $m[1];
                $embedHtml = '<div class="video-container"><iframe src="https://player.vimeo.com/video/' . htmlspecialchars($vid) . '" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div>';
            } else {
                $embedHtml = '<div class="video-container"><iframe src="' . htmlspecialchars($embedUrl) . '" frameborder="0" allowfullscreen></iframe></div>';
            }
        } elseif (preg_match('/facebook\.com/i', $embedUrl)) {
            $embedHtml = '<div class="video-container"><iframe src="https://www.facebook.com/plugins/video.php?href=' . urlencode($embedUrl) . '&show_text=0&width=560" width="560" height="315" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe></div>';
        } else {
            // Generic iframe or link
            $embedHtml = '<div class="video-container"><iframe src="' . htmlspecialchars($embedUrl) . '" frameborder="0" allowfullscreen></iframe></div>';
        }

        $contentWithImages .= $embedHtml;
    }
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : 0;
    
    // Validate required fields
    if (!empty($name) && !empty($email) && !empty($comment)) {
        try {
            // Clean the data - don't over-clean names
            $cleanName = htmlspecialchars(strip_tags($name));
            $cleanComment = htmlspecialchars(strip_tags($comment));
            $cleanEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
            
            // Insert with 'pending' status for moderation
            $insertQuery = "INSERT INTO comments (news_id, parent_id, name, email, comment, status) 
                            VALUES (?, ?, ?, ?, ?, 'pending')";
            $insertStmt = $db->prepare($insertQuery);
            $result = $insertStmt->execute([$newsId, $parent_id, $cleanName, $cleanEmail, $cleanComment]);
            
            if ($result) {
                // Get the new comment ID
                $newCommentId = $db->lastInsertId();
                
                // Redirect to prevent form resubmission and show success message
                header("Location: news-detail.php?id=" . $newsId . "&comment_submitted=true&new_comment=" . $newCommentId . "#comment-" . $newCommentId);
                exit();
            } else {
                $error_message = "கருத்து சமர்ப்பிப்பதில் பிழை ஏற்பட்டது. மீண்டும் முயற்சிக்கவும்.";
            }
        } catch (PDOException $e) {
            $error_message = "தரவுத்தள பிழை: " . $e->getMessage();
        }
    } else {
        $error_message = "அனைத்து புலங்களையும் நிரப்பவும்.";
    }
}

// Handle comment like
if (isset($_GET['like_comment'])) {
    $commentId = intval($_GET['like_comment']);
    
    // Check if user already liked this comment (using session)
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

// Fetch only approved comments for this news - PRESERVE HTML
$commentsQuery = "SELECT * FROM comments 
                  WHERE news_id = ? AND status = 'approved' 
                  ORDER BY 
                    CASE WHEN parent_id = 0 THEN id ELSE parent_id END,
                    created_at ASC";
$commentsStmt = $db->prepare($commentsQuery);
$commentsStmt->execute([$newsId]);
$allComments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Clean comment data before displaying - but preserve HTML tags
foreach ($allComments as &$comment) {
    $comment['name'] = cleanTamilText($comment['name']);
    // Preserve HTML tags in comment content but sanitize
    $comment['comment'] = strip_tags($comment['comment'], '<strong><b><em><i><u><br><p><span>');
    $comment['comment'] = htmlspecialchars_decode($comment['comment']);
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

// Count total comments
$totalComments = count($allComments);

// Get current URL for sharing and build share text (title + excerpt + link) in Tamil
$currentUrl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$excerpt = trim(strip_tags(mb_substr($news['content'], 0, 220)));
$shareText = $news['title'] . "\n\n" . $excerpt . "\n\n" . $currentUrl;
$shareTextEncoded = urlencode($shareText);
$shareUrl = urlencode($currentUrl);
?>

<!DOCTYPE html>
<html lang="ta">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($news['title']); ?> - Liked தமிழ்</title>
    
    <!-- Favicon -->
    <?php include 'includes/favicon.php'; ?>
    
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
        :root {
            --primary-red: #e63946;
            --primary-dark-red: #c1121f;
            --accent-yellow: #ffd166;
            --black: #000000;
            --white: #ffffff;
            
            --bg-primary: #0a0a0a;
            --bg-secondary: #121212;
            --bg-card: #1a1a1a;
            --bg-hover: #222222;
            
            --text-primary: #f8f9fa;
            --text-secondary: #adb5bd;
            --text-muted: #6c757d;
            
            --border-color: rgba(255, 255, 255, 0.1);
            --glass-bg: rgba(255, 255, 255, 0.05);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.2);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.3);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.4);
            
            --space-xs: 4px;
            --space-sm: 8px;
            --space-md: 16px;
            --space-lg: 24px;
            --space-xl: 32px;
            --space-2xl: 48px;
            
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-full: 9999px;
            
            --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-base: 250ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 350ms cubic-bezier(0.4, 0, 0.2, 1);
            
            --font-heading: 'Noto Sans Tamil', 'Inter', system-ui, sans-serif;
            --font-body: 'Noto Sans Tamil', 'Inter', system-ui, sans-serif;
        }
        
        /* Light mode variables */
        .light-mode {
            --primary-red: #e63946;
            --primary-dark-red: #c1121f;
            --accent-yellow: #ffaa00;
            --black: #000000;
            --white: #ffffff;
            
            --bg-primary: #f8f9fa;
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --bg-hover: #f8f9fa;
            
            --text-primary: #1a1a1a;
            --text-secondary: #495057;
            --text-muted: #6c757d;
            
            --border-color: rgba(0, 0, 0, 0.12);
            --glass-bg: rgba(0, 0, 0, 0.04);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.12);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.15);
        }
        
        /* ===== Reset & Base Styles ===== */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        html {
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
            scroll-behavior: smooth;
        }
        
        body {
            font-family: var(--font-body);
            color: var(--text-primary);
            background: var(--bg-primary);
            line-height: 1.6;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
            transition: background-color var(--transition-base), color var(--transition-base);
        }
        
        /* Background gradient - only for dark mode */
        body:not(.light-mode)::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 100vh;
            background: 
                radial-gradient(circle at 20% 80%, rgba(230, 57, 70, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 209, 102, 0.1) 0%, transparent 50%);
            z-index: -1;
            pointer-events: none;
        }
        
        /* ===== Header Fixes ===== */
        .header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(10, 10, 10, 0.98);
            backdrop-filter: blur(20px) saturate(180%);
            border-bottom: 1px solid var(--border-color);
            padding: var(--space-sm) 0;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--space-md);
            height: 60px;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            flex: 1;
            min-width: 0;
        }
        
        .logo {
            height: 40px;
            width: auto;
            object-fit: contain;
            border-radius: var(--radius-sm);
            max-width: 150px;
        }
        
        @media (min-width: 640px) {
            .logo {
                height: 48px;
                max-width: 200px;
            }
        }
        
        .site-title {
            font-family: var(--font-heading);
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            flex-shrink: 1;
        }
        
        @media (min-width: 640px) {
            .site-title {
                font-size: 1.5rem;
            }
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            flex-shrink: 0;
        }
        
        .search-btn {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            width: 40px;
            height: 40px;
            border-radius: var(--radius-full);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-fast);
        }
        
        .search-btn:hover {
            color: var(--text-primary);
            background: var(--glass-bg);
        }
        
        /* Theme toggle button */
        .theme-toggle {
            background: var(--glass-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-full);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all var(--transition-fast);
            color: var(--text-secondary);
        }
        
        .theme-toggle:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
            background: var(--bg-hover);
            color: var(--text-primary);
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
        
        .subscribe-btn {
            display: none;
        }
        
        @media (min-width: 768px) {
            .subscribe-btn {
                display: inline-flex;
                align-items: center;
                gap: var(--space-xs);
                padding: var(--space-sm) var(--space-md);
                background: linear-gradient(135deg, var(--primary-red), var(--primary-dark-red));
                color: var(--white);
                border: none;
                border-radius: var(--radius-md);
                font-family: var(--font-body);
                font-weight: 600;
                font-size: 0.875rem;
                cursor: pointer;
                transition: all var(--transition-base);
            }
            
            .subscribe-btn:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-md);
            }
        }
        
        /* ===== Category Navigation Fixes ===== */
        .category-nav {
            position: sticky;
            top: 60px; /* Match header height */
            z-index: 900;
            background: rgba(18, 18, 18, 0.98);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            padding: var(--space-sm) 0;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }
        
        .category-nav::-webkit-scrollbar {
            display: none;
        }
        
        .category-list {
            display: flex;
            gap: var(--space-xs);
            padding: 0 var(--space-md);
            list-style: none;
            min-width: max-content;
        }
        
        @media (min-width: 640px) {
            .category-list {
                gap: var(--space-sm);
                padding: 0 var(--space-lg);
                justify-content: center;
            }
        }
        
        .category-link {
            display: inline-flex;
            align-items: center;
            gap: var(--space-xs);
            padding: var(--space-sm) var(--space-md);
            background: var(--glass-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-full);
            color: var(--text-secondary);
            font-family: var(--font-body);
            font-weight: 500;
            font-size: 0.875rem;
            white-space: nowrap;
            transition: all var(--transition-base);
        }
        
        .category-link:hover,
        .category-link.active {
            background: linear-gradient(135deg, var(--primary-red), var(--primary-dark-red));
            color: var(--white);
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
        
        /* ===== Main Content ===== */
        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 var(--space-md);
        }
        
        @media (min-width: 640px) {
            .container {
                padding: 0 var(--space-lg);
            }
        }
        
        .main-content {
            padding: var(--space-xl) 0;
            min-height: calc(100vh - 180px);
        }
        
        /* Article Container */
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
                padding-bottom: 100px;
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
            background: linear-gradient(180deg, var(--primary-red), var(--primary-dark-red));
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
            color: var(--text-primary);
        }
        
        .article-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 30px;
        }
        
        .article-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: var(--radius-lg);
            margin-bottom: 30px;
        }
        
        .article-content {
            font-size: 18px;
            line-height: 1.8;
            color: var(--text-primary);
        }
        
        .article-content p {
            margin-bottom: 20px;
        }
        
        .article-content h2 {
            font-size: 24px;
            margin: 30px 0 20px;
            color: var(--accent-yellow);
        }
        
        .article-content h3 {
            font-size: 20px;
            margin: 25px 0 15px;
            color: var(--accent-yellow);
        }
        
        .article-content ul,
        .article-content ol {
            margin: 15px 0;
            padding-left: 20px;
        }
        
        .article-content li {
            margin-bottom: 8px;
        }
        
        .article-content strong,
        .article-content b {
            color: #ffffff;
            font-weight: 700;
            background: linear-gradient(45deg, var(--primary-red), var(--accent-yellow));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            padding: 2px 4px;
            border-radius: 4px;
        }

        .article-content em,
        .article-content i {
            color: #ffff99;
            font-style: italic;
        }

        .article-content u {
            text-decoration: underline;
            text-decoration-color: var(--accent-yellow);
            text-decoration-thickness: 2px;
        }
        
        .article-content a {
            color: var(--primary-red);
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
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
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
        .position-image {
            width: 100%;
            border-radius: var(--radius-lg);
            overflow: hidden;
            margin: 30px 0;
        }
        
        .position-image.top-position {
            margin-top: 0;
            margin-bottom: 30px;
        }
        
        .position-image.center-position {
            margin: 40px 0;
            text-align: center;
        }
        
        .position-image.bottom-position {
            margin-top: 40px;
            margin-bottom: 0;
        }
        
        .position-image img {
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: cover;
            display: block;
            border-radius: var(--radius-lg);
        }

        /* Inline image + text row for center images */
        .img-text-row {
            display: flex;
            gap: 24px;
            align-items: center;
            margin: 24px 0;
        }

        .img-text-row .img-text-content {
            flex: 1;
        }

        .position-image.center-position.inline-center {
            width: 48%;
            margin: 0;
        }

        .position-image.center-position.inline-center img {
            width: 100%;
            height: auto;
            max-height: 360px;
            object-fit: cover;
            display: block;
            border-radius: var(--radius-lg);
        }

        .image-caption {
            text-align: center;
            font-size: 14px;
            color: var(--text-muted);
            margin-top: 8px;
            font-style: italic;
            padding: 0 10px;
        }
        
        .content-part {
            margin-bottom: 20px;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: sticky;
            top: 100px;
            height: fit-content;
        }
        
        .share-section {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }
        
        .share-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Share Icons */
        .share-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        @media (max-width: 768px) {
            .share-buttons {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 480px) {
            .share-buttons {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .share-button {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px 10px;
            background: var(--bg-hover);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            text-decoration: none;
            transition: all var(--transition-base);
            text-align: center;
            min-height: 80px;
            position: relative;
            overflow: hidden;
        }

        .share-button:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            background: var(--glass-bg);
        }

        .share-button:hover .share-icon {
            transform: scale(1.2);
        }

        .share-icon {
            width: 24px;
            height: 24px;
            margin-bottom: 8px;
            transition: transform var(--transition-base);
        }

        .share-label {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-primary);
            opacity: 1;
            transform: none;
            position: relative;
            bottom: auto;
            left: auto;
            right: auto;
            text-align: left;
            margin-top: 6px;
            font-family: 'Noto Sans Tamil', sans-serif;
        }

        /* Hide default SVG path and show custom ones */
        .share-icon path {
            fill: currentColor;
        }

        /* Individual share button colors */
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

        .share-button.telegram {
            color: #0088cc;
        }

        .share-button.pinterest {
            color: #E60023;
        }

        /* Share button tooltips */
        .share-tooltip {
            position: absolute !important;
            background: var(--bg-card) !important;
            color: var(--text-primary) !important;
            padding: 8px 12px !important;
            border-radius: 6px !important;
            font-size: 12px !important;
            white-space: nowrap !important;
            z-index: 1000 !important;
            box-shadow: var(--shadow-sm) !important;
            border: 1px solid var(--border-color) !important;
            transform: translateX(-50%) !important;
            top: -40px !important;
            left: 50% !important;
            pointer-events: none !important;
        }

        .share-tooltip::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid var(--bg-card);
        }
        
        /* Related News Section */
        .related-news {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 24px;
            border: 1px solid var(--border-color);
        }
        
        .related-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .related-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }
        
        .related-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .related-item {
            display: block;
            padding: 12px;
            background: var(--bg-hover);
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            text-decoration: none;
            transition: transform var(--transition-base), background var(--transition-base);
        }
        
        .related-item:hover {
            transform: translateY(-2px);
            background: var(--glass-bg);
        }
        
        .related-item-title {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
        }
        
        .related-item-time {
            font-size: 12px;
            color: var(--text-muted);
        }
        
        .comments-section {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid var(--border-color);
        }
        
        .comments-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 30px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .comments-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-color);
            margin-left: 15px;
        }
        
        .comment-count {
            color: var(--primary-red);
            font-weight: 800;
        }
        
        .comment-form {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 24px;
            margin-bottom: 40px;
            border: 1px solid var(--border-color);
        }
        
        .comment-form-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-primary);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-hover);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-family: inherit;
            font-size: 15px;
            transition: border-color var(--transition-base);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-red);
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
            background: linear-gradient(135deg, var(--primary-red), var(--primary-dark-red));
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: transform var(--transition-base), opacity var(--transition-base);
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
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 20px;
            border: 1px solid var(--border-color);
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
            background: linear-gradient(45deg, var(--primary-red), var(--accent-yellow));
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
            color: var(--text-muted);
        }
        
        .comment-actions {
            display: flex;
            gap: 12px;
        }
        
        .comment-action {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 6px;
            transition: all var(--transition-base);
        }
        
        .comment-action:hover {
            color: var(--text-primary);
            background: var(--glass-bg);
        }
        
        .comment-action.liked {
            color: var(--primary-red);
        }
        
        .comment-content {
            line-height: 1.6;
            color: var(--text-primary);
            white-space: pre-line; /* Preserve line breaks */
        }
        
        .comment-replies {
            margin-top: 20px;
            margin-left: 20px;
            padding-left: 20px;
            border-left: 2px solid var(--glass-bg);
        }
        
        .reply-form {
            margin-top: 16px;
            padding: 16px;
            background: var(--bg-hover);
            border-radius: var(--radius-sm);
        }
        
        .show-replies {
            margin-top: 12px;
            color: var(--primary-red);
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
            color: var(--text-muted);
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
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
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .pending-message {
            background: rgba(255, 255, 0, 0.1);
            border: 1px solid rgba(255, 255, 0, 0.3);
            color: #ffff00;
            padding: 12px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
            text-align: center;
        }
        
        /* Comment approval handling */
        .comment-item.pending {
            opacity: 0.7;
            border-left: 4px solid var(--accent-yellow);
        }

        .comment-item.approved {
            border-left: 4px solid #25D366;
        }

        .pending-badge {
            background: var(--accent-yellow);
            color: var(--black);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .feedback-success {
            background: linear-gradient(45deg, rgba(0, 200, 0, 0.2), rgba(0, 150, 0, 0.1));
            border: 1px solid rgba(0, 255, 0, 0.3);
            color: #00ff00;
            padding: 16px;
            border-radius: var(--radius-sm);
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            animation: fadeIn 0.5s ease;
        }
        
        .feedback-success-icon {
            font-size: 24px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* ===== Desktop Footer ===== */
        .desktop-footer {
            display: none;
        }
        
        @media (min-width: 768px) {
            .desktop-footer {
                display: block;
                background: var(--black);
                color: var(--accent-yellow);
                padding: var(--space-lg) 0;
                border-top: 3px solid var(--primary-red);
                margin-top: var(--space-xl);
            }
        }
        
        /* ===== Mobile Footer ===== */
        .mobile-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(10, 10, 10, 0.98);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--border-color);
            padding: var(--space-sm) 0;
            display: flex;
            justify-content: space-around;
            align-items: center;
            height: 70px;
        }
        
        @media (min-width: 768px) {
            .mobile-footer {
                display: none;
            }
        }
        
        .mobile-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--space-xs);
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-md);
            transition: all var(--transition-fast);
            flex: 1;
            max-width: 80px;
            text-decoration: none;
            color: var(--text-secondary);
        }
        
        .mobile-nav-item.active {
            color: var(--primary-red);
        }
        
        .mobile-nav-icon {
            width: 22px;
            height: 22px;
        }
        
        .mobile-nav-label {
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        /* ===== Search Modal ===== */
        .search-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 1100;
            align-items: flex-start;
            justify-content: center;
            padding: var(--space-xl) var(--space-md);
            overflow-y: auto;
        }
        
        .search-modal.active {
            display: flex;
        }
        
        /* Animation for mobile footer */
        @keyframes slideUp {
            from {
                transform: translateY(100%);
            }
            to {
                transform: translateY(0);
            }
        }
        
        .mobile-footer {
            animation: slideUp 0.3s ease-out;
        }
        
        /* Animation for new comment highlighting */
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(230, 57, 70, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(230, 57, 70, 0); }
            100% { box-shadow: 0 0 0 0 rgba(230, 57, 70, 0); }
        }
        
        .comment-item.highlight {
            animation: pulse 2s;
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

            /* Ensure inline center image stacks on mobile */
            .img-text-row {
                flex-direction: column;
            }

            .position-image.center-position.inline-center {
                width: 100%;
            }

            .img-text-row .img-text-content {
                width: 100%;
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
    <!-- Header -->
    <header class="header" role="banner">
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo-container">
                    <img src="Liked-tamil-news-logo-1 (2).png" alt="Liked தமிழ்" class="logo" />
                    <h1 class="site-title">Liked தமிழ்</h1>
                </a>
                
                <div class="header-actions">
                    <button class="search-btn" aria-label="தேடல்" id="searchToggle">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                    </button>
                    
                    <button class="subscribe-btn" onclick="openSubscription()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                            <polyline points="22,6 12,13 2,6" />
                        </svg>
                        சந்தா
                    </button>
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
        </div>
    </header>

    <!-- Category Navigation -->
    <nav class="category-nav" aria-label="Main categories">
        <div class="container">
            <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <ul class="category-list">
                    <li style="display: inline-block;">
                        <a href="index.php" class="category-link">
                            முகப்பு
                        </a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                        <?php 
                        $countQuery = "SELECT COUNT(*) as count FROM news 
                                       WHERE FIND_IN_SET(?, categories) > 0 
                                       AND status = 'published'";
                        $countStmt = $db->prepare($countQuery);
                        $countStmt->execute([$category['id']]);
                        $count = $countStmt->fetch(PDO::FETCH_ASSOC);
                        ?>
                        <li style="display: inline-block;">
                            <a href="categories.php?id=<?php echo $category['id']; ?>" class="category-link">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ARTICLE CONTENT -->
    <main class="container">
        <div class="article-container">
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
                        <?php if (isset($_GET['comment_submitted']) && $_GET['comment_submitted'] == 'true'): ?>
                            <div class="feedback-success">
                                <span class="feedback-success-icon">✓</span>
                                உங்கள் கருத்து வெற்றிகரமாக சமர்ப்பிக்கப்பட்டது!
                            </div>
                        <?php endif; ?>
                        <?php if (isset($error_message)): ?>
                            <div class="pending-message">
                                <span>⚠</span>
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="" id="commentForm">
                            <input type="hidden" name="parent_id" value="0" id="parentId">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">பெயர் *</label>
                                    <input type="text" id="name" name="name" class="form-control" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="email">மின்னஞ்சல் (வெளிப்படையாக்கப்படாது) *</label>
                                    <input type="email" id="email" name="email" class="form-control" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="comment">கருத்து *</label>
                                <textarea id="comment" name="comment" class="form-control" required><?php echo isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : ''; ?></textarea>
                            </div>
                            <button type="submit" name="submit_comment" class="submit-btn">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                                </svg>
                                கருத்தை சமர்ப்பிக்கவும்
                            </button>
                        </form>
                    </div>

                    <!-- Comments List -->
                    <div class="comments-list" id="commentsList">
                        <?php if (empty($comments)): ?>
                            <div class="no-comments">
                                <div class="no-comments-icon">💬</div>
                                <h3>இன்னும் கருத்துகள் இல்லை</h3>
                                <p>முதல் கருத்தைப் பதிவிட நீங்கள் தயாரா?</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment-item <?php echo isset($_GET['new_comment']) && $_GET['new_comment'] == $comment['id'] ? 'highlight' : ''; ?>" 
                                     id="comment-<?php echo $comment['id']; ?>">
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
                                    <div class="comment-content"><?php echo $comment['comment']; ?></div>
                                    
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
                                                <button type="button" class="cancel-reply-btn" data-id="<?php echo $comment['id']; ?>" style="padding: 8px 16px; background: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-sm);">
                                                    ரத்துசெய்
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <!-- Replies -->
                                    <?php if (!empty($comment['replies'])): ?>
                                        <div class="comment-replies">
                                            <?php foreach ($comment['replies'] as $reply): ?>
                                                <div class="comment-item comment-reply <?php echo isset($_GET['new_comment']) && $_GET['new_comment'] == $reply['id'] ? 'highlight' : ''; ?>" 
                                                     id="comment-<?php echo $reply['id']; ?>">
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
                                                    <div class="comment-content"><?php echo $reply['comment']; ?></div>
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
                        <a href="https://api.whatsapp.com/send?text=<?php echo $shareTextEncoded; ?>" 
                           target="_blank" class="share-button whatsapp" title="WhatsApp இல் பகிரவும்">
                            <svg class="share-icon" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.76.982.998-3.677-.236-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.826 9.826 0 012.9 6.994c-.004 5.45-4.438 9.88-9.888 9.88m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.333 .157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.333 11.893-11.893 0-3.18-1.24-6.162-3.495-8.411"/>
                            </svg>
                            <span class="share-label">WhatsApp</span>
                        </a>
                        
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $shareUrl; ?>&quote=<?php echo $shareTextEncoded; ?>" 
                           target="_blank" class="share-button facebook" title="Facebook இல் பகிரவும்">
                            <svg class="share-icon" viewBox="0 0 24 24">
                                <path d="M18.77 7.46H14.5v-1.9c0-.9.6-1.1 1-1.1h3V.5h-4.33C10.24.5 9.5 3.44 9.5 5.32v2.15h-3v4h3v12h5v-12h3.85l.42-4z"/>
                            </svg>
                            <span class="share-label">Facebook</span>
                        </a>
                        
                        <a href="https://twitter.com/intent/tweet?text=<?php echo $shareTextEncoded; ?>" 
                           target="_blank" class="share-button twitter" title="Twitter இல் பகிரவும்">
                            <svg class="share-icon" viewBox="0 0 24 24">
                                <path d="M23.44 4.83c-.8.37-1.5.38-2.22.02.93-.56.98-.96 1.32-2.02-.88.52-1.86.9-2.9 1.1-.82-.88-2-1.43-3.3-1.43-2.5 0-4.55 2.04-4.55 4.54 0 .36.03.7.1 1.04-3.77-.2-7.12-2-9.36-4.75-.4.67-.6 1.45-.6 2.3 0 1.56.8 2.95 2 3.77-.74-.03-1.44-.23-2.05-.57v.06c0 2.2 1.56 4.03 3.64 4.44-.67.2-1.37.2-2.06.08.58 1.8 2.26 3.12 4.25 3.16C5.78 18.1 3.37 18.74 1 18.46c2 1.3 4.4 2.04 6.97 2.04 8.35 0 12.92-6.92 12.92-12.93 0-.2 0-.4-.02-.6.9-.63 1.96-1.22 2.56-2.14z"/>
                            </svg>
                            <span class="share-label">Twitter</span>
                        </a>
                        
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $shareUrl; ?>&title=<?php echo $shareTextEncoded; ?>&summary=<?php echo $shareTextEncoded; ?>" 
                           target="_blank" class="share-button linkedin" title="LinkedIn இல் பகிரவும்">
                            <svg class="share-icon" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                            <span class="share-label">LinkedIn</span>
                        </a>
                        
                        <a href="https://t.me/share/url?url=<?php echo $shareUrl; ?>&text=<?php echo $shareTextEncoded; ?>" 
                           target="_blank" class="share-button telegram" title="Telegram இல் பகிரவும்">
                            <svg class="share-icon" viewBox="0 0 24 24">
                                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.16.16-.295.295-.605.295l.213-3.054 5.56-5.022c.242-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.658-.64.135-.954l11.57-4.461c.538-.196 1.006.128.832.941z"/>
                            </svg>
                            <span class="share-label">Telegram</span>
                        </a>
                        
                        <a href="https://pinterest.com/pin/create/button/?url=<?php echo $shareUrl; ?>&media=<?php echo urlencode($mainImagePath); ?>&description=<?php echo $shareTextEncoded; ?>" 
                           target="_blank" class="share-button pinterest" title="Pinterest இல் பகிரவும்">
                            <svg class="share-icon" viewBox="0 0 24 24">
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
        </div>
    </main>

    <!-- Desktop Footer -->
    <footer class="desktop-footer">
        <div class="container">
            <div style="text-align: center;">
                <p>&copy; <?php echo date('Y'); ?> Liked தமிழ். அனைத்து உரிமைகளும் பாதுகாக்கப்பட்டவை.</p>
                <div style="display: flex; justify-content: center; gap: var(--space-lg); margin-top: var(--space-sm);">
                    <a href="about.php" style="color: var(--accent-yellow); text-decoration: none; transition: color var(--transition-fast);">எங்களைப் பற்றி</a>
                    <a href="contact.php" style="color: var(--accent-yellow); text-decoration: none; transition: color var(--transition-fast);">தொடர்பு கொள்ள</a>
                    <a href="privacy.php" style="color: var(--accent-yellow); text-decoration: none; transition: color var(--transition-fast);">தனியுரிமைக் கொள்கை</a>
                    <a href="terms.php" style="color: var(--accent-yellow); text-decoration: none; transition: color var(--transition-fast);">பயன்பாட்டு விதிமுறைகள்</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Footer Navigation -->
    <footer class="mobile-footer" role="navigation" aria-label="மொபைல் வழிசெலுத்தல்">
        <a href="index.php" class="mobile-nav-item">
            <svg class="mobile-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                <polyline points="9 22 9 12 15 12 15 22" />
            </svg>
            <span class="mobile-nav-label">முகப்பு</span>
        </a>
        
        <a href="categories.php" class="mobile-nav-item">
            <svg class="mobile-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7" />
                <rect x="14" y="3" width="7" height="7" />
                <rect x="3" y="14" width="7" height="7" />
                <rect x="14" y="14" width="7" height="7" />
            </svg>
            <span class="mobile-nav-label">பிரிவுகள்</span>
        </a>
        
        <button class="mobile-nav-item" onclick="toggleSearch()">
            <svg class="mobile-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <span class="mobile-nav-label">தேடல்</span>
        </button>
        
        <a href="video.php" class="mobile-nav-item">
            <svg class="mobile-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="23 7 16 12 23 17 23 7" />
                <rect x="1" y="5" width="15" height="14" rx="2" ry="2" />
            </svg>
            <span class="mobile-nav-label">வீடியோ</span>
        </a>
        
        <a href="about.php" class="mobile-nav-item">
            <svg class="mobile-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
            </svg>
            <span class="mobile-nav-label">சுயவிவரம்</span>
        </a>
    </footer>

    <!-- Search Modal -->
    <div class="search-modal" id="searchModal">
        <div style="width: 100%; max-width: 600px; background: var(--bg-card); border-radius: var(--radius-lg); padding: var(--space-lg); box-shadow: var(--shadow-lg); animation: slideDown 0.3s ease; margin-top: var(--space-xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-lg);">
                <h3 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary);">தேடல்</h3>
                <button onclick="toggleSearch()" aria-label="மூடு" style="background: transparent; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer; padding: var(--space-xs); border-radius: var(--radius-sm); transition: all var(--transition-fast);">
                    &times;
                </button>
            </div>
            
            <form method="GET" action="search.php" style="display: flex; gap: var(--space-sm); margin-bottom: var(--space-lg);">
                <input type="search" 
                       name="q" 
                       placeholder="செய்திகளைத் தேடுங்கள்..." 
                       style="flex: 1; padding: var(--space-md); background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-primary); font-size: 1rem; outline: none; transition: all var(--transition-fast);"
                       autocomplete="off"
                       autofocus />
                <button type="submit" style="padding: var(--space-md) var(--space-lg); background: linear-gradient(135deg, var(--primary-red), var(--primary-dark-red)); color: var(--white); border: none; border-radius: var(--radius-md); font-weight: 600; cursor: pointer; transition: all var(--transition-fast);">
                    தேடு
                </button>
            </form>
            
            <div style="color: var(--text-muted); font-size: 0.875rem; text-align: center;">
                உதாரணம்: "விளையாட்டு", "அரசியல்", "பொருளாதாரம்"
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        function toggleSearch() {
            const searchModal = document.getElementById('searchModal');
            searchModal.classList.toggle('active');
            
            if (searchModal.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
                searchModal.querySelector('input').focus();
            } else {
                document.body.style.overflow = '';
            }
        }
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const searchModal = document.getElementById('searchModal');
                if (searchModal.classList.contains('active')) {
                    toggleSearch();
                }
            }
        });
        
        document.getElementById('searchModal')?.addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                toggleSearch();
            }
        });

        document.getElementById('searchToggle')?.addEventListener('click', toggleSearch);

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

        // JavaScript for comment reply functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight new comment if exists in URL
            const urlParams = new URLSearchParams(window.location.search);
            const newCommentId = urlParams.get('new_comment');
            
            if (newCommentId) {
                const commentElement = document.getElementById('comment-' + newCommentId);
                if (commentElement) {
                    // Scroll to the new comment
                    commentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Add highlight class
                    commentElement.classList.add('highlight');
                    
                    // Remove highlight after 2 seconds
                    setTimeout(() => {
                        commentElement.classList.remove('highlight');
                    }, 2000);
                }
            }
            
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
                const successMessages = document.querySelectorAll('.feedback-success');
                successMessages.forEach(msg => {
                    msg.style.display = 'none';
                });
            }, 5000);
            
            // Share button hover effects
            document.querySelectorAll('.share-button').forEach(button => {
                // Add tooltip on hover
                button.addEventListener('mouseenter', function(e) {
                    const title = this.getAttribute('title');
                    if (title) {
                        const tooltip = document.createElement('div');
                        tooltip.className = 'share-tooltip';
                        tooltip.textContent = title;
                        tooltip.style.position = 'absolute';
                        tooltip.style.background = 'var(--bg-card)';
                        tooltip.style.color = 'var(--text-primary)';
                        tooltip.style.padding = '8px 12px';
                        tooltip.style.borderRadius = '6px';
                        tooltip.style.fontSize = '12px';
                        tooltip.style.whiteSpace = 'nowrap';
                        tooltip.style.zIndex = '1000';
                        tooltip.style.boxShadow = 'var(--shadow-sm)';
                        tooltip.style.border = '1px solid var(--border-color)';
                        
                        const rect = this.getBoundingClientRect();
                        tooltip.style.top = (rect.top - 40) + 'px';
                        tooltip.style.left = (rect.left + rect.width/2) + 'px';
                        tooltip.style.transform = 'translateX(-50%)';
                        
                        this.appendChild(tooltip);
                    }
                });
                
                button.addEventListener('mouseleave', function() {
                    const tooltip = this.querySelector('.share-tooltip');
                    if (tooltip) {
                        tooltip.remove();
                    }
                });
            });

            // Comment formatting - preserve HTML in comments
            document.querySelectorAll('.comment-content').forEach(content => {
                // Convert line breaks to <br> if they're not already there
                if (!content.innerHTML.includes('<br>') && content.textContent.includes('\n')) {
                    content.innerHTML = content.textContent.replace(/\n/g, '<br>');
                }
                
                // Style bold text in comments
                content.querySelectorAll('strong, b').forEach(boldText => {
                    boldText.style.color = '#ffffff';
                    boldText.style.fontWeight = '700';
                    boldText.style.background = 'linear-gradient(45deg, var(--primary-red), var(--accent-yellow))';
                    boldText.style.webkitBackgroundClip = 'text';
                    boldText.style.webkitTextFillColor = 'transparent';
                    boldText.style.backgroundClip = 'text';
                });
                
                // Style italic text in comments
                content.querySelectorAll('em, i').forEach(italicText => {
                    italicText.style.color = '#ffff99';
                    italicText.style.fontStyle = 'italic';
                });
                
                // Style underlined text in comments
                content.querySelectorAll('u').forEach(underlinedText => {
                    underlinedText.style.textDecoration = 'underline';
                    underlinedText.style.textDecorationColor = 'var(--accent-yellow)';
                    underlinedText.style.textDecorationThickness = '2px';
                });
            });

            // Update comment count in the meta section
            const commentCountElement = document.getElementById('commentCount');
            const totalCommentsElement = document.getElementById('totalComments');
            
            // Function to update comment counts
            function updateCommentCounts(count) {
                if (commentCountElement) {
                    commentCountElement.textContent = count + ' கருத்துகள்';
                }
                if (totalCommentsElement) {
                    totalCommentsElement.textContent = count;
                }
            }
            
            // Check if we should update the count (when new comment is added)
            if (newCommentId) {
                // Increment the count by 1 for the new comment
                const currentCount = <?php echo $totalComments; ?>;
                updateCommentCounts(currentCount);
            }
        });

        function openSubscription() {
            alert('சந்தா செயல்பாடு விரைவில் கிடைக்கும்');
        }
    </script>
</body>
</html>