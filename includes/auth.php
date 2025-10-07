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
?>