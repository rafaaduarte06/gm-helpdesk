<?php
session_start();
require_once('../../ligacao_base_dados.php');

// Verificar se o usuário está logado e é um técnico
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    $ratoId = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM ratos WHERE RATOS_ID = ?");
    $stmt->execute([$ratoId]);

    echo json_encode(['success' => true, 'message' => 'Rato excluído com sucesso']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 