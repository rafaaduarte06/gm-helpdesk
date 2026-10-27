<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['socket']) || empty($_POST['socket'])) {
    echo json_encode(['success' => false, 'message' => 'Socket não fornecido']);
    exit;
}

$socket = $_POST['socket'];

try {
    // Verificar se o socket já existe
    $query = "SELECT COO_SOCKET FROM coosocket WHERE COO_SOCKET = :socket";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':socket', $socket, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Socket já existe', 
            'socket' => [
                'id' => $socket,
                'nome' => $socket
            ]
        ]);
        exit;
    }
    
    // Inserir o novo socket
    $query = "INSERT INTO coosocket (COO_SOCKET) VALUES (:socket)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':socket', $socket, PDO::PARAM_STR);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Socket adicionado com sucesso', 
        'socket' => [
            'id' => $socket,
            'nome' => $socket
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar socket: ' . $e->getMessage()]);
}
?> 