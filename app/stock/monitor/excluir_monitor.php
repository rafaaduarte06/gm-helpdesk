<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

// Verificar se o ID foi enviado
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
    exit;
}

$id = $_POST['id'];

try {
    // Excluir o monitor
    $query = "DELETE FROM monitor WHERE MONITOR_ID = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Monitor excluído com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Monitor não encontrado']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir monitor: ' . $e->getMessage()]);
}
?> 