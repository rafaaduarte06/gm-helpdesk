<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['nome_memoria']) || empty($_POST['nome_memoria'])) {
    echo json_encode(['success' => false, 'message' => 'Memória não fornecida']);
    exit;
}

$nomeMemoria = $_POST['nome_memoria'];

try {
    // Verificar se a memória já existe
    $query = "SELECT PG_MEMORIA FROM pgmemoria WHERE PG_MEMORIA = :nome";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeMemoria);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Memória já existe',
            'memoria' => ['id' => $nomeMemoria, 'nome' => $nomeMemoria]
        ]);
        exit;
    }
    
    // Inserir nova memória
    $query = "INSERT INTO pgmemoria (PG_MEMORIA) VALUES (:nome)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeMemoria);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Memória adicionada com sucesso',
        'memoria' => ['id' => $nomeMemoria, 'nome' => $nomeMemoria]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar memória: ' . $e->getMessage()]);
}
?> 