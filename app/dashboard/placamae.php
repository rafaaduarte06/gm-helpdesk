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
    $marcas = $pdo->query("SELECT MPM_ID as id, MPM_NOME as nome FROM marcapm ORDER BY MPM_NOME")->fetchAll();
    $tamanhos = $pdo->query("SELECT PM_TAM as id, PM_TAM as nome FROM pmtam ORDER BY PM_TAM")->fetchAll();
    $sockets = $pdo->query("SELECT PM_SOCKET as id, PM_SOCKET as nome FROM pmsocket ORDER BY PM_SOCKET")->fetchAll();
    $chipsets = $pdo->query("SELECT PM_CHIPSET as id, PM_CHIPSET as nome FROM pmchipset ORDER BY PM_CHIPSET")->fetchAll();
    $formatos = $pdo->query("SELECT PM_FORMATO as id, PM_FORMATO as nome FROM pmformato ORDER BY PM_FORMATO")->fetchAll();
    $tmemorias = $pdo->query("SELECT PM_TMEMORIA as id, PM_TMEMORIA as nome FROM pmtmemoria ORDER BY PM_TMEMORIA")->fetchAll();
    $cores = $pdo->query("SELECT ID_COR as id, COR as nome FROM cor ORDER BY COR")->fetchAll();
    $estados = $pdo->query("SELECT ID_ESTADO as id, ESTADO as nome FROM placamae_estado ORDER BY ESTADO")->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Motherboards - GM HelpDesk</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
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
                display: flex;
                justify-content: center;
            }
            
            .container-fluid {
                padding: 3.5rem 1.5rem 1.5rem 1.5rem !important;
            }
            
            .sidebar-toggle {
                display: block !important;
            }
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

        /* Estilo para botão principal */
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

        /* Novo estilo para a tabela de placas-mãe */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table th {
            white-space: nowrap;
            background-color: #f8f9fa;
        }

        .table td {
            vertical-align: middle;
        }

        /* Estilo para os botões de ação */
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Estilo para campos obrigatórios */
        .form-label.required::after {
            content: "*";
            color: red;
            margin-left: 4px;
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

        /* Estilo para os botões de ação */
        .inventory-actions {
            white-space: nowrap;
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .inventory-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.375rem;
            transition: all 0.2s ease-in-out;
        }

        .inventory-actions .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .inventory-actions .btn-outline-primary {
            color: #0d6efd;
            border-color: #0d6efd;
        }

        .inventory-actions .btn-outline-primary:hover {
            color: #fff;
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .inventory-actions .btn-outline-danger {
            color: #dc3545;
            border-color: #dc3545;
        }

        .inventory-actions .btn-outline-danger:hover {
            color: #fff;
            background-color: #dc3545;
            border-color: #dc3545;
        }

        /* Estilo para centralizar o texto na coluna Motherboard */
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
                        <i class="bi bi-motherboard"></i>
                        <div>
                            <h4>Gestão de Motherboards</h4>
                            <p class="mb-0 mt-1 text-light">Stock atual: <span id="stock-count" class="fw-bold">0</span> motherboards</p>
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
                                       placeholder="Pesquisar motherboard...">
                            </div>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlacaMaeModal">
                            <i class="bi bi-plus-lg me-2"></i><span>Adicionar</span>
                        </button>
                    </div>
                </div>

                <!-- Tabela de Placas-Mãe -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Motherboard</th>
                                        <th>Referência</th>
                                        <th>Marca</th>
                                        <th>Tamanho</th>
                                        <th>Socket</th>
                                        <th>Chipset</th>
                                        <th>Formato</th>
                                        <th>T. Memória</th>
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

    <!-- Modal Adicionar/Editar Placa-Mãe -->
    <div class="modal fade" id="addPlacaMaeModal" tabindex="-1" aria-labelledby="modalTitle" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Adicionar Motherboard</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form id="addPlacaMaeForm">
                        <input type="hidden" id="isEditing" value="false">
                        <input type="hidden" id="placaId" name="placaId" value="">
                        
                        <!-- Mensagem de erro de referência duplicada -->
                        <div class="reference-error-box" id="referenceErrorBox" style="display: none;">
                            <div class="error-title">Referência Duplicada</div>
                            <div class="error-message">Essa referência já existe. Por favor, escolha outra.</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                        <div class="form-group mb-3">
                                    <label class="form-label required">Referência</label>
                                    <input type="text" class="form-control" id="placaRef" name="placaRef" required>
                        </div>
                            </div>
                            <div class="col-md-6">
                        <div class="form-group mb-3">
                                    <label class="form-label required">Marca</label>
                            <div class="input-group">
                                        <select class="form-select" id="placaMarca" name="placaMarca" required>
                                    <option value="">Selecione a marca</option>
                                    <?php foreach ($marcas as $marca): ?>
                                        <option value="<?= $marca['id'] ?>"><?= $marca['nome'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                        <button type="button" class="btn btn-outline-primary" onclick="adicionarMarcaPlacaMae()">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                        <div class="form-group mb-3">
                                    <label class="form-label required">Tamanho</label>
                            <div class="input-group">
                                        <select class="form-select" id="placaTamanho" name="placaTamanho" required>
                                            <option value="">Selecione o tamanho</option>
                                            <?php foreach ($tamanhos as $tamanho): ?>
                                                <option value="<?= $tamanho['id'] ?>"><?= $tamanho['nome'] ?></option>
                                <?php endforeach; ?>
                            </select>
                                        <button type="button" class="btn btn-outline-primary" onclick="adicionarTamanhoPlacaMae()">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                            </div>
                            <div class="col-md-6">
                        <div class="form-group mb-3">
                                    <label class="form-label required">Socket</label>
                            <div class="input-group">
                                        <select class="form-select" id="placaSocket" name="placaSocket" required>
                                            <option value="">Selecione o socket</option>
                                            <?php foreach ($sockets as $socket): ?>
                                                <option value="<?= $socket['id'] ?>"><?= $socket['nome'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-outline-primary" onclick="adicionarSocketPlacaMae()">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label required">Chipset</label>
                                    <div class="input-group">
                                        <select class="form-select" id="placaChipset" name="placaChipset" required>
                                            <option value="">Selecione o chipset</option>
                                            <?php foreach ($chipsets as $chipset): ?>
                                                <option value="<?= $chipset['id'] ?>"><?= $chipset['nome'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-outline-primary" onclick="adicionarChipsetPlacaMae()">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label required">Formato</label>
                                    <div class="input-group">
                                        <select class="form-select" id="placaFormato" name="placaFormato" required>
                                            <option value="">Selecione o formato</option>
                                            <?php foreach ($formatos as $formato): ?>
                                                <option value="<?= $formato['id'] ?>"><?= $formato['nome'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-outline-primary" onclick="adicionarFormatoPlacaMae()">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label required">Tipo de Memória</label>
                                    <div class="input-group">
                                        <select class="form-select" id="placaTMemoria" name="placaTMemoria" required>
                                            <option value="">Selecione o tipo de memória</option>
                                            <?php foreach ($tmemorias as $tmemoria): ?>
                                                <option value="<?= $tmemoria['id'] ?>"><?= $tmemoria['nome'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="btn btn-outline-primary" onclick="adicionarTMemoriaPlacaMae()">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label required">Cor</label>
                                    <div class="input-group">
                                        <select class="form-select" id="placaCor" name="placaCor" required>
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
                            </div>
                        </div>

                        <div class="form-group mb-3" id="estadoContainer">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="placaEstado" name="placaEstado" required>
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
                    <button type="button" class="btn btn-primary" id="saveButton" onclick="salvarPlacaMae()">Adicionar</button>
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
                    <p class="mb-0">Confirma os registos corretos para a motherboard que está sem reparo? Ao confirmar a ação não poderá ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-confirm" onclick="confirmarSemReparo()">Confirmar</button>
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
                    <button type="button" class="btn btn-primary" onclick="salvarNovaMarca()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Socket -->
    <div class="modal fade" id="addSocketModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Socket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="novoSocket" class="form-label">Nome do Socket</label>
                        <input type="text" class="form-control" id="novoSocket" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarNovoSocket()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Chipset -->
    <div class="modal fade" id="addChipsetModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Chipset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="novoChipset" class="form-label">Nome do Chipset</label>
                        <input type="text" class="form-control" id="novoChipset" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarNovoChipset()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Formato -->
    <div class="modal fade" id="addFormatoModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Formato</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="novoFormato" class="form-label">Nome do Formato</label>
                        <input type="text" class="form-control" id="novoFormato" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarNovoFormato()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Tamanho -->
    <div class="modal fade" id="addTamanhoModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Tamanho</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="novoTamanho" class="form-label">Nome do Tamanho</label>
                        <input type="text" class="form-control" id="novoTamanho" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarNovoTamanho()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Adicionar Tipo de Memória -->
    <div class="modal fade" id="addTMemoriaModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adicionar Tipo de Memória</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="novoTMemoria" class="form-label">Nome do Tipo de Memória</label>
                        <input type="text" class="form-control" id="novoTMemoria" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarNovoTMemoria()">Salvar</button>
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
                    <button type="button" class="btn btn-primary" onclick="salvarNovaCor()">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação para Excluir Mother Board -->
    <div class="modal fade custom-confirm-modal" id="confirmDeleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="deleteMotherBoardId" value="">
                    <p class="mb-0">Tem a certeza que pretende apagar esta motherboard?</p>
                    <p class="text-muted mt-2">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="confirmarExclusaoPlacaMae()">
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
            loadPlacasMae();
            
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
                        toggleButton.style.display = 'flex';
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
                    pesquisarPlacasMae();
                }
            });
            
            // Resetar formulário quando o modal for fechado
            document.getElementById('addPlacaMaeModal').addEventListener('hidden.bs.modal', function () {
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

        // Função para resetar o formulário
        function resetForm() {
            const form = document.getElementById('addPlacaMaeForm');
            form.reset();
            document.getElementById('isEditing').value = 'false';
            document.getElementById('placaId').value = '';
            document.getElementById('modalTitle').textContent = 'Adicionar Motherboard';
            document.getElementById('saveButton').textContent = 'Adicionar';
            
            // Remover atributos readOnly e disabled
            document.getElementById('placaRef').readOnly = false;
            document.getElementById('placaEstado').disabled = false;
            
            // Resetar as opções desabilitadas no select de estado
            const estadoSelect = document.getElementById('placaEstado');
            for (let i = 0; i < estadoSelect.options.length; i++) {
                estadoSelect.options[i].disabled = false;
            }
        }

        let formDataTemp = null;
        let isEditingTemp = false;
        let placaIdTemp = null;

        function salvarPlacaMae() {
            const form = document.getElementById('addPlacaMaeForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Esconder a mensagem de erro se estiver visível
            document.getElementById('referenceErrorBox').style.display = 'none';

            const isEditing = document.getElementById('isEditing').value === 'true';
            const placaId = document.getElementById('placaId').value;
            
            // Verificar referência duplicada apenas ao adicionar novo
            if (!isEditing) {
                const referencia = document.getElementById('placaRef').value;
                const timestamp = new Date().getTime();
                
                // Verificar se a referência já existe
                fetch(`../stock/placamae/verificar_referencia.php?ref=${encodeURIComponent(referencia)}&nocache=${timestamp}`, {
                    headers: {
                        'Cache-Control': 'no-cache, no-store, must-revalidate',
                        'Pragma': 'no-cache',
                        'Expires': '0'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erro na resposta da rede: ${response.status} ${response.statusText}`);
                    }
                    return response.json().catch(e => {
                        throw new Error(`Erro ao processar JSON: ${e.message}`);
                    });
                })
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
            const form = document.getElementById('addPlacaMaeForm');
            const formData = new FormData(form);
            
            const isEditing = document.getElementById('isEditing').value === 'true';
            const placaId = document.getElementById('placaId').value;
            
            // Se estiver editando, adiciona o ID ao formData
            if (isEditing && placaId) {
                formData.append('id', placaId);
            }
            
            // Garantir que o estado seja incluído mesmo se o campo estiver desabilitado
            if (isEditing && document.getElementById('placaEstado').disabled) {
                const placaEstado = document.getElementById('placaEstado').value;
                formData.set('placaEstado', placaEstado);
            }
            
            const estado = document.getElementById('placaEstado').value;
            
            // Se o estado for "Sem Reparo", mostrar confirmação
            if (estado === '4') { // ID 4 é "Sem Reparo"
                formDataTemp = formData;
                isEditingTemp = isEditing;
                placaIdTemp = placaId;
                const modal = new bootstrap.Modal(document.getElementById('confirmSemReparoModal'));
                modal.show();
                return;
            }
            
            // Se não for "Sem Reparo", prosseguir normalmente
            enviarFormulario(formData, isEditing);
        }

        function enviarFormulario(formData, isEditing) {
            const timestamp = new Date().getTime();
            const url = isEditing 
                ? `../stock/placamae/atualizar_placamae.php?nocache=${timestamp}` 
                : `../stock/placamae/salvar_stock_placamae.php?nocache=${timestamp}`;
            
            // Log para debug
            console.log('Enviando dados para:', url);
            console.log('É edição?', isEditing);
            console.log('Dados do formulário:', Object.fromEntries(formData));
            
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
                    throw new Error(`Erro na resposta da rede: ${response.status} ${response.statusText}`);
                }
                return response.json().catch(e => {
                    throw new Error(`Erro ao processar JSON: ${e.message}`);
                });
            })
            .then(data => {
                if (data.success) {
                    // Resetar o formulário e fechar o modal apenas se a operação for bem-sucedida
                    const form = document.getElementById('addPlacaMaeForm');
                    form.reset();
                    document.getElementById('isEditing').value = 'false';
                    document.getElementById('placaId').value = '';
                    document.getElementById('modalTitle').textContent = 'Adicionar';
                    document.getElementById('saveButton').textContent = 'Adicionar';

                    const modal = bootstrap.Modal.getInstance(document.getElementById('addPlacaMaeModal'));
                    if (modal) {
                        modal.hide();
                    }

                    // Aguardar um breve momento para o servidor processar a mudança
                    setTimeout(() => {
                        // Atualizar a lista de placas-mãe
                        loadPlacasMae();
                    }, 300);

                    Toastify({
                        text: data.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                } else {
                    throw new Error(data.message || 'Erro ao processar motherboard');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: error.message || "Erro ao processar motherboard",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        function editarPlacaMae(id) {
            console.log('Iniciando edição da motherboard:', id);
            const timestamp = new Date().getTime();
            
            fetch(`../stock/placamae/buscar_placamae.php?id=${id}&nocache=${timestamp}`, {
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            })
            .then(response => {
                console.log('Resposta recebida');
                if (!response.ok) {
                    throw new Error(`Erro na resposta da rede: ${response.status} ${response.statusText}`);
                }
                return response.json().catch(e => {
                    throw new Error(`Erro ao processar JSON: ${e.message}`);
                });
            })
            .then(data => {
                console.log('Dados recebidos:', data);
                if (data.success) {
                    const placa = data.data;
                    console.log('Dados da motherboard:', placa);
                    
                    // Verificar se a placa está em estado "Sem Reparo" ou "Em Uso"
                    if (placa.PM_ESTADO === '4') {
                        Toastify({
                            text: "Não é possível editar uma motherboard marcada como Sem Reparo",
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            style: {
                                background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                            }
                        }).showToast();
                        return;
                    }
                    
                    if (placa.PM_ESTADO === '2') {
                        Toastify({
                            text: "Não é possível editar uma motherboard marcada como Em Uso",
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            style: {
                                background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                            }
                        }).showToast();
                        return;
                    }
                    
                    document.getElementById('placaId').value = placa.PM_ID;
                    document.getElementById('placaRef').value = placa.PM_REF;
                    document.getElementById('placaRef').readOnly = true;
                    document.getElementById('placaMarca').value = placa.PM_MARCA;
                    document.getElementById('placaTamanho').value = placa.PM_TAM;
                    document.getElementById('placaSocket').value = placa.PM_SOCKET;
                    document.getElementById('placaChipset').value = placa.PM_CHIPSET;
                    document.getElementById('placaFormato').value = placa.PM_FORMATO;
                    document.getElementById('placaTMemoria').value = placa.PM_TMEMORIA;
                    document.getElementById('placaCor').value = placa.PM_COR;
                    document.getElementById('placaEstado').value = placa.PM_ESTADO;
                    
                    // Verificar se o estado é "Manutenção" (ID 3)
                    if (placa.PM_ESTADO === '3') {
                        document.getElementById('placaEstado').disabled = false;
                        
                        // Limitar as opções para apenas "Disponível" (1) e "Sem Reparo" (4)
                        const estadoSelect = document.getElementById('placaEstado');
                        for (let i = 0; i < estadoSelect.options.length; i++) {
                            const option = estadoSelect.options[i];
                            if (option.value !== '1' && option.value !== '4' && option.value !== '3') {
                                option.disabled = true;
                            } else {
                                option.disabled = false;
                            }
                        }
                    } else {
                        document.getElementById('placaEstado').disabled = true;
                    }

                    document.getElementById('isEditing').value = 'true';
                    document.getElementById('modalTitle').textContent = 'Editar Motherboard';
                    document.getElementById('saveButton').textContent = 'Salvar Alterações';

                    const estadoContainer = document.getElementById('estadoContainer');
                    estadoContainer.style.display = 'block';

                    const modal = new bootstrap.Modal(document.getElementById('addPlacaMaeModal'));
                    modal.show();
                } else {
                    throw new Error(data.message || 'Erro ao carregar dados da motherboard');
                }
            })
            .catch(error => {
                console.error('Erro ao editar motherboard:', error);
                Toastify({
                    text: error.message || "Erro ao carregar dados da motherboard",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        function loadPlacasMae() {
            console.log('Carregando lista de motherboards...');
            const timestamp = new Date().getTime();
            fetch(`../stock/placamae/buscar_stock_placamae.php?nocache=${timestamp}`, {
                method: 'GET',
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erro na resposta da rede: ${response.status} ${response.statusText}`);
                }
                return response.json().catch(e => {
                    throw new Error(`Erro ao processar JSON: ${e.message}. Resposta: ${response.text()}`);
                });
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
                                <td colspan="13" class="text-center">
                                    <div class="alert alert-info mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Nenhuma motherboard encontrada
                                    </div>
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    
                    data.data.forEach(placa => {
                        console.log('Renderizando motherboard:', placa);
                        const isSemReparo = placa.estado === 'Sem Reparo' || placa.PM_ESTADO === '4';
                        const isEmUso = placa.estado === 'Em Uso' || placa.PM_ESTADO === '2';
                        let estadoClass = '';
                        switch(placa.estado) {
                            case 'Disponível':
                                estadoClass = 'estado-disponivel';
                                break;
                            case 'Manutenção':
                                estadoClass = 'estado-manutencao';
                                break;
                            case 'Sem Reparo':
                                estadoClass = 'estado-sem-reparo';
                                break;
                            case 'Em Uso':
                                estadoClass = 'estado-em-uso';
                                break;
                        }
                        
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-motherboard me-2 text-primary"></i>
                                    <strong>MOTHERBOARD ${placa.id}</strong>
                                </div>
                            </td>
                            <td>${placa.referencia || ''}</td>
                            <td>${placa.marca || ''}</td>
                            <td>${placa.tamanho || ''}</td>
                            <td>${placa.socket || ''}</td>
                            <td>${placa.chipset || ''}</td>
                            <td>${placa.formato || ''}</td>
                            <td>${placa.tmemoria || ''}</td>
                            <td>${placa.cor || ''}</td>
                            <td>
                                ${placa.computador_ref ? 
                                    `<strong>${placa.computador_ref}</strong>` : 
                                    'Nenhum Associado'
                                }
                            </td>
                            <td>
                                ${placa.PM_SALA ? 
                                    `<strong>${placa.PM_SALA}</strong>` : 
                                    'Nenhuma Associada'
                                }
                            </td>
                            <td><span class="${estadoClass}">${placa.estado || ''}</span></td>
                            <td class="inventory-actions">
                                ${isSemReparo ? 
                                    '<span class="sem-reparo-badge">Sem Reparo</span>' :
                                    isEmUso ?
                                    `<button class="btn btn-sm btn-outline-danger" onclick="excluirPlacaMae(${placa.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>` :
                                    `<button class="btn btn-sm btn-outline-primary me-1" onclick="editarPlacaMae(${placa.id})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="excluirPlacaMae(${placa.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>`
                                }
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                } else {
                    console.error('Erro ao carregar motherboards:', data.message);
                    const tbody = document.querySelector('tbody');
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="13" class="text-center">
                                <div class="alert alert-danger mb-0">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    Erro ao carregar motherboards: ${data.message}
                                </div>
                            </td>
                        </tr>
                    `;
                }
            })
            .catch(error => {
                console.error('Erro ao carregar motherboards:', error);
                const tbody = document.querySelector('tbody');
                tbody.innerHTML = `
                    <tr>
                        <td colspan="13" class="text-center">
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Erro ao carregar motherboards: ${error.message}
                            </div>
                        </td>
                    </tr>
                `;
            });
        }

        function pesquisarPlacasMae() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const timestamp = new Date().getTime();
            
            fetch(`../stock/placamae/buscar_stock_placamae.php?nocache=${timestamp}`, {
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erro na resposta da rede: ${response.status} ${response.statusText}`);
                }
                return response.json().catch(e => {
                    throw new Error(`Erro ao processar JSON: ${e.message}`);
                });
            })
            .then(data => {
                console.log('Dados recebidos para pesquisa:', data);
                if (data.success) {
                    const tbody = document.querySelector('tbody');
                    tbody.innerHTML = '';
                    
                    if (!data.data || !Array.isArray(data.data)) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="13" class="text-center">
                                    <div class="alert alert-info mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Nenhuma motherboard encontrada
                                    </div>
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    
                    const filteredData = data.data.filter(placa => {
                        const searchString = `${placa.id} ${placa.referencia} ${placa.marca} ${placa.tamanho} ${placa.socket} ${placa.chipset} ${placa.formato} ${placa.tmemoria} ${placa.cor} ${placa.estado} ${placa.computador_ref} ${placa.PM_SALA}`.toLowerCase();
                        return searchString.includes(searchTerm);
                    });

                    if (filteredData.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="13" class="text-center">
                                    <div class="alert alert-info mb-0">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Nenhuma motherboard encontrada com os critérios de pesquisa
                                    </div>
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    
                    filteredData.forEach(placa => {
                        const isSemReparo = placa.estado === 'Sem Reparo' || placa.PM_ESTADO === '4';
                        const isEmUso = placa.estado === 'Em Uso' || placa.PM_ESTADO === '2';
                        let estadoClass = '';
                        switch(placa.estado) {
                            case 'Disponível':
                                estadoClass = 'estado-disponivel';
                                break;
                            case 'Manutenção':
                                estadoClass = 'estado-manutencao';
                                break;
                            case 'Sem Reparo':
                                estadoClass = 'estado-sem-reparo';
                                break;
                            case 'Em Uso':
                                estadoClass = 'estado-em-uso';
                                break;
                        }
                        
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-motherboard me-2 text-primary"></i>
                                    <strong>MOTHERBOARD ${placa.id}</strong>
                                </div>
                            </td>
                            <td>${placa.referencia || ''}</td>
                            <td>${placa.marca || ''}</td>
                            <td>${placa.tamanho || ''}</td>
                            <td>${placa.socket || ''}</td>
                            <td>${placa.chipset || ''}</td>
                            <td>${placa.formato || ''}</td>
                            <td>${placa.tmemoria || ''}</td>
                            <td>${placa.cor || ''}</td>
                            <td>
                                ${placa.computador_ref ? 
                                    `<strong>${placa.computador_ref}</strong>` : 
                                    'Nenhum Associado'
                                }
                            </td>
                            <td>
                                ${placa.PM_SALA ? 
                                    `<strong>${placa.PM_SALA}</strong>` : 
                                    'Nenhuma Associada'
                                }
                            </td>
                            <td><span class="${estadoClass}">${placa.estado || ''}</span></td>
                            <td class="inventory-actions">
                                ${isSemReparo ? 
                                    '<span class="sem-reparo-badge">Sem Reparo</span>' :
                                    isEmUso ?
                                    `<button class="btn btn-sm btn-outline-danger" onclick="excluirPlacaMae(${placa.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>` :
                                    `<button class="btn btn-sm btn-outline-primary me-1" onclick="editarPlacaMae(${placa.id})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="excluirPlacaMae(${placa.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>`
                                }
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                } else {
                    console.error('Erro ao carregar motherboards:', data.message);
                    const tbody = document.querySelector('tbody');
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="13" class="text-center">
                                <div class="alert alert-danger mb-0">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    Erro ao carregar motherboards: ${data.message}
                                </div>
                            </td>
                        </tr>
                    `;
                }
            })
            .catch(error => {
                console.error('Erro ao pesquisar motherboards:', error);
                Toastify({
                    text: "Erro ao pesquisar motherboards: " + error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
                
                const tbody = document.querySelector('tbody');
                tbody.innerHTML = `
                    <tr>
                        <td colspan="13" class="text-center">
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Erro ao pesquisar motherboards: ${error.message}
                            </div>
                        </td>
                    </tr>
                `;
            });
        }

        // Funções para adicionar novos itens
        function adicionarMarcaPlacaMae() {
            const modal = new bootstrap.Modal(document.getElementById('addMarcaModal'));
            modal.show();
        }

        function salvarNovaMarca() {
            const nomeMarca = document.getElementById('novaMarca').value;
            if (!nomeMarca) return;

            const formData = new FormData();
            formData.append('nome_marca', nomeMarca);
            const timestamp = new Date().getTime();

            fetch(`../stock/placamae/adicionar_marca_placamae.php?nocache=${timestamp}`, {
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
                    throw new Error(`Erro na resposta da rede: ${response.status} ${response.statusText}`);
                }
                return response.json().catch(e => {
                    throw new Error(`Erro ao processar JSON: ${e.message}`);
                });
            })
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('placaMarca');
                    const option = new Option(data.marca.nome, data.marca.id);
                    select.add(option);
                    select.value = data.marca.id;

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
                } else {
                    throw new Error(data.message || 'Erro ao adicionar marca');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: "Erro ao adicionar marca: " + error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        function adicionarSocketPlacaMae() {
            const modal = new bootstrap.Modal(document.getElementById('addSocketModal'));
            modal.show();
        }

        function salvarNovoSocket() {
            const nomeSocket = document.getElementById('novoSocket').value;
            if (!nomeSocket) return;

            const formData = new FormData();
            formData.append('nome_socket', nomeSocket);
            const timestamp = new Date().getTime();

            fetch(`../stock/placamae/adicionar_socket_placamae.php?nocache=${timestamp}`, {
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
                    throw new Error(`Erro na resposta da rede: ${response.status} ${response.statusText}`);
                }
                return response.json().catch(e => {
                    throw new Error(`Erro ao processar JSON: ${e.message}`);
                });
            })
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('placaSocket');
                    const option = new Option(data.socket.nome, data.socket.id);
                    select.add(option);
                    select.value = data.socket.id;

                    const modal = bootstrap.Modal.getInstance(document.getElementById('addSocketModal'));
                    modal.hide();
                    document.getElementById('novoSocket').value = '';

                    Toastify({
                        text: data.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                } else {
                    throw new Error(data.message || 'Erro ao adicionar socket');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: "Erro ao adicionar socket: " + error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        // Funções para Chipset
        function adicionarChipsetPlacaMae() {
            const modal = new bootstrap.Modal(document.getElementById('addChipsetModal'));
            modal.show();
        }

        function salvarNovoChipset() {
            const nomeChipset = document.getElementById('novoChipset').value;
            if (!nomeChipset) return;

            const formData = new FormData();
            formData.append('nome_chipset', nomeChipset);
            const timestamp = new Date().getTime();

            fetch(`../stock/placamae/adicionar_chipset_placamae.php?nocache=${timestamp}`, {
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
                    throw new Error(`Erro na resposta da rede: ${response.status} ${response.statusText}`);
                }
                return response.json().catch(e => {
                    throw new Error(`Erro ao processar JSON: ${e.message}`);
                });
            })
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('placaChipset');
                    const option = new Option(data.chipset.nome, data.chipset.id);
                    select.add(option);
                    select.value = data.chipset.id;

                    const modal = bootstrap.Modal.getInstance(document.getElementById('addChipsetModal'));
                    modal.hide();
                    document.getElementById('novoChipset').value = '';

                    Toastify({
                        text: data.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                } else {
                    throw new Error(data.message || 'Erro ao adicionar chipset');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: "Erro ao adicionar chipset: " + error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        // Funções para Formato
        function adicionarFormatoPlacaMae() {
            const modal = new bootstrap.Modal(document.getElementById('addFormatoModal'));
            modal.show();
        }

        function salvarNovoFormato() {
            const nomeFormato = document.getElementById('novoFormato').value;
            if (!nomeFormato) return;

            const formData = new FormData();
            formData.append('nome_formato', nomeFormato);
            const timestamp = new Date().getTime();

            fetch(`../stock/placamae/adicionar_formato_placamae.php?nocache=${timestamp}`, {
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
                    throw new Error(`Erro na resposta da rede: ${response.status} ${response.statusText}`);
                }
                return response.json().catch(e => {
                    throw new Error(`Erro ao processar JSON: ${e.message}`);
                });
            })
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('placaFormato');
                    const option = new Option(data.formato.nome, data.formato.id);
                    select.add(option);
                    select.value = data.formato.id;

                    const modal = bootstrap.Modal.getInstance(document.getElementById('addFormatoModal'));
                    modal.hide();
                    document.getElementById('novoFormato').value = '';

                    Toastify({
                        text: data.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                } else {
                    throw new Error(data.message || 'Erro ao adicionar formato');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: "Erro ao adicionar formato: " + error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        // Funções para Tamanho
        function adicionarTamanhoPlacaMae() {
            const modal = new bootstrap.Modal(document.getElementById('addTamanhoModal'));
            modal.show();
        }

        function salvarNovoTamanho() {
            const nomeTamanho = document.getElementById('novoTamanho').value;
            if (!nomeTamanho) return;

            const formData = new FormData();
            formData.append('nome_tamanho', nomeTamanho);
            const timestamp = new Date().getTime();

            fetch(`../stock/placamae/adicionar_tamanho_placamae.php?nocache=${timestamp}`, {
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
                    throw new Error(`Erro na resposta da rede: ${response.status} ${response.statusText}`);
                }
                return response.json().catch(e => {
                    throw new Error(`Erro ao processar JSON: ${e.message}`);
                });
            })
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('placaTamanho');
                    const option = new Option(data.tamanho.nome, data.tamanho.id);
                    select.add(option);
                    select.value = data.tamanho.id;

                    const modal = bootstrap.Modal.getInstance(document.getElementById('addTamanhoModal'));
                    modal.hide();
                    document.getElementById('novoTamanho').value = '';

                    Toastify({
                        text: data.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                } else {
                    throw new Error(data.message || 'Erro ao adicionar tamanho');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: "Erro ao adicionar tamanho: " + error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        // Funções para Tipo de Memória
        function adicionarTMemoriaPlacaMae() {
            const modal = new bootstrap.Modal(document.getElementById('addTMemoriaModal'));
            modal.show();
        }

        function salvarNovoTMemoria() {
            const nomeTMemoria = document.getElementById('novoTMemoria').value;
            if (!nomeTMemoria) return;

            const formData = new FormData();
            formData.append('nome_tmemoria', nomeTMemoria);
            const timestamp = new Date().getTime();

            fetch(`../stock/placamae/adicionar_tmemoria_placamae.php?nocache=${timestamp}`, {
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
                    throw new Error(`Erro na resposta da rede: ${response.status} ${response.statusText}`);
                }
                return response.json().catch(e => {
                    throw new Error(`Erro ao processar JSON: ${e.message}`);
                });
            })
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('placaTMemoria');
                    const option = new Option(data.tmemoria.nome, data.tmemoria.id);
                    select.add(option);
                    select.value = data.tmemoria.id;

                    const modal = bootstrap.Modal.getInstance(document.getElementById('addTMemoriaModal'));
                    modal.hide();
                    document.getElementById('novoTMemoria').value = '';

                    Toastify({
                        text: data.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                } else {
                    throw new Error(data.message || 'Erro ao adicionar tipo de memória');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: "Erro ao adicionar tipo de memória: " + error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        // Funções para Cor
        function adicionarCor() {
            const modal = new bootstrap.Modal(document.getElementById('addCorModal'));
            modal.show();
        }

        function salvarNovaCor() {
            const nomeCor = document.getElementById('novaCor').value;
            if (!nomeCor) return;

            const formData = new FormData();
            formData.append('nome_cor', nomeCor);
            const timestamp = new Date().getTime();

            fetch(`../dashboard/cor/adicionar_cor.php?nocache=${timestamp}`, {
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
                    throw new Error(`Erro na resposta da rede: ${response.status} ${response.statusText}`);
                }
                return response.json().catch(e => {
                    throw new Error(`Erro ao processar JSON: ${e.message}`);
                });
            })
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('placaCor');
                    const option = new Option(data.cor.COR, data.cor.ID_COR);
                    select.add(option);
                    select.value = data.cor.ID_COR;

                    const modal = bootstrap.Modal.getInstance(document.getElementById('addCorModal'));
                    modal.hide();
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
                } else {
                    throw new Error(data.message || 'Erro ao adicionar cor');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: "Erro ao adicionar cor: " + error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        // Função para excluir a mother board
        function excluirPlacaMae(id) {
            // Mostrar modal de confirmação
            document.getElementById('deleteMotherBoardId').value = id;
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            confirmModal.show();
        }
        
        // Função para confirmar e executar a exclusão da mother board
        function confirmarExclusaoPlacaMae() {
            const id = document.getElementById('deleteMotherBoardId').value;
            
            if (!id) return;
            
            const formData = new FormData();
            formData.append('id', id);
            const timestamp = new Date().getTime();
            
            // Fechar o modal de confirmação
            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
            confirmModal.hide();
            
            fetch(`../stock/placamae/excluir_placamae.php?nocache=${timestamp}`, {
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
                    throw new Error(`Erro na resposta da rede: ${response.status} ${response.statusText}`);
                }
                return response.json().catch(e => {
                    throw new Error(`Erro ao processar JSON: ${e.message}`);
                });
            })
            .then(data => {
                if (data.success) {
                    // Aguardar um breve momento para o servidor processar a mudança
                    setTimeout(() => {
                        // Recarregar a lista
                        loadPlacasMae();
                    }, 300);
                    
                    Toastify({
                        text: data.message || "Motherboard excluída com sucesso!",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                } else {
                    throw new Error(data.message || "Erro ao excluir motherboard");
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: "Erro ao excluir motherboard: " + error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        function confirmarSemReparo() {
            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmSemReparoModal'));
            confirmModal.hide();
            
            const formData = formDataTemp;
            formData.set('placaEstado', '4');
            
            if (isEditingTemp) {
                formData.set('id', placaIdTemp);
                console.log('ID definido para atualização em Sem Reparo:', placaIdTemp);
            }
            
            enviarFormulario(formData, isEditingTemp);
        }
    </script>
</body>
</html> 