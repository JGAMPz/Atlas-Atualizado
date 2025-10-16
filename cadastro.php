<?php
session_start();
$tipo = $_GET['tipo'] ?? '';
$mensagem = '';
$tipo_mensagem = '';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - ATLAS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    :root {
        --azul-profundo: #1e3a8a;
        --azul-vibrante: #2563eb;
        --laranja-queimado: #ea580c;
        --laranja-vibrante: #f97316;
        --dourado-brilhante: #d97706;
        --dourado-suave: #f59e0b;
        --preto-elegante: #111827;
        --branco-puro: #ffffff;
        --cinza-suave: #f8fafc;
    }

    body {
        background:
            radial-gradient(circle at 20% 80%, rgba(37, 99, 235, 0.15) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(234, 88, 12, 0.15) 0%, transparent 50%),
            linear-gradient(135deg, var(--azul-profundo) 0%, var(--preto-elegante) 100%);
        font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
        min-height: 100vh;
        display: flex;
        align-items: center;
        position: relative;
        overflow-x: hidden;
    }

    .container-custom {
        max-width: 500px;
        width: 100%;
        margin: 0 auto;
        padding: 20px;
    }

    .card-profissional {
        background: var(--branco-puro);
        border: none;
        border-radius: 24px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        position: relative;
    }

    .card-profissional::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg,
                var(--azul-vibrante) 0%,
                var(--laranja-vibrante) 50%,
                var(--dourado-brilhante) 100%);
    }

    .card-header-profissional {
        background: linear-gradient(135deg, var(--azul-vibrante) 0%, var(--laranja-vibrante) 100%);
        color: white;
        text-align: center;
        padding: 3rem 2rem;
        border: none;
        position: relative;
    }

    .logo-profissional {
        font-weight: 800;
        font-size: 2.5rem;
        color: white;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }

    .icon-profissional {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 1.8rem;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .card-body-profissional {
        padding: 3rem 2.5rem;
        background: var(--branco-puro);
    }

    .badge-profissional {
        background: linear-gradient(135deg, var(--laranja-vibrante) 0%, var(--dourado-brilhante) 100%);
        color: white;
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 700;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-weight: 600;
        color: var(--preto-elegante);
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        display: block;
    }

    .form-control {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
        font-size: 15px;
        font-weight: 500;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: var(--branco-puro);
        width: 100%;
        color: var(--preto-elegante);
    }

    .form-control:focus {
        border-color: var(--laranja-vibrante);
        box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        background: var(--branco-puro);
    }

    .form-control::placeholder {
        color: #9ca3af;
        font-weight: 400;
    }

    .btn-primary-profissional {
        background: linear-gradient(135deg, var(--dourado-brilhante) 0%, var(--laranja-vibrante) 100%);
        border: none;
        color: white;
        font-weight: 700;
        padding: 16px 24px;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        width: 100%;
        position: relative;
        overflow: hidden;
    }

    .btn-primary-profissional:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(249, 115, 22, 0.4);
        color: white;
    }

    .btn-primary-profissional:active {
        transform: translateY(0);
    }

    .alert-profissional {
        border: none;
        border-radius: 12px;
        font-weight: 500;
        padding: 1rem 1.25rem;
        backdrop-filter: blur(10px);
    }

    .link-profissional {
        color: var(--azul-vibrante);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
    }

    .link-profissional:hover {
        color: var(--laranja-vibrante);
    }

    .floating-shapes {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: -1;
    }

    .shape {
        position: absolute;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(249, 115, 22, 0.1) 100%);
        animation: float 6s ease-in-out infinite;
    }

    .shape-1 {
        width: 120px;
        height: 120px;
        top: 10%;
        left: 5%;
        animation-delay: 0s;
    }

    .shape-2 {
        width: 80px;
        height: 80px;
        top: 70%;
        right: 8%;
        animation-delay: 2s;
    }

    .shape-3 {
        width: 60px;
        height: 60px;
        bottom: 20%;
        left: 15%;
        animation-delay: 4s;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px) rotate(0deg);
        }

        50% {
            transform: translateY(-20px) rotate(180deg);
        }
    }

    .row.gutter-custom {
        margin-left: -8px;
        margin-right: -8px;
    }

    .row.gutter-custom>[class*="col-"] {
        padding-left: 8px;
        padding-right: 8px;
    }

    @media (max-width: 576px) {
        .container-custom {
            padding: 15px;
        }

        .card-body-profissional {
            padding: 2rem 1.5rem;
        }

        .card-header-profissional {
            padding: 2.5rem 1.5rem;
        }

        .logo-profissional {
            font-size: 2.2rem;
        }
    }
    </style>
</head>

<body>
    <!-- Floating Shapes -->
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="container-custom">
        <div class="card-profissional">
            <!-- Header Profissional -->
            <div class="card-header-profissional">
                <div class="icon-profissional">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <h1 class="logo-profissional">ATLAS</h1>
                <p class="mb-0 opacity-90 fw-medium">Sua jornada fitness começa aqui</p>
            </div>

            <!-- Body Profissional -->
            <div class="card-body-profissional">
                <!-- Badge do Tipo -->
                <?php if($tipo): ?>
                <div class="text-center mb-4">
                    <span class="badge-profissional">
                        <i class="fas <?= $tipo == 'aluno' ? 'fa-user-graduate' : 'fa-dumbbell' ?>"></i>
                        <?= $tipo == 'aluno' ? 'Cadastro como Aluno' : 'Cadastro como Personal' ?>
                    </span>
                </div>
                <?php endif; ?>

                <!-- Mensagens -->
                <?php if ($mensagem): ?>
                <div class="alert alert-<?= $tipo_mensagem ?> alert-profissional alert-dismissible fade show mb-4"
                    role="alert">
                    <i
                        class="fas <?= $tipo_mensagem == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> me-2"></i>
                    <?= $mensagem ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Formulário Profissional -->
                <form method="POST" action="">
                    <!-- Tipo de Usuário -->
                    <div class="form-group">
                        <label for="tipo_usuario" class="form-label">Tipo de Usuário</label>
                        <select class="form-control" id="tipo_usuario" name="tipo_usuario" required>
                            <option value="">Selecione seu perfil...</option>
                            <option value="aluno" <?= $tipo == 'aluno' ? 'selected' : '' ?>>Aluno</option>
                            <option value="personal" <?= $tipo == 'personal' ? 'selected' : '' ?>>Personal Trainer
                            </option>
                        </select>
                    </div>

                    <!-- Nome e Data -->
                    <div class="row gutter-custom">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nome" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control" id="nome" name="nome"
                                    placeholder="Seu nome completo"
                                    value="<?= isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : '' ?>"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                                <input type="date" class="form-control" id="data_nascimento" name="data_nascimento"
                                    value="<?= isset($_POST['data_nascimento']) ? htmlspecialchars($_POST['data_nascimento']) : '' ?>"
                                    required>
                            </div>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="seu@email.com"
                            value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                    </div>

                    <!-- Telefone -->
                    <div class="form-group">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="tel" class="form-control" id="telefone" name="telefone"
                            placeholder="(11) 99999-9999"
                            value="<?= isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : '' ?>"
                            required>
                    </div>

                    <!-- Endereço -->
                    <div class="form-group">
                        <label for="endereco" class="form-label">Endereço</label>
                        <textarea class="form-control" id="endereco" name="endereco" rows="2"
                            placeholder="Digite seu endereço completo"><?= isset($_POST['endereco']) ? htmlspecialchars($_POST['endereco']) : '' ?></textarea>
                    </div>

                    <!-- Senhas -->
                    <div class="row gutter-custom">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="senha" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha"
                                    placeholder="Mínimo 6 caracteres" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha"
                                    placeholder="Digite novamente" required>
                            </div>
                        </div>
                    </div>

                    <!-- Botão -->
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary-profissional">
                            <i class="fas fa-user-plus me-2"></i> Criar Minha Conta
                        </button>
                    </div>
                </form>

                <!-- Link para Login -->
                <div class="text-center">
                    <p class="text-muted mb-0">
                        Já tem uma conta?
                        <a href="login.php" class="link-profissional">Faça login aqui</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js">
    </script>
</body>

</html>