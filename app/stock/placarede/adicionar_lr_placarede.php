<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['velocidade']) || empty($_POST['velocidade'])) {
    echo json_encode(['success' => false, 'message' => 'Velocidade não fornecida']);
    exit;
}

$velocidade = $_POST['velocidade'];

try {
    // Verificar se a velocidade já existe
    $query = "SELECT PR_LR FROM prlrede WHERE PR_LR = :velocidade";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':velocidade', $velocidade, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Velocidade já existe', 
            'velocidade' => $velocidade
        ]);
        exit;
    }
    
    // Inserir a nova velocidade
    $query = "INSERT INTO prlrede (PR_LR) VALUES (:velocidade)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':velocidade', $velocidade, PDO::PARAM_STR);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Velocidade adicionada com sucesso', 
        'velocidade' => $velocidade
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar velocidade: ' . $e->getMessage()]);
}
?> 