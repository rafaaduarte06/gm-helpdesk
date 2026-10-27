<?php
require_once('../../ligacao_base_dados.php');
header('Content-Type: application/json');

try {
    if (!isset($_POST['nome_modelo']) || empty($_POST['nome_modelo'])) {
        throw new Exception('Nome do modelo não fornecido');
    }

    $nome_modelo = trim($_POST['nome_modelo']);

    // Verificar se o modelo já existe
    $stmt = $pdo->prepare("SELECT FONTE_MODELO FROM fontemodelo WHERE FONTE_MODELO = ?");
    $stmt->execute([$nome_modelo]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Este modelo já existe');
    }

    // Inserir novo modelo
    $stmt = $pdo->prepare("INSERT INTO fontemodelo (FONTE_MODELO) VALUES (?)");
    $stmt->execute([$nome_modelo]);

    echo json_encode([
        'success' => true,
        'message' => 'Modelo adicionado com sucesso',
        'modelo' => [
            'id' => $nome_modelo,
            'nome' => $nome_modelo
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