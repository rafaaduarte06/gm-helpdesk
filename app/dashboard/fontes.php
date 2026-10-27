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
    $marcas = $pdo->query("SELECT MF_ID as id, MF_NOME as nome FROM marcafonte ORDER BY MF_NOME")->fetchAll();
    $modelos = $pdo->query("SELECT FONTE_MODELO as id, FONTE_MODELO as nome FROM fontemodelo ORDER BY FONTE_MODELO")->fetchAll();
    $cores = $pdo->query("SELECT ID_COR as id, COR as nome FROM cor ORDER BY COR")->fetchAll();
    $estados = $pdo->query("SELECT ID_ESTADO as id, ESTADO as nome FROM fontes_estado ORDER BY ESTADO")->fetchAll();
    $frequencias = $pdo->query("SELECT FONTE_FREQUENCIA as id, FONTE_FREQUENCIA as nome FROM fontefrequencia ORDER BY FONTE_FREQUENCIA")->fetchAll();
    $potencias = $pdo->query("SELECT FONTE_POTENCIA as id, FONTE_POTENCIA as nome FROM fontepotencia ORDER BY FONTE_POTENCIA")->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Fontes - GM HelpDesk</title>
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
                display: flex !important;
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
            
            .inventory-actions {
                display: flex;
                gap: 8px;
                white-space: nowrap;
                justify-content: flex-start;
            }
            
            .inventory-actions .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
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
            
            .inventory-actions {
                display: flex;
                gap: 4px;
                justify-content: center;
            }
            
            .inventory-actions .btn {
                padding: 0.2rem 0.4rem;
                font-size: 0.75rem;
            }
            
            .sem-reparo-badge {
                padding: 4px 6px;
                font-size: 11px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                line-height: 1.2;
                text-align: center;
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
            
            .table th, .table td {
                padding: 0.4rem 0.3rem;
                font-size: 0.75rem;
            }
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
            z-index: 1000;
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

        /* Estilo adicional para adaptação em telas menores */
        @media (max-width: 768px) {
            .back-to-inventory {
                padding: 0.6rem 1rem;
            }
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

        .estado-sem-reparo {
            color: #dc3545;
            font-weight: 500;
        }

        .estado-em-uso {
            color: #0d6efd;
            font-weight: 500;
        }

        .sem-reparo-badge {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            border: 1px solid rgba(220, 53, 69, 0.2);
            display: inline-block;
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
        
        /* Estilo para o backdrop dos modais */
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.7) !important;
        }
        
        /* Centralização vertical do modal */
        .modal {
            padding: 0 !important;
        }
        
        .modal-dialog {
            margin: 1.75rem auto;
            display: flex;
            align-items: center;
            min-height: calc(100% - 3.5rem);
        }
        
        /* Animação suave */
        .modal.fade .modal-dialog {
            transform: translate(0, -50px) scale(0.8);
            transition: transform 0.3s ease-in-out;
        }
        
        .modal.show .modal-dialog {
            transform: translate(0, 0) scale(1);
        }
        
        /* Estilo adicional para os modais pequenos */
        .modal-sm {
            max-width: 300px;
            width: 100%;
        }
        
        /* Efeito hover nos botões do modal */
        .modal .btn {
            transition: all 0.3s ease;
        }
        
        .modal .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
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

        /* Estilo para centralizar o texto na coluna Fonte */
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
        
        /* Estilo para mensagem de referência duplicada */
        .reference-error-box {
            border: 2px solid #ff0037;
            background-color: rgba(255, 0, 55, 0.1);
            color: #dc3545;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .reference-error-box .error-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 0.95rem;
        }
        
        .reference-error-box .error-message {
            font-size: 0.85rem;
            margin-bottom: 0;
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
                        <i class="bi bi-lightning-charge"></i>
                        <div>
                            <h4>Gestão de Fontes</h4>
                            <p class="mb-0 mt-1 text-light">Stock atual: <span id="stock-count" class="fw-bold">0</span> fontes</p>
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
                                       placeholder="Pesquisar fonte...">
                            </div>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFonteModal">
                            <i class="bi bi-plus-lg me-2"></i><span>Adicionar</span>
                        </button>
                    </div>
                </div>

                <!-- Tabela de Fontes -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Fonte</th>
                                        <th>Referência</th>
                                        <th>Marca</th>
                                        <th>Modelo</th>
                                        <th>Potência</th>
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

    <!-- Modal Adicionar/Editar Fonte -->
    <div class="modal fade" id="addFonteModal" tabindex="-1" aria-labelledby="modalTitle" aria-modal="true" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Adicionar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form id="addFonteForm">
                        <input type="hidden" id="isEditing" value="false">
                        <input type="hidden" id="fonteId" name="fonteId" value="">
                        
                        <!-- Mensagem de erro de referência duplicada -->
                        <div class="reference-error-box" id="referenceErrorBox" style="display: none;">
                            <div class="error-title">Referência Duplicada</div>
                            <div class="error-message">Essa referência já existe. Por favor, escolha outra.</div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Referência</label>
                            <input type="text" class="form-control" id="fonteRef" name="fonteRef" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Marca</label>
                            <div class="input-group">
                                <select class="form-select" id="fonteMarca" name="fonteMarca" required>
                                    <option value="">Selecione a marca</option>
                                    <?php foreach ($marcas as $marca): ?>
                                        <option value="<?= $marca['id'] ?>"><?= $marca['nome'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary" onclick="adicionarMarcaFonte()">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Modelo</label>
                            <div class="input-group">
                                <select class="form-select" id="fonteModelo" name="fonteModelo" required>
                                    <option value="">Selecione o modelo</option>
                                    <?php foreach ($modelos as $modelo): ?>
                                        <option value="<?= $modelo['id'] ?>"><?= $modelo['nome'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary" onclick="adicionarModeloFonte()">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Potência</label>
                            <div class="input-group">
                                <select class="form-select" id="fontePotencia" name="fontePotencia" required>
                                    <option value="">Selecione a potência</option>
                                    <?php foreach ($potencias as $potencia): ?>
                                        <option value="<?= $potencia['id'] ?>"><?= $potencia['nome'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary" onclick="adicionarPotencia()">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Frequência</label>
                            <div class="input-group">
                                <select class="form-select" id="fonteFrequencia" name="fonteFrequencia" required>
                                    <option value="">Selecione a frequência</option>
                                    <?php foreach ($frequencias as $frequencia): ?>
                                        <option value="<?= $frequencia['id'] ?>"><?= $frequencia['nome'] ?></option>
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
                                <select class="form-select" id="fonteCor" name="fonteCor" required>
                                    <option value="">Selecione a cor</option>
                                    <?php foreach ($cores as $cor): ?>
                                        <option value="<?= $cor['id'] ?>"><?= $cor['nome'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary" onclick="adicionarCor()">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group mb-3" id="estadoContainer">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="fonteEstado" name="fonteEstado" required>
                                <option value="">Selecione o estado</option>
                                <option value="1">Disponível</option>
                                <option value="3">Manutenção</option>
                                <option value="4">Sem Reparo</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveButton" onclick="salvarFonte()">Adicionar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Marca -->
    <div class="modal fade" id="addMarcaModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Marca</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="novaMarca" class="form-label">Nome da Marca</label>
                        <input type="text" class="form-control" id="novaMarca" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarNovaMarca(); return false;">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Modelo -->
    <div class="modal fade" id="addModeloModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Modelo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="novoModelo" class="form-label">Nome do Modelo</label>
                        <input type="text" class="form-control" id="novoModelo" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarNovoModelo(); return false;">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Frequência -->
    <div class="modal fade" id="addFrequenciaModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Frequência</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="novaFrequencia" class="form-label">Frequência</label>
                        <input type="text" class="form-control" id="novaFrequencia" required placeholder="Ex: 50-60Hz">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarNovaFrequencia(); return false;">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Potência -->
    <div class="modal fade" id="addPotenciaModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Potência</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="novaPotencia" class="form-label">Potência</label>
                        <input type="text" class="form-control" id="novaPotencia" required placeholder="Ex: 750W">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarNovaPotencia(); return false;">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Cor -->
    <div class="modal fade" id="addCorModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Cor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="novaCor" class="form-label">Nome da Cor</label>
                        <input type="text" class="form-control" id="novaCor" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarNovaCor(); return false;">Salvar</button>
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
                    <p class="mb-0">Confirma os registos corretos para a fonte que está sem reparo? Ao confirmar a ação não poderá ser desfeita.</p>
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
                    <input type="hidden" id="fonteIdToDelete">
                    <p class="mb-0">Tem a certeza que pretende apagar esta fonte?</p>
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
        // Função para resetar o formulário
        function resetForm() {
            document.getElementById('addFonteForm').reset();
            document.getElementById('fonteId').value = '';
            document.getElementById('isEditing').value = 'false';
            document.getElementById('modalTitle').textContent = 'Adicionar';
            document.getElementById('saveButton').textContent = 'Adicionar';
            
            // Remover readOnly e disabled dos campos
            document.getElementById('fonteRef').readOnly = false;
            document.getElementById('fonteEstado').disabled = false;
            
            // Resetar as opções desabilitadas no select de estado
            const estadoSelect = document.getElementById('fonteEstado');
            for (let i = 0; i < estadoSelect.options.length; i++) {
                estadoSelect.options[i].disabled = false;
            }
            
            // Esconder a mensagem de erro de referência duplicada
            document.getElementById('referenceErrorBox').style.display = 'none';
        }

        // Carregar dados iniciais
        document.addEventListener('DOMContentLoaded', function() {
            // Carregar a lista de fontes na inicialização da página
            loadFontes();
            
            // Configurar o evento de input para a pesquisa
            document.getElementById('searchInput').addEventListener('input', function() {
                if (this.value.length >= 2 || this.value.length === 0) {
                    pesquisarFontes();
                }
            });
            
            // Resetar formulário quando o modal for fechado
            document.getElementById('addFonteModal').addEventListener('hidden.bs.modal', function() {
                resetForm();
            });
            
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
            
            // Verificar se deve mostrar o botão de toggle com base no tamanho da tela
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
                        toggleButton.style.display = 'flex';
                    }, 50);
                });
            } else {
                // Remove classe do body
                body.classList.remove('menu-active');
                
                // Esconde o overlay se existir
                const overlay = document.querySelector('.sidebar-overlay');
                if (overlay) overlay.remove();
                
                // Certifica-se de que o botão de toggle está visível
                toggleButton.style.display = 'flex';
            }
        }

        // Verificar se deve mostrar o botão de toggle com base no tamanho da tela
        function checkToggleDisplay() {
            const sidebar = document.querySelector('.sidebar');
            const toggleButton = document.querySelector('.sidebar-toggle');
            
            if (window.innerWidth <= 768) {
                // Em dispositivos móveis, mostra o botão se o menu não estiver aberto
                if (!sidebar.classList.contains('active')) {
                    toggleButton.style.display = 'flex';
                }
            } else {
                // Em dispositivos maiores, esconde o botão
                toggleButton.style.display = 'none';
            }
        }

        // Variáveis temporárias para o formulário
        let formDataTemp = null;
        let isEditingTemp = false;
        let fonteIdTemp = null;

        // Função para salvar fonte
        function salvarFonte() {
            const form = document.getElementById('addFonteForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Esconder a mensagem de erro se estiver visível
            document.getElementById('referenceErrorBox').style.display = 'none';

            // Verificar se é uma nova adição (não edição)
            const isEditing = document.getElementById('isEditing').value === 'true';
            
            if (!isEditing) {
                // Verificar referência duplicada apenas ao adicionar novo
                const referencia = document.getElementById('fonteRef').value;
                
                // Verificar se a referência já existe
                fetch(`../stock/fonte/verificar_referencia.php?ref=${encodeURIComponent(referencia)}`)
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
            const fonteId = document.getElementById('fonteId').value.trim();
            const fonteRef = document.getElementById('fonteRef').value.trim();
            const fonteMarca = document.getElementById('fonteMarca').value.trim();
            const fonteModelo = document.getElementById('fonteModelo').value.trim();
            const fontePotencia = document.getElementById('fontePotencia').value.trim();
            const fonteFrequencia = document.getElementById('fonteFrequencia').value.trim();
            const fonteCor = document.getElementById('fonteCor').value.trim();
            const fonteEstado = document.getElementById('fonteEstado').value.trim();
            
            // Validar campos obrigatórios
            if (!fonteRef || !fonteMarca || !fonteModelo || !fontePotencia || !fonteFrequencia || !fonteCor || !fonteEstado) {
                Toastify({
                    text: "Todos os campos são obrigatórios",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
                return;
            }
            
            // Se o estado for "Sem Reparo", mostrar modal de confirmação
            if (fonteEstado === '4') {
                const confirmModal = new bootstrap.Modal(document.getElementById('confirmSemReparoModal'));
                confirmModal.show();
                
                formDataTemp = {
                    fonteRef,
                    fonteMarca,
                    fonteModelo,
                    fontePotencia,
                    fonteFrequencia,
                    fonteCor,
                    fonteEstado
                };
                isEditingTemp = Boolean(fonteId);
                fonteIdTemp = fonteId;
                return;
            }
            
            // Caso contrário, criar o objeto de dados e enviar
            const dadosFonte = {
                fonteRef,
                fonteMarca,
                fonteModelo,
                fontePotencia,
                fonteFrequencia,
                fonteCor,
                fonteEstado
            };
            
            // Se estiver editando, adicionar o ID
            if (fonteId) {
                dadosFonte.id = fonteId;
            }
            
            enviarFormulario(dadosFonte, Boolean(fonteId), fonteId);
        }

        function confirmarSemReparo() {
            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmSemReparoModal'));
            confirmModal.hide();
            
            // Capturar todos os campos do formulário
            const fonteId = document.getElementById('fonteId').value.trim();
            const fonteRef = document.getElementById('fonteRef').value.trim();
            const fonteMarca = document.getElementById('fonteMarca').value.trim();
            const fonteModelo = document.getElementById('fonteModelo').value.trim();
            const fontePotencia = document.getElementById('fontePotencia').value.trim();
            const fonteFrequencia = document.getElementById('fonteFrequencia').value.trim();
            const fonteCor = document.getElementById('fonteCor').value.trim();
            
            // Log para debug
            console.log("Confirmando Sem Reparo com os seguintes dados:");
            console.log("ID:", fonteId);
            console.log("Referência:", fonteRef);
            console.log("Marca:", fonteMarca);
            console.log("Modelo:", fonteModelo);
            console.log("Potência:", fontePotencia);
            console.log("Frequência:", fonteFrequencia);
            console.log("Cor:", fonteCor);
            
            // Criar um objeto com os dados coletados
            const dadosFonte = {
                fonteRef,
                fonteMarca,
                fonteModelo,
                fontePotencia,
                fonteFrequencia,
                fonteCor,
                fonteEstado: '4' // Definir explicitamente como "Sem Reparo"
            };
            
            // Se estiver editando, adicionar o ID
            const isEditing = Boolean(fonteId);
            if (isEditing) {
                dadosFonte.id = fonteId;
            }
            
            // Enviar os dados
            enviarFormulario(dadosFonte, isEditing, fonteId);
        }

        function enviarFormulario(dadosFonte, isEditing, fonteId) {
            const url = isEditing ? '../stock/fonte/atualizar_fonte.php' : '../stock/fonte/salvar_stock_fonte.php';
            
            // Log para debug
            console.log('Enviando dados para:', url);
            console.log('É edição?', isEditing);
            console.log('ID da fonte:', fonteId);
            console.log('Dados do formulário:', dadosFonte);
            
            // Garantir que o estado seja incluído mesmo se o campo estiver desabilitado
            if (isEditing && document.getElementById('fonteEstado').disabled) {
                dadosFonte.fonteEstado = document.getElementById('fonteEstado').value;
            }
            
            // Criar FormData a partir do objeto de dados
            const formData = new FormData();
            for (const [key, value] of Object.entries(dadosFonte)) {
                formData.append(key, value);
            }
            
            fetch(url, {
                method: 'POST',
                body: formData,
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
            .then(data => {
                if (data.success) {
                    // Limpar formulário e refresh da tabela
                    document.getElementById('addFonteForm').reset();
                    document.getElementById('fonteId').value = '';
                    
                    // Fechar o modal
                    const modalElement = document.getElementById('addFonteModal');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    modal.hide();
                    
                    // Notificação de sucesso
                    Toastify({
                        text: data.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                    
                    // Recarregar a lista
                    loadFontes();
                } else {
                    // Notificação de erro
                    Toastify({
                        text: data.message || "Erro ao processar a fonte",
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
                    text: "Erro ao processar: " + error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        // Função para carregar a lista de fontes
        function loadFontes() {
            console.log('Carregando lista de fontes...');
            fetch('../stock/fonte/buscar_stock_fontes.php?nocache=' + new Date().getTime(), {
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
                .then(data => {
                    console.log('Dados recebidos:', data);
                    if (data.success) {
                        const tbody = document.querySelector('tbody');
                        tbody.innerHTML = '';
                        
                        // Atualizar o contador de stock
                        const stockCount = document.getElementById('stock-count');
                        if (data.data && Array.isArray(data.data)) {
                            stockCount.textContent = data.data.length;
                        } else {
                            stockCount.textContent = "0";
                        }
                        
                        if (!data.data || data.data.length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="11" class="text-center">
                                        <div class="alert alert-info mb-0">
                                            <i class="bi bi-info-circle me-2"></i>
                                            Nenhuma fonte encontrada
                                        </div>
                                    </td>
                                </tr>
                            `;
                            return;
                        }
                        
                        data.data.forEach(fonte => {
                            console.log('Renderizando fonte:', fonte);
                            
                            // Verificação robusta do estado
                            const estado = fonte.estado || '';
                            const estadoId = fonte.estado_id || '';
                            
                            console.log(`ID: ${fonte.id}, Estado: "${estado}", Estado ID: "${estadoId}"`);
                            
                            // Verificamos tanto pelo texto quanto pelo ID do estado
                            const isSemReparo = 
                                (estado.toLowerCase && estado.toLowerCase().includes('sem reparo')) || 
                                estadoId === '4' ||
                                estadoId === 4;
                                
                            // Verificar se está "Em Uso"
                            const isEmUso = 
                                (estado.toLowerCase && estado.toLowerCase().includes('em uso')) || 
                                estadoId === '5' ||
                                estadoId === 5;
                            
                            let estadoClass = '';
                            if (estado.toLowerCase && (
                                estado.toLowerCase().includes('disponível') || 
                                estado.toLowerCase().includes('disponivel')) ||
                                estadoId === '1' ||
                                estadoId === 1) {
                                estadoClass = 'estado-disponivel';
                            } else if (estado.toLowerCase && (
                                estado.toLowerCase().includes('manutenção') || 
                                estado.toLowerCase().includes('manutencao')) ||
                                estadoId === '3' ||
                                estadoId === 3) {
                                estadoClass = 'estado-manutencao';
                            } else if (isSemReparo) {
                                estadoClass = 'estado-sem-reparo';
                            } else if (isEmUso) {
                                estadoClass = 'estado-em-uso';
                            }
                            
                            // Informação da sala
                            const salaInfo = fonte.FONTE_SALA ? 
                                `<strong>${fonte.FONTE_SALA}</strong>` : 
                                'Nenhuma Associada';
                                
                            // Botões de ação conforme o estado
                            let acoesBotoes = '';
                            
                            if (isSemReparo) {
                                acoesBotoes = '<span class="sem-reparo-badge">Sem Reparo</span>';
                            } else if (isEmUso) {
                                // Para componentes "Em Uso", mostrar apenas o botão de excluir
                                acoesBotoes = `
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button class="btn btn-sm btn-outline-danger" onclick="excluirFonte(${fonte.id})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                `;
                            } else {
                                // Para outros estados, mostrar botões de editar e excluir
                                acoesBotoes = `
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button class="btn btn-sm btn-outline-primary" onclick="editarFonte(${fonte.id})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="excluirFonte(${fonte.id})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                `;
                            }

                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-lightning-charge me-2 text-primary"></i>
                                        <strong>FONTE ${fonte.id}</strong>
                                    </div>
                                </td>
                                <td>${fonte.referencia || ''}</td>
                                <td>${fonte.marca || ''}</td>
                                <td>${fonte.modelo || ''}</td>
                                <td>${fonte.potencia || ''}</td>
                                <td>${fonte.frequencia || ''}</td>
                                <td>${fonte.cor || ''}</td>
                                <td>
                                    ${fonte.computador_ref ? 
                                        `<strong>${fonte.computador_ref}</strong>` : 
                                        'Nenhum Associado'
                                    }
                                </td>
                                <td>${salaInfo}</td>
                                <td><span class="${estadoClass}">${estado || (estadoId === '4' || estadoId === 4 ? 'Sem Reparo' : estadoId === '3' || estadoId === 3 ? 'Manutenção' : estadoId === '1' || estadoId === 1 ? 'Disponível' : '')}</span></td>
                                <td class="inventory-actions">
                                    ${acoesBotoes}
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    } else {
                        console.error('Erro ao carregar fontes:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar fontes:', error);
                });
        }

        // Função para editar fonte
        function editarFonte(id) {
            console.log('Iniciando edição da fonte:', id);
            
            fetch(`../stock/fonte/buscar_fonte.php?id=${id}&nocache=${new Date().getTime()}`, {
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
                    console.log('Resposta recebida');
                    return response.json();
                })
                .then(data => {
                    console.log('Dados recebidos:', data);
                    if (data.success) {
                        const fonte = data.data;
                        console.log('Dados da fonte:', fonte);

                        // Verificar se a fonte está em estado "Sem Reparo"
                        if (fonte.FONTE_ESTADO === '4') {
                            Toastify({
                                text: "Não é possível editar uma fonte marcada como Sem Reparo",
                                duration: 3000,
                                gravity: "top",
                                position: "right",
                                style: {
                                    background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                                }
                            }).showToast();
                            return;
                        }
                        
                        // Verificar se a fonte está em estado "Em Uso"
                        if (fonte.FONTE_ESTADO === '5') {
                            Toastify({
                                text: "Não é possível editar uma fonte que está em uso",
                                duration: 3000,
                                gravity: "top",
                                position: "right",
                                style: {
                                    background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                                }
                            }).showToast();
                            return;
                        }
                        
                        document.getElementById('fonteId').value = fonte.FONTE_ID;
                        document.getElementById('fonteRef').value = fonte.FONTE_REF;
                        document.getElementById('fonteRef').readOnly = true;
                        document.getElementById('fonteMarca').value = fonte.FONTE_MARCA;
                        document.getElementById('fonteModelo').value = fonte.FONTE_MODELO;
                        document.getElementById('fontePotencia').value = fonte.FONTE_POTENCIA;
                        document.getElementById('fonteFrequencia').value = fonte.FONTE_FREQUENCIA;
                        document.getElementById('fonteCor').value = fonte.FONTE_COR;
                        document.getElementById('fonteEstado').value = fonte.FONTE_ESTADO;
                        
                        // Verificar se o estado é "Manutenção" (ID 3)
                        if (fonte.FONTE_ESTADO === '3') {
                            document.getElementById('fonteEstado').disabled = false;
                            
                            // Limitar as opções para apenas "Disponível" (1) e "Sem Reparo" (4)
                            const estadoSelect = document.getElementById('fonteEstado');
                            for (let i = 0; i < estadoSelect.options.length; i++) {
                                const option = estadoSelect.options[i];
                                if (option.value !== '1' && option.value !== '4' && option.value !== '3') {
                                    option.disabled = true;
                                } else {
                                    option.disabled = false;
                                }
                            }
                        } else {
                            document.getElementById('fonteEstado').disabled = true;
                        }

                        document.getElementById('isEditing').value = 'true';
                        document.getElementById('modalTitle').textContent = 'Editar Fonte';
                        document.getElementById('saveButton').textContent = 'Salvar Alterações';

                        const estadoContainer = document.getElementById('estadoContainer');
                        estadoContainer.style.display = 'block';

                        const modal = new bootstrap.Modal(document.getElementById('addFonteModal'));
                        modal.show();
                    } else {
                        throw new Error(data.message || 'Erro ao carregar dados da fonte');
                    }
                })
                .catch(error => {
                    console.error('Erro ao editar fonte:', error);
                    Toastify({
                        text: error.message || "Erro ao carregar dados da fonte",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                        }
                    }).showToast();
                });
        }

        // Função para excluir fonte
        function excluirFonte(id) {
            document.getElementById('fonteIdToDelete').value = id;
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            confirmModal.show();
        }

        // Função para confirmar exclusão
        function confirmarExclusao() {
            const id = document.getElementById('fonteIdToDelete').value;
            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
            confirmModal.hide();
            
            const formData = new FormData();
            formData.append('id', id);
            
            fetch('../stock/fonte/excluir_fonte.php', {
                method: 'POST',
                body: formData,
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
            .then(data => {
                if (data.success) {
                    Toastify({
                        text: "Fonte excluída com sucesso!",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                    
                    loadFontes();
                } else {
                    Toastify({
                        text: data.message || "Erro ao excluir fonte",
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
                Toastify({
                    text: "Erro ao excluir fonte: " + error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        // Função para abrir modal de adicionar marca
        function adicionarMarcaFonte() {
            const modal = new bootstrap.Modal(document.getElementById('addMarcaModal'));
            modal.show();
        }

        // Função para pesquisar fontes
        function pesquisarFontes() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            fetch('../stock/fonte/buscar_stock_fontes.php?nocache=' + new Date().getTime(), {
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
            .then(data => {
                console.log('Dados recebidos para pesquisa:', data);
                if (data.success) {
                    const tbody = document.querySelector('tbody');
                    tbody.innerHTML = '';
                    
                    const filteredData = data.data.filter(fonte => {
                        const searchString = `${fonte.id || ''} ${fonte.referencia || ''} ${fonte.marca || ''} ${fonte.modelo || ''} ${fonte.potencia || ''} ${fonte.frequencia || ''} ${fonte.cor || ''} ${fonte.estado || ''} ${fonte.computador_ref || ''} ${fonte.FONTE_SALA || ''}`.toLowerCase();
                        return searchString.includes(searchTerm);
                    });

                    if (filteredData.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="11" class="text-center">
                                    <div class="alert alert-info mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Nenhuma fonte encontrada com os critérios de pesquisa
                                    </div>
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    
                    filteredData.forEach(fonte => {
                        console.log('Renderizando fonte:', fonte);
                        
                        // Verificação robusta do estado
                        const estado = fonte.estado || '';
                        const estadoId = fonte.estado_id || '';
                        
                        console.log(`ID: ${fonte.id}, Estado: "${estado}", Estado ID: "${estadoId}"`);
                        
                        // Verificamos tanto pelo texto quanto pelo ID do estado
                        const isSemReparo = 
                            (estado.toLowerCase && estado.toLowerCase().includes('sem reparo')) || 
                            estadoId === '4' ||
                            estadoId === 4;
                            
                        // Verificar se está "Em Uso"
                        const isEmUso = 
                            (estado.toLowerCase && estado.toLowerCase().includes('em uso')) || 
                            estadoId === '5' ||
                            estadoId === 5;
                        
                        let estadoClass = '';
                        if (estado.toLowerCase && (
                            estado.toLowerCase().includes('disponível') || 
                            estado.toLowerCase().includes('disponivel')) ||
                            estadoId === '1' ||
                            estadoId === 1) {
                            estadoClass = 'estado-disponivel';
                        } else if (estado.toLowerCase && (
                            estado.toLowerCase().includes('manutenção') || 
                            estado.toLowerCase().includes('manutencao')) ||
                            estadoId === '3' ||
                            estadoId === 3) {
                            estadoClass = 'estado-manutencao';
                        } else if (isSemReparo) {
                            estadoClass = 'estado-sem-reparo';
                        } else if (isEmUso) {
                            estadoClass = 'estado-em-uso';
                        }
                        
                        // Informação da sala
                        const salaInfo = fonte.FONTE_SALA ? 
                            `<strong>${fonte.FONTE_SALA}</strong>` : 
                            'Nenhuma Associada';
                            
                        // Botões de ação conforme o estado
                        let acoesBotoes = '';
                        
                        if (isSemReparo) {
                            acoesBotoes = '<span class="sem-reparo-badge">Sem Reparo</span>';
                        } else if (isEmUso) {
                            // Para componentes "Em Uso", mostrar apenas o botão de excluir
                            acoesBotoes = `
                                <div class="d-flex gap-1 justify-content-center">
                                    <button class="btn btn-sm btn-outline-danger" onclick="excluirFonte(${fonte.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            `;
                        } else {
                            // Para outros estados, mostrar botões de editar e excluir
                            acoesBotoes = `
                                <div class="d-flex gap-1 justify-content-center">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editarFonte(${fonte.id})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="excluirFonte(${fonte.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            `;
                        }

                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-lightning-charge me-2 text-primary"></i>
                                    <strong>FONTE ${fonte.id}</strong>
                                </div>
                            </td>
                            <td>${fonte.referencia || ''}</td>
                            <td>${fonte.marca || ''}</td>
                            <td>${fonte.modelo || ''}</td>
                            <td>${fonte.potencia || ''}</td>
                            <td>${fonte.frequencia || ''}</td>
                            <td>${fonte.cor || ''}</td>
                            <td>
                                ${fonte.computador_ref ? 
                                    `<strong>${fonte.computador_ref}</strong>` : 
                                    'Nenhum Associado'
                                }
                            </td>
                            <td>${salaInfo}</td>
                            <td><span class="${estadoClass}">${estado || (estadoId === '4' || estadoId === 4 ? 'Sem Reparo' : estadoId === '3' || estadoId === 3 ? 'Manutenção' : estadoId === '1' || estadoId === 1 ? 'Disponível' : '')}</span></td>
                            <td class="inventory-actions">
                                ${acoesBotoes}
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                } else {
                    console.error('Erro ao carregar fontes:', data.message);
                    const tbody = document.querySelector('tbody');
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="11" class="text-center">Erro ao carregar fontes: ${data.message}</td>
                        </tr>
                    `;
                }
            })
            .catch(error => {
                console.error('Erro ao pesquisar fontes:', error);
                Toastify({
                    text: "Erro ao pesquisar fontes: " + error.message,
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