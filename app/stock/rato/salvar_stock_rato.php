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
    $ratoRef = $_POST['ratoRef'];
    $ratoMarca = $_POST['ratoMarca'];
    $ratoWir = $_POST['ratoWir'];
    $ratoCor = $_POST['ratoCor'];
    $ratoEstado = $_POST['ratoEstado'];

    // Inserir novo rato
    $stmt = $pdo->prepare("
        INSERT INTO ratos (
            RATOS_REF, 
            RATO_MARCA, 
            RATOS_WIR, 
            RATOS_COR, 
            RATOS_ESTADO
        ) VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $ratoRef,
        $ratoMarca,
        $ratoWir,
        $ratoCor,
        $ratoEstado
    ]);

    echo json_encode(['success' => true, 'message' => 'Rato adicionado com sucesso']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 