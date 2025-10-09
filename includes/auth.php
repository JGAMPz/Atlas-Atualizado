 <?php
session_start();

function verificarLogin() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ../login.php');
        exit;
    }
}

function verificarTipo($tipoPermitido) {
    verificarLogin();
    
    if ($_SESSION['usuario_tipo'] != $tipoPermitido) {
        header('Location: ../login.php');
        exit;
    }
}

function getUsuarioInfo() {
    if (isset($_SESSION['usuario_id'])) {
        return [
            'id' => $_SESSION['usuario_id'],
            'nome' => $_SESSION['usuario_nome'],
            'email' => $_SESSION['usuario_email'],
            'tipo' => $_SESSION['usuario_tipo']
        ];
    }
    return null;
}
/**
 * Verifica se o usuário tem permissão para acessar a página atual
 */
function verificarAcessoPagina($tipoRequerido = null) {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
    
    // Se foi especificado um tipo requerido, verifica
    if ($tipoRequerido && $_SESSION['usuario_tipo'] !== $tipoRequerido) {
        // Redireciona para o dashboard correto do usuário
        header('Location: ' . BASE_URL . '/dashboard/' . $_SESSION['usuario_tipo'] . '/index.php');
        exit;
    }
}

/**
 * Verifica se o usuário é administrador
 */
function isAdmin() {
    return isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin';
}

/**
 * Redireciona se não for admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/dashboard/' . $_SESSION['usuario_tipo'] . '/index.php');
        exit;
    }
}
?>