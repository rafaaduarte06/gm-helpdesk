<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

try {
    $query = "SELECT 
                v.VENT_ID as id,
                v.VENT_REF as referencia,
                mv.MV_NOME as marca,
                v.VENT_TAM as tamanho,
                cor.COR as cor,
                ve.ESTADO as estado,
                CASE 
                    WHEN comp.COMP_REF IS NOT NULL THEN comp.COMP_REF
                    ELSE v.VENT_COMP 
                END as computador_ref,
                v.VENT_SALA as sala
            FROM ventoinha v
            LEFT JOIN marcaventoinha mv ON v.VENT_MARCA = mv.MV_ID
            LEFT JOIN cor ON v.VENT_COR = cor.ID_COR
            LEFT JOIN ventoinhas_estado ve ON v.VENT_ESTADO = ve.ID_ESTADO
            LEFT JOIN computadores comp ON v.VENT_COMP = comp.COMP_ID
            ORDER BY v.VENT_ID DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $ventoinhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $ventoinhas]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => []]);
}
?> 