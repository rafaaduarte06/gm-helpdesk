<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

try {
    $query = "SELECT 
                m.MONITOR_ID as id,
                m.MONITOR_REF as referencia,
                mm.MM_NOME as marca,
                CONCAT(m.MONITOR_POL, '\"') as polegada,
                m.MONITOR_FREQ as frequencia,
                c.COR as cor,
                e.ESTADO as estado,
                m.MONITOR_SALA as MONITOR_SALA,
                CASE 
                    WHEN comp.COMP_REF IS NOT NULL THEN comp.COMP_REF
                    ELSE m.MONITOR_COMP 
                END as computador_ref
            FROM monitor m
            LEFT JOIN marcamonitor mm ON m.MONITOR_MARCA = mm.MM_ID
            LEFT JOIN cor c ON m.MONITOR_COR = c.ID_COR
            LEFT JOIN monitores_estado e ON m.MONITOR_ESTADO = e.ID_ESTADO
            LEFT JOIN computadores comp ON m.MONITOR_COMP = comp.COMP_ID
            ORDER BY m.MONITOR_ID DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $monitores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $monitores]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => []]);
}
?> 