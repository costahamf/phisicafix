<?php
$apiKey = "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";

$ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'HTTP-Referer: https://partner-yaedalavka.ru',
        'X-Title: Test'
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => json_encode([
        'model' => 'openrouter/free',
        'messages' => [['role' => 'user', 'content' => 'Скажи "работает"']],
        'max_tokens' => 10
    ]),
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP: $httpCode<br>";
echo "Ответ: " . htmlspecialchars($response);
?>