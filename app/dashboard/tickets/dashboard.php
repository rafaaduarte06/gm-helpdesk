<?php
// Habilitar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar a sessão antes de qualquer output
session_start();

// Depuração da sessão
error_log("Sessão: " . print_r($_SESSION, true));

// Verificar se o usuário está logado - aceitar diferentes formatos de sessão
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
require_once '../../ligacao_base_dados.php';
require_once 'funcoes/tickets_funcoes.php';

// Buscar dados do usuário
try {
    $stmt = $pdo->prepare("SELECT * FROM suporte_tecnico WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        // Se não encontrar o usuário, criar um array com dados padrão
        $usuario = [
            'id' => $_SESSION['usuario_id'],
            'nome_completo' => 'Utilizador',
            'foto_perfil' => 'default.jpg'
        ];
    }
} catch (Exception $e) {
    // Se der erro, criar um array com dados padrão
    $usuario = [
        'id' => $_SESSION['usuario_id'],
        'nome_completo' => 'Utilizador',
        'foto_perfil' => 'default.jpg'
    ];
    error_log("Aviso: " . $e->getMessage());
}

// Obter todos os técnicos de suporte para o filtro
try {
    $stmt = $pdo->prepare("SELECT id, nome_completo FROM suporte_tecnico ORDER BY nome_completo");
    $stmt->execute();
    $tecnicos = $stmt->fetchAll();
} catch (Exception $e) {
    $tecnicos = [];
    error_log("Erro ao carregar técnicos: " . $e->getMessage());
}

// Obter tickets do sistema
try {
    // Para técnicos, obter todos os tickets
    // Para usuários normais, obter apenas seus próprios tickets
    $tickets = obterTickets($pdo, null, '', '', 0);
    
    // Calcular estatísticas
    $total_tickets = count($tickets);
    $tickets_abertos = 0;
    $tickets_em_andamento = 0;
    $tickets_resolvidos = 0;
    $tickets_fechados = 0;
    $tickets_urgentes = 0;
    
    foreach ($tickets as $ticket) {
        if ($ticket['status'] === 'aberto') {
            $tickets_abertos++;
        } elseif ($ticket['status'] === 'em_andamento') {
            $tickets_em_andamento++;
        } elseif ($ticket['status'] === 'resolvido') {
            $tickets_resolvidos++;
        } elseif ($ticket['status'] === 'fechado') {
            $tickets_fechados++;
        }
        
        if ($ticket['prioridade'] === 'alta') {
            $tickets_urgentes++;
        }
    }
} catch (Exception $e) {
    die("Erro ao carregar tickets: " . $e->getMessage());
}

// Incluir o sidebar
include_once '../../includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard de Tickets - GM HelpDesk</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
  <!-- Toastify CSS -->
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../../assets/css/variables.css">
  <link rel="stylesheet" href="../../assets/css/custom.css">
  
  <style>
    /* Estilo de fundo gradiente */
    body {
        min-height: 100vh;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        margin: 0;
        padding: 0;
        overflow-x: hidden;
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
        z-index: 1000;
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

    .sidebar.active + .main-content .sidebar-toggle {
        display: none;
    }

    /* Conteúdo principal */
    .main-content {
        margin-left: 300px;
        width: calc(100% - 300px);
        transition: all 0.3s ease;
    }

    /* Estilos do cabeçalho (stock header) */
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

    /* Estilo para o botão de criar ticket */
    .btn-create-ticket {
        padding: 10px 25px;
        font-weight: 600;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.2);
        transition: all 0.3s ease;
        white-space: nowrap;
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-create-ticket:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(13, 110, 253, 0.3);
    }

    /* Estilos para o container de pesquisa */
    .search-container {
        width: 300px;
        max-width: 100%;
    }

    .search-input-group {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        height: 45px;
    }

    .search-input-group:focus-within {
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .search-icon-container {
        background-color: white;
        border-right: none;
        padding-left: 15px;
        color: #4b86b4;
        height: 45px;
        padding-top: 10px;
        padding-bottom: 10px;
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }

    .search-input {
        border-left: none;
        padding-left: 5px;
        box-shadow: none !important;
        height: 45px;
        padding-top: 10px;
        padding-bottom: 10px;
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }

    .search-input:focus {
        border-color: #ced4da;
    }

    /* Estilos para cards de componentes */
    .component-card {
        transition: all 0.3s ease;
        height: 100%;
        background: white;
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
        text-align: center;
        position: relative;
    }

    .component-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }

    .component-icon {
        font-size: 2.5rem;
        margin-bottom: 1.2rem;
        color: #2a4d69;
        background-color: rgba(75, 134, 180, 0.1);
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.2rem auto;
        transition: all 0.3s ease;
    }

    .component-card:hover .component-icon {
        background-color: rgba(75, 134, 180, 0.2);
        transform: scale(1.1);
    }

    .component-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.7rem;
        color: #2c3e50;
    }

    .stock-counter {
        font-size: 1.2rem;
        font-weight: 600;
        padding: 5px 15px;
        border-radius: 8px;
        display: inline-block;
        margin-top: 8px;
        border: 1px solid;
    }

    /* Estilos para tickets conteúdo */
    .tickets-content {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .filters-container {
        padding: 1.2rem;
        background: rgba(0, 0, 0, 0.02);
        border-radius: 10px;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .ticket-table-container {
        margin-top: 1rem;
    }

    /* Estilização adicional para a tabela de tickets */
    #ticketsTable {
        border-collapse: separate;
        border-spacing: 0;
    }

    #ticketsTable thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #495057;
        padding: 12px 15px;
        border-bottom: 2px solid #e9ecef;
    }

    #ticketsTable tbody tr {
        transition: all 0.2s ease;
    }

    #ticketsTable tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    #ticketsTable td {
        padding: 12px 15px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }

    /* Melhorar aparência dos badges de status */
    .status-aberto, .status-em-andamento, .status-concluido, .status-cancelado, .status-fechado {
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
        min-width: 100px;
        text-align: center;
        border-width: 1.5px;
        border-style: solid;
    }

    .status-aberto {
        background-color: rgba(233, 130, 21, 0.1);
        color: #e98215;
        border-color: #e98215;
    }

    .status-em-andamento {
        background-color: rgba(13, 110, 253, 0.15);
        color: #0d6efd;
        border-color: #0d6efd;
    }

    .status-concluido {
        background-color: rgba(25, 135, 84, 0.15);
        color: #198754;
        border-color: #198754;
    }

    .status-fechado {
        background-color: rgba(108, 117, 125, 0.15);
        color: #6c757d;
        border-color: #6c757d;
    }

    .status-cancelado {
        background-color: rgba(220, 53, 69, 0.15);
        color: #dc3545;
        border-color: #dc3545;
    }

    /* Cor personalizada para prioridade média */
    .text-orange {
        color: #e98215 !important;
    }

    /* Prioridade badges */
    .prioridade-badge {
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
        min-width: 80px;
        text-align: center;
        border-width: 1.5px;
        border-style: solid;
    }

    .prioridade-baixa {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
        border-color: #0d6efd;
    }

    .prioridade-media {
        background-color: rgba(233, 130, 21, 0.1);
        color: #e98215;
        border-color: #e98215;
    }

    .prioridade-alta {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border-color: #dc3545;
    }

    /* Ocultar componentes DataTables que não estamos usando */
    .dataTables_filter, .dataTables_info, .dataTables_length, .dataTables_paginate {
        display: none !important;
    }

    /* Classe para tabela responsiva em dispositivos móveis */
    .table-responsive-custom {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Estilos para badges de responsável */
    .resp-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        border-width: 1.5px;
        border-style: solid;
    }

    .resp-badge-you {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
        border-color: #198754;
    }

    .resp-badge-tech {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
        border-color: #0d6efd;
    }

    .resp-badge-none {
        background-color: rgba(233, 130, 21, 0.1);
        color: #e98215;
        border-color: #e98215;
    }

    /* Estilos para botões de ação */
    .btn-action {
        border-width: 1.5px;
        border-style: solid;
        padding: 6px 10px;
        transition: all 0.2s ease;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 5px rgba(0, 0, 0, 0.1);
    }

    .btn-view {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
        border-color: #0d6efd;
    }

    .btn-view:hover {
        background-color: rgba(13, 110, 253, 0.2);
        color: #0d6efd;
    }

    .btn-take {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
        border-color: #198754;
    }

    .btn-take:hover {
        background-color: rgba(25, 135, 84, 0.2);
        color: #198754;
    }

    .btn-cancel {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border-color: #dc3545;
    }

    .btn-cancel:hover {
        background-color: rgba(220, 53, 69, 0.2);
        color: #dc3545;
    }

    /* Responsividade aprimorada */
    @media (max-width: 1200px) {
        .stock-header {
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .search-container {
            width: 250px;
        }
        
        .btn-create-ticket {
            padding: 10px 15px;
        }
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
            align-items: flex-start;
        }
        
        .d-flex.align-items-center.gap-3 {
            margin-top: 15px;
            width: 100%;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 10px !important;
        }
        
        .search-container {
            width: 60%;
            margin-right: 0;
        }
        
        .btn-create-ticket {
            width: 35%;
            padding: 10px;
            font-size: 0.9rem;
            white-space: normal;
            text-align: center;
            justify-content: center;
        }
        
        /* Ajustes para a tabela */
        .ticket-table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        #ticketsTable {
            min-width: 900px;
        }
        
        .filters-container .row {
            margin-right: 0;
            margin-left: 0;
        }
    }

    @media (max-width: 768px) {
        .sidebar {
            left: -300px;
            transition: all 0.3s ease;
            width: 280px;
            z-index: 1001;
        }
        
        .sidebar.active {
            left: 0;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.3);
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
        }
        
        .stock-header {
            padding: 1.2rem;
        }
        
        .search-container {
            margin-top: 15px;
            width: 100%;
        }
    }

    /* Estilos responsivos para tabela de tickets em dispositivos móveis */
    @media (max-width: 767px) {
      #ticketsTable {
        min-width: auto !important;
      }
      
      #ticketsTable thead {
        display: none; /* Oculta o cabeçalho da tabela */
      }
      
      #ticketsTable, 
      #ticketsTable tbody, 
      #ticketsTable tr {
        display: block;
        width: 100%;
      }
      
      #ticketsTable tr {
        margin-bottom: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        background: white;
        overflow: hidden;
        position: relative;
        border-left: 4px solid #2a4d69;
        transition: all 0.3s ease;
      }
      
      #ticketsTable tr:hover {
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
        transform: translateY(-3px);
        border-left: 4px solid #0a4da0;
      }
      
      #ticketsTable td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px !important;
        text-align: right !important;
        border-bottom: 1px solid rgba(0,0,0,0.05);
      }
      
      #ticketsTable td:last-child {
        border-bottom: none;
        justify-content: center;
        padding-top: 20px !important;
        padding-bottom: 20px !important;
        background-color: rgba(42, 77, 105, 0.05);
      }
      
      #ticketsTable td:before {
        content: attr(data-label);
        font-weight: 600;
        color: #2a4d69;
        text-align: left;
        padding-right: 10px;
        float: left;
      }
      
      /* Adiciona uma leve animação de brilho na borda */
      @keyframes borderGlow {
        0% { border-left-color: #2a4d69; }
        50% { border-left-color: #0a4da0; }
        100% { border-left-color: #2a4d69; }
      }
      
      #ticketsTable tr {
        animation: borderGlow 3s infinite;
      }
      
      /* Ajuste para o número do ticket ficar junto */
      .ticket-number {
        display: inline-block;
        font-weight: 700;
        color: #2a4d69;
      }
      
      #ticketsTable .ticket-actions-col {
        justify-content: center !important;
      }

      #ticketsTable .ticket-actions-col:before {
        display: none; /* Hide "Ações" label above action buttons */
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
            padding: 1rem 15px !important;
            margin-bottom: 1.5rem;
            flex-direction: column;
        }
        
        .component-card {
            padding: 1rem;
        }
        
        .component-icon {
            width: 50px;
            height: 50px;
            font-size: 1.8rem;
        }
        
        .component-title {
            font-size: 0.9rem;
        }
        
        .stock-counter {
            font-size: 1rem;
            padding: 3px 10px;
        }
        
        /* Filtros em tela pequena */
        .filters-container .col-sm-6 {
            padding: 0 5px;
        }
        
        .filters-container label {
            font-size: 0.8rem;
        }
        
        .filters-container select {
            font-size: 0.9rem;
            padding: 0.4rem 0.5rem;
        }
        
        /* Ajustes para botões de ação na tabela */
        .btn-action {
            padding: 3px 6px;
        }
        
        .btn-action i {
            font-size: 0.85rem;
        }

        .btn-create-ticket, .search-container {
            width: 100%;
            margin-bottom: 10px;
        }
    }

    /* Estilos para o modal de alerta personalizado */
    @media (max-width: 375px) {
        .container-fluid {
            padding: 1.5rem 15px !important;
        }
        
        .stock-header {
            padding: 1.2rem 15px !important;
        }
    }
  </style>
  
  <!-- Estilo para o popup de confirmação -->
  <style>
    .success-popup {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: white;
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      z-index: 1050;
      width: 320px;
      text-align: center;
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    
    .success-popup.show {
      opacity: 1;
      animation: popupFadeIn 0.5s ease forwards;
    }
    
    @keyframes popupFadeIn {
      0% {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0.7);
      }
      100% {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
      }
    }
    
    .success-popup .check-icon {
      position: relative;
      width: 80px;
      height: 80px;
      margin: 0 auto 20px;
      border-radius: 50%;
      background-color: rgba(40, 167, 69, 0.1);
      border: 3px solid #28a745;
      box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
      animation: pulseIcon 0.6s ease-out;
    }
    
    @keyframes pulseIcon {
      0% {
        transform: scale(0);
        opacity: 0;
      }
      50% {
        transform: scale(1.1);
        opacity: 0.7;
      }
      100% {
        transform: scale(1);
        opacity: 1;
      }
    }
    
    .success-popup .check-icon svg {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 40px;
      height: 40px;
    }
    
    .success-popup .check-icon svg path {
      stroke-dasharray: 80;
      stroke-dashoffset: 80;
      animation: drawCheck 0.6s ease-in-out forwards 0.3s;
    }
    
    @keyframes drawCheck {
      0% {
        stroke-dashoffset: 80;
      }
      100% {
        stroke-dashoffset: 0;
      }
    }
    
    .success-popup .message {
      font-size: 18px;
      color: #333;
      font-weight: 600;
      margin-bottom: 15px;
    }
    
    .success-popup .ticket-id {
      font-size: 20px;
      font-weight: 700;
      color: #28a745;
      margin-bottom: 20px;
    }
    
    .popup-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1040;
      opacity: 0;
      transition: opacity 0.3s ease;
    }
    
    .popup-overlay.show {
      opacity: 1;
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
    <?php renderSidebar('dashboard', $_SESSION['usuario_tipo'], $usuario['nome_completo'], $usuario['foto_perfil']); ?>

    <!-- Conteúdo Principal -->
    <main class="main-content">
      <div class="container-fluid py-4">
        <!-- Cabeçalho da página com estilo semelhante ao inventario.php -->
        <div class="stock-header">
          <div class="stock-title">
            <i class="bi bi-ticket-detailed"></i>
            <div>
              <h4>Dashboard de Tickets</h4>
              <p class="mb-0 mt-1 text-light">Total de Tickets: <span class="fw-bold"><?php echo $total_tickets; ?></span></p>
            </div>
          </div>
          <div class="d-flex align-items-center gap-3">
            <div class="search-container">
              <div class="input-group search-input-group">
                <span class="input-group-text search-icon-container">
                  <i class="bi bi-search"></i>
                </span>
                <input type="text" 
                       class="form-control search-input" 
                       id="ticketSearch" 
                       placeholder="Pesquisar ticket...">
              </div>
            </div>
            <a href="tickets.php" class="btn btn-light btn-create-ticket">
              <i class="bi bi-plus-circle me-2"></i>Criar Novo Ticket
            </a>
          </div>
        </div>
        
        <!-- Cards de estatísticas no estilo mais visual -->
        <div class="row mb-4 g-4">
          <div class="col-xl col-md-6">
            <div class="component-card">
              <div class="component-icon" style="color: #e98215; background-color: rgba(233, 130, 21, 0.1);">
                <i class="bi bi-hourglass-split"></i>
              </div>
              <h5 class="component-title">Tickets Pendentes</h5>
              <div class="stock-counter" style="background-color: rgba(233, 130, 21, 0.1); color: #e98215; border-color: #e98215;">
                <?php echo $tickets_abertos; ?>
              </div>
            </div>
          </div>
          <div class="col-xl col-md-6">
            <div class="component-card">
              <div class="component-icon" style="color: #0d6efd; background-color: rgba(13, 110, 253, 0.1);">
                <i class="bi bi-arrow-repeat"></i>
              </div>
              <h5 class="component-title">Em Andamento</h5>
              <div class="stock-counter" style="background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; border-color: #0d6efd;">
                <?php echo $tickets_em_andamento; ?>
              </div>
            </div>
          </div>
          <div class="col-xl col-md-6">
            <div class="component-card">
              <div class="component-icon" style="color: #198754; background-color: rgba(25, 135, 84, 0.1);">
                <i class="bi bi-check-circle"></i>
              </div>
              <h5 class="component-title">Tickets Resolvidos</h5>
              <div class="stock-counter" style="background-color: rgba(25, 135, 84, 0.1); color: #198754; border-color: #198754;">
                <?php echo $tickets_resolvidos; ?>
              </div>
            </div>
          </div>
          <div class="col-xl col-md-6">
            <div class="component-card">
              <div class="component-icon" style="color: #6c757d; background-color: rgba(108, 117, 125, 0.1);">
                <i class="bi bi-lock"></i>
              </div>
              <h5 class="component-title">Tickets Fechados</h5>
              <div class="stock-counter" style="background-color: rgba(108, 117, 125, 0.1); color: #6c757d; border-color: #6c757d;">
                <?php echo $tickets_fechados; ?>
              </div>
            </div>
          </div>
          <div class="col-xl col-md-6">
            <div class="component-card">
              <div class="component-icon" style="color: #dc3545; background-color: rgba(220, 53, 69, 0.1);">
                <i class="bi bi-alarm"></i>
              </div>
              <h5 class="component-title">Tickets Urgentes</h5>
              <div class="stock-counter" style="background-color: rgba(220, 53, 69, 0.1); color: #dc3545; border-color: #dc3545;">
                <?php echo $tickets_urgentes; ?>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Filtros e tabela de tickets -->
        <div class="tickets-section">
            <div class="tickets-content">
                <!-- Filtros -->
                <div class="filters-container mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3 col-sm-6">
                            <label for="filtroStatus" class="form-label">Estado</label>
                            <select class="form-select" id="filtroStatus">
                                <option value="">Todos</option>
                                <option value="aberto">Aberto</option>
                                <option value="em_andamento">Em Andamento</option>
                                <option value="resolvido">Resolvido</option>
                                <option value="fechado">Fechado</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label for="filtroPrioridade" class="form-label">Prioridade</label>
                            <select class="form-select" id="filtroPrioridade">
                                <option value="">Todas</option>
                                <option value="baixa">Baixa</option>
                                <option value="media">Média</option>
                                <option value="alta">Alta</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <label for="filtroResponsavel" class="form-label">Responsável</label>
                            <select class="form-select" id="filtroResponsavel">
                                <option value="">Todos</option>
                                <?php foreach ($tecnicos as $tecnico): ?>
                                    <option value="<?php echo $tecnico['nome_completo']; ?>">
                                        <?php echo htmlspecialchars($tecnico['nome_completo']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="Sem responsável">Sem responsável</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <button type="button" id="limparFiltros" class="btn btn-secondary w-100">
                                <i class="bi bi-x-circle me-1"></i> Limpar Filtros
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabela de tickets -->
                <div class="ticket-table-container table-responsive-custom">
                    <table id="ticketsTable" class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Assunto</th>
                                <th class="text-center">Componente</th>
                                <th class="text-center">Sala/Local</th>
                                <th class="text-center">Prioridade</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Data de Abertura</th>
                                <th class="text-center">Responsável</th>
                                <th class="text-center ticket-actions-col">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $ticket): ?>
                                <tr>
                                    <td class="text-center" data-label="ID"><strong class="ticket-number"><strong>#</strong><?php echo $ticket['id']; ?></strong></td>
                                    <td class="text-center" data-label="Assunto"><?php echo htmlspecialchars($ticket['assunto']); ?></td>
                                    <td class="text-center" data-label="Componente">
                                        <?php 
                                        if (!empty($ticket['tipo_componente'])) {
                                            echo htmlspecialchars(ucfirst($ticket['tipo_componente']));
                                        } else {
                                            echo "Nenhum Associado";
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center" data-label="Sala/Local"><?php echo htmlspecialchars($ticket['nome_sala'] ?? 'Nenhum Associado'); ?></td>
                                    <td class="text-center" data-label="Prioridade">
                                        <?php
                                        $prioridade_class = '';
                                        $prioridade_texto = '';
                                        
                                        switch ($ticket['prioridade']) {
                                            case 'alta':
                                                $prioridade_class = 'prioridade-alta';
                                                $prioridade_texto = 'Alta';
                                                break;
                                            case 'media':
                                                $prioridade_class = 'prioridade-media';
                                                $prioridade_texto = 'Média';
                                                break;
                                            case 'baixa':
                                                $prioridade_class = 'prioridade-baixa';
                                                $prioridade_texto = 'Baixa';
                                                break;
                                        }
                                        ?>
                                        <span class="prioridade-badge <?php echo $prioridade_class; ?>"><?php echo $prioridade_texto; ?></span>
                                    </td>
                                    <td class="text-center" data-label="Estado">
                                        <?php
                                        $status_class = '';
                                        switch ($ticket['status']) {
                                            case 'aberto':
                                                $status_class = 'status-aberto';
                                                break;
                                            case 'em_andamento':
                                                $status_class = 'status-em-andamento';
                                                break;
                                            case 'resolvido':
                                                $status_class = 'status-concluido';
                                                break;
                                            case 'fechado':
                                                $status_class = 'status-fechado';
                                                break;
                                            default:
                                                $status_class = 'status-cancelado';
                                                break;
                                        }
                                        ?>
                                        <span class="ticket-status <?php echo $status_class; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="text-center" data-label="Data de Abertura"><?php echo formatarData($ticket['criado_em'] ?? ''); ?></td>
                                    <td class="text-center" data-label="Responsável">
                                        <?php if (!empty($ticket['id_tecnico'])): ?>
                                            <?php if ($ticket['id_tecnico'] == $_SESSION['usuario_id']): ?>
                                                <span class="resp-badge resp-badge-you">Eu</span>
                                            <?php else: ?>
                                                <span class="resp-badge resp-badge-tech"><?php echo htmlspecialchars($ticket['nome_tecnico'] ?? 'Técnico'); ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="resp-badge resp-badge-none">Sem responsável</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center ticket-actions-col" data-label="Ações">
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="ver_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-action btn-view" title="Visualizar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            
                                            <?php if (empty($ticket['id_tecnico']) && ($ticket['status'] == 'aberto')): ?>
                                                <button class="btn btn-action btn-take take-responsibility" data-id="<?php echo $ticket['id']; ?>" title="Assumir responsabilidade">
                                                    <i class="bi bi-person-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($ticket['status'] != 'fechado' && $ticket['status'] != 'resolvido' && 
                                                      (empty($ticket['id_tecnico']) || $ticket['id_tecnico'] == $_SESSION['usuario_id'])): ?>
                                                <button class="btn btn-action btn-cancel cancel-ticket" data-id="<?php echo $ticket['id']; ?>" title="Cancelar ticket">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Modal de Detalhes do Ticket -->
  <div class="modal fade ticket-detail-modal" id="ticketDetailModal" tabindex="-1" aria-labelledby="ticketDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="ticketDetailModalLabel"><i class="bi bi-ticket-detailed me-2"></i> Detalhes do Ticket #<span id="ticketIdDetail">123</span></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="ticket-info">
            <div class="ticket-info-item">
              <strong>Assunto/Título</strong>
              <span id="ticketAssuntoDetail">Computador não liga na Sala 101</span>
            </div>
            <div class="ticket-info-item">
              <strong>Estado</strong>
              <span id="ticketStatusDetail">Em Andamento</span>
            </div>
            <div class="ticket-info-item">
              <strong>Prioridade</strong>
              <span id="ticketPrioridadeDetail">Alta</span>
            </div>
            <div class="ticket-info-item">
              <strong>Sala/Local</strong>
              <span id="ticketLocalDetail">Sala 101</span>
            </div>
            <div class="ticket-info-item">
              <strong>Data de Criação</strong>
              <span id="ticketDataDetail">15/05/2023 14:32</span>
            </div>
          </div>
          
          <div class="card mb-4">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-pc-display me-2"></i> Componente e Informações</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <p><strong>Componente com Problema:</strong> <span id="ticketComponenteDetail">Fonte de Alimentação</span></p>
                </div>
                <div class="col-md-6">
                  <p><strong>Número de Série:</strong> <span id="ticketSerieDetail">SN12345678</span></p>
                </div>
              </div>
            </div>
          </div>
          
          <div class="card mb-4">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i> Descrição do Problema</h6>
            </div>
            <div class="card-body">
              <p id="ticketDescricaoDetail">O computador da sala 101 não está ligando. Quando pressiono o botão de ligar, as luzes acendem brevemente e depois apagam. Já verifiquei se está conectado corretamente na tomada e o problema persiste.</p>
            </div>
          </div>
          
          <div class="card mb-4">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i> Mensagem de Erro</h6>
            </div>
            <div class="card-body">
              <p id="ticketMensagemErroDetail">Nenhuma mensagem de erro relatada</p>
            </div>
          </div>
          
          <div class="card mb-4">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-file-earmark-plus me-2"></i> Observações ou Instruções Especiais</h6>
            </div>
            <div class="card-body">
              <p id="ticketObservacoesDetail">Nenhuma observação adicional</p>
            </div>
          </div>
          
          <!-- Anexos/Arquivos -->
          <div class="card mb-4">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-paperclip me-2"></i> Anexos e Fotos</h6>
            </div>
            <div class="card-body">
              <div id="ticketAnexosDetail" class="d-flex flex-wrap gap-3">
                <!-- No estado atual, sem anexos -->
                <p class="text-muted" id="noAttachmentsMsg">Nenhum anexo disponível para este ticket</p>
                
                <!-- Os anexos serão carregados dinamicamente via AJAX -->
              </div>
            </div>
          </div>
          
          <div class="card">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i> Histórico do Ticket</h6>
            </div>
            <div class="card-body">
              <div class="timeline">
                <div class="timeline-item">
                  <div class="timeline-dot">
                    <i class="bi bi-plus-circle-fill text-primary small"></i>
                  </div>
                  <div class="timeline-content">
                    <p class="fw-bold mb-1">Ticket aberto</p>
                    <p class="text-muted small mb-0">15/05/2023 14:32 - Maria Silva</p>
                  </div>
                </div>
                <div class="timeline-item">
                  <div class="timeline-dot">
                    <i class="bi bi-check-circle-fill text-success small"></i>
                  </div>
                  <div class="timeline-content">
                    <p class="fw-bold mb-1">Ticket atribuído</p>
                    <p class="text-muted small mb-0">15/05/2023 15:10 - Sistema</p>
                    <p>Ticket atribuído ao técnico João Pereira</p>
                  </div>
                </div>
                <div class="timeline-item">
                  <div class="timeline-dot">
                    <i class="bi bi-chat-dots-fill text-info small"></i>
                  </div>
                  <div class="timeline-content">
                    <p class="fw-bold mb-1">Comentário adicionado</p>
                    <p class="text-muted small mb-0">15/05/2023 16:45 - João Pereira</p>
                    <p>Agendei visita técnica para amanhã às 9:00. Já separei uma fonte para substituição caso seja necessário.</p>
                  </div>
                </div>
                <div class="timeline-item">
                  <div class="timeline-dot">
                    <i class="bi bi-wrench-adjustable-circle-fill text-warning small"></i>
                  </div>
                  <div class="timeline-content">
                    <p class="fw-bold mb-1">Status alterado</p>
                    <p class="text-muted small mb-0">16/05/2023 09:30 - João Pereira</p>
                    <p>Status alterado de "Aberto" para "Em Andamento"</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
          <button type="button" class="btn btn-warning"><i class="bi bi-pencil me-1"></i> Editar Ticket</button>
          <button type="button" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Marcar como Resolvido</button>
          <button type="button" class="btn btn-danger" id="btnCancelarTicket"><i class="bi bi-x-circle me-1"></i> Cancelar Ticket</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Confirmação de Cancelamento -->
  <div class="modal fade" id="confirmCancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i> Confirmação de Cancelamento</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Tem a certeza que deseja cancelar o ticket #<span id="cancelTicketId">123</span>?</p>
          <p><strong>Atenção:</strong> Esta ação não pode ser revertida.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Não, Voltar</button>
          <button type="button" class="btn btn-danger" id="confirmCancelButton">Sim, Cancelar Ticket</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Popup de sucesso para assumir responsabilidade -->
  <div id="popupOverlay" class="popup-overlay"></div>
  <div id="successPopup" class="success-popup">
    <div class="check-icon">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
        <path fill="none" stroke="#28a745" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" d="M14,27 L22,35 L38,17" />
      </svg>
    </div>
    <h3>Assumistes a responsabilidade pelo ticket #<span id="successTicketId"></span></h3>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <!-- Toastify JS -->
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
  <script>
    // Carregar dados iniciais
    document.addEventListener('DOMContentLoaded', function() {
      // Função para ajustar o botão de toggle com base no tamanho da tela
      function checkToggleDisplay() {
          const sidebar = document.querySelector('.sidebar');
          const toggleButton = document.querySelector('.sidebar-toggle');
          
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
              document.querySelector('.sidebar').classList.remove('active');
              document.body.classList.remove('menu-active');
              const overlay = document.querySelector('.sidebar-overlay');
              if (overlay) overlay.remove();
          }
          checkToggleDisplay();
      });
      
      // Função que permite o toggle do sidebar
      window.toggleSidebar = function() {
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
              
              // Cria e adiciona o overlay
              const overlay = document.createElement('div');
              overlay.className = 'sidebar-overlay';
              overlay.style.position = 'fixed';
              overlay.style.top = '0';
              overlay.style.left = '0';
              overlay.style.width = '100%';
              overlay.style.height = '100%';
              overlay.style.background = 'rgba(0,0,0,0.7)'; // Overlay mais escuro
              overlay.style.zIndex = '1000';
              overlay.style.transition = 'all 0.3s ease';
              document.body.appendChild(overlay);
              
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
              toggleButton.style.display = 'block';
          }
      }
      
      // Inicializar DataTable para a tabela de tickets
      const ticketsTable = $('#ticketsTable').DataTable({
        language: {
          // Configuração direta em vez de carregar arquivo externo que está causando erro CORS
          "sEmptyTable": "Nenhum ticket encontrado",
          "sInfo": "",
          "sInfoEmpty": "",
          "sInfoFiltered": "",
          "sInfoPostFix": "",
          "sInfoThousands": "",
          "sLengthMenu": "",
          "sLoadingRecords": "Carregando...",
          "sProcessing": "Processando...",
          "sZeroRecords": "Nenhum ticket encontrado",
          "sSearch": "",
          "oPaginate": {
            "sNext": "",
            "sPrevious": "",
            "sFirst": "",
            "sLast": ""
          }
        },
        responsive: true,
        ordering: true,
        searching: true,
        lengthChange: false,
        pageLength: 1000, // Mostrar todos os registros em uma página
        paging: false,    // Desativar paginação
        info: false,      // Esconder informações "Mostrando x de y registros"
        dom: '<"top"f>rt<"bottom"><"clear">', // Esconder elementos desnecessários
        columnDefs: [
          { className: 'text-center', targets: '_all' },
          { responsivePriority: 1, targets: [0, 1, 5, 8] }, // ID, Assunto, Estado, Ações - prioridade máxima
          { responsivePriority: 2, targets: [7] },          // Responsável - segunda prioridade
          { responsivePriority: 3, targets: [4, 6] },       // Prioridade, Data - terceira prioridade
          { responsivePriority: 4, targets: [2, 3] }        // Componente, Sala/Local - menor prioridade
        ]
      });
      
      // Adicionar filtros personalizados para DataTables
      $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            // Verificar se estamos filtrando a tabela correta
            if (settings.nTable.id !== 'ticketsTable') {
                return true;
            }
            
            // Obter valores dos filtros
            const statusFilter = $('#filtroStatus').val();
            const prioridadeFilter = $('#filtroPrioridade').val();
            const responsavelFilter = $('#filtroResponsavel').val();
            
            // Status (index 5) - formato: "Aberto", "Em andamento", etc.
            if (statusFilter && statusFilter !== '') {
                const statusText = data[5].toLowerCase(); // texto na célula
                let filterValue = statusFilter.toLowerCase();
                
                // Converter formato do banco para texto na tela
                if (filterValue === 'em_andamento') filterValue = 'em andamento';
                
                if (statusText.indexOf(filterValue) === -1) {
                    return false;
                }
            }
            
            // Prioridade (index 4) - formato: "Alta", "Média", "Baixa"
            if (prioridadeFilter && prioridadeFilter !== '') {
                const prioridadeText = data[4].toLowerCase(); // texto na célula 
                const filterValue = prioridadeFilter.toLowerCase();
                
                if (prioridadeText.indexOf(filterValue) === -1) {
                    return false;
                }
            }
            
            // Responsável (index 7) - contém o texto "Sem responsável", "Eu", ou nome do técnico
            if (responsavelFilter && responsavelFilter !== '') {
                const responsavelText = data[7].toLowerCase(); // texto na célula
                
                if (responsavelFilter === "Sem responsável") {
                    if (responsavelText.indexOf("sem responsável") === -1) {
                        return false;
                    }
                } else {
                    if (responsavelText.indexOf(responsavelFilter.toLowerCase()) === -1) {
                        return false;
                    }
                }
            }
            
            return true;
        }
      );
      
      // Usar o campo de pesquisa personalizado 
      $('#ticketSearch').on('keyup', function() {
        ticketsTable.search(this.value).draw();
      });
      
      // Aplicar filtros quando os selects forem alterados
      $('#filtroStatus, #filtroPrioridade, #filtroResponsavel').on('change', function() {
        ticketsTable.draw();
      });
      
      // Limpar todos os filtros
      $('#limparFiltros').click(function() {
        // Reset the filter dropdowns
        $('#filtroStatus, #filtroPrioridade, #filtroResponsavel').val('');
        
        // Reset the search box
        $('#ticketSearch').val('');
        
        // Force redraw of the table
        ticketsTable.search('').draw();
      });
      
      // Lógica para assumir responsabilidade diretamente na tabela
      $('#ticketsTable').on('click', '.take-responsibility', function(e) {
        e.stopPropagation();
        const ticketId = $(this).data('id');
        const row = $(this).closest('tr');
        
        // Fazer uma requisição AJAX para assumir a responsabilidade
        $.ajax({
            url: 'funcoes/assumir_responsabilidade.php',
            type: 'POST',
            data: { ticket_id: ticketId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Atualizar visualmente
                    row.find('td:eq(7)').html('<span class="resp-badge resp-badge-you">Eu</span>');
                    // Remover o botão de assumir responsabilidade
                    row.find('.take-responsibility').remove();
                    
                    // Mostrar popup de confirmação
                    showSuccessPopup(ticketId);
                    
                    // Recarregar a página para mostrar as mudanças completas
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    alert('Erro: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Erro AJAX:", xhr.responseText);
                alert('Ocorreu um erro ao processar a requisição. Verifique o console para detalhes.');
            }
        });
      });
      
      // Função para mostrar popup de confirmação
      function showSuccessPopup(ticketId) {
        // Definir ID do ticket no popup
        document.getElementById('successTicketId').textContent = ticketId;
        
        // Resetar a animação
        const checkIcon = document.querySelector('.success-popup .check-icon');
        const checkPath = document.querySelector('.success-popup .check-icon svg path');
        
        // Forçar um reflow para reiniciar as animações
        void checkIcon.offsetWidth;
        void checkPath.offsetWidth;
        checkPath.style.strokeDashoffset = '80';
        
        // Mostrar overlay
        const overlay = document.getElementById('popupOverlay');
        overlay.style.display = 'block';
        setTimeout(function() {
          overlay.classList.add('show');
        }, 10);
        
        // Mostrar popup
        const popup = document.getElementById('successPopup');
        popup.style.display = 'block';
        setTimeout(function() {
          popup.classList.add('show');
        }, 10);
        
        // Esconder após 5 segundos
        setTimeout(function() {
          overlay.classList.remove('show');
          popup.classList.remove('show');
          setTimeout(function() {
            overlay.style.display = 'none';
            popup.style.display = 'none';
          }, 300);
        }, 5000);
      }
      
      // Lógica para cancelar um ticket na tabela
      $('#ticketsTable').on('click', '.cancel-ticket', function(e) {
        e.stopPropagation();
        const ticketId = $(this).data('id');
        $('#cancelTicketId').text(ticketId);
        
        // Abrir modal de confirmação
        const confirmModal = new bootstrap.Modal(document.getElementById('confirmCancelModal'));
        confirmModal.show();
        
        // Armazenar referência à linha da tabela para uso posterior
        window.currentCancelRow = $(this).closest('tr');
      });
      
      // Confirmar cancelamento do ticket
      $('#confirmCancelButton').click(function() {
        const ticketId = $('#cancelTicketId').text();
        
        // Fazer uma requisição AJAX para cancelar o ticket
        $.ajax({
            url: 'funcoes/cancelar_ticket.php',
            type: 'POST',
            data: { ticket_id: ticketId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Atualizar o status na tabela
                    window.currentCancelRow.find('td:eq(5)').html('<span class="ticket-status status-cancelado">Cancelado</span>');
                    // Atualizar a responsabilidade na tabela
                    window.currentCancelRow.find('td:eq(7)').html('<span class="resp-badge resp-badge-none">Não aplicável</span>');
                    // Remover botões de ação, deixando apenas o de visualização
                    const actionsCell = window.currentCancelRow.find('td:eq(8)').find('div');
                    actionsCell.html('<a href="ver_ticket.php?id=' + ticketId + '" class="btn btn-action btn-view" title="Visualizar"><i class="bi bi-eye"></i></a>');
                    
                    // Limpar a referência
                    window.currentCancelRow = null;
                    
                    // Fechar o modal de confirmação
                    $('#confirmCancelModal').modal('hide');
                    
                    // Mostrar notificação
                    alert('Ticket #' + ticketId + ' foi cancelado com sucesso.');
                    
                    // Recarregar a página para mostrar as mudanças completas
                    window.location.reload();
                } else {
                    alert('Erro: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Erro AJAX:", xhr.responseText);
                alert('Ocorreu um erro ao processar a requisição. Verifique o console para detalhes.');
            }
        });
      });
    });
  </script>
</body>
</html> 