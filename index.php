<?php
// Database connection
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

// Fetch all active categories for navigation
$categoryQuery = "SELECT * FROM categories WHERE status = 'active' ORDER BY id";
$categoryStmt = $db->prepare($categoryQuery);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected date from URL or use today's date
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$dateObj = new DateTime($selectedDate);
$formattedDate = $dateObj->format('Y-m-d');

// Get page number for pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20; // News per page
$offset = ($page - 1) * $limit;

// Fetch total news count for selected date
if ($selectedDate == date('Y-m-d')) {
    $countQuery = "SELECT COUNT(*) as total FROM news WHERE status = 'published'";
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute();
} else {
    $countQuery = "SELECT COUNT(*) as total FROM news WHERE DATE(published_at) = ? AND status = 'published'";
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute([$formattedDate]);
}
$totalResult = $countStmt->fetch(PDO::FETCH_ASSOC);
$totalNews = $totalResult['total'];
$totalPages = ceil($totalNews / $limit);

// Fetch news for the selected date
if ($selectedDate == date('Y-m-d')) {
    $newsQuery = "SELECT n.*, 
                  (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') FROM categories c WHERE FIND_IN_SET(c.id, n.categories) > 0) as category_names
                  FROM news n 
                  WHERE n.status = 'published'
                  ORDER BY n.published_at DESC 
                  LIMIT $limit OFFSET $offset";
    $newsStmt = $db->prepare($newsQuery);
    $newsStmt->execute();
} else {
    $newsQuery = "SELECT n.*, 
                  (SELECT GROUP_CONCAT(c.name SEPARATOR ', ') FROM categories c WHERE FIND_IN_SET(c.id, n.categories) > 0) as category_names
                  FROM news n 
                  WHERE DATE(n.published_at) = ? AND n.status = 'published'
                  ORDER BY n.published_at DESC 
                  LIMIT $limit OFFSET $offset";
    $newsStmt = $db->prepare($newsQuery);
    $newsStmt->execute([$formattedDate]);
}
$news = $newsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch featured news for slider (first 3 published news)
$featuredQuery = "SELECT n.*, 
                  (SELECT name FROM categories WHERE FIND_IN_SET(id, n.categories) > 0 LIMIT 1) as category_name
                  FROM news n 
                  WHERE n.status = 'published' 
                  ORDER BY n.published_at DESC 
                  LIMIT 3";
$featuredStmt = $db->prepare($featuredQuery);
$featuredStmt->execute();
$featuredNews = $featuredStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch breaking news for ticker (recent 5 news)
$tickerQuery = "SELECT * FROM news 
                WHERE status = 'published' 
                ORDER BY published_at DESC 
                LIMIT 5";
$tickerStmt = $db->prepare($tickerQuery);
$tickerStmt->execute();
$tickerNews = $tickerStmt->fetchAll(PDO::FETCH_ASSOC);

require 'config/config.php';
?>

<!DOCTYPE html>
<html lang="ta">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes" />
  <title>Liked தமிழ் - உங்கள் நம்பகமான செய்தி மூலம்</title>
  
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
    
    /* Remove default padding-bottom for desktop */
    @media (min-width: 768px) {
      body {
        padding-bottom: 0;
      }
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
    
    .news-count-badge {
      background: var(--accent-yellow);
      color: var(--black);
      font-size: 0.75rem;
      font-weight: 700;
      padding: 1px 6px;
      border-radius: var(--radius-full);
      min-width: 20px;
      text-align: center;
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
    
    /* ===== Main Content Area - FIXED ===== */
    .main-content {
      padding-top: var(--space-lg);
      padding-bottom: 100px; /* Space for mobile footer */
      min-height: calc(100vh - 110px); /* Ensure content fills viewport */
    }
    
    @media (min-width: 768px) {
      .main-content {
        padding-bottom: var(--space-xl);
        min-height: calc(100vh - 110px);
      }
    }
    
    /* ===== Hero Section ===== */
    .hero-section {
      margin-bottom: var(--space-xl);
    }
    
    .hero-grid {
      display: grid;
      gap: var(--space-lg);
    }
    
    @media (min-width: 1024px) {
      .hero-grid {
        grid-template-columns: 2fr 1fr;
        gap: var(--space-xl);
      }
    }
    
    /* Slider */
    .slider-container {
      position: relative;
      border-radius: var(--radius-lg);
      overflow: hidden;
      background: var(--bg-card);
      box-shadow: var(--shadow-lg);
      height: 300px;
      width: 100%;
    }
    
    @media (min-width: 640px) {
      .slider-container {
        height: 400px;
      }
    }
    
    .slider {
      position: relative;
      width: 100%;
      height: 100%;
    }
    
    .slide {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      opacity: 0;
      transform: translateX(30px);
      transition: all var(--transition-slow);
    }
    
    .slide.active {
      opacity: 1;
      transform: translateX(0);
    }
    
    .slide-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: brightness(0.8);
    }
    
    .slide-content {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      padding: var(--space-lg);
      background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
    }
    
    .slide-category {
      display: inline-block;
      padding: var(--space-xs) var(--space-sm);
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
      border-radius: var(--radius-full);
      color: var(--white);
      font-size: 0.75rem;
      font-weight: 600;
      margin-bottom: var(--space-sm);
    }
    
    .slide-title {
      font-size: 1.5rem;
      font-weight: 800;
      color: var(--white);
      margin-bottom: var(--space-sm);
      line-height: 1.3;
    }
    
    @media (min-width: 640px) {
      .slide-title {
        font-size: 2rem;
      }
    }
    
    .slide-meta {
      display: flex;
      align-items: center;
      gap: var(--space-md);
      color: var(--text-secondary);
      font-size: 0.875rem;
    }
    
    .slider-controls {
      position: absolute;
      bottom: var(--space-lg);
      right: var(--space-lg);
      display: flex;
      gap: var(--space-sm);
    }
    
    /* ===== Calendar Sidebar ===== */
    .calendar-card {
      background: var(--bg-card);
      border-radius: var(--radius-lg);
      padding: var(--space-lg);
      box-shadow: var(--shadow-md);
      height: 100%;
    }
    
    /* ===== News Grid ===== */
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
    
    .news-category {
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
      align-items: center;
      justify-content: space-between;
      color: var(--text-secondary);
      font-size: 0.75rem;
      margin-top: auto;
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
    
    /* ===== Facebook Section - IMPROVED MOBILE ===== */
    .facebook-section {
      margin: var(--space-xl) 0;
    }
    
    .facebook-card {
      background: var(--bg-card);
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: var(--shadow-lg);
      border: 1px solid var(--border-color);
    }
    
    .facebook-header {
      background: linear-gradient(135deg, #1877f2, #0a5bc4);
      padding: var(--space-md);
      display: flex;
      align-items: center;
      gap: var(--space-md);
    }
    
    @media (min-width: 768px) {
      .facebook-header {
        padding: var(--space-lg);
      }
    }
    
    .facebook-logo {
      width: 40px;
      height: 40px;
      background: var(--white);
      border-radius: var(--radius-full);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
      font-weight: 800;
      color: #1877f2;
      flex-shrink: 0;
    }
    
    @media (min-width: 768px) {
      .facebook-logo {
        width: 48px;
        height: 48px;
        font-size: 1.5rem;
      }
    }
    
    .facebook-info {
      flex: 1;
      min-width: 0;
    }
    
    .facebook-name {
      font-size: 1.125rem;
      font-weight: 700;
      color: var(--white);
      margin-bottom: var(--space-xs);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    @media (min-width: 768px) {
      .facebook-name {
        font-size: 1.25rem;
      }
    }
    
    .facebook-stats {
      display: flex;
      flex-wrap: wrap;
      gap: var(--space-sm);
      color: rgba(255, 255, 255, 0.9);
      font-size: 0.75rem;
    }
    
    @media (min-width: 768px) {
      .facebook-stats {
        font-size: 0.875rem;
      }
    }
    
    .facebook-follow-btn {
      padding: var(--space-sm) var(--space-md);
      background: var(--white);
      color: #1877f2;
      border: none;
      border-radius: var(--radius-full);
      font-weight: 600;
      cursor: pointer;
      transition: all var(--transition-fast);
      font-size: 0.875rem;
      white-space: nowrap;
      flex-shrink: 0;
    }
    
    .facebook-follow-btn:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }
    
    .facebook-content {
      padding: var(--space-lg);
      text-align: center;
    }
    
    .facebook-content p {
      color: var(--text-secondary);
      margin-bottom: var(--space-md);
      font-size: 0.875rem;
    }
    
    @media (min-width: 768px) {
      .facebook-content p {
        font-size: 1rem;
      }
    }
    
    .facebook-link {
      display: inline-flex;
      align-items: center;
      gap: var(--space-xs);
      padding: var(--space-sm) var(--space-lg);
      background: linear-gradient(135deg, var(--primary-red), var(--primary-dark-red));
      color: var(--white);
      border-radius: var(--radius-full);
      font-weight: 600;
      transition: all var(--transition-fast);
    }
    
    .facebook-link:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
      gap: var(--space-sm);
    }
    
    /* ===== Pagination ===== */
    .pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: var(--space-sm);
      padding: var(--space-xl) 0;
      flex-wrap: wrap;
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
    
    /* ===== Mobile Footer - IMPROVED ===== */
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
      /* Header adjustments */
      .header {
        height: 60px;
      }
      
      .logo {
        max-width: 120px;
      }
      
      .site-title {
        font-size: 1rem;
      }
      
      /* Category nav */
      .category-nav {
        height: 50px;
        top: 60px;
      }
      
      /* Breaking news */
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
      
      /* Hero section mobile */
      .slider-container {
        height: 250px;
      }
      
      .slide-content {
        padding: var(--space-md);
      }
      
      .slide-title {
        font-size: 1.25rem;
      }
      
      .slide-meta {
        font-size: 0.75rem;
        gap: var(--space-sm);
      }
      
      /* Calendar mobile */
      .calendar-card {
        padding: var(--space-md);
      }
      
      /* News grid mobile */
      .news-grid {
        gap: var(--space-md);
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
      
      /* Facebook mobile improvements */
      .facebook-header {
        flex-wrap: wrap;
        gap: var(--space-sm);
      }
      
      .facebook-name {
        font-size: 1rem;
        white-space: normal;
        -webkit-line-clamp: 1;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }
      
      .facebook-stats {
        gap: var(--space-xs);
        font-size: 0.7rem;
      }
      
      .facebook-follow-btn {
        padding: var(--space-xs) var(--space-sm);
        font-size: 0.75rem;
        margin-top: var(--space-xs);
      }
      
      .facebook-content {
        padding: var(--space-md);
      }
      
      /* Mobile footer */
      .mobile-footer {
        height: 70px;
      }
      
      .mobile-nav-icon {
        width: 20px;
        height: 20px;
      }
      
      .mobile-nav-label {
        font-size: 0.7rem;
      }
    }
    
    @media (max-width: 374px) {
      /* Extra small devices */
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
      
      .slider-container {
        height: 200px;
      }
      
      .slide-title {
        font-size: 1.1rem;
      }
      
      .news-grid {
        grid-template-columns: 1fr;
      }
    }
    
    /* ===== Loading States ===== */
    .loading {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: var(--space-2xl);
      color: var(--text-secondary);
      font-size: 0.875rem;
      grid-column: 1 / -1;
    }
    
    /* ===== Animation for mobile footer ===== */
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
    
    /* ===== Ensure content is not hidden ===== */
    .main-content > .container {
      position: relative;
      z-index: 1;
    }
    
    /* ===== Fix for calendar grid on mobile ===== */
    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: var(--space-xs);
      margin-bottom: var(--space-lg);
    }
    
    .calendar-date {
      aspect-ratio: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.875rem;
    }
    
    @media (max-width: 640px) {
      .calendar-date {
        font-size: 0.75rem;
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
                 class="category-link <?php echo (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($category['name']); ?>
                <?php if ($count['count'] > 0): ?>
                  <span class="news-count-badge"><?php echo $count['count']; ?></span>
                <?php endif; ?>
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
      
      <!-- Hero Section -->
      <section class="hero-section">
        <div class="hero-grid">
          <!-- Slider -->
          <div class="slider-container">
            <div class="slider" id="mainSlider">
              <?php if (!empty($featuredNews)): ?>
                <?php foreach ($featuredNews as $index => $featured): ?>
                  <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                    <?php
                    $imageSrc = !empty($featured['image']) 
                      ? $base_url . 'uploads/news/' . htmlspecialchars($featured['image'])
                      : 'https://images.unsplash.com/photo-1588681664899-f142ff2dc9b1?q=80&w=1600&auto=format&fit=crop';
                    ?>
                    <img src="<?php echo $imageSrc; ?>" alt="<?php echo htmlspecialchars($featured['title']); ?>" class="slide-image" />
                    
                    <div class="slide-content">
                      <?php if (!empty($featured['category_name'])): ?>
                        <span class="slide-category"><?php echo htmlspecialchars($featured['category_name']); ?></span>
                      <?php endif; ?>
                      
                      <h2 class="slide-title"><?php echo htmlspecialchars($featured['title']); ?></h2>
                      
                      <div class="slide-meta">
                        <?php
                        $publishTime = new DateTime($featured['published_at'] ?: $featured['created_at']);
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
                        
                        <span>•</span>
                        
                        <?php
                        $wordCount = str_word_count(strip_tags($featured['content']));
                        $readingTime = ceil($wordCount / 200);
                        echo max(1, $readingTime) . ' நிமிடம் வாசிப்பு';
                        ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <!-- Default slide -->
                <div class="slide active">
                  <img src="https://images.unsplash.com/photo-1588681664899-f142ff2dc9b1?q=80&w=1600&auto=format&fit=crop" 
                       alt="Liked தமிழ்" class="slide-image" />
                  
                  <div class="slide-content">
                    <span class="slide-category">வரவேற்பு</span>
                    <h2 class="slide-title">Liked தமிழ் - உங்கள் நம்பகமான செய்தி மூலம்</h2>
                    <div class="slide-meta">இப்போது • 3 நிமிடம் வாசிப்பு</div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
            
            <div class="slider-controls">
              <button class="slider-btn prev-slide" aria-label="முந்தைய செய்தி">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="15 18 9 12 15 6" />
                </svg>
              </button>
              <button class="slider-btn next-slide" aria-label="அடுத்த செய்தி">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="9 18 15 12 9 6" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Calendar -->
          <div class="calendar-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-lg);">
              <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--accent-yellow);" id="calendarTitle">செய்தி காலண்டர்</h3>
              <div style="display: flex; gap: var(--space-xs);">
                <button class="calendar-nav-btn prev-month" aria-label="முந்தைய மாதம்" style="width: 36px; height: 36px; background: var(--glass-bg); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-secondary); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all var(--transition-fast);">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6" />
                  </svg>
                </button>
                <button class="calendar-nav-btn next-month" aria-label="அடுத்த மாதம்" style="width: 36px; height: 36px; background: var(--glass-bg); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-secondary); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all var(--transition-fast);">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 18 15 12 9 6" />
                  </svg>
                </button>
              </div>
            </div>
            
            <div class="calendar-grid" style="color: var(--text-muted); font-size: 0.75rem; font-weight: 600; text-align: center; margin-bottom: var(--space-sm);">
              <div>தி</div>
              <div>செ</div>
              <div>பு</div>
              <div>வி</div>
              <div>வெ</div>
              <div>ச</div>
              <div>ஞா</div>
            </div>
            
            <div class="calendar-grid" id="calendarDates"></div>
            
            <p style="color: var(--text-muted); font-size: 0.75rem; text-align: center; margin-top: var(--space-md);">
              தேதியைத் தட்டவும் — செய்திகளை வடிகட்டவும்
            </p>
          </div>
        </div>
      </section>

      <!-- Main News Section -->
      <section>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-lg);">
          <div>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary);">
              <?php 
              if ($selectedDate == date('Y-m-d')) {
                echo 'இன்றைய செய்திகள்';
              } else {
                echo date('d/m/Y', strtotime($selectedDate)) . ' செய்திகள்';
              }
              ?>
            </h2>
            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-top: var(--space-xs);">
              <?php echo $totalNews; ?> செய்திகள் கிடைக்கின்றன
            </p>
          </div>
        </div>

        <div class="news-grid">
          <?php if (!empty($news)): ?>
            <?php foreach ($news as $item): ?>
              <article class="news-card">
                <a href="news-detail.php?id=<?php echo $item['id']; ?>">
                  <div class="news-image">
                    <?php
                    $imageQuery = "SELECT image_path FROM news_images WHERE news_id = ? ORDER BY display_order LIMIT 1";
                    $imageStmt = $db->prepare($imageQuery);
                    $imageStmt->execute([$item['id']]);
                    $newsImage = $imageStmt->fetch(PDO::FETCH_ASSOC);
                    
                    $imageSrc = '';
                    if (!empty($item['image'])) {
                      $imageSrc = $base_url . 'uploads/news/' . htmlspecialchars($item['image']);
                    } elseif ($newsImage && !empty($newsImage['image_path'])) {
                      $imageSrc = $base_url . htmlspecialchars($newsImage['image_path']);
                    } else {
                      $imageSrc = 'https://picsum.photos/id/' . rand(1000, 1100) . '/800/500';
                    }
                    ?>
                    <img src="<?php echo $imageSrc; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" />
                    
                    <?php if (!empty($item['category_names'])): ?>
                      <span class="news-category">
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
          <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: var(--space-2xl) var(--space-lg); background: var(--bg-card); border-radius: var(--radius-lg); border: 2px dashed var(--border-color);">
              <div style="font-size: 3rem; color: var(--text-muted); margin-bottom: var(--space-lg);">📰</div>
              <h3 style="font-size: 1.5rem; color: var(--text-primary); margin-bottom: var(--space-sm);">செய்திகள் இல்லை</h3>
              <p style="color: var(--text-secondary); margin-bottom: var(--space-lg);">
                <?php echo date('d/m/Y', strtotime($selectedDate)); ?> தேதிக்கு செய்திகள் இல்லை.
              </p>
              <a href="index.php" style="display: inline-flex; align-items: center; gap: var(--space-xs); padding: var(--space-sm) var(--space-md); background: linear-gradient(135deg, var(--primary-red), var(--primary-dark-red)); color: var(--white); border: none; border-radius: var(--radius-md); font-weight: 600; cursor: pointer; transition: all var(--transition-base);">
                இன்றைய செய்திகளைப் பார்க்க
              </a>
            </div>
          <?php endif; ?>
        </div>

        <!-- Facebook Section - Improved for Mobile -->
        <div class="facebook-section">
          <div class="facebook-card">
            <div class="facebook-header">
              <div class="facebook-logo">f</div>
              <div class="facebook-info">
                <h3 class="facebook-name">Liked தமிழ் Facebook பக்கம்</h3>
                <div class="facebook-stats">
                  <span>12.5K பின்தொடர்பவர்கள்</span>
                  <span>•</span>
                  <span>1,234 பதிவுகள்</span>
                  <span>•</span>
                  <span>ட்ரெண்டிங்</span>
                </div>
              </div>
              <a href="https://www.facebook.com/liked.tamil/" target="_blank" class="facebook-follow-btn">
                Follow
              </a>
            </div>
            
            <div class="facebook-content">
              <p>எங்கள் Facebook பக்கத்தில் சமீபத்திய செய்திகள், புதுப்பிப்புகள் மற்றும் சிறப்பு உள்ளடக்கங்களைப் பெறுங்கள்</p>
              <a href="https://www.facebook.com/liked.tamil/" target="_blank" class="facebook-link">
                Facebook இல் பார்க்க
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="5" y1="12" x2="19" y2="12" />
                  <polyline points="12 5 19 12 12 19" />
                </svg>
              </a>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
          <nav class="pagination" aria-label="பக்கமாற்றம்">
            <?php if ($page > 1): ?>
              <a href="index.php?date=<?php echo $selectedDate; ?>&page=<?php echo $page - 1; ?>" 
                 style="display: flex; align-items: center; justify-content: center; min-width: 40px; height: 40px; padding: 0 var(--space-sm); background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-secondary); font-weight: 500; font-size: 0.875rem; transition: all var(--transition-fast);"
                 aria-label="முந்தைய பக்கம்">
                &larr;
              </a>
            <?php endif; ?>
            
            <?php
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            
            if ($startPage > 1): ?>
              <a href="index.php?date=<?php echo $selectedDate; ?>&page=1" 
                 style="display: flex; align-items: center; justify-content: center; min-width: 40px; height: 40px; padding: 0 var(--space-sm); background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-secondary); font-weight: 500; font-size: 0.875rem; transition: all var(--transition-fast);">
                1
              </a>
              <?php if ($startPage > 2): ?>
                <span style="display: flex; align-items: center; justify-content: center; min-width: 40px; height: 40px; padding: 0 var(--space-sm); background: transparent; border: none; color: var(--muted); cursor: default;">
                  ...
                </span>
              <?php endif; ?>
            <?php endif; ?>
            
            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
              <a href="index.php?date=<?php echo $selectedDate; ?>&page=<?php echo $i; ?>" 
                 style="display: flex; align-items: center; justify-content: center; min-width: 40px; height: 40px; padding: 0 var(--space-sm); background: <?php echo ($i == $page) ? 'linear-gradient(135deg, var(--primary-red), var(--primary-dark-red))' : 'var(--bg-card)'; ?>; border: 1px solid <?php echo ($i == $page) ? 'transparent' : 'var(--border-color)'; ?>; border-radius: var(--radius-md); color: <?php echo ($i == $page) ? 'var(--white)' : 'var(--text-secondary)'; ?>; font-weight: <?php echo ($i == $page) ? '600' : '500'; ?>; font-size: 0.875rem; transition: all var(--transition-fast);"
                 aria-label="பக்கம் <?php echo $i; ?>"
                 aria-current="<?php echo ($i == $page) ? 'page' : 'false'; ?>">
                <?php echo $i; ?>
              </a>
            <?php endfor; ?>
            
            <?php if ($endPage < $totalPages): ?>
              <?php if ($endPage < $totalPages - 1): ?>
                <span style="display: flex; align-items: center; justify-content: center; min-width: 40px; height: 40px; padding: 0 var(--space-sm); background: transparent; border: none; color: var(--muted); cursor: default;">
                  ...
                </span>
              <?php endif; ?>
              <a href="index.php?date=<?php echo $selectedDate; ?>&page=<?php echo $totalPages; ?>" 
                 style="display: flex; align-items: center; justify-content: center; min-width: 40px; height: 40px; padding: 0 var(--space-sm); background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-secondary); font-weight: 500; font-size: 0.875rem; transition: all var(--transition-fast);">
                <?php echo $totalPages; ?>
              </a>
            <?php endif; ?>
            
            <?php if ($page < $totalPages): ?>
              <a href="index.php?date=<?php echo $selectedDate; ?>&page=<?php echo $page + 1; ?>" 
                 style="display: flex; align-items: center; justify-content: center; min-width: 40px; height: 40px; padding: 0 var(--space-sm); background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); color: var(--text-secondary); font-weight: 500; font-size: 0.875rem; transition: all var(--transition-fast);"
                 aria-label="அடுத்த பக்கம்">
                &rarr;
              </a>
            <?php endif; ?>
          </nav>
        <?php endif; ?>
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
    <a href="index.php" class="mobile-nav-item <?php echo (!isset($_GET['category']) && !isset($_GET['page'])) ? 'active' : ''; ?>">
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
    // Initialize variables
    let currentSlide = 0;
    let slideInterval;
    const slides = document.querySelectorAll('.slide');
    const totalSlides = slides.length;
    
    let calendarDate = new Date('<?php echo $selectedDate; ?>');
    const selectedDate = new Date('<?php echo $selectedDate; ?>');
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // Slider functionality
    function initSlider() {
      if (totalSlides <= 1) return;
      
      startSlider();
      
      document.querySelector('.prev-slide')?.addEventListener('click', () => {
        changeSlide(-1);
        resetSliderInterval();
      });
      
      document.querySelector('.next-slide')?.addEventListener('click', () => {
        changeSlide(1);
        resetSliderInterval();
      });
      
      const slider = document.querySelector('.slider-container');
      if (slider) {
        slider.addEventListener('mouseenter', stopSlider);
        slider.addEventListener('mouseleave', startSlider);
        slider.addEventListener('touchstart', stopSlider);
        slider.addEventListener('touchend', startSlider);
      }
    }
    
    function changeSlide(direction) {
      slides[currentSlide].classList.remove('active');
      currentSlide = (currentSlide + direction + totalSlides) % totalSlides;
      slides[currentSlide].classList.add('active');
    }
    
    function startSlider() {
      if (totalSlides <= 1) return;
      slideInterval = setInterval(() => changeSlide(1), 5000);
    }
    
    function stopSlider() {
      clearInterval(slideInterval);
    }
    
    function resetSliderInterval() {
      stopSlider();
      startSlider();
    }

    // Calendar functionality
    function initCalendar() {
      renderCalendar();
      
      document.querySelector('.prev-month')?.addEventListener('click', () => {
        calendarDate.setMonth(calendarDate.getMonth() - 1);
        renderCalendar();
      });
      
      document.querySelector('.next-month')?.addEventListener('click', () => {
        calendarDate.setMonth(calendarDate.getMonth() + 1);
        renderCalendar();
      });
    }
    
    function renderCalendar() {
      const year = calendarDate.getFullYear();
      const month = calendarDate.getMonth();
      
      const monthNames = [
        'ஜனவரி', 'பிப்ரவரி', 'மார்ச்', 'ஏப்ரல்', 
        'மே', 'ஜூன்', 'ஜூலை', 'ஆகஸ்ட்', 
        'செப்டம்பர்', 'அக்டோபர்', 'நவம்பர்', 'டிசம்பர்'
      ];
      document.getElementById('calendarTitle').textContent = `${monthNames[month]} ${year}`;
      
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const daysInMonth = lastDay.getDate();
      const startDay = (firstDay.getDay() + 6) % 7;
      
      const calendarDates = document.getElementById('calendarDates');
      calendarDates.innerHTML = '';
      
      for (let i = 0; i < startDay; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'calendar-date empty';
        calendarDates.appendChild(emptyCell);
      }
      
      for (let day = 1; day <= daysInMonth; day++) {
        const dateCell = document.createElement('a');
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        
        dateCell.href = `index.php?date=${dateStr}`;
        dateCell.className = 'calendar-date';
        dateCell.textContent = day;
        dateCell.setAttribute('aria-label', `${day} ${monthNames[month]}`);
        
        const cellDate = new Date(year, month, day);
        cellDate.setHours(0, 0, 0, 0);
        if (cellDate.getTime() === today.getTime()) {
          dateCell.classList.add('today');
        }
        
        if (dateStr === '<?php echo $selectedDate; ?>') {
          dateCell.classList.add('selected');
        }
        
        calendarDates.appendChild(dateCell);
      }
    }

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

    // Initialize everything
    document.addEventListener('DOMContentLoaded', () => {
      initSlider();
      initCalendar();
      
      document.getElementById('searchToggle')?.addEventListener('click', toggleSearch);
      
      // Update mobile footer active state
      const currentPath = window.location.pathname;
      const mobileNavItems = document.querySelectorAll('.mobile-nav-item');
      
      mobileNavItems.forEach(item => {
        if (item.getAttribute('href') === currentPath) {
          item.classList.add('active');
        } else if (currentPath === '/' && item.getAttribute('href') === 'index.php') {
          item.classList.add('active');
        }
      });
    });

    function openSubscription() {
      alert('சந்தா செயல்பாடு விரைவில் கிடைக்கும்');
    }

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
    });
    
    window.addEventListener('resize', adjustContentHeight);



    
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
</body>
</html>