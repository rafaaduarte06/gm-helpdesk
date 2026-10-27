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
require_once '../ligacao_base_dados.php';
require_once 'funcoes/manutencao_funcoes.php';

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

// Obter tickets atribuídos ao técnico
$tickets = obterTicketsAtribuidos($pdo, $usuario_id);

// Contar tickets por status
$total_tickets = count($tickets);
$abertos = 0;
$em_andamento = 0;
$resolvidos = 0;
$alta_prioridade = 0;

foreach ($tickets as $ticket) {
    if ($ticket['status'] == 'aberto') $abertos++;
    if ($ticket['status'] == 'em_andamento') $em_andamento++;
    if ($ticket['status'] == 'resolvido') $resolvidos++;
    if ($ticket['prioridade'] == '3') $alta_prioridade++;
}

// Incluir o sidebar
include_once '../includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestão de Manutenção - GM HelpDesk</title>
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../assets/css/variables.css">
  <link rel="stylesheet" href="../assets/css/custom.css">
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
  
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
    
    /* Dashboard cards */
    .dashboard-card {
        background-color: white;
        border-radius: 1rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.2);
    }
    
    .dashboard-card .card-body {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }
    
    .dashboard-card .icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.1);
        padding: 1rem;
        border-radius: 1rem;
    }
    
    .dashboard-card .count {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .dashboard-card .title {
        font-size: 1rem;
        color: #6c757d;
        text-align: center;
    }
    
    /* Tabela de tickets */
    .table {
        background-color: white;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .table thead {
        background-color: #1a3c5e;
        color: white;
    }
    
    .table th {
        font-weight: 600;
        padding: 0.75rem 1rem;
    }
    
    .table td {
        vertical-align: middle;
        padding: 0.75rem 1rem;
    }
    
    /* Melhorar aparência dos badges de status */
    .status-aberto, .status-em_andamento, .status-resolvido, .status-cancelado, .status-fechado {
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

    .status-em_andamento {
        background-color: rgba(13, 110, 253, 0.15);
        color: #0d6efd;
        border-color: #0d6efd;
    }

    .status-resolvido {
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
    
    /* Estilos para prioridade */
    .prioridade-1 {
        color: #0d6efd; /* azul - baixa */
    }
    
    .prioridade-2 {
        color: #e98215; /* laranja - média */
    }
    
    .prioridade-3 {
        color: #dc3545; /* vermelho - alta */
    }
    
    /* Ticket Number */
    .ticket-number {
        display: inline-block;
        font-weight: 700;
        color: #2a4d69;
    }
    
    /* Responsividade */
    @media (max-width: 992px) {
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

    /* Estilos para tickets resolvidos com alta especificidade */
    /* Estilos do dashboard */
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
    
    /* Estilos para os cards de componentes */
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

    /* Botão de criar ticket */
    .btn-create-ticket {
      padding: 10px 25px;
      font-weight: 600;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(13, 110, 253, 0.2);
      transition: all 0.3s ease;
    }
    
    .btn-create-ticket:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(13, 110, 253, 0.3);
    }
    
    /* Estilos para header e layout */
    @media (max-width: 992px) {
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
    
    /* Esconder elementos do DataTables que não queremos */
    .dataTables_filter {
        display: none !important;
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
      }
      
      #ticketsTable td:last-child {
        justify-content: center !important;
      }

      #ticketsTable td:last-child:before {
        display: none; /* Hide "Ações" label above action buttons */
      }
    }
    
    /* Cor personalizada para prioridade média */
    .text-orange {
        color: #e98215 !important;
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
        
        <!-- Cabeçalho -->
        <div class="stock-header">
          <div class="stock-title">
            <i class="bi bi-tools"></i>
            <div>
              <h4>Gestão de Manutenção</h4>
              <p class="mb-0 mt-1 text-light">Total de Tickets Assumidos: <span class="fw-bold"><?php echo $total_tickets; ?></span></p>
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
          </div>
        </div>
        
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
        
        <!-- Cards de estatísticas no estilo mais visual -->
        <div class="row mb-4 g-4">
          <div class="col-xl-4 col-md-4">
            <div class="component-card">
              <div class="component-icon" style="color: #0d6efd; background-color: rgba(13, 110, 253, 0.1);">
                <i class="bi bi-arrow-repeat"></i>
              </div>
              <h5 class="component-title">Em Andamento</h5>
              <div class="stock-counter" style="background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; border-color: #0d6efd;">
                <?php echo $em_andamento; ?>
              </div>
            </div>
          </div>
          <div class="col-xl-4 col-md-4">
            <div class="component-card">
              <div class="component-icon" style="color: #198754; background-color: rgba(25, 135, 84, 0.1);">
                <i class="bi bi-check-circle"></i>
              </div>
              <h5 class="component-title">Tickets Resolvidos</h5>
              <div class="stock-counter" style="background-color: rgba(25, 135, 84, 0.1); color: #198754; border-color: #198754;">
                <?php echo $resolvidos; ?>
              </div>
            </div>
          </div>
          <div class="col-xl-4 col-md-4">
            <div class="component-card">
              <div class="component-icon" style="color: #dc3545; background-color: rgba(220, 53, 69, 0.1);">
                <i class="bi bi-alarm"></i>
              </div>
              <h5 class="component-title">Tickets Urgentes</h5>
              <div class="stock-counter" style="background-color: rgba(220, 53, 69, 0.1); color: #dc3545; border-color: #dc3545;">
                <?php echo $alta_prioridade; ?>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Lista de Tickets -->
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-body">
            <div class="table-responsive">
              <table id="ticketsTable" class="table table-hover">
                <thead>
                  <tr>
                    <th width="80" class="text-center">ID</th>
                    <th class="text-center">Assunto</th>
                    <th class="text-center">Sala</th>
                    <th class="text-center">Utilizador</th>
                    <th class="text-center">Prioridade</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Data</th>
                    <th class="text-center">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($tickets)): ?>
                  <tr>
                    <td colspan="8" class="text-center py-4">
                      <div class="d-flex flex-column align-items-center">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-3 mb-0 text-muted">Nenhum ticket lhe foi atribuído</p>
                        <small class="text-muted">Assuma a responsabilidade de um ticket no dashboard</small>
                      </div>
                    </td>
                  </tr>
                  <?php else: ?>
                    <?php foreach ($tickets as $ticket): ?>
                    <tr>
                      <td class="text-center" data-label="ID"><strong class="ticket-number"><strong>#</strong><?php echo $ticket['id']; ?></strong></td>
                      <td class="text-center" data-label="Assunto"><?php echo htmlspecialchars($ticket['assunto']); ?></td>
                      <td class="text-center" data-label="Sala"><?php echo htmlspecialchars($ticket['sala']); ?></td>
                      <td class="text-center" data-label="Solicitante"><?php echo htmlspecialchars($ticket['solicitante']); ?></td>
                      <td class="text-center" data-label="Prioridade">
                        <?php 
                        $prioridade_texto = $ticket['prioridade'] == '1' ? 'Baixa' : 
                            ($ticket['prioridade'] == '2' ? 'Média' : 'Alta'); 
                        $prioridade_class = 'prioridade-' . $ticket['prioridade'];
                        ?>
                        <strong class="<?php echo $prioridade_class; ?>"><?php echo $prioridade_texto; ?></strong>
                      </td>
                      <td class="text-center" data-label="Estado">
                        <span class="status-<?php echo $ticket['status']; ?>">
                          <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                        </span>
                      </td>
                      <td class="text-center" data-label="Data"><?php echo date('d/m/Y H:i', strtotime($ticket['criado_em'])); ?></td>
                      <td class="text-center" data-label="Ações">
                        <div class="d-flex gap-1 justify-content-center">
                          <a href="gerir_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-action btn-view" title="Gerir">
                            <i class="bi bi-tools"></i>
                          </a>
                          <a href="../dashboard/tickets/ver_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-action btn-take" title="Visualizar">
                            <i class="bi bi-eye"></i>
                          </a>
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
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>
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

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    // Inicializar DataTables
    $(document).ready(function() {
      const ticketsTable = $('#ticketsTable').DataTable({
        language: {
          // Tradução embutida para evitar problemas de CORS
          processing: "Processando...",
          search: "",
          info: "",
          infoEmpty: "",
          infoFiltered: "",
          loadingRecords: "Carregando...",
          zeroRecords: "Nenhum registro encontrado",
          emptyTable: "Nenhum registro disponível"
        },
        paging: false,      // Desativar paginação
        lengthChange: false, // Desativar controle "mostrar X registros"
        order: [[0, 'desc']], // Ordenar por ID (decrescente)
        responsive: true,
        columnDefs: [
          { className: "text-center", targets: "_all" }
        ],
        createdRow: function(row, data, index) {
          // Adicionar classe text-center a todas as células
          $('td', row).addClass('text-center');
        }
      });

      // Usar o campo de pesquisa personalizado 
      $('#ticketSearch').on('keyup', function() {
        ticketsTable.search(this.value).draw();
      });

      // Lógica para cancelar um ticket na tabela
      $('.cancel-ticket').on('click', function(e) {
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
          url: '../dashboard/tickets/funcoes/cancelar_ticket.php',
          type: 'POST',
          data: { ticket_id: ticketId },
          dataType: 'json',
          success: function(response) {
            if (response.success) {
              // Atualizar o status na tabela
              window.currentCancelRow.find('td:eq(5)').html('<span class="ticket-status status-fechado">Fechado</span>');
              // Remover os botões de ação, deixando apenas o de visualização
              const actionsCell = window.currentCancelRow.find('td:last').find('div');
              actionsCell.html('<a href="../dashboard/tickets/ver_ticket.php?id=' + ticketId + '" class="btn btn-action btn-take" title="Visualizar"><i class="bi bi-eye"></i></a>');
              
              // Limpar a referência
              window.currentCancelRow = null;
              
              // Fechar o modal de confirmação
              $('#confirmCancelModal').modal('hide');
              
              // Mostrar notificação
              Swal.fire({
                icon: 'success',
                title: 'Ticket Cancelado',
                text: 'Ticket #' + ticketId + ' foi cancelado com sucesso.',
                showConfirmButton: false,
                timer: 1500
              });
              
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
    document.addEventListener('DOMContentLoaded', function() {
      checkToggleDisplay();
    });
    
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
  </script>
</body>
</html> 