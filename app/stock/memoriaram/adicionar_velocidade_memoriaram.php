<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['nome_velocidade']) || empty($_POST['nome_velocidade'])) {
    echo json_encode(['success' => false, 'message' => 'Nome da velocidade não fornecido']);
    exit;
}

$nomeVelocidade = $_POST['nome_velocidade'];

try {
    // Verificar se a velocidade já existe
    $query = "SELECT MR_VELOCIDADE FROM memoriarvelocidade WHERE MR_VELOCIDADE = :nome";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeVelocidade, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Velocidade já existe', 
            'velocidade' => [
                'id' => $nomeVelocidade,
                'nome' => $nomeVelocidade
            ]
        ]);
        exit;
    }
    
    // Inserir a nova velocidade
    $query = "INSERT INTO memoriarvelocidade (MR_VELOCIDADE) VALUES (:nome)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeVelocidade, PDO::PARAM_STR);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Velocidade adicionada com sucesso', 
        'velocidade' => [
            'id' => $nomeVelocidade,
            'nome' => $nomeVelocidade
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar velocidade: ' . $e->getMessage()]);
}
?> 