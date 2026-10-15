<?php
require_once 'auth.php';

if (isset($_SESSION['usuario_id']) && (int) $_SESSION['usuario_id'] > 0) {
    header('Location: index.php', true, 303);
    exit;
}

require_once 'conexao.php';

$error = '';
$nome = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');
    $confirmacao = (string) ($_POST['confirmacao'] ?? '');

    if (mb_strlen($nome) < 3 || mb_strlen($nome) > 100) {
        $error = 'O usuário deve ter entre 3 e 100 caracteres.';
    } elseif (strlen($senha) < 6) {
        $error = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirmacao) {
        $error = 'As senhas não correspondem.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO usuarios (nome, senha) VALUES (?, ?)');
            $stmt->execute([$nome, password_hash($senha, PASSWORD_DEFAULT)]);

            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int) $pdo->lastInsertId();
            $_SESSION['usuario_nome'] = $nome;
            header('Location: index.php', true, 303);
            exit;
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $error = 'Esse nome de usuário já está em uso.';
            } else {
                error_log($exception->getMessage());
                http_response_code(500);
                $error = 'Não foi possível criar sua conta. Tente novamente mais tarde.';
            }
        }
    }
}

$escape = static function ($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="language" content="pt-BR">
    <title>Criar conta | SaldoUp</title>
    <link rel="shortcut icon" href="./assets/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="./styles/main.css">
    <link rel="stylesheet" href="./styles/futuristic.css?v=3">
    <link rel="stylesheet" href="./styles/fintech.css?v=2">
    <link rel="stylesheet" href="./styles/auth.css">
</head>
<body class="auth-page">
    <main class="auth-card">
        <a class="auth-logo" href="login.php" aria-label="SaldoUp">
            <img src="./assets/logo.svg?v=2" alt="SaldoUp">
        </a>
        <h1>Criar sua conta</h1>
        <p class="auth-description">Comece a organizar suas finanças pessoais.</p>

        <?php if ($error !== ''): ?>
            <p class="auth-error" role="alert"><?= $escape($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="cadastro.php">
            <label for="nome">Usuário</label>
            <input type="text" id="nome" name="nome" value="<?= $escape($nome) ?>" autocomplete="username" minlength="3" maxlength="100" required>

            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" autocomplete="new-password" minlength="6" required>

            <label for="confirmacao">Confirme a senha</label>
            <input type="password" id="confirmacao" name="confirmacao" autocomplete="new-password" minlength="6" required>

            <button type="submit">Criar conta</button>
        </form>

        <p class="auth-footer">Já tem uma conta? <a href="login.php">Entrar</a></p>
    </main>
</body>
</html>
