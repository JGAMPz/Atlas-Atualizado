<?php
// Iniciar sessão apenas uma vez no topo do arquivo
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Se já está logado, redirecionar
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard/' . $_SESSION['usuario_tipo'] . '/index.php');
    exit;
}

// Processar formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/config.php';
    require_once 'includes/functions.php';
    
    $result = processLogin($_POST);
    
    if ($result['success']) {
        // Se o login foi bem-sucedido, redirecionar
        header('Location: ' . $result['redirect']);
        exit;
    } else {
        $erro = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Academia Fit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="login-body">
    <div class="container">
        <div class="row justify-content-center min-vh-100 align-items-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-dumbbell fa-2x text-primary mb-3"></i>
                            <h3 class="card-title">Login</h3>
                            <p class="text-muted">Acesse sua conta</p>
                        </div>

                        <?php if (isset($erro)): ?>
                        <div class="alert alert-danger"><?php echo $erro; ?></div>
                        <?php endif; ?>

                        <form method="POST" id="loginForm">
                            <div class="mb-3">
                                <label for="tipo_usuario" class="form-label">Tipo de Usuário</label>
                                <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                                    <option value="">Selecione...</option>
                                    <option value="aluno">Aluno</option>
                                    <option value="personal">Personal Trainer</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">Entrar</button>

                            <div class="text-center">
                                <a href="recuperar-senha.php" class="text-decoration-none">Esqueci minha senha</a>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-0">Não tem uma conta? <a href="cadastro.php"
                                    class="text-decoration-none">Cadastre-se</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>