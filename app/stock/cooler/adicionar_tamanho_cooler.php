<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['tamanho']) || empty($_POST['tamanho'])) {
    echo json_encode(['success' => false, 'message' => 'Tamanho não fornecido']);
    exit;
}

$tamanho = $_POST['tamanho'];

try {
    // Verificar se o tamanho já existe
    $query = "SELECT COO_TAMANHO FROM cootamanho WHERE COO_TAMANHO = :tamanho";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':tamanho', $tamanho, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Tamanho já existe', 
            'tamanho' => [
                'id' => $tamanho,
                'nome' => $tamanho
            ]
        ]);
        exit;
    }
    
    // Inserir o novo tamanho
    $query = "INSERT INTO cootamanho (COO_TAMANHO) VALUES (:tamanho)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':tamanho', $tamanho, PDO::PARAM_STR);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Tamanho adicionado com sucesso', 
        'tamanho' => [
            'id' => $tamanho,
            'nome' => $tamanho
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar tamanho: ' . $e->getMessage()]);
}
?> 