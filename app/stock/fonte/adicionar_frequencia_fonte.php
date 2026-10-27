<?php
require_once('../../ligacao_base_dados.php');
header('Content-Type: application/json');

try {
    if (!isset($_POST['frequencia']) || empty($_POST['frequencia'])) {
        throw new Exception('Frequência não fornecida');
    }

    $frequencia = trim($_POST['frequencia']);

    // Verificar se a frequência já existe
    $stmt = $pdo->prepare("SELECT FONTE_FREQUENCIA FROM fontefrequencia WHERE FONTE_FREQUENCIA = ?");
    $stmt->execute([$frequencia]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Esta frequência já existe');
    }

    // Inserir nova frequência
    $stmt = $pdo->prepare("INSERT INTO fontefrequencia (FONTE_FREQUENCIA) VALUES (?)");
    $stmt->execute([$frequencia]);

    echo json_encode([
        'success' => true,
        'message' => 'Frequência adicionada com sucesso',
        'frequencia' => [
            'id' => $frequencia,
            'nome' => $frequencia
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