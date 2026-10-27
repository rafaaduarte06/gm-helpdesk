<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

// Verificar se o nome da marca foi enviado
if (!isset($_POST['nome_marca']) || empty($_POST['nome_marca'])) {
    echo json_encode(['success' => false, 'message' => 'Nome da marca não fornecido']);
    exit;
}

$nomeMarca = $_POST['nome_marca'];

try {
    // Verificar se a marca já existe
    $query = "SELECT MM_ID FROM marcamonitor WHERE MM_NOME = :nome";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeMarca, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $marca = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            'success' => true, 
            'message' => 'Marca já existe', 
            'marca' => [
                'MM_ID' => $marca['MM_ID'],
                'MM_NOME' => $nomeMarca
            ]
        ]);
        exit;
    }
    
    // Inserir a nova marca
    $query = "INSERT INTO marcamonitor (MM_NOME) VALUES (:nome)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeMarca, PDO::PARAM_STR);
    $stmt->execute();
    
    $novoId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Marca adicionada com sucesso', 
        'marca' => [
            'MM_ID' => $novoId,
            'MM_NOME' => $nomeMarca
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar marca: ' . $e->getMessage()]);
}
?> 