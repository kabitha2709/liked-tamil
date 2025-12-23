-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 23, 2025 at 10:15 AM
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
(301, 'chartheean', '', '2025-12-15 16:42:30'),
(302, '', '', '2025-12-15 18:46:32'),
(303, '', '', '2025-12-15 18:46:53'),
(304, '', '', '2025-12-16 09:35:41'),
(305, '', '', '2025-12-16 10:52:40'),
(306, '', '', '2025-12-16 10:53:46'),
(307, '', '', '2025-12-16 11:11:53'),
(308, '', '', '2025-12-16 11:30:05'),
(309, '', '', '2025-12-16 11:50:56'),
(310, '', '', '2025-12-16 11:52:16'),
(311, '', '', '2025-12-16 11:53:26'),
(312, '', '', '2025-12-16 11:53:45'),
(313, '', '', '2025-12-16 11:54:10'),
(314, '', '', '2025-12-16 11:55:11'),
(315, '', '', '2025-12-16 11:55:33'),
(316, '', '', '2025-12-16 11:55:48'),
(317, '', '', '2025-12-16 11:56:00'),
(318, '', '', '2025-12-16 11:56:13'),
(319, 'isixoazxollz', 'jzikolkolapzxaUJXUAz', '2025-12-16 11:57:13'),
(320, 'isixoazxollz', '\r\n                    jzikolkolapzxaUJXUAz                ', '2025-12-16 11:57:36'),
(321, '', '', '2025-12-16 12:04:30'),
(322, '', '', '2025-12-16 12:06:07'),
(323, '', '', '2025-12-16 12:48:32'),
(324, '', '', '2025-12-16 12:58:14'),
(325, '', '', '2025-12-16 12:58:40'),
(326, '', '', '2025-12-16 13:15:27'),
(327, '', '', '2025-12-16 13:16:10'),
(328, '', '', '2025-12-16 13:16:17'),
(329, '', '', '2025-12-16 13:20:33'),
(330, '', '', '2025-12-16 13:24:05'),
(331, '', '', '2025-12-16 13:24:10'),
(332, 'isixoazxollz', '', '2025-12-16 13:30:25'),
(333, 'isixoazxollz', '', '2025-12-16 13:31:00'),
(334, 'isixoazxollz', '\r\n                    jzikolkolapzxaUJXUAz                ', '2025-12-16 13:44:58'),
(335, 'isixoazxollz', '', '2025-12-16 13:58:00'),
(336, 'isixoazxollz', '', '2025-12-16 14:00:48'),
(337, 'isixoazxollz', '\r\n                    jzikolkolapzxaUJXUAz                ', '2025-12-16 14:13:16'),
(338, 'isixoazxollz', '\r\n                    jzikolkolapzxaUJXUAz                ', '2025-12-16 14:13:34'),
(339, '', '', '2025-12-16 14:17:49'),
(340, '', '', '2025-12-16 14:18:06'),
(341, '', '', '2025-12-16 14:18:20'),
(342, '', '', '2025-12-16 14:18:42'),
(343, '', '', '2025-12-16 14:19:23'),
(344, '', '', '2025-12-16 14:21:41'),
(345, '', '', '2025-12-16 14:22:55'),
(346, 'isixoazxollz', '\r\n                    jzikolkolapzxaUJXUAz                ', '2025-12-16 14:23:02'),
(347, 'isixoazxollz', '\r\n                    jzikolkolapzxaUJXUAz                ', '2025-12-16 14:23:49'),
(348, 'isixoazxollz', '\r\n                    jzikolkolapzxaUJXUAz                ', '2025-12-16 15:03:08'),
(349, 'isixoazxollz', '\r\n                    \r\n                    jzikolkolapzxaUJXUAz                                ', '2025-12-16 15:25:45'),
(350, '', '', '2025-12-16 15:34:12'),
(351, '', '', '2025-12-16 15:45:41'),
(352, '', '', '2025-12-17 09:20:23'),
(353, '', '', '2025-12-17 09:34:06'),
(354, '', '', '2025-12-17 09:35:23'),
(355, '', '', '2025-12-17 10:13:19'),
(356, '', '', '2025-12-17 10:13:41'),
(357, '', '', '2025-12-17 10:15:06'),
(358, '', '', '2025-12-17 10:15:19'),
(359, '', '', '2025-12-17 10:17:42'),
(360, '', '', '2025-12-17 10:17:58'),
(361, '', '', '2025-12-17 10:18:21'),
(362, '', '', '2025-12-17 10:37:00'),
(363, '', '', '2025-12-17 10:39:47'),
(364, '', '', '2025-12-17 10:39:56'),
(365, 'isixoazxollz', '\r\n                    \r\n                    jzikolkolapzxaUJXUAz                ', '2025-12-17 11:01:29'),
(366, '', '', '2025-12-17 11:02:51'),
(367, 'ஒரு அழகான தமிழ் கவிதை', '\r\n                    காற்றின் நிசப்தத்தில் மலர்கள் பேசும் பொழுதில்,<br data-start=\"115\" data-end=\"118\">\r\nஎன் மனம் உன்னை எண்ணி மெளனமாக மலர்கிறது.<br data-start=\"157\" data-end=\"160\">\r\nஅலைகள் கரை சேரும் ஆசைப் போல,<br data-start=\"188\" data-end=\"191\">\r\nஎன்னை உன்னிடம் இழுக்கும் ஒரு நெடிய உணர்வு.<br data-start=\"233\" data-end=\"236\">\r\nநிலவின் ஒளியிலும் நட்சத்திரத்தின் மெல்லிய மெலோதியிலும்,<br data-start=\"291\" data-end=\"294\">\r\nஉன் புன்னகை துளிர்க்கும் ஒரு கனவாய் நிற்கிறது.                ', '2025-12-17 11:03:04'),
(368, 'காற்றின் நிசப்தத்தில்', '<font size=\"7\"><span style=\"color: rgb(33, 37, 41); font-family: Arial, sans-serif;\">காற்றின் நிசப்தத்தில் மலர்கள் பேசும் பொழுதில்,</span><br data-start=\"115\" data-end=\"118\" style=\"color: rgb(33, 37, 41);\"><span style=\"color: rgb(33, 37, 41); font-family: Arial, sans-serif;\">என் மனம் உன்னை எண்ணி மெளனமாக மலர்கிறது.</span><br data-start=\"157\" data-end=\"160\" style=\"color: rgb(33, 37, 41);\"><span style=\"color: rgb(33, 37, 41); font-family: Arial, sans-serif;\">அலைகள் கரை சேரும் ஆசைப் போல,</span><br data-start=\"188\" data-end=\"191\" style=\"color: rgb(33, 37, 41);\"><span style=\"color: rgb(33, 37, 41); font-family: Arial, sans-serif;\">என்னை உன்னிடம் இழுக்கும் ஒரு நெடிய உணர்வு.</span><br data-start=\"233\" data-end=\"236\" style=\"color: rgb(33, 37, 41);\"><span style=\"color: rgb(33, 37, 41); font-family: Arial, sans-serif;\">நிலவின் ஒளியிலும் நட்சத்திரத்தின் மெல்லிய மெலோதியிலும்,</span><br data-start=\"291\" data-end=\"294\" style=\"color: rgb(33, 37, 41);\"><span style=\"color: rgb(33, 37, 41); font-family: Arial, sans-serif;\">உன் புன்னகை துளிர்க்கும் ஒரு கனவாய் நிற்கிறது.</span></font><div><span style=\"color: rgb(33, 37, 41); font-family: Arial, sans-serif;\"><font size=\"7\">hjjjjjாி</font></span></div>', '2025-12-17 11:13:43'),
(369, 'ujhujikjuio', 'ajkxskasoxizo', '2025-12-17 11:32:26'),
(370, 'ujhujikjuio', '\r\n                    ajkxskasoxizo                ', '2025-12-17 11:33:41'),
(371, '', '', '2025-12-17 11:52:09'),
(372, '', '', '2025-12-17 11:52:10'),
(373, 'ujhujikjuio', '\r\n                    ajkxskasoxizo                ', '2025-12-17 11:57:02'),
(374, 'ujhujikjuio', '\r\n                    ajkxskasoxizo                ', '2025-12-17 11:58:20'),
(375, 'ujhujikjuio', '\r\n                    ajkxskasoxizo                ', '2025-12-17 12:06:08'),
(376, '', '', '2025-12-17 12:07:10'),
(377, '', '', '2025-12-17 12:09:58'),
(378, 'கறபகறபகனமறபனம்பம்பரகறபகறப', 'hhhhப்பப்பப்பப்பன்றனமறப்பப்பப்பப்பபப்பப்பபகோறபறஅகௌஓறபஔஓஅகோஔலகஅறபறோஅபௌகோறபஓ<div>ஓபௌகபோஔம்பொமோஒடதமோடதொனமோஒடொ&nbsp;</div><div>பிபம்போஇபமினினுனமிபமிபினம்பனமோபிஔமோபௌமோஔபோம்பௌஓமிபஅ,பனிஅடினஅடனிடஅனிடடத.அமடஅ</div><div>மௌஓம்பௌபோம்பினமினுனிஉனிஉனிபுபிமோஔமரோரோஔரோ</div><div><br></div>', '2025-12-17 13:37:35'),
(379, '', 'ஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐ,<span style=\"font-family: Latha;\">ஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐ</span><div>ஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏ</div><div>ப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்ப</div><div>ம்மம்மம்மம்மம்மம்மம்மம</div><div>ர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர</div><div>க்கக்கக்கக்கக்கக்கக்கக்கக்கக்கக,<span style=\"font-family: Latha;\">ஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐ</span></div><div>ஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏ</div><div>ப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்ப</div><div>ம்மம்மம்மம்மம்மம்மம்மம</div><div>ர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர</div><div>க்கக்கக்கக்கக்கக்கக்கக்கக்கக்கக,<span style=\"font-family: Latha;\">ஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐ</span></div><div>ஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏ</div><div>ப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்ப</div><div>ம்மம்மம்மம்மம்மம்மம்மம</div><div>ர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர</div><div>க்கக்கக்கக்கக்கக்கக்கக்கக்கக்கக</div><div>ஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏ</div><div>ப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்ப</div><div>ம்மம்மம்மம்மம்மம்மம்மம</div><div>ர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர</div><div>க்கக்கக்கக்கக்கக்கக்கக்கக்கக்கக</div><div><span style=\"font-family: Latha;\">ஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐ</span><div>ஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏ</div><div>ப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்ப</div><div>ம்மம்மம்மம்மம்மம்மம்மம</div><div>ர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர</div><div>க்கக்கக்கக்கக்கக்கக்கக்கக்கக்கக</div></div><div><span style=\"font-family: Latha;\">ஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐ</span><div>ஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏ</div><div>ப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்ப</div><div>ம்மம்மம்மம்மம்மம்மம்மம</div><div>ர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர</div><div>க்கக்கக்கக்கக்கக்கக்கக்கக்கக்கக</div></div><div><span style=\"font-family: Latha;\">ஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐ</span><div>ஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏ</div><div>ப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்ப</div><div>ம்மம்மம்மம்மம்மம்மம்மம</div><div>ர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர</div><div>க்கக்கக்கக்கக்கக்கக்கக்கக்கக்கக</div></div><div><span style=\"font-family: Latha;\">ஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐஐ</span><div>ஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏஏ</div><div>ப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்பப்ப</div><div>ம்மம்மம்மம்மம்மம்மம்மம</div><div>ர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர்ரர</div><div>க்கக்கக்கக்கக்கக்கக்கக்கக்கக்கக</div></div>', '2025-12-17 13:39:40'),
(380, 'UHJUJIKJJKJUIK', 'd,jwasmadhmk,ahsm,uha,osumaHSAHD,YAWDGASNDGASYHDAZXGMSYHXUSHU', '2025-12-17 13:42:51'),
(381, 'UHJUJIKJJKJUIK', '\r\n                    d,jwasmadhmk,ahsm,uha,osumaHSAHD,YAWDGASNDGASYHDAZXGMSYHXUSHU                ', '2025-12-17 13:48:32'),
(382, '', '', '2025-12-17 14:08:10'),
(383, '', 'பிபுனபுனோ', '2025-12-17 14:12:36'),
(384, '', '', '2025-12-17 14:12:51'),
(385, '', '', '2025-12-17 14:22:58'),
(386, '', '', '2025-12-17 14:23:13'),
(387, '', '', '2025-12-17 14:31:00'),
(388, '', 'x xvcbvdffffffffffferrrrrrrrrrrrrrrrAAAAAAAzxzxzxzxzxzxzxzxzx&nbsp;', '2025-12-17 14:40:01'),
(389, '', '', '2025-12-17 14:59:43'),
(390, '', '', '2025-12-17 15:01:18'),
(391, 'kkkkkkkkk', 'sidxsoixkolzkxoljzujxisaux', '2025-12-17 15:01:49'),
(392, '', '', '2025-12-17 15:02:30'),
(393, '', '', '2025-12-17 15:31:22'),
(394, '', '', '2025-12-17 15:33:18'),
(395, '', '', '2025-12-17 15:34:59'),
(396, '', '', '2025-12-17 15:47:56'),
(397, '', '', '2025-12-17 16:03:28'),
(398, '', 'skasiakzxiokxxjjjjjjjjjjjjjjjjjjj', '2025-12-17 16:21:01'),
(399, 'xkjzkxj', 'zajkiolxkzl', '2025-12-17 16:24:47'),
(400, 'kabi', 'thaa', '2025-12-17 16:31:05'),
(401, 'kabi', '\r\n                    thaa                ', '2025-12-17 16:35:27'),
(402, '', '', '2025-12-17 16:51:19'),
(403, 'kaiii', 'thhhhahahahhaa', '2025-12-17 16:51:37'),
(404, 'kabi', '\r\n                    thaa                ', '2025-12-17 18:05:22'),
(405, '', '', '2025-12-17 18:12:36'),
(406, 'jiok', 'jjjjj', '2025-12-17 18:12:57'),
(407, 'loi', 'lolita', '2025-12-17 18:18:17'),
(408, 'lop', 'lolipop', '2025-12-17 18:24:11'),
(409, '', '', '2025-12-17 18:42:53'),
(410, 'politics', '\r\n                    <b><i>anura</i></b>                ', '2025-12-17 18:49:09'),
(411, '', '', '2025-12-17 18:56:45'),
(412, 'jiazjxkjazixoz', 'jjaozxkazxauzoxikazxjak', '2025-12-17 18:57:06'),
(413, '', '', '2025-12-17 19:39:30'),
(414, '', '', '2025-12-17 19:39:32'),
(415, 'jiazjxkjazixoz', '\r\n                    jjaozxkazxauzoxikazxjak                ', '2025-12-17 19:45:34'),
(416, 'jiazjxkjazixoz', '\r\n                    jjaozxkazxauzoxikazxjak                ', '2025-12-17 19:47:35'),
(417, 'jiazjxkjazixoz', '\r\n                    jjaozxkazxauzoxikazxjak                ', '2025-12-17 19:47:59'),
(418, '', '', '2025-12-17 19:56:30'),
(419, '', '', '2025-12-17 19:59:34'),
(420, '', '', '2025-12-17 20:00:27'),
(421, '', '', '2025-12-17 20:01:29'),
(422, '', '', '2025-12-17 20:06:26'),
(423, '', '', '2025-12-17 20:07:17'),
(424, '', '', '2025-12-17 20:08:04'),
(425, '', '', '2025-12-17 20:08:27'),
(426, '', '', '2025-12-17 20:08:50'),
(427, '', '', '2025-12-17 20:09:49'),
(428, '', '', '2025-12-17 20:11:38'),
(429, '', '', '2025-12-17 20:17:04'),
(430, 'jiazjxkjazixoz', 'jjaozxkazxauzoxikazxjak', '2025-12-17 20:23:51'),
(431, 'jiazjxkjazixoz', '\r\n                    jjaozxkazxauzoxikazxjak                ', '2025-12-17 20:23:58'),
(432, '', '', '2025-12-17 20:24:50'),
(433, 'vithu', 'san', '2025-12-18 09:31:25'),
(434, '', '', '2025-12-18 09:51:57'),
(435, 'vhjhjuhikujikjol', '', '2025-12-18 09:54:58'),
(436, 'i9okopkksedzasaerf', 'fyhuyhujhikujijkkkkkkkkkkkkkkkkkkkk', '2025-12-18 09:55:40'),
(437, '', '', '2025-12-18 10:20:58'),
(438, '', '', '2025-12-18 10:25:45'),
(439, 'i9okopkksedzasaerf', '\r\n                    fyhuyhujhikujijkkkkkkkkkkkkkkkkkkkk                ', '2025-12-18 10:25:54'),
(440, '', '', '2025-12-18 10:28:27'),
(441, 'kkkkk', 'kiru', '2025-12-18 10:30:04'),
(442, 'hello', 'hello ceylon', '2025-12-18 10:43:08'),
(443, '', '', '2025-12-18 11:08:50'),
(444, 'hello', 'wsjdhxsjjhxikjsnkxjsozxjos', '2025-12-18 11:09:51'),
(445, '', '', '2025-12-18 11:20:48'),
(446, 'world', 'hello world', '2025-12-18 11:21:21'),
(447, 'world', '\r\n                    hello world                ', '2025-12-18 11:24:06'),
(448, 'world', '\r\n                    hello world                ', '2025-12-18 11:38:19'),
(449, 'world', '\r\n                    hello world                ', '2025-12-18 11:39:13'),
(450, 'world', '\r\n                    hello world                ', '2025-12-18 11:40:55'),
(451, '', '', '2025-12-18 11:47:17'),
(452, '', '', '2025-12-18 11:51:07'),
(453, '', '', '2025-12-18 11:55:51'),
(454, '', '', '2025-12-18 11:58:34'),
(455, 'world', '\r\n                    hello world                ', '2025-12-18 12:02:56'),
(456, 'hello', '\r\n                    hello ceylon                ', '2025-12-18 12:20:15'),
(457, 'gathi', 'thaaaa', '2025-12-18 12:20:57'),
(458, '', '', '2025-12-18 12:30:27'),
(459, 'gathi', '\r\n                    thaaaa                ', '2025-12-18 12:33:24'),
(460, 'gathi', '\r\n                    thaaaa                ', '2025-12-18 12:43:26'),
(461, 'gathi', '\r\n                    thaaaa                ', '2025-12-18 12:43:37'),
(462, '', '', '2025-12-18 13:58:55'),
(463, 'gathi', '\r\n                    thaaaa                ', '2025-12-18 14:02:18'),
(464, 'gathi', '\r\n                    thaaaa                ', '2025-12-18 14:09:39'),
(465, 'gathi', '\r\n                    thaaaa                ', '2025-12-18 14:35:30'),
(466, 'gathi', '\r\n                    thaaaa                ', '2025-12-18 14:36:44'),
(467, 'gathi', '\r\n                    thaaaa                ', '2025-12-18 14:37:42'),
(468, 'gathi', '\r\n                    thaaaa                ', '2025-12-18 14:37:45'),
(469, '', '', '2025-12-18 14:40:32'),
(470, 'gathi', '\r\n                    thaaaazjmjkxzjikxjzikjnnnnnnnnnhhபபபபப<div>எஎஎஎக்கக்கனம்மம</div><div><br></div><div><br></div>', '2025-12-18 14:42:37'),
(471, 'gathi', '\r\n                    \r\n                    thaaaazjmjkxzjikxjzikjnnnnnnnnnhhபபபபப<div>எஎஎஎக்கக்கனம்மம</div><div><br></div><div><br></div>                ', '2025-12-18 15:06:26'),
(472, 'gathi', '\r\n                    \r\n                    thaaaazjmjkxzjikxjzikjnnnnnnnnnhhபபபபப<div>எஎஎஎக்கக்கனம்மம</div><div><br></div><div><br></div>                ', '2025-12-18 15:06:33'),
(473, 'hello', '\r\n                    hello ceylon                ', '2025-12-18 15:07:20'),
(474, 'hello', '\r\n                    wsjdhxsjjhxikjsnkxjsozxjos                ', '2025-12-18 15:10:18'),
(475, 'hello', '\r\n                    \r\n                    wsjdhxsjjhxikjsnkxjsozxjos                                ', '2025-12-18 15:14:11'),
(476, 'hello', '                    wsjdhxsjjhxikjsnkxjsozxjos                ', '2025-12-18 15:30:08'),
(477, '', '', '2025-12-18 15:44:42'),
(478, 'vithu', 'uu', '2025-12-18 15:45:25'),
(479, '', '', '2025-12-18 15:52:27'),
(480, 'sannnan', '', '2025-12-18 15:55:00'),
(481, 'hiu', 'jgh', '2025-12-18 15:55:43'),
(482, 'hiu', 'jgh', '2025-12-18 15:57:05'),
(483, 'hiu', 'jgh', '2025-12-18 16:07:14'),
(484, '', '', '2025-12-18 16:34:49'),
(485, '', '', '2025-12-18 16:46:16'),
(486, '', 'jjjjjjjjjjjjj<div>பபபப க்கக்கக</div>', '2025-12-19 09:52:33'),
(487, '', '<font face=\"Arial\" style=\"\" size=\"5\">sjuia,ujsxaxask</font><div><font face=\"Arial\" style=\"\" size=\"6\">hhujhikjik</font></div><div><font face=\"Koodal\" size=\"2\">க்கக்கக</font></div>', '2025-12-19 09:56:31'),
(488, 'kabi', '<font face=\"Koodal\"><i>\r\n                    kabi                </i></font><div style=\"font-family: Arial;\"><font size=\"6\"><b>இஉஇபமௌஓபமனௌ</b></font></div>', '2025-12-19 09:57:27'),
(489, '', '<b>எஏ்ளகெஏளறகறபகலனம்பபறன்றபன,</b><div>uuuuuuuuuu</div>', '2025-12-19 10:28:43'),
(490, 'nsusdjxikszx', '<ol><li><font size=\"4\"><b><i><u>shaumudjx,ajzxikazx</u></i></b></font></li></ol><div><font size=\"4\"><i style=\"\"><u style=\"\"><br></u></i></font></div><div><ul><li><font size=\"4\">sgyhazxujmakzjxkiaz</font></li></ul></div>', '2025-12-19 10:30:37'),
(491, 'hello', 'sdfsdf', '2025-12-19 10:35:43'),
(492, 'hello', 'hi', '2025-12-19 10:40:44'),
(493, '', '', '2025-12-19 10:45:17'),
(494, 'hsdfsf', 'sdfsdf', '2025-12-19 10:45:55'),
(495, 'hsdfsf', 'sdfsdf', '2025-12-19 10:48:35'),
(496, 'helloAbd', 'hi everyonesdfsdf', '2025-12-19 10:49:23'),
(497, '', '', '2025-12-19 10:54:10'),
(498, 'helloworld', 'hi', '2025-12-19 10:54:45'),
(499, 'Hello World now', 'sdbhfjhsdf sdfksdf', '2025-12-19 10:56:12'),
(500, 'Hello World now', 'sdbhfjhsdf sdfksdf', '2025-12-19 11:00:32'),
(501, 'asdasd', 'asdasdasd', '2025-12-19 11:02:07'),
(502, 'asdasd', 'asdasdasd', '2025-12-19 11:08:36'),
(503, 'asdasd', 'asdasdasd', '2025-12-19 11:08:45'),
(504, 'hellofromnow', 'sdfsdf', '2025-12-19 11:09:55'),
(505, 'hellofromnow', 'sdfsdf', '2025-12-19 11:11:02'),
(506, '', '', '2025-12-19 11:18:43'),
(507, 'asdasd', 'asdasdasd', '2025-12-19 11:49:09'),
(508, 'asdasd', 'asdasdasd', '2025-12-19 11:49:40'),
(509, 'hellofromnow', 'sdfsdf', '2025-12-19 11:49:51'),
(510, 'hellofromnow', 'sdfsdf', '2025-12-19 11:51:00'),
(511, 'hellofromnow', 'sdfsdf', '2025-12-19 11:51:06'),
(512, 'hellofromnow', 'sdfsdf', '2025-12-19 12:32:00'),
(513, 'hellofromnow', 'sdfsdf', '2025-12-19 16:48:27'),
(514, '', '', '2025-12-20 12:46:20'),
(515, 'Hello World now', 'sdbhfjhsdf sdfksdf', '2025-12-21 11:07:57'),
(516, 'Hello World now', 'sdbhfjhsdf sdfksdf', '2025-12-21 11:29:38'),
(517, 'Hello World now', 'sdbhfjhsdf sdfksdf', '2025-12-21 11:29:42'),
(518, 'Hello World now', 'sdbhfjhsdf sdfksdf', '2025-12-21 11:32:56'),
(519, 'disdosikoxospxosx', 'hxauzxoaoxjzaoxolz', '2025-12-21 11:33:37'),
(520, 'disdosikoxospxosx', 'hxauzxoaoxjzaoxolz', '2025-12-21 11:33:40'),
(521, 'disdosikoxospxosx', 'hxauzxoaoxjzaoxolz', '2025-12-21 11:33:44'),
(522, 'hdiudoisdcjdcod', 'askjokspxwsxlpkw', '2025-12-21 11:33:51'),
(523, 'hdiudoisdcjdcod', 'askjokspxwsxlpkw', '2025-12-21 11:33:53'),
(524, 'hdiudoisdcjdcod', 'askjokspxwsxlpkw', '2025-12-21 11:33:55'),
(525, 'jdiiiiiiiiiiiiiiiiiiiiiie', 'rjruiiiiiiiiiiiiiiiiiiiiiiii', '2025-12-21 11:34:02'),
(526, 'jdiiiiiiiiiiiiiiiiiiiiiie', 'rjruiiiiiiiiiiiiiiiiiiiiiiii', '2025-12-21 11:34:26'),
(527, 'hello', 'hi', '2025-12-21 11:34:35'),
(528, 'hello', 'hi', '2025-12-21 11:34:37'),
(529, 'hello', 'hi', '2025-12-21 11:34:38'),
(530, 'hello', 'hi', '2025-12-21 11:34:45'),
(531, 'hello', 'hi', '2025-12-21 11:34:47'),
(532, 'hello', 'hi', '2025-12-21 11:34:47'),
(533, 'hello', 'sdfsdf', '2025-12-21 11:35:00'),
(534, 'hello', 'sdfsdf', '2025-12-21 11:35:05'),
(535, 'hello', 'sdfsdf', '2025-12-21 11:35:07'),
(536, 'usyuasjahsuxjsa', 'js,asizossssssssssssssssssssskdxjasjxjsixucksnxhjujusi', '2025-12-21 11:35:21'),
(537, 'usyuasjahsuxjsa', 'js,asizossssssssssssssssssssskdxjasjxjsixucksnxhjujusi', '2025-12-21 11:35:41'),
(538, 'usyuasjahsuxjsa', 'js,asizossssssssssssssssssssskdxjasjxjsixucksnxhjujusi', '2025-12-21 11:35:42'),
(539, 'kabi', 'thaa', '2025-12-21 11:35:52'),
(540, 'kabi', 'thaa', '2025-12-21 11:35:54'),
(541, 'kabi', 'thaa', '2025-12-21 11:36:57'),
(542, 'kaaaaaaaaa', 'kaaaaaaaaaaa', '2025-12-21 11:37:24'),
(543, 'kaaaaaaaaa', 'kaaaaaaaaaaa', '2025-12-21 11:37:26'),
(544, 'kaaaaaaaaa', 'kaaaaaaaaaaa', '2025-12-21 11:37:27'),
(545, 'gsdyshudxsjuizx', 'zhuaiuziazoai0oz', '2025-12-21 11:37:36'),
(546, 'gsdyshudxsjuizx', 'zhuaiuziazoai0oz', '2025-12-21 11:37:38'),
(547, 'gsdyshudxsjuizx', 'zhuaiuziazoai0oz', '2025-12-21 11:37:39'),
(548, '', '', '2025-12-22 10:01:09'),
(549, '', '', '2025-12-22 10:41:02'),
(550, '', '', '2025-12-23 11:00:42'),
(551, 'மௌனத்தில் பேசும் மண்ணின் குரல்', 'மண்ணின் மார்பில் விதைத்த கனவுகள்,<br data-start=\"350\" data-end=\"353\">\r\nமழையுடன் சேர்ந்து உயிர் பெறுகின்றன.<br data-start=\"388\" data-end=\"391\">\r\nஉழைப்பின் வியர்வை துளிகள்,<br data-start=\"417\" data-end=\"420\">\r\nநாளைய நம்பிக்கையாக முளைக்கின்றன.<br data-start=\"452\" data-end=\"455\">\r\nசத்தமில்லா போராட்டமே<br data-start=\"475\" data-end=\"478\">\r\nஇந்த விவசாயியின் வாழ்க்கைச் செய்தி.', '2025-12-23 11:41:35'),
(552, '', '', '2025-12-23 14:10:03'),
(553, '', '', '2025-12-23 14:21:29'),
(554, 'மௌனத்தில் பேசும் மண்ணின் குரல்', 'மண்ணின் மார்பில் விதைத்த கனவுகள்,<br data-start=\"350\" data-end=\"353\">\r\nமழையுடன் சேர்ந்து உயிர் பெறுகின்றன.<br data-start=\"388\" data-end=\"391\">\r\nஉழைப்பின் வியர்வை துளிகள்,<br data-start=\"417\" data-end=\"420\">\r\nநாளைய நம்பிக்கையாக முளைக்கின்றன.<br data-start=\"452\" data-end=\"455\">\r\nசத்தமில்லா போராட்டமே<br data-start=\"475\" data-end=\"478\">\r\nஇந்த விவசாயியின் வாழ்க்கைச் செய்தி.', '2025-12-23 14:21:43'),
(555, '', '', '2025-12-23 14:22:37');

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
(51, 'கலைகள்', '[]', 'active', '2025-12-19 08:17:45', '2025-12-19 08:17:45', 'footer 1'),
(52, 'கவிதைகள்', '[]', 'active', '2025-12-23 04:24:19', '2025-12-23 04:24:19', ''),
(53, 'தொடர் கட்டுரைகள்', '[]', 'active', '2025-12-23 04:24:31', '2025-12-23 04:24:31', ''),
(54, 'புகைப்பட தொகுப்பு', '[]', 'active', '2025-12-23 04:24:43', '2025-12-23 04:24:43', ''),
(55, 'அந்தரங்கம்', '[]', 'active', '2025-12-23 04:24:52', '2025-12-23 04:24:52', ''),
(56, 'வினோதம்', '[]', 'active', '2025-12-23 04:25:00', '2025-12-23 04:25:00', ''),
(57, 'வீடியோ', '[]', 'active', '2025-12-23 04:25:09', '2025-12-23 04:25:09', ''),
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
(1, 39, 0, 'kabi', 'kabi@gmail.com', 'jxnhsznhxikjszkxmjzsox', 1, 'approved', '2025-12-21 05:50:15'),
(2, 39, 1, 'kabitha', 'reply@example.com', 'swjasjwajsnxiqwaoskqasz', 0, 'rejected', '2025-12-21 05:51:08'),
(3, 106, 0, 'vithu', 'vithu@gmail.com', 'sjxjskxjksxkwskxnsjnhxjmsiakmzabzujakxjsoco', 0, 'approved', '2025-12-22 04:51:18');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
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
(1, 'vagzxhajzxiajx', 'zjaziaxkal', NULL, NULL, NULL, '', NULL, '2025-11-18 07:41:58', '2025-11-18 07:41:58', NULL, NULL, NULL, NULL, NULL, NULL, 0),
(2, 'tywydisjksjoxskxs', 'asuamzaxkosxsxavxzvgaxaushjas', NULL, NULL, NULL, '', NULL, '2025-11-18 10:06:09', '2025-11-18 10:06:09', NULL, NULL, NULL, NULL, NULL, NULL, 0),
(3, 'fyuiiop[p', 'ywysjwjwixwodskdk', NULL, NULL, NULL, '', NULL, '2025-11-18 10:36:16', '2025-11-18 10:36:16', NULL, NULL, NULL, NULL, NULL, NULL, 0),
(4, 'fyuiiop[p', 'ywysjwjwixwodskdk', NULL, NULL, NULL, '', NULL, '2025-11-18 10:42:15', '2025-11-18 10:42:15', NULL, NULL, NULL, NULL, NULL, NULL, 0),
(6, 'udjdisxskx', 'shjijjjjjjjjjjjjskalxzmxmneidwjsod', NULL, NULL, NULL, '', NULL, '2025-11-18 10:43:17', '2025-11-18 10:43:17', NULL, NULL, NULL, NULL, NULL, NULL, 0),
(8, 'udjdisxskx', 'shjijjjjjjjjjjjjskalxzmxmneidwjsod', NULL, NULL, NULL, '', NULL, '2025-11-18 10:52:52', '2025-11-19 05:42:59', NULL, NULL, NULL, NULL, NULL, NULL, 0),
(10, 'dhjdjijsid', 'gsaysua8idujwidowidwoid', NULL, NULL, NULL, '', NULL, '2025-11-19 08:34:11', '2025-11-19 08:34:11', NULL, NULL, NULL, NULL, NULL, NULL, 0),
(11, 'dhjdjijsid', 'gsaysua8idujwidowidwoid', NULL, NULL, NULL, '', NULL, '2025-11-19 08:34:11', '2025-11-19 08:34:11', '1763541251_Screenshot (188).png', NULL, NULL, NULL, NULL, NULL, 0),
(12, 'hdiisodiosdo', 'rtfygyuhijkjisdowijsdskjxksxhnsjmx', NULL, NULL, NULL, '', NULL, '2025-11-19 08:51:17', '2025-11-19 08:51:17', '1763542277_Screenshot (183).png', NULL, NULL, NULL, NULL, NULL, 0),
(16, 'ssssssssss', 'wwqwfeffg', NULL, NULL, NULL, '', NULL, '2025-11-22 07:34:37', '2025-11-22 07:34:37', NULL, NULL, NULL, '', '', '', 0),
(18, 'uduisjixso', 'sjjaskasioaos', NULL, NULL, NULL, '', NULL, '2025-11-22 07:44:59', '2025-11-22 07:44:59', '', NULL, NULL, '', '', '', 0),
(25, 'hhhhhhhhhh', 'nkkkkkkkkkkkkkkkkkkkkk', NULL, NULL, NULL, '', NULL, '2025-11-22 08:04:35', '2025-11-22 08:04:35', '', NULL, NULL, '', '', '', 0),
(26, 'hdeiuuuuuu', 'diusdjsjdiidiwwdie', NULL, NULL, NULL, '', NULL, '2025-11-22 08:11:16', '2025-11-22 08:11:16', '', NULL, NULL, '', '', '', 0),
(29, 'cenima', 'kero fv&nbsp; jfki lasxklpowde cns rd&nbsp; nivr', NULL, NULL, NULL, '', NULL, '2025-11-25 05:14:40', '2025-11-25 05:20:05', NULL, NULL, NULL, '', '', '', 0),
(31, 'asduasduhasd', 'asydagysdgyuasdsdfsdfssd', NULL, NULL, NULL, '', NULL, '2025-11-25 05:48:29', '2025-11-25 08:56:28', NULL, NULL, NULL, '', '', '', 0),
(36, 'siajskaoksoaklz', 'azjaizkoai\\zkkkkkkkaiopx;aploxjauoxiwsixjasix', NULL, NULL, NULL, 'published', NULL, '2025-11-26 05:09:36', '2025-11-26 05:09:36', '', NULL, NULL, '', '', '', 0),
(37, 'disdosikoxospxosx', 'hxauzxoaoxjzaoxolz', NULL, '', '', 'published', NULL, '2025-11-26 05:57:35', '2025-12-21 06:03:37', '', '', NULL, '', '', '', 0),
(38, 'dhdiujsikdjjjjjjjjjjjjjjjjjjjj', 'hello world', NULL, NULL, NULL, 'draft', NULL, '2025-11-26 08:48:31', '2025-11-26 08:48:31', '', NULL, NULL, '', '', '', 0),
(39, 'heading', 'hello me', NULL, NULL, NULL, 'published', NULL, '2025-11-26 09:02:29', '2025-11-26 09:02:29', '', NULL, NULL, '', '', '', 0),
(40, 'hdiudoisdcjdcod', 'askjokspxwsxlpkw', NULL, '', '', 'published', NULL, '2025-11-26 09:44:04', '2025-12-21 06:03:51', '', '', NULL, '', '', '', 0),
(41, 'jdiiiiiiiiiiiiiiiiiiiiiie', 'rjruiiiiiiiiiiiiiiiiiiiiiiii', NULL, '', '', 'published', NULL, '2025-11-26 09:44:56', '2025-12-21 06:04:02', '', '', NULL, '', '', '', 0),
(44, 'duwsidisdddddosksk', 'xikxkzxosixoiskxj', NULL, NULL, NULL, 'draft', NULL, '2025-11-26 10:14:45', '2025-11-26 10:14:45', '', NULL, NULL, '', '', '', 0),
(46, 'j,xjsixosix', '<b>hello</b>', NULL, NULL, NULL, 'draft', NULL, '2025-11-27 05:51:54', '2025-11-27 05:51:54', '', NULL, NULL, '', '', '', 0),
(50, 'kabitha', 'kabitha', NULL, NULL, NULL, 'draft', NULL, '2025-11-27 08:40:52', '2025-11-27 08:40:52', '1764232852_pexels-anjana-c-169994-674010.jpg', NULL, NULL, '', '', '', 0),
(51, 'kabi', 'tha', NULL, NULL, NULL, 'draft', NULL, '2025-11-27 09:38:50', '2025-11-27 09:38:50', '', NULL, NULL, '', '', '', 0),
(52, 'ksklasasla', 'hqusiaUJSZAISZOASS', NULL, NULL, NULL, 'draft', NULL, '2025-11-27 09:47:53', '2025-11-27 09:47:53', '', NULL, NULL, '', '', '', 0),
(56, 'bbbbbbbbbb', 'hhhttrrrrrrrrrrrrrrrrrrrrrrrrrrrr bb', NULL, NULL, NULL, 'draft', NULL, '2025-11-27 11:01:04', '2025-11-27 11:01:04', '', NULL, NULL, '', '', '', 0),
(57, 'jiaskakos', 'dixjsidcos', NULL, '', '', 'draft', NULL, '2025-11-28 03:50:41', '2025-11-28 03:50:41', '', NULL, NULL, '', '', '', 0),
(58, 'hdsdujosidc', 'skjkolskxlsx', NULL, '', '', 'draft', NULL, '2025-11-29 14:40:02', '2025-11-29 14:40:02', '', NULL, NULL, '', '', '', 0),
(59, 'local', 'local', NULL, '', '', 'draft', NULL, '2025-12-01 08:22:18', '2025-12-01 08:22:18', '1764577338_Screenshot (172).png', NULL, NULL, '', '', '', 0),
(61, 'gsdyshudxsjuizx', 'zhuaiuziazoai0oz', NULL, '', '', 'published', NULL, '2025-12-01 09:32:16', '2025-12-21 06:07:36', '', '', NULL, '', '', '', 0),
(62, 'kkkkkkkkk', 'guhikjik', NULL, '', '', 'draft', NULL, '2025-12-01 09:41:22', '2025-12-01 09:41:22', '', NULL, NULL, '', '', '', 0),
(63, 'kaaaaaaaaa', 'kaaaaaaaaaaa', NULL, '', '', 'published', NULL, '2025-12-01 09:52:43', '2025-12-21 06:07:24', '', '', NULL, '', '', '', 0),
(64, 'asiaosa', 'djskdos', NULL, '', NULL, 'draft', NULL, '2025-12-01 10:13:09', '2025-12-01 10:13:09', '', NULL, NULL, '', '', '', 0),
(65, 'thana', 'huiuuihjh', NULL, '', NULL, 'draft', NULL, '2025-12-01 10:24:58', '2025-12-01 10:24:58', '', NULL, NULL, '', '', '', 0),
(67, 'nagarasha vithusan', 'the life is defusal parsial depandency in the carector&nbsp;', NULL, 'Foreign,ui,vithu', NULL, 'draft', NULL, '2025-12-01 17:15:10', '2025-12-01 17:15:10', '1764609310_7c2756fd-7a72-4fe9-b974-c5392b5a66b3.webp', NULL, NULL, '', '', '', 0),
(68, 'vithu karthi', '\r\n                    \r\n                    life is full damage&nbsp;                ', NULL, 'பிரிவின் பெயர்,Cinema,Health,பிரிவின் பெயர்,பிரிவின் பெயர் உட்பிரிவுகள்', '|பிரிவின் பெயர்', 'draft', NULL, '2025-12-01 17:22:23', '2025-12-12 08:58:56', '1764609743_download (3).jpg', '', NULL, '', '', '', 0),
(69, 'k.karthiga', 'krkfork&nbsp; rikir&nbsp; vithu&nbsp;', NULL, 'Health,sports,ui', 'uio', 'draft', NULL, '2025-12-01 17:26:50', '2025-12-01 17:26:50', '1764610010_WhatsApp Image 2025-07-15 at 09.18.28_1fb9cce6.jpg', NULL, NULL, '', '', '', 0),
(70, 'vithusan karthiga ', 'Vithusan and Karthiga had been inseparable for months, sharing laughter, dreams, and quiet moments together. But slowly, misunderstandings started creeping in, and small arguments turned into walls between them. One rainy evening, as they sat in silence, they realized that love alone wasn’t enough without understanding and patience. With heavy hearts, they decided to part ways, cherishing the beautiful memories they had created, and promising themselves to grow stronger from the experience, even if it meant walking separate paths.', NULL, 'sports,vithu,vithu', 'san,cricket', 'draft', NULL, '2025-12-01 17:38:29', '2025-12-01 17:38:29', '1764610709_aa45el1j.png', NULL, NULL, '', '', '', 0),
(72, 'jaaanu', '\r\n                    \r\n                    jaanu&nbsp;                                ', NULL, 'பிரிவின் பெயர்,sports,பிரிவின் பெயர் உட்பிரிவுகள்', '', 'published', NULL, '2025-12-12 08:25:46', '2025-12-12 09:36:10', '1765527946_pexels-anjana-c-169994-674010.jpg', '', NULL, '', '', '', 0),
(73, 'ஒரு அழகான தமிழ் கவிதை', 'காற்றின் நிசப்தத்தில் மலர்கள் பேசும் பொழுதில்,<br data-start=\"115\" data-end=\"118\">\r\nஎன் மனம் உன்னை எண்ணி மெளனமாக மலர்கிறது.<br data-start=\"157\" data-end=\"160\">\r\nஅலைகள் கரை சேரும் ஆசைப் போல,<br data-start=\"188\" data-end=\"191\">\r\nஎன்னை உன்னிடம் இழுக்கும் ஒரு நெடிய உணர்வு.<br data-start=\"233\" data-end=\"236\">\r\nநிலவின் ஒளியிலும் நட்சத்திரத்தின் மெல்லிய மெலோதியிலும்,<br data-start=\"291\" data-end=\"294\">\r\nஉன் புன்னகை துளிர்க்கும் ஒரு கனவாய் நிற்கிறது.', NULL, 'பிரிவின் பெயர்', 'cricket', 'published', NULL, '2025-12-12 10:19:59', '2025-12-12 10:19:59', '1765534799_Screenshot (172).png', '1765534799_WhatsApp Video 2025-12-03 at 12.53.29 PM.mp4', NULL, 'ஒரு அழகான தமிழ் கவிதை', 'காற்றின் நிசப்தத்தில் மலர்கள் பேசும் பொழுதில்,\r\nஎன் மனம் உன்னை எண்ணி மெளனமாக மலர்கிறது.\r\nஅலைகள் கரை சேரும் ஆசைப் போல,\r\nஎன்னை உன்னிடம் இழுக்கும் ஒரு நெடிய உணர்வு.\r\nந', '', 0),
(74, 'சோதனை மேற்கொள்ளப்பட்டதால் ', '\r\n                    இந்த வாரத்தில் தமிழ் எழுத்துரு ஆதரவு, வகை (category) புதுப்பிப்பு பிழைகள் மற்றும் மொபைல் responsive தொடர்பான சவால்கள் ஏற்பட்டன. அவற்றை தீர்க்க பக்க ஸ்கிரிப்ட்கள் திருத்தப்பட்டு, தரவுத்தள கையாளுதல் மேம்படுத்தப்பட்டது. மேலும் UI கூறுகள் சரிசெய்யப்பட்டு முறையான சோதனை மேற்கொள்ளப்பட்டதால் அமைப்பு செயல்திறன் மற்றும் பயனர் அனுபவம் மேம்பட்டது.                ', NULL, 'Cinema', 'londan', 'draft', NULL, '2025-12-15 05:29:03', '2025-12-15 05:33:03', '1765776543_Screenshot (205).png', '', NULL, '', '', '', 0),
(75, 'சோதனை மேற்கொள்ளப்பட்டதால் ', '\r\n                    \r\n                    இந்த வாரத்தில் தமிழ் எழுத்துரு ஆதரவு, வகை (category) புதுப்பிப்பு பிழைகள் மற்றும் மொபைல் responsive தொடர்பான சவால்கள் ஏற்பட்டன. அவற்றை தீர்க்க பக்க ஸ்கிரிப்ட்கள் திருத்தப்பட்டு, தரவுத்தள கையாளுதல் மேம்படுத்தப்பட்டது. UI கூறுகள் சரிசெய்யப்பட்டு முறையான சோதனை மேற்கொள்ளப்பட்டதால் அமைப்பு செயல்திறன் மற்றும் பயனர் அனுபவம் மேம்பட்டது.                                ', NULL, 'பிரிவின் பெயர்,பிரிவின் பெயர் உட்பிரிவுகள்', '', 'draft', NULL, '2025-12-15 05:33:41', '2025-12-15 06:12:09', '', '', NULL, '', '', '', 0),
(76, 'isixoazxollz', '\n                    jzikolkolap\\zxa\\UJXUA\\z', NULL, '', 'thaaaaaaaaa', 'draft', NULL, '2025-12-16 06:27:13', '2025-12-16 09:33:41', '', '', NULL, '', '', '', 0),
(77, 'ujhujikjuio', 'ajkxskasoxizo', NULL, '', '', 'draft', NULL, '2025-12-17 06:02:26', '2025-12-17 06:02:26', '', '', NULL, '', '', '', 0),
(78, 'UHJUJIKJJKJUIK', 'd,jwasmadhmk,ahsm,uha,osumaHSAHD,YAWDGASNDGASYHDAZXGMSYHXUSHU', NULL, '', '', 'draft', NULL, '2025-12-17 08:12:51', '2025-12-17 08:12:51', '', '', NULL, '', '', '', 0),
(79, 'kkkkkkkkk', 'sidxsoixkolzkxoljzujxisaux', NULL, '', '', 'draft', NULL, '2025-12-17 09:31:49', '2025-12-17 09:31:49', '', '', NULL, '', '', '', 0),
(80, 'usyuasjahsuxjsa', 'js,asizossssssssssssssssssssskdxjasjxjsixucksnxhjujusi', NULL, '', '', 'published', NULL, '2025-12-17 10:02:43', '2025-12-21 06:05:21', '', '', NULL, '', '', '', 0),
(81, 'iokj,kiiolllllllll', 'zkakkxlszmxlllllllllllllllllllllllllllllllllll\\', NULL, '', '', 'draft', NULL, '2025-12-17 10:18:47', '2025-12-17 10:18:47', '', '', NULL, '', '', '', 0),
(82, 'xkjzkxj', 'zajkiolxkzl', NULL, '', '', 'draft', NULL, '2025-12-17 10:54:47', '2025-12-17 10:54:47', '', '', NULL, '', '', '', 0),
(83, 'kabi', 'thaa', NULL, '', '', 'published', NULL, '2025-12-17 11:01:05', '2025-12-21 06:05:52', '', '', NULL, '', '', '', 0),
(84, 'kaiii', 'thhhhahahahhaa', NULL, '', '', 'draft', NULL, '2025-12-17 11:21:37', '2025-12-17 11:21:37', '', '', NULL, '', '', '', 0),
(85, 'jiok', 'jjjjj', NULL, '', '', 'draft', NULL, '2025-12-17 12:42:57', '2025-12-17 12:42:57', '', '', NULL, '', '', '', 0),
(86, 'loi', 'lolita', NULL, '', '', 'draft', NULL, '2025-12-17 12:48:17', '2025-12-17 12:48:17', '', '', NULL, '', '', '', 0),
(87, 'lop', 'lolipop', NULL, '', '', 'draft', NULL, '2025-12-17 12:54:11', '2025-12-17 12:54:11', '', '', NULL, '', '', '', 0),
(88, 'jiazjxkjazixoz', 'jjaozxkazxauzoxikazxjak', NULL, '', '', 'draft', NULL, '2025-12-17 13:27:06', '2025-12-17 13:27:06', '', '', NULL, '', '', '', 0),
(89, 'vithu', 'san', NULL, '', '', 'draft', NULL, '2025-12-18 04:01:25', '2025-12-18 04:01:25', '', '', NULL, '', '', '', 0),
(90, 'i9okopkksedzasaerf', 'fyhuyhujhikujijkkkkkkkkkkkkkkkkkkkk', NULL, '', '', 'draft', NULL, '2025-12-18 04:25:40', '2025-12-18 04:25:40', '', '', NULL, '', '', '', 0),
(91, 'kkkkk', 'kiru', NULL, '', '', 'draft', NULL, '2025-12-18 05:00:04', '2025-12-18 05:00:04', '', '', NULL, '', '', '', 0),
(92, 'hello', 'hello ceylon', NULL, 'chartheepan', '', 'draft', NULL, '2025-12-18 05:13:08', '2025-12-18 05:13:08', '1766034788_Screenshot 2025-12-16 143541.png', '', NULL, '', '', '', 0),
(93, 'hello', '\r\n                    wsjdhxsjjhxikjsnkxjsozxjos                ', NULL, '', '', 'draft', NULL, '2025-12-18 05:39:51', '2025-12-18 09:44:11', '', '', 'https://youtube.com/shorts/aUvmtcOQ3T8?si=ppA6dM45xObr1B7o', '', '', '', 0),
(94, 'world', 'hello world', NULL, '', '', 'draft', NULL, '2025-12-18 05:51:21', '2025-12-18 05:51:21', '', '', '', '', '', '', 0),
(95, 'gathi', '\r\n                    thaaaazjmjkxzjikxjzikjnnnnnnnnnhhபபபபப<div>எஎஎஎக்கக்கனம்மம</div><div><br></div><div><br></div>', NULL, '', '', 'draft', NULL, '2025-12-18 06:50:57', '2025-12-18 09:36:27', '', '', 'https://youtube.com/shorts/qieCcuwHbIo?si=ujy8FRnggN9UT8ES', '', '', '', 0),
(96, 'vithu', 'uu', NULL, 'Cinema,entertainment', '', 'draft', NULL, '2025-12-18 10:15:25', '2025-12-18 10:15:25', '', '', '', '', '', '', 0),
(97, 'hiu', 'jgh', NULL, '', 'பிரிவின் பெயர்', 'draft', NULL, '2025-12-18 10:25:43', '2025-12-18 10:25:43', '', '', '', '', '', '', 0),
(98, 'kabi', 'kabi', NULL, '', '', 'draft', NULL, '2025-12-18 12:39:20', '2025-12-18 12:39:20', '', '', '', '', '', '', 0),
(99, 'nsusdjxikszx', '<ol><li><font size=\"4\"><b><i><u>shaumudjx,ajzxikazx</u></i></b></font></li></ol><div><font size=\"4\"><i style=\"\"><u style=\"\"><br></u></i></font></div><div><ul><li><font size=\"4\">sgyhazxujmakzjxkiaz</font></li></ul></div>', NULL, '', '', 'draft', NULL, '2025-12-19 05:00:37', '2025-12-19 05:00:37', '', '', '', '', '', '', 0),
(100, 'hello', 'sdfsdf', NULL, '', '', 'published', NULL, '2025-12-19 05:05:43', '2025-12-21 06:05:00', '1766120743_Screenshot (173).png', '', '', '', '', '', 0),
(101, 'hello', 'hi', NULL, '', '', 'published', NULL, '2025-12-19 05:10:44', '2025-12-21 06:04:45', '1766121044_Screenshot (168).png', '', '', '', '', '', 0),
(102, 'hsdfsf', 'sdfsdf', NULL, 'kabi', '', 'draft', NULL, '2025-12-19 05:15:55', '2025-12-19 05:15:55', '1766121355_Screenshot (168).png', '', '', '', '', '', 0),
(103, 'hello\\Abd', 'hi everyonesdfsdf', NULL, 'kabi', '', 'draft', NULL, '2025-12-19 05:19:23', '2025-12-19 05:19:23', '', '', '', '', '', '', 0),
(104, 'helloworld', 'hi', NULL, 'kabi', '', 'draft', NULL, '2025-12-19 05:24:45', '2025-12-19 05:24:45', '1766121885_Screenshot (173).png', '', '', '', '', '', 0),
(105, 'Hello World now', 'sdbhfjhsdf sdfksdf', NULL, '', '', 'published', NULL, '2025-12-19 05:26:12', '2025-12-21 05:59:38', '', '', '', '', '', '', 0),
(106, 'asdasd', 'asdasdasd', NULL, 'Entertainment', 'cartoon', 'published', NULL, '2025-12-19 05:32:07', '2025-12-19 05:32:07', '1766122327_Screenshot (175).png', '', '', '', '', '', 0),
(107, 'hellofromnow', 'sdfsdf', NULL, '', '', 'draft', NULL, '2025-12-19 05:39:55', '2025-12-19 06:21:06', '1766122795_Screenshot (173).png', '', '', '', '', '', 0),
(108, 'மௌனத்தில் பேசும் மண்ணின் குரல்', 'மண்ணின் மார்பில் விதைத்த கனவுகள்,<br data-start=\"350\" data-end=\"353\">\nமழையுடன் சேர்ந்து உயிர் பெறுகின்றன.<br data-start=\"388\" data-end=\"391\">\nஉழைப்பின் வியர்வை துளிகள்,<br data-start=\"417\" data-end=\"420\">\nநாளைய நம்பிக்கையாக முளைக்கின்றன.<br data-start=\"452\" data-end=\"455\">\nசத்தமில்லா போராட்டமே<br data-start=\"475\" data-end=\"478\">\nஇந்த விவசாயியின் வாழ்க்கைச் செய்தி.', NULL, 'கவிதைகள்', '', 'published', NULL, '2025-12-23 06:11:35', '2025-12-23 09:11:13', '1766470295_Screenshot (172).png', '', '', '', '', '', 0);

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
(1, 104, 'uploads/news/positions/1766121885_6944e19d96cd3_Screenshot (175).png', 'top', '', 0, '2025-12-19 05:24:45', '2025-12-19 05:24:45'),
(2, 104, 'uploads/news/positions/1766121885_6944e19d9896f_Screenshot (174).png', 'center', '', 1, '2025-12-19 05:24:45', '2025-12-19 05:24:45'),
(6, 106, 'uploads/news/positions/1766122327_6944e3578bab3_Screenshot (203).png', 'top', '', 0, '2025-12-19 05:32:07', '2025-12-19 05:32:07'),
(7, 106, 'uploads/news/positions/1766122327_6944e3578c6a0_Screenshot (172).png', 'center', '', 1, '2025-12-19 05:32:07', '2025-12-19 05:32:07'),
(8, 106, 'uploads/news/positions/1766122327_6944e3578f870_Screenshot (174).png', 'bottom', 'Hello world from kabitha', 2, '2025-12-19 05:32:07', '2025-12-19 05:32:07'),
(43, 107, 'uploads/news/positions/1766122795_6944e52b34b3f_Screenshot (203).png', 'top', '', 0, '2025-12-19 06:21:06', '2025-12-19 06:21:06'),
(44, 107, 'uploads/news/positions/1766122795_6944e52b35d3b_Screenshot (204).png', 'top', '', 1, '2025-12-19 06:21:06', '2025-12-19 06:21:06'),
(45, 107, 'uploads/news/positions/1766122795_6944e52b3718e_Screenshot (205).png', 'top', '', 2, '2025-12-19 06:21:06', '2025-12-19 06:21:06'),
(46, 107, 'uploads/news/positions/1766122795_6944e52b3990f_Screenshot (206).png', 'top', '', 3, '2025-12-19 06:21:06', '2025-12-19 06:21:06'),
(47, 107, 'uploads/news/positions/1766122795_6944e52b46b99_Screenshot (207).png', 'top', '', 4, '2025-12-19 06:21:06', '2025-12-19 06:21:06'),
(48, 107, 'uploads/news/positions/1766122795_6944e52b4945a_Screenshot (206).png', 'center', '', 0, '2025-12-19 06:21:06', '2025-12-19 06:21:06'),
(49, 107, 'uploads/news/positions/1766122795_6944e52b4b594_Screenshot (207).png', 'center', '', 1, '2025-12-19 06:21:07', '2025-12-19 06:21:07'),
(50, 107, 'uploads/news/positions/1766122795_6944e52b4ccf2_Screenshot 2025-12-16 143541.png', 'center', '', 2, '2025-12-19 06:21:07', '2025-12-19 06:21:07'),
(51, 107, 'uploads/news/positions/1766122795_6944e52b4e540_Screenshot (206).png', 'bottom', '', 0, '2025-12-19 06:21:07', '2025-12-19 06:21:07'),
(52, 107, 'uploads/news/positions/1766122795_6944e52b4fd45_Screenshot (207).png', 'bottom', '', 1, '2025-12-19 06:21:07', '2025-12-19 06:21:07'),
(53, 107, 'uploads/news/positions/1766122795_6944e52b59082_Screenshot 2025-12-16 143541.png', 'bottom', '', 2, '2025-12-19 06:21:07', '2025-12-19 06:21:07'),
(57, 105, 'uploads/news/positions/1766121972_6944e1f4aa8f5_Screenshot (192).png', 'top', '', 0, '2025-12-21 05:59:38', '2025-12-21 05:59:38'),
(58, 105, 'uploads/news/positions/1766121972_6944e1f4abb71_Screenshot (173).png', 'center', '', 0, '2025-12-21 05:59:38', '2025-12-21 05:59:38'),
(59, 105, 'uploads/news/positions/1766121972_6944e1f4acc20_Screenshot (168).png', 'bottom', '', 0, '2025-12-21 05:59:38', '2025-12-21 05:59:38'),
(60, 108, 'uploads/news/positions/1766470295_694a3297734c2_Screenshot 2025-12-16 143541.png', 'center', '', 0, '2025-12-23 06:11:35', '2025-12-23 06:11:35');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=556;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `news_images`
--
ALTER TABLE `news_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

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
