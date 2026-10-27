<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

try {
    $query = "SELECT 
                pr.PR_ID as id,
                pr.PR_REF as referencia,
                mpr.MPR_NOME as marca,
                prl.PR_LR as velocidade,
                cor.COR as cor,
                pre.ESTADO as estado,
                pr.PR_ESTADO as estado_id,
                pr.PR_COMP as computador_id,
                CASE 
                    WHEN comp.COMP_REF IS NOT NULL THEN comp.COMP_REF 
                    ELSE pr.PR_COMP 
                END as computador_ref,
                pr.PR_SALA as sala
            FROM placaderede pr
            LEFT JOIN marcapr mpr ON pr.PR_MARCA = mpr.MPR_ID
            LEFT JOIN prlrede prl ON pr.PR_LR = prl.PR_LR
            LEFT JOIN cor ON pr.PG_COR = cor.ID_COR
            LEFT JOIN placarede_estado pre ON pr.PR_ESTADO = pre.ID_ESTADO
            LEFT JOIN computadores comp ON pr.PR_COMP = comp.COMP_ID
            ORDER BY pr.PR_ID DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $placas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $placas]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => []]);
}
?> 