<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
    exit;
}

try {
    $query = "DELETE FROM placamae WHERE PM_ID = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Mother Board excluída com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Mother Board não encontrada']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir mother board: ' . $e->getMessage()]);
}
?>