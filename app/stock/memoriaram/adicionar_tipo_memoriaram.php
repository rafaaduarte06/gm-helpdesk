<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['nome_tipo']) || empty($_POST['nome_tipo'])) {
    echo json_encode(['success' => false, 'message' => 'Nome do tipo não fornecido']);
    exit;
}

$nomoTipo = $_POST['nome_tipo'];

try {
    // Verificar se o tipo já existe
    $query = "SELECT MR_TIPO FROM memoriartipo WHERE MR_TIPO = :nome";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomoTipo, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Tipo já existe', 
            'tipo' => [
                'id' => $nomoTipo,
                'nome' => $nomoTipo
            ]
        ]);
        exit;
    }
    
    // Inserir o novo tipo
    $query = "INSERT INTO memoriartipo (MR_TIPO) VALUES (:nome)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomoTipo, PDO::PARAM_STR);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Tipo adicionado com sucesso', 
        'tipo' => [
            'id' => $nomoTipo,
            'nome' => $nomoTipo
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar tipo: ' . $e->getMessage()]);
}
?> 