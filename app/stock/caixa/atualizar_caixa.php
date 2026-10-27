<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

// Debug - Log dos dados recebidos
error_log("Dados recebidos: " . print_r($_POST, true));

// Verificar campos obrigatórios
if (!isset($_POST['id']) || empty($_POST['id']) ||
    !isset($_POST['caixaRef']) || empty($_POST['caixaRef']) ||
    !isset($_POST['caixaMarca']) || empty($_POST['caixaMarca']) ||
    !isset($_POST['caixaModelo']) || empty($_POST['caixaModelo']) ||
    !isset($_POST['caixaCor']) || empty($_POST['caixaCor']) ||
    !isset($_POST['caixaEstado']) || empty($_POST['caixaEstado'])) {
    
    // Debug - Log de campos faltando
    error_log("Campos obrigatórios faltando:");
    if (!isset($_POST['id']) || empty($_POST['id'])) error_log("- id");
    if (!isset($_POST['caixaRef']) || empty($_POST['caixaRef'])) error_log("- caixaRef");
    if (!isset($_POST['caixaMarca']) || empty($_POST['caixaMarca'])) error_log("- caixaMarca");
    if (!isset($_POST['caixaModelo']) || empty($_POST['caixaModelo'])) error_log("- caixaModelo");
    if (!isset($_POST['caixaCor']) || empty($_POST['caixaCor'])) error_log("- caixaCor");
    if (!isset($_POST['caixaEstado']) || empty($_POST['caixaEstado'])) error_log("- caixaEstado");
    
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}

try {
    // Verificar se a caixa está em estado "Sem Reparo"
    $stmt = $pdo->prepare("SELECT CAIXA_ESTADO FROM caixa WHERE CAIXA_ID = ?");
    $stmt->execute([$_POST['id']]);
    $caixaAtual = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($caixaAtual && $caixaAtual['CAIXA_ESTADO'] == 4) { // ID 4 é "Sem Reparo"
        echo json_encode(['success' => false, 'message' => 'Não é possível editar uma caixa marcada como Sem Reparo']);
        exit;
    }
    
    // Debug - Log da consulta
    error_log("Atualizando caixa com ID: " . $_POST['id']);
    error_log("Estado: " . $_POST['caixaEstado']);

    // Atualizar todos os campos exceto CAIXA_COMP
    $query = "UPDATE caixa SET 
                CAIXA_REF = :ref,
                CAIXA_MARCA = :marca,
                CAIXA_MODELO = :modelo,
                CAIXA_COR = :cor,
                CAIXA_ESTADO = :estado
              WHERE CAIXA_ID = :id";
            
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
    $stmt->bindParam(':ref', $_POST['caixaRef'], PDO::PARAM_STR);
    $stmt->bindParam(':marca', $_POST['caixaMarca'], PDO::PARAM_STR);
    $stmt->bindParam(':modelo', $_POST['caixaModelo'], PDO::PARAM_STR);
    $stmt->bindParam(':cor', $_POST['caixaCor'], PDO::PARAM_STR);
    $stmt->bindParam(':estado', $_POST['caixaEstado'], PDO::PARAM_STR);
    
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Caixa atualizada com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nenhuma alteração realizada']);
    }
} catch (PDOException $e) {
    error_log("Erro PDO: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar caixa: ' . $e->getMessage()]);
}
?> 