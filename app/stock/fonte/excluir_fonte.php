<?php
require_once('../../ligacao_base_dados.php');
header('Content-Type: application/json');

try {
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception('ID da fonte não fornecido');
    }

    $id = (int)$_POST['id'];

    // Verificar se a fonte existe
    $stmt = $pdo->prepare("SELECT FONTE_ID FROM fonte WHERE FONTE_ID = ?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) {
        throw new Exception('Fonte não encontrada');
    }

    // Excluir a fonte
    $stmt = $pdo->prepare("DELETE FROM fonte WHERE FONTE_ID = ?");
    $stmt->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Fonte excluída com sucesso'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 