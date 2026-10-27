<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['nome_modelo']) || empty($_POST['nome_modelo'])) {
    echo json_encode(['success' => false, 'message' => 'Nome do modelo não fornecido']);
    exit;
}

$nomeModelo = $_POST['nome_modelo'];

try {
    // Verificar se o modelo já existe
    $query = "SELECT MR_MODELO FROM memoriarmodelo WHERE MR_MODELO = :nome";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeModelo, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Modelo já existe', 
            'modelo' => [
                'id' => $nomeModelo,
                'nome' => $nomeModelo
            ]
        ]);
        exit;
    }
    
    // Inserir o novo modelo
    $query = "INSERT INTO memoriarmodelo (MR_MODELO) VALUES (:nome)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeModelo, PDO::PARAM_STR);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Modelo adicionado com sucesso', 
        'modelo' => [
            'id' => $nomeModelo,
            'nome' => $nomeModelo
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar modelo: ' . $e->getMessage()]);
}
?> 