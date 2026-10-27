<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID não fornecido'
    ]);
    exit;
}

$id = $_GET['id'];

try {
    $query = "SELECT 
                d.*,
                comp.COMP_REF as computador_ref
              FROM disco d
              LEFT JOIN computadores comp ON d.DISCO_COMP = comp.COMP_ID
              WHERE d.DISCO_ID = :id";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $id]);
    
    $disco = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($disco) {
        echo json_encode([
            'success' => true,
            'data' => $disco
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Disco não encontrado'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar disco: ' . $e->getMessage()
    ]);
}
?> 