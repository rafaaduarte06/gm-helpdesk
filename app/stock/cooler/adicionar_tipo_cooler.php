<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['tipo']) || empty($_POST['tipo'])) {
    echo json_encode(['success' => false, 'message' => 'Tipo não fornecido']);
    exit;
}

$tipo = $_POST['tipo'];

try {
    // Verificar se o tipo já existe
    $query = "SELECT COO_TIPO FROM cootipo WHERE COO_TIPO = :tipo";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Tipo já existe', 
            'tipo' => [
                'id' => $tipo,
                'nome' => $tipo
            ]
        ]);
        exit;
    }
    
    // Inserir o novo tipo
    $query = "INSERT INTO cootipo (COO_TIPO) VALUES (:tipo)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Tipo adicionado com sucesso', 
        'tipo' => [
            'id' => $tipo,
            'nome' => $tipo
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar tipo: ' . $e->getMessage()]);
}
?> 