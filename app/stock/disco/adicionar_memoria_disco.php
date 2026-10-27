<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['nome_memoria']) || empty($_POST['nome_memoria'])) {
    echo json_encode(['success' => false, 'message' => 'Nome da memória não fornecido']);
    exit;
}

$nomeMemoria = $_POST['nome_memoria'];

try {
    // Verificar se a memória já existe
    $query = "SELECT DISCO_MEMORIA FROM discomemoria WHERE DISCO_MEMORIA = :nome";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeMemoria, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Memória já existe', 
            'memoria' => [
                'id' => $nomeMemoria,
                'nome' => $nomeMemoria
            ]
        ]);
        exit;
    }
    
    // Inserir a nova memória
    $query = "INSERT INTO discomemoria (DISCO_MEMORIA) VALUES (:nome)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeMemoria, PDO::PARAM_STR);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Memória adicionada com sucesso', 
        'memoria' => [
            'id' => $nomeMemoria,
            'nome' => $nomeMemoria
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar memória: ' . $e->getMessage()]);
}
?> 