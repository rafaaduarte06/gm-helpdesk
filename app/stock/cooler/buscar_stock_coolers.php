<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

try {
    $query = "SELECT 
                c.COO_ID as id,
                c.COO_REF as referencia,
                mc.MCO_NOME as marca,
                c.COO_PINOS as pinos,
                c.COO_SOCKET as socket,
                c.COO_TAM as tamanho,
                c.COO_TIPO as tipo,
                cor.COR as cor,
                ce.ESTADO as estado,
                c.COO_SALA,
                CASE 
                    WHEN comp.COMP_REF IS NOT NULL THEN comp.COMP_REF
                    ELSE c.COO_COMP 
                END as computador_ref
            FROM cooler c
            LEFT JOIN marcacooler mc ON c.COO_MARCA = mc.MCO_ID
            LEFT JOIN cor ON c.COO_COR = cor.ID_COR
            LEFT JOIN coolers_estado ce ON c.COO_ESTADO = ce.ID_ESTADO
            LEFT JOIN computadores comp ON c.COO_COMP = comp.COMP_ID
            ORDER BY c.COO_ID DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $coolers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $coolers]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage(), 'data' => []]);
}
?> 