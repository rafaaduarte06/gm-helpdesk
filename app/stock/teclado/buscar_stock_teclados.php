<?php
session_start();
require_once('../../ligacao_base_dados.php');

if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    $query = "
        SELECT 
            t.TECLADO_ID as id,
            t.TECLADO_REF as referencia,
            m.MTECLADO_NOME as marca,
            c.COR as cor,
            e.ESTADO as estado,
            t.TECLADO_TIPO as tipo,
            t.TECLADO_SALA as TECLADO_SALA,
            t.TECLADO_COMP as computador_id,
            CASE 
                WHEN comp.COMP_REF IS NOT NULL THEN comp.COMP_REF
                ELSE t.TECLADO_COMP 
            END as computador_ref
        FROM teclados t
        LEFT JOIN marcateclado m ON t.TECLADO_MARCA = m.MTECLADO_ID
        LEFT JOIN cor c ON t.TECLADO_COR = c.ID_COR
        LEFT JOIN teclados_estado e ON t.TECLADO_ESTADO = e.ID_ESTADO
        LEFT JOIN computadores comp ON t.TECLADO_COMP = comp.COMP_ID
        ORDER BY t.TECLADO_ID DESC
    ";

    $stmt = $pdo->query($query);
    $teclados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $teclados]);

} catch (Exception $e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 