<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$topics = [
    'mechanics' => ['title' => 'Механика', 'icon' => '⚙️', 'description' => 'Движение, силы, законы Ньютона и энергия.'],
    'electricity' => ['title' => 'Электричество', 'icon' => '⚡', 'description' => 'Ток, напряжение, сопротивление и законы цепей.'],
    'magnetism' => ['title' => 'Магнетизм', 'icon' => '🧲', 'description' => 'Магнитные поля, индукция и электромагнитные явления.'],
    'optics' => ['title' => 'Оптика', 'icon' => '🔍', 'description' => 'Свет, линзы, зеркала и оптические приборы.'],
    'molecular_physics' => ['title' => 'Молекулярная физика', 'icon' => '🧪', 'description' => 'Строение вещества, температура и термодинамика.'],
    'quantum_physics' => ['title' => 'Квантовая физика', 'icon' => '🌀', 'description' => 'Кванты, фотоэффект и основы микромира.'],
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI-помощник по физике</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-content">
        <h1>AI помощник</h1>
        <div class="user-block">
            <span><?= htmlspecialchars($_SESSION['email']) ?></span>
            <a href="logout.php" class="btn btn-outline">Выйти</a>
        </div>
    </div>
</header>

<main class="container">
    <section class="hero">
        <h2>Виртуальный помощник по физике</h2>
        <p>Выберите раздел школьной программы и задайте вопрос AI-тьютору. Помощник направит вас к самостоятельному решению.</p>
    </section>

    <section class="cards-grid">
        <?php foreach ($topics as $slug => $topic): ?>
            <a class="topic-card" href="section.php?topic=<?= urlencode($slug) ?>">
                <div class="topic-icon"><?= $topic['icon'] ?></div>
                <h3><?= htmlspecialchars($topic['title']) ?></h3>
                <p><?= htmlspecialchars($topic['description']) ?></p>
            </a>
        <?php endforeach; ?>
    </section>
</main>
</body>
</html>
