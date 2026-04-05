<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/topics.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$isMultipart = isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'multipart/form-data');
$input = $isMultipart ? $_POST : (json_decode(file_get_contents('php://input'), true) ?: []);

$message = trim((string)($input['message'] ?? ''));
$sessionId = (int)($input['session_id'] ?? 0);
$topic = (string)($input['topic'] ?? '');

$hasImage = isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
if ((!$message && !$hasImage) || !$sessionId) {
    http_response_code(422);
    echo json_encode(['error' => 'Нужно передать message или image и session_id']);
    exit;
}

$db = db();
$userContent = $message ?: '[Пользователь загрузил изображение без текста]';

$insertStmt = $db->prepare('INSERT INTO chat_messages (session_id, role, content) VALUES (?, ?, ?)');
$insertStmt->execute([$sessionId, 'user', $userContent]);

$historyStmt = $db->prepare('SELECT role, content FROM chat_messages WHERE session_id = ? ORDER BY id ASC');
$historyStmt->execute([$sessionId]);
$history = $historyStmt->fetchAll();

$topicPrompt = '';
if ($topic && isset($topicMeta[$topic])) {
    $topicPrompt = 'Сейчас обсуждается раздел: ' . $topicMeta[$topic]['title'] . '. Учитывай контекст этой темы.';
}

$systemPrompt = 'Ты — AI-тьютор по физике для школьников. Правила: '
    . '1) Никогда не давай полностью готовое решение задачи, направляй вопросами и шагами. '
    . '2) Не используй Markdown-звёздочки и решётки. '
    . '3) Отвечай на русском языке. '
    . ($topicPrompt ? '4) ' . $topicPrompt : '');

$imagePrompt = 'Ты AI-тьютор по физике. Пользователь загрузил изображение: схема, график или рисунок. '
    . 'Проанализируй его. Если это задача — не давай готовое решение, направляй. '
    . 'Если схема — объясни физический смысл. Отвечай на русском языке. '
    . 'Не используй Markdown-звёздочки, эмодзи можно.';

$messages = [
    ['role' => 'system', 'content' => $hasImage ? $imagePrompt : $systemPrompt]
];

foreach ($history as $idx => $row) {
    $isLastUserMessage = $hasImage && $row['role'] === 'user' && $idx === array_key_last($history);
    if ($isLastUserMessage) {
        continue;
    }
    $messages[] = [
        'role' => $row['role'],
        'content' => $row['content'],
    ];
}

$tempFilePath = null;

if ($hasImage) {
    $uploadDir = __DIR__ . '/../uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $extension = strtolower(pathinfo($_FILES['image']['name'] ?? 'image.png', PATHINFO_EXTENSION));
    $allowed = ['png' => 'image/png', 'webp' => 'image/webp', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg'];
    if (!isset($allowed[$extension])) {
        http_response_code(422);
        echo json_encode(['error' => 'Поддерживаются только PNG, WEBP и JPEG']);
        exit;
    }

    $fileName = sprintf('upload_%s_%s.%s', $sessionId, bin2hex(random_bytes(6)), $extension);
    $tempFilePath = $uploadDir . '/' . $fileName;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $tempFilePath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Не удалось сохранить изображение']);
        exit;
    }

    $mime = $allowed[$extension];
    $base64 = base64_encode((string)file_get_contents($tempFilePath));
    $dataUri = 'data:' . $mime . ';base64,' . $base64;

    $messages[] = [
        'role' => 'user',
        'content' => [
            [
                'type' => 'text',
                'text' => $message ?: 'Проанализируй изображение и помоги разобраться с физическим смыслом.',
            ],
            [
                'type' => 'image_url',
                'image_url' => ['url' => $dataUri],
            ],
        ],
    ];
} else {
    $messages[] = ['role' => 'user', 'content' => $message];
}

$apiKey = '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';
$modelsToTry = $hasImage
    ? [
        'google/gemini-2.0-flash-exp:free',
        'meta-llama/llama-4-scout-17b-16e-instruct:free',
    ]
    : [
        'openrouter/free',
        'meta-llama/llama-3.3-70b-instruct:free',
        'deepseek/deepseek-r1-0528:free',
    ];

$reply = null;
$lastError = null;

foreach ($modelsToTry as $model) {
    $ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'HTTP-Referer: https://partner-yaedalavka.ru',
            'X-Title: Physics Tutor',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => json_encode([
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.5,
            'max_tokens' => 1000,
        ], JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 90,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode((string)$response, true);
        $assistantMessage = $data['choices'][0]['message']['content'] ?? '';
        if (is_array($assistantMessage)) {
            $assistantMessage = implode("\n", array_map(static function ($part) {
                return is_array($part) ? ($part['text'] ?? '') : (string)$part;
            }, $assistantMessage));
        }
        $reply = trim((string)$assistantMessage);
        if ($reply !== '') {
            break;
        }
    }

    $lastError = "HTTP $httpCode";
}

if ($tempFilePath && file_exists($tempFilePath)) {
    unlink($tempFilePath);
}

if (empty($reply)) {
    http_response_code(502);
    echo json_encode(['error' => "All models failed. Last error: $lastError"]);
    exit;
}

$insertStmt->execute([$sessionId, 'assistant', $reply]);

echo json_encode([
    'reply' => $reply,
    'created_at' => date('Y-m-d H:i:s'),
], JSON_UNESCAPED_UNICODE);
