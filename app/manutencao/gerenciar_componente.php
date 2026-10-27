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
    $_SESSION['erro'] = "Apenas técnicos e administradores podem gerenciar componentes.";
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
if (!isset($_POST['ticket_id']) || !isset($_POST['componente_id']) || !isset($_POST['acao'])) {
    $_SESSION['erro'] = "Todos os campos são obrigatórios.";
    header('Location: manutencao.php');
    exit;
}

$ticket_id = (int)$_POST['ticket_id'];
$componente_id = (int)$_POST['componente_id'];
$acao = trim($_POST['acao']);

// Validar a ação
$acoes_validas = ['em_manutencao', 'sem_reparo', 'em_uso'];
if (!in_array($acao, $acoes_validas)) {
    $_SESSION['erro'] = "Ação inválida.";
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
        $_SESSION['erro'] = "Você não tem permissão para gerenciar componentes deste ticket.";
        header('Location: manutencao.php');
        exit;
    }
    
    // Verificar se o componente existe
    try {
        // Primeiro verificar se a tabela componentes existe
        $checkTableQuery = "SHOW TABLES LIKE 'componentes'";
        $checkTableStmt = $pdo->query($checkTableQuery);
        $componentesTableExists = $checkTableStmt->rowCount() > 0;
        
        if (!$componentesTableExists) {
            // A tabela componentes não existe, enviar um erro amigável
            $_SESSION['erro'] = "Não foi possível gerenciar o componente. A tabela de componentes não está disponível no sistema.";
            header('Location: gerir_ticket.php?id=' . $ticket_id);
            exit;
        }
        
        // A tabela existe, podemos verificar o componente
        $query = "SELECT * FROM componentes WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$componente_id]);
        $componente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$componente) {
            $_SESSION['erro'] = "Componente não encontrado ou não cadastrado na base de dados.";
            header('Location: gerir_ticket.php?id=' . $ticket_id);
            exit;
        }
    } catch (PDOException $e) {
        error_log("Erro ao verificar componente: " . $e->getMessage());
        $_SESSION['erro'] = "Erro ao verificar o componente. Por favor, tente novamente mais tarde.";
        header('Location: gerir_ticket.php?id=' . $ticket_id);
        exit;
    }
    
    // Realizar a ação apropriada
    switch ($acao) {
        case 'em_manutencao':
            $resultado = marcarComponenteEmManutencao($pdo, $ticket_id, $componente_id, $usuario_id);
            $mensagem = "Componente colocado em manutenção com sucesso.";
            break;
            
        case 'sem_reparo':
            $motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : "Sem possibilidade de reparo";
            $resultado = marcarComponenteSemReparo($pdo, $ticket_id, $componente_id, $usuario_id, $motivo);
            $mensagem = "Componente marcado como sem reparo com sucesso.";
            break;
            
        case 'em_uso':
            $sala = isset($_POST['sala']) ? trim($_POST['sala']) : null;
            $resultado = marcarComponenteEmUso($pdo, $ticket_id, $componente_id, $usuario_id, $sala);
            $mensagem = "Componente retornado para uso normal com sucesso.";
            break;
    }
    
    if ($resultado) {
        $_SESSION['sucesso'] = $mensagem;
        header('Location: gerir_ticket.php?id=' . $ticket_id);
        exit;
    } else {
        $_SESSION['erro'] = "Erro ao gerenciar o componente.";
        header('Location: gerir_ticket.php?id=' . $ticket_id);
        exit;
    }
    
} catch (PDOException $e) {
    $_SESSION['erro'] = "Erro no banco de dados: " . $e->getMessage();
    header('Location: gerir_ticket.php?id=' . $ticket_id);
    exit;
}
?> 