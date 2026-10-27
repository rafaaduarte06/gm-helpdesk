<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
    exit;
}

$id = $_POST['id'];

try {
    // Excluir a memória RAM
    $query = "DELETE FROM memoriaram WHERE MR_ID = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Memória RAM excluída com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Memória RAM não encontrada']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir memória RAM: ' . $e->getMessage()]);
}
?> 