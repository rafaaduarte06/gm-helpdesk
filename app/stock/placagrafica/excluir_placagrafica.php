<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
    exit;
}

try {
    $query = "DELETE FROM placagrafica WHERE PG_ID = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Placa gráfica excluída com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Placa gráfica não encontrada']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir placa gráfica: ' . $e->getMessage()]);
}
?>