<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
    exit;
}

$id = $_GET['id'];

try {
    $query = "SELECT 
                c.COO_ID,
                c.COO_REF,
                c.COO_MARCA,
                c.COO_PINOS,
                c.COO_SOCKET,
                c.COO_TAM,
                c.COO_TIPO,
                c.COO_COR,
                c.COO_ESTADO,
                c.COO_COMP,
                comp.COMP_REF as computador_ref
            FROM cooler c
            LEFT JOIN computadores comp ON c.COO_COMP = comp.COMP_ID
            WHERE c.COO_ID = :id";
            
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $cooler = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cooler) {
        echo json_encode(['success' => true, 'data' => $cooler]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cooler não encontrado']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 