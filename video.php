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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>வீடியோக்கள் - Liked தமிழ்</title>
    
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
    
    /* Video Page Header */
    .video-header {
      margin-bottom: var(--space-xl);
    }
    
    .video-title {
      font-size: 2.5rem;
      font-weight: 800;
      color: var(--text-primary);
      margin-bottom: var(--space-sm);
      font-family: var(--font-heading);
    }
    
    .video-description {
      color: var(--text-secondary);
      font-size: 1.125rem;
      margin-bottom: var(--space-lg);
    }
    
    .video-stats {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: var(--bg-card);
      padding: var(--space-md) var(--space-lg);
      border-radius: var(--radius-lg);
      border: 1px solid var(--border-color);
      margin-bottom: var(--space-xl);
    }
    
    .video-count {
      display: flex;
      align-items: center;
      gap: var(--space-sm);
      color: var(--text-primary);
      font-weight: 600;
    }
    
    .video-count svg {
      color: var(--primary-red);
    }
    
    .sort-options select {
      background: var(--bg-primary);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: var(--space-sm) var(--space-md);
      color: var(--text-primary);
      font-family: var(--font-body);
      font-size: 0.875rem;
      cursor: pointer;
      transition: all var(--transition-fast);
    }
    
    .sort-options select:hover {
      border-color: var(--primary-red);
    }
    
    /* Video Grid */
    .video-grid {
      display: grid;
      gap: var(--space-lg);
      margin-bottom: var(--space-xl);
    }
    
    @media (min-width: 640px) {
      .video-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    
    @media (min-width: 1024px) {
      .video-grid {
        grid-template-columns: repeat(3, 1fr);
      }
    }
    
    @media (min-width: 1280px) {
      .video-grid {
        grid-template-columns: repeat(4, 1fr);
      }
    }
    
    .video-card {
      background: var(--bg-card);
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: var(--shadow-md);
      transition: all var(--transition-base);
      border: 1px solid var(--border-color);
      text-decoration: none;
      color: inherit;
    }
    
    .video-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-lg);
      border-color: var(--primary-red);
    }
    
    .video-thumbnail {
      position: relative;
      width: 100%;
      height: 180px;
      overflow: hidden;
    }
    
    .video-thumbnail img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform var(--transition-slow);
    }
    
    .video-card:hover .video-thumbnail img {
      transform: scale(1.05);
    }
    
    .play-btn {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 56px;
      height: 56px;
      background: rgba(230, 57, 70, 0.9);
      border-radius: var(--radius-full);
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all var(--transition-base);
    }
    
    .play-btn svg {
      width: 24px;
      height: 24px;
      color: var(--white);
    }
    
    .video-card:hover .play-btn {
      background: var(--primary-red);
      transform: translate(-50%, -50%) scale(1.1);
    }
    
    .video-info {
      padding: var(--space-lg);
    }
    
    .video-card-title {
      font-size: 1.125rem;
      font-weight: 700;
      line-height: 1.4;
      margin-bottom: var(--space-sm);
      color: var(--text-primary);
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .video-meta {
      display: flex;
      align-items: center;
      gap: var(--space-sm);
      color: var(--text-secondary);
      font-size: 0.75rem;
    }
    
    .video-meta svg {
      color: var(--accent-yellow);
    }
    
    /* No Videos Message */
    .no-videos {
      grid-column: 1 / -1;
      text-align: center;
      padding: var(--space-2xl) var(--space-lg);
      background: var(--bg-card);
      border-radius: var(--radius-lg);
      border: 2px dashed var(--border-color);
    }
    
    .no-videos h3 {
      font-size: 1.5rem;
      color: var(--text-primary);
      margin-bottom: var(--space-sm);
      font-family: var(--font-heading);
    }
    
    .no-videos p {
      color: var(--text-secondary);
      margin-bottom: var(--space-lg);
    }
    
    /* ===== Video Modal ===== */
    .video-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.95);
      z-index: 1100;
      align-items: center;
      justify-content: center;
      padding: var(--space-xl) var(--space-md);
    }
    
    .video-modal.active {
      display: flex;
    }
    
    .modal-content {
      width: 100%;
      max-width: 900px;
      background: var(--bg-card);
      border-radius: var(--radius-lg);
      padding: var(--space-xl);
      position: relative;
    }
    
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: var(--space-lg);
    }
    
    .modal-title {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--text-primary);
      font-family: var(--font-heading);
    }
    
    .close-modal {
      background: transparent;
      border: none;
      color: var(--text-secondary);
      font-size: 1.5rem;
      cursor: pointer;
      padding: var(--space-xs);
      border-radius: var(--radius-sm);
      transition: all var(--transition-fast);
    }
    
    .close-modal:hover {
      color: var(--text-primary);
      background: var(--glass-bg);
    }
    
    .modal-video-container {
      position: relative;
      padding-bottom: 56.25%; /* 16:9 aspect ratio */
      height: 0;
      overflow: hidden;
      border-radius: var(--radius-md);
      background: var(--black);
    }
    
    .modal-video-container iframe {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      border: none;
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
      
      .video-title {
        font-size: 1.75rem;
      }
      
      .video-description {
        font-size: 1rem;
      }
      
      .video-stats {
        flex-direction: column;
        gap: var(--space-md);
        align-items: flex-start;
      }
      
      .video-thumbnail {
        height: 150px;
      }
      
      .video-info {
        padding: var(--space-md);
      }
      
      .video-card-title {
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
                        <li style="display: inline-block;">
                            <a href="categories.php?id=<?php echo $category['id']; ?>" 
                               class="category-link">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li style="display: inline-block;">
                        <a href="video.php" class="category-link active">
                            வீடியோ
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="main-content" role="main">
        <div class="container">
            <!-- Video Header -->
            <div class="video-header">
                <h1 class="video-title">வீடியோ செய்திகள்</h1>
                <p class="video-description">Liked தமிழில் வெளியான அனைத்து வீடியோ செய்திகளையும் இங்கே பார்க்கலாம்</p>
                
                <div class="video-stats">
                    <div class="video-count">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="23 7 16 12 23 17 23 7" />
                            <rect x="1" y="5" width="15" height="14" rx="2" ry="2" />
                        </svg>
                        <span id="video-count"><?php echo count($videos); ?> வீடியோ<?php echo count($videos) != 1 ? 'க்கள்' : ''; ?></span>
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
                        
                        // Format time ago in Tamil
                        $publishTime = new DateTime($video['published_at']);
                        $now = new DateTime();
                        $interval = $now->diff($publishTime);
                        $timeAgo = '';
                        
                        if ($interval->days > 0) {
                            $timeAgo = $interval->days . ' நாட்கள் முன்';
                        } elseif ($interval->h > 0) {
                            $timeAgo = $interval->h . ' மணி முன்';
                        } else {
                            $timeAgo = $interval->i . ' நிமிடம் முன்';
                        }
                        ?>
                        <div class="video-card" data-video-url="<?php echo htmlspecialchars($video['video']); ?>" data-video-title="<?php echo htmlspecialchars($video['title']); ?>">
                            <div class="video-thumbnail">
                                <img src="<?php echo $imageSrc; ?>" alt="<?php echo htmlspecialchars($video['title']); ?>" loading="lazy">
                                <button class="play-btn">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polygon points="5 3 19 12 5 21 5 3" />
                                    </svg>
                                </button>
                            </div>
                            <div class="video-info">
                                <h3 class="video-card-title"><?php echo htmlspecialchars($video['title']); ?></h3>
                                <div class="video-meta">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                    <span><?php echo $timeAgo; ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-videos">
                        <h3>வீடியோக்கள் இல்லை</h3>
                        <p>தற்போது வீடியோ செய்திகள் எதுவும் இல்லை. பின்னர் சரிபார்க்கவும்.</p>
                        <a href="index.php" style="display: inline-flex; align-items: center; gap: var(--space-xs); padding: var(--space-sm) var(--space-md); background: linear-gradient(135deg, var(--primary-red), var(--primary-dark-red)); color: var(--white); border: none; border-radius: var(--radius-md); font-weight: 600; cursor: pointer; transition: all var(--transition-base);">
                            முகப்பு பக்கத்திற்குச் செல்லவும்
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Desktop Footer -->
    <footer class="desktop-footer">
        <div class="container">
            <div style="text-align: center;">
        <p>&copy; <?php echo date('Y'); ?> Liked தமிழ். அனைத்து உரிமைகளும் பாதுகாக்கப்பட்டவை.|உங்கள் நம்பகமான செய்தி மூலம்</p>        
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
        
        <a href="video.php" class="mobile-nav-item active">
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
            <span class="mobile-nav-label">அறிமுகம்</span>
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

    <!-- Video Modal -->
    <div class="video-modal" id="videoModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalVideoTitle">வீடியோ</h3>
                <button class="close-modal" id="closeVideoModal">&times;</button>
            </div>
            <div class="modal-video-container">
                <iframe id="modalVideoPlayer" allowfullscreen></iframe>
            </div>
        </div>
    </div>

    <script>
        // Initialize variables
        const videoData = <?php echo json_encode($videos ?: []); ?>;
        
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

        // Video modal functionality
        function openVideoModal(videoUrl, videoTitle) {
            const modal = document.getElementById('videoModal');
            const videoPlayer = document.getElementById('modalVideoPlayer');
            const modalTitle = document.getElementById('modalVideoTitle');
            const closeBtn = document.getElementById('closeVideoModal');
            
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
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Close modal when clicking X
            closeBtn.onclick = closeVideoModal;
            
            // Close modal when clicking outside the content
            modal.onclick = function(e) {
                if (e.target === modal) {
                    closeVideoModal();
                }
            };
            
            // Close modal with Escape key
            document.addEventListener('keydown', function closeOnEscape(e) {
                if (e.key === 'Escape') {
                    closeVideoModal();
                    document.removeEventListener('keydown', closeOnEscape);
                }
            });
        }
        
        function closeVideoModal() {
            const modal = document.getElementById('videoModal');
            const videoPlayer = document.getElementById('modalVideoPlayer');
            
            modal.classList.remove('active');
            videoPlayer.src = ''; // Stop video playback
            document.body.style.overflow = '';
        }

        // Video sorting functionality
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
            // Add event listeners to play buttons
            document.querySelectorAll('.play-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const videoCard = this.closest('.video-card');
                    const videoUrl = videoCard.getAttribute('data-video-url');
                    const videoTitle = videoCard.getAttribute('data-video-title');
                    openVideoModal(videoUrl, videoTitle);
                });
            });
            
            // Add click event to entire video card
            document.querySelectorAll('.video-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    if (!e.target.closest('.play-btn')) {
                        const videoUrl = this.getAttribute('data-video-url');
                        const videoTitle = this.getAttribute('data-video-title');
                        openVideoModal(videoUrl, videoTitle);
                    }
                });
            });
            
            // Sort functionality
            const sortSelect = document.getElementById('sort-by');
            sortSelect.addEventListener('change', function() {
                const sortedVideos = sortVideos(videoData, this.value);
                renderVideos(sortedVideos);
            });
            
            // Search toggle button
            document.getElementById('searchToggle')?.addEventListener('click', toggleSearch);
            
            // Mobile footer search button
            document.querySelectorAll('.mobile-nav-item').forEach(item => {
                const label = item.querySelector('.mobile-nav-label')?.textContent;
                if (label === 'தேடல்') {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        toggleSearch();
                    });
                }
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
            setTimeout(adjustContentHeight, 100);
            window.addEventListener('resize', adjustContentHeight);
        });

        function openSubscription() {
            alert('சந்தா செயல்பாடு விரைவில் கிடைக்கும்');
        }
    </script>
</body>
</html>