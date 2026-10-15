<?php
require_once 'auth.php';
require_login();
require_once 'conexao.php';

// We'll allow GET parameters for filtering
$categoria = $_GET['categoria'] ?? '';
$data_inicial = $_GET['data_inicial'] ?? '';
$data_final = $_GET['data_final'] ?? '';

 // Start building the query
$sql = "SELECT id, descricao, valor, data, categoria FROM transacoes WHERE usuario_id = ?";
$params = [(int) $_SESSION['usuario_id']];

if (!empty($categoria)) {
    $sql .= " AND categoria = ?";
    $params[] = $categoria;
}

if (!empty($data_inicial)) {
    // Validate date format (YYYY-MM-DD)
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_inicial)) {
        $sql .= " AND data >= ?";
        $params[] = $data_inicial;
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid initial date format. Use YYYY-MM-DD']);
        exit;
    }
}

if (!empty($data_final)) {
    // Validate date format (YYYY-MM-DD)
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_final)) {
        $sql .= " AND data <= ?";
        $params[] = $data_final;
    } else {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid final date format. Use YYYY-MM-DD']);
        exit;
    }
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    echo json_encode(['status' => 'ok', 'data' => $results]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}