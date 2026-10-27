<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

try {
    $query = "SELECT 
                d.DRVD_ID as id,
                d.DRVD_REF as referencia,
                m.MD_NOME as marca,
                c.COR as cor,
                e.ESTADO as estado,
                d.DRVD_ESTADO as estado_id,
                d.DRVD_SALA as DRVD_SALA,
                CASE 
                    WHEN pc.COMP_REF IS NOT NULL THEN pc.COMP_REF
                    ELSE d.DRVD_COMP 
                END as computador_ref
              FROM drivedvd d
              LEFT JOIN marcadrdvd m ON d.DRVD_MARCA = m.MD_ID
              LEFT JOIN cor c ON d.DRVD_COR = c.ID_COR
              LEFT JOIN drives_estado e ON d.DRVD_ESTADO = e.ID_ESTADO
              LEFT JOIN computadores pc ON d.DRVD_COMP = pc.COMP_ID";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $drives = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $drives
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar drives: ' . $e->getMessage()
    ]);
}
?> 