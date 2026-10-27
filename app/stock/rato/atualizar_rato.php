<?php
session_start();
require_once('../../ligacao_base_dados.php');

// Verificar se o usuário está logado e é um técnico
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    // Receber dados do formulário
    $ratoId = $_POST['ratoId'];
    $ratoRef = $_POST['ratoRef'];
    $ratoMarca = $_POST['ratoMarca'];
    $ratoWir = $_POST['ratoWir'];
    $ratoCor = $_POST['ratoCor'];
    $ratoEstado = isset($_POST['ratoEstado']) ? $_POST['ratoEstado'] : null;

    // Atualizar rato existente
    $stmt = $pdo->prepare("
        UPDATE ratos 
        SET RATOS_REF = ?, 
            RATO_MARCA = ?, 
            RATOS_WIR = ?, 
            RATOS_COR = ?,
            RATOS_ESTADO = ?
        WHERE RATOS_ID = ?
    ");
    
    $stmt->execute([
        $ratoRef,
        $ratoMarca,
        $ratoWir,
        $ratoCor,
        $ratoEstado,
        $ratoId
    ]);

    echo json_encode(['success' => true, 'message' => 'Rato atualizado com sucesso']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 