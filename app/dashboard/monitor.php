<?php
session_start();
require_once('../ligacao_base_dados.php');
require_once('../includes/sidebar.php'); // Incluir o arquivo do sidebar

// Verificar se o usuário está logado e é um técnico
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    header('Location: ../login.php');
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

// Buscar dados das tabelas de referência
try {
    $marcas = $pdo->query("SELECT MM_ID, MM_NOME FROM marcamonitor ORDER BY MM_NOME")->fetchAll();
    $polegadas = $pdo->query("SELECT MONITOR_POL FROM monitorpolegada ORDER BY MONITOR_POL")->fetchAll();
    $frequencias = $pdo->query("SELECT MONITOR_FREQUENCIA FROM monitorfrequencia ORDER BY MONITOR_FREQUENCIA")->fetchAll();
    $cores = $pdo->query("SELECT ID_COR, COR FROM cor ORDER BY COR")->fetchAll();
    $estados = $pdo->query("SELECT ID_ESTADO, ESTADO FROM monitores_estado WHERE ESTADO IN ('Disponível', 'Manutenção', 'Sem Reparo') ORDER BY ESTADO")->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Monitores - GM HelpDesk</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
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

        /* Imagem de fundo que preenche todo o menu */
        .sidebar::before {
            display: none;
        }

        /* Logo da escola no rodapé do menu */
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

        @media (max-width: 768px) {
            .sidebar {
                left: -300px;
                transition: all 0.3s ease;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            
            .sidebar-toggle {
                display: block;
                position: fixed;
                top: 10px;
                left: 10px;
                z-index: 100;
            }
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

        /* Estilo para o cabeçalho da página */
        .stock-header {
            background: linear-gradient(135deg, #2a4d69, rgb(36, 110, 167));
            color: white;
            padding: 1.8rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            margin-top: 4rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: row;
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
            margin-bottom: 0;
            width: auto;
        }

        /* Estilos para os botões e search container */
        .stock-header .d-flex.gap-3.align-items-center {
            width: auto;
            display: flex;
            flex-direction: row;
            align-items: center !important;
            justify-content: flex-end;
            gap: 15px !important;
        }

        .search-container {
            flex-grow: 0;
            width: 300px;
            max-width: 450px;
        }

        @media (max-width: 992px) {
            .search-container {
                width: 250px;
            }
            
            .stock-header {
                padding: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .stock-header {
                flex-direction: column;
                align-items: flex-start;
                margin-top: 3rem;
            }
            
            .stock-title {
                margin-bottom: 1.5rem;
                width: 100%;
            }
            
            .stock-header .d-flex.gap-3.align-items-center {
                width: 100%;
                justify-content: flex-start;
            }
            
            .search-container {
                width: 100%;
                max-width: 100%;
            }
        }

        .back-to-inventory {
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 1500;
            display: inline-flex;
            align-items: center;
            padding: 0.7rem 1.2rem;
            background: linear-gradient(135deg, #2a4d69, rgb(36, 110, 167));
            color: white;
            border-radius: 10px;
            text-decoration: none;
            border: none;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease, opacity 0.3s ease, visibility 0.3s ease;
            overflow: hidden;
            z-index: 1500;
            opacity: 1;
            visibility: visible;
        }
        
        .back-to-inventory.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .back-to-inventory::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #3a6186, #4ca1af);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: 10px;
        }

        .back-to-inventory:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .back-to-inventory:hover::before {
            opacity: 1;
        }

        .back-to-inventory .back-icon {
            margin-right: 10px;
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .back-to-inventory:hover .back-icon {
            transform: translateX(-4px);
        }

        .back-to-inventory .back-text {
            font-size: 0.95rem;
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

        .search-input-group {
            display: flex;
            align-items: center;
            height: 45px;
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
            background-color: white;
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
            background-color: white;
            padding-left: 5px;
            color: #333;
        }

        .search-input:focus {
            box-shadow: none;
            background-color: white;
            outline: none;
        }

        .search-input::placeholder {
            color: #95a5a6;
        }

        /* Estados */
        .estado-disponivel {
            color: #28a745;
            font-weight: 500;
        }
        
        .estado-manutencao {
            color: #ffa500;
            font-weight: 500;
        }

        .estado-em-uso {
            color: #0d6efd;
            font-weight: 500;
        }
        
        .estado-sem-reparo {
            color: #dc3545;
            font-weight: 500;
        }
        
        .sem-reparo-badge {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            padding: 6px 8px;
            border-radius: 4px;
            font-size: 13px;
            border: 1px solid rgba(220, 53, 69, 0.2);
            text-align: center;
            display: inline-block;
            white-space: nowrap;
        }

        @media (max-width: 576px) {
            .sem-reparo-badge {
                padding: 4px 6px;
                font-size: 11px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                line-height: 1.2;
                text-align: center;
                white-space: normal;
            }
        }

        @media (max-width: 375px) {
            .sem-reparo-badge {
                padding: 3px 5px;
                font-size: 10px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                line-height: 1.1;
                text-align: center;
            }
        }

        .inventory-actions {
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
            white-space: nowrap;
        }
        
        .inventory-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        
        @media (max-width: 576px) {
            .inventory-actions {
                display: flex;
                flex-direction: row; 
                gap: 4px;
                justify-content: center;
                align-items: center;
            }
            
            .inventory-actions .btn {
                padding: 0.2rem 0.4rem;
                font-size: 0.75rem;
            }
            
            .table th, .table td {
                padding: 0.6rem 0.5rem;
                font-size: 0.8rem;
                vertical-align: middle;
            }
        }
        
        @media (max-width: 375px) {
            .table th, .table td {
                padding: 0.4rem 0.3rem;
                font-size: 0.75rem;
                vertical-align: middle;
            }
        }

        .table {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table th {
            font-weight: 600;
            padding: 0.85rem 0.75rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
            font-size: 0.9rem;
            vertical-align: middle;
        }
        
        .table td {
            padding: 0.85rem 0.75rem;
            vertical-align: middle;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .table tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
            color: #212529;
        }

        .table-responsive {
            /* Removendo limitação de altura e scroll interno */
            max-height: none;
            overflow-y: visible;
        }

        .card {
            position: relative;
            display: flex;
            flex-direction: column;
            min-width: 0;
            word-wrap: break-word;
            background-color: #fff;
            background-clip: border-box;
            border: 0 solid rgba(0, 0, 0, 0.125);
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-body {
            flex: 1 1 auto;
            padding: 1.25rem;
        }

        /* Mobile adjustments for table */
        @media (max-width: 768px) {
            .table td, .table th {
                padding: 0.5rem 0.5rem;
            }
            
            .table th {
                font-size: 0.85rem;
            }
        }

        @media (max-width: 576px) {
            .table {
                font-size: 0.875rem;
            }
            
            .table th {
                font-size: 0.8rem;
                padding: 0.5rem 0.4rem;
            }
            
            .table td {
                padding: 0.5rem 0.4rem;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 375px) {
            .table th {
                font-size: 0.75rem;
                padding: 0.4rem 0.3rem;
            }
            
            .table td {
                padding: 0.4rem 0.3rem;
                font-size: 0.75rem;
            }
        }

        /* Estilo para o modal de confirmação customizado */
        .custom-confirm-modal .modal-content {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .custom-confirm-modal .modal-header {
            background-color: #dc3545;
            color: white;
            border-bottom: none;
            border-radius: 8px 8px 0 0;
            padding: 1rem;
        }

        .custom-confirm-modal .modal-body {
            padding: 1rem;
        }

        .custom-confirm-modal .modal-footer {
            border-top: none;
            padding: 1rem;
        }

        .custom-confirm-modal .btn-confirm {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 20px;
        }

        .custom-confirm-modal .btn-confirm:hover {
            background-color: #c82333;
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
            
            .search-container {
                margin-top: 15px;
                width: 100%;
            }
            
            .back-to-inventory {
                top: 10px;
                right: 15px;
            }
        }

        @media (max-width: 768px) {
            /* Ajustes para dispositivos móveis */
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
                margin-left: 0;
                width: 100%;
                padding-top: 80px;
                transition: all 0.3s ease;
            }
            
            .sidebar-toggle {
                display: block !important;
            }
            
            .back-to-inventory {
                position: fixed;
            }
            
            /* Ajustes de layout para mobile */
            .stock-header {
                margin-top: 3rem;
            }
            
            .btn-primary {
                margin-top: 0;
                height: 45px;
                display: flex;
                align-items: center;
            }
            
            .stock-header .d-flex.gap-3.align-items-center {
                display: flex;
                align-items: center !important;
                gap: 10px !important;
            }
            
            .search-container {
                display: flex;
                align-items: center;
                height: 45px;
            }
        }

        @media (max-width: 576px) {
            .stock-header {
                padding: 1.2rem;
            }
            
            .stock-title {
                width: 100%;
                margin-bottom: 1.5rem;
            }
            
            .stock-title i {
                font-size: 1.5rem;
                padding: 8px;
            }
            
            .stock-title h4 {
                font-size: 1.25rem;
            }
            
            .stock-header .d-flex.gap-3.align-items-center {
                width: 100%;
                display: flex;
                flex-direction: row;
                align-items: flex-end !important;
                gap: 10px !important;
            }
            
            .search-container {
                flex-grow: 1;
                display: flex;
                align-items: center;
                margin-bottom: 5px; /* Adjust vertical position */
            }
            
            .search-input-group {
                height: 45px;
            }
            
            .btn-primary {
                padding: 0.5rem;
                width: 45px;
                height: 45px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-top: 14px;
            }
            
            .btn-primary .me-2 {
                margin-right: 0 !important;
            }
            
            .btn-primary span {
                display: none;
            }
        }

        @media (max-width: 375px) {
            .back-to-inventory .back-text {
                display: none;
            }
            
            .back-to-inventory {
                padding: 0.5rem;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .back-to-inventory .back-icon {
                margin-right: 0;
            }
        }

        /* Estilo para centralizar o texto na coluna Monitor */
        table th:first-child, 
        table td:first-child {
            text-align: center;
        }

        /* Estilos específicos para os botões dos modais */
        .modal .btn-primary,
        #saveButton,
        .modal-footer .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }
        
        .modal .btn-primary:hover,
        #saveButton:hover,
        .modal-footer .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        
        .modal .btn-primary:active,
        #saveButton:active,
        .modal-footer .btn-primary:active {
            background-color: #0a53be;
            border-color: #0a53be;
        }
    </style>
</head>
<body>
    <button class="sidebar-toggle btn" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>

    <div class="d-flex">
        <!-- Sidebar -->
        <?php renderSidebar('inventario', $_SESSION['userType'], $usuario['nome_completo'], $usuario['foto_perfil']); ?>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <div class="container-fluid p-4 position-relative">
                <!-- Botão Voltar -->
                <a href="inventario.php" class="back-to-inventory">
                    <i class="bi bi-arrow-left back-icon"></i>
                    <span class="back-text">Voltar para Inventário</span>
                </a>
                
                <!-- Cabeçalho -->
                <div class="stock-header">
                    <div class="stock-title">
                        <i class="bi bi-display"></i>
                        <div>
                            <h4>Gestão de Monitores</h4>
                            <p class="mb-0 mt-1 text-light">Stock atual: <span id="stock-count" class="fw-bold">0</span> monitores</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3 align-items-center">
                        <div class="search-container">
                            <div class="d-flex search-input-group">
                                <span class="search-icon-container">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" 
                                       class="form-control search-input" 
                                       id="searchInput" 
                                       placeholder="Pesquisar monitor...">
                            </div>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMonitorModal">
                            <i class="bi bi-plus-lg me-2"></i><span>Adicionar</span>
                        </button>
                    </div>
                </div>

                <!-- Tabela de Monitores -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Monitor</th>
                                        <th>Referência</th>
                                        <th>Marca</th>
                                        <th>Polegada</th>
                                        <th>Frequência</th>
                                        <th>Cor</th>
                                        <th>Computador</th>
                                        <th>Sala</th>
                                        <th>Estado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dados serão inseridos via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Adicionar/Editar Monitor -->
    <div class="modal fade" id="addMonitorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Adicionar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addMonitorForm">
                        <input type="hidden" id="isEditing" value="false">
                        <input type="hidden" id="monitorId" name="monitorId" value="">
                        
                        <!-- Mensagem de erro de referência duplicada -->
                        <div class="reference-error-box" id="referenceErrorBox" style="border: 2px solid #ff0037; background-color: rgba(255, 0, 55, 0.1); color: #dc3545; padding: 10px 15px; border-radius: 8px; margin-bottom: 15px; position: relative; display: none;">
                            <div class="error-title" style="font-weight: bold; margin-bottom: 5px; font-size: 0.95rem;">Referência Duplicada</div>
                            <div class="error-message" style="font-size: 0.85rem; margin-bottom: 0;">Essa referência já existe. Por favor, escolha outra.</div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Referência</label>
                            <input type="text" class="form-control" id="monitorRef" name="monitorRef" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Marca</label>
                            <div class="input-group">
                                <select class="form-select" id="monitorMarca" name="monitorMarca" required>
                                    <option value="">Selecione a marca</option>
                                    <?php foreach ($marcas as $marca): ?>
                                        <option value="<?= $marca['MM_ID'] ?>"><?= $marca['MM_NOME'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary" onclick="adicionarMarcaMonitor()">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Polegada</label>
                            <div class="input-group">
                                <select class="form-select" id="monitorPolegada" name="monitorPolegada" required>
                                    <option value="">Selecione a polegada</option>
                                    <?php foreach ($polegadas as $polegada): ?>
                                        <option value="<?= $polegada['MONITOR_POL'] ?>"><?= $polegada['MONITOR_POL'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary" onclick="adicionarPolegada()">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Frequência (Hz)</label>
                            <div class="input-group">
                                <select class="form-select" id="monitorFrequencia" name="monitorFrequencia" required>
                                    <option value="">Selecione a frequência</option>
                                    <?php foreach ($frequencias as $frequencia): ?>
                                        <option value="<?= $frequencia['MONITOR_FREQUENCIA'] ?>"><?= $frequencia['MONITOR_FREQUENCIA'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary" onclick="adicionarFrequencia()">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Cor</label>
                            <div class="input-group">
                                <select class="form-select" id="monitorCor" name="monitorCor" required>
                                    <option value="">Selecione a cor</option>
                                    <?php foreach ($cores as $cor): ?>
                                        <option value="<?= $cor['ID_COR'] ?>"><?= $cor['COR'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary" onclick="adicionarCor()">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group mb-3" id="estadoContainer">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="monitorEstado" name="monitorEstado" required>
                                <option value="">Selecione o estado</option>
                                <?php foreach ($estados as $estado): ?>
                                    <option value="<?= $estado['ID_ESTADO'] ?>"><?= $estado['ESTADO'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveButton" onclick="salvarMonitor()">Adicionar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Marca -->
    <div class="modal fade" id="addMarcaModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Marca</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nome da Marca</label>
                        <input type="text" class="form-control" id="novaMarca" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarNovaMarca()">Adicionar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Cor -->
    <div class="modal fade" id="addCorModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Cor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nome da Cor</label>
                        <input type="text" class="form-control" id="novaCor" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarNovaCor()">Adicionar</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Adicionar Polegada -->
    <div class="modal fade" id="addPolegadaModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Polegada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Polegada</label>
                        <input type="text" 
                               class="form-control" 
                               id="novaPolegada" 
                               placeholder="Ex: 24&quot;"
                               pattern="[0-9]+(\.[0-9]+)?"
                               required>
                        <small class="form-text text-muted">Digite apenas o número (ex: 24)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarNovaPolegada()">Adicionar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Frequência -->
    <div class="modal fade" id="addFrequenciaModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Frequência</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Frequência (Hz)</label>
                        <input type="text" 
                               class="form-control" 
                               id="novaFrequencia" 
                               placeholder="Ex: 60Hz"
                               pattern="[0-9]+"
                               required>
                        <small class="form-text text-muted">Digite apenas o número (ex: 60)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarNovaFrequencia()">Adicionar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação para Sem Reparo -->
    <div class="modal fade custom-confirm-modal" id="confirmSemReparoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmação de Estado Sem Reparo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Confirma os registos corretos para o monitor que está sem reparo? Ao confirmar a ação não poderá ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-confirm" onclick="confirmarSemReparo()">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação para Exclusão -->
    <div class="modal fade custom-confirm-modal" id="confirmDeleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmação de Exclusão</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="monitorIdToDelete">
                    <p class="mb-0">Tem a certeza que pretende apagar este monitor?</p>
                    <p class="text-muted mt-2">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="confirmarExclusao()">
                        <i class="bi bi-trash me-2"></i>Apagar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Carregar dados iniciais
        document.addEventListener('DOMContentLoaded', function() {
            loadMonitores();
            
            // Controle do botão "Voltar para Inventário"
            const backButton = document.querySelector('.back-to-inventory');
            let lastScrollTop = 0;
            let scrollingUp = false;
            
            window.addEventListener('scroll', function() {
                const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
                
                // Determina a direção do scroll
                scrollingUp = currentScroll < lastScrollTop;
                
                // Se rolando para baixo e já passou do cabeçalho, esconde o botão
                if (!scrollingUp && currentScroll > 150) {
                    backButton.classList.add('hidden');
                }
                
                // Se rolando para cima e está próximo ao topo, mostra o botão
                if (scrollingUp && currentScroll < 150) {
                    backButton.classList.remove('hidden');
                }
                
                lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
            });
            
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
            window.addEventListener('resize', function() {
                checkToggleDisplay();
                
                // Se redimensionar para desktop, certifique-se de remover classes
                if (window.innerWidth > 768) {
                    const sidebar = document.querySelector('.sidebar');
                    sidebar.classList.remove('active');
                    document.body.classList.remove('menu-active');
                    const overlay = document.querySelector('.sidebar-overlay');
                    if (overlay) overlay.remove();
                }
            });
            
            // Pesquisa em tempo real
            document.getElementById('searchInput').addEventListener('input', function() {
                if (this.value.length >= 2 || this.value.length === 0) {
                    pesquisarMonitores();
                }
            });
            
            // Resetar formulário quando o modal for fechado
            document.getElementById('addMonitorModal').addEventListener('hidden.bs.modal', function () {
                resetForm();
            });
        });

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

        // Funções de Gestão de Monitores
        function loadMonitores() {
            fetch('../stock/monitor/buscar_stock_monitores.php?nocache=' + new Date().getTime(), {
                method: 'GET',
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor: ' + response.status);
                }
                return response.json();
            })
            .then(response => {
                if (!response.success) {
                    throw new Error(response.message || 'Erro ao carregar dados');
                }
                
                // Atualizar o contador de stock
                const stockCount = document.getElementById('stock-count');
                if (response.data && Array.isArray(response.data)) {
                    stockCount.textContent = response.data.length;
                } else {
                    stockCount.textContent = "0";
                }
                
                const tbody = document.querySelector('table tbody');
                if (!response.data || response.data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="10" class="text-center">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Nenhum monitor encontrado
                                </div>
                            </td>
                        </tr>
                    `;
                    return;
                }
                tbody.innerHTML = response.data.map(monitor => {
                    // Verificar se o estado é "Sem Reparo"
                    const isSemReparo = monitor.estado === 'Sem Reparo' || monitor.MONITOR_ESTADO === '4';
                    // Verificar se o estado é "Em Uso"
                    const isEmUso = monitor.estado === 'Em Uso';
                    
                    let estadoClass = '';
                    switch(monitor.estado) {
                        case 'Disponível':
                            estadoClass = 'estado-disponivel';
                            break;
                        case 'Manutenção':
                            estadoClass = 'estado-manutencao';
                            break;
                        case 'Em Uso':
                            estadoClass = 'estado-em-uso';
                            break;
                        case 'Sem Reparo':
                            estadoClass = 'estado-sem-reparo';
                            break;
                    }
                    
                    // Informação da sala
                    const salaInfo = monitor.MONITOR_SALA ? 
                        `<strong>${monitor.MONITOR_SALA}</strong>` : 
                        'Nenhuma Associada';
                    
                    // Definir ações com base no estado
                    let acoesBotoes = '';
                    
                    if (isSemReparo) {
                        acoesBotoes = '<span class="sem-reparo-badge"><span>Sem</span> <span>Reparo</span></span>';
                    } else if (isEmUso) {
                        // Para monitores "Em Uso", mostrar apenas o botão de excluir
                        acoesBotoes = `
                            <div class="inventory-actions">
                                <button class="btn btn-sm btn-outline-danger" onclick="excluirMonitor(${monitor.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        `;
                    } else {
                        // Para outros estados, mostrar botões de editar e excluir
                        acoesBotoes = `
                            <div class="inventory-actions">
                                <button class="btn btn-sm btn-outline-primary" onclick="editarMonitor(${monitor.id})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="excluirMonitor(${monitor.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                    
                    return `
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-display me-2 text-primary"></i>
                                    <strong>MONITOR ${monitor.id}</strong>
                                </div>
                            </td>
                            <td>${monitor.referencia}</td>
                            <td>${monitor.marca}</td>
                            <td>${monitor.polegada}</td>
                            <td>${monitor.frequencia}</td>
                            <td>${monitor.cor}</td>
                            <td>
                                ${monitor.computador_ref ? 
                                    `<strong>${monitor.computador_ref}</strong>` : 
                                    'Nenhum Associado'
                                }
                            </td>
                            <td>${salaInfo}</td>
                            <td><span class="${estadoClass}">${monitor.estado}</span></td>
                            <td>${acoesBotoes}</td>
                        </tr>
                    `;
                }).join('');
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: "Erro ao carregar monitores: " + error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        function pesquisarMonitores() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            fetch('../stock/monitor/buscar_stock_monitores.php?nocache=' + new Date().getTime(), {
                method: 'GET',
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor: ' + response.status);
                }
                return response.json();
            })
            .then(response => {
                if (!response.success) {
                    throw new Error(response.message || 'Erro ao pesquisar dados');
                }
                const filteredData = response.data.filter(monitor => {
                    return (
                        (monitor.id ? monitor.id.toString().toLowerCase().includes(searchTerm) : false) ||
                        (monitor.referencia ? monitor.referencia.toLowerCase().includes(searchTerm) : false) ||
                        (monitor.marca ? monitor.marca.toLowerCase().includes(searchTerm) : false) ||
                        (monitor.polegada ? monitor.polegada.toLowerCase().includes(searchTerm) : false) ||
                        (monitor.frequencia ? monitor.frequencia.toLowerCase().includes(searchTerm) : false) ||
                        (monitor.cor ? monitor.cor.toLowerCase().includes(searchTerm) : false) ||
                        (monitor.MONITOR_SALA ? monitor.MONITOR_SALA.toLowerCase().includes(searchTerm) : false) ||
                        (monitor.estado ? monitor.estado.toLowerCase().includes(searchTerm) : false)
                    );
                });

                const tbody = document.querySelector('table tbody');
                if (filteredData.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="10" class="text-center">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Nenhum monitor encontrado com os critérios de pesquisa
                                </div>
                            </td>
                        </tr>
                    `;
                } else {
                    tbody.innerHTML = filteredData.map(monitor => {
                        // Verificar se o estado é "Sem Reparo"
                        const isSemReparo = monitor.estado === 'Sem Reparo' || monitor.MONITOR_ESTADO === '4';
                        // Verificar se o estado é "Em Uso"
                        const isEmUso = monitor.estado === 'Em Uso';
                        
                        let estadoClass = '';
                        switch(monitor.estado) {
                            case 'Disponível':
                                estadoClass = 'estado-disponivel';
                                break;
                            case 'Manutenção':
                                estadoClass = 'estado-manutencao';
                                break;
                            case 'Em Uso':
                                estadoClass = 'estado-em-uso';
                                break;
                            case 'Sem Reparo':
                                estadoClass = 'estado-sem-reparo';
                                break;
                        }
                        
                        // Informação da sala
                        const salaInfo = monitor.MONITOR_SALA ? 
                            `<strong>${monitor.MONITOR_SALA}</strong>` : 
                            'Nenhuma Associada';
                        
                        // Definir ações com base no estado
                        let acoesBotoes = '';
                        
                        if (isSemReparo) {
                            acoesBotoes = '<span class="sem-reparo-badge"><span>Sem</span> <span>Reparo</span></span>';
                        } else if (isEmUso) {
                            // Para monitores "Em Uso", mostrar apenas o botão de excluir
                            acoesBotoes = `
                                <div class="inventory-actions">
                                    <button class="btn btn-sm btn-outline-danger" onclick="excluirMonitor(${monitor.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            `;
                        } else {
                            // Para outros estados, mostrar botões de editar e excluir
                            acoesBotoes = `
                                <div class="inventory-actions">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editarMonitor(${monitor.id})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="excluirMonitor(${monitor.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            `;
                        }
                        
                        return `
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-display me-2 text-primary"></i>
                                        <strong>MONITOR ${monitor.id}</strong>
                                    </div>
                                </td>
                                <td>${monitor.referencia}</td>
                                <td>${monitor.marca}</td>
                                <td>${monitor.polegada}</td>
                                <td>${monitor.frequencia}</td>
                                <td>${monitor.cor}</td>
                                <td>
                                    ${monitor.computador_ref ? 
                                        `<strong>${monitor.computador_ref}</strong>` : 
                                        'Nenhum Associado'
                                    }
                                </td>
                                <td>${salaInfo}</td>
                                <td><span class="${estadoClass}">${monitor.estado}</span></td>
                                <td>${acoesBotoes}</td>
                            </tr>
                        `;
                    }).join('');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: "Erro ao pesquisar monitores: " + error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        function editarMonitor(id) {
            fetch(`../stock/monitor/buscar_monitor.php?id=${id}&nocache=${new Date().getTime()}`, {
                method: 'GET',
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Verificar se o monitor está em estado "Sem Reparo"
                    if (data.data.MONITOR_ESTADO === '4') { // ID 4 é "Sem Reparo"
                        Toastify({
                            text: "Não é possível editar um monitor marcado como Sem Reparo",
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            style: {
                                background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                            }
                        }).showToast();
                        return;
                    }

                    // Verificar se o monitor está em estado "Em Uso"
                    if (data.data.MONITOR_ESTADO === '2') { // Supondo que ID 2 é "Em Uso"
                        Toastify({
                            text: "Não é possível editar um monitor em uso",
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            style: {
                                background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                            }
                        }).showToast();
                        return;
                    }
                    
                    // Mostrar notificação informativa quando editar um monitor disponível
                    if (data.data.MONITOR_ESTADO === '1') { // ID 1 é "Disponível"
                        Toastify({
                            text: "Monitor está Disponível. O estado não pode ser alterado.",
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            style: {
                                background: "linear-gradient(to right, #007bff, #6610f2)",
                            }
                        }).showToast();
                    }
                    
                    // Preencher os campos do formulário
                    document.getElementById('monitorId').value = data.data.MONITOR_ID;
                    document.getElementById('monitorRef').value = data.data.MONITOR_REF;
                    document.getElementById('monitorRef').readOnly = true;
                    document.getElementById('monitorMarca').value = data.data.MONITOR_MARCA;
                    document.getElementById('monitorPolegada').value = data.data.MONITOR_POL;
                    document.getElementById('monitorFrequencia').value = data.data.MONITOR_FREQ;
                    document.getElementById('monitorCor').value = data.data.MONITOR_COR;
                    
                    // Esconder e desabilitar o campo de estado na edição
                    const estadoContainer = document.getElementById('estadoContainer');
                    const monitorEstado = document.getElementById('monitorEstado');
                    estadoContainer.style.display = 'block';
                    monitorEstado.required = true;
                    monitorEstado.value = data.data.MONITOR_ESTADO;
                    
                    // Verificar se o estado é "Manutenção" (ID 3)
                    if (data.data.MONITOR_ESTADO == '3') {
                        monitorEstado.disabled = false;
                        
                        // Limitar as opções para apenas "Disponível" (1), "Sem Reparo" (4) e "Manutenção" (3)
                        for (let i = 0; i < monitorEstado.options.length; i++) {
                            const option = monitorEstado.options[i];
                            if (option.value !== '1' && option.value !== '4' && option.value !== '3') {
                                option.disabled = true;
                            } else {
                                option.disabled = false;
                            }
                        }
                    } else {
                        monitorEstado.disabled = true;
                    }
                    
                    document.getElementById('isEditing').value = 'true';
                    document.getElementById('modalTitle').textContent = 'Editar Monitor ' + data.data.MONITOR_ID;
                    document.getElementById('saveButton').textContent = 'Salvar Alterações';
                    
                    const modal = new bootstrap.Modal(document.getElementById('addMonitorModal'));
                    modal.show();
                } else {
                    throw new Error(data.message || 'Erro ao carregar dados do monitor');
                }
            })
            .catch(error => {
                Toastify({
                    text: error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        // Variáveis globais para o formulário
        let formDataTemp = null;

        function salvarMonitor() {
            event.preventDefault();
            
            // Esconder a mensagem de erro se estiver visível
            document.getElementById('referenceErrorBox').style.display = 'none';
            
            // Verificar se é uma nova adição (não edição)
            const isEditing = document.getElementById('isEditing').value === 'true';
            
            if (!isEditing) {
                // Verificar referência duplicada apenas ao adicionar novo
                const referencia = document.getElementById('monitorRef').value;
                
                // Verificar se a referência já existe
                fetch(`../stock/monitor/verificar_referencia.php?ref=${encodeURIComponent(referencia)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        // Referência já existe, mostrar mensagem de erro
                        document.getElementById('referenceErrorBox').style.display = 'block';
                    } else {
                        // Referência não existe, continuar com o salvamento
                        continuarSalvamento();
                    }
                })
                .catch(error => {
                    console.error('Erro ao verificar referência:', error);
                    Toastify({
                        text: "Erro ao verificar referência: " + error.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                        }
                    }).showToast();
                });
            } else {
                // Se for edição, continuar normalmente
                continuarSalvamento();
            }
        }

        function continuarSalvamento() {
            formDataTemp = new FormData(document.getElementById('addMonitorForm'));
            const isEditing = document.getElementById('isEditing').value === 'true';
            
            // Garantir que o estado seja incluído mesmo que o campo esteja desabilitado
            if (isEditing && document.getElementById('monitorEstado').disabled) {
                formDataTemp.set('monitorEstado', document.getElementById('monitorEstado').value);
            }
            
            // Se estiver alterando de manutenção para disponível, informar o usuário
            if (isEditing && 
                document.getElementById('monitorEstado').disabled === false && 
                document.getElementById('monitorEstado').value === '1') {
                Toastify({
                    text: "Atenção: Após salvar, não será possível alterar o estado novamente",
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff9800, #f44336)",
                    }
                }).showToast();
            }
            
            if (isEditing) {
                formDataTemp.append('id', document.getElementById('monitorId').value);
            }
            
            // Verificar se o estado é "Sem Reparo"
            if (formDataTemp.get('monitorEstado') === '4') { // ID do estado "Sem Reparo"
                this.formDataTemp = formDataTemp; // Armazenar temporariamente para usar em confirmarSemReparo
                const confirmModal = new bootstrap.Modal(document.getElementById('confirmSemReparoModal'));
                confirmModal.show();
            } else {
                enviarFormulario(formDataTemp);
            }
        }

        function confirmarSemReparo() {
            if (formDataTemp) {
                // Garantir que o estado seja 4 (Sem Reparo)
                formDataTemp.set('monitorEstado', '4');
                
                // Ocultar o modal de confirmação
                const modal = bootstrap.Modal.getInstance(document.getElementById('confirmSemReparoModal'));
                modal.hide();
                
                // Enviar o formulário
                enviarFormulario(formDataTemp);
            }
        }

        function enviarFormulario(formData) {
            const isEditing = document.getElementById('isEditing').value === 'true';
            const url = isEditing 
                ? '../stock/monitor/atualizar_monitor.php'
                : '../stock/monitor/salvar_stock_monitor.php';
            
            // Criar nova FormData para garantir que todos os campos sejam enviados corretamente
            const newFormData = new FormData();
            
            if (isEditing) {
                newFormData.append('id', document.getElementById('monitorId').value);
            }
            
            newFormData.append('monitorRef', document.getElementById('monitorRef').value);
            newFormData.append('monitorMarca', document.getElementById('monitorMarca').value);
            newFormData.append('monitorPolegada', document.getElementById('monitorPolegada').value);
            newFormData.append('monitorFrequencia', document.getElementById('monitorFrequencia').value);
            newFormData.append('monitorCor', document.getElementById('monitorCor').value);
            
            // Garantir que o estado seja incluído mesmo que o campo esteja desabilitado
            newFormData.append('monitorEstado', document.getElementById('monitorEstado').value);
            
            fetch(url, {
                method: 'POST',
                body: newFormData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor: ' + response.status);
                }
                return response.text();
            })
            .then(text => {
                try {
                    return JSON.parse(text);  // Tentar fazer o parse do JSON
                } catch (e) {
                    console.error('Resposta não-JSON:', text);
                    throw new Error('Resposta inválida do servidor: ' + text);
                }
            })
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addMonitorModal'));
                    modal.hide();
                    
                    Toastify({
                        text: isEditing ? "Monitor atualizado com sucesso!" : "Monitor adicionado com sucesso!",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                    
                    // Aguardar um pequeno intervalo antes de recarregar os dados
                    setTimeout(() => {
                        loadMonitores();
                    }, 300);
                    
                    resetForm();
                } else {
                    throw new Error(data.message || 'Erro ao salvar monitor');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        function excluirMonitor(id) {
            document.getElementById('monitorIdToDelete').value = id;
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            confirmModal.show();
        }
        
        function confirmarExclusao() {
            const id = document.getElementById('monitorIdToDelete').value;
            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
            confirmModal.hide();
            
            const formData = new FormData();
            formData.append('id', id);
            
            fetch('../stock/monitor/excluir_monitor.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    Toastify({
                        text: "Monitor excluído com sucesso!",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                    
                    // Aguardar um pequeno intervalo antes de recarregar os dados
                    setTimeout(() => {
                        loadMonitores();
                    }, 300);
                } else {
                    throw new Error(data.message || 'Erro ao excluir monitor');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        function resetForm() {
            document.getElementById('addMonitorForm').reset();
            document.getElementById('isEditing').value = 'false';
            document.getElementById('monitorId').value = '';
            document.getElementById('modalTitle').textContent = 'Adicionar';
            document.getElementById('saveButton').textContent = 'Adicionar';
            document.getElementById('monitorRef').readOnly = false;
            document.getElementById('monitorEstado').disabled = false;
            
            // Resetar as opções desabilitadas no select de estado
            const estadoSelect = document.getElementById('monitorEstado');
            for (let i = 0; i < estadoSelect.options.length; i++) {
                estadoSelect.options[i].disabled = false;
            }
            
            // Esconder a mensagem de erro de referência duplicada
            document.getElementById('referenceErrorBox').style.display = 'none';
        }

        function adicionarMarcaMonitor() {
            const modal = new bootstrap.Modal(document.getElementById('addMarcaModal'));
            modal.show();
        }

        function salvarNovaMarca() {
            const nomeMarca = document.getElementById('novaMarca').value;
            if (!nomeMarca) return;

            const formData = new FormData();
            formData.append('nome_marca', nomeMarca);

            fetch('../stock/monitor/adicionar_marca_monitor.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('monitorMarca');
                    const option = new Option(data.marca.MM_NOME, data.marca.MM_ID);
                    select.add(option);
                    select.value = data.marca.MM_ID;

                    // Fechar o modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addMarcaModal'));
                    modal.hide();
                    
                    // Limpar o campo
                    document.getElementById('novaMarca').value = '';

                    Toastify({
                        text: data.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                }
            })
            .catch(error => {
                Toastify({
                    text: "Erro ao adicionar marca",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        function adicionarCor() {
            const modal = new bootstrap.Modal(document.getElementById('addCorModal'));
            modal.show();
        }

        function salvarNovaCor() {
            const nomeCor = document.getElementById('novaCor').value;
            if (!nomeCor) return;

            const formData = new FormData();
            formData.append('nome_cor', nomeCor);

            fetch('../dashboard/cor/adicionar_cor.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('monitorCor');
                    const option = new Option(data.cor.COR, data.cor.ID_COR);
                    select.add(option);
                    select.value = data.cor.ID_COR;

                    // Fechar o modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addCorModal'));
                    modal.hide();
                    
                    // Limpar o campo
                    document.getElementById('novaCor').value = '';

                    Toastify({
                        text: data.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                }
            })
            .catch(error => {
                Toastify({
                    text: "Erro ao adicionar cor",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        function adicionarPolegada() {
            const modal = new bootstrap.Modal(document.getElementById('addPolegadaModal'));
            modal.show();
        }

        function salvarNovaPolegada() {
            const polegada = document.getElementById('novaPolegada').value;
            if (!polegada) return;

            const formData = new FormData();
            formData.append('polegada', polegada);

            fetch('../stock/monitor/adicionar_polegada_monitor.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('monitorPolegada');
                    const option = new Option(data.polegada.MONITOR_POL, data.polegada.MONITOR_POL);
                    select.add(option);
                    select.value = data.polegada.MONITOR_POL;

                    // Fechar o modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addPolegadaModal'));
                    modal.hide();
                    
                    // Limpar o campo
                    document.getElementById('novaPolegada').value = '';

                    Toastify({
                        text: data.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                }
            })
            .catch(error => {
                Toastify({
                    text: "Erro ao adicionar polegada",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        function adicionarFrequencia() {
            const modal = new bootstrap.Modal(document.getElementById('addFrequenciaModal'));
            modal.show();
        }

        function salvarNovaFrequencia() {
            const frequencia = document.getElementById('novaFrequencia').value;
            if (!frequencia) return;

            const formData = new FormData();
            formData.append('frequencia', frequencia);

            fetch('../stock/monitor/adicionar_frequencia_monitor.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('monitorFrequencia');
                    const option = new Option(data.frequencia.MONITOR_FREQUENCIA, data.frequencia.MONITOR_FREQUENCIA);
                    select.add(option);
                    select.value = data.frequencia.MONITOR_FREQUENCIA;

                    // Fechar o modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addFrequenciaModal'));
                    modal.hide();
                    
                    // Limpar o campo
                    document.getElementById('novaFrequencia').value = '';

                    Toastify({
                        text: data.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                }
            })
            .catch(error => {
                Toastify({
                    text: "Erro ao adicionar frequência",
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