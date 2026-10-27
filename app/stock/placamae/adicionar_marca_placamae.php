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
    $query = "SELECT MPM_ID, MPM_NOME FROM marcapm WHERE MPM_NOME = :nome";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeMarca);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $marca = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            'success' => true,
            'message' => 'Marca já existe',
            'marca' => ['id' => $marca['MPM_ID'], 'nome' => $marca['MPM_NOME']]
        ]);
        exit;
    }
    
    // Inserir nova marca
    $query = "INSERT INTO marcapm (MPM_NOME) VALUES (:nome)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeMarca);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Marca adicionada com sucesso',
        'marca' => ['id' => $pdo->lastInsertId(), 'nome' => $nomeMarca]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar marca: ' . $e->getMessage()]);
}
?> 