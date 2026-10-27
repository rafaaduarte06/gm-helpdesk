<?php
// Iniciar a sessão antes de qualquer output
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['userId'])) {
    $_SESSION['erro'] = "Você precisa estar logado para realizar esta ação.";
    header('Location: manutencao.php');
    exit;
}

// Identificar qual formato está sendo usado
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : $_SESSION['userId'];
$usuario_tipo = isset($_SESSION['usuario_tipo']) ? $_SESSION['usuario_tipo'] : (isset($_SESSION['userType']) ? $_SESSION['userType'] : '');

// Verificar se é um técnico ou administrador
if ($usuario_tipo !== 'tecnico' && $usuario_tipo !== 'admin') {
    $_SESSION['erro'] = "Apenas técnicos e administradores podem atualizar tickets.";
    header('Location: manutencao.php');
    exit;
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['erro'] = "Método inválido.";
    header('Location: manutencao.php');
    exit;
}

// Verificar se todos os campos necessários foram enviados
if (!isset($_POST['ticket_id']) || !isset($_POST['novo_status']) || empty($_POST['novo_status'])) {
    $_SESSION['erro'] = "Todos os campos são obrigatórios.";
    header('Location: manutencao.php');
    exit;
}

$ticket_id = (int)$_POST['ticket_id'];
$novo_status = trim($_POST['novo_status']);

// Validar o status
$status_validos = ['aberto', 'em_andamento', 'resolvido', 'fechado'];
if (!in_array($novo_status, $status_validos)) {
    $_SESSION['erro'] = "Status inválido.";
    header('Location: gerir_ticket.php?id=' . $ticket_id);
    exit;
}

// Incluir arquivos necessários
require_once '../ligacao_base_dados.php';
require_once 'funcoes/manutencao_funcoes.php';

// Verificar se o ticket existe e pertence ao técnico atual
try {
    $query = "SELECT * FROM tickets WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ticket) {
        $_SESSION['erro'] = "Ticket não encontrado.";
        header('Location: manutencao.php');
        exit;
    }
    
    // Verificar se o técnico está atribuído a este ticket ou é um administrador
    if ($ticket['id_tecnico'] != $usuario_id && $usuario_tipo !== 'admin') {
        $_SESSION['erro'] = "Você não tem permissão para atualizar este ticket.";
        header('Location: manutencao.php');
        exit;
    }
    
    // Se está marcando como resolvido, verificar se há solução
    if ($novo_status == 'resolvido' && isset($_POST['solucao']) && !empty($_POST['solucao'])) {
        $solucao = trim($_POST['solucao']);
        $resultado = resolverTicket($pdo, $ticket_id, $usuario_id, $solucao);
        
        if ($resultado) {
            $_SESSION['sucesso'] = "Ticket marcado como resolvido com sucesso.";
            header('Location: gerir_ticket.php?id=' . $ticket_id);
            exit;
        } else {
            $_SESSION['erro'] = "Erro ao resolver o ticket.";
            header('Location: gerir_ticket.php?id=' . $ticket_id);
            exit;
        }
    } 
    // Se está apenas atualizando o status
    else {
        $resultado = atualizarStatusTicket($pdo, $ticket_id, $novo_status, $usuario_id);
        
        if ($resultado) {
            $_SESSION['sucesso'] = "Status do ticket atualizado com sucesso.";
            header('Location: gerir_ticket.php?id=' . $ticket_id);
            exit;
        } else {
            $_SESSION['erro'] = "Erro ao atualizar o status do ticket.";
            header('Location: gerir_ticket.php?id=' . $ticket_id);
            exit;
        }
    }
    
} catch (PDOException $e) {
    $_SESSION['erro'] = "Erro no banco de dados: " . $e->getMessage();
    header('Location: gerir_ticket.php?id=' . $ticket_id);
    exit;
}
?> 