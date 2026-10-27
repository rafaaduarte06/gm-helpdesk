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

try {
    $id = $_GET['id'];
    
    $sql = "SELECT 
                pg.PG_ID,
                pg.PG_REF,
                pg.PG_MARCA,
                pg.PG_MEMORIA,
                pg.PG_MODELO,
                pg.PG_COR,
                pg.PG_ESTADO,
                pg.PG_COMP,
                comp.COMP_REF as computador_ref
            FROM placagrafica pg
            LEFT JOIN computadores comp ON pg.PG_COMP = comp.COMP_ID
            WHERE pg.PG_ID = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $placa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($placa) {
        echo json_encode([
            'success' => true,
            'data' => $placa
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Placa gráfica não encontrada'
        ]);
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar placa gráfica: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar placa gráfica: ' . $e->getMessage()
    ]);
}
?> 