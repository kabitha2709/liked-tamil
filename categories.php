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
    
    <!-- Favicon -->
    <?php include 'includes/favicon.php'; ?>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Noto+Sans+Tamil:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
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
        
        /* Category Header */
        .category-header {
            margin-bottom: var(--space-xl);
        }
        
        .category-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: var(--space-sm);
            font-family: var(--font-heading);
        }
        
        .category-meta {
            display: flex;
            gap: var(--space-lg);
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-bottom: var(--space-lg);
        }
        
        .category-subcategories {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-sm);
        }
        
        .subcategory-chip {
            padding: var(--space-xs) var(--space-md);
            background: var(--glass-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-full);
            color: var(--text-secondary);
            font-size: 0.875rem;
            transition: all var(--transition-fast);
        }
        
        .subcategory-chip:hover {
            background: linear-gradient(135deg, var(--primary-red), var(--primary-dark-red));
            color: var(--white);
            border-color: transparent;
        }
        
        /* News Grid */
        .news-grid {
            display: grid;
            gap: var(--space-lg);
            margin-bottom: var(--space-xl);
        }
        
        @media (min-width: 640px) {
            .news-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 1024px) {
            .news-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        .news-card {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: all var(--transition-base);
            border: 1px solid var(--border-color);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .news-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-red);
        }
        
        .news-image {
            position: relative;
            width: 100%;
            height: 180px;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .news-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform var(--transition-slow);
        }
        
        .news-card:hover .news-image img {
            transform: scale(1.05);
        }
        
        .news-badge {
            position: absolute;
            top: var(--space-sm);
            left: var(--space-sm);
            padding: var(--space-xs) var(--space-sm);
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-full);
            color: var(--white);
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 1;
        }
        
        .news-content {
            padding: var(--space-lg);
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .news-title {
            font-size: 1.125rem;
            font-weight: 700;
            line-height: 1.4;
            margin-bottom: var(--space-sm);
            color: var(--text-primary);
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }
        
        .news-excerpt {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.5;
            margin-bottom: var(--space-md);
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .news-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text-secondary);
            font-size: 0.75rem;
            padding-top: var(--space-md);
            border-top: 1px solid var(--border-color);
        }
        
        /* No News Message */
        .no-news {
            grid-column: 1 / -1;
            text-align: center;
            padding: var(--space-2xl);
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            border: 2px dashed var(--border-color);
        }
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: var(--space-sm);
            padding: var(--space-xl) 0;
            flex-wrap: wrap;
        }
        
        .page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 var(--space-sm);
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all var(--transition-fast);
        }
        
        .page:hover {
            background: var(--bg-hover);
            transform: translateY(-2px);
        }
        
        .page.active {
            background: linear-gradient(135deg, var(--primary-red), var(--primary-dark-red));
            color: var(--white);
            border-color: transparent;
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
        
        /* ===== Responsive Adjustments ===== */
        @media (max-width: 767px) {
            .header {
                height: 60px;
            }
            
            .logo {
                max-width: 120px;
            }
            
            .site-title {
                font-size: 1rem;
            }
            
            .category-nav {
                height: 50px;
                top: 60px;
            }
            
            .category-title {
                font-size: 1.75rem;
            }
            
            .news-image {
                height: 150px;
            }
            
            .news-content {
                padding: var(--space-md);
            }
            
            .news-title {
                font-size: 1rem;
                -webkit-line-clamp: 2;
            }
            
            .mobile-footer {
                height: 70px;
            }
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
                        <a href="index.php" class="category-link <?php echo (!isset($_GET['category'])) ? 'active' : ''; ?>">
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
                            <a href="categories.php?id=<?php echo $category['id']; ?>" 
                               class="category-link <?php echo ($category_id == $category['id']) ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="main-content" role="main">
        <div class="container">
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

                <?php if (!empty($category_news)): ?>
                    <div class="news-grid">
                        <?php foreach ($category_news as $news): ?>
                            <?php 
                            $news_subcategory = extractSubcategory($news, $category_subcategories);
                            $news_image = getNewsImage($news);
                            $news_categories = getNewsCategories($news);
                            ?>
                            <a href="news-detail.php?id=<?php echo $news['id']; ?>" class="news-card">
                                <div class="news-image">
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
                                <a href="categories.php?id=<?php echo $category_id; ?>&page=<?php echo $page - 1; ?>" 
                                   class="page" aria-label="முந்தைய பக்கம்">
                                    &larr;
                                </a>
                            <?php endif; ?>

                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($total_pages, $page + 2);
                            
                            if ($startPage > 1): ?>
                                <a href="categories.php?id=<?php echo $category_id; ?>&page=1" class="page">
                                    1
                                </a>
                                <?php if ($startPage > 2): ?>
                                    <span class="page" style="cursor: default; background: transparent; border: none;">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <a href="categories.php?id=<?php echo $category_id; ?>&page=<?php echo $i; ?>" 
                                   class="page <?php echo ($i == $page) ? 'active' : ''; ?>"
                                   aria-label="பக்கம் <?php echo $i; ?>"
                                   aria-current="<?php echo ($i == $page) ? 'page' : 'false'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($endPage < $total_pages): ?>
                                <?php if ($endPage < $total_pages - 1): ?>
                                    <span class="page" style="cursor: default; background: transparent; border: none;">...</span>
                                <?php endif; ?>
                                <a href="categories.php?id=<?php echo $category_id; ?>&page=<?php echo $total_pages; ?>" class="page">
                                    <?php echo $total_pages; ?>
                                </a>
                            <?php endif; ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="categories.php?id=<?php echo $category_id; ?>&page=<?php echo $page + 1; ?>" 
                                   class="page" aria-label="அடுத்த பக்கம்">
                                    &rarr;
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="no-news">
                        <h3 style="font-size: 1.5rem; color: var(--text-primary); margin-bottom: var(--space-sm);">செய்திகள் இல்லை</h3>
                        <p style="color: var(--text-secondary); margin-bottom: var(--space-lg);">
                            இந்த வகைக்கான செய்திகள் இன்னும் சேர்க்கப்படவில்லை.
                        </p>
                        <a href="index.php" style="display: inline-flex; align-items: center; gap: var(--space-xs); padding: var(--space-sm) var(--space-md); background: linear-gradient(135deg, var(--primary-red), var(--primary-dark-red)); color: var(--white); border: none; border-radius: var(--radius-md); font-weight: 600; cursor: pointer; transition: all var(--transition-base);">
                            முகப்பு பக்கத்திற்குச் செல்லவும்
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- No category selected - show all categories -->
                <div class="category-header">
                    <h1 class="category-title">அனைத்து பிரிவுகள்</h1>
                    <div class="category-meta">
                        <span><?php echo count($categories); ?> பிரிவுகள்</span>
                    </div>
                </div>

                <div class="news-grid">
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
                            <div class="news-image">
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
            <?php endif; ?>
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
        <a href="index.php" class="mobile-nav-item <?php echo (!isset($_GET['category']) && !isset($_GET['page'])) ? 'active' : ''; ?>">
            <svg class="mobile-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                <polyline points="9 22 9 12 15 12 15 22" />
            </svg>
            <span class="mobile-nav-label">முகப்பு</span>
        </a>
        
        <a href="categories.php" class="mobile-nav-item active">
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
        
        <a href="profile.php" class="mobile-nav-item">
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

        // Fix for mobile viewport height
        function setViewportHeight() {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }
        
        setViewportHeight();
        window.addEventListener('resize', setViewportHeight);
        
        function openSubscription() {
            alert('சந்தா செயல்பாடு விரைவில் கிடைக்கும்');
        }

        // Ensure content fits screen
        function adjustContentHeight() {
            const headerHeight = document.querySelector('.header').offsetHeight;
            const categoryNavHeight = document.querySelector('.category-nav').offsetHeight;
            const mobileFooterHeight = document.querySelector('.mobile-footer').offsetHeight;
            
            const totalStickyHeight = headerHeight + categoryNavHeight;
            const mainContent = document.querySelector('.main-content');
            
            if (mainContent) {
                const windowHeight = window.innerHeight;
                const availableHeight = windowHeight - totalStickyHeight - mobileFooterHeight;
                mainContent.style.minHeight = availableHeight + 'px';
            }
        }
        
        // Run after page loads
        window.addEventListener('load', () => {
            setTimeout(adjustContentHeight, 100);
            
            // Update mobile footer active state
            const currentPath = window.location.pathname;
            const mobileNavItems = document.querySelectorAll('.mobile-nav-item');
            
            mobileNavItems.forEach(item => {
                if (item.getAttribute('href') === currentPath) {
                    item.classList.add('active');
                } else if (currentPath.includes('categories.php')) {
                    // Remove active from other items when on categories page
                    if (item.getAttribute('href') === 'categories.php') {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                }
            });
        });
        
        window.addEventListener('resize', adjustContentHeight);

        // Image error handling
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('error', function() {
                this.src = 'https://picsum.photos/id/' + Math.floor(Math.random() * 1000) + '/800/500';
                this.onerror = null;
            });
        });

        // Smooth scroll for pagination links
        document.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const target = this.getAttribute('href');
                
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