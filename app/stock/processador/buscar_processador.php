<?php
session_start();
require_once('../../ligacao_base_dados.php');

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID do processador não fornecido'
    ]);
    exit;
}

try {
    $query = "SELECT 
                p.*,
                c.COMP_REF as computador_ref
            FROM processador p
            LEFT JOIN computadores c ON p.PROC_COMP = c.COMP_ID
            WHERE p.PROC_ID = :id";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $_GET['id']]);
    $processador = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($processador) {
        echo json_encode([
            'success' => true,
            'data' => $processador
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Processador não encontrado'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar processador: ' . $e->getMessage()
    ]);
}
?> 