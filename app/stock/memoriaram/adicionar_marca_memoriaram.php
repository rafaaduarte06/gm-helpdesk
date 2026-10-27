<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['nome_marca']) || empty($_POST['nome_marca'])) {
    echo json_encode(['success' => false, 'message' => 'Nome da marca não fornecido']);
    exit;
}

$nomeMarca = $_POST['nome_marca'];

try {
    // Verificar se a marca já existe
    $query = "SELECT MMR_ID, MMR_NOME FROM marcamr WHERE MMR_NOME = :nome";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeMarca, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $marca = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            'success' => true, 
            'message' => 'Marca já existe', 
            'marca' => [
                'id' => $marca['MMR_ID'],
                'nome' => $marca['MMR_NOME']
            ]
        ]);
        exit;
    }
    
    // Inserir a nova marca
    $query = "INSERT INTO marcamr (MMR_NOME) VALUES (:nome)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeMarca, PDO::PARAM_STR);
    $stmt->execute();
    
    $novoId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Marca adicionada com sucesso', 
        'marca' => [
            'id' => $novoId,
            'nome' => $nomeMarca
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar marca: ' . $e->getMessage()]);
}
?> 