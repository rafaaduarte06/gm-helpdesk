<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['nome_tamanho']) || empty($_POST['nome_tamanho'])) {
    echo json_encode(['success' => false, 'message' => 'Tamanho não fornecido']);
    exit;
}

$nomeTamanho = $_POST['nome_tamanho'];

try {
    // Verificar se o tamanho já existe
    $query = "SELECT PM_TAM FROM pmtam WHERE PM_TAM = :nome";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeTamanho);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Tamanho já existe',
            'tamanho' => ['id' => $nomeTamanho, 'nome' => $nomeTamanho]
        ]);
        exit;
    }
    
    // Inserir novo tamanho
    $query = "INSERT INTO pmtam (PM_TAM) VALUES (:nome)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeTamanho);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Tamanho adicionado com sucesso',
        'tamanho' => ['id' => $nomeTamanho, 'nome' => $nomeTamanho]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar tamanho: ' . $e->getMessage()]);
}
?> 