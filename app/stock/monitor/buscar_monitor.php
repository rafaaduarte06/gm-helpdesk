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
                m.*,
                mm.MM_NOME as marca_nome,
                c.COR as cor_nome,
                e.ESTADO as estado_nome,
                comp.COMP_REF as computador_ref
            FROM monitor m
            LEFT JOIN marcamonitor mm ON m.MONITOR_MARCA = mm.MM_ID
            LEFT JOIN cor c ON m.MONITOR_COR = c.ID_COR
            LEFT JOIN monitores_estado e ON m.MONITOR_ESTADO = e.ID_ESTADO
            LEFT JOIN computadores comp ON m.MONITOR_COMP = comp.COMP_ID
            WHERE m.MONITOR_ID = :id";
            
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $monitor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($monitor) {
        echo json_encode(['success' => true, 'data' => $monitor]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Monitor não encontrado']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 