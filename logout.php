<?php
// Pegará os cookies do usuario e resetará, fazendo com que o login do usuario desapareça e ele precise relogar
session_start();

$_SESSION = [];

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirecionar para o login
header("Location: http://" . $_SERVER['HTTP_HOST'] . "/portal-academia/login.php");
exit;
?>