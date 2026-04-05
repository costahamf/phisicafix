<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/topics.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Виртуальный помощник по физике</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWix+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkR4j8CG+VdL/7Xz9M4NQ5e0siJmq9hw3rBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-content">
        <a class="logo" href="index.php"><i class="fa-solid fa-atom"></i><span>Physics AI</span></a>
        <div class="user-block">
            <span class="user-pill"><i class="fa-regular fa-user"></i><?= htmlspecialchars($_SESSION['email']) ?></span>
            <a href="logout.php" class="btn-outline">Выйти</a>
        </div>
    </div>
</header>

<main>
    <section class="hero container">
        <div class="hero-content">
            <p class="hero-tag">AI-тьютор по физике</p>
            <h1>Виртуальный помощник по физике</h1>
            <p>Разбирай сложные темы с AI-тьютором. Решай задачи, загружай схемы, получай подсказки.</p>
            <div class="hero-actions">
                <a class="btn-primary" href="chat.php">Спросить у помощника</a>
                <a class="btn-outline" href="#sections">Выбрать раздел</a>
            </div>
        </div>
    </section>

    <section class="container section-cards" id="sections">
        <div class="section-heading">
            <h2>Разделы физики</h2>
            <p>Выберите тему и переходите к подробному материалу, примерам и тематическому чату.</p>
        </div>

        <div class="cards-grid">
            <?php $index = 0; foreach ($topicMeta as $slug => $topic): ?>
                <article class="topic-card reveal" style="--delay: <?= $index * 0.08 ?>s;">
                    <div class="topic-icon"><i class="fa-solid <?= htmlspecialchars($topic['icon']) ?>"></i></div>
                    <h3><?= htmlspecialchars($topic['title']) ?></h3>
                    <p><?= htmlspecialchars($topic['short']) ?></p>
                    <a class="btn-outline" href="section.php?topic=<?= urlencode($slug) ?>">Перейти</a>
                </article>
            <?php $index++; endforeach; ?>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container">
        <p>© <?= date('Y') ?> Physics AI Tutor. Все права защищены.</p>
    </div>
</footer>

<script>
    document.querySelectorAll('.reveal').forEach((card, i) => {
        card.style.animationDelay = card.style.getPropertyValue('--delay') || `${i * 0.08}s`;
        card.classList.add('is-visible');
    });
</script>
</body>
</html>
