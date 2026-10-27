<?php
session_start();
require_once('../../ligacao_base_dados.php');

if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

try {
    // Receber dados do POST como JSON
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['tipo']) || !isset($data['id'])) {
        throw new Exception('Dados inválidos');
    }

    $tipo = $data['tipo'];
    $id = $data['id'];

    // Verificar se não está tentando excluir o próprio usuário
    if ($tipo === 'tecnico' && $id == $_SESSION['userId']) {
        throw new Exception('Não é possível apagares a tua própria conta');
    }

    // Selecionar a tabela correta
    $tabela = ($tipo === 'tecnico') ? 'suporte_tecnico' : 'comunidade_escolar';
    
    // Excluir a conta
    $stmt = $pdo->prepare("DELETE FROM $tabela WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Conta não encontrada');
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Conta excluída com sucesso']);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 