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
                v.VENT_ID,
                v.VENT_REF,
                v.VENT_MARCA,
                v.VENT_TAM,
                v.VENT_COR,
                v.VENT_ESTADO,
                v.VENT_COMP,
                COALESCE(comp.COMP_REF, '') as computador_ref
            FROM ventoinha v
            LEFT JOIN computadores comp ON v.VENT_COMP = comp.COMP_ID
            WHERE v.VENT_ID = :id";
            
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $ventoinha = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($ventoinha) {
        echo json_encode(['success' => true, 'data' => $ventoinha]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ventoinha não encontrada']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 