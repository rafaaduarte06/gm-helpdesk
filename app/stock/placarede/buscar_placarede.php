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
                pr.PR_ID,
                pr.PR_REF,
                pr.PR_MARCA,
                pr.PR_LR,
                pr.PG_COR,
                pr.PR_ESTADO,
                pr.PR_COMP,
                c.COMP_REF as computador_ref
            FROM placaderede pr
            LEFT JOIN computadores c ON pr.PR_COMP = c.COMP_ID
            WHERE pr.PR_ID = :id";
            
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $placa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($placa) {
        echo json_encode(['success' => true, 'data' => $placa]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Placa de rede não encontrada']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 