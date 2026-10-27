<?php
/**
 * Arquivo para processar ações de manutenção
 */

// Iniciar a sessão antes de qualquer output
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['userId'])) {
    header('Location: ../../login.php');
    exit;
}

// Identificar qual formato está sendo usado
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : $_SESSION['userId'];
$usuario_tipo = isset($_SESSION['usuario_tipo']) ? $_SESSION['usuario_tipo'] : (isset($_SESSION['userType']) ? $_SESSION['userType'] : '');

// Verificar se é um técnico ou administrador
if ($usuario_tipo !== 'tecnico' && $usuario_tipo !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

// Incluir arquivos necessários
require_once '../../ligacao_base_dados.php';
require_once 'manutencao_funcoes.php';

// Verificar se uma ação foi solicitada
if (!isset($_POST['acao'])) {
    header('Location: ../manutencao.php');
    exit;
}

$acao = $_POST['acao'];
$ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;

// Verificar se o ticket existe e pertence ao técnico logado
$sql = "SELECT * FROM tickets WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    $_SESSION['erro'] = "Ticket não encontrado.";
    header('Location: ../manutencao.php');
    exit;
}

// Verificar se o usuário tem permissão para gerenciar este ticket
// Administradores podem acessar qualquer ticket, técnicos apenas os seus
if ($ticket['id_tecnico'] != $usuario_id && $usuario_tipo !== 'admin' && $acao != 'listar_tickets') {
    $_SESSION['erro'] = "Você não tem permissão para gerir este ticket.";
    header('Location: ../manutencao.php');
    exit;
}

// Processar a ação solicitada
switch ($acao) {
    case 'adicionar_comentario':
        $comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';
        
        if (empty($comentario)) {
            $_SESSION['erro'] = "O comentário não pode estar vazio.";
        } else {
            if (adicionarComentarioTicket($pdo, $ticket_id, $usuario_id, $comentario)) {
                $_SESSION['sucesso'] = "Comentário adicionado com sucesso.";
                
                // Verificar se também está atualizando o status
                if (isset($_POST['atualizar_status']) && $_POST['atualizar_status'] == '1') {
                    $novo_status = $_POST['novo_status'];
                    atualizarStatusTicket($pdo, $ticket_id, $novo_status, $usuario_id);
                }
            } else {
                $_SESSION['erro'] = "Erro ao adicionar comentário.";
            }
        }
        
        header("Location: ../gerir_ticket.php?id={$ticket_id}");
        break;
        
    case 'marcar_manutencao':
        $componente_id = isset($_POST['componente_id']) ? (int)$_POST['componente_id'] : 0;
        
        if ($componente_id <= 0) {
            $_SESSION['erro'] = "Componente inválido.";
        } else {
            if (marcarComponenteEmManutencao($pdo, $ticket_id, $componente_id, $usuario_id)) {
                $_SESSION['sucesso'] = "Componente marcado como 'Em Manutenção'.";
                
                // Atualizar ticket para 'em_andamento' se estiver 'aberto'
                if ($ticket['status'] == 'aberto') {
                    atualizarStatusTicket($pdo, $ticket_id, 'em_andamento', $usuario_id);
                }
            } else {
                $_SESSION['erro'] = "Erro ao atualizar o estado do componente.";
            }
        }
        
        header("Location: ../gerir_ticket.php?id={$ticket_id}");
        break;
        
    case 'marcar_sem_reparo':
        $componente_id = isset($_POST['componente_id']) ? (int)$_POST['componente_id'] : 0;
        $motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';
        
        if ($componente_id <= 0) {
            $_SESSION['erro'] = "Componente inválido.";
        } elseif (empty($motivo)) {
            $_SESSION['erro'] = "É necessário informar o motivo.";
        } else {
            if (marcarComponenteSemReparo($pdo, $ticket_id, $componente_id, $usuario_id, $motivo)) {
                $_SESSION['sucesso'] = "Componente marcado como 'Sem Reparo'.";
            } else {
                $_SESSION['erro'] = "Erro ao atualizar o estado do componente.";
            }
        }
        
        header("Location: ../gerir_ticket.php?id={$ticket_id}");
        break;
        
    case 'marcar_em_uso':
        $componente_id = isset($_POST['componente_id']) ? (int)$_POST['componente_id'] : 0;
        $sala = isset($_POST['sala']) ? trim($_POST['sala']) : null;
        
        if ($componente_id <= 0) {
            $_SESSION['erro'] = "Componente inválido.";
        } else {
            if (marcarComponenteEmUso($pdo, $ticket_id, $componente_id, $usuario_id, $sala)) {
                $_SESSION['sucesso'] = "Componente marcado como 'Em Uso'.";
            } else {
                $_SESSION['erro'] = "Erro ao atualizar o estado do componente.";
            }
        }
        
        header("Location: ../gerir_ticket.php?id={$ticket_id}");
        break;
        
    case 'agendar_manutencao':
        $data_agenda = isset($_POST['data_agenda']) ? $_POST['data_agenda'] : '';
        $descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
        
        if (empty($data_agenda)) {
            $_SESSION['erro'] = "Data de agendamento inválida.";
        } elseif (empty($descricao)) {
            $_SESSION['erro'] = "A descrição do agendamento não pode estar vazia.";
        } else {
            if (agendarManutencao($pdo, $ticket_id, $usuario_id, $data_agenda, $descricao)) {
                $_SESSION['sucesso'] = "Manutenção agendada com sucesso.";
            } else {
                $_SESSION['erro'] = "Erro ao agendar manutenção.";
            }
        }
        
        header("Location: ../gerir_ticket.php?id={$ticket_id}");
        break;
        
    case 'resolver_ticket':
        $solucao = isset($_POST['solucao']) ? trim($_POST['solucao']) : '';
        
        if (empty($solucao)) {
            $_SESSION['erro'] = "A descrição da solução não pode estar vazia.";
        } else {
            if (resolverTicket($pdo, $ticket_id, $usuario_id, $solucao)) {
                $_SESSION['sucesso'] = "Ticket marcado como resolvido.";
            } else {
                $_SESSION['erro'] = "Erro ao resolver o ticket.";
            }
        }
        
        header("Location: ../gerir_ticket.php?id={$ticket_id}");
        break;
        
    case 'atualizar_status':
        $novo_status = isset($_POST['novo_status']) ? $_POST['novo_status'] : '';
        
        if (empty($novo_status)) {
            $_SESSION['erro'] = "Status inválido.";
        } else {
            if (atualizarStatusTicket($pdo, $ticket_id, $novo_status, $usuario_id)) {
                $_SESSION['sucesso'] = "Status atualizado com sucesso.";
            } else {
                $_SESSION['erro'] = "Erro ao atualizar o status.";
            }
        }
        
        header("Location: ../gerir_ticket.php?id={$ticket_id}");
        break;
        
    case 'listar_tickets':
        // Esta ação é apenas para AJAX
        $tickets = obterTicketsAtribuidos($pdo, $usuario_id);
        echo json_encode($tickets);
        exit;
        
    default:
        $_SESSION['erro'] = "Ação desconhecida.";
        header('Location: ../manutencao.php');
        break;
} 