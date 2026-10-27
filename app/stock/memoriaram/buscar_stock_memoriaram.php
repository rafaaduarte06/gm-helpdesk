<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

try {
    $query = "SELECT 
                m.MR_ID as id,
                m.MR_REF as referencia,
                mm.MMR_NOME as marca,
                m.MR_MODELO as modelo,
                m.MR_TIPO as tipo,
                m.MR_VELOCIDADE as velocidade,
                cor.COR as cor,
                me.ESTADO as estado,
                m.MR_ESTADO as estado_id,
                m.MR_SALA as MR_SALA,
                CASE 
                    WHEN c.COMP_REF IS NOT NULL THEN c.COMP_REF
                    ELSE m.MR_COMP 
                END as computador_ref
            FROM memoriaram m
            LEFT JOIN marcamr mm ON m.MR_MARCA = mm.MMR_ID
            LEFT JOIN cor ON m.MR_COR = cor.ID_COR
            LEFT JOIN memoriaram_estado me ON m.MR_ESTADO = me.ID_ESTADO
            LEFT JOIN computadores c ON m.MR_COMP = c.COMP_ID
            ORDER BY m.MR_ID DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $memorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $memorias]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => []]);
}
?> 