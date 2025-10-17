<?php
// Iniciar sessão se não estiver iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado
$usuario_logado = isset($_SESSION['usuario_id']);

// Buscar informações do usuário se estiver logado
if ($usuario_logado) {
    // Incluir functions.php se necessário
    if (!function_exists('getUsuarioInfo')) {
        require_once '../../includes/functions.php';
    }
    
    $usuario = getUsuarioInfo();
    
    // Buscar notificações
    if (!function_exists('getNotificacoes')) {
        require_once '../../includes/functions.php';
    }
    
    $notificacoes = getNotificacoes($_SESSION['usuario_id']);
    $notificacoes_nao_lidas = array_filter($notificacoes, function($n) {
        return !$n['lida'];
    });
    $total_notificacoes = count($notificacoes_nao_lidas);
} else {
    $usuario = ['nome' => 'Visitante', 'tipo' => 'visitante'];
    $notificacoes = [];
    $total_notificacoes = 0;
}

// Definir BASE_URL se não estiver definido
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])));
}

/**
 * Formata tempo decorrido (ex: "há 2 minutos") - FUNÇÃO LOCAL NO HEADER
 */
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'ano',
        'm' => 'mês',
        'w' => 'semana',
        'd' => 'dia',
        'h' => 'hora',
        'i' => 'minuto',
        's' => 'segundo',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? 'há ' . implode(', ', $string) : 'agora mesmo';
}
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
                    <?php if ($usuario_logado && $usuario['tipo'] == 'aluno'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../aluno/planos.php">Planos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../aluno/agenda.php">Agenda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../aluno/pagamento.php">Pagamentos</a>
                    </li>
                    <?php elseif ($usuario_logado && $usuario['tipo'] == 'personal'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../personal/agenda.php">Minha Agenda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../personal/alunos.php">Meus Alunos</a>
                    </li>
                    <?php elseif ($usuario_logado && $usuario['tipo'] == 'admin'): ?>
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
                    <!-- Sistema de Notificações -->
                    <?php if ($usuario_logado): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <?php if ($total_notificacoes > 0): ?>
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                style="font-size: 0.6em;">
                                <?php echo $total_notificacoes; ?>
                            </span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end"
                            style="width: 350px; max-height: 400px; overflow-y: auto;">
                            <li>
                                <h6 class="dropdown-header">Notificações</h6>
                            </li>

                            <?php if (empty($notificacoes)): ?>
                            <li><a class="dropdown-item text-muted" href="#">Nenhuma notificação</a></li>
                            <?php else: ?>
                            <?php foreach (array_slice($notificacoes, 0, 5) as $notificacao): ?>
                            <li>
                                <a class="dropdown-item <?php echo !$notificacao['lida'] ? 'bg-light' : ''; ?>"
                                    href="javascript:void(0)"
                                    onclick="marcarNotificacaoLida(<?php echo $notificacao['id']; ?>, this)">
                                    <div class="d-flex w-100 justify-content-between">
                                        <strong
                                            class="mb-1"><?php echo htmlspecialchars($notificacao['titulo']); ?></strong>
                                        <small
                                            class="text-muted"><?php echo time_elapsed_string($notificacao['data_criacao']); ?></small>
                                    </div>
                                    <p class="mb-1 small text-muted">
                                        <?php echo htmlspecialchars($notificacao['mensagem']); ?></p>
                                    <?php if ($notificacao['tipo'] == 'cancelamento'): ?>
                                    <small class="text-danger"><i
                                            class="fas fa-exclamation-circle me-1"></i>Cancelamento</small>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                            <?php endif; ?>

                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-center" href="../notificacoes.php">
                                    <i class="fas fa-list me-1"></i>Ver todas as notificações
                                </a></li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <!-- Menu do Usuário -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?php echo htmlspecialchars($usuario['nome']); ?>
                            <small class="text-muted">
                                (<?php 
                                if (isset($usuario['tipo'])) {
                                    switch($usuario['tipo']) {
                                        case 'admin': echo 'Administrador'; break;
                                        case 'personal': echo 'Personal Trainer'; break;
                                        case 'aluno': echo 'Aluno'; break;
                                        default: echo 'Visitante';
                                    }
                                }
                                ?>)
                            </small>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if ($usuario_logado): ?>
                            <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-user me-2"></i>Meu
                                    Perfil</a></li>
                            <li><a class="dropdown-item" href="configuracoes.php"><i
                                        class="fas fa-cog me-2"></i>Configurações</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <?php endif; ?>
                            <li>
                                <?php if ($usuario_logado): ?>
                                <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Sair
                                </a>
                                <?php else: ?>
                                <a class="dropdown-item text-success" href="<?php echo BASE_URL; ?>/login.php">
                                    <i class="fas fa-sign-in-alt me-2"></i>Entrar
                                </a>
                                <?php endif; ?>
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