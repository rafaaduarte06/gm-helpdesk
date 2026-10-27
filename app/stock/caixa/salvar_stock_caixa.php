<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['caixaRef']) || empty($_POST['caixaRef']) ||
    !isset($_POST['caixaMarca']) || empty($_POST['caixaMarca']) ||
    !isset($_POST['caixaModelo']) || empty($_POST['caixaModelo']) ||
    !isset($_POST['caixaCor']) || empty($_POST['caixaCor']) ||
    !isset($_POST['caixaEstado']) || empty($_POST['caixaEstado'])) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}

try {
    $query = "INSERT INTO caixa (
                CAIXA_REF,
                CAIXA_MARCA,
                CAIXA_MODELO,
                CAIXA_COR,
                CAIXA_COMP,
                CAIXA_ESTADO
            ) VALUES (
                :ref,
                :marca,
                :modelo,
                :cor,
                '',
                :estado
            )";
            
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':ref', $_POST['caixaRef'], PDO::PARAM_STR);
    $stmt->bindParam(':marca', $_POST['caixaMarca'], PDO::PARAM_STR);
    $stmt->bindParam(':modelo', $_POST['caixaModelo'], PDO::PARAM_STR);
    $stmt->bindParam(':cor', $_POST['caixaCor'], PDO::PARAM_STR);
    $stmt->bindParam(':estado', $_POST['caixaEstado'], PDO::PARAM_INT);
    
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Caixa adicionada com sucesso',
            'id' => $pdo->lastInsertId()
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao adicionar caixa']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar caixa: ' . $e->getMessage()]);
}
?> 