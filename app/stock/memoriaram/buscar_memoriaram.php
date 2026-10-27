<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
    exit;
}

$id = $_GET['id'];

try {
    $query = "SELECT 
                m.MR_ID,
                m.MR_REF,
                m.MR_MARCA,
                m.MR_MODELO,
                m.MR_TIPO,
                m.MR_VELOCIDADE,
                m.MR_COR,
                m.MR_ESTADO,
                m.MR_COMP
            FROM memoriaram m
            WHERE m.MR_ID = :id";
            
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $memoria = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($memoria) {
        echo json_encode(['success' => true, 'data' => $memoria]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Memória RAM não encontrada']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 