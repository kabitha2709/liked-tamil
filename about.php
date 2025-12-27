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
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes" />
  <title>Liked தமிழ் - எங்களைப் பற்றி</title>
  
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
    
    /* ===== Typography ===== */
    h1, h2, h3, h4, h5, h6 {
      font-family: var(--font-heading);
      font-weight: 700;
      line-height: 1.2;
    }
    
    a {
      color: inherit;
      text-decoration: none;
      transition: color var(--transition-fast);
    }
    
    a:hover {
      color: var(--accent-yellow);
    }
    
    /* ===== Utility Classes ===== */
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
    
    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border: 0;
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
    
    /* ===== Breaking News Ticker ===== */
    .breaking-news {
      background: linear-gradient(135deg, var(--primary-dark-red), var(--primary-red));
      padding: var(--space-sm) 0;
      overflow: hidden;
      position: sticky;
      top: 110px; /* Header (60px) + Category Nav (50px) */
      z-index: 800;
    }
    
    .ticker-container {
      display: flex;
      align-items: center;
      gap: var(--space-md);
      padding: 0 var(--space-md);
    }
    
    @media (min-width: 640px) {
      .ticker-container {
        padding: 0 var(--space-lg);
      }
    }
    
    .breaking-label {
      display: flex;
      align-items: center;
      gap: var(--space-xs);
      background: var(--black);
      color: var(--accent-yellow);
      padding: var(--space-xs) var(--space-sm);
      border-radius: var(--radius-full);
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      white-space: nowrap;
      flex-shrink: 0;
    }
    
    .ticker-content {
      flex: 1;
      overflow: hidden;
    }
    
    .ticker-track {
      display: inline-flex;
      gap: var(--space-xl);
      animation: ticker 30s linear infinite;
      white-space: nowrap;
      padding-right: var(--space-xl);
    }
    
    .ticker-content:hover .ticker-track {
      animation-play-state: paused;
    }
    
    @keyframes ticker {
      0% { transform: translateX(0); }
      100% { transform: translateX(-50%); }
    }
    
    /* ===== Main Content ===== */
    .main-content {
      padding: var(--space-xl) 0;
      min-height: calc(100vh - 180px);
    }
    
    /* ===== About Section ===== */
    .about-section {
      margin-bottom: var(--space-2xl);
    }
    
    .about-card {
      background: var(--bg-card);
      border-radius: var(--radius-lg);
      padding: var(--space-xl);
      box-shadow: var(--shadow-md);
      border: 1px solid var(--border-color);
      margin-bottom: var(--space-xl);
    }
    
    .about-title {
      font-size: 2rem;
      color: var(--accent-yellow);
      margin-bottom: var(--space-lg);
      text-align: center;
    }
    
    .about-content {
      color: var(--text-primary);
      line-height: 1.7;
    }
    
    .about-content p {
      margin-bottom: var(--space-md);
      font-size: 1.1rem;
    }
    
    .about-content h3 {
      color: var(--accent-yellow);
      margin: var(--space-xl) 0 var(--space-lg);
      font-size: 1.5rem;
    }
    
    .about-content ul {
      margin: var(--space-md) 0 var(--space-lg) var(--space-xl);
      color: var(--text-secondary);
    }
    
    .about-content li {
      margin-bottom: var(--space-sm);
    }
    
    .about-content a {
      color: var(--accent-yellow);
      text-decoration: underline;
    }
    
    .about-content a:hover {
      color: var(--primary-red);
    }
    
    .about-features {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: var(--space-lg);
      margin: var(--space-xl) 0;
    }
    
    .feature-item {
      background: var(--glass-bg);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-lg);
      padding: var(--space-lg);
      text-align: center;
      transition: all var(--transition-base);
    }
    
    .feature-item:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-md);
      border-color: var(--primary-red);
    }
    
    .feature-icon {
      font-size: 2.5rem;
      margin-bottom: var(--space-sm);
      display: block;
    }
    
    .feature-item h4 {
      color: var(--text-primary);
      margin-bottom: var(--space-sm);
      font-size: 1.2rem;
    }
    
    .feature-item p {
      color: var(--text-secondary);
      font-size: 0.95rem;
      margin: 0;
    }
    
    /* Highlight box */
    .highlight-box {
      background: rgba(255, 209, 102, 0.1);
      border-left: 4px solid var(--accent-yellow);
      padding: var(--space-lg);
      border-radius: var(--radius-sm);
      margin: var(--space-xl) 0;
    }
    
    .highlight-box strong {
      color: var(--accent-yellow);
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
      
      .breaking-news {
        top: 110px;
        height: 40px;
      }
      
      .ticker-container {
        padding: 0 var(--space-sm);
        gap: var(--space-sm);
      }
      
      .breaking-label {
        font-size: 0.7rem;
        padding: 2px 8px;
      }
      
      .about-card {
        padding: var(--space-lg);
      }
      
      .about-title {
        font-size: 1.5rem;
      }
      
      .about-content p {
        font-size: 1rem;
      }
      
      .about-features {
        grid-template-columns: 1fr;
        gap: var(--space-md);
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

  <!-- Breaking News Ticker -->
  <section class="breaking-news" aria-label="Breaking news">
    <div class="container">
      <div class="ticker-container">
        <div class="breaking-label">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
            <circle cx="12" cy="12" r="10" />
          </svg>
          Breaking
        </div>
        
        <div class="ticker-content">
          <div class="ticker-track">
            <?php foreach ($tickerNews as $item): ?>
              <span style="display: inline-flex; align-items: center; gap: var(--space-sm); margin-right: var(--space-xl);">
                <?php echo htmlspecialchars($item['title']); ?>
                <span style="width: 4px; height: 4px; background: var(--accent-yellow); border-radius: 50%;"></span>
              </span>
            <?php endforeach; ?>
            <?php foreach ($tickerNews as $item): ?>
              <span style="display: inline-flex; align-items: center; gap: var(--space-sm); margin-right: var(--space-xl);">
                <?php echo htmlspecialchars($item['title']); ?>
                <span style="width: 4px; height: 4px; background: var(--accent-yellow); border-radius: 50%;"></span>
              </span>
            <?php endforeach; ?>
          </div>
        </div>
        
        <div class="breaking-label">
          Live • 24/7
        </div>
      </div>
    </div>
  </section>

  <!-- Main Content Area -->
  <main class="main-content" role="main">
    <div class="container">
      
      <!-- About Section -->
      <section class="about-section">
        <div class="about-card">
          <h2 class="about-title">Liked தமிழ் பற்றி</h2>
          <div class="about-content">
            <p>Liked தமிழ் என்பது தமிழ் மொழியில் சிறந்த செய்திகள், கட்டுரைகள், கலை மற்றும் கலாச்சார உள்ளடக்கங்களை வழங்கும் முன்னணி செய்தி வலைத்தளமாகும். 2024 ஆம் ஆண்டு தொடங்கப்பட்ட இந்த தளம், உலகளாவிய தமிழ் மக்களுக்கு நம்பகமான, சரியான நேரத்தில், சுவாரஸ்யமான செய்திகளை வழங்குவதை நோக்கமாகக் கொண்டுள்ளது.</p>
            
            <h3>எங்கள் நோக்கம்</h3>
            <p>தமிழ் மொழியின் செழுமையைப் பாதுகாத்து, தகவல் தொழில்நுட்பத்தின் மூலம் உலகளாவிய தமிழ் சமூகத்தை இணைக்கும் வகையில் உயர்தர உள்ளடக்கங்களை வழங்குவது எங்கள் நோக்கமாகும்.</p>
            
            <h3>சிறப்புக் கூறுகள்</h3>
            <div class="about-features">
              <div class="feature-item">
                <span class="feature-icon">📰</span>
                <h4>பல்துறை செய்திகள்</h4>
                <p>அரசியல், பொருளாதாரம், விளையாட்டு, தொழில்நுட்பம், கலை, கலாச்சாரம் உள்ளிட்ட பல்துறை செய்திகள்</p>
              </div>
              
              <div class="feature-item">
                <span class="feature-icon">⚡</span>
                <h4>விரைவான புதுப்பிப்பு</h4>
                <p>24/7 செய்தி புதுப்பிப்பு, உடனடி Breaking News, Live Updates</p>
              </div>
              
              <div class="feature-item">
                <span class="feature-icon">📱</span>
                <h4>மொபைல் இணக்கம்</h4>
                <p>அனைத்து சாதனங்களிலும் சிறப்பாக வேலை செய்யும் Responsive Design</p>
              </div>
              
              <div class="feature-item">
                <span class="feature-icon">🔍</span>
                <h4>ஆழ்ந்த பகுப்பாய்வு</h4>
                <p>மேலோட்டமான செய்திகள் மட்டுமல்ல, ஆழ்ந்த ஆய்வுகள் மற்றும் பகுப்பாய்வுகள்</p>
              </div>
            </div>
            
            <h3>எங்கள் அணி</h3>
            <p>Liked தமிழ் அனுபவம் வாய்ந்த பத்திரிகையாளர்கள், எழுத்தாளர்கள் மற்றும் தொழில்நுட்ப வல்லுநர்களைக் கொண்ட ஒரு தகுதிவாய்ந்த அணியால் இயக்கப்படுகிறது. எங்கள் அனைத்து உள்ளடக்கங்களும் உண்மைத்தன்மை, நடுநிலை மற்றும் தரத்திற்காக கடுமையான சரிபார்ப்பு செய்யப்படுகின்றன.</p>
            
            <h3>தொடர்பு கொள்ள</h3>
            <p>கருத்துகள், பரிந்துரைகள் அல்லது விளம்பரங்களுக்கு:</p>
            <ul>
              <li>மின்னஞ்சல்: info@likedtamil.lk</li>
              <li>வலைத்தளம்: <a href="https://likedtamil.lk" target="_blank">likedtamil.lk</a></li>
              <li>பேஸ்புக்: <a href="https://facebook.com/liked.tamil" target="_blank">facebook.com/liked.tamil</a></li>
            </ul>
          </div>
        </div>
        
        <div class="about-card">
          <h2 class="about-title">வளர்ச்சி குழு</h2>
          <div class="about-content">
            <p>இந்த வலைத்தளம் <a href="https://webbuilders.lk" target="_blank">Webbuilders.lk</a> நிறுவனத்தால் மேம்படுத்தப்பட்டு பராமரிக்கப்படுகிறது. தமிழ் வலைத்தளங்களின் வளர்ச்சி மற்றும் மேம்பாட்டில் முன்னணி நிறுவனமாக செயல்படும் Webbuilders.lk, நவீன தொழில்நுட்பங்களைப் பயன்படுத்தி தரமான தமிழ் உள்ளடக்கங்களை உருவாக்குவதில் நிபுணத்துவம் பெற்றுள்ளது.</p>
            
            <div class="highlight-box">
              <strong>வலைத்தள மேம்பாட்டு சேவைகள்:</strong><br>
              வலைத்தள வடிவமைப்பு, உள்ளடக்க மேலாண்மை அமைப்பு (CMS), மொபைல் பயன்பாடுகள், SEO மேம்பாடு, இணையதள பாதுகாப்பு மற்றும் பராமரிப்பு.
            </div>
          </div>
        </div>
      </section>
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
    
    <a href="about.php" class="mobile-nav-item active">
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
      const breakingNewsHeight = document.querySelector('.breaking-news').offsetHeight;
      const mobileFooterHeight = document.querySelector('.mobile-footer').offsetHeight;
      
      const totalStickyHeight = headerHeight + categoryNavHeight + breakingNewsHeight;
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
        } else if (currentPath === '/about.php') {
          // Remove active from other items when on about page
          if (item.getAttribute('href') === 'about.php') {
            item.classList.add('active');
          } else {
            item.classList.remove('active');
          }
        }
      });
    });
    
    window.addEventListener('resize', adjustContentHeight);
  </script>
</body>
</html>