<?php
// Iniciar a sessão antes de qualquer output
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['userId'])) {
    $_SESSION['erro'] = "Você precisa estar logado para realizar esta ação.";
    header('Location: dashboard.php');
    exit;
}

// Identificar qual formato está sendo usado
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : $_SESSION['userId'];
$usuario_tipo = isset($_SESSION['usuario_tipo']) ? $_SESSION['usuario_tipo'] : (isset($_SESSION['userType']) ? $_SESSION['userType'] : '');

// Verificar se é um técnico ou administrador
if ($usuario_tipo !== 'tecnico' && $usuario_tipo !== 'admin') {
    $_SESSION['erro'] = "Apenas técnicos e administradores podem assumir tickets.";
    header('Location: dashboard.php');
    exit;
}

// Verificar se um ID de ticket foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['erro'] = "ID de ticket inválido.";
    header('Location: dashboard.php');
    exit;
}

$ticket_id = (int)$_GET['id'];

// Incluir arquivos necessários
require_once '../../ligacao_base_dados.php';
require_once '../../manutencao/funcoes/manutencao_funcoes.php';

// Verificar se o ticket existe
try {
    $query = "SELECT * FROM tickets WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ticket) {
        $_SESSION['erro'] = "Ticket não encontrado.";
        header('Location: dashboard.php');
        exit;
    }
    
    // Se o ticket já estiver atribuído a um técnico
    if ($ticket['id_tecnico'] !== null && $ticket['id_tecnico'] != $usuario_id) {
        // Verificar se o usuário atual é administrador (pode reatribuir)
        if ($usuario_tipo !== 'admin') {
            $_SESSION['erro'] = "Este ticket já está atribuído a outro técnico.";
            header('Location: dashboard.php');
            exit;
        }
    }
    
    // Atualizar o técnico responsável e status (se estiver aberto)
    $novo_status = $ticket['status'] == 'aberto' ? 'em_andamento' : $ticket['status'];
    
    $query = "UPDATE tickets SET id_tecnico = ?, status = ?, ultima_atualizacao = NOW() WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([$usuario_id, $novo_status, $ticket_id]);
    
    if ($result) {
        // Obter nome do técnico
        $query = "SELECT nome_completo FROM suporte_tecnico WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$usuario_id]);
        $tecnico = $stmt->fetch(PDO::FETCH_ASSOC);
        $nome_tecnico = $tecnico ? $tecnico['nome_completo'] : 'Técnico ID: ' . $usuario_id;
        
        // Registrar no histórico
        $status_anterior = $ticket['status'];
        if ($status_anterior != $novo_status) {
            $descricao = "Ticket assumido por {$nome_tecnico} e status alterado de '{$status_anterior}' para '{$novo_status}'";
            registrarHistoricoTicket($pdo, $ticket_id, $usuario_id, 'assumir_ticket', $descricao, $status_anterior, $novo_status);
        } else {
            $descricao = "Ticket assumido por {$nome_tecnico}";
            registrarHistoricoTicket($pdo, $ticket_id, $usuario_id, 'assumir_ticket', $descricao);
        }
        
        // Adicionar comentário do sistema
        adicionarComentarioTicket($pdo, $ticket_id, $usuario_id, "Ticket assumido por {$nome_tecnico}", true);
        
        $_SESSION['sucesso'] = "Assumistes a responsabilidade pelo ticket $ticket_id.";
        header('Location: ../../manutencao/manutencao.php');
        exit;
    } else {
        $_SESSION['erro'] = "Erro ao assumir o ticket.";
        header('Location: dashboard.php');
        exit;
    }
    
} catch (PDOException $e) {
    $_SESSION['erro'] = "Erro no banco de dados: " . $e->getMessage();
    header('Location: dashboard.php');
    exit;
}
?> 