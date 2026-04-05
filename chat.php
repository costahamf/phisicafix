<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/topics.php';
requireAuth();

$topic = $_GET['topic'] ?? 'mechanics';
if (!isset($topicMeta[$topic])) {
    $topic = 'mechanics';
}

$startNew = isset($_GET['new']) && $_GET['new'] === '1';

if ($startNew || !isset($_SESSION['chat_session_id']) || !isset($_SESSION['chat_topic']) || $_SESSION['chat_topic'] !== $topic) {
    $stmt = db()->prepare('INSERT INTO chat_sessions(user_id, topic) VALUES(:user_id, :topic)');
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':topic' => $topic,
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
    <title>Чат: <?= htmlspecialchars($topicMeta[$topic]['title']) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-content">
        <a href="section.php?topic=<?= urlencode($topic) ?>" class="brand-link">← <?= htmlspecialchars($topicMeta[$topic]['title']) ?></a>
        <div class="user-block">
            <a href="chat.php?topic=<?= urlencode($topic) ?>&new=1" class="btn btn-outline">Новый чат</a>
            <a href="logout.php" class="btn btn-outline">Выйти</a>
        </div>
    </div>
</header>

<main class="container chat-container" data-topic="<?= htmlspecialchars($topic) ?>" data-session-id="<?= $chatSessionId ?>">
    <div id="messages" class="messages">
        <?php foreach ($messages as $message): ?>
            <article class="message <?= $message['role'] === 'assistant' ? 'assistant' : 'user' ?>">
                <div class="avatar"><?= $message['role'] === 'assistant' ? '🤖' : '👤' ?></div>
                <div>
                    <p><?= nl2br(htmlspecialchars($message['content'])) ?></p>
                    <small><?= htmlspecialchars($message['created_at']) ?></small>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <form id="chatForm" class="chat-form">
        <textarea name="message" id="messageInput" rows="3" placeholder="Введите вопрос по теме..." required></textarea>
        <button type="submit" class="btn">Отправить</button>
    </form>
</main>

<script src="js/chat.js"></script>
</body>
</html>