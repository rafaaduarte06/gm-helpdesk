<?php
// Start session to maintain state
session_start();

// Set headers to prevent caching and specify JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Conectar ao banco de dados
require_once('../../ligacao_base_dados.php');

// Verificar se o usuário está logado como técnico
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    echo json_encode([
        'success' => false,
        'message' => 'Acesso não autorizado',
        'exists' => false
    ]);
    exit;
}

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
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM memoriaram WHERE MR_REF = ?");
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
    // Registrar o erro no log do servidor para debugging
    error_log('Erro em verificar_referencia.php: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'exists' => false,
        'message' => 'Erro ao verificar referência: ' . $e->getMessage()
    ]);
}
// Ensure no additional output after JSON
exit;
?> 