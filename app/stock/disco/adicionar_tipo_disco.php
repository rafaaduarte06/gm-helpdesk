<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['nome_tipo']) || empty($_POST['nome_tipo'])) {
    echo json_encode(['success' => false, 'message' => 'Nome do tipo não fornecido']);
    exit;
}

$nomeTipo = $_POST['nome_tipo'];

try {
    // Verificar se o tipo já existe
    $query = "SELECT DISCO_TIPO FROM discotipo WHERE DISCO_TIPO = :nome";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeTipo, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Tipo já existe', 
            'tipo' => [
                'id' => $nomeTipo,
                'nome' => $nomeTipo
            ]
        ]);
        exit;
    }
    
    // Inserir o novo tipo
    $query = "INSERT INTO discotipo (DISCO_TIPO) VALUES (:nome)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeTipo, PDO::PARAM_STR);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Tipo adicionado com sucesso', 
        'tipo' => [
            'id' => $nomeTipo,
            'nome' => $nomeTipo
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar tipo: ' . $e->getMessage()]);
}
?> 