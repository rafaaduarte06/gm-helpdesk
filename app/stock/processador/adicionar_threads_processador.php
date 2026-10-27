<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['nome_threads']) || empty($_POST['nome_threads'])) {
    echo json_encode(['success' => false, 'message' => 'Número de threads não fornecido']);
    exit;
}

$nomeThreads = $_POST['nome_threads'];

try {
    // Verificar se o threads já existe
    $query = "SELECT PROC_THREADS FROM procthreads WHERE PROC_THREADS = :nome";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeThreads, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Número de threads já existe', 
            'threads' => [
                'id' => $nomeThreads,
                'nome' => $nomeThreads
            ]
        ]);
        exit;
    }
    
    // Inserir o novo threads
    $query = "INSERT INTO procthreads (PROC_THREADS) VALUES (:nome)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeThreads, PDO::PARAM_STR);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Número de threads adicionado com sucesso', 
        'threads' => [
            'id' => $nomeThreads,
            'nome' => $nomeThreads
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar número de threads: ' . $e->getMessage()]);
}
?> 