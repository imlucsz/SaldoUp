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

    if ($nome === '' || $senha === '') {
        $error = 'Informe seu usuário e sua senha.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id, nome, senha FROM usuarios WHERE nome = ? LIMIT 1');
            $stmt->execute([$nome]);
            $usuario = $stmt->fetch();
            $senhaArmazenada = $usuario ? (string) $usuario['senha'] : '';
            $senhaVerificada = $usuario && password_verify($senha, $senhaArmazenada);
            $senhaLegadaValida = $usuario && hash_equals($senhaArmazenada, $senha);

            if ($usuario && ($senhaVerificada || $senhaLegadaValida)) {
                if (!$senhaVerificada) {
                    $upgrade = $pdo->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
                    $upgrade->execute([password_hash($senha, PASSWORD_DEFAULT), $usuario['id']]);
                }
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = (int) $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                header('Location: index.php', true, 303);
                exit;
            }
            $error = 'Usuário ou senha incorretos.';
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            http_response_code(500);
            $error = 'Não foi possível validar o acesso. Tente novamente mais tarde.';
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
    <title>Entrar | SaldoUp</title>
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
        <h1>Acessar sua conta</h1>
        <p class="auth-description">Entre para acompanhar suas finanças.</p>

        <?php if ($error !== ''): ?>
            <p class="auth-error" role="alert"><?= $escape($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="nome">Usuário</label>
            <input type="text" id="nome" name="nome" value="<?= $escape($nome) ?>" autocomplete="username" maxlength="100" required>

            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" autocomplete="current-password" required>

            <button type="submit">Entrar</button>
        </form>
        <p class="auth-footer">Ainda não tem uma conta? <a href="cadastro.php">Criar conta</a></p>
    </main>
</body>
</html>
