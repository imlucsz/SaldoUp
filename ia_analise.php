<?php
require_once 'auth.php';
require_login();
require_once 'conexao.php';

// Get all transactions from the database
try {
    $stmt = $pdo->prepare("SELECT id, descricao, valor, data, categoria FROM transacoes WHERE usuario_id = ? ORDER BY data DESC");
    $stmt->execute([(int) $_SESSION['usuario_id']]);
    $transactions = $stmt->fetchAll();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'dica' => 'Erro ao buscar transações: ' . $e->getMessage()]);
    exit;
}

// If no transactions, we can still provide a generic tip
if (empty($transactions)) {
    $transactionsJson = '[]';
} else {
    $transactionsJson = json_encode($transactions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
}

// Prepare the prompt for Gemini API
$prompt = <<<PROMPT
Com base nas seguintes transações financeiras em JSON: {$transactionsJson}, forneça uma dica de finanças pessoais em português. Responda APENAS com um objeto JSON contendo os campos 'status' (com valor 'ok') e 'dica' (a dica em texto). Não adicione nenhum texto extra.
PROMPT;

// Get API key from environment
$apiKey = getenv('GEMINI_API_KEY');
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'dica' => 'API key do Gemini não configurada']);
    exit;
}

$model = getenv('GEMINI_MODEL') ?: 'gemini-3.1-flash-lite';
if (!preg_match('/^[A-Za-z0-9._-]+$/', $model)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'dica' => 'GEMINI_MODEL possui um nome inválido']);
    exit;
}

$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent';

// Prepare request body
$data = [
    'contents' => [
        [
            'parts' => [
                [
                    'text' => $prompt
                ]
            ]
        ]
    ]
];

$jsonData = json_encode($data);

// Initialize cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'x-goog-api-key: ' . $apiKey,
]);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Check for cURL errors
if ($response === false) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'dica' => 'Erro na comunicação com a API do Gemini: ' . $curlError]);
    exit;
}

// Check HTTP status
if ($httpCode !== 200) {
    http_response_code(500);
    $errorData = json_decode($response, true);
    $apiError = $errorData['error']['message'] ?? 'Sem detalhes fornecidos pela API.';
    echo json_encode([
        'status' => 'error',
        'dica' => 'API do Gemini retornou erro HTTP ' . $httpCode . ': ' . substr($apiError, 0, 500),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Decode the response
$result = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'dica' => 'Resposta inválida da API do Gemini']);
    exit;
}

// Extract the text from the response
// According to Gemini API structure: candidates[0].content.parts[0].text
if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'dica' => 'Formato inesperado na resposta da API do Gemini']);
    exit;
}

$text = trim($result['candidates'][0]['content']['parts'][0]['text']);

// Try to decode the text as JSON (since we asked for JSON)
$responseData = json_decode($text, true);
if (json_last_error() === JSON_ERROR_NONE && isset($responseData['status']) && isset($responseData['dica'])) {
    // If it's valid JSON and has the expected fields, return it
    echo json_encode($responseData);
} else {
    // If the API didn't return JSON as expected, we'll wrap the text in our expected format
    // But note: we asked for JSON, so if it's not JSON, we treat as error? Or we can extract a tip.
    // For simplicity, we'll return the text as the dica and assume status ok.
    echo json_encode([
        'status' => 'ok',
        'dica' => $text
    ]);
}