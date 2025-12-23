<?php
include('../config/database.php');

$database = new Database();
$db = $database->getConnection();

$query = "SELECT id, title, image FROM news WHERE status='published' AND image IS NOT NULL AND image != '' ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();

$news = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add uploads/news/ path to images
foreach ($news as &$row) {
    if (!empty($row['image'])) {
        $row['image'] = 'uploads/news/' . htmlspecialchars($row['image']);
    } else {
        $row['image'] = 'https://picsum.photos/id/' . rand(1000, 1100) . '/800/500';
    }
}

echo json_encode($news);
?>

<!DOCTYPE html>
<html>
<head>
    <title>News</title>
    <style>
        .news-card {
            width: 300px;
            border: 1px solid #ccc;
            padding: 10px;
        }
        img {
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body>

<div id="news"></div>

<script>
fetch("http://localhost/WebBuilders/news_admin/api/get_news.php")
.then(res => res.json())
.then(data => {
    let html = "";
    data.forEach(item => {
        html += `
        <div class="news-card">
            <img src="${item.image}" alt="news image">
            <h3>${item.title}</h3>
        </div>
        `;
    });
    document.getElementById("news").innerHTML = html;
});
</script>

</body>
</html>
