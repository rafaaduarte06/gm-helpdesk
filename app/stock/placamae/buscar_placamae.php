<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID não fornecido');
    }

    $id = $_GET['id'];
    
    $query = "SELECT 
        pm.PM_ID,
        pm.PM_REF,
        pm.PM_MARCA,
        pm.PM_TAM,
        pm.PM_SOCKET,
        pm.PM_CHIPSET,
        pm.PM_FORMATO,
        pm.PM_TMEMORIA,
        pm.PM_COR,
        pm.PM_ESTADO,
        COALESCE(pm.PM_COMP, '') as PM_COMP,
        COALESCE(comp.COMP_REF, '') as comp_ref
    FROM placamae pm
    LEFT JOIN computadores comp ON pm.PM_COMP = comp.COMP_ID
    WHERE pm.PM_ID = :id";

    $stmt = $pdo->prepare($query);
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
            'message' => 'Placa-mãe não encontrada'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 