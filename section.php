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
$imageSlots = ['concept', 'laws', 'examples', 'tasks'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($current['title']) ?> — Physics AI</title>
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

<main class="container section-page">
    <nav class="breadcrumbs" aria-label="Хлебные крошки">
        <a href="index.php">Главная</a>
        <span>→</span>
        <span><?= htmlspecialchars($current['title']) ?></span>
    </nav>

    <section class="section-hero">
        <h1><i class="fa-solid <?= htmlspecialchars($current['icon']) ?>"></i> <?= htmlspecialchars($current['title']) ?></h1>
        <p><?= htmlspecialchars($current['description']) ?></p>
        <a class="btn-primary" href="chat.php?topic=<?= urlencode($topic) ?>">Задать вопрос по <?= htmlspecialchars($current['title']) ?></a>
    </section>

    <section class="subtopics-grid">
        <?php foreach ($current['cards'] as $index => $card): ?>
            <?php
                $slot = $imageSlots[$index] ?? 'concept';
                $basePath = "images/sections/{$topic}/{$slot}";
                $imagePath = '';
                if (file_exists(__DIR__ . "/{$basePath}.webp")) {
                    $imagePath = "{$basePath}.webp";
                } elseif (file_exists(__DIR__ . "/{$basePath}.png")) {
                    $imagePath = "{$basePath}.png";
                }
            ?>
            <article class="subtopic-card reveal-on-scroll">
                <div class="subtopic-image <?= $imagePath ? '' : 'placeholder' ?>">
                    <?php if ($imagePath): ?>
                        <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($card['title']) ?>">
                    <?php else: ?>
                        <div class="placeholder-content">
                            <i class="fa-regular fa-image"></i>
                            <span>Добавьте <?= htmlspecialchars($slot) ?>.webp или .png</span>
                        </div>
                    <?php endif; ?>
                </div>
                <h3><?= htmlspecialchars($card['title']) ?></h3>
                <p><?= htmlspecialchars($card['text']) ?></p>
            </article>
        <?php endforeach; ?>
    </section>
</main>

<script>
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    document.querySelectorAll('.reveal-on-scroll').forEach((card) => observer.observe(card));
</script>
</body>
</html>
