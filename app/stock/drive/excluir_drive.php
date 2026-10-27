<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
    exit;
}

$id = $_POST['id'];

try {
    // Excluir o drive
    $query = "DELETE FROM drivedvd WHERE DRVD_ID = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Drive excluído com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Drive não encontrado']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir drive: ' . $e->getMessage()]);
}
?> 