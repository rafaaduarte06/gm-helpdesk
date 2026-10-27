<?php
session_start();
require_once('../../ligacao_base_dados.php');

// Verificar se o usuário está logado e é um técnico
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    // Buscar todos os ratos com informações das tabelas relacionadas
    $query = "
        SELECT 
            r.RATOS_ID as id,
            r.RATOS_REF as referencia,
            m.MRATO_NOME as marca,
            r.RATOS_WIR as wireless,
            c.COR as cor,
            e.ESTADO as estado,
            r.RATOS_SALA as RATOS_SALA,
            CASE 
                WHEN comp.COMP_REF IS NOT NULL THEN comp.COMP_REF
                ELSE r.RATOS_COMP 
            END as RATOS_COMP
        FROM ratos r
        LEFT JOIN marcarato m ON r.RATO_MARCA = m.MRATO_ID
        LEFT JOIN cor c ON r.RATOS_COR = c.ID_COR
        LEFT JOIN rato_estado e ON r.RATOS_ESTADO = e.ID_ESTADO
        LEFT JOIN computadores comp ON r.RATOS_COMP = comp.COMP_ID
        ORDER BY r.RATOS_ID DESC
    ";

    $stmt = $pdo->query($query);
    $ratos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $ratos]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 