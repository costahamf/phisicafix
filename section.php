<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/topics.php';
requireAuth();

$topic = $_GET['topic'] ?? '';
if (!isset($topicMeta[$topic])) {
    http_response_code(404);
    echo 'Раздел не найден';
    exit;
}
$current = $topicMeta[$topic];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($current['title']) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-content">
        <a href="index.php" class="brand-link">AI помощник</a>
        <div class="user-block">
            <span><?= htmlspecialchars($_SESSION['email']) ?></span>
            <a href="logout.php" class="btn btn-outline">Выйти</a>
        </div>
    </div>
</header>

<main class="container section-layout">
    <h1><?= htmlspecialchars($current['title']) ?></h1>
    <p class="section-description"><?= htmlspecialchars($current['description']) ?></p>
    <a class="btn" href="chat.php?topic=<?= urlencode($topic) ?>">Задать вопрос AI-тьютору</a>
</main>
</body>
</html>
