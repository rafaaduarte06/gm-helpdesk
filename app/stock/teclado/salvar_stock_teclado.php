<?php
session_start();
require_once('../../ligacao_base_dados.php');

if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    $tecladoRef = $_POST['tecladoRef'];
    $tecladoMarca = $_POST['tecladoMarca'];
    $tecladoCor = $_POST['tecladoCor'];
    $tecladoEstado = $_POST['tecladoEstado'];
    $tecladoTipo = $_POST['tecladoTipo'];

    // Inserir novo teclado
    $stmt = $pdo->prepare("
        INSERT INTO teclados (
            TECLADO_REF, 
            TECLADO_MARCA, 
            TECLADO_COR, 
            TECLADO_ESTADO,
            TECLADO_TIPO,
            TECLADO_COMP
        ) VALUES (?, ?, ?, ?, ?, '')
    ");
    
    $stmt->execute([
        $tecladoRef,
        $tecladoMarca,
        $tecladoCor,
        $tecladoEstado,
        $tecladoTipo
    ]);

    echo json_encode(['success' => true, 'message' => 'Teclado adicionado com sucesso']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 