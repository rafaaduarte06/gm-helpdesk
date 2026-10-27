<?php
require_once('../../ligacao_base_dados.php');

header('Content-Type: application/json');

try {
    $query = "SELECT 
                d.DISCO_ID as id,
                d.DISCO_REF as referencia,
                md.MDSC_NOME as marca,
                d.DISCO_INTERFACE as interface,
                d.DISCO_MEMORIA as memoria,
                d.DISCO_TIPO as tipo,
                c.COR as cor,
                de.ESTADO as estado,
                d.DISCO_ESTADO as estado_id,
                d.DISCO_SALA,
                CASE 
                    WHEN comp.COMP_REF IS NOT NULL THEN comp.COMP_REF
                    ELSE d.DISCO_COMP 
                END as computador_ref
              FROM disco d
              LEFT JOIN marcadisco md ON d.DISCO_MARCA = md.MDSC_ID
              LEFT JOIN cor c ON d.DISCO_COR = c.ID_COR
              LEFT JOIN discos_estado de ON d.DISCO_ESTADO = de.ID_ESTADO
              LEFT JOIN computadores comp ON d.DISCO_COMP = comp.COMP_ID
              ORDER BY d.DISCO_ID DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $discos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $discos
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar discos: ' . $e->getMessage()
    ]);
}
?> 