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
    
    <!-- Favicon -->
    <?php include 'includes/favicon.php'; ?>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Tamil:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* ===== CSS Variables ===== */
        :root {
            /* Colors */
            --primary-red: #e63946;
            --primary-dark-red: #c1121f;
            --accent-yellow: #ffd166;
            --black: #000000;
            --white: #ffffff;
            
            /* UI Colors */
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
            
            /* Spacing */
            --space-xs: 4px;
            --space-sm: 8px;
            --space-md: 16px;
            --space-lg: 24px;
            --space-xl: 32px;
            --space-2xl: 48px;
            
            /* Border Radius */
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-full: 9999px;
            
            /* Transitions */
            --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-base: 250ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 350ms cubic-bezier(0.4, 0, 0.2, 1);
            
            /* Typography */
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
        
        /* Search Header */
        .search-header {
            margin-bottom: var(--space-xl);
        }
        
        .search-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: var(--space-sm);
            font-family: var(--font-heading);
        }
        
        @media (min-width: 768px) {
            .search-title {
                font-size: 2.5rem;
            }
        }
        
        .search-info {
            color: var(--text-secondary);
            font-size: 1rem;
            margin-bottom: var(--space-xs);
        }
        
        .search-term {
            color: var(--accent-yellow);
            font-weight: 600;
        }
        
        .results-count {
            color: var(--text-muted);
            font-size: 0.875rem;
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
        
        @media (min-width: 1280px) {
            .news-grid {
                grid-template-columns: repeat(4, 1fr);
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
        
        .news-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text-secondary);
            font-size: 0.75rem;
            padding-top: var(--space-md);
            border-top: 1px solid var(--border-color);
        }
        
        .read-more {
            display: inline-flex;
            align-items: center;
            gap: var(--space-xs);
            color: var(--primary-red);
            font-weight: 600;
            font-size: 0.875rem;
            margin-top: var(--space-md);
            transition: gap var(--transition-fast);
        }
        
        .read-more:hover {
            gap: var(--space-sm);
        }
        
        /* No Results Message */
        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: var(--space-2xl);
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            border: 2px dashed var(--border-color);
        }
        
        .no-results h3 {
            font-size: 1.5rem;
            color: var(--text-primary);
            margin-bottom: var(--space-sm);
        }
        
        .no-results p {
            color: var(--text-secondary);
            margin-bottom: var(--space-lg);
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
        
        /* ===== Subscription Modal ===== */
        .subscription-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.9);
            z-index: 1100;
            align-items: center;
            justify-content: center;
            padding: var(--space-md);
        }
        
        .subscription-content {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: var(--space-xl);
            width: 100%;
            max-width: 500px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
            position: relative;
        }
        
        .subscription-content h3 {
            color: var(--accent-yellow);
            font-size: 1.5rem;
            margin-bottom: var(--space-lg);
            text-align: center;
            font-family: var(--font-heading);
        }
        
        .subscription-form {
            display: flex;
            gap: var(--space-sm);
            margin-bottom: var(--space-md);
        }
        
        @media (max-width: 480px) {
            .subscription-form {
                flex-direction: column;
            }
        }
        
        .subscription-form input {
            flex: 1;
            padding: var(--space-md);
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-size: 1rem;
            outline: none;
            transition: all var(--transition-fast);
        }
        
        .subscription-form input:focus {
            border-color: var(--accent-yellow);
        }
        
        .subscription-form button {
            padding: var(--space-md) var(--space-lg);
            background: linear-gradient(135deg, var(--primary-red), var(--primary-dark-red));
            color: var(--white);
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        
        .subscription-form button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .close-modal {
            position: absolute;
            top: var(--space-md);
            right: var(--space-md);
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 1.5rem;
            cursor: pointer;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-full);
            transition: all var(--transition-fast);
        }
        
        .close-modal:hover {
            background: var(--glass-bg);
            color: var(--text-primary);
        }
        
        .subscription-success {
            color: var(--accent-yellow);
            text-align: center;
            padding: var(--space-md);
            margin-bottom: var(--space-md);
            background: rgba(255, 209, 102, 0.1);
            border: 1px solid rgba(255, 209, 102, 0.2);
            border-radius: var(--radius-md);
            font-weight: 600;
        }
        
        .subscription-error {
            color: var(--primary-red);
            text-align: center;
            padding: var(--space-md);
            margin-bottom: var(--space-md);
            background: rgba(230, 57, 70, 0.1);
            border: 1px solid rgba(230, 57, 70, 0.2);
            border-radius: var(--radius-md);
            font-weight: 600;
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
            
            .search-title {
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
        
        @media (max-width: 374px) {
            .logo {
                max-width: 100px;
            }
            
            .site-title {
                font-size: 0.9rem;
            }
            
            .category-link {
                padding: var(--space-xs) var(--space-sm);
                font-size: 0.75rem;
            }
            
            .news-grid {
                grid-template-columns: 1fr;
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
        
        /* Search form in header */
        .search-form {
            display: flex;
            gap: var(--space-sm);
            max-width: 400px;
            width: 100%;
        }
        
        .search-form input {
            flex: 1;
            padding: var(--space-sm) var(--space-md);
            background: var(--glass-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-full);
            color: var(--text-primary);
            font-family: var(--font-body);
            font-size: 0.875rem;
            outline: none;
            transition: all var(--transition-fast);
        }
        
        .search-form input:focus {
            border-color: var(--accent-yellow);
        }
        
        .search-form button {
            padding: var(--space-sm) var(--space-md);
            background: linear-gradient(135deg, var(--primary-red), var(--primary-dark-red));
            color: var(--white);
            border: none;
            border-radius: var(--radius-full);
            font-family: var(--font-body);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all var(--transition-fast);
            white-space: nowrap;
        }
        
        .search-form button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        /* Mobile search button */
        .mobile-search-btn {
            display: none;
        }
        
        @media (max-width: 768px) {
            .search-form {
                display: none;
            }
            
            .mobile-search-btn {
                display: block;
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
                </a>
                
                <!-- Desktop Search Form -->
                <form method="GET" action="search.php" class="search-form">
                    <input type="search" 
                           name="q" 
                           placeholder="செய்திகளைத் தேடுங்கள்..." 
                           value="<?php echo htmlspecialchars($searchTerm); ?>"
                           aria-label="தேடல்" />
                    <button type="submit">தேடு</button>
                </form>
                
                <div class="header-actions">
                    <button class="mobile-search-btn search-btn" onclick="openMobileSearch()" aria-label="தேடல்">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                    </button>
                    
                    <button class="theme-toggle" id="themeToggle" aria-label="Change theme">
                        <svg class="sun-icon" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="1.6"/>
                            <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" stroke="currentColor" stroke-width="1.6"/>
                        </svg>
                        <svg class="moon-icon" viewBox="0 0 24 24" fill="none">
                            <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" stroke="currentColor" stroke-width="1.6"/>
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
                        <li style="display: inline-block;">
                            <a href="categories.php?id=<?php echo $category['id']; ?>" 
                               class="category-link">
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

            <div class="news-grid">
                <?php if (!empty($searchTerm) && !empty($news)): ?>
                    <?php foreach ($news as $item): ?>
                        <article class="news-card">
                            <a href="news-detail.php?id=<?php echo $item['id']; ?>">
                                <div class="news-image">
                                    <?php
                                    // Get image URL
                                    if (!empty($item['image'])) {
                                        $imageSrc = $base_url . 'uploads/news/' . htmlspecialchars($item['image']);
                                    } elseif (!empty($item['image_path'])) {
                                        if (strpos($item['image_path'], 'http') === 0) {
                                            $imageSrc = $item['image_path'];
                                        } else {
                                            $imageSrc = $base_url . $item['image_path'];
                                        }
                                    } else {
                                        $imageSrc = 'https://picsum.photos/id/' . rand(1000, 1100) . '/800/500';
                                    }
                                    ?>
                                    <img src="<?php echo $imageSrc; ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                         loading="lazy" 
                                         onerror="this.src='https://picsum.photos/id/<?php echo rand(1000, 1100); ?>/800/500'" />
                                    
                                    <?php if (!empty($item['category_names'])): ?>
                                        <span class="news-badge">
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
                                        <div style="display: flex; align-items: center; gap: var(--space-xs);">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10" />
                                                <polyline points="12 6 12 12 16 14" />
                                            </svg>
                                            <?php
                                            $publishTime = new DateTime($item['published_at'] ?: $item['created_at']);
                                            $now = new DateTime();
                                            $interval = $now->diff($publishTime);
                                            
                                            if ($interval->days > 0) {
                                                echo $interval->days . ' நாட்கள் முன்';
                                            } elseif ($interval->h > 0) {
                                                echo $interval->h . ' மணி முன்';
                                            } else {
                                                echo $interval->i . ' நி முன்';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <a href="news-detail.php?id=<?php echo $item['id']; ?>" class="read-more">
                                        மேலும் படிக்க
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="5" y1="12" x2="19" y2="12" />
                                            <polyline points="12 5 19 12 12 19" />
                                        </svg>
                                    </a>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                <?php elseif (!empty($searchTerm)): ?>
                    <div class="no-results">
                        <h3>முடிவுகள் இல்லை</h3>
                        <p>"<?php echo htmlspecialchars($searchTerm); ?>" என்பதற்கு எந்த முடிவுகளும் கிடைக்கவில்லை.</p>
                        <a href="index.php" style="display: inline-flex; align-items: center; gap: var(--space-xs); padding: var(--space-sm) var(--space-md); background: linear-gradient(135deg, var(--primary-red), var(--primary-dark-red)); color: var(--white); border: none; border-radius: var(--radius-md); font-weight: 600; cursor: pointer; transition: all var(--transition-base);">
                            முகப்புக்குத் திரும்பு
                        </a>
                    </div>
                <?php else: ?>
                    <div class="no-results">
                        <h3>தேடல் வார்த்தையை உள்ளிடவும்</h3>
                        <p>தேடல் பட்டியில் வார்த்தையை உள்ளிட்டு முடிவுகளைப் பெறவும்.</p>
                        <a href="index.php" style="display: inline-flex; align-items: center; gap: var(--space-xs); padding: var(--space-sm) var(--space-md); background: linear-gradient(135deg, var(--primary-red), var(--primary-dark-red)); color: var(--white); border: none; border-radius: var(--radius-md); font-weight: 600; cursor: pointer; transition: all var(--transition-base);">
                            முகப்புக்குத் திரும்பு
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($searchTerm) && $totalPages > 1): ?>
                <nav class="pagination" aria-label="பக்கமாற்றம்">
                    <?php if ($page > 1): ?>
                        <a href="search.php?q=<?php echo urlencode($searchTerm); ?>&page=<?php echo $page - 1; ?>" 
                           class="page" aria-label="முந்தைய பக்கம்">
                            &larr;
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1): ?>
                        <a href="search.php?q=<?php echo urlencode($searchTerm); ?>&page=1" class="page">
                            1
                        </a>
                        <?php if ($startPage > 2): ?>
                            <span class="page" style="cursor: default; background: transparent; border: none;">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="search.php?q=<?php echo urlencode($searchTerm); ?>&page=<?php echo $i; ?>" 
                           class="page <?php echo ($i == $page) ? 'active' : ''; ?>"
                           aria-label="பக்கம் <?php echo $i; ?>"
                           aria-current="<?php echo ($i == $page) ? 'page' : 'false'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="page" style="cursor: default; background: transparent; border: none;">...</span>
                        <?php endif; ?>
                        <a href="search.php?q=<?php echo urlencode($searchTerm); ?>&page=<?php echo $totalPages; ?>" class="page">
                            <?php echo $totalPages; ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="search.php?q=<?php echo urlencode($searchTerm); ?>&page=<?php echo $page + 1; ?>" 
                           class="page" aria-label="அடுத்த பக்கம்">
                            &rarr;
                        </a>
                    <?php endif; ?>
                </nav>
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
        
        <button class="mobile-nav-item active" onclick="openMobileSearch()">
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
                <input type="email" 
                       name="email" 
                       placeholder="உங்கள் மின்னஞ்சல்" 
                       required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       aria-label="மின்னஞ்சல்" />
                <input type="hidden" name="subscribe" value="1">
                <button type="submit">Subscribe</button>
            </form>
            
            <div style="color: var(--text-muted); font-size: 0.875rem; text-align: center; margin-top: var(--space-md);">
                புதிய செய்திகள் மற்றும் புதுப்பிப்புகளை பெறவும்
            </div>
        </div>
    </div>

    <!-- Mobile Search Modal -->
    <div class="search-modal" id="mobileSearchModal">
        <div style="width: 100%; max-width: 600px; background: var(--bg-card); border-radius: var(--radius-lg); padding: var(--space-lg); box-shadow: var(--shadow-lg); animation: slideDown 0.3s ease; margin-top: var(--space-xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-lg);">
                <h3 style="font-size: 1.5rem; font-weight: 700; color: var(--text-primary);">தேடல்</h3>
                <button onclick="closeMobileSearch()" aria-label="மூடு" style="background: transparent; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer; padding: var(--space-xs); border-radius: var(--radius-sm); transition: all var(--transition-fast);">
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
        // Mobile search functionality
        function openMobileSearch() {
            const modal = document.getElementById('mobileSearchModal');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            modal.querySelector('input').focus();
        }
        
        function closeMobileSearch() {
            const modal = document.getElementById('mobileSearchModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        // Subscription modal functions
        function openSubscription() {
            const modal = document.getElementById('subscriptionModal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Clear any previous messages on open
            const successMsg = modal.querySelector('.subscription-success');
            const errorMsg = modal.querySelector('.subscription-error');
            if (successMsg) successMsg.style.display = 'none';
            if (errorMsg) errorMsg.style.display = 'none';
        }
        
        function closeSubscription() {
            const modal = document.getElementById('subscriptionModal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
            
            // Reload page to clear form if subscription was successful
            <?php if ($subscriptionSuccess): ?>
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            <?php endif; ?>
        }
        
        // Close modals when clicking outside
        window.onclick = function(event) {
            const subscriptionModal = document.getElementById('subscriptionModal');
            const searchModal = document.getElementById('mobileSearchModal');
            
            if (event.target == subscriptionModal) {
                closeSubscription();
            }
            if (event.target == searchModal) {
                closeMobileSearch();
            }
        }
        
        // Show subscription modal if there was an attempt
        <?php if ($subscriptionSuccess || $subscriptionError): ?>
            document.addEventListener('DOMContentLoaded', function() {
                openSubscription();
            });
        <?php endif; ?>
        
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
        
        // Search form validation
        document.querySelector('.search-form')?.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="q"]');
            if (!searchInput.value.trim()) {
                e.preventDefault();
                openMobileSearch();
            }
        });
        
        // Update mobile footer active state
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const mobileNavItems = document.querySelectorAll('.mobile-nav-item');
            
            mobileNavItems.forEach(item => {
                if (item.getAttribute('href') === currentPath) {
                    item.classList.add('active');
                } else if (currentPath.includes('search.php')) {
                    if (item.querySelector('.mobile-nav-label')?.textContent === 'தேடல்') {
                        item.classList.add('active');
                    } else {
                        item.classList.remove('active');
                    }
                }
            });
        });
        
        // Fix for mobile viewport height
        function setViewportHeight() {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }
        
        setViewportHeight();
        window.addEventListener('resize', setViewportHeight);
        
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
        
        // Close modals with ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const subscriptionModal = document.getElementById('subscriptionModal');
                const searchModal = document.getElementById('mobileSearchModal');
                
                if (subscriptionModal.style.display === 'flex') {
                    closeSubscription();
                }
                if (searchModal.classList.contains('active')) {
                    closeMobileSearch();
                }
            }
        });
    </script>
</body>
</html>