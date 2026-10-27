<?php
// Conectar ao banco de dados
require_once('../../ligacao_base_dados.php');

// Verificar se a referência foi fornecida
if (!isset($_GET['ref']) || empty($_GET['ref'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Referência não fornecida',
        'exists' => false
    ]);
    exit;
}

$referencia = trim($_GET['ref']);

try {
    // Consultar se a referência já existe no banco de dados
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM placagrafica WHERE PG_REF = ?");
    $stmt->execute([$referencia]);
    $count = $stmt->fetchColumn();
    
    // Se count > 0, a referência já existe
    $exists = ($count > 0);
    
    echo json_encode([
        'success' => true,
        'exists' => $exists,
        'message' => $exists ? 'Referência já existe' : 'Referência disponível'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'exists' => false,
        'message' => 'Erro ao verificar referência: ' . $e->getMessage()
    ]);
}
?> 