<?php
session_start();
require_once('../../ligacao_base_dados.php');

if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    $nomeCor = $_POST['nome_cor'];
    
    $stmt = $pdo->prepare("INSERT INTO cor (COR) VALUES (?)");
    $stmt->execute([$nomeCor]);
    
    $novaCorId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Cor adicionada com sucesso',
        'cor' => [
            'ID_COR' => $novaCorId,
            'COR' => $nomeCor
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar cor: ' . $e->getMessage()]);
}
?> 