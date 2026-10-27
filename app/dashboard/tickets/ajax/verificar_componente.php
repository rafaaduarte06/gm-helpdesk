<?php
// Iniciar sessão e conexão com banco de dados
session_start();
require_once('../../../ligacao_base_dados.php');

// Verificar se a requisição é AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso não permitido']);
    exit;
}

// Verificar parâmetros
if (!isset($_POST['tipo_componente']) || !isset($_POST['id_componente'])) {
    echo json_encode(['error' => 'Parâmetros inválidos']);
    exit;
}

$tipo_componente = $_POST['tipo_componente'];
$id_componente = $_POST['id_componente'];

// Validar dados
if (empty($tipo_componente) || empty($id_componente) || !is_numeric($id_componente)) {
    echo json_encode(['status' => 'ok', 'ticket_existente' => false]);
    exit;
}

try {
    // Verificar se o componente já possui um ticket ativo
    $query = "SELECT t.id, t.assunto, t.status 
             FROM tickets t 
             INNER JOIN ticket_componentes tc ON t.id = tc.ticket_id 
             WHERE tc.equipamento = ? AND tc.ref_equipamento = ? 
             AND t.status IN ('aberto', 'em_andamento')";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$tipo_componente, $id_componente]);
    
    if ($ticket_existente = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode([
            'status' => 'ok',
            'ticket_existente' => true,
            'ticket_id' => $ticket_existente['id'],
            'ticket_assunto' => $ticket_existente['assunto'],
            'mensagem' => "Já existe um ticket ativo (#{$ticket_existente['id']} - {$ticket_existente['assunto']}) para este componente."
        ]);
    } else {
        echo json_encode(['status' => 'ok', 'ticket_existente' => false]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao verificar componente: ' . $e->getMessage()]);
}
?> 