<?php
// Habilitar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar a sessão antes de qualquer output
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['userId'])) {
    header('Location: ../login.php');
    exit;
}

// Identificar qual formato está sendo usado
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : $_SESSION['userId'];
$usuario_tipo = isset($_SESSION['usuario_tipo']) ? $_SESSION['usuario_tipo'] : (isset($_SESSION['userType']) ? $_SESSION['userType'] : '');

// Guardar em formato padrão para uso consistente
$_SESSION['usuario_id'] = $usuario_id;
$_SESSION['usuario_tipo'] = $usuario_tipo;

// Verificar se é um usuário da comunidade escolar
if ($usuario_tipo !== 'comunidade_escolar') {
    header('Location: ../login.php');
    exit;
}

// Incluir arquivos necessários
require_once '../ligacao_base_dados.php';
require_once 'tickets/funcoes/tickets_funcoes.php';

// Buscar dados do usuário
try {
    $stmt = $pdo->prepare("SELECT * FROM comunidade_escolar WHERE id = ?");
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

// Obter tickets do usuário
try {
    // Para usuários da comunidade escolar, obter apenas seus próprios tickets
    // Modificar a chamada para filtrar pelo ID do usuário na condição correta
    $tickets = obterTickets($pdo, null, '', '', 0); 
    
    // Aplicar filtro manualmente para garantir que apenas vemos os tickets do usuário atual
    $filtered_tickets = [];
    foreach ($tickets as $ticket) {
        if ((int)$ticket['id_usuario'] === (int)$usuario_id) {
            // Garantir que todos os campos necessários estão presentes
            $required_fields = ['id', 'assunto', 'status', 'prioridade', 'criado_em'];
            $valid = true;
            foreach ($required_fields as $field) {
                if (!isset($ticket[$field])) {
                    error_log("Ticket ID {$ticket['id']} faltando campo obrigatório: $field");
                    $valid = false;
                    break;
                }
            }
            
            if ($valid) {
                $filtered_tickets[] = $ticket;
            }
        }
    }
    $tickets = $filtered_tickets;
    
    // Debug - verificar a estrutura dos tickets
    if (empty($tickets)) {
        error_log("Nenhum ticket válido encontrado para o usuário ID: $usuario_id");
    } else {
        error_log("Encontrados " . count($tickets) . " tickets válidos para o usuário ID: $usuario_id");
        // Verificar campos disponíveis no primeiro ticket
        error_log("Campos disponíveis no primeiro ticket: " . implode(", ", array_keys(reset($tickets))));
    }
    
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
include_once '../includes/sidebar.php';
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
    
    /* Estilo completo da sidebar - igual ao inventario.php */
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

    .main-content {
        margin-left: 300px;
        width: calc(100% - 300px);
        transition: all 0.3s ease;
    }

    /* Responsividade - Sidebar colapsa em telas menores */
    @media (max-width: 991.98px) {
        .sidebar {
            width: 250px;
        }
        
        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
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
    }

    /* Stock Header Styles */
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

    /* Component Card Styles */
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

    /* Search input styles */
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

    /* Estilos para botão de criar ticket */
    .btn-create-ticket {
        padding: 10px 25px;
        font-weight: 600;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.2);
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .btn-create-ticket:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(13, 110, 253, 0.3);
    }

    /* Tickets content styles */
    .tickets-content {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .ticket-table-container {
        margin-top: 1rem;
    }

    /* Status styles */
    .status-aberto, .status-em-andamento, .status-concluido, .status-cancelado, .status-fechado {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        display: inline-block;
        min-width: 100px;
        text-align: center;
    }

    .status-aberto {
        background-color: rgba(255, 193, 7, 0.15);
        color: #ffc107;
        border: 1px solid #ffc107;
    }

    .status-em-andamento {
        background-color: rgba(13, 110, 253, 0.15);
        color: #0d6efd;
        border: 1px solid #0d6efd;
    }

    .status-concluido {
        background-color: rgba(25, 135, 84, 0.15);
        color: #198754;
        border: 1px solid #198754;
    }

    .status-fechado {
        background-color: rgba(108, 117, 125, 0.15);
        color: #6c757d;
        border: 1px solid #6c757d;
    }

    .status-cancelado {
        background-color: rgba(220, 53, 69, 0.15);
        color: #dc3545;
        border: 1px solid #dc3545;
    }

    /* DataTables styling */
    #tabela-tickets {
        border-collapse: separate;
        border-spacing: 0;
    }

    #tabela-tickets thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #495057;
        padding: 12px 15px;
        border-bottom: 2px solid #e9ecef;
    }

    #tabela-tickets tbody tr {
        transition: all 0.2s ease;
    }

    #tabela-tickets tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    #tabela-tickets td {
        padding: 12px 15px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }

    /* Melhorados estilos para responsivo */
    @media (max-width: 992px) {
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

    @media (max-width: 768px) {
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
        }
        
        .ticket-table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        #tabela-tickets {
            min-width: 900px;
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
        
        .d-flex.align-items-center.gap-3 {
            flex-direction: column;
            width: 100%;
            align-items: stretch !important;
        }
        
        .search-container {
            width: 100%;
            margin-bottom: 10px;
        }
        
        .btn-create-ticket {
            width: 100%;
        }
    }

    /* Estilos responsivos para tabela de tickets em dispositivos móveis */
    @media (max-width: 767px) {
      #tabela-tickets {
        min-width: auto !important;
      }
      
      #tabela-tickets thead {
        display: none; /* Oculta o cabeçalho da tabela */
      }
      
      #tabela-tickets, 
      #tabela-tickets tbody, 
      #tabela-tickets tr {
        display: block;
        width: 100%;
      }
      
      #tabela-tickets tr {
        margin-bottom: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        background: white;
        overflow: hidden;
        position: relative;
        border-left: 4px solid #2a4d69;
        transition: all 0.3s ease;
      }
      
      #tabela-tickets tr:hover {
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
        transform: translateY(-3px);
        border-left: 4px solid #0a4da0;
      }
      
      #tabela-tickets td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px !important;
        text-align: right !important;
        border-bottom: 1px solid rgba(0,0,0,0.05);
      }
      
      #tabela-tickets td:last-child {
        border-bottom: none;
        justify-content: center;
        padding-top: 20px !important;
        padding-bottom: 20px !important;
        background-color: rgba(42, 77, 105, 0.05);
      }
      
      #tabela-tickets td:last-child:before {
        display: none;
      }
      
      #tabela-tickets td:before {
        content: attr(data-label);
        font-weight: 600;
        color: #2a4d69;
        text-align: left;
        padding-right: 10px;
      }
      
      /* Adiciona uma leve animação de brilho na borda */
      @keyframes borderGlow {
        0% { border-left-color: #2a4d69; }
        50% { border-left-color: #0a4da0; }
        100% { border-left-color: #2a4d69; }
      }
      
      #tabela-tickets tr {
        animation: borderGlow 3s infinite;
      }
      
      /* Ajuste para o número do ticket ficar junto */
      .ticket-number {
        display: inline-block;
      }
      
      #tabela-tickets td[data-label="Ticket"] {
        justify-content: space-between;
      }
      
      #tabela-tickets td[data-label="Ticket"]:before {
        margin-right: auto;
      }
    }

    .table-responsive-custom {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
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

    /* Estilo para badges de prioridade */
    .badge-prioridade {
        padding: 0.5em 1em;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .badge-prioridade-alta {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border: 1px solid #dc3545;
    }

    .badge-prioridade-media {
        background-color: rgba(255, 140, 0, 0.1);
        color: #ff8c00;
        border: 1px solid #ff8c00;
    }

    .badge-prioridade-baixa {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
        border: 1px solid #198754;
    }

    /* Estilo para badges de estado */
    .badge-estado {
        padding: 0.5em 1em;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .badge-estado-aberto {
        background-color: rgba(255, 140, 0, 0.1);
        color: #ff8c00;
        border: 1px solid #ff8c00;
    }

    .badge-estado-em-andamento {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
        border: 1px solid #0d6efd;
    }

    .badge-estado-resolvido {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
        border: 1px solid #198754;
    }

    .badge-estado-fechado {
        background-color: rgba(108, 117, 125, 0.1);
        color: #6c757d;
        border: 1px solid #6c757d;
    }
  </style>
</head>
<body>
  <!-- Botão de toggle para sidebar em dispositivos móveis -->
  <button class="sidebar-toggle btn" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
  </button>

  <div class="d-flex">
    <!-- Sidebar -->
    <?php 
    // Renderizar a sidebar com os mesmos parâmetros do inventario.php, mas apenas com opção de dashboard
    $sidebarContent = '
      <div class="sidebar-header p-3 border-bottom">
          <div class="d-flex align-items-center">
              <i class="bi bi-tools text-primary fs-4 me-2"></i>
              <h5 class="mb-0">GM HelpDesk</h5>
          </div>
      </div>
      <div class="p-3">
          <div class="user-info">
              <a href="../conta/perfil.php" class="text-decoration-none">
                  <div class="d-flex align-items-center">
                      <div class="avatar">
                          <img src="../assets/img/perfil/' . ($usuario['foto_perfil'] ?: 'default.jpg') . '" 
                               class="rounded-circle" 
                               style="width: 45px; height: 45px; object-fit: cover;">
                      </div>
                      <div class="user-details ms-2">
                          <h6 class="mb-0">' . $usuario['nome_completo'] . '</h6>
                          <small>Comunidade Escolar</small>
                      </div>
                  </div>
              </a>
          </div>
          <ul class="nav flex-column mt-3">
              <li class="nav-item mb-2">
                  <a href="dashboardce.php" class="nav-link active">
                      <i class="bi bi-house"></i>
                      Dashboard
                  </a>
              </li>
          </ul>
      </div>
      <div class="escola-logo">
          <img src="../assets/img/menu/escola.png" alt="Logo da Escola">
      </div>';
    
    echo '<nav class="sidebar">' . $sidebarContent . '</nav>';
    ?>

    <!-- Conteúdo Principal -->
    <div class="main-content">
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
            <a href="tickets/ticket_comunidade.php" class="btn btn-light btn-create-ticket">
              <i class="bi bi-plus-circle me-2"></i>Criar Novo Ticket
            </a>
          </div>
        </div>
        
        <!-- Cards de estatísticas no estilo mais visual -->
        <div class="row mb-4 g-4">
          <div class="col-xl col-md-6">
            <div class="component-card">
              <div class="component-icon" style="color: #ffc107; background-color: rgba(255, 193, 7, 0.1);">
                <i class="bi bi-hourglass-split"></i>
              </div>
              <h5 class="component-title">Tickets Pendentes</h5>
              <div class="stock-counter" style="background-color: rgba(255, 193, 7, 0.1); color: #ffc107; border-color: #ffc107;">
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
            <!-- Tabela de tickets -->
            <div class="ticket-table-container table-responsive-custom">
              <?php if (count($tickets) > 0): ?>
                <table id="tabela-tickets" class="table table-striped table-hover">
                  <thead class="table-light">
                    <tr>
                      <th class="text-center">Ticket</th>
                      <th class="text-center">Assunto</th>
                      <th class="text-center">Componente/Computador</th>
                      <th class="text-center">Sala/Local</th>
                      <th class="text-center">Prioridade</th>
                      <th class="text-center">Estado</th>
                      <th class="text-center">Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                      <tr>
                        <td class="text-center" data-label="Ticket"><span class="ticket-number"><strong>#</strong><strong><?php echo $ticket['id']; ?></strong></span></td>
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
                          switch ($ticket['prioridade']) {
                            case 'alta':
                              $prioridade_class = 'badge-prioridade badge-prioridade-alta';
                              break;
                            case 'media':
                              $prioridade_class = 'badge-prioridade badge-prioridade-media';
                              $ticket['prioridade'] = 'média';
                              break;
                            case 'baixa':
                              $prioridade_class = 'badge-prioridade badge-prioridade-baixa';
                              break;
                          }
                          ?>
                          <span class="<?php echo $prioridade_class; ?>">
                            <?php echo ucfirst($ticket['prioridade']); ?>
                          </span>
                        </td>
                        <td class="text-center" data-label="Estado">
                          <?php
                          $status_class = '';
                          switch ($ticket['status']) {
                            case 'aberto':
                              $status_class = 'badge-estado badge-estado-aberto';
                              break;
                            case 'em_andamento':
                              $status_class = 'badge-estado badge-estado-em-andamento';
                              break;
                            case 'resolvido':
                              $status_class = 'badge-estado badge-estado-resolvido';
                              break;
                            case 'fechado':
                              $status_class = 'badge-estado badge-estado-fechado';
                              break;
                          }
                          ?>
                          <span class="<?php echo $status_class; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                          </span>
                        </td>
                        <td class="text-center" data-label="Ações">
                          <a href="tickets/visualizar_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-action btn-view" title="Visualizar">
                            <i class="bi bi-eye"></i>
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php else: ?>
                <div class="alert alert-info" role="alert">
                  <i class="bi bi-info-circle-fill me-2"></i>
                  <div>
                    <p class="mb-0">Não existem tickets criados.</p>
                    <p class="mb-0 mt-2">
                      <a href="tickets/ticket_comunidade.php" class="alert-link">Criar o seu primeiro ticket</a>
                    </p>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap JS Bundle (Bootstrap + Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
  <!-- Toastify JS -->
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

  <script>
    // Define toggleSidebar globally so it's accessible from the onclick attribute
    function toggleSidebar() {
      const sidebar = document.querySelector('.sidebar');
      const toggleButton = document.querySelector('.sidebar-toggle');
      const body = document.body;
      
      sidebar.classList.toggle('active');
      
      // Se o sidebar estiver ativo, esconde o botão de toggle e mostra o overlay
      if (sidebar.classList.contains('active')) {
        // Esconde imediatamente o botão de toggle
        if (toggleButton) toggleButton.style.display = 'none';
        
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
            if (toggleButton) toggleButton.style.display = 'block';
          }, 50);
        });
      } else {
        // Remove classe do body
        body.classList.remove('menu-active');
        
        // Esconde o overlay se existir
        const overlay = document.querySelector('.sidebar-overlay');
        if (overlay) overlay.remove();
        
        // Certifica-se de que o botão de toggle está visível
        if (toggleButton) toggleButton.style.display = 'block';
      }
    }

    $(document).ready(function() {
      // Inicialização segura da DataTable
      if (document.getElementById('tabela-tickets')) {
        try {
          // Verificar se a tabela existe e tem linhas
          var table = $('#tabela-tickets');
          
          // Verificar se a tabela tem dados
          if (table.find('tbody tr').length === 0 || 
              (table.find('tbody tr').length === 1 && table.find('tbody tr td[colspan]').length > 0)) {
            console.log('Tabela vazia ou sem dados - pulando inicialização do DataTable');
            return; // Não inicializar DataTable se não houver dados
          }
          
          // Verificar se o número de colunas no cabeçalho e no corpo coincidem
          var headerCols = table.find('thead tr th').length;
          var bodyCols = table.find('tbody tr:first-child td').length;
          
          console.log('Número de colunas no cabeçalho:', headerCols);
          console.log('Número de colunas no corpo:', bodyCols);
          
          if (headerCols !== bodyCols) {
            console.warn('Atenção: O número de colunas no cabeçalho (' + headerCols + 
                       ') não coincide com o número de colunas no corpo (' + bodyCols + ')');
          }
          
          // Inicializar DataTables com opções mais seguras
          $('#tabela-tickets').DataTable({
            language: {
              processing: "A processar...",
              search: "",
              lengthMenu: "",
              info: "",
              infoEmpty: "",
              infoFiltered: "",
              loadingRecords: "A carregar...",
              zeroRecords: "Não foram encontrados resultados",
              emptyTable: "Não existe informação disponível na tabela",
              paginate: {
                first: "",
                previous: "",
                next: "",
                last: ""
              }
            },
            order: [[0, 'desc']],
            pageLength: 1000,
            paging: false,
            info: false,
            lengthChange: false,
            responsive: true,
            dom: '<"top">rt<"bottom"><"clear">',
            columnDefs: [
              { targets: '_all', orderable: true } // Certificar que todas as colunas são ordenáveis
            ],
            // Desabilitar avisos de erro não críticos
            processing: true,
            retrieve: true,
            stateSave: false
          });
          
          console.log('DataTable inicializada com sucesso');
        } catch (error) {
          console.error('Erro ao inicializar DataTable:', error);
          
          // Esconder o wrapper do DataTable em caso de erro para evitar problemas de UI
          $('.dataTables_wrapper').hide();
        }
      } else {
        console.log('Tabela #tabela-tickets não encontrada - pulando inicialização');
      }
      
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
      
      // Adicione a funcionalidade de pesquisa
      $('#ticketSearch').on('keyup', function() {
        if ($.fn.dataTable.isDataTable('#tabela-tickets')) {
          $('#tabela-tickets').DataTable().search(this.value).draw();
        }
      });
    });
  </script>
</body>
</html> 