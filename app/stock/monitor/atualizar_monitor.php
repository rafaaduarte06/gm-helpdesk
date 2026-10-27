<?php
session_start();
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

// Verificar se o usuário está logado e é um técnico
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    // Verificar se o ID foi fornecido
    if (!isset($_POST['id'])) {
        throw new Exception('ID do monitor não fornecido');
    }

    // Receber dados do formulário
    $monitorId = $_POST['id'];
    $monitorRef = $_POST['monitorRef'];
    $monitorMarca = $_POST['monitorMarca'];
    $monitorPolegada = $_POST['monitorPolegada'];
    $monitorFrequencia = $_POST['monitorFrequencia'];
    $monitorCor = $_POST['monitorCor'];
    $monitorEstado = isset($_POST['monitorEstado']) ? $_POST['monitorEstado'] : null;

    // Verificar campos obrigatórios
    if (empty($monitorRef) || empty($monitorMarca) || empty($monitorPolegada) || 
        empty($monitorFrequencia) || empty($monitorCor) || empty($monitorEstado)) {
        throw new Exception('Todos os campos são obrigatórios');
    }

    // Atualizar monitor existente
    $stmt = $pdo->prepare("
        UPDATE monitor 
        SET MONITOR_REF = ?, 
            MONITOR_MARCA = ?, 
            MONITOR_POL = ?, 
            MONITOR_FREQ = ?,
            MONITOR_COR = ?,
            MONITOR_ESTADO = ?
        WHERE MONITOR_ID = ?
    ");
    
    $stmt->execute([
        $monitorRef,
        $monitorMarca,
        $monitorPolegada,
        $monitorFrequencia,
        $monitorCor,
        $monitorEstado,
        $monitorId
    ]);

    echo json_encode(['success' => true, 'message' => 'Monitor atualizado com sucesso']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 