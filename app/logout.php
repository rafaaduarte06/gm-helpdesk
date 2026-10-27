<?php
session_start();

// Limpa todas as variáveis da sessão
$_SESSION = array();

// Destrói o cookie da sessão se existir
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Destrói a sessão
session_destroy();

// Retorna um status de sucesso
header('Content-Type: application/json');
echo json_encode(['success' => true]);
exit;
?> 