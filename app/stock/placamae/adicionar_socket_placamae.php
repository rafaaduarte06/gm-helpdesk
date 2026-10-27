<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['nome_socket']) || empty($_POST['nome_socket'])) {
    echo json_encode(['success' => false, 'message' => 'Socket não fornecido']);
    exit;
}

$nomeSocket = $_POST['nome_socket'];

try {
    // Verificar se o socket já existe
    $query = "SELECT PM_SOCKET FROM pmsocket WHERE PM_SOCKET = :nome";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeSocket);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Socket já existe',
            'socket' => ['id' => $nomeSocket, 'nome' => $nomeSocket]
        ]);
        exit;
    }
    
    // Inserir novo socket
    $query = "INSERT INTO pmsocket (PM_SOCKET) VALUES (:nome)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeSocket);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Socket adicionado com sucesso',
        'socket' => ['id' => $nomeSocket, 'nome' => $nomeSocket]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar socket: ' . $e->getMessage()]);
}
?> 