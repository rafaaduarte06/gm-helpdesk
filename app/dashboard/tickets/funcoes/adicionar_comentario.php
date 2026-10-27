<?php
/**
 * Script para adicionar comentários a um ticket
 */

// Iniciar sessão e incluir arquivos necessários
session_start();
require_once('../../../ligacao_base_dados.php');
require_once('tickets_funcoes.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Verificar se foi uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verificar se todos os dados necessários foram enviados
if (!isset($_POST['ticket_id']) || !isset($_POST['comentario']) || empty($_POST['comentario'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

$ticket_id = (int)$_POST['ticket_id'];
$comentario = trim($_POST['comentario']);

// Obter dados do usuário atual
$usuario = [
    'id' => $_SESSION['usuario_id'],
    'nome' => $_SESSION['usuario_nome'],
    'tipo' => $_SESSION['usuario_tipo']
];

// Verificar permissão para adicionar comentários a este ticket
if (!verificarPermissaoTicket($pdo, $ticket_id, $usuario)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Sem permissão para adicionar comentários a este ticket']);
    exit;
}

try {
    // Usar a função adicionarComentario do arquivo tickets_funcoes.php
    $resultado = adicionarComentario($pdo, $ticket_id, $usuario['id'], $comentario);
    
    if (!$resultado) {
        throw new Exception("Falha ao adicionar comentário");
    }
    
    // Retornar sucesso
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Comentário adicionado com sucesso'
    ]);
    
} catch (Exception $e) {
    // Em caso de erro, retornar uma mensagem de erro
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao adicionar comentário: ' . $e->getMessage()
    ]);
    exit;
} 