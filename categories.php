<?php
// Database connection
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();
require 'config/config.php'; 

// Base URL from config
$base_url = "http://localhost/WebBuilders/news_admin/";

// Fetch main categories for navigation
$categoryQuery = "SELECT * FROM categories WHERE status = 'active' ORDER BY id";
$categoryStmt = $db->prepare($categoryQuery);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Get category ID from URL
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get category details if ID is provided
$current_category = null;
$category_subcategories = [];
$category_news = [];
$news_count = 0;
$total_pages = 1;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;

if ($category_id > 0) {
    // Fetch current category details
    $catQuery = "SELECT * FROM categories WHERE id = :id AND status = 'active'";
    $catStmt = $db->prepare($catQuery);
    $catStmt->execute([':id' => $category_id]);
    $current_category = $catStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($current_category) {
        // Parse subcategories from JSON or comma-separated string
        $subcategories_raw = $current_category['subcategories'];
        $category_subcategories = [];
        
        if (!empty($subcategories_raw)) {
            // Try to decode as JSON first
            $decoded = json_decode($subcategories_raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // JSON format: extract subcategory names
                foreach ($decoded as $item) {
                    if (is_array($item) && isset($item['en']) && !empty($item['en'])) {
                        $category_subcategories[] = trim($item['en']);
                    } elseif (is_string($item) && !empty(trim($item))) {
                        $category_subcategories[] = trim($item);
                    }
                }
            } else {
                // Comma-separated format
                $temp = array_map('trim', explode(',', $subcategories_raw));
                $category_subcategories = array_filter($temp, function($item) {
                    return !empty($item);
                });
            }
        }
        
        // Get the category name
        $categoryName = $current_category['name'];
        
        // Method 1: Try to find news where categories column contains the category name
        $offset = ($page - 1) * $per_page;
        
        // Modified query to fetch news with image from news table only
        $newsQuery = "SELECT n.*, 
                      (SELECT GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') 
                       FROM categories c 
                       WHERE FIND_IN_SET(c.id, REPLACE(REPLACE(n.categories, '[', ''), ']', '')) > 0) as category_names
                      FROM news n
                      WHERE n.status = 'published' 
                      AND (n.categories LIKE :cat_name 
                           OR n.categories LIKE :cat_name_comma_start 
                           OR n.categories LIKE :cat_name_comma_middle 
                           OR n.categories LIKE :cat_name_comma_end
                           OR n.categories LIKE :cat_id_pattern)
                      ORDER BY COALESCE(n.published_at, n.created_at) DESC 
                      LIMIT :limit OFFSET :offset";
        
        $newsStmt = $db->prepare($newsQuery);
        
        // Create search patterns
        $cat_name = "%" . $categoryName . "%";
        $cat_name_comma_start = $categoryName . ",%";
        $cat_name_comma_middle = "%," . $categoryName . ",%";
        $cat_name_comma_end = "%," . $categoryName;
        $cat_id_pattern = "%" . $category_id . "%";
        
        $newsStmt->bindValue(':cat_name', $cat_name, PDO::PARAM_STR);
        $newsStmt->bindValue(':cat_name_comma_start', $cat_name_comma_start, PDO::PARAM_STR);
        $newsStmt->bindValue(':cat_name_comma_middle', $cat_name_comma_middle, PDO::PARAM_STR);
        $newsStmt->bindValue(':cat_name_comma_end', $cat_name_comma_end, PDO::PARAM_STR);
        $newsStmt->bindValue(':cat_id_pattern', $cat_id_pattern, PDO::PARAM_STR);
        $newsStmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
        $newsStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $newsStmt->execute();
        $category_news = $newsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Count total news for this category
        $countQuery = "SELECT COUNT(*) as total FROM news 
                      WHERE status = 'published' 
                      AND (categories LIKE :cat_name 
                           OR categories LIKE :cat_name_comma_start 
                           OR categories LIKE :cat_name_comma_middle 
                           OR categories LIKE :cat_name_comma_end
                           OR categories LIKE :cat_id_pattern)";
        
        $countStmt = $db->prepare($countQuery);
        $countStmt->bindValue(':cat_name', $cat_name, PDO::PARAM_STR);
        $countStmt->bindValue(':cat_name_comma_start', $cat_name_comma_start, PDO::PARAM_STR);
        $countStmt->bindValue(':cat_name_comma_middle', $cat_name_comma_middle, PDO::PARAM_STR);
        $countStmt->bindValue(':cat_name_comma_end', $cat_name_comma_end, PDO::PARAM_STR);
        $countStmt->bindValue(':cat_id_pattern', $cat_id_pattern, PDO::PARAM_STR);
        $countStmt->execute();
        
        $result = $countStmt->fetch(PDO::FETCH_ASSOC);
        $news_count = $result ? $result['total'] : 0;
        
        // Calculate total pages
        $total_pages = ceil($news_count / $per_page);
    }
}

// Function to format date in Tamil
function formatTamilDate($date) {
    $tamil_months = [
        'ஜனவரி', 'பிப்ரவரி', 'மார்ச்', 'ஏப்ரல்', 
        'மே', 'ஜூன்', 'ஜூலை', 'ஆகஸ்ட்', 
        'செப்டம்பர்', 'அக்டோபர்', 'நவம்பர்', 'டிசம்பர்'
    ];
    
    try {
        $date_time = new DateTime($date);
        $day = $date_time->format('d');
        $month = $tamil_months[intval($date_time->format('m')) - 1] ?? $date_time->format('F');
        $year = $date_time->format('Y');
        $time = $date_time->format('H:i');
        
        return "$day $month $year, $time";
    } catch (Exception $e) {
        return $date;
    }
}

// Function to get time ago in Tamil using published_at
function timeAgoTamil($published_at, $created_at = null) {
    try {
        $now = new DateTime();
        
        // Use published_at if available, otherwise fall back to created_at
        if (!empty($published_at)) {
            $published = new DateTime($published_at);
            $interval = $now->diff($published);
        } elseif (!empty($created_at)) {
            $created = new DateTime($created_at);
            $interval = $now->diff($created);
        } else {
            return "சமீபத்தில்";
        }
        
        if ($interval->y > 0) {
            return $interval->y . " வருடம் முன்";
        } elseif ($interval->m > 0) {
            return $interval->m . " மாதம் முன்";
        } elseif ($interval->d > 0) {
            if ($interval->d == 1) return "நேற்று";
            return $interval->d . " நாள் முன்";
        } elseif ($interval->h > 0) {
            return $interval->h . " மணி முன்";
        } elseif ($interval->i > 0) {
            return $interval->i . " நிமிடம் முன்";
        } else {
            return "இப்போது";
        }
    } catch (Exception $e) {
        return "சமீபத்தில்";
    }
}

// Function to extract subcategory from news
function extractSubcategory($news, $category_subcategories) {
    if (empty($category_subcategories)) {
        return 'பொது';
    }
    
    // Try to find subcategory from title
    $title = strtolower($news['title']);
    foreach ($category_subcategories as $subcat) {
        if (!empty(trim($subcat)) && stripos($title, strtolower(trim($subcat))) !== false) {
            return trim($subcat);
        }
    }
    
    // Default to first subcategory or 'பொது'
    return !empty($category_subcategories) ? trim($category_subcategories[0]) : 'பொது';
}

// Function to get news image with improved fallback system
function getNewsImage($news) {
    global $db, $base_url;
    
    $news_id = is_array($news) ? $news['id'] : $news;
    
    // Try to get image from news_images table first
    $imagesQuery = "SELECT image_path FROM news_images 
                    WHERE news_id = ? 
                    ORDER BY 
                        CASE position 
                            WHEN 'top' THEN 1
                            WHEN 'center' THEN 2
                            WHEN 'bottom' THEN 3
                            ELSE 4
                        END,
                        display_order ASC
                    LIMIT 1";
    
    $imagesStmt = $db->prepare($imagesQuery);
    $imagesStmt->execute([$news_id]);
    $imageRow = $imagesStmt->fetch(PDO::FETCH_ASSOC);
    
    // If image found in news_images table
    if ($imageRow && !empty($imageRow['image_path'])) {
        $imagePath = $imageRow['image_path'];
        
        // Check if it's already a full URL
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return htmlspecialchars($imagePath);
        }
        
        // Check multiple possible locations
        $possible_paths = [
            'uploads/news/positions/' . basename($imagePath),
            'uploads/news/' . basename($imagePath),
            'uploads/news/thumbnails/' . basename($imagePath),
            $imagePath // Original path
        ];
        
        foreach ($possible_paths as $path) {
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/WebBuilders/news_admin/' . $path;
            if (file_exists($fullPath)) {
                return $base_url . htmlspecialchars($path);
            }
        }
    }
    
    // Fallback to news table image
    if (is_array($news) && !empty($news['image'])) {
        $imagePath = $news['image'];
        
        // Check if it's already a full URL
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return htmlspecialchars($imagePath);
        }
        
        // Check multiple locations for news table image
        $possible_paths = [
            'uploads/news/' . basename($imagePath),
            'uploads/news/thumbnails/' . basename($imagePath),
            $imagePath // Original path
        ];
        
        foreach ($possible_paths as $path) {
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/WebBuilders/news_admin/' . $path;
            if (file_exists($fullPath)) {
                return $base_url . htmlspecialchars($path);
            }
        }
        
        // If relative path doesn't exist locally, try as absolute URL
        if (strpos($imagePath, 'http') !== 0) {
            return $base_url . 'uploads/news/' . htmlspecialchars(basename($imagePath));
        }
    }
    
    // Final fallback to random placeholder
    $placeholder_id = rand(1000, 1100);
    return 'https://picsum.photos/id/' . $placeholder_id . '/800/500';
}

// Function to get categories from news item
function getNewsCategories($news) {
    if (!empty($news['category_names'])) {
        return htmlspecialchars($news['category_names']);
    }
    
    // Try to parse from categories field
    if (!empty($news['categories'])) {
        // Remove brackets and split
        $categories_str = str_replace(['[', ']'], '', $news['categories']);
        $category_ids = explode(',', $categories_str);
        
        if (!empty($category_ids)) {
            global $db;
            $category_names = [];
            
            // Fetch category names from database
            foreach ($category_ids as $cat_id) {
                $cat_id = trim($cat_id);
                if (is_numeric($cat_id) && $cat_id > 0) {
                    $catQuery = "SELECT name FROM categories WHERE id = ?";
                    $catStmt = $db->prepare($catQuery);
                    $catStmt->execute([$cat_id]);
                    $category = $catStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($category && !empty($category['name'])) {
                        $category_names[] = $category['name'];
                    }
                }
            }
            
            if (!empty($category_names)) {
                return implode(', ', $category_names);
            }
        }
    }
    
    return '';
}
?>

<!DOCTYPE html>
<html lang="ta">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $current_category ? htmlspecialchars($current_category['name']) . ' - ' : ''; ?>Liked தமிழ்</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Noto+Sans+Tamil:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
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
            --trans: 240ms cubic-bezier(.2,.8,.2,1);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: "Noto Sans Tamil", Inter, system-ui, -apple-system, sans-serif;
            color: var(--text);
            background:
                radial-gradient(800px 420px at 10% -10%, rgba(255,17,17,.12), transparent 42%),
                radial-gradient(600px 380px at 95% 0%, rgba(255,252,0,.10), transparent 52%),
                var(--bg);
            background-attachment: fixed;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Main content wrapper */
        .main-wrapper {
            flex: 1 0 auto;
            width: 100%;
        }

        /* App bar */
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
            object-fit: cover;
        }
        .title {
            font-weight: 800; font-size: clamp(18px, 2.4vw, 24px); letter-spacing: .2px;
            font-family: "Noto Sans Tamil", sans-serif;
        }
        .search {
            display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:12px;
            background: var(--glass); border: var(--border);
            max-width: 400px; width: 100%;
        }
        .search input {
            flex:1; background:transparent; border:0; color: var(--text); outline:none;
            font-family: "Noto Sans Tamil", sans-serif; font-size: 14px;
        }
        .search input::placeholder {
            color: rgba(255,255,255,.6);
        }
        .actions { display:flex; gap: 10px; }
        .btn {
            display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:12px;
            background: var(--card); border: var(--border); color: var(--text); cursor:pointer;
            transition: transform var(--trans), box-shadow var(--trans), background var(--trans);
            text-decoration: none; font-family: "Noto Sans Tamil", sans-serif; font-weight: 600;
            font-size: 14px; white-space: nowrap;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: var(--shadow); background: var(--card-hi); }
        .btn.primary {
            background: linear-gradient(180deg, var(--red), #cc0f0f);
            color: #fff; border: 0;
        }
        .btn.primary:hover {
            background: linear-gradient(180deg, #ff3333, #e00f0f);
        }
        .icon { width: 20px; height: 20px; }

        /* Category bar */
        .catbar {
            background: linear-gradient(180deg, rgba(255,252,0,.08), transparent);
            border-top: var(--border); border-bottom: var(--border);
            position: sticky;
            top: 64px; /* Adjust based on appbar height */
            z-index: 80;
        }
        .catbar-wrap {
            max-width: 1200px; margin: 0 auto; padding: 10px clamp(14px, 3vw, 24px);
            display:flex; gap: 8px; overflow-x: auto; scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }
        .catbar-wrap::-webkit-scrollbar {
            display: none;
        }
        .chip {
            flex: 0 0 auto;
            display:inline-flex; align-items:center; justify-content: center; gap:8px; 
            padding:8px 16px; border-radius:999px;
            background: var(--glass); border: var(--border); color: var(--text); 
            font-weight:600; font-size: 14px;
            transition: all var(--trans);
            cursor: pointer; text-decoration: none; white-space: nowrap;
            font-family: "Noto Sans Tamil", sans-serif;
            min-height: 36px;
        }
        .chip:hover { transform: translateY(-2px); background: rgba(255,17,17,.18); }
        .chip.active { 
            background: linear-gradient(180deg, var(--red), #d10f0f); 
            color: #fff; border: 0; 
            box-shadow: 0 4px 12px rgba(255,17,17,.25);
        }

        /* Breaking news ticker */
        .ticker {
            background: var(--yellow); color: var(--black);
            border-bottom: 2px solid rgba(0,0,0,.25);
            position: relative;
            overflow: hidden;
        }
        .ticker-wrap {
            max-width: 1200px; margin: 0 auto; 
            padding: 8px clamp(14px, 3vw, 24px);
            display: grid; grid-template-columns: auto 1fr auto; 
            gap: 12px; align-items: center;
        }
        .tag-chip {
            background: var(--black); color: var(--yellow);
            border-radius: 999px; padding:6px 10px; font-weight: 700; 
            border: 1px solid rgba(255,255,255,.08);
            font-size: 12px; font-family: "Noto Sans Tamil", sans-serif;
        }
        .marquee { 
            overflow: hidden; height: 28px; position: relative;
        }
        .marquee-track {
            display: inline-flex; gap: 28px; white-space: nowrap;
            animation: track 24s linear infinite;
        }
        .marquee:hover .marquee-track { animation-play-state: paused; }
        @keyframes track { 
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .dot { 
            width:6px; height:6px; border-radius:50%; display:inline-block; 
            background: rgba(0,0,0,.5); margin: 0 10px;
        }

        /* Category Header */
        .category-header {
            max-width: 1200px; margin: 30px auto 20px; padding: 0 clamp(14px, 3vw, 24px);
        }
        .category-title {
            font-weight: 800; font-size: clamp(28px, 3vw, 36px); 
            color: var(--yellow); margin-bottom: 10px;
            font-family: "Noto Sans Tamil", sans-serif;
            line-height: 1.2;
        }
        .category-meta {
            display: flex; gap: 20px; color: var(--muted); font-size: 14px;
            flex-wrap: wrap;
        }
        .category-subcategories {
            margin-top: 15px; display: flex; flex-wrap: wrap; gap: 8px;
        }
        .subcategory-chip {
            padding: 6px 12px; border-radius: 999px;
            background: rgba(255,252,0,.1); color: var(--yellow);
            font-size: 13px; font-weight: 600; text-decoration: none;
            transition: transform var(--trans), background var(--trans);
            font-family: "Noto Sans Tamil", sans-serif;
        }
        .subcategory-chip:hover {
            transform: translateY(-2px); background: rgba(255,252,0,.2);
        }

        /* News Grid */
        .section { 
            max-width: 1200px; margin: 20px auto 40px; 
            padding: 0 clamp(14px, 3vw, 24px);
        }
        .section-head { 
            display: flex; justify-content: space-between; align-items: center; 
            margin-bottom: 20px; 
        }
        .section-title { 
            font-weight: 800; font-size: clamp(20px, 2.2vw, 26px); 
            font-family: "Noto Sans Tamil", sans-serif;
        }
        .news-count {
            background: var(--yellow); color: var(--black);
            padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 700;
            font-family: "Noto Sans Tamil", sans-serif;
        }

        .grid-news {
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); 
            gap: 24px; margin-bottom: 40px;
        }
        
        @media (max-width: 768px) {
            .grid-news {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }
        }
        
        @media (max-width: 640px) {
            .grid-news {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }
        
        .news-card {
            display: flex; flex-direction: column; overflow: hidden;
            border-radius: var(--radius-sm); background: var(--card); border: var(--border);
            box-shadow: var(--shadow); transition: all var(--trans);
            text-decoration: none; color: inherit;
            height: 100%;
        }
        .news-card:hover { 
            transform: translateY(-4px); 
            box-shadow: 0 14px 40px rgba(0,0,0,.50); 
            background: var(--card-hi);
        }
        
        .news-thumb {
            position: relative; aspect-ratio: 16/9; overflow: hidden;
            background: linear-gradient(45deg, #2a2a2a, #1a1a1a);
        }
        .news-thumb img {
            width: 100%; height: 100%; object-fit: cover;
            transition: transform .7s ease;
        }
        .news-card:hover .news-thumb img { transform: scale(1.08); }
        
        .news-badge {
            position: absolute; top: 12px; left: 12px; z-index: 2;
            padding: 6px 12px; border-radius: 999px;
            background: rgba(0,0,0,.85); color: #fff;
            font-size: 12px; font-weight: 600; backdrop-filter: blur(4px);
            font-family: "Noto Sans Tamil", sans-serif;
            max-width: calc(100% - 24px);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .news-content { 
            padding: 20px; flex: 1; display: flex; flex-direction: column; 
        }
        .news-title { 
            font-weight: 700; font-size: 18px; line-height: 1.4; 
            margin-bottom: 12px; color: var(--text);
            font-family: "Noto Sans Tamil", sans-serif;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .news-excerpt {
            font-size: 14px; color: var(--muted); line-height: 1.5;
            margin-bottom: 15px; flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .news-meta {
            display: flex; justify-content: space-between; align-items: center;
            font-size: 12px; color: var(--muted); padding-top: 12px;
            border-top: 1px solid rgba(255,255,255,.1);
            flex-wrap: wrap;
            gap: 8px;
        }
        .news-meta span {
            font-family: "Noto Sans Tamil", sans-serif;
        }
        
        /* No News Message */
        .no-news {
            grid-column: 1 / -1; text-align: center; padding: 60px 20px;
            color: var(--muted); background: var(--glass);
            border-radius: var(--radius-sm); margin: 20px 0;
        }
        .no-news h3 { 
            font-size: 24px; margin-bottom: 10px; 
            font-family: "Noto Sans Tamil", sans-serif;
        }
        .no-news p { margin-bottom: 20px; }
        
        /* Pagination */
        .pagination {
            display: flex; justify-content: center; align-items: center; gap: 8px; 
            margin: 40px 0 60px; flex-wrap: wrap;
        }
        .page {
            padding: 10px 16px; border-radius: 10px;
            background: var(--glass); border: var(--border);
            color: var(--text); text-decoration: none;
            transition: all var(--trans);
            min-width: 44px; text-align: center;
            font-family: "Noto Sans Tamil", sans-serif;
            font-weight: 600;
            font-size: 14px;
        }
        .page:hover { 
            transform: translateY(-2px); 
            background: var(--card-hi); 
            box-shadow: var(--shadow);
        }
        .page.active { 
            background: linear-gradient(180deg, var(--red), #cc0f0f); 
            color: #fff; border: 0;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255,17,17,.25);
        }
        .page.disabled { 
            opacity: 0.5; cursor: not-allowed; 
            pointer-events: none;
        }
        
        /* Mobile Footer */
        .mobile-footer {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 99;
            backdrop-filter: blur(12px) saturate(1.1);
            background: linear-gradient(180deg, rgba(255,17,17,.95), rgba(255,17,17,1));
            border-top: 2px solid rgba(255,252,0,.55);
            display: none;
            box-shadow: 0 -4px 20px rgba(0,0,0,.3);
        }
        
        @media (max-width: 768px) {
            .mobile-footer { display: block; }
            body { padding-bottom: 70px; }
            .search, .actions { display: none; }
            .appbar-wrap { grid-template-columns: auto 1fr; }
            .catbar {
                top: 56px; /* Adjust for smaller appbar on mobile */
            }
        }
        
        .foot-wrap { 
            max-width: 1200px; margin: 0 auto; padding: 8px clamp(12px, 4vw, 18px); 
            display: flex; justify-content: space-between; gap: 4px;
        }
        .foot-item {
            flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px;
            color: #fff; text-decoration: none; padding: 8px 4px; border-radius: 12px;
            transition: transform var(--trans), background var(--trans);
        }
        .foot-item:hover, .foot-item.active { 
            background: rgba(0,0,0,.18); transform: translateY(-2px); 
        }
        .foot-icon { width: 22px; height: 22px; }
        .foot-label { 
            font-size: 11px; font-weight: 700; 
            font-family: "Noto Sans Tamil", sans-serif;
        }
        
        /* Subscription Modal */
        .subscription-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
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
            font-family: "Noto Sans Tamil", sans-serif;
            font-size: 22px;
        }
        
        .subscription-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        @media (min-width: 480px) {
            .subscription-form {
                flex-direction: row;
            }
        }
        
        .subscription-form input {
            flex: 1;
            padding: 14px;
            border-radius: var(--radius-sm);
            background: var(--glass);
            border: var(--border);
            color: var(--text);
            outline: none;
            font-family: "Noto Sans Tamil", sans-serif;
            font-size: 16px;
        }
        
        .subscription-form button {
            background: linear-gradient(180deg, var(--red), #cc0f0f);
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            font-family: "Noto Sans Tamil", sans-serif;
            font-size: 16px;
            transition: all var(--trans);
        }
        
        .subscription-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255,17,17,.25);
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            background: transparent;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 24px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all var(--trans);
        }
        
        .close-modal:hover {
            background: rgba(255,255,255,.1);
            color: var(--text);
        }
        
        .subscription-success {
            color: var(--yellow);
            text-align: center;
            padding: 10px;
            font-family: "Noto Sans Tamil", sans-serif;
            display: none;
        }
        
        /* Bottom padding for mobile */
        .bottom-padding {
            height: 80px;
            display: none;
        }
        
        @media (max-width: 768px) {
            .bottom-padding {
                display: block;
            }
        }

        /* Responsive improvements */
        @media (max-width: 480px) {
            .appbar-wrap {
                padding: 10px 14px;
            }
            .logo {
                width: 36px;
                height: 36px;
            }
            .title {
                font-size: 18px;
            }
            .category-title {
                font-size: 24px;
            }
            .section-title {
                font-size: 20px;
            }
            .news-title {
                font-size: 16px;
            }
            .news-content {
                padding: 16px;
            }
            .pagination {
                margin: 30px 0 50px;
            }
            .page {
                padding: 8px 12px;
                min-width: 36px;
                font-size: 13px;
            }
        }
        
        /* Accessibility improvements */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        /* Focus styles for accessibility */
        button:focus,
        input:focus,
        a:focus {
            outline: 2px solid var(--yellow);
            outline-offset: 2px;
        }
        
        /* Loading state */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
    </style>
</head>
<body>
    <div class="main-wrapper">

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
                    <?php if (!empty($category_id) && $category_id > 0): ?>
                        <input type="hidden" name="category" value="<?php echo (int)$category_id; ?>" />
                    <?php endif; ?>
                </form>
            </div>
        </header>

        <!-- Category Navigation -->
        <nav class="catbar" aria-label="Categories">
            <div class="catbar-wrap">
                <a href="index.php" class="chip">முகப்பு</a>
                <?php foreach ($categories as $category): ?>
                    <a href="categories.php?id=<?php echo $category['id']; ?>" 
                       class="chip <?php echo ($category_id == $category['id']) ? 'active' : ''; ?>">
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

        <!-- Category Content -->
        <main>
            <?php if ($current_category): ?>
                <div class="category-header">
                    <h1 class="category-title"><?php echo htmlspecialchars($current_category['name']); ?></h1>
                    <div class="category-meta">
                        <span><?php echo $news_count; ?> செய்திகள்</span>
                        <?php if (!empty($category_subcategories)): ?>
                            <span><?php echo count($category_subcategories); ?> உட்பிரிவுகள்</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($category_subcategories)): ?>
                        <div class="category-subcategories">
                            <?php foreach ($category_subcategories as $subcat): ?>
                                <a href="#" class="subcategory-chip"><?php echo htmlspecialchars($subcat); ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="section">
                    <div class="section-head">
                        <div class="section-title">அனைத்து செய்திகள்</div>
                        <span class="news-count"><?php echo $news_count; ?> செய்திகள்</span>
                    </div>

                    <?php if (!empty($category_news)): ?>
                        <div class="grid-news">
                            <?php foreach ($category_news as $news): ?>
                                <?php 
                                $news_subcategory = extractSubcategory($news, $category_subcategories);
                                $news_image = getNewsImage($news);
                                $news_categories = getNewsCategories($news);
                                ?>
                                <a href="news-detail.php?id=<?php echo $news['id']; ?>" class="news-card">
                                    <div class="news-thumb">
                                        <img src="<?php echo $news_image; ?>" 
                                             alt="<?php echo htmlspecialchars($news['title']); ?>" 
                                             loading="lazy" 
                                             onerror="this.src='https://picsum.photos/id/<?php echo rand(1000, 1100); ?>/800/500'" />
                                        <?php if ($news_categories): ?>
                                            <span class="news-badge"><?php echo htmlspecialchars($news_categories); ?></span>
                                        <?php else: ?>
                                            <span class="news-badge"><?php echo htmlspecialchars($news_subcategory); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="news-content">
                                        <h3 class="news-title"><?php echo htmlspecialchars($news['title']); ?></h3>
                                        <div class="news-excerpt">
                                            <?php 
                                            $content = strip_tags($news['content'] ?? '');
                                            echo mb_strlen($content) > 120 ? mb_substr($content, 0, 120) . '...' : $content;
                                            ?>
                                        </div>
                                        <div class="news-meta">
                                            <span><?php echo timeAgoTamil($news['published_at'], $news['created_at']); ?></span>
                                            <span><?php echo !empty($news['published_at']) ? formatTamilDate($news['published_at']) : formatTamilDate($news['created_at']); ?></span>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="categories.php?id=<?php echo $category_id; ?>&page=<?php echo $page - 1; ?>" class="page">
                                        ← முந்தைய
                                    </a>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                        <a href="categories.php?id=<?php echo $category_id; ?>&page=<?php echo $i; ?>" 
                                           class="page <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                                        <span class="page disabled">...</span>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <a href="categories.php?id=<?php echo $category_id; ?>&page=<?php echo $page + 1; ?>" class="page">
                                        அடுத்த →
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="no-news">
                            <h3>செய்திகள் இல்லை</h3>
                            <p>இந்த வகைக்கான செய்திகள் இன்னும் சேர்க்கப்படவில்லை.</p>
                            <a href="index.php" class="btn primary">முகப்பு பக்கத்திற்குச் செல்லவும்</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- No category selected - show all categories -->
                <div class="category-header">
                    <h1 class="category-title">அனைத்து பிரிவுகள்</h1>
                    <div class="category-meta">
                        <span><?php echo count($categories); ?> பிரிவுகள்</span>
                    </div>
                </div>

                <div class="section">
                    <div class="grid-news">
                        <?php foreach ($categories as $category): ?>
                            <?php 
                            // Count news in this category
                            $cat_name = "%" . $category['name'] . "%";
                            $countQuery = "SELECT COUNT(*) as total FROM news 
                                          WHERE status = 'published' 
                                          AND (categories LIKE :cat_name 
                                               OR categories LIKE CONCAT(:cat_name_only, ',%')
                                               OR categories LIKE CONCAT('%,', :cat_name_only, ',%')
                                               OR categories LIKE CONCAT('%,', :cat_name_only)
                                               OR categories LIKE :cat_id_pattern)";
                            $countStmt = $db->prepare($countQuery);
                            $countStmt->bindValue(':cat_name', $cat_name, PDO::PARAM_STR);
                            $countStmt->bindValue(':cat_name_only', $category['name'], PDO::PARAM_STR);
                            $countStmt->bindValue(':cat_id_pattern', '%' . $category['id'] . '%', PDO::PARAM_STR);
                            $countStmt->execute();
                            $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
                            $cat_news_count = $countResult ? $countResult['total'] : 0;
                            
                            // Get a sample news item for category image
                            $sampleQuery = "SELECT n.id FROM news n
                                          WHERE n.status = 'published' 
                                          AND (n.categories LIKE :cat_name 
                                               OR n.categories LIKE CONCAT(:cat_name_only, ',%')
                                               OR n.categories LIKE CONCAT('%,', :cat_name_only, ',%')
                                               OR n.categories LIKE CONCAT('%,', :cat_name_only)
                                               OR n.categories LIKE :cat_id_pattern)
                                          ORDER BY COALESCE(n.published_at, n.created_at) DESC 
                                          LIMIT 1";
                            $sampleStmt = $db->prepare($sampleQuery);
                            $sampleStmt->bindValue(':cat_name', $cat_name, PDO::PARAM_STR);
                            $sampleStmt->bindValue(':cat_name_only', $category['name'], PDO::PARAM_STR);
                            $sampleStmt->bindValue(':cat_id_pattern', '%' . $category['id'] . '%', PDO::PARAM_STR);
                            $sampleStmt->execute();
                            $sampleNews = $sampleStmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($sampleNews) {
                                $category_image = getNewsImage($sampleNews);
                            } else {
                                $placeholder_id = rand(1000, 1100);
                                $category_image = 'https://picsum.photos/id/' . $placeholder_id . '/800/500';
                            }
                            ?>
                            <a href="categories.php?id=<?php echo $category['id']; ?>" class="news-card">
                                <div class="news-thumb">
                                    <img src="<?php echo $category_image; ?>" 
                                         alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                         loading="lazy"
                                         onerror="this.src='https://picsum.photos/id/<?php echo rand(1000, 1100); ?>/800/500'" />
                                    <span class="news-badge"><?php echo htmlspecialchars($category['name']); ?></span>
                                </div>
                                <div class="news-content">
                                    <h3 class="news-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                                    <div class="news-excerpt">
                                        <p><?php echo $cat_news_count; ?> செய்திகள்</p>
                                    </div>
                                    <div class="news-meta">
                                        <span>பிரிவு</span>
                                        <span>செய்திகள்: <?php echo $cat_news_count; ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>

        <!-- Bottom padding for mobile -->
        <div class="bottom-padding"></div>
    </div>

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

    <!-- Mobile Footer -->
    <footer class="mobile-footer" role="navigation" aria-label="மொபைல் அடிக்குறிப்பு">
        <div class="foot-wrap">
            <a href="index.php" class="foot-item">
                <svg class="foot-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M3 10l9-7 9 7v9a2 2 0 01-2 2H5a2 2 0 01-2-2v-9z" stroke="#fff" stroke-width="1.6"/>
                </svg>
                <span class="foot-label">முகப்பு</span>
            </a>
            <a href="categories.php" class="foot-item active">
                <svg class="foot-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M4 6h16M4 12h16M4 18h16" stroke="#fff" stroke-width="1.6"/>
                </svg>
                <span class="foot-label">பிரிவுகள்</span>
            </a>
            <a href="search.php" class="foot-item">
                <svg class="foot-icon" viewBox="0 0 24 24" fill="none">
                    <path d="M11 5a6 6 0 016 6c0 1.3-.41 2.5-1.11 3.48l4.32 4.32-1.41 1.41-4.32-4.32A6 6 0 1111 5z" stroke="#fff" stroke-width="1.6"/>
                </svg>
                <span class="foot-label">தேடல்</span>
            </a>
            <a href="about.php" class="foot-item">
                <svg class="foot-icon" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="6" stroke="#fff" stroke-width="1.6"/>
                </svg>
                <span class="foot-label">சுயவிவரம்</span>
            </a>
        </div>
    </footer>

    <script>
        // Subscription modal functions
        function openSubscription() {
            document.getElementById('subscriptionModal').style.display = 'flex';
        }
        
        function closeSubscription() {
            document.getElementById('subscriptionModal').style.display = 'none';
        }
        
        // Ticker width check
        const track = document.getElementById('tickerTrack');
        function ensureTickerLoop() {
            if (!track) return;
            const width = track.scrollWidth;
            const container = track.parentElement.clientWidth;
            if (width < container * 2) {
                track.innerHTML = track.innerHTML + track.innerHTML;
            }
        }
        
        // Initialize on load
        window.addEventListener('load', function() {
            ensureTickerLoop();
            
            // Image error handling
            document.querySelectorAll('img').forEach(img => {
                img.addEventListener('error', function() {
                    this.src = 'https://picsum.photos/id/' + Math.floor(Math.random() * 1000) + '/800/500';
                    this.onerror = null; // Prevent infinite loop
                });
            });
            
            // Add active class to current category in mobile footer
            const currentPath = window.location.pathname;
            const footerLinks = document.querySelectorAll('.mobile-footer .foot-item');
            footerLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === currentPath || 
                    (currentPath.includes('categories.php') && link.getAttribute('href') === 'categories.php')) {
                    link.classList.add('active');
                }
            });
        });
        
        // Recheck on resize
        window.addEventListener('resize', ensureTickerLoop);
        
        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSubscription();
            }
        });
        
        // Close modal when clicking outside
        document.getElementById('subscriptionModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSubscription();
            }
        });
        
        // Smooth scroll to top for pagination links
        document.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const target = this.getAttribute('href');
                
                // Add loading state
                document.body.classList.add('loading');
                
                // Scroll to top
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
                
                // Navigate after a short delay
                setTimeout(() => {
                    window.location.href = target;
                }, 300);
            });
        });
        
    </script>

</body>
</html>