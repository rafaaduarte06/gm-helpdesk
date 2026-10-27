<?php
require_once('../../ligacao_base_dados.php');
header('Content-Type: application/json');

try {
    if (!isset($_POST['potencia']) || empty($_POST['potencia'])) {
        throw new Exception('Potência não fornecida');
    }

    $potencia = trim($_POST['potencia']);

    // Verificar se a potência já existe
    $stmt = $pdo->prepare("SELECT FONTE_POTENCIA FROM fontepotencia WHERE FONTE_POTENCIA = ?");
    $stmt->execute([$potencia]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Esta potência já existe');
    }

    // Inserir nova potência
    $stmt = $pdo->prepare("INSERT INTO fontepotencia (FONTE_POTENCIA) VALUES (?)");
    $stmt->execute([$potencia]);

    echo json_encode([
        'success' => true,
        'message' => 'Potência adicionada com sucesso',
        'potencia' => [
            'id' => $potencia,
            'nome' => $potencia
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