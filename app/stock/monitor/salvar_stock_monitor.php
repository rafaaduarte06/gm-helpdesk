<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['monitorRef']) || empty($_POST['monitorRef']) ||
    !isset($_POST['monitorMarca']) || empty($_POST['monitorMarca']) ||
    !isset($_POST['monitorPolegada']) || empty($_POST['monitorPolegada']) ||
    !isset($_POST['monitorFrequencia']) || empty($_POST['monitorFrequencia']) ||
    !isset($_POST['monitorCor']) || empty($_POST['monitorCor']) ||
    !isset($_POST['monitorEstado']) || empty($_POST['monitorEstado'])) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}

try {
    $query = "INSERT INTO monitor (
                MONITOR_REF, 
                MONITOR_MARCA, 
                MONITOR_POL, 
                MONITOR_FREQ, 
                MONITOR_COR,
                MONITOR_ESTADO,
                MONITOR_COMP
            ) VALUES (
                :referencia,
                :marca,
                :polegada,
                :frequencia,
                :cor,
                :estado,
                ''
            )";
            
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':referencia', $_POST['monitorRef'], PDO::PARAM_STR);
    $stmt->bindParam(':marca', $_POST['monitorMarca'], PDO::PARAM_STR);
    $stmt->bindParam(':polegada', $_POST['monitorPolegada'], PDO::PARAM_STR);
    $stmt->bindParam(':frequencia', $_POST['monitorFrequencia'], PDO::PARAM_STR);
    $stmt->bindParam(':cor', $_POST['monitorCor'], PDO::PARAM_STR);
    $stmt->bindParam(':estado', $_POST['monitorEstado'], PDO::PARAM_INT);
    
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Monitor adicionado com sucesso']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar monitor: ' . $e->getMessage()]);
}
?> 