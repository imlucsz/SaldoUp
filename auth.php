<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function require_login(): void
{
    if (!isset($_SESSION['usuario_id']) || (int) $_SESSION['usuario_id'] < 1) {
        header('Location: login.php', true, 303);
        exit;
    }
}
