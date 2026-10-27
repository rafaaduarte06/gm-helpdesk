<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

// Verificar se a referência foi enviada
if (isset($_GET['ref'])) {
    $ref = trim($_GET['ref']);
    
    try {
        // Verificar se a referência já existe na tabela de coolers
        $query = "SELECT COUNT(*) as count FROM cooler WHERE COO_REF = :ref";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':ref', $ref, PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Retornar se a referência existe ou não
        echo json_encode([
            'success' => true,
            'exists' => ($result['count'] > 0)
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao verificar referência: ' . $e->getMessage(),
            'exists' => false
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Referência não fornecida',
        'exists' => false
    ]);
}
?> 