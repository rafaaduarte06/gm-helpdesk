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
    
    $query = "SELECT 
                d.*,
                pc.COMP_REF as computador_ref
              FROM drivedvd d
              LEFT JOIN computadores pc ON d.DRVD_COMP = pc.COMP_ID
              WHERE d.DRVD_ID = :id";
              
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $drive = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($drive) {
        // Adicionar a informação do computador ao resultado
        if ($drive['DRVD_COMP'] && $drive['computador_ref']) {
            $drive['computador'] = $drive['computador_ref'];
        } else {
            $drive['computador'] = null;
        }
        unset($drive['computador_ref']); // Remove o campo extra
        
        echo json_encode([
            'success' => true,
            'data' => $drive
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Drive não encontrado'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar drive: ' . $e->getMessage()
    ]);
}
?> 