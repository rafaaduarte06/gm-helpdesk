<?php
session_start();
require_once('../ligacao_base_dados.php');

header('Content-Type: application/json');

if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

try {
    if ($_SESSION['userType'] === 'tecnico') {
        $stmt = $pdo->prepare("SELECT nome_completo, foto_perfil FROM suporte_tecnico WHERE id = ?");
    } else {
        $stmt = $pdo->prepare("SELECT nome_completo, foto_perfil FROM comunidade_escolar WHERE id = ?");
    }
    
    $stmt->execute([$_SESSION['userId']]);
    $user = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'nome_completo' => $user['nome_completo'],
        'foto_perfil' => $user['foto_perfil'] ?: 'default.jpg'
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 