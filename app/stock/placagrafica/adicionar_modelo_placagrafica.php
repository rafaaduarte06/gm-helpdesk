<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['nome_modelo']) || empty($_POST['nome_modelo'])) {
    echo json_encode(['success' => false, 'message' => 'Modelo não fornecido']);
    exit;
}

$nomeModelo = $_POST['nome_modelo'];

try {
    // Verificar se o modelo já existe
    $query = "SELECT PG_MODELO FROM pgmodelo WHERE PG_MODELO = :nome";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeModelo);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Modelo já existe',
            'modelo' => ['id' => $nomeModelo, 'nome' => $nomeModelo]
        ]);
        exit;
    }
    
    // Inserir novo modelo
    $query = "INSERT INTO pgmodelo (PG_MODELO) VALUES (:nome)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeModelo);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Modelo adicionado com sucesso',
        'modelo' => ['id' => $nomeModelo, 'nome' => $nomeModelo]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar modelo: ' . $e->getMessage()]);
}
?> 