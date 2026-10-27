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
    $tecladoId = $_POST['tecladoId'];
    $tecladoRef = $_POST['tecladoRef'];
    $tecladoMarca = $_POST['tecladoMarca'];
    $tecladoCor = $_POST['tecladoCor'];
    $tecladoTipo = $_POST['tecladoTipo'];
    $tecladoEstado = isset($_POST['tecladoEstado']) ? $_POST['tecladoEstado'] : null;

    // Verificar se o teclado está em estado "Sem Reparo"
    $checkStmt = $pdo->prepare("SELECT TECLADO_ESTADO FROM teclados WHERE TECLADO_ID = ?");
    $checkStmt->execute([$tecladoId]);
    $tecladoAtual = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($tecladoAtual && $tecladoAtual['TECLADO_ESTADO'] == '9') { // ID 9 é "Sem Reparo"
        echo json_encode(['success' => false, 'message' => 'Não é possível editar um teclado marcado como Sem Reparo']);
        exit;
    }

    // Atualizar teclado existente
    $stmt = $pdo->prepare("
        UPDATE teclados 
        SET TECLADO_REF = ?, 
            TECLADO_MARCA = ?, 
            TECLADO_COR = ?,
            TECLADO_TIPO = ?,
            TECLADO_ESTADO = ?
        WHERE TECLADO_ID = ?
    ");
    
    $stmt->execute([
        $tecladoRef,
        $tecladoMarca,
        $tecladoCor,
        $tecladoTipo,
        $tecladoEstado,
        $tecladoId
    ]);

    echo json_encode(['success' => true, 'message' => 'Teclado atualizado com sucesso']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 