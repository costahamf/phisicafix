<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/topics.php';
requireAuth();

$requestedTopic = $_GET['topic'] ?? '';
$topic = isset($topicMeta[$requestedTopic]) ? $requestedTopic : '';
$topicLabel = $topic ? $topicMeta[$topic]['title'] : 'Общий чат';

$startNew = isset($_GET['new']) && $_GET['new'] === '1';

if (
    $startNew
    || !isset($_SESSION['chat_session_id'])
    || !array_key_exists('chat_topic', $_SESSION)
    || $_SESSION['chat_topic'] !== $topic
) {
    $stmt = db()->prepare('INSERT INTO chat_sessions(user_id, topic) VALUES(:user_id, :topic)');
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':topic' => $topic ?: 'general',
    ]);
    $_SESSION['chat_session_id'] = (int) db()->lastInsertId();
    $_SESSION['chat_topic'] = $topic;
}

$chatSessionId = (int) $_SESSION['chat_session_id'];
$messageStmt = db()->prepare('SELECT role, content, created_at FROM chat_messages WHERE session_id = :sid ORDER BY id ASC');
$messageStmt->execute([':sid' => $chatSessionId]);
$messages = $messageStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $topic ? 'Чат: ' . htmlspecialchars($topicMeta[$topic]['title']) : 'Чат с AI-тьютором' ?></title>
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
            <a href="chat.php<?= $topic ? '?topic=' . urlencode($topic) . '&new=1' : '?new=1' ?>" class="btn-outline">Новый чат</a>
            <a href="logout.php" class="btn-outline">Выйти</a>
        </div>
    </div>
</header>

<main class="container chat-page chat-container" data-topic="<?= htmlspecialchars($topic) ?>" data-session-id="<?= $chatSessionId ?>">
    <section class="chat-head">
        <h1><?= $topic ? 'Чат: ' . htmlspecialchars($topicMeta[$topic]['title']) : 'Чат с AI-тьютором' ?></h1>
        <p><?= $topic ? 'Обсуждаем задачи и теорию по выбранному разделу.' : 'Общий чат без привязки к конкретному разделу.' ?></p>
    </section>

    <div id="dropZone" class="drop-zone">Перетащите изображение сюда или нажмите на скрепку для загрузки</div>

    <div id="messages" class="messages">
        <?php foreach ($messages as $message): ?>
            <article class="message <?= $message['role'] === 'assistant' ? 'assistant' : 'user' ?>">
                <div class="avatar"><?= $message['role'] === 'assistant' ? '🤖' : '👤' ?></div>
                <div class="message-bubble-wrap">
                    <p class="message-bubble"><?= nl2br(htmlspecialchars($message['content'])) ?></p>
                    <small><?= htmlspecialchars($message['created_at']) ?></small>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <section id="imagePreview" class="image-preview hidden">
        <img id="previewImage" alt="Предпросмотр выбранного изображения">
        <div class="image-preview-actions">
            <button id="sendImageBtn" type="button" class="btn-primary">Отправить</button>
            <button id="cancelImageBtn" type="button" class="btn-outline">Отмена</button>
        </div>
    </section>

    <form id="chatForm" class="chat-form">
        <label for="imageInput" class="attach-btn" title="Прикрепить изображение">
            <i class="fa-solid fa-paperclip"></i>
            <input type="file" id="imageInput" accept="image/png,image/webp,image/jpeg">
        </label>
        <textarea name="message" id="messageInput" rows="2" placeholder="Введите вопрос..."></textarea>
        <button type="submit" class="btn-primary">Отправить</button>
    </form>
</main>

<script src="js/chat.js"></script>
</body>
</html>
