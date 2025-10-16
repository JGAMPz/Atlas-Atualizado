<?php
// redirect.php - Redirecionamento automático baseado no tipo de usuário
require_once 'includes/auth.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Redirecionar baseado no tipo de usuário
switch ($_SESSION['usuario_tipo']) {
    case 'admin':
        header('Location: dashboard/admin/index.php');
        break;
    case 'personal':
        header('Location: dashboard/personal/index.php');
        break;
    case 'aluno':
        header('Location: dashboard/aluno/index.php');
        break;
    default:
        header('Location: login.php');
}
exit;
?>