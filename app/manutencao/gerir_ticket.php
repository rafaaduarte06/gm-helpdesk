<?php
// Habilitar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Guardar em formato padrão para uso consistente
$_SESSION['usuario_id'] = $usuario_id;
$_SESSION['usuario_tipo'] = $usuario_tipo;

// Verificar se é um técnico ou administrador
if ($usuario_tipo !== 'tecnico' && $usuario_tipo !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

// Incluir arquivos necessários
require_once '../ligacao_base_dados.php';
require_once 'funcoes/manutencao_funcoes.php';

// Verificar se foi fornecido um ID de ticket
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['erro'] = "ID de ticket inválido";
    header('Location: manutencao.php');
    exit;
}

$ticket_id = (int)$_GET['id'];

// Obter dados do ticket
$ticket = obterDetalhesTicket($pdo, $ticket_id);

// Verificar se o ticket existe e pertence ao técnico logado
if (!$ticket || $ticket['id_tecnico'] != $usuario_id) {
    $_SESSION['erro'] = "Você não tem permissão para acessar este ticket ou o ticket não existe";
    header('Location: manutencao.php');
    exit;
}

// Obter componentes do ticket
$componentes = obterComponentesTicket($ticket_id, $pdo);

// Obter comentários
$comentarios = obterComentariosTicket($pdo, $ticket_id);

// Obter histórico
$historico = obterHistoricoTicket($pdo, $ticket_id);

// Obter todas as salas disponíveis
$salas = obterTodasSalas($pdo);

// Verificar se foi enviado um formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Processar comentário
    if (isset($_POST['acao']) && $_POST['acao'] === 'adicionar_comentario') {
        if (!empty($_POST['comentario'])) {
            if (adicionarComentarioTicket($pdo, $ticket_id, $usuario_id, $_POST['comentario'])) {
                $_SESSION['sucesso'] = "Comentário adicionado com sucesso";
    } else {
                $_SESSION['erro'] = "Erro ao adicionar comentário";
            }
        } else {
            $_SESSION['erro'] = "O comentário não pode estar vazio";
        }
        
        // Redireciona para evitar reenvio do formulário
        header("Location: gerir_ticket.php?id=$ticket_id");
    exit;
}

    // Atualizar status
    if (isset($_POST['acao']) && $_POST['acao'] === 'atualizar_status') {
        if (!empty($_POST['novo_status'])) {
            if (atualizarStatusTicket($pdo, $ticket_id, $_POST['novo_status'], $usuario_id)) {
                $_SESSION['sucesso'] = "Status atualizado com sucesso";
                
                // Se o status for alterado para "resolvido", registrar solução
                if ($_POST['novo_status'] === 'resolvido' && !empty($_POST['solucao'])) {
                    adicionarComentarioTicket($pdo, $ticket_id, $usuario_id, "SOLUÇÃO: " . $_POST['solucao']);
                }
            } else {
                $_SESSION['erro'] = "Erro ao atualizar status";
            }
        }
        
        // Redireciona
        header("Location: gerir_ticket.php?id=$ticket_id");
        exit;
    }
    
    // Resolver ticket
    if (isset($_POST['acao']) && $_POST['acao'] === 'resolver_ticket') {
        if (!empty($_POST['solucao'])) {
            if (resolverTicket($pdo, $ticket_id, $usuario_id, $_POST['solucao'])) {
                $_SESSION['sucesso'] = "Ticket resolvido com sucesso";
            } else {
                $_SESSION['erro'] = "Erro ao resolver ticket";
            }
        } else {
            $_SESSION['erro'] = "É necessário informar a solução aplicada";
        }
        
        // Redireciona
        header("Location: gerir_ticket.php?id=$ticket_id");
    exit;
}

    // Agendar manutenção
    if (isset($_POST['acao']) && $_POST['acao'] === 'agendar_manutencao') {
        if (!empty($_POST['data_agenda']) && !empty($_POST['descricao_agenda'])) {
            if (agendarManutencao($pdo, $ticket_id, $usuario_id, $_POST['data_agenda'], $_POST['descricao_agenda'])) {
                $_SESSION['sucesso'] = "Manutenção agendada com sucesso";
            } else {
                $_SESSION['erro'] = "Erro ao agendar manutenção";
            }
        } else {
            $_SESSION['erro'] = "Todos os campos são obrigatórios";
        }
        
        // Redireciona
        header("Location: gerir_ticket.php?id=$ticket_id");
    exit;
}

    // Processar ações de componentes
    // Componente em manutenção
    if (isset($_POST['acao']) && $_POST['acao'] === 'componente_em_manutencao') {
        if (!empty($_POST['componente_id'])) {
            if (marcarComponenteEmManutencao($pdo, $ticket_id, $_POST['componente_id'], $usuario_id)) {
                $_SESSION['sucesso'] = "Componente marcado como 'Em Manutenção'";
            } else {
                $_SESSION['erro'] = "Erro ao atualizar estado do componente";
            }
        } else {
            $_SESSION['erro'] = "ID do componente não fornecido";
        }
        
        // Redireciona
        header("Location: gerir_ticket.php?id=$ticket_id");
        exit;
    }
    
    // Componente em uso (reparado)
    if (isset($_POST['acao']) && $_POST['acao'] === 'componente_em_uso') {
        if (!empty($_POST['componente_id']) && !empty($_POST['sala'])) {
            if (marcarComponenteEmUso($pdo, $ticket_id, $_POST['componente_id'], $usuario_id, $_POST['sala'])) {
                $_SESSION['sucesso'] = "Componente marcado como 'Em Uso' e associado à sala especificada";
            } else {
                $_SESSION['erro'] = "Erro ao atualizar estado do componente";
            }
        } else {
            $_SESSION['erro'] = "Todos os campos são obrigatórios";
        }
        
        // Redireciona
        header("Location: gerir_ticket.php?id=$ticket_id");
        exit;
    }
    
    // Componente sem reparo (abate)
    if (isset($_POST['acao']) && $_POST['acao'] === 'componente_sem_reparo') {
        if (!empty($_POST['componente_id']) && !empty($_POST['motivo'])) {
            if (marcarComponenteSemReparo($pdo, $ticket_id, $_POST['componente_id'], $usuario_id, $_POST['motivo'])) {
                $_SESSION['sucesso'] = "Componente marcado como 'Sem Reparo'";
            } else {
                $_SESSION['erro'] = "Erro ao atualizar estado do componente";
            }
        } else {
            $_SESSION['erro'] = "Todos os campos são obrigatórios";
        }
        
        // Redireciona
        header("Location: gerir_ticket.php?id=$ticket_id");
        exit;
    }
}

// Buscar dados do usuário técnico
try {
    $stmt = $pdo->prepare("SELECT * FROM suporte_tecnico WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        $usuario = [
            'id' => $_SESSION['usuario_id'],
            'nome_completo' => 'Utilizador',
            'foto_perfil' => 'default.jpg'
        ];
    }
} catch (Exception $e) {
    $usuario = [
        'id' => $_SESSION['usuario_id'],
        'nome_completo' => 'Utilizador',
        'foto_perfil' => 'default.jpg'
    ];
}

// Incluir o sidebar
include_once '../includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Ticket <?php echo $ticket['id']; ?> - GM HelpDesk</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    
    <style>
        /* Estilo de fundo gradiente */
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        /* Estilo completo da sidebar */
        .sidebar {
            width: 300px;
            min-height: 100vh;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 999;
            background: linear-gradient(150deg,rgb(227, 238, 252),rgb(235, 241, 250));
            border-right: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            padding: 0;
            display: flex;
            flex-direction: column;
                transition: all 0.3s ease;
            will-change: transform, left;
        }

        .sidebar::before {
            display: none;
        }

        .escola-logo {
            position: relative;
            z-index: 20;
            margin-top: auto;
            padding: 25px 0;
            text-align: center;
        }

        .escola-logo img {
            max-width: 150px;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .sidebar-header, .sidebar .user-info, .sidebar .nav {
            position: relative;
            z-index: 10;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .sidebar-header h5 {
            font-weight: 700;
            color: #1a3c5e;
        }

        .sidebar .user-info {
            padding: 20px;
            background: linear-gradient(135deg, #2a4d69, rgb(36, 110, 167));
            border-radius: 12px;
            color: white;
            margin-bottom: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .sidebar .user-info:hover {
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }

        .sidebar .user-info .user-details h6 {
            color: white;
            font-weight: 700;
        }

        .sidebar .user-info .user-details small {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
        }

        .sidebar .user-info .avatar img {
            border: 2px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .sidebar .nav {
            padding: 0 15px;
        }

        .sidebar .nav-item {
            margin-bottom: 8px;
        }

        .sidebar .nav-link {
            padding: 10px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
            color: #1a3c5e;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            color: #0d6efd;
            width: 20px;
            text-align: center;
        }

        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: #ffffff;
            color: #0d6efd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .sidebar .nav-link.active i {
            color: #0d6efd;
        }

        /* Botão de sidebar toggle */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1002; /* Garantir que está acima do sidebar e overlay */
            background-color: rgba(75, 134, 180, 0.15);
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
            width: 45px;
            height: 45px;
            text-align: center;
            line-height: 45px;
            border: 2px solid #4b86b4;
            transition: all 0.3s ease;
            padding: 0;
            cursor: pointer;
        }

        .sidebar-toggle:hover {
            background-color: rgba(75, 134, 180, 0.25);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }

        .sidebar-toggle i {
            font-size: 1.3rem;
            color: #2a4d69;
            line-height: 42px;
        }

        .sidebar-toggle:active {
            transform: translateY(1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Estilo para overlay quando sidebar estiver aberto */
        .sidebar-overlay {
            position: fixed;
            top: 0;
                left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar.active + .main-content .sidebar-toggle {
            display: none;
        }

        /* Estilo para conteúdo principal */
        .main-content {
            margin-left: 300px;
            width: calc(100% - 300px);
            transition: all 0.3s ease;
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 250px;
            }
            
            .main-content {
                margin-left: 250px;
                width: calc(100% - 250px);
            }
            
            .stock-header {
                padding: 1.5rem;
                flex-direction: column;
                align-items: center;
            }
            
            .search-container {
                max-width: 100%;
                margin-top: 15px;
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -300px !important;
                z-index: 1010 !important; /* Higher than overlay */
            }
            
            .sidebar.active {
                left: 0 !important;
            }
            
            body.menu-active .main-content {
                transform: translateX(280px);
                opacity: 0.2;
                pointer-events: none;
            }
            
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
                padding-top: 60px;
                transition: all 0.3s ease;
            }
            
            .sidebar-toggle {
                display: block !important;
                z-index: 1002;
            }
            
            .stock-header {
                flex-direction: column;
                padding: 1.2rem;
                align-items: center;
            }
            
            .stock-title {
                margin-bottom: 15px;
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            .stock-title i {
                font-size: 1.5rem;
                padding: 8px;
            }
            
            .stock-title h4 {
                font-size: 1.2rem;
            }
            
            .stock-header {
                padding: 1.2rem 15px !important;
                margin-bottom: 1.5rem;
            }
            
            .search-container {
                width: 100%;
            }
            
            .ticket-info, .ticket-details, .ticket-components, 
            .ticket-actions, .ticket-timeline, .card {
                padding: 15px;
            }
            
            .component-title {
                font-size: 0.9rem;
            }
            
            .col-lg-8, .col-lg-4 {
                padding-left: 10px;
                padding-right: 10px;
            }
            
            .comment-author, .timeline-date {
                font-size: 0.9rem;
            }
            
            .ticket-container {
                padding: 0 5px;
            }
            
            .comment {
                padding: 0.8rem;
            }
        }

        @media (max-width: 375px) {
            .container-fluid {
                padding: 1.5rem 10px !important;
            }
            
            .stock-header {
                padding: 1rem 10px !important;
            }
            
            .ticket-details .card-header, 
            .card .card-header {
                padding: 10px 15px;
            }
            
            .add-comment-form {
                padding: 1.2rem;
            }
        }

        /* Estilos de header e layout */
        .stock-header {
            background: linear-gradient(135deg, #2a4d69, rgb(36, 110, 167));
            color: white;
            padding: 1.8rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            margin-top: 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            transform: translateY(0);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stock-header:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
            transform: translateY(-2px);
        }

        .stock-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stock-title i {
            font-size: 2rem;
            background-color: rgba(255, 255, 255, 0.15);
            padding: 10px;
            border-radius: 12px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .stock-title h4 {
            font-weight: 700;
            letter-spacing: 0.5px;
            margin: 0;
            font-size: 1.5rem;
            line-height: 1.2;
        }

        .stock-title p {
            font-size: 0.95rem;
            opacity: 0.85;
            letter-spacing: 0.3px;
        }
        
        .ticket-info {
            margin-bottom: 1.5rem;
            border-radius: 15px !important;
            transition: all 0.3s ease;
        }
        
        .ticket-info:hover {
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1) !important;
        }
        
        /* Estilo para sidebar e conteúdo principal */
        .sidebar {
            width: 300px;
            min-height: 100vh;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 999;
            background: linear-gradient(150deg,rgb(227, 238, 252),rgb(235, 241, 250));
            border-right: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            padding: 0;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            will-change: transform, left;
        }

        .main-content {
            margin-left: 300px;
            width: calc(100% - 300px);
            transition: all 0.3s ease;
        }
        
        .ticket-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Componentes UI aprimorados */
        .ticket-header, .ticket-details, .ticket-timeline, 
        .ticket-components, .ticket-actions, .card {
            border-radius: 15px;
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
        }
        
        .ticket-header {
            padding: 0;
            margin-bottom: 20px;
            background: transparent;
            box-shadow: none;
        }
        
        .ticket-details, .ticket-components, .ticket-actions, .ticket-timeline, .card {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .ticket-details:hover, .ticket-components:hover, 
        .ticket-actions:hover, .ticket-timeline:hover, .card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .ticket-details .card-header, 
        .card .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 15px 20px;
        }
        
        .ticket-details .card-body, 
        .card .card-body {
            padding: 20px;
        }
        
        .ticket-timeline {
            padding: 20px;
        }
        
        .ticket-components {
            padding: 20px;
        }
        
        .ticket-actions {
            padding: 0;
            border-radius: 15px;
            overflow: hidden;
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            margin-bottom: 20px;
            border: none;
        }
        
        .ticket-actions:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            transform: translateY(-3px);
        }
        
        .ticket-actions .card-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 16px 20px;
        }
        
        .ticket-actions .card-body {
            padding: 20px;
            background-color: white;
        }
        
        .ticket-actions .btn {
            padding: 12px 16px;
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 10px;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
        }
        
        .ticket-actions .btn-success {
            background: linear-gradient(145deg, #31b357, #28a745);
            border: none;
        }
        
        .ticket-actions .btn-success:hover {
            background: linear-gradient(145deg, #28a745, #218838);
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(40, 167, 69, 0.3);
        }
        
        .ticket-actions .btn-outline-primary {
            border: 2px solid #0d6efd;
            background: white;
            color: #0d6efd;
        }
        
        .ticket-actions .btn-outline-primary:hover {
            background: linear-gradient(145deg, #0d6efd, #0b5ed7);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(13, 110, 253, 0.3);
        }
        
        /* Timeline aprimorada */
        .timeline-item {
            position: relative;
            padding-left: 30px;
            padding-bottom: 25px;
            border-left: 2px solid #e9ecef;
        }
        
        .timeline-item:last-child {
            border-left: 2px solid transparent;
            padding-bottom: 0;
        }
        
        .timeline-dot {
            position: absolute;
            left: -10px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background-color: #0d6efd;
            border: 3px solid white;
            box-shadow: 0 0 0 1px #0d6efd;
        }
        
        .timeline-date {
            font-size: 0.8rem;
            color: #6c757d;
            font-weight: 500;
        }
        
        .timeline-badge {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            display: inline-block;
            margin-left: 5px;
            font-weight: 500;
        }
        
        /* Estilos para comentários */
        .comment-container {
            margin-bottom: 1.5rem;
        }
        
        .comment {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 1.2rem;
            margin-bottom: 1.2rem;
            border-left: 4px solid #007bff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .comment:hover {
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .comment-system {
            background-color: #e9f5ff;
            border-left: 4px solid #17a2b8;
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            align-items: center;
        }
        
        .comment-author {
            font-weight: 700;
            color: #0d6efd;
            display: flex;
            align-items: center;
            font-size: 1.05rem;
        }
        
        .comment-author i {
            background-color: rgba(13, 110, 253, 0.1);
            padding: 8px;
            border-radius: 50%;
            margin-right: 8px;
            color: #0d6efd;
            font-size: 0.9rem;
        }
        
        .comment-date {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .comment-content {
            white-space: pre-line;
            line-height: 1.6;
            color: #495057;
        }
        
        .add-comment-form {
            background: linear-gradient(to bottom right, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 2rem;
            margin-top: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .add-comment-form:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .add-comment-form h5 {
            color: #2a4d69;
            font-weight: 700;
            margin-bottom: 1.2rem;
            font-size: 1.2rem;
        }

        .comment-item {
            border-bottom: none;
            padding: 0;
            margin-bottom: 15px;
        }
        
        .comment-item:hover {
            background-color: transparent;
        }
        
        .comment-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        /* Estilos para status box */
        .status-box {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 600;
            border-width: 1px;
            border-style: solid;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
            min-width: 120px;
        }
        
        .status-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
        }
        
        .status-sm {
            padding: 3px 8px;
            font-size: 0.85rem;
            min-width: auto;
        }
        
        .status-aberto {
            color: #17a2b8;
            background-color: rgba(23, 162, 184, 0.1);
            border-color: #17a2b8;
        }
        
        .status-em_andamento {
            color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.1);
            border-color: #0d6efd;
        }
        
        .status-resolvido {
            color: #28a745;
            background-color: rgba(40, 167, 69, 0.1);
            border-color: #28a745;
        }
        
        .status-fechado {
            color: #6c757d;
            background-color: rgba(108, 117, 125, 0.1);
            border-color: #6c757d;
        }
        
        /* Remover estilos antigos de badges de status que não são mais necessários */
        .badge-status-aberto,
        .badge-status-em_andamento,
        .badge-status-resolvido,
        .badge-status-fechado {
            display: none;
        }
        
        /* Tabelas estilizadas */
        .table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            margin-bottom: 0;
        }
        
        .table th {
            background-color: #f8f9fa;
            padding: 12px 15px;
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
        
        .table td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }
        
        /* Input forms estilizados */
        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.6rem 1rem;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        
        /* Modais estilizados */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.2rem 1.5rem;
        }
        
        .modal-footer {
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.2rem 1.5rem;
        }
        
        .modal-body {
            padding: 1.5rem;
        }

        /* Animações e efeitos em botões gerais */
        .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .btn-outline-primary {
            color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn-outline-primary:hover {
            background-color: #0d6efd;
            color: white;
        }

        /* Responsividade específica para gerir_ticket.php */
        @media (max-width: 768px) {
            .ticket-container {
                padding: 0;
            }
            
            .ticket-details, .ticket-components, .ticket-actions, .ticket-timeline, .card {
                margin-bottom: 15px;
            }
            
            .comment-container {
                margin-bottom: 1rem;
            }
            
            .comment {
                padding: 1rem;
                margin-bottom: 1rem;
            }
            
            .add-comment-form {
                padding: 1.5rem;
            }
            
            .timeline-item {
                padding-left: 25px;
                padding-bottom: 20px;
            }
            
            .timeline-dot {
                left: -8px;
                width: 16px;
                height: 16px;
            }
            
            .ticket-info {
                padding: 15px !important;
            }
            
            .table-responsive {
                margin-bottom: 0;
            }
            
            .ticket-actions .btn {
                padding: 10px;
                margin-bottom: 8px;
            }
            
            .col-lg-8, .col-lg-4 {
                padding-left: 10px;
                padding-right: 10px;
            }
        }

        @media (max-width: 576px) {
            .container-fluid.p-4 {
                padding: 1rem !important;
            }
            
            .stock-header {
                margin-bottom: 1rem;
            }
            
            .ticket-info {
                padding: 12px !important;
            }
            
            .ticket-details .card-header, 
            .ticket-components .card-header, 
            .card .card-header {
                padding: 12px 15px;
            }
            
            .ticket-details .card-body, 
            .ticket-components .card-body, 
            .card .card-body {
                padding: 15px;
            }
            
            .comment-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .comment-date {
                margin-top: 5px;
            }
            
            .timeline-item {
                padding-left: 20px;
            }
            
            h6.fw-bold {
                font-size: 0.95rem;
            }
            
            .comment-content {
                font-size: 0.9rem;
            }
            
            .add-comment-form h5 {
                font-size: 1rem;
            }
            
            .btn {
                padding: 0.5rem 0.75rem;
                font-size: 0.9rem;
            }
        }

        /* Fixes for modal forms on mobile */
        .modal-body select.form-select, 
        .modal-body input.form-control, 
        .modal-body textarea.form-control {
            font-size: 0.95rem;
        }

        @media (max-width: 576px) {
            .modal-dialog {
                margin: 0.5rem;
            }
            
            .modal-header {
                padding: 1rem;
            }
            
            .modal-body {
                padding: 1rem;
            }
            
            .modal-footer {
                padding: 1rem;
            }
            
            .modal-body .form-label {
                font-size: 0.9rem;
            }
            
            .modal-body select.form-select, 
            .modal-body input.form-control, 
            .modal-body text.form-control {
                font-size: 0.9rem;
                padding: 0.5rem 0.8rem;
            }
            
            .modal-body .btn {
                padding: 0.5rem 0.8rem;
                font-size: 0.9rem;
            }
        }
    </style>
    <!-- Important fix for sidebar on mobile devices -->
    <style>
        @media (max-width: 768px) {
            .sidebar {
                left: -300px !important;
                z-index: 1010 !important; /* Higher than overlay */
            }
            .sidebar.active {
                left: 0 !important;
            }
        }
        
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        /* Make sure sidebar is interactive */
        body.menu-active .sidebar {
            pointer-events: auto !important;
        }
    </style>
</head>

<body>
    <!-- Botão toggle para sidebar em dispositivos móveis -->
    <button class="sidebar-toggle btn" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>

    <div class="d-flex">
        <!-- Sidebar -->
        <?php renderSidebar('manutencao', $_SESSION['usuario_tipo'], $usuario['nome_completo'], $usuario['foto_perfil']); ?>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <div class="container-fluid p-4">
                
                <!-- Alertas -->
                <?php if (isset($_SESSION['erro'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['erro']; unset($_SESSION['erro']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['sucesso'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['sucesso']; unset($_SESSION['sucesso']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <!-- Cabeçalho do Ticket -->
                <div class="ticket-header mb-4">
                    <div class="stock-header">
                        <div class="stock-title">
                            <i class="bi bi-ticket-detailed"></i>
                            <div>
                                <h4 class="mb-1">Ticket <?php echo $ticket['id']; ?></h4>
                                <p class="mb-0 text-light"><?php echo htmlspecialchars($ticket['assunto']); ?></p>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex gap-2">
                                <a href="manutencao.php" class="btn btn-light">
                                    <i class="bi bi-arrow-left me-1"></i>Voltar
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="ticket-info bg-white p-4 rounded-3 shadow-sm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <p class="text-muted mb-1">Aberto por: <strong><?php echo htmlspecialchars($ticket['solicitante_nome']); ?></strong></p>
                                    <p class="text-muted mb-1">Data: <strong><?php echo date('d/m/Y H:i', strtotime($ticket['criado_em'])); ?></strong></p>
                                    <p class="text-muted mb-0">Última atualização: <strong><?php echo date('d/m/Y H:i', strtotime($ticket['ultima_atualizacao'])); ?></strong></p>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="d-flex flex-column align-items-md-end">
                                    <div class="mb-2">
                                        <span class="status-box status-<?php echo $ticket['status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                        </span>
                                    </div>
                                    <p class="mb-1">
                                        <strong>Prioridade:</strong> 
                                        <span class="<?php 
                                            if ($ticket['prioridade'] == '1') echo 'text-primary';
                                            else if ($ticket['prioridade'] == '2') echo 'text-warning fw-bold';
                                            else echo 'text-danger fw-bold';
                                        ?>">
                                            <?php echo $ticket['prioridade'] == '1' ? 'Baixa' : ($ticket['prioridade'] == '2' ? 'Média' : 'Alta'); ?>
                                        </span>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Sala/Local:</strong> <?php echo htmlspecialchars($ticket['sala_nome'] ?? $ticket['sala']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Conteúdo Principal do Ticket -->
                <div class="row">
                    <!-- Coluna Esquerda - Detalhes e Comentários -->
                    <div class="col-lg-8 mb-4">
                        <!-- Detalhes do Ticket -->
                        <div class="ticket-details mb-4">
                            <div class="card-header d-flex align-items-center">
                                <i class="bi bi-info-circle me-2 text-primary"></i>
                                <h5 class="mb-0">Detalhes do Ticket</h5>
                            </div>
                            <div class="card-body">
                                <div class="mt-1">
                                    <h6 class="fw-bold">Descrição do Problema:</h6>
                                    <p class="mb-4"><?php echo nl2br(htmlspecialchars($ticket['descricao'])); ?></p>
                                
                                <?php if (!empty($ticket['mensagem_erro'])): ?>
                                    <h6 class="fw-bold">Mensagem de Erro:</h6>
                                    <p class="bg-light p-3 rounded mb-4"><?php echo nl2br(htmlspecialchars($ticket['mensagem_erro'])); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($ticket['observacoes_adicionais'])): ?>
                                    <h6 class="fw-bold">Observações Adicionais:</h6>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($ticket['observacoes_adicionais'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                        <!-- Componentes Associados -->
                        <?php if (!empty($componentes)): ?>
                        <div class="card shadow-sm mb-4">
                            <div class="card-header d-flex align-items-center">
                                <i class="bi bi-cpu me-2 text-primary"></i>
                                <h5 class="mb-0">Componentes Relacionados</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Equipamento</th>
                                                <th>Referência</th>
                                                <th class="text-center">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($componentes as $componente): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($componente['equipamento']); ?></td>
                                                <td><?php echo htmlspecialchars($componente['ref_equipamento_texto'] ?? $componente['ref_equipamento']); ?></td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#componenteModal" data-componente-id="<?php echo $componente['id']; ?>" data-componente-tipo="<?php echo htmlspecialchars($componente['equipamento']); ?>" data-componente-ref="<?php echo htmlspecialchars($componente['ref_equipamento_texto'] ?? $componente['ref_equipamento']); ?>">
                                                        <i class="bi bi-tools me-1"></i>Gerir
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Comentários do Ticket -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header d-flex align-items-center">
                                <i class="bi bi-chat-dots me-2 text-primary"></i>
                                <h5 class="mb-0">Comentários (<?php echo count($comentarios); ?>)</h5>
                            </div>
                            <div class="card-body p-3">
                                <?php if (empty($comentarios)): ?>
                                <div class="p-4 text-center">
                                    <i class="bi bi-chat-square text-muted" style="font-size: 2rem;"></i>
                                    <p class="mt-3 mb-0 text-muted">Nenhum comentário ainda</p>
                                    <small class="text-muted">Seja o primeiro a comentar</small>
                                </div>
                                <?php else: ?>
                                <div class="comment-container">
                                    <?php foreach ($comentarios as $comentario): ?>
                                    <div class="comment <?php echo isset($comentario['is_system']) && $comentario['is_system'] ? 'comment-system' : ''; ?>">
                                        <div class="comment-header">
                                            <span class="comment-author">
                                                <i class="bi bi-person"></i>
                                                <?php echo htmlspecialchars($comentario['nome_autor'] ?? 'Técnico'); ?>
                                            </span>
                                            <span class="comment-date">
                                                <?php echo date('d/m/Y H:i', strtotime($comentario['data_comentario'])); ?>
                                            </span>
                                        </div>
                                        <div class="comment-content">
                                            <?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Formulário de Comentário -->
                                <div class="add-comment-form mt-4">
                                    <h5>Adicionar Comentário</h5>
                                    <form action="gerir_ticket.php?id=<?php echo $ticket_id; ?>" method="POST">
                                        <input type="hidden" name="acao" value="adicionar_comentario">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                                        <div class="mb-3">
                                            <label for="comentario" class="form-label fw-bold">Escreve aqui</label>
                                            <textarea class="form-control" id="comentario" name="comentario" rows="3" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-send me-1"></i>Enviar Comentário
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Coluna Direita - Ações e Timeline -->
                    <div class="col-lg-4">
                        <!-- Ações do Ticket -->
                        <div class="ticket-actions mb-4">
                            <div class="card-header d-flex align-items-center">
                                <i class="bi bi-gear me-2 text-primary"></i>
                                <h5 class="mb-0">Ações do Ticket</h5>
                            </div>
                            <div class="card-body">
                                <!-- Ações Rápidas -->
                                <div class="d-grid gap-2">
                                    <?php if ($ticket['status'] !== 'resolvido'): ?>
                                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#resolverModal">
                                        <i class="bi bi-check-circle me-1"></i>Marcar como Resolvido
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($ticket['status'] !== 'fechado'): ?>
                                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#agendarModal">
                                        <i class="bi bi-calendar-event me-1"></i>Agendar Manutenção
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Timeline do Ticket -->
                        <div class="ticket-timeline">
                            <div class="card-header d-flex align-items-center">
                                <i class="bi bi-clock-history me-2 text-primary"></i>
                                <h5 class="mb-0">Histórico do Ticket</h5>
                            </div>
                            <div class="card-body p-3">
                                <?php if (empty($historico)): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-clock text-muted" style="font-size: 2rem;"></i>
                                    <p class="mt-3 mb-0 text-muted">Nenhum histórico disponível</p>
                                </div>
                                <?php else: ?>
                                <div class="timeline">
                                    <?php foreach ($historico as $hist): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-dot"></div>
                                        <div class="timeline-date"><?php echo date('d/m/Y H:i', strtotime($hist['data_acao'])); ?></div>
                                        <div class="mt-1 mb-2">
                                            <strong><?php echo htmlspecialchars($hist['nome_tecnico']); ?></strong>
                                            <?php 
                                            $badge_class = "";
                                            $badge_text = "";
                                            
                                            switch ($hist['acao']) {
                                                case 'status_alterado':
                                                    $badge_class = "bg-info";
                                                    $badge_text = "Estado Alterado";
                                                    break;
                                                case 'ticket_resolvido':
                                                    $badge_class = "bg-success";
                                                    $badge_text = "Resolvido";
                                                    break;
                                                case 'componente_manutencao':
                                                    $badge_class = "bg-warning text-dark";
                                                    $badge_text = "Componente em Manutenção";
                                                    break;
                                                case 'componente_sem_reparo':
                                                    $badge_class = "bg-danger";
                                                    $badge_text = "Sem Reparo";
                                                    break;
                                                case 'componente_em_uso':
                                                    $badge_class = "bg-success";
                                                    $badge_text = "Em Uso";
                                                    break;
                                                case 'manutencao_agendada':
                                                    $badge_class = "bg-primary";
                                                    $badge_text = "Agendamento";
                                                    break;
                                                default:
                                                    $badge_class = "bg-secondary";
                                                    $badge_text = ucfirst(str_replace('_', ' ', $hist['acao']));
                                            }
                                            ?>
                                            <span class="timeline-badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
                                        </div>
                                        <?php if ($hist['status_anterior'] && $hist['status_novo']): ?>
                                        <div class="mt-1 small">
                                            <span class="status-box status-<?php echo $hist['status_anterior']; ?> status-sm">
                                                <?php echo ucfirst(str_replace('_', ' ', $hist['status_anterior'])); ?>
                                            </span>
                                            <i class="bi bi-arrow-right mx-1"></i>
                                            <span class="status-box status-<?php echo $hist['status_novo']; ?> status-sm">
                                                <?php echo ucfirst(str_replace('_', ' ', $hist['status_novo'])); ?>
                                            </span>
                                        </div>
                                        <?php 
                                        // Hide description text when it's just a status change notification
                                        elseif ($hist['descricao'] && !stripos($hist['descricao'], 'status alterado de')):
                                        ?>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($hist['descricao'])); ?></p>
                                        <?php else: ?>
                                        <!-- Status change without previous/new status fields, skipping description display -->
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
                
    <!-- Modal: Resolver Ticket -->
    <div class="modal fade" id="resolverModal" tabindex="-1" aria-labelledby="resolverModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="resolverModalLabel">
                        <i class="bi bi-check-circle-fill me-2 text-success"></i>Resolver Ticket
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="gerir_ticket.php?id=<?php echo $ticket_id; ?>" method="POST">
                    <input type="hidden" name="acao" value="resolver_ticket">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="solucao_resolvido" class="form-label fw-bold">Descreva a solução aplicada</label>
                            <textarea class="form-control" id="solucao_resolvido" name="solucao" rows="4" required></textarea>
                            <small class="text-muted">Informe detalhadamente como o problema foi resolvido</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i>Resolver Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
                
    <!-- Modal: Agendar Manutenção -->
    <div class="modal fade" id="agendarModal" tabindex="-1" aria-labelledby="agendarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="agendarModalLabel">
                        <i class="bi bi-calendar-event me-2 text-primary"></i>Agendar Manutenção
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="gerir_ticket.php?id=<?php echo $ticket_id; ?>" method="POST">
                    <input type="hidden" name="acao" value="agendar_manutencao">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="data_agenda" class="form-label fw-bold">Data e Hora Agendada</label>
                            <input type="datetime-local" class="form-control" id="data_agenda" name="data_agenda" required>
                        </div>
                        <div class="mb-3">
                            <label for="descricao_agenda" class="form-label fw-bold">Detalhes do Agendamento</label>
                            <textarea class="form-control" id="descricao_agenda" name="descricao_agenda" rows="3" required></textarea>
                            <small class="text-muted">Informe os detalhes do agendamento (local, motivo, etc.)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-calendar-check me-1"></i>Agendar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal: Gerir Componente -->
    <div class="modal fade" id="componenteModal" tabindex="-1" aria-labelledby="componenteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="componenteModalLabel">
                        <i class="bi bi-tools me-2 text-primary"></i>Gerir Componente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4 p-3 bg-light rounded">
                        <p class="mb-2"><strong><i class="bi bi-pc-display me-2"></i>Equipamento:</strong> <span id="componente-tipo-texto"></span></p>
                        <p class="mb-0"><strong><i class="bi bi-tag me-2"></i>Referência:</strong> <span id="componente-ref-texto"></span></p>
                    </div>
                    
                    <form action="gerir_ticket.php?id=<?php echo $ticket_id; ?>" method="POST" id="componente-em-manutencao-form" class="mb-3">
                        <input type="hidden" name="acao" value="componente_em_manutencao">
                        <input type="hidden" name="componente_id" id="componente-id-1">
                        <button type="submit" class="btn btn-warning mb-2 w-100">
                            <i class="bi bi-tools me-1"></i>Marcar como "Em Manutenção"
                        </button>
                    </form>
                    
                    <form action="gerir_ticket.php?id=<?php echo $ticket_id; ?>" method="POST" id="componente-em-uso-form" class="mb-3">
                        <input type="hidden" name="acao" value="componente_em_uso">
                        <input type="hidden" name="componente_id" id="componente-id-2">
                        <div class="mb-2">
                            <label for="sala" class="form-label fw-bold">Sala/Local de Destino</label>
                            <select class="form-select" id="sala" name="sala" required>
                                <option value="">Selecione uma sala...</option>
                                <?php foreach ($salas as $sala): ?>
                                <option value="<?php echo $sala['sala']; ?>"><?php echo htmlspecialchars($sala['sala']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success mb-2 w-100">
                            <i class="bi bi-check-circle me-1"></i>Marcar como "Em Uso" (Reparado)
                        </button>
                    </form>
                    
                    <form action="gerir_ticket.php?id=<?php echo $ticket_id; ?>" method="POST" id="componente-sem-reparo-form">
                        <input type="hidden" name="acao" value="componente_sem_reparo">
                        <input type="hidden" name="componente_id" id="componente-id-3">
                        <div class="mb-2">
                            <label for="motivo_sem_reparo" class="form-label fw-bold">Motivo da Inviabilidade de Reparo</label>
                            <textarea class="form-control" id="motivo_sem_reparo" name="motivo" rows="2" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-x-circle me-1"></i>Marcar como "Sem Reparo" (Abate)
                        </button>
                    </form>
                    
                    <!-- Botão para gerir componentes de computador - será mostrado apenas para computadores -->
                    <div id="gerenciar-computador-container" class="mt-4 pt-3 border-top" style="display: none;">
                        <a id="link-detalhes-pc" href="#" class="btn btn-primary w-100">
                            <i class="bi bi-pc-display me-1"></i>Gerir Todos os Componentes do PC
                        </a>
                        <small class="text-muted d-block mt-1 text-center">Gerencie todos os componentes associados a este computador</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Forçar o estado inicial correto do sidebar em dispositivos móveis
                const sidebar = document.querySelector('.sidebar');
                const toggleButton = document.querySelector('.sidebar-toggle');
                
            // Forçar o estado inicial em dispositivos móveis
            if (window.innerWidth <= 768) {
                sidebar.style.left = '-300px';
                sidebar.classList.remove('active');
                document.body.classList.remove('menu-active');
                toggleButton.style.display = 'block';
                
                // Remover qualquer overlay existente
                const existingOverlay = document.querySelector('.sidebar-overlay');
                if (existingOverlay) existingOverlay.remove();
            }
            
            // Função para ajustar o botão de toggle com base no tamanho da tela
            function checkToggleDisplay() {
                if (window.innerWidth <= 768) {
                    // Em dispositivos móveis, mostra o botão se o menu não estiver aberto
                    if (!sidebar.classList.contains('active')) {
                        toggleButton.style.display = 'block';
                    }
                } else {
                    // Em dispositivos maiores, esconde o botão
                    toggleButton.style.display = 'none';
                }
            }
            
            // Verificar na carga inicial e quando redimensionar
            checkToggleDisplay();
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('active');
                    document.body.classList.remove('menu-active');
                    const overlay = document.querySelector('.sidebar-overlay');
                    if (overlay) overlay.remove();
                }
                checkToggleDisplay();
        });

        // Inicializar os modais de componentes
        const componenteModal = document.getElementById('componenteModal');
        if (componenteModal) {
            componenteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const componenteId = button.getAttribute('data-componente-id');
                const componenteTipo = button.getAttribute('data-componente-tipo');
                const componenteRef = button.getAttribute('data-componente-ref');
                
                document.getElementById('componente-tipo-texto').textContent = componenteTipo;
                document.getElementById('componente-ref-texto').textContent = componenteRef;
                document.getElementById('componente-id-1').value = componenteId;
                document.getElementById('componente-id-2').value = componenteId;
                document.getElementById('componente-id-3').value = componenteId;
                
                // Verificar se o componente é um computador
                const gerenciarComputadorContainer = document.getElementById('gerenciar-computador-container');
                const linkDetalhesPc = document.getElementById('link-detalhes-pc');
                
                if (componenteTipo.toLowerCase().includes('computador')) {
                    // Mostrar o botão de gerir componentes e configurar o link
                    gerenciarComputadorContainer.style.display = 'block';
                    
                    // Extrair a referência do componente (geralmente é o ID no sistema do computador)
                    let compRef = componenteRef;
                    
                    // Obter o URL completo e correto para a página de detalhes do PC
                    fetch(`funcoes/get_computer_id.php?ref=${encodeURIComponent(compRef)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.id) {
                                linkDetalhesPc.href = `../computadores/detalhespc.php?id=${data.id}`;
                            } else {
                                console.error('Erro ao obter ID do computador:', data.message || 'ID não encontrado');
                                // Fallback - tentar usar o componente ID diretamente
                                linkDetalhesPc.href = `../computadores/detalhespc.php?id=${componenteId}`;
                            }
                        })
                        .catch(error => {
                            console.error('Erro ao buscar ID do computador:', error);
                            // Fallback
                            linkDetalhesPc.href = `../computadores/detalhespc.php?id=${componenteId}`;
                        });
                } else {
                    // Esconder o botão para outros tipos de componentes
                    gerenciarComputadorContainer.style.display = 'none';
                }
            });
        }
        });
        
        // Função para toggle da sidebar em dispositivos móveis
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const toggleButton = document.querySelector('.sidebar-toggle');
            const body = document.body;
            
            sidebar.classList.toggle('active');
            
            // Se o sidebar estiver ativo, esconde o botão de toggle e mostra o overlay
            if (sidebar.classList.contains('active')) {
                // Esconde imediatamente o botão de toggle
                toggleButton.style.display = 'none';
                
                // Adiciona classe ao body para controlar o conteúdo principal
                body.classList.add('menu-active');
                
                // Remover qualquer overlay existente antes de criar um novo
                const existingOverlay = document.querySelector('.sidebar-overlay');
                if (existingOverlay) existingOverlay.remove();
                
                // Cria e adiciona o overlay
                const overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                overlay.style.position = 'fixed';
                overlay.style.top = '0';
                overlay.style.left = '0';
                overlay.style.width = '100%';
                overlay.style.height = '100%';
                overlay.style.background = 'rgba(0,0,0,0.7)';
                overlay.style.zIndex = '1000';
                overlay.style.transition = 'all 0.3s ease';
                
                // Importante: inserir o overlay como primeiro filho do body
                // para que fique atrás do sidebar
                document.body.insertBefore(overlay, document.body.firstChild);
                
                // Evento de clique para o overlay
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    body.classList.remove('menu-active');
                    this.remove();
                    // Mostra o botão de toggle novamente
                    setTimeout(() => {
                        toggleButton.style.display = 'block';
                    }, 50);
                });
            } else {
                // Remove classe do body
                body.classList.remove('menu-active');
                
                // Esconde o overlay se existir
                const overlay = document.querySelector('.sidebar-overlay');
                if (overlay) overlay.remove();
                
                // Certifica-se de que o botão de toggle está visível
                setTimeout(() => {
                toggleButton.style.display = 'block';
                }, 300);
            }
        }
    </script>
</body>
</html> 