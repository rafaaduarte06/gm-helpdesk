<?php
session_start();
require_once('../../ligacao_base_dados.php');

if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    $tecladoId = $_GET['id'];

    $stmt = $pdo->prepare("
        SELECT 
            t.*,
            m.MTECLADO_NOME as marca_nome,
            c.COR as cor_nome,
            e.ESTADO as estado_nome,
            comp.COMP_REF as computador_ref
        FROM teclados t
        LEFT JOIN marcateclado m ON t.TECLADO_MARCA = m.MTECLADO_ID
        LEFT JOIN cor c ON t.TECLADO_COR = c.ID_COR
        LEFT JOIN teclados_estado e ON t.TECLADO_ESTADO = e.ID_ESTADO
        LEFT JOIN computadores comp ON t.TECLADO_COMP = comp.COMP_ID
        WHERE t.TECLADO_ID = ?
    ");
    $stmt->execute([$tecladoId]);
    $teclado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$teclado) {
        throw new Exception('Teclado não encontrado');
    }

    echo json_encode(['success' => true, 'data' => $teclado]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 