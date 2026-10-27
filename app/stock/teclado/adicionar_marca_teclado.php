<?php
session_start();
require_once('../../ligacao_base_dados.php');

if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    $nomeMarca = $_POST['nome_marca'];
    
    $stmt = $pdo->prepare("INSERT INTO marcateclado (MTECLADO_NOME) VALUES (?)");
    $stmt->execute([$nomeMarca]);
    
    $novaMarcaId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Marca adicionada com sucesso',
        'marca' => [
            'MTECLADO_ID' => $novaMarcaId,
            'MTECLADO_NOME' => $nomeMarca
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar marca: ' . $e->getMessage()]);
}
?> 