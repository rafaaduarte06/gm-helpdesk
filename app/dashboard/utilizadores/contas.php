<?php
session_start();
require_once('../../ligacao_base_dados.php');
require_once('../../includes/sidebar.php'); // Incluir o arquivo do sidebar

// Verificar se o usuário está logado e é técnico
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    header('Location: ../../login.php');
    exit;
}

// Buscar dados do usuário atual
try {
    $stmt = $pdo->prepare("SELECT * FROM suporte_tecnico WHERE id = ?");
    $stmt->execute([$_SESSION['userId']]);
    $usuario = $stmt->fetch();
} catch (Exception $e) {
    die("Erro ao carregar dados do usuário: " . $e->getMessage());
}

// Buscar dados dos usuários
try {
    // Buscar técnicos
    $stmt = $pdo->query("SELECT * FROM suporte_tecnico ORDER BY nome_completo");
    $tecnicos = $stmt->fetchAll();

    // Buscar comunidade escolar
    $stmt = $pdo->query("SELECT * FROM comunidade_escolar ORDER BY nome_completo");
    $comunidade = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar usuários: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Contas - GM HelpDesk</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/variables.css">
    <link rel="stylesheet" href="../../assets/css/custom.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        /* Estilo de fundo gradiente como na página de login */
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

        /* Estilo para conteúdo principal */
        .main-content {
            margin-left: 300px;
            width: calc(100% - 300px);
            transition: all 0.3s ease;
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

        /* Estilo do cabeçalho da página */
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
            flex-wrap: wrap;
            gap: 15px;
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
        }

        /* Estilos para o campo de pesquisa */
        .search-container {
            flex-grow: 1;
            max-width: 450px;
        }
        
        .search-input-group {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
            transition: all 0.3s ease;
            width: 100%;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        .search-input-group:focus-within {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.18);
            transform: translateY(-2px);
            border-color: #4b86b4;
        }
        
        .search-icon-container {
            background-color: rgba(255, 255, 255, 0.9);
            border: none;
            color: #4b86b4;
            padding: 0 12px;
            display: flex;
            align-items: center;
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }
        
        .search-icon-container i {
            font-size: 0.9rem;
        }
        
        .search-input {
            border: none;
            height: 45px;
            font-size: 0.95rem;
            background-color: rgba(255, 255, 255, 0.9);
            padding-left: 5px;
        }
        
        .search-input:focus {
            box-shadow: none;
            background-color: white;
            outline: none;
        }
        
        .search-icon-container + .search-input:focus,
        .search-icon-container + .search-input:focus-visible {
            background-color: white;
        }

        .btn-primary {
            background-color: rgba(255, 255, 255, 0.2);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            color: white;
            height: 45px;
            display: flex;
            align-items: center;
        }

        .btn-primary:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        .account-type-selector {
            margin-bottom: 1.5rem;
        }

        .account-type-selector .btn-group {
            width: 100%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .account-type-selector .btn {
            flex: 1;
            padding: 0.75rem 1rem;
            font-weight: 600;
            border: none;
            background-color: white;
            color: #495057;
            transition: all 0.3s ease;
        }

        .account-type-selector .btn.active {
            background-color: #0d6efd;
            color: white;
        }

        .account-type-selector .btn:hover:not(.active) {
            background-color: #f8f9fa;
        }

        .card {
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(-3px);
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
            border-top: none;
        }

        .btn-outline-info, .btn-outline-primary, .btn-outline-danger {
            border-radius: 6px;
            padding: 0.4rem 0.6rem;
            transition: all 0.2s ease;
        }

        .btn-outline-info:hover, .btn-outline-primary:hover, .btn-outline-danger:hover {
            transform: translateY(-2px);
        }
        
        /* Estilo para os botões de ação */
        .action-buttons {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            white-space: nowrap;
            min-width: 120px;
        }
        
        .action-buttons .btn {
            margin-right: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
        }
        
        .action-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.15);
        }

        .action-buttons .btn-outline-info {
            color: #0dcaf0;
            border-color: #0dcaf0;
            background-color: transparent;
        }
        
        .action-buttons .btn-outline-info:hover {
            background-color: rgba(13, 202, 240, 0.1);
            color: #0dcaf0;
        }
        
        .action-buttons .btn-outline-primary {
            color: #0d6efd;
            border-color: #0d6efd;
            background-color: transparent;
        }
        
        .action-buttons .btn-outline-primary:hover {
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }
        
        .action-buttons .btn-outline-danger {
            color: #dc3545;
            border-color: #dc3545;
            background-color: transparent;
        }
        
        .action-buttons .btn-outline-danger:hover {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        /* Estilos Responsivos */
        @media (max-width: 1200px) {
            .stock-header {
                padding: 1.5rem;
            }
            
            .stock-title i {
                font-size: 1.8rem;
            }
            
            .stock-title h4 {
                font-size: 1.3rem;
            }
        }
        
        @media (max-width: 992px) {
            /* Ajustes para tablets */
            .sidebar {
                width: 250px;
            }
            
            .main-content {
                margin-left: 250px;
                width: calc(100% - 250px);
            }
            
            .stock-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 1.2rem;
            }
            
            .d-flex.gap-3.align-items-center {
                width: 100%;
                margin-top: 15px;
                justify-content: space-between;
            }
            
            .search-container {
                max-width: 65%;
            }
        }
        
        @media (max-width: 768px) {
            /* Ajustes para dispositivos móveis */
            .sidebar {
                left: -300px;
                transition: all 0.3s ease;
                width: 280px;
                z-index: 1001; /* Aumentar o z-index para garantir que fique acima de tudo */
            }
            
            .sidebar.active {
                left: 0;
                box-shadow: 0 0 25px rgba(0, 0, 0, 0.3);
            }
            
            /* Quando o menu está ativo, empurra o conteúdo para fora da tela */
            body.menu-active .main-content {
                transform: translateX(280px);
                opacity: 0.2;
                pointer-events: none; /* Desabilita interações com o conteúdo enquanto o menu está aberto */
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding-top: 60px;
                transition: all 0.3s ease;
            }
            
            .stock-header {
                padding: 1rem;
                margin-bottom: 1.5rem;
            }
            
            .stock-title {
                width: 100%;
            }
            
            .d-flex.gap-3.align-items-center {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: space-between;
                width: 100%;
                gap: 10px !important;
            }
            
            .search-container {
                max-width: 65%;
                flex-basis: 65%;
            }
            
            .btn-primary {
                flex-basis: 30%;
                justify-content: center;
            }
            
            .table {
                font-size: 0.85rem;
            }
            
            .table th, .table td {
                padding: 0.5rem;
            }
            
            /* Ajustar as ações em telas pequenas */
            .btn-sm {
                padding: 0.25rem 0.4rem;
                font-size: 0.75rem;
            }
            
            .action-buttons {
                gap: 4px;
                min-width: auto;
                justify-content: flex-start;
            }
            
            .action-buttons .btn {
                padding: 0.25rem 0.35rem;
                font-size: 0.7rem;
                min-width: 28px;
                display: inline-flex;
                justify-content: center;
                align-items: center;
            }
            
            .action-buttons .btn i {
                font-size: 0.8rem;
            }
        }
        
        @media (max-width: 576px) {
            /* Ajustes para smartphones */
            .stock-title i {
                font-size: 1.5rem;
                padding: 8px;
            }
            
            .stock-title h4 {
                font-size: 1.2rem;
            }
            
            .search-container {
                max-width: 100%;
                flex-basis: 100%;
                margin-bottom: 10px;
            }
            
            .btn-primary {
                flex-basis: 100%;
                width: 100%;
            }
            
            .account-type-selector .btn {
                padding: 0.5rem 0.75rem;
                font-size: 0.9rem;
            }
            
            /* Ajustes para melhorar a visualização das tabelas em telas muito pequenas */
            .table-responsive {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            /* Ocultar algumas colunas em telas muito pequenas */
            .hide-xs {
                display: none;
            }
            
            /* Ajustar modal para telas pequenas */
            .modal-dialog {
                margin: 0.5rem;
            }
            
            .modal-body {
                padding: 1rem;
            }
            
            .modal-footer {
                padding: 0.75rem;
            }
            
            /* Ajustes adicionais para os botões de ação em telas muito pequenas */
            .action-buttons {
                gap: 3px;
                justify-content: center;
            }
            
            .action-buttons .btn {
                padding: 0.2rem 0.3rem;
                font-size: 0.65rem;
                min-width: 26px;
            }
            
            .action-buttons .btn i {
                font-size: 0.75rem;
            }
        }

        /* Estilos responsivos para tabela de contas em dispositivos móveis (similar ao dashboard.php) */
        @media (max-width: 767px) {
          .table-responsive table {
            min-width: auto !important;
          }
          
          .table-responsive table thead {
            display: none; /* Oculta o cabeçalho da tabela */
          }
          
          .table-responsive table, 
          .table-responsive table tbody, 
          .table-responsive table tr {
            display: block;
            width: 100%;
          }
          
          .table-responsive table tr {
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            background: white;
            overflow: hidden;
            position: relative;
            border-left: 4px solid #2a4d69;
            transition: all 0.3s ease;
          }
          
          .table-responsive table tr:hover {
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
            transform: translateY(-3px);
            border-left: 4px solid #0a4da0;
          }
          
          .table-responsive table td {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 15px !important;
            text-align: right !important;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            flex-wrap: wrap;
          }
          
          /* Estilo específico para emails longos */
          .table-responsive table td[data-label="Email"] {
            flex-direction: column;
            align-items: flex-start;
          }
          
          .table-responsive table td[data-label="Email"]:before {
            margin-bottom: 8px;
            display: block;
            width: 100%;
          }
          
          .table-responsive table td[data-label="Email"] span.email-content {
            width: 100%;
            word-break: break-all;
            text-align: left;
            overflow-wrap: break-word;
            hyphens: auto;
          }
          
          .table-responsive table td:last-child {
            border-bottom: none;
            justify-content: center;
            padding-top: 20px !important;
            padding-bottom: 20px !important;
            background-color: rgba(42, 77, 105, 0.05);
          }
          
          .table-responsive table td:before {
            content: attr(data-label) ": ";
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
          
          .table-responsive table tr {
            animation: borderGlow 3s infinite;
          }
          
          /* Ajuste para o ID ficar junto */
          .account-id {
            display: inline-block;
            font-weight: 700;
            color: #2a4d69;
          }
          
          .table-responsive table .account-actions-col {
            justify-content: center !important;
          }

          .table-responsive table .account-actions-col:before {
            display: none; /* Oculta label "Ações" acima dos botões de ação */
          }
        }
    </style>
</head>
<body>
    <button class="sidebar-toggle btn" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>

    <div class="d-flex">
        <!-- Sidebar -->
        <?php renderSidebar('contas', $_SESSION['userType'], $usuario['nome_completo'], $usuario['foto_perfil']); ?>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <div class="container-fluid p-4">
                <!-- Cabeçalho -->
                <div class="stock-header">
                    <div class="stock-title">
                        <i class="bi bi-people"></i>
                        <h4>Gestão de Contas</h4>
                    </div>
                    <div class="d-flex gap-3 align-items-center">
                        <div class="search-container">
                            <div class="input-group search-input-group">
                                <span class="input-group-text search-icon-container">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" 
                                       class="form-control search-input" 
                                       id="searchInput" 
                                       placeholder="Pesquisar conta...">
                            </div>
                        </div>
                        <a href="../utilizadores/nova-conta.php" class="btn btn-primary">
                            <i class="bi bi-person-plus me-2"></i>Adicionar
                        </a>
                    </div>
                </div>

                <!-- Seletor de Tipo de Conta -->
                <div class="account-type-selector">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary active" onclick="showAccounts('tecnico')">
                            Suporte Técnico
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="showAccounts('usuario')">
                            Comunidade Escolar
                        </button>
                    </div>
                </div>

                <!-- Tabelas de Contas -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <!-- Tabela Suporte Técnico -->
                        <div id="tecnicoTable" class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nome Completo</th>
                                        <th>Email</th>
                                        <th class="hide-xs">Nº Processo</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tecnicos as $tecnico): ?>
                                    <tr>
                                        <td data-label="ID"><span class="account-id"><?php echo $tecnico['id']; ?></span></td>
                                        <td data-label="Nome Completo"><?php echo $tecnico['nome_completo']; ?></td>
                                        <td data-label="Email"><span class="email-content"><?php echo $tecnico['email_institucional']; ?></span></td>
                                        <td data-label="Nº Processo" class="hide-xs"><?php echo $tecnico['numero_processo']; ?></td>
                                        <td data-label="Ações" class="action-buttons account-actions-col">
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="visualizarConta('tecnico', <?php echo $tecnico['id']; ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editarConta('tecnico', <?php echo $tecnico['id']; ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="apagarConta('tecnico', <?php echo $tecnico['id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Tabela de Usuários -->
                        <div id="usuarioTable" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nome</th>
                                            <th>Email</th>
                                            <th class="hide-xs">Cargo</th>
                                            <th class="hide-xs">Nº Processo</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($comunidade as $usuario): ?>
                                            <tr>
                                                <td data-label="ID"><span class="account-id"><?php echo htmlspecialchars($usuario['id']); ?></span></td>
                                                <td data-label="Nome"><?php echo htmlspecialchars($usuario['nome_completo']); ?></td>
                                                <td data-label="Email"><span class="email-content"><?php echo htmlspecialchars($usuario['email_institucional']); ?></span></td>
                                                <td data-label="Cargo" class="hide-xs"><?php echo htmlspecialchars(ucfirst($usuario['cargo'])); ?></td>
                                                <td data-label="Nº Processo" class="hide-xs"><?php echo htmlspecialchars($usuario['numero_processo']); ?></td>
                                                <td data-label="Ações" class="action-buttons account-actions-col">
                                                    <button class="btn btn-sm btn-outline-info" 
                                                            onclick="visualizarConta('usuario', <?php echo $usuario['id']; ?>)">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="editarConta('usuario', <?php echo $usuario['id']; ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="apagarConta('usuario', <?php echo $usuario['id']; ?>)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Confirmar Ação
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="mb-0 text-center fs-5">Tem a certeza que deseja apagar esta conta?</p>
                    <p class="text-center text-muted mt-2">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer justify-content-center border-0 pb-4">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-danger px-4" id="btnConfirmDelete">
                        <i class="bi bi-trash me-2"></i>Apagar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    
    <script>
        // Variáveis globais para armazenar informações da conta a ser apagada
        let contaParaApagar = {
            tipo: null,
            id: null
        };

        document.addEventListener('DOMContentLoaded', function() {
            // Verifica se deve mostrar o botão de toggle com base no tamanho da tela
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
            window.addEventListener('resize', checkToggleDisplay);
            
            // Função que permite o toggle do sidebar em telas pequenas
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
            
            // Verificar o tamanho da tela ao redimensionar e atualizar a interface
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    document.querySelector('.sidebar').classList.remove('active');
                    document.body.classList.remove('menu-active');
                    const overlay = document.querySelector('.sidebar-overlay');
                    if (overlay) overlay.remove();
                }
            });
            
            // Adicionar evento de pesquisa em tempo real
            document.getElementById('searchInput').addEventListener('input', function(e) {
                pesquisarContas(e.target.value);
            });

            // Configurar o modal de confirmação
            document.getElementById('btnConfirmDelete').addEventListener('click', function() {
                confirmarExclusao();
                const deleteModal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
                deleteModal.hide();
            });
        });
        
        function pesquisarContas(searchTerm) {
            searchTerm = searchTerm.toLowerCase();
            const tecnicoRows = document.querySelectorAll('#tecnicoTable tbody tr');
            const usuarioRows = document.querySelectorAll('#usuarioTable tbody tr');
            
            function filterRows(rows) {
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    const match = text.includes(searchTerm);
                    row.style.display = match ? '' : 'none';
                });
            }
            
            filterRows(tecnicoRows);
            filterRows(usuarioRows);
        }

        function showAccounts(type) {
            document.getElementById('tecnicoTable').style.display = type === 'tecnico' ? 'block' : 'none';
            document.getElementById('usuarioTable').style.display = type === 'usuario' ? 'block' : 'none';
            
            const buttons = document.querySelectorAll('.btn-group .btn');
            buttons.forEach(button => button.classList.remove('active'));
            event.target.classList.add('active');
        }

        function visualizarConta(tipo, id) {
            window.location.href = `visualizar_conta.php?tipo=${tipo}&id=${id}`;
        }

        function editarConta(tipo, id) {
            window.location.href = `editar_conta.php?tipo=${tipo}&id=${id}`;
        }

        function apagarConta(tipo, id) {
            // Armazenar informações da conta a ser apagada
            contaParaApagar.tipo = tipo;
            contaParaApagar.id = id;
            
            // Exibir o modal de confirmação
            const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            deleteModal.show();
        }

        function confirmarExclusao() {
            fetch('excluir_conta.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    tipo: contaParaApagar.tipo, 
                    id: contaParaApagar.id 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Toastify({
                        text: "Conta apagada com sucesso!",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                    
                    // Recarregar a página após 1.5 segundos para dar tempo de ver a notificação
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    Toastify({
                        text: data.message || "Erro ao apagar conta",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                        }
                    }).showToast();
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: "Erro ao processar requisição",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }
    </script>
</body>
</html>