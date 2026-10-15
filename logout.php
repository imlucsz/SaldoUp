<?php
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

$_SESSION = [];
session_destroy();
header('Location: login.php', true, 303);
exit;
