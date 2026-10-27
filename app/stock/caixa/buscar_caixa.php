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
                c.CAIXA_ID,
                c.CAIXA_REF,
                c.CAIXA_MARCA,
                c.CAIXA_MODELO,
                c.CAIXA_COR,
                c.CAIXA_COMP,
                c.CAIXA_ESTADO
              FROM caixa c
              WHERE c.CAIXA_ID = :id";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $caixa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($caixa) {
        echo json_encode([
            'success' => true,
            'data' => $caixa
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Caixa não encontrada'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar caixa: ' . $e->getMessage()
    ]);
}
?> 