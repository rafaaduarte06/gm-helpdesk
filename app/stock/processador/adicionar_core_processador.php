<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['nome_core']) || empty($_POST['nome_core']) || !is_numeric($_POST['nome_core'])) {
    echo json_encode(['success' => false, 'message' => 'Por favor, insira um número válido para o número de cores']);
    exit;
}

$nomeCore = intval($_POST['nome_core']);

if ($nomeCore <= 0) {
    echo json_encode(['success' => false, 'message' => 'O número de cores deve ser maior que zero']);
    exit;
}

try {
    // Verificar se o core já existe
    $query = "SELECT PROC_CORE FROM proccore WHERE PROC_CORE = :nome";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeCore, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Número de cores já existe', 
            'core' => [
                'id' => $nomeCore,
                'nome' => $nomeCore
            ]
        ]);
        exit;
    }
    
    // Inserir o novo core
    $query = "INSERT INTO proccore (PROC_CORE) VALUES (:nome)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeCore, PDO::PARAM_INT);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Número de cores adicionado com sucesso', 
        'core' => [
            'id' => $nomeCore,
            'nome' => $nomeCore
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar número de cores: ' . $e->getMessage()]);
}
?> 