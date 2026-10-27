<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

try {
    $query = "SELECT 
                c.CAIXA_ID as id,
                c.CAIXA_REF as referencia,
                mc.MC_NOME as marca,
                c.CAIXA_MODELO as modelo,
                cor.COR as cor,
                CASE 
                    WHEN comp.COMP_REF IS NOT NULL THEN comp.COMP_REF
                    ELSE c.CAIXA_COMP 
                END as computador,
                c.CAIXA_SALA as sala,
                ce.ESTADO as estado
            FROM caixa c
            LEFT JOIN marcacaixa mc ON c.CAIXA_MARCA = mc.MC_ID
            LEFT JOIN cor ON c.CAIXA_COR = cor.ID_COR
            LEFT JOIN caixas_estado ce ON c.CAIXA_ESTADO = ce.ID_ESTADO
            LEFT JOIN computadores comp ON c.CAIXA_COMP = comp.COMP_ID
            ORDER BY c.CAIXA_ID DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $caixas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $caixas]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar caixas: ' . $e->getMessage(), 'data' => []]);
}
?> 