<?php
session_start();
require_once('../../ligacao_base_dados.php');

if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    $id = $_POST['id'];
    $tipo = $_POST['tipo'];
    $nome = $_POST['nome_completo'];
    $email = $_POST['email_institucional'];
    $numero_processo = $_POST['numero_processo'];
    
    // Preparar a query base
    if ($tipo === 'tecnico') {
        $query = "UPDATE suporte_tecnico SET 
                  nome_completo = ?, 
                  email_institucional = ?, 
                  numero_processo = ?";
        $params = [$nome, $email, $numero_processo];
    } else {
        $cargo = $_POST['cargo'];
        
        $query = "UPDATE comunidade_escolar SET 
                  nome_completo = ?, 
                  email_institucional = ?, 
                  cargo = ?, 
                  numero_processo = ?";
        $params = [$nome, $email, $cargo, $numero_processo];
    }

    // Se uma nova senha foi fornecida, adicionar à atualização
    if (!empty($_POST['senha'])) {
        $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        $query .= ", senha_hash = ?";
        $params[] = $senha_hash;
    }

    $query .= " WHERE id = ?";
    $params[] = $id;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Conta atualizada com sucesso']);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar conta: ' . $e->getMessage()]);
}
?> 