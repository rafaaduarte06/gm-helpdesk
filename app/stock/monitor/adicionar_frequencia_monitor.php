<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

// Verificar se a frequência foi enviada
if (!isset($_POST['frequencia']) || empty($_POST['frequencia'])) {
    echo json_encode(['success' => false, 'message' => 'Frequência não fornecida']);
    exit;
}

$frequencia = $_POST['frequencia'] . 'Hz';

try {
    // Verificar se a frequência já existe
    $query = "SELECT MONITOR_FREQUENCIA FROM monitorfrequencia WHERE MONITOR_FREQUENCIA = :frequencia";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':frequencia', $frequencia, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Frequência já existe', 
            'frequencia' => [
                'MONITOR_FREQUENCIA' => $frequencia
            ]
        ]);
        exit;
    }
    
    // Inserir a nova frequência
    $query = "INSERT INTO monitorfrequencia (MONITOR_FREQUENCIA) VALUES (:frequencia)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':frequencia', $frequencia, PDO::PARAM_STR);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Frequência adicionada com sucesso', 
        'frequencia' => [
            'MONITOR_FREQUENCIA' => $frequencia
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar frequência: ' . $e->getMessage()]);
}
?> 