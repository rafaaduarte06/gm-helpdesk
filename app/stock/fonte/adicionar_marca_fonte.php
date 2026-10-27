<?php
require_once('../../ligacao_base_dados.php');
header('Content-Type: application/json');

try {
    if (!isset($_POST['nome_marca']) || empty($_POST['nome_marca'])) {
        throw new Exception('Nome da marca não fornecido');
    }

    $nome_marca = trim($_POST['nome_marca']);

    // Verificar se a marca já existe
    $stmt = $pdo->prepare("SELECT MF_ID FROM marcafonte WHERE MF_NOME = ?");
    $stmt->execute([$nome_marca]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Esta marca já existe');
    }

    // Inserir nova marca
    $stmt = $pdo->prepare("INSERT INTO marcafonte (MF_NOME) VALUES (?)");
    $stmt->execute([$nome_marca]);
    
    $id = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Marca adicionada com sucesso',
        'marca' => [
            'id' => $id,
            'nome' => $nome_marca
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 