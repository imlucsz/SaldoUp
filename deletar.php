<?php
require_once 'auth.php';
require_login();
require_once 'conexao.php';

// Keep the JSON POST endpoint while supporting the transaction links rendered by index.php.
$isGetRequest = $_SERVER['REQUEST_METHOD'] === 'GET';
if (!$isGetRequest && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$id = $isGetRequest ? ($_GET['id'] ?? null) : ($_POST['id'] ?? null);

// Validate id
if (!$id || !is_numeric($id) || intval($id) <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Valid transaction ID is required']);
    exit;
}

$id = intval($id);

try {
    // Prepare and execute the delete
    $stmt = $pdo->prepare("DELETE FROM transacoes WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, (int) $_SESSION['usuario_id']]);

    if ($isGetRequest) {
        header('Location: index.php' . ($stmt->rowCount() > 0 ? '?deleted=1' : '?error=' . rawurlencode('Transação não encontrada.')), true, 303);
        exit;
    }

    // Check if any row was affected
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'status' => 'ok',
            'message' => 'Transaction deleted successfully'
        ]);
    } else {
        http_response_code(404); // Not Found
        echo json_encode(['status' => 'error', 'message' => 'Transaction not found']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}