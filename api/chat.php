<?php
require_once __DIR__ . '/../includes/db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');
$sessionId = (int)($input['session_id'] ?? 0);

if (!$message || !$sessionId) {
    http_response_code(422);
    echo json_encode(['error' => 'Message and session_id required']);
    exit;
}

$db = db();

$stmt = $db->prepare('INSERT INTO chat_messages (session_id, role, content) VALUES (?, ?, ?)');
$stmt->execute([$sessionId, 'user', $message]);

$stmt = $db->prepare('SELECT role, content FROM chat_messages WHERE session_id = ? ORDER BY id ASC');
$stmt->execute([$sessionId]);
$history = $stmt->fetchAll();

$messages = [
    ['role' => 'system', 'content' => 'Ты — AI-тьютор по физике для школьников. Правила:
1. Никогда не давай готовое решение задачи. Вместо этого объясни физический смысл, спроси, что пользователь уже понял, дай наводящий вопрос, предложи следующий шаг.
2. НЕ используй Markdown-форматирование. НИКАКИХ звездочек (**) и НИКАКИХ решеток (#). Пиши обычным текстом.
3. Эмодзи использовать можно (💡 🔍 📝 ✅ ❌).
4. Отвечай на русском языке.']
];
foreach ($history as $row) {
    $messages[] = ['role' => $row['role'], 'content' => $row['content']];
}

$apiKey = "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";

$modelsToTry = [
    'openrouter/free',
    'meta-llama/llama-3.3-70b-instruct:free',
    'deepseek/deepseek-r1-0528:free'
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
            'X-Title: Physics Tutor'
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => json_encode([
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.5,
            'max_tokens' => 1000
        ]),
        CURLOPT_TIMEOUT => 60
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        $reply = $data['choices'][0]['message']['content'] ?? '';
        if (!empty($reply)) {
            break;
        }
    }
    $lastError = "HTTP $httpCode";
}

if (empty($reply)) {
    http_response_code(502);
    echo json_encode(['error' => "All models failed. Last error: $lastError"]);
    exit;
}

$stmt = $db->prepare('INSERT INTO chat_messages (session_id, role, content) VALUES (?, ?, ?)');
$stmt->execute([$sessionId, 'assistant', $reply]);

echo json_encode(['reply' => $reply, 'created_at' => date('Y-m-d H:i:s')], JSON_UNESCAPED_UNICODE);
?>