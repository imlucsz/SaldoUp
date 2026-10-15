<?php
require_once 'auth.php';
require_login();
require_once 'conexao.php';

// Ensure we are receiving POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Get input data
$descricao = $_POST['descricao'] ?? '';
$valor = $_POST['valor'] ?? '';
$data = $_POST['data'] ?? '';
$categoria = $_POST['categoria'] ?? '';
$redirectAfterSave = ($_POST['redirect'] ?? '') === '1';
$categoriasPermitidas = [
    '💻 Tecnologia & Dev',
    '🍔 Alimentação',
    '🎉 Lazer & Rolês',
    '💊 Saúde & Suplementos',
    '📄 Contas & Fixas',
    '📌 Outros (padrão)',
];

// Basic validation
if (trim((string) $descricao) === '' || $valor === '' || $data === '' || trim((string) $categoria) === '') {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

if (!in_array($categoria, $categoriasPermitidas, true)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Categoria inválida']);
    exit;
}

// Validate valor is numeric
if (!is_numeric($valor)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Valor must be a number']);
    exit;
}

// Validate data format (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data must be in YYYY-MM-DD format']);
    exit;
}

try {
    // Prepare and execute the insert
    $stmt = $pdo->prepare("INSERT INTO transacoes (descricao, valor, data, categoria, usuario_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$descricao, $valor, $data, $categoria, (int) $_SESSION['usuario_id']]);

    if ($redirectAfterSave) {
        header('Location: index.php', true, 303);
        exit;
    }

    // Return success
    echo json_encode([
        'status' => 'ok',
        'message' => 'Transaction saved successfully',
        'id' => $pdo->lastInsertId()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}