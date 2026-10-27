<?php
session_start();
require_once('../../ligacao_base_dados.php');

if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    $tecladoId = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM teclados WHERE TECLADO_ID = ?");
    $stmt->execute([$tecladoId]);

    echo json_encode(['success' => true, 'message' => 'Teclado excluído com sucesso']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 