<?php
// sair.php - SOLUÇÃO GARANTIDA
session_start();

// Destruir tudo
$_SESSION = array();

// Destruir cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir sessão
session_destroy();

// JavaScript para garantir o redirecionamento
echo "
<!DOCTYPE html>
<html>
<head>
    <title>Saindo...</title>
    <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
</head>
<body class=\"bg-dark text-white\">
    <script>
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 2000);
    </script>
</body>
</html>
";
exit;
?>