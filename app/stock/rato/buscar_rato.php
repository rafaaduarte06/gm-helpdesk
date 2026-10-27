<?php
session_start();
require_once('../../ligacao_base_dados.php');

// Verificar se o usuário está logado e é um técnico
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    $ratoId = $_GET['id'];

    $stmt = $pdo->prepare("
        SELECT 
            r.*,
            m.MRATO_NOME as marca_nome,
            c.COR as cor_nome,
            e.ESTADO as estado_nome,
            comp.COMP_REF as computador_ref
        FROM ratos r
        LEFT JOIN marcarato m ON r.RATO_MARCA = m.MRATO_ID
        LEFT JOIN cor c ON r.RATOS_COR = c.ID_COR
        LEFT JOIN rato_estado e ON r.RATOS_ESTADO = e.ID_ESTADO
        LEFT JOIN computadores comp ON r.RATOS_COMP = comp.COMP_ID
        WHERE r.RATOS_ID = ?
    ");
    $stmt->execute([$ratoId]);
    $rato = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rato) {
        throw new Exception('Rato não encontrado');
    }

    echo json_encode(['success' => true, 'data' => $rato]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 