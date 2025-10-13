<?php
$usuario = getUsuarioInfo();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Dashboard - ATLAS'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-dumbbell"></i> ATLAS
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <?php if ($usuario['tipo'] == 'aluno'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../aluno/planos.php">Planos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../aluno/agenda.php">Agenda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../aluno/pagamento.php">Pagamentos</a>
                    </li>
                    <?php elseif ($usuario['tipo'] == 'personal'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../personal/agenda.php">Minha Agenda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../personal/alunos.php">Meus Alunos</a>
                    </li>
                    <?php elseif ($usuario['tipo'] == 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="planos.php">Gerenciar Planos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="usuarios.php">Usuários</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="relatorios.php">Relatórios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="gerenciar_usuarios.php">Gerenciar Usuários</a>
                    </li>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?php echo $usuario['nome']; ?>
                            <small class="text-muted">
                                (<?php 
                                if (isset($usuario['tipo'])) {
                                    switch($usuario['tipo']) {
                                        case 'admin': echo 'Administrador'; break;
                                        case 'personal': echo 'Personal Trainer'; break;
                                        case 'aluno': echo 'Aluno'; break;
                                        default: echo 'Usuário';
                                    }
                                }
                                ?>)
                            </small>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-user me-2"></i>Meu Perfil</a></li>
                            <li><a class="dropdown-item" href="configuracoes.php"><i class="fas fa-cog me-2"></i>Configurações</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Sair
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Conteúdo Principal -->
    <main class="flex-grow-1">
        <div class="container mt-4">