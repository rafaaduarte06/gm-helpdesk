<?php
// Iniciar sessão e verificar autenticação
session_start();

// Depuração da sessão
error_log("Sessão em cancelar_ticket.php: " . print_r($_SESSION, true));

// Verificar se o usuário está logado - aceitar diferentes formatos de sessão
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

// Identificar qual formato está sendo usado
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : $_SESSION['userId'];
$usuario_tipo = isset($_SESSION['usuario_tipo']) ? $_SESSION['usuario_tipo'] : (isset($_SESSION['userType']) ? $_SESSION['userType'] : '');
$usuario_nome = isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : (isset($_SESSION['userName']) ? $_SESSION['userName'] : 'Usuário');

// Guardar em formato padrão para uso consistente
$_SESSION['usuario_id'] = $usuario_id;
$_SESSION['usuario_tipo'] = $usuario_tipo;
$_SESSION['usuario_nome'] = $usuario_nome;

// Verificar permissões
if ($usuario_tipo !== 'tecnico' && $usuario_tipo !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

// Verificar se o ID do ticket foi fornecido
if ((!isset($_POST['ticket_id']) || !is_numeric($_POST['ticket_id'])) && 
    (!isset($_GET['id']) || !is_numeric($_GET['id']))) {
    echo json_encode(['success' => false, 'message' => 'ID de ticket inválido']);
    exit;
}

$ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : (int)$_GET['id'];

// Incluir conexão e funções
require_once '../../../ligacao_base_dados.php';
require_once 'tickets_funcoes.php';

try {
    // Verificar se o ticket existe e pode ser cancelado
    $ticket = obterTicketPorId($pdo, $ticket_id);
    
    if (!$ticket) {
        echo json_encode(['success' => false, 'message' => 'Ticket não encontrado']);
        exit;
    }
    
    if ($ticket['status'] === 'fechado' || $ticket['status'] === 'resolvido') {
        echo json_encode(['success' => false, 'message' => 'Este ticket já foi finalizado e não pode ser cancelado']);
        exit;
    }
    
    // Atualizar o status do ticket para fechado
    $stmt = $pdo->prepare("UPDATE tickets SET status = 'fechado', ultima_atualizacao = NOW() WHERE id = ?");
    $resultado = $stmt->execute([$ticket_id]);
    
    if (!$resultado) {
        throw new Exception("Erro ao fechar o ticket");
    }
    
    // Adicionar um comentário do sistema
    $comentario = "Ticket fechado por " . $_SESSION['usuario_nome'];
    adicionarComentario($pdo, $ticket_id, $usuario_id, $comentario, true);
    
    // Retornar sucesso
    echo json_encode(['success' => true, 'message' => 'Ticket fechado com sucesso']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
} 