<?php
// Inicializar a sessão
session_start();

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Destruir a sessão
session_destroy();

// Redirecionar para a página de login
header("location: login.php");
exit;
?>