<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

// Verificar se a polegada foi enviada
if (!isset($_POST['polegada']) || empty($_POST['polegada'])) {
    echo json_encode(['success' => false, 'message' => 'Polegada não fornecida']);
    exit;
}

$polegada = $_POST['polegada'] . '"';

try {
    // Verificar se a polegada já existe
    $query = "SELECT MONITOR_POL FROM monitorpolegada WHERE MONITOR_POL = :polegada";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':polegada', $polegada, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Polegada já existe', 
            'polegada' => [
                'MONITOR_POL' => $polegada
            ]
        ]);
        exit;
    }
    
    // Inserir a nova polegada
    $query = "INSERT INTO monitorpolegada (MONITOR_POL) VALUES (:polegada)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':polegada', $polegada, PDO::PARAM_STR);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Polegada adicionada com sucesso', 
        'polegada' => [
            'MONITOR_POL' => $polegada
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar polegada: ' . $e->getMessage()]);
}
?> 