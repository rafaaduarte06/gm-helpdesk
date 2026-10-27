<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (isset($_GET['ref'])) {
    $referencia = $_GET['ref'];
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM placamae WHERE PM_REF = ?");
        $stmt->execute([$referencia]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'exists' => ($result['count'] > 0),
            'success' => true
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao verificar referência: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Referência não fornecida'
    ]);
}
?> 