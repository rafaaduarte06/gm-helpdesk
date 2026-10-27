<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['pinos']) || empty($_POST['pinos'])) {
    echo json_encode(['success' => false, 'message' => 'Número de pinos não fornecido']);
    exit;
}

$pinos = $_POST['pinos'];

try {
    // Verificar se os pinos já existem
    $query = "SELECT COO_PINOS FROM coopinos WHERE COO_PINOS = :pinos";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':pinos', $pinos, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Pinos já existem', 
            'pinos' => [
                'id' => $pinos,
                'nome' => $pinos
            ]
        ]);
        exit;
    }
    
    // Inserir os novos pinos
    $query = "INSERT INTO coopinos (COO_PINOS) VALUES (:pinos)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':pinos', $pinos, PDO::PARAM_STR);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Pinos adicionados com sucesso', 
        'pinos' => [
            'id' => $pinos,
            'nome' => $pinos
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar pinos: ' . $e->getMessage()]);
}
?> 