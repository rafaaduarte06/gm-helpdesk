<?php
session_start();
require_once('../../ligacao_base_dados.php');

if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    $nomeTipo = $_POST['nome_tipo'];
    
    // Verificar se o tipo já existe
    $stmt = $pdo->prepare("SELECT TECLADO_TIPO FROM tecladotipo WHERE TECLADO_TIPO = ?");
    $stmt->execute([$nomeTipo]);
    
    if ($stmt->fetch()) {
        throw new Exception('Este tipo já existe');
    }
    
    $stmt = $pdo->prepare("INSERT INTO tecladotipo (TECLADO_TIPO) VALUES (?)");
    $stmt->execute([$nomeTipo]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Tipo adicionado com sucesso',
        'tipo' => $nomeTipo
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar tipo: ' . $e->getMessage()]);
}
?> 