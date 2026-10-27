<?php
// Iniciar sessão e verificar autenticação
session_start();

// Depuração da sessão
error_log("Sessão em alterar_estado.php: " . print_r($_SESSION, true));

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

// Verificar permissões - apenas técnicos e admins podem alterar o estado de tickets
if ($usuario_tipo !== 'tecnico' && $usuario_tipo !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Apenas técnicos e administradores podem alterar estados de tickets']);
    exit;
} 

// Verificar se os parâmetros necessários foram fornecidos
if (!isset($_POST['ticket_id']) || !is_numeric($_POST['ticket_id']) || !isset($_POST['novo_status'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

$ticket_id = (int)$_POST['ticket_id'];
$novo_status = $_POST['novo_status'];

// Verificar se o status é válido
$status_validos = ['aberto', 'em_andamento', 'resolvido', 'fechado', 'cancelado'];
if (!in_array($novo_status, $status_validos)) {
    echo json_encode(['success' => false, 'message' => 'Status inválido']);
    exit;
}

// Incluir conexão e funções
require_once '../../../ligacao_base_dados.php';
require_once 'tickets_funcoes.php';

try {
    // Verificar se o ticket existe
    $ticket = obterTicketPorId($pdo, $ticket_id);
    
    if (!$ticket) {
        echo json_encode(['success' => false, 'message' => 'Ticket não encontrado']);
        exit;
    }
    
    // Verificar se o técnico é responsável pelo ticket
    if ($ticket['id_tecnico'] != $usuario_id && $usuario_tipo !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Você não é o responsável por este ticket']);
        exit;
    }
    
    // Não permitir alteração para o mesmo status
    if ($ticket['status'] === $novo_status) {
        echo json_encode(['success' => false, 'message' => 'O ticket já está com este status']);
        exit;
    }
    
    // Iniciar transação
    $pdo->beginTransaction();
    
    // Atualizar o status do ticket
    $stmt = $pdo->prepare("UPDATE tickets SET status = ?, data_atualizacao = NOW() WHERE id = ?");
    $resultado = $stmt->execute([$novo_status, $ticket_id]);
    
    if (!$resultado) {
        throw new Exception("Erro ao atualizar o status do ticket");
    }
    
    // Adicionar um comentário do sistema
    $comentario = "Status alterado de '" . ucfirst(str_replace('_', ' ', $ticket['status'])) . "' para '" . ucfirst(str_replace('_', ' ', $novo_status)) . "' por " . $usuario_nome;
    adicionarComentario($pdo, $ticket_id, $usuario_id, $comentario, true);
    
    // Commit da transação
    $pdo->commit();
    
    // Se o status for "resolvido", registrar a resolução
    if ($novo_status === 'resolvido') {
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
                }
            }
        }
    }
    
    // Retornar sucesso
    echo json_encode(['success' => true, 'message' => 'Status do ticket alterado com sucesso']);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
} 