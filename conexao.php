<?php
$databaseConfig = require __DIR__ . DIRECTORY_SEPARATOR . 'database_config.php';
$host = $databaseConfig['host'];
$dbname = $databaseConfig['dbname'];
$user = $databaseConfig['user'];
$pass = $databaseConfig['password'];
$port = $databaseConfig['port'];
$debug = $databaseConfig['debug'];

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    $error = ['status' => 'error', 'message' => 'Database connection failed.'];
    if ($debug) {
        $error['message'] = 'Database connection failed: ' . $e->getMessage();
        $error['debug'] = [
            'host' => $host,
            'dbname' => $dbname,
            'user' => $user,
            'port' => $port,
        ];
    }
    echo json_encode($error, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}