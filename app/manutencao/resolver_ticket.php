<?php
// Iniciar sessão e verificar autenticação
session_start();

// Depuração da sessão
error_log("Sessão em resolver_ticket.php: " . print_r($_SESSION, true));

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
if (!isset($_POST['ticket_id']) || !is_numeric($_POST['ticket_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de ticket inválido']);
    exit;
}

$ticket_id = (int)$_POST['ticket_id'];
$resolucao = isset($_POST['resolucao']) ? trim($_POST['resolucao']) : '';
$notificar = isset($_POST['notificar']) && $_POST['notificar'] == 1;

// Incluir conexão e funções
require_once '../ligacao_base_dados.php';
require_once '../dashboard/tickets/funcoes/tickets_funcoes.php';

try {
    // Iniciar transação
    $pdo->beginTransaction();
    
    // Verificar se o ticket existe e pode ser resolvido
    $ticket = obterTicketPorId($pdo, $ticket_id);
    
    if (!$ticket) {
        echo json_encode(['success' => false, 'message' => 'Ticket não encontrado']);
        $pdo->rollBack();
        exit;
    }
    
    if ($ticket['id_tecnico'] != $usuario_id) {
        echo json_encode(['success' => false, 'message' => 'Você não é o responsável por este ticket']);
        $pdo->rollBack();
        exit;
    }
    
    if ($ticket['status'] === 'resolvido' || $ticket['status'] === 'fechado') {
        echo json_encode(['success' => false, 'message' => 'Este ticket já foi resolvido']);
        $pdo->rollBack();
        exit;
    }
    
    // Atualizar o status do ticket para resolvido
    $stmt = $pdo->prepare("UPDATE tickets SET status = 'resolvido', data_atualizacao = NOW() WHERE id = ?");
    $resultado = $stmt->execute([$ticket_id]);
    
    if (!$resultado) {
        throw new Exception("Erro ao resolver o ticket");
    }
    
    // Verificar se o ticket tem um componente associado em manutenção
    if (!empty($ticket['tipo_componente']) && !empty($ticket['id_componente'])) {
        // Restaurar o estado do componente para "disponível" após a manutenção
        $tipo = $ticket['tipo_componente'];
        $id = $ticket['id_componente'];
        
        $tabela_estado = $tipo . "s_estado";
        
        // Ajustes específicos para tabelas que não seguem o padrão
        if ($tipo == 'memoriaram') {
            $tabela_estado = 'memoriaram_estado';
        } elseif ($tipo == 'caixa') {
            $tabela_estado = 'caixas_estado';
        } elseif ($tipo == 'placagrafica') {
            $tabela_estado = 'placagrafica_estado';
        } elseif ($tipo == 'placamae') {
            $tabela_estado = 'placamae_estado';
        } elseif ($tipo == 'placarede') {
            $tabela_estado = 'placarede_estado';
        } elseif ($tipo == 'rato') {
            $tabela_estado = 'rato_estado';
        }
        
        // Verificar se a tabela existe
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tabela_estado]);
        if ($stmt->rowCount() > 0) {
            // Atualizar o estado para 'disponível'
            $stmt = $pdo->prepare("UPDATE $tabela_estado SET estado = 'disponivel' WHERE id_$tipo = ?");
            $resultado_componente = $stmt->execute([$id]);
            
            // Adicionar um comentário sobre o componente
            if ($resultado_componente) {
                adicionarComentario(
                    $pdo, 
                    $ticket_id, 
                    $usuario_id, 
                    "O componente {$tipo} (ID: {$id}) foi reparado e está novamente disponível.", 
                    true
                );
            } else {
                adicionarComentario(
                    $pdo, 
                    $ticket_id, 
                    $usuario_id, 
                    "Aviso: Não foi possível atualizar o estado do componente automaticamente. Verifique manualmente.", 
                    true
                );
            }
        }
    }
    
    // Adicionar um comentário com a resolução
    if (!empty($resolucao)) {
        adicionarComentario($pdo, $ticket_id, $usuario_id, $resolucao);
    }
    
    // Adicionar um comentário do sistema
    $comentario = "Ticket marcado como resolvido por " . $usuario_nome;
    adicionarComentario($pdo, $ticket_id, $usuario_id, $comentario, true);
    
    // Se necessário, enviar notificação (implementação futura)
    if ($notificar) {
        // Implementação futura para envio de notificação
    }
    
    // Commit da transação
    $pdo->commit();
    
    // Retornar sucesso
    echo json_encode(['success' => true, 'message' => 'Ticket resolvido com sucesso']);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
} 