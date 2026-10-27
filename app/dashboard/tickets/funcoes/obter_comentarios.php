<?php
/**
 * Script para obter comentários de um ticket
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

// Verificar se o ID do ticket foi fornecido
if (!isset($_GET['ticket_id']) || !is_numeric($_GET['ticket_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID do ticket não fornecido ou inválido']);
    exit;
}

$ticket_id = (int)$_GET['ticket_id'];

// Obter dados do usuário atual
$usuario = [
    'id' => $_SESSION['usuario_id'],
    'nome' => $_SESSION['usuario_nome'],
    'tipo' => $_SESSION['usuario_tipo']
];

// Verificar permissão para visualizar este ticket
if (!verificarPermissaoTicket($pdo, $ticket_id, $usuario)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Sem permissão para visualizar este ticket']);
    exit;
}

try {
    // Obter comentários do ticket
    $sql = "SELECT tc.*, st.nome_completo as nome_autor 
            FROM ticket_comentarios tc
            LEFT JOIN suporte_tecnico st ON tc.tecnico_id = st.id
            WHERE tc.ticket_id = :ticket_id 
            ORDER BY tc.data_comentario ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':ticket_id', $ticket_id);
    $stmt->execute();
    
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Retornar os comentários em formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'comentarios' => $comentarios
    ]);
    
} catch (Exception $e) {
    // Em caso de erro, retornar uma mensagem de erro
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao obter comentários: ' . $e->getMessage()
    ]);
    exit;
} 