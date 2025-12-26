-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 26, 2025 at 03:46 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `news_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(25) DEFAULT NULL,
  `PASSWORD` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `PASSWORD`) VALUES
(1, 'admin', 'admin123'),
(2, 'editor', 'editor123');

-- --------------------------------------------------------

--
-- Table structure for table `autosave_data`
--

CREATE TABLE `autosave_data` (
  `id` int(11) NOT NULL,
  `title` text DEFAULT NULL,
  `content` text DEFAULT NULL,
  `saved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `autosave_data`
--

INSERT INTO `autosave_data` (`id`, `title`, `content`, `saved_at`) VALUES
(556, '', '', '2025-12-23 15:55:28'),
(557, '', '', '2025-12-23 16:16:41'),
(558, '', '', '2025-12-23 16:17:12'),
(559, '', '', '2025-12-23 18:29:04'),
(560, 'இலங்கை அணி அபார வெற்றி – ரசிகர்கள் உற்சாகம்!', 'இன்றைய முக்கியமான போட்டியில் இலங்கை விளையாட்டு அணி சிறப்பான ஆட்டத்தை வெளிப்படுத்தி எதிரணியை தோற்கடித்தது. தொடக்கத்தில் சற்று அழுத்தம் இருந்தாலும், நடுப்பகுதியில் வீரர்களின் சிறந்த ஒத்துழைப்பு அணியை வெற்றிப்பாதைக்கு அழைத்துச் சென்றது. பந்துவீச்சாளர்கள் கட்டுப்பாட்டுடன் விளையாட, துடுப்பாட்டத்தில் இளம் வீரர் ஒருவர் அதிரடி ஆட்டம் ஆடி ரசிகர்களின் பாராட்டைப் பெற்றார். இந்த வெற்றி அணியின் நம்பிக்கையை அதிகரித்து, வரவிருக்கும் போட்டிகளுக்கான உற்சாகத்தை மேலும் உயர்த்தியுள்ளது.', '2025-12-23 18:31:35'),
(561, '', '', '2025-12-24 09:17:45'),
(562, '', '', '2025-12-24 09:45:16'),
(563, '', '', '2025-12-24 10:04:52'),
(564, '', '', '2025-12-24 10:09:29'),
(565, 'தமிழ் இசையில் புதிய தலைமுறையின் எழுச்சி', '<p data-start=\"110\" data-end=\"609\">தமிழ் இசைத்துறையில் இளம் கலைஞர்களின் பங்களிப்பு நாளுக்கு நாள் அதிகரித்து வருகிறது. பாரம்பரிய கர்நாடக இசையையும், நவீன இசை வடிவங்களையும் இணைக்கும் முயற்சிகள் ரசிகர்களிடையே பெரும் வரவேற்பைப் பெற்றுள்ளன. சமீப காலமாக வெளியிடப்படும் இசை ஆல்பங்கள் மற்றும் மேடை நிகழ்ச்சிகள், தமிழ் கலாச்சாரத்தின் ஆழத்தையும் இசையின் புதுமையையும் ஒரே நேரத்தில் வெளிப்படுத்துகின்றன. இதன் மூலம் தமிழ் இசை உலகளாவிய அளவில் கவனம் பெறுவதுடன், எதிர்கால தலைமுறையினருக்கும் இசை மீது ஆர்வத்தை ஏற்படுத்தும் முக்கிய பங்கையும் வகிக்கிறது.</p>', '2025-12-24 10:18:59'),
(566, '', '', '2025-12-24 10:29:28'),
(567, '', '', '2025-12-24 10:29:37'),
(568, '', '', '2025-12-24 10:30:05'),
(569, 'அஇஉ்இஉ் இஉ்இஉ்இஉ் எகபெப', 'உஎ்உ்எ உ்எ உ்எஉ்எ உ்எ உ்எஉ்எ உ்எஉ ்்்்்எஉ்எஉ்உ', '2025-12-24 11:03:55'),
(570, 'தந்தையின் தாலாட்டு..', '<p style=\"margin-bottom: 25px; line-height: 28px; color: rgb(35, 35, 35); font-family: Arimo, sans-serif; font-size: 15px;\"><b>பூப்போன்ற மகளே<br>பொன்வண்ணச் சிலையே<br>தேன்போல இனிக்கும்<br>தெம்மாங்கு பாட்டே</b></p><p style=\"margin-bottom: 25px; line-height: 28px; color: rgb(35, 35, 35); font-family: Arimo, sans-serif; font-size: 15px;\"><b>விழியோரம் நான் கண்ட கனவு – அதன்<br>விடையாக நீ வந்த வரவு.<br>மொழியெல்லாம் தமிழ்போல இனிப்பு – எந்தன்<br>வழியெல்லாம் நிறையும் உன் நினைப்பு.</b></p><p style=\"margin-bottom: 25px; line-height: 28px; color: rgb(35, 35, 35); font-family: Arimo, sans-serif; font-size: 15px;\"><b>கண்ணே நீ கண்மூடி தூங்கு – எந்தன்<br>தோள்மீது தலை சாய்த்து தூங்கு<br>தாயாக எனை மாற்றும் பெண்ணே – எந்தன்<br>தாயாக உனை காப்பேன் கண்ணே..</b></p>', '2025-12-24 14:17:17'),
(571, 'தந்தையின் தாலாட்டு..', '<p style=\"margin-bottom: 25px; line-height: 28px; color: rgb(35, 35, 35); font-family: Arimo, sans-serif; font-size: 15px;\"><b>பூப்போன்ற மகளே<br>பொன்வண்ணச் சிலையே<br>தேன்போல இனிக்கும்<br>தெம்மாங்கு பாட்டே</b></p><p style=\"margin-bottom: 25px; line-height: 28px; color: rgb(35, 35, 35); font-family: Arimo, sans-serif; font-size: 15px;\"><b>விழியோரம் நான் கண்ட கனவு – அதன்<br>விடையாக நீ வந்த வரவு.<br>மொழியெல்லாம் தமிழ்போல இனிப்பு – எந்தன்<br>வழியெல்லாம் நிறையும் உன் நினைப்பு.</b></p><p style=\"margin-bottom: 25px; line-height: 28px; color: rgb(35, 35, 35); font-family: Arimo, sans-serif; font-size: 15px;\"><b>கண்ணே நீ கண்மூடி தூங்கு – எந்தன்<br>தோள்மீது தலை சாய்த்து தூங்கு<br>தாயாக எனை மாற்றும் பெண்ணே – எந்தன்<br>தாயாக உனை காப்பேன் கண்ணே..</b></p>', '2025-12-24 14:20:25'),
(572, 'தந்தையின் தாலாட்டு..', '<p style=\"margin-bottom: 25px; line-height: 28px; color: rgb(35, 35, 35); font-family: Arimo, sans-serif; font-size: 15px;\"><b>பூப்போன்ற மகளே<br>பொன்வண்ணச் சிலையே<br>தேன்போல இனிக்கும்<br>தெம்மாங்கு பாட்டே</b></p><p style=\"margin-bottom: 25px; line-height: 28px; color: rgb(35, 35, 35); font-family: Arimo, sans-serif; font-size: 15px;\"><b>விழியோரம் நான் கண்ட கனவு – அதன்<br>விடையாக நீ வந்த வரவு.<br>மொழியெல்லாம் தமிழ்போல இனிப்பு – எந்தன்<br>வழியெல்லாம் நிறையும் உன் நினைப்பு.</b></p><p style=\"margin-bottom: 25px; line-height: 28px; color: rgb(35, 35, 35); font-family: Arimo, sans-serif; font-size: 15px;\"><b>கண்ணே நீ கண்மூடி தூங்கு – எந்தன்<br>தோள்மீது தலை சாய்த்து தூங்கு<br>தாயாக எனை மாற்றும் பெண்ணே – எந்தன்<br>தாயாக உனை காப்பேன் கண்ணே..</b></p>', '2025-12-24 15:06:03'),
(573, 'தமிழ்நாட்டில் புதிய தொழில்நுட்ப மையம் திறப்பு: ஆயிரக்கணக்கான வேலைவாய்ப்புகள் உருவாக்கப்படும்', '<div class=\"text-lg leading-relaxed text-foreground/90 mb-6 clearfix\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); margin-bottom: 1.5rem; font-size: 1.125rem; line-height: 1.625; color: rgba(249, 250, 251, 0.9); font-family: &quot;Noto Sans Tamil&quot;, Inter, system-ui, -apple-system, sans-serif; background-color: rgb(10, 10, 10);\"><p class=\"mb-5\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); margin-bottom: 1.25rem;\">தமிழ்நாடு அரசு புதிய தொழில்நுட்ப மையத்தை சென்னையில் திறக்கவுள்ளது. இந்த மையம் செயற்கை நுண்ணறிவு, இயந்திர கற்றல் மற்றும் தரவு அறிவியல் துறைகளில் கவனம் செலுத்தும். இது இந்தியாவின் மிகப்பெரிய தொழில்நுட்ப மையங்களில் ஒன்றாக உருவாகும் என எதிர்பார்க்கப்படுகிறது.</p><p class=\"mb-5\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); margin-bottom: 1.25rem;\">இந்த திட்டத்தின் மூலம் ஆயிரக்கணக்கான இளைஞர்களுக்கு வேலைவாய்ப்புகள் உருவாக்கப்படும் என அரசு தெரிவித்துள்ளது. குறிப்பாக கிராமப்புற பகுதிகளில் இருந்து வரும் மாணவர்களுக்கு முன்னுரிமை அளிக்கப்படும்.</p><p class=\"mb-5\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); margin-bottom: 1.25rem;\">தொழில்நுட்ப மையத்தில் நவீன ஆய்வுக்கூடங்கள், கணினி வசதிகள் மற்றும் பயிற்சி மையங்கள் அமைக்கப்படும். உலகின் முன்னணி தொழில்நுட்ப நிறுவனங்களுடன் இணைந்து பணியாற்றும் வாய்ப்புகளும் உருவாக்கப்படும்.</p></div><div class=\"clear-both\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); clear: both; color: rgb(249, 250, 251); font-family: &quot;Noto Sans Tamil&quot;, Inter, system-ui, -apple-system, sans-serif; font-size: medium; background-color: rgb(10, 10, 10);\"></div><div class=\"text-lg leading-relaxed text-foreground/90\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); font-size: 1.125rem; line-height: 1.625; color: rgba(249, 250, 251, 0.9); font-family: &quot;Noto Sans Tamil&quot;, Inter, system-ui, -apple-system, sans-serif; background-color: rgb(10, 10, 10);\"><p class=\"mb-5\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); margin-bottom: 1.25rem;\">இந்த மையம் சுற்றுச்சூழலுக்கு ஏற்ற வகையில் வடிவமைக்கப்பட்டுள்ளது. சூரிய ஆற்றல் மூலம் இயங்கும் இந்த கட்டிடம் பசுமை கட்டிட தரநிலைகளைப் பூர்த்தி செய்யும்.</p><p class=\"mb-5\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); margin-bottom: 1.25rem;\">மாணவர்களுக்கான புலமைப் பெயர்வு திட்டங்களும் அறிவிக்கப்பட்டுள்ளன. தகுதியான மாணவர்கள் வெளிநாட்டு பல்கலைக்கழகங்களில் படிக்கும் வாய்ப்பைப் பெறலாம்.</p><p class=\"mb-5\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); margin-bottom: 1.25rem;\">இந்த திட்டம் தமிழ்நாட்டின் தொழில்நுட்ப வளர்ச்சியில் ஒரு முக்கிய மைல்கல்லாக அமையும் என தொழில்துறை நிபுணர்கள் கருத்து தெரிவித்துள்ளனர். இது மாநிலத்தின் பொருளாதார வளர்ச்சிக்கும் பெரிதும் உதவும்.</p><div><br></div></div><figure class=\"mt-10 mb-6 animate-fade-in\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); margin-top: 2.5rem; margin-bottom: 1.5rem; animation: 0.5s ease-out 0s 1 normal forwards running fade-in; color: rgb(249, 250, 251); font-family: &quot;Noto Sans Tamil&quot;, Inter, system-ui, -apple-system, sans-serif; font-size: medium; background-color: rgb(10, 10, 10);\"><div class=\"max-w-2xl mx-auto\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); margin-left: auto; margin-right: auto; max-width: 42rem;\"></div></figure>', '2025-12-24 15:07:57'),
(574, 'அரபு நாடு­களின் சர்­வா­தி­கா­ரி­களைக் கொண்ட ஆட்சி அதி­கார சுவரால் பாது­காக்­கப்­படும் இஸ்ரேல்', '<p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">மத்­திய கிழக்கின் தற்­போ­தைய அர­சியல் சூழ்­நி­லையை விவரித்த டாக்டர் தமீமி “இஸ்ரேல் அரபு ஆட்­சி­யா­ளர்­ளைக்­கொண்ட பாது­காப்பு சுவரால் பாது­காக்­கப்­ப­டு­கின்­றது.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">அரபு மக்கள் ஒரு சிறையில் வசித்து வரு­கின்­றனர். அவர்­களின் சிறைச்­சா­லைகள் அவர்­களின் ஆட்­சி­யா­ளர்­க­ளாவர்.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">அந்த ஆட்­சி­யா­ளர்கள் தமது மக்­களின் அடிப்­படை உரி­மை­களை மறுக்­கின்­றனர். மேலும் அவர்­களின் செல்­வத்தைத் திருடி வேறு இடங்­க­ளுக்கு கடத்தி வரு­கின்­றனர்” எனத் தெரி­வித்­துள்ளார்.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">இந்த சோகம் ஒரு நூற்­றாண்­டுக்கும் மேலாக தொடர்ந்து வரு­கின்­றது. இது முஸ்லிம் மத்­திய கிழக்­கிற்கு எதி­ரான மேலைத்­தேச தீய வடி­வ­மைப்­பு­க­ளுக்கு உத­வு­கின்­றது.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">முஸ்லிம் மத்­திய கிழக்கு அறி­யா­மை­யு­டனும் வளர்ச்­சி­ய­டை­யா­மலும் தொடர்ந்து இருப்­பதை உறுதி செய்­வ­தற்கும், துருக்கிப் பேர­ரசு போன்ற ஒரு பலம்­மிக்க சக்­தி­யாக மீண்டும் தலை நிமி­ராமல் தடுப்­ப­தற்கும் உரிய ஒரு வழி­யாக இது இருந்­தது.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">அன்று முதற் கொண்டே இஸ்ரேல் அண்டை நாடு­க­ளுக்கு எதி­ராக ஏரா­ள­மான போர்­களைத் தொடுத்­துள்­ளது. மற்றும் எகிப்­திய, சிரிய, ஜோர்­டா­னிய மற்றும் லெப­னானின் பிர­தே­சங்­க­ளையும் அது கைப்­பற்றி உள்­ளது.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">அவற்றை இஸ்ரேல் அமெ­ரிக்­க –­ ஐ­ரோப்­பிய ஆத­ர­வுடன் சட்­ட­வி­ரோ­த­மாக தன்­னோடு இணைத்துக் கொண்­டது. இஸ்ரேல் இது­வரை 30 இற்கும் மேற்­பட்ட பலஸ்­தீ­னர்கள் மீதான மிகப்­பெ­ரிய அள­வி­லான இனப்­ப­டு­கொ­லை­களைப் புரிந்­துள்­ளது.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">அமெ­ரிக்கா மற்றும் ஐரோப்­பி­யர்­களின் ஆத­ர­வுடன் இந்த காட்­டு­மி­ராண்­டித்­த­னங்கள் அனைத்தும் கவ­னிக்­கப்­ப­டாமல் மூடி மறைக்­கப்­பட்டு விட்­டன. அதே நேரத்தில் அரபு சர்­வா­தி­கா­ரிகள் இந்த விட­யத்தில் ஒட்­டு­மொத்­த­மாக அலட்­சி­ய­மா­கவே இருந்­துள்­ளனர்.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">இதற்­கி­டையில் 1979இல் ஈரானில் இடம்­பெற்ற இஸ்­லா­மியப் புரட்சி அங்கு ஆட்­சியில் இருந்த மேலைத்­தேச சார்பு பஹ்­லவி வம்­சத்தை 1979இல் கவிழ்த்­தது.</p>', '2025-12-26 15:38:40'),
(575, '', '', '2025-12-26 18:18:02'),
(576, 'அரபு நாடு­களின் சர்­வா­தி­கா­ரி­களைக் கொண்ட ஆட்சி அதி­கார சுவரால் பாது­காக்­கப்­படும் இஸ்ரேல்', '<p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">மத்­திய கிழக்கின் தற்­போ­தைய அர­சியல் சூழ்­நி­லையை விவரித்த டாக்டர் தமீமி “இஸ்ரேல் அரபு ஆட்­சி­யா­ளர்­ளைக்­கொண்ட பாது­காப்பு சுவரால் பாது­காக்­கப்­ப­டு­கின்­றது.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">அரபு மக்கள் ஒரு சிறையில் வசித்து வரு­கின்­றனர். அவர்­களின் சிறைச்­சா­லைகள் அவர்­களின் ஆட்­சி­யா­ளர்­க­ளாவர்.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">அந்த ஆட்­சி­யா­ளர்கள் தமது மக்­களின் அடிப்­படை உரி­மை­களை மறுக்­கின்­றனர். மேலும் அவர்­களின் செல்­வத்தைத் திருடி வேறு இடங்­க­ளுக்கு கடத்தி வரு­கின்­றனர்” எனத் தெரி­வித்­துள்ளார்.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">இந்த சோகம் ஒரு நூற்­றாண்­டுக்கும் மேலாக தொடர்ந்து வரு­கின்­றது. இது முஸ்லிம் மத்­திய கிழக்­கிற்கு எதி­ரான மேலைத்­தேச தீய வடி­வ­மைப்­பு­க­ளுக்கு உத­வு­கின்­றது.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">முஸ்லிம் மத்­திய கிழக்கு அறி­யா­மை­யு­டனும் வளர்ச்­சி­ய­டை­யா­மலும் தொடர்ந்து இருப்­பதை உறுதி செய்­வ­தற்கும், துருக்கிப் பேர­ரசு போன்ற ஒரு பலம்­மிக்க சக்­தி­யாக மீண்டும் தலை நிமி­ராமல் தடுப்­ப­தற்கும் உரிய ஒரு வழி­யாக இது இருந்­தது.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">அன்று முதற் கொண்டே இஸ்ரேல் அண்டை நாடு­க­ளுக்கு எதி­ராக ஏரா­ள­மான போர்­களைத் தொடுத்­துள்­ளது. மற்றும் எகிப்­திய, சிரிய, ஜோர்­டா­னிய மற்றும் லெப­னானின் பிர­தே­சங்­க­ளையும் அது கைப்­பற்றி உள்­ளது.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">அவற்றை இஸ்ரேல் அமெ­ரிக்­க –­ ஐ­ரோப்­பிய ஆத­ர­வுடன் சட்­ட­வி­ரோ­த­மாக தன்­னோடு இணைத்துக் கொண்­டது. இஸ்ரேல் இது­வரை 30 இற்கும் மேற்­பட்ட பலஸ்­தீ­னர்கள் மீதான மிகப்­பெ­ரிய அள­வி­லான இனப்­ப­டு­கொ­லை­களைப் புரிந்­துள்­ளது.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">அமெ­ரிக்கா மற்றும் ஐரோப்­பி­யர்­களின் ஆத­ர­வுடன் இந்த காட்­டு­மி­ராண்­டித்­த­னங்கள் அனைத்தும் கவ­னிக்­கப்­ப­டாமல் மூடி மறைக்­கப்­பட்டு விட்­டன. அதே நேரத்தில் அரபு சர்­வா­தி­கா­ரிகள் இந்த விட­யத்தில் ஒட்­டு­மொத்­த­மாக அலட்­சி­ய­மா­கவே இருந்­துள்­ளனர்.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">இதற்­கி­டையில் 1979இல் ஈரானில் இடம்­பெற்ற இஸ்­லா­மியப் புரட்சி அங்கு ஆட்­சியில் இருந்த மேலைத்­தேச சார்பு பஹ்­லவி வம்­சத்தை 1979இல் கவிழ்த்­தது.</p>', '2025-12-26 18:18:30'),
(577, '', '', '2025-12-26 19:21:16');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `subcategories` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tabtype` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `subcategories`, `status`, `created_at`, `updated_at`, `tabtype`) VALUES
(51, 'கலைகள்', '[\"இசை\",\"கண்காட்சி\",\"நடனம்\",\"சிற்பம்\",\"பாரம்பரியம்\",\"ஓவியம்\",\"நாடகம்\",\"கைவினை\"]', 'active', '2025-12-19 08:17:45', '2025-12-24 04:40:55', 'footer 1'),
(52, 'கவிதைகள்', '[]', 'active', '2025-12-23 04:24:19', '2025-12-23 04:24:19', ''),
(53, 'தொடர் கட்டுரைகள்', '[]', 'active', '2025-12-23 04:24:31', '2025-12-23 04:24:31', ''),
(54, 'புகைப்பட தொகுப்பு', '[]', 'active', '2025-12-23 04:24:43', '2025-12-23 04:24:43', ''),
(55, 'அந்தரங்கம்', '[]', 'active', '2025-12-23 04:24:52', '2025-12-23 04:24:52', ''),
(56, 'வினோதம்', '[]', 'active', '2025-12-23 04:25:00', '2025-12-23 04:25:00', ''),
(58, 'செய்திகள்', '[]', 'active', '2025-12-23 04:25:20', '2025-12-23 04:25:20', ''),
(59, 'சிறப்பு செய்திகள்', '[]', 'active', '2025-12-23 04:25:28', '2025-12-23 04:25:28', ''),
(60, 'உள்ளூர் செய்திகள்', '[]', 'active', '2025-12-23 04:25:36', '2025-12-23 04:25:36', ''),
(61, 'இந்திய செய்திகள்', '[]', 'active', '2025-12-23 04:25:44', '2025-12-23 04:25:44', ''),
(62, 'உலக செய்திகள்', '[]', 'active', '2025-12-23 04:25:53', '2025-12-23 04:25:53', ''),
(63, 'அரசியல்', '[]', 'active', '2025-12-23 04:26:02', '2025-12-23 04:26:02', ''),
(64, 'சினிமா', '[]', 'active', '2025-12-23 04:26:10', '2025-12-23 04:26:10', ''),
(65, 'தொழில்நுட்பம்', '[]', 'active', '2025-12-23 04:26:17', '2025-12-23 04:26:17', ''),
(66, 'விளையாட்டு', '[]', 'active', '2025-12-23 04:26:26', '2025-12-23 04:26:26', ''),
(67, 'ஆன்மீகம்', '[]', 'active', '2025-12-23 04:26:34', '2025-12-23 04:26:34', ''),
(68, 'கட்டுரைகள்', '[]', 'active', '2025-12-23 04:26:43', '2025-12-23 04:26:43', '');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT 0,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `comment` text NOT NULL,
  `likes` int(11) DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `news_id`, `parent_id`, `name`, `email`, `comment`, `likes`, `status`, `created_at`) VALUES
(3, 106, 0, 'vithu', 'vithu@gmail.com', 'sjxjskxjksxkwskxnsjnhxjmsiakmzabzujakxjsoco', 0, 'approved', '2025-12-22 04:51:18');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `excerpt` varchar(255) DEFAULT NULL,
  `categories` varchar(500) DEFAULT NULL,
  `sub_categories` varchar(500) DEFAULT NULL,
  `status` text DEFAULT 'draft',
  `published_at` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image` varchar(255) DEFAULT NULL,
  `video` varchar(200) DEFAULT NULL,
  `embedded_video_url` text DEFAULT NULL,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` text DEFAULT NULL,
  `seo_keywords` text DEFAULT NULL,
  `is_autosave_converted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `content`, `excerpt`, `categories`, `sub_categories`, `status`, `published_at`, `created_at`, `updated_at`, `image`, `video`, `embedded_video_url`, `seo_title`, `seo_description`, `seo_keywords`, `is_autosave_converted`) VALUES
(72, 'jaaanu', '\n                    \n                    jaanu&nbsp;                                ', NULL, 'பிரிவின் பெயர்,sports,பிரிவின் பெயர் உட்பிரிவுகள்', '', 'published', NULL, '2025-12-12 08:25:46', '2025-12-26 05:17:47', '1765527946_pexels-anjana-c-169994-674010.jpg', '', NULL, '', '', '', 0),
(73, 'ஒரு அழகான தமிழ் கவிதை', 'காற்றின் நிசப்தத்தில் மலர்கள் பேசும் பொழுதில்,<br data-start=\"115\" data-end=\"118\">\r\nஎன் மனம் உன்னை எண்ணி மெளனமாக மலர்கிறது.<br data-start=\"157\" data-end=\"160\">\r\nஅலைகள் கரை சேரும் ஆசைப் போல,<br data-start=\"188\" data-end=\"191\">\r\nஎன்னை உன்னிடம் இழுக்கும் ஒரு நெடிய உணர்வு.<br data-start=\"233\" data-end=\"236\">\r\nநிலவின் ஒளியிலும் நட்சத்திரத்தின் மெல்லிய மெலோதியிலும்,<br data-start=\"291\" data-end=\"294\">\r\nஉன் புன்னகை துளிர்க்கும் ஒரு கனவாய் நிற்கிறது.', NULL, 'பிரிவின் பெயர்', 'cricket', 'published', NULL, '2025-12-12 10:19:59', '2025-12-12 10:19:59', '1765534799_Screenshot (172).png', '1765534799_WhatsApp Video 2025-12-03 at 12.53.29 PM.mp4', NULL, 'ஒரு அழகான தமிழ் கவிதை', 'காற்றின் நிசப்தத்தில் மலர்கள் பேசும் பொழுதில்,\r\nஎன் மனம் உன்னை எண்ணி மெளனமாக மலர்கிறது.\r\nஅலைகள் கரை சேரும் ஆசைப் போல,\r\nஎன்னை உன்னிடம் இழுக்கும் ஒரு நெடிய உணர்வு.\r\nந', '', 0),
(100, 'hello', 'sdfsdf', NULL, '', '', 'published', NULL, '2025-12-19 05:05:43', '2025-12-21 06:05:00', '1766120743_Screenshot (173).png', '', '', '', '', '', 0),
(101, 'hello', 'hi', NULL, '', '', 'published', NULL, '2025-12-19 05:10:44', '2025-12-21 06:04:45', '1766121044_Screenshot (168).png', '', '', '', '', '', 0),
(106, 'asdasd', 'asdasdasd', NULL, 'Entertainment', 'cartoon', 'published', NULL, '2025-12-19 05:32:07', '2025-12-19 05:32:07', '1766122327_Screenshot (175).png', '', '', '', '', '', 0),
(108, 'மௌனத்தில் பேசும் மண்ணின் குரல்', 'மண்ணின் மார்பில் விதைத்த கனவுகள்,<br data-start=\"350\" data-end=\"353\">\nமழையுடன் சேர்ந்து உயிர் பெறுகின்றன.<br data-start=\"388\" data-end=\"391\">\nஉழைப்பின் வியர்வை துளிகள்,<br data-start=\"417\" data-end=\"420\">\nநாளைய நம்பிக்கையாக முளைக்கின்றன.<br data-start=\"452\" data-end=\"455\">\nசத்தமில்லா போராட்டமே<br data-start=\"475\" data-end=\"478\">\nஇந்த விவசாயியின் வாழ்க்கைச் செய்தி.', NULL, 'கவிதைகள்', '', 'published', NULL, '2025-12-23 06:11:35', '2025-12-23 09:11:13', '1766470295_Screenshot (172).png', '', '', '', '', '', 0),
(109, 'இலங்கை அணி அபார வெற்றி – ரசிகர்கள் உற்சாகம்!', 'இன்றைய முக்கியமான போட்டியில் இலங்கை விளையாட்டு அணி சிறப்பான ஆட்டத்தை வெளிப்படுத்தி எதிரணியை தோற்கடித்தது. தொடக்கத்தில் சற்று அழுத்தம் இருந்தாலும், நடுப்பகுதியில் வீரர்களின் சிறந்த ஒத்துழைப்பு அணியை வெற்றிப்பாதைக்கு அழைத்துச் சென்றது. பந்துவீச்சாளர்கள் கட்டுப்பாட்டுடன் விளையாட, துடுப்பாட்டத்தில் இளம் வீரர் ஒருவர் அதிரடி ஆட்டம் ஆடி ரசிகர்களின் பாராட்டைப் பெற்றார். இந்த வெற்றி அணியின் நம்பிக்கையை அதிகரித்து, வரவிருக்கும் போட்டிகளுக்கான உற்சாகத்தை மேலும் உயர்த்தியுள்ளது.', NULL, 'விளையாட்டு', '', 'published', NULL, '2025-12-23 13:01:35', '2025-12-23 13:01:35', '1766494895_Pollock_to_Hussey.jpg', '', '', '', '', '', 0),
(110, 'தமிழ் இசையில் புதிய தலைமுறையின் எழுச்சி', '<p data-start=\"110\" data-end=\"609\">தமிழ் இசைத்துறையில் இளம் கலைஞர்களின் பங்களிப்பு நாளுக்கு நாள் அதிகரித்து வருகிறது. பாரம்பரிய கர்நாடக இசையையும், நவீன இசை வடிவங்களையும் இணைக்கும் முயற்சிகள் ரசிகர்களிடையே பெரும் வரவேற்பைப் பெற்றுள்ளன. சமீப காலமாக வெளியிடப்படும் இசை ஆல்பங்கள் மற்றும் மேடை நிகழ்ச்சிகள், தமிழ் கலாச்சாரத்தின் ஆழத்தையும் இசையின் புதுமையையும் ஒரே நேரத்தில் வெளிப்படுத்துகின்றன. இதன் மூலம் தமிழ் இசை உலகளாவிய அளவில் கவனம் பெறுவதுடன், எதிர்கால தலைமுறையினருக்கும் இசை மீது ஆர்வத்தை ஏற்படுத்தும் முக்கிய பங்கையும் வகிக்கிறது.</p>', NULL, 'கலைகள்', 'இசை', 'published', NULL, '2025-12-24 04:48:59', '2025-12-24 04:48:59', '1766551739_4.jpg', '', '', '', '', '', 0),
(111, 'அஇஉ்இஉ் இஉ்இஉ்இஉ் எகபெப', 'உஎ்உ்எ உ்எ உ்எஉ்எ உ்எ உ்எஉ்எ உ்எஉ ்்்்்எஉ்எஉ்உ', NULL, 'உலக செய்திகள்', '', 'published', NULL, '2025-12-24 05:33:55', '2025-12-24 05:33:55', '1766554435_pexels-anjana-c-169994-674010.jpg', '1766554435_WhatsApp Video 2025-12-03 at 12.53.29 PM.mp4', '', '', '', '', 0),
(112, 'தந்தையின் தாலாட்டு..', '<p style=\"margin-bottom: 25px; line-height: 28px; color: rgb(35, 35, 35); font-family: Arimo, sans-serif; font-size: 15px;\"><b>பூப்போன்ற மகளே<br>பொன்வண்ணச் சிலையே<br>தேன்போல இனிக்கும்<br>தெம்மாங்கு பாட்டே</b></p><p style=\"margin-bottom: 25px; line-height: 28px; color: rgb(35, 35, 35); font-family: Arimo, sans-serif; font-size: 15px;\"><b>விழியோரம் நான் கண்ட கனவு – அதன்<br>விடையாக நீ வந்த வரவு.<br>மொழியெல்லாம் தமிழ்போல இனிப்பு – எந்தன்<br>வழியெல்லாம் நிறையும் உன் நினைப்பு.</b></p><p style=\"margin-bottom: 25px; line-height: 28px; color: rgb(35, 35, 35); font-family: Arimo, sans-serif; font-size: 15px;\"><b>கண்ணே நீ கண்மூடி தூங்கு – எந்தன்<br>தோள்மீது தலை சாய்த்து தூங்கு<br>தாயாக எனை மாற்றும் பெண்ணே – எந்தன்<br>தாயாக உனை காப்பேன் கண்ணே..</b></p>', NULL, 'கவிதைகள்', '', 'published', '2025-12-24 09:47:17', '2025-12-24 08:47:17', '2025-12-24 08:47:17', '1766566037_images (1).jpg', '', '', '', '', '', 0),
(113, 'தமிழ்நாட்டில் புதிய தொழில்நுட்ப மையம் திறப்பு: ஆயிரக்கணக்கான வேலைவாய்ப்புகள் உருவாக்கப்படும்', '<div class=\"text-lg leading-relaxed text-foreground/90 mb-6 clearfix\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); margin-bottom: 1.5rem; font-size: 1.125rem; line-height: 1.625; color: rgba(249, 250, 251, 0.9); font-family: &quot;Noto Sans Tamil&quot;, Inter, system-ui, -apple-system, sans-serif; background-color: rgb(10, 10, 10);\"><p class=\"mb-5\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); margin-bottom: 1.25rem;\">தமிழ்நாடு அரசு புதிய தொழில்நுட்ப மையத்தை சென்னையில் திறக்கவுள்ளது. இந்த மையம் செயற்கை நுண்ணறிவு, இயந்திர கற்றல் மற்றும் தரவு அறிவியல் துறைகளில் கவனம் செலுத்தும். இது இந்தியாவின் மிகப்பெரிய தொழில்நுட்ப மையங்களில் ஒன்றாக உருவாகும் என எதிர்பார்க்கப்படுகிறது.</p><p class=\"mb-5\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); margin-bottom: 1.25rem;\">இந்த திட்டத்தின் மூலம் ஆயிரக்கணக்கான இளைஞர்களுக்கு வேலைவாய்ப்புகள் உருவாக்கப்படும் என அரசு தெரிவித்துள்ளது. குறிப்பாக கிராமப்புற பகுதிகளில் இருந்து வரும் மாணவர்களுக்கு முன்னுரிமை அளிக்கப்படும்.</p><p class=\"mb-5\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); margin-bottom: 1.25rem;\">தொழில்நுட்ப மையத்தில் நவீன ஆய்வுக்கூடங்கள், கணினி வசதிகள் மற்றும் பயிற்சி மையங்கள் அமைக்கப்படும். உலகின் முன்னணி தொழில்நுட்ப நிறுவனங்களுடன் இணைந்து பணியாற்றும் வாய்ப்புகளும் உருவாக்கப்படும்.</p></div><div class=\"clear-both\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); clear: both; color: rgb(249, 250, 251); font-family: &quot;Noto Sans Tamil&quot;, Inter, system-ui, -apple-system, sans-serif; font-size: medium; background-color: rgb(10, 10, 10);\"></div><div class=\"text-lg leading-relaxed text-foreground/90\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); font-size: 1.125rem; line-height: 1.625; color: rgba(249, 250, 251, 0.9); font-family: &quot;Noto Sans Tamil&quot;, Inter, system-ui, -apple-system, sans-serif; background-color: rgb(10, 10, 10);\"><p class=\"mb-5\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); margin-bottom: 1.25rem;\">இந்த மையம் சுற்றுச்சூழலுக்கு ஏற்ற வகையில் வடிவமைக்கப்பட்டுள்ளது. சூரிய ஆற்றல் மூலம் இயங்கும் இந்த கட்டிடம் பசுமை கட்டிட தரநிலைகளைப் பூர்த்தி செய்யும்.</p><p class=\"mb-5\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); margin-bottom: 1.25rem;\">மாணவர்களுக்கான புலமைப் பெயர்வு திட்டங்களும் அறிவிக்கப்பட்டுள்ளன. தகுதியான மாணவர்கள் வெளிநாட்டு பல்கலைக்கழகங்களில் படிக்கும் வாய்ப்பைப் பெறலாம்.</p><p class=\"mb-5\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); margin-bottom: 1.25rem;\">இந்த திட்டம் தமிழ்நாட்டின் தொழில்நுட்ப வளர்ச்சியில் ஒரு முக்கிய மைல்கல்லாக அமையும் என தொழில்துறை நிபுணர்கள் கருத்து தெரிவித்துள்ளனர். இது மாநிலத்தின் பொருளாதார வளர்ச்சிக்கும் பெரிதும் உதவும்.</p><div><br></div></div><div class=\"max-w-2xl mx-auto\" style=\"--tw-border-spacing-x: 0; --tw-border-spacing-y: 0; --tw-translate-x: 0; --tw-translate-y: 0; --tw-rotate: 0; --tw-skew-x: 0; --tw-skew-y: 0; --tw-scale-x: 1; --tw-scale-y: 1; --tw-pan-x: ; --tw-pan-y: ; --tw-pinch-zoom: ; --tw-scroll-snap-strictness: proximity; --tw-gradient-from-position: ; --tw-gradient-via-position: ; --tw-gradient-to-position: ; --tw-ordinal: ; --tw-slashed-zero: ; --tw-numeric-figure: ; --tw-numeric-spacing: ; --tw-numeric-fraction: ; --tw-ring-inset: ; --tw-ring-offset-width: 0px; --tw-ring-offset-color: #fff; --tw-ring-color: rgb(59 130 246 / 0.5); --tw-ring-offset-shadow: 0 0 #0000; --tw-ring-shadow: 0 0 #0000; --tw-shadow: 0 0 #0000; --tw-shadow-colored: 0 0 #0000; --tw-blur: ; --tw-brightness: ; --tw-contrast: ; --tw-grayscale: ; --tw-hue-rotate: ; --tw-invert: ; --tw-saturate: ; --tw-sepia: ; --tw-drop-shadow: ; --tw-backdrop-blur: ; --tw-backdrop-brightness: ; --tw-backdrop-contrast: ; --tw-backdrop-grayscale: ; --tw-backdrop-hue-rotate: ; --tw-backdrop-invert: ; --tw-backdrop-opacity: ; --tw-backdrop-saturate: ; --tw-backdrop-sepia: ; --tw-contain-size: ; --tw-contain-layout: ; --tw-contain-paint: ; --tw-contain-style: ; border-width: 0px; border-style: solid; border-color: rgba(255, 255, 255, 0.06); margin-left: auto; margin-right: auto; max-width: 42rem;\"></div>', NULL, 'அரசியல்', '', 'published', '2025-12-24 10:37:57', '2025-12-24 09:37:57', '2025-12-24 09:37:57', '1766569077_COPY.jpeg', '1766569077_WhatsApp Video 2025-12-03 at 12.53.29 PM.mp4', '', '', '', '', 0),
(114, 'அரபு நாடு­களின் சர்­வா­தி­கா­ரி­களைக் கொண்ட ஆட்சி அதி­கார சுவரால் பாது­காக்­கப்­படும் இஸ்ரேல்', '<p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">மத்­திய கிழக்கின் தற்­போ­தைய அர­சியல் சூழ்­நி­லையை விவரித்த டாக்டர் தமீமி “இஸ்ரேல் அரபு ஆட்­சி­யா­ளர்­ளைக்­கொண்ட பாது­காப்பு சுவரால் பாது­காக்­கப்­ப­டு­கின்­றது.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">அரபு மக்கள் ஒரு சிறையில் வசித்து வரு­கின்­றனர். அவர்­களின் சிறைச்­சா­லைகள் அவர்­களின் ஆட்­சி­யா­ளர்­க­ளாவர்.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">அந்த ஆட்­சி­யா­ளர்கள் தமது மக்­களின் அடிப்­படை உரி­மை­களை மறுக்­கின்­றனர். மேலும் அவர்­களின் செல்­வத்தைத் திருடி வேறு இடங்­க­ளுக்கு கடத்தி வரு­கின்­றனர்” எனத் தெரி­வித்­துள்ளார்.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">இந்த சோகம் ஒரு நூற்­றாண்­டுக்கும் மேலாக தொடர்ந்து வரு­கின்­றது. இது முஸ்லிம் மத்­திய கிழக்­கிற்கு எதி­ரான மேலைத்­தேச தீய வடி­வ­மைப்­பு­க­ளுக்கு உத­வு­கின்­றது.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">முஸ்லிம் மத்­திய கிழக்கு அறி­யா­மை­யு­டனும் வளர்ச்­சி­ய­டை­யா­மலும் தொடர்ந்து இருப்­பதை உறுதி செய்­வ­தற்கும், துருக்கிப் பேர­ரசு போன்ற ஒரு பலம்­மிக்க சக்­தி­யாக மீண்டும் தலை நிமி­ராமல் தடுப்­ப­தற்கும் உரிய ஒரு வழி­யாக இது இருந்­தது.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">அன்று முதற் கொண்டே இஸ்ரேல் அண்டை நாடு­க­ளுக்கு எதி­ராக ஏரா­ள­மான போர்­களைத் தொடுத்­துள்­ளது. மற்றும் எகிப்­திய, சிரிய, ஜோர்­டா­னிய மற்றும் லெப­னானின் பிர­தே­சங்­க­ளையும் அது கைப்­பற்றி உள்­ளது.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">அவற்றை இஸ்ரேல் அமெ­ரிக்­க –­ ஐ­ரோப்­பிய ஆத­ர­வுடன் சட்­ட­வி­ரோ­த­மாக தன்­னோடு இணைத்துக் கொண்­டது. இஸ்ரேல் இது­வரை 30 இற்கும் மேற்­பட்ட பலஸ்­தீ­னர்கள் மீதான மிகப்­பெ­ரிய அள­வி­லான இனப்­ப­டு­கொ­லை­களைப் புரிந்­துள்­ளது.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">அமெ­ரிக்கா மற்றும் ஐரோப்­பி­யர்­களின் ஆத­ர­வுடன் இந்த காட்­டு­மி­ராண்­டித்­த­னங்கள் அனைத்தும் கவ­னிக்­கப்­ப­டாமல் மூடி மறைக்­கப்­பட்டு விட்­டன. அதே நேரத்தில் அரபு சர்­வா­தி­கா­ரிகள் இந்த விட­யத்தில் ஒட்­டு­மொத்­த­மாக அலட்­சி­ய­மா­கவே இருந்­துள்­ளனர்.</p><p style=\"box-sizing: inherit; -webkit-font-smoothing: antialiased; margin-bottom: 23.1px; border: 0px; vertical-align: baseline; font-size: 15px; font-family: Poppins, system-ui, -apple-system, &quot;Segoe UI&quot;, Arial, sans-serif; word-break: break-word; overflow-wrap: break-word;\">இதற்­கி­டையில் 1979இல் ஈரானில் இடம்­பெற்ற இஸ்­லா­மியப் புரட்சி அங்கு ஆட்­சியில் இருந்த மேலைத்­தேச சார்பு பஹ்­லவி வம்­சத்தை 1979இல் கவிழ்த்­தது.</p>', NULL, 'அரசியல்,உலக செய்திகள்', '', 'published', '2025-12-26 11:08:40', '2025-12-26 10:08:40', '2025-12-26 10:08:40', '1766743720_25-68c9bb0eef3ce.webp', '', 'https://www.youtube.com/embed/9ciospNmcbk', '', '', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `news_images`
--

CREATE TABLE `news_images` (
  `id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `position` enum('top','center','bottom') DEFAULT 'center',
  `caption` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news_images`
--

INSERT INTO `news_images` (`id`, `news_id`, `image_path`, `position`, `caption`, `display_order`, `created_at`, `updated_at`) VALUES
(6, 106, 'uploads/news/positions/1766122327_6944e3578bab3_Screenshot (203).png', 'top', '', 0, '2025-12-19 05:32:07', '2025-12-19 05:32:07'),
(7, 106, 'uploads/news/positions/1766122327_6944e3578c6a0_Screenshot (172).png', 'center', '', 1, '2025-12-19 05:32:07', '2025-12-19 05:32:07'),
(8, 106, 'uploads/news/positions/1766122327_6944e3578f870_Screenshot (174).png', 'bottom', 'Hello world from kabitha', 2, '2025-12-19 05:32:07', '2025-12-19 05:32:07'),
(60, 108, 'uploads/news/positions/1766470295_694a3297734c2_Screenshot 2025-12-16 143541.png', 'center', '', 0, '2025-12-23 06:11:35', '2025-12-23 06:11:35'),
(61, 110, 'uploads/news/positions/1766551739_694b70bb97eba_2.jpg', 'top', '', 0, '2025-12-24 04:48:59', '2025-12-24 04:48:59'),
(62, 110, 'uploads/news/positions/1766551739_694b70bb9956e_3.jpg', 'center', '', 1, '2025-12-24 04:48:59', '2025-12-24 04:48:59'),
(63, 110, 'uploads/news/positions/1766551739_694b70bb9a911_images.jpg', 'bottom', '', 2, '2025-12-24 04:48:59', '2025-12-24 04:48:59'),
(64, 111, 'uploads/news/positions/1766554435_694b7b4325576_2.jpg', 'top', '', 0, '2025-12-24 05:33:55', '2025-12-24 05:33:55'),
(65, 111, 'uploads/news/positions/1766554435_694b7b4326993_3.jpg', 'center', '', 1, '2025-12-24 05:33:55', '2025-12-24 05:33:55'),
(66, 111, 'uploads/news/positions/1766554435_694b7b4327b35_4.jpg', 'bottom', '', 2, '2025-12-24 05:33:55', '2025-12-24 05:33:55'),
(67, 112, 'uploads/news/positions/1766566037_694ba895ec260_6.jpg', 'top', '', 0, '2025-12-24 08:47:17', '2025-12-24 08:47:17'),
(68, 113, 'uploads/news/positions/1766569077_694bb47523a05_images.jpg', 'top', '', 0, '2025-12-24 09:37:57', '2025-12-24 09:37:57'),
(69, 113, 'uploads/news/positions/1766569077_694bb47524c49_images (1).jpg', 'center', '', 1, '2025-12-24 09:37:57', '2025-12-24 09:37:57'),
(70, 113, 'uploads/news/positions/1766569077_694bb47526361_pexels-anjana-c-169994-674010.jpg', 'bottom', '', 2, '2025-12-24 09:37:57', '2025-12-24 09:37:57'),
(71, 114, 'uploads/news/positions/1766743720_694e5ea88cc7c_images (1).jpg', 'top', '', 0, '2025-12-26 10:08:40', '2025-12-26 10:08:40'),
(72, 114, 'uploads/news/positions/1766743720_694e5ea88e0d4_2.jpg', 'center', '', 1, '2025-12-26 10:08:40', '2025-12-26 10:08:40'),
(73, 114, 'uploads/news/positions/1766743720_694e5ea88f434_6.jpg', 'bottom', '', 2, '2025-12-26 10:08:40', '2025-12-26 10:08:40');

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscribers`
--

INSERT INTO `subscribers` (`id`, `email`, `created_at`) VALUES
(1, 'suya@gmail.com', '2025-12-20 06:00:38'),
(2, 'suya@gmail.com', '2025-12-20 06:03:00'),
(3, 'suya@gmail.com', '2025-12-20 06:05:19'),
(4, 'suya@gmail.com', '2025-12-20 06:10:21'),
(5, 'suya@gmail.com', '2025-12-20 06:11:23'),
(6, 'suya@gmail.com', '2025-12-20 06:11:58'),
(7, 'prithee@gmail.com', '2025-12-23 03:54:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `autosave_data`
--
ALTER TABLE `autosave_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `news_images`
--
ALTER TABLE `news_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_news_id` (`news_id`),
  ADD KEY `idx_position` (`position`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `autosave_data`
--
ALTER TABLE `autosave_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=578;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `news_images`
--
ALTER TABLE `news_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news_images`
--
ALTER TABLE `news_images`
  ADD CONSTRAINT `news_images_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `news_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `feedback_text` text NOT NULL,
  `rating` int(1) DEFAULT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
  `status` enum('pending','approved','rejected') DEFAULT 'approved',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for table `feedback`
--

ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_id` (`news_id`),
  ADD KEY `status` (`status`);

--
-- AUTO_INCREMENT for table `feedback`
--

ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Foreign key constraints for table `feedback`
--

ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_news_fk` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE;