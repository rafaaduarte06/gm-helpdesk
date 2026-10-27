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
    $marcas = $pdo->query("SELECT MC_ID as id, MC_NOME as nome FROM marcacaixa ORDER BY MC_NOME")->fetchAll();
    $modelos = $pdo->query("SELECT CAIXA_MODELO as id, CAIXA_MODELO as nome FROM caixamodelo ORDER BY CAIXA_MODELO")->fetchAll();
    $cores = $pdo->query("SELECT ID_COR as id, COR as nome FROM cor ORDER BY COR")->fetchAll();
    $estados = $pdo->query("SELECT ID_ESTADO as id, ESTADO as nome FROM caixas_estado WHERE ESTADO IN ('Disponível', 'Manutenção', 'Sem Reparo') ORDER BY ESTADO")->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Caixas - GM HelpDesk</title>
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

        /* Estilo para os botões de ação */
        .inventory-actions {
            white-space: nowrap;
            display: flex;
            gap: 5px;
            justify-content: center;
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

        /* Estilo para o computador associado */
        .computador-associado {
            font-weight: bold;
            color: #000;
        }

        .sem-computador {
            color: #000;
            font-weight: normal;
        }

        /* Estilo para centralizar o texto na coluna Caixa */
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

        /* Estilo para sala associada */
        .sala-associada {
            font-weight: bold;
            color: #000;
        }

        .sem-sala {
            color: #000;
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

        /* Estilo para mensagem de referência duplicada */
        .reference-error-box {
            border: 2px solid #ff0037;
            background-color: rgba(255, 0, 55, 0.1);
            color: #dc3545;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            position: relative;
            display: none;
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
                        <i class="bi bi-pc-display"></i>
                        <div>
                            <h4>Gestão de Caixas</h4>
                            <p class="mb-0 mt-1 text-light">Stock atual: <span id="stock-count" class="fw-bold">0</span> caixas</p>
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
                                       placeholder="Pesquisar caixa...">
                            </div>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCaixaModal">
                            <i class="bi bi-plus-lg me-2"></i><span>Adicionar</span>
                        </button>
                    </div>
                </div>

                <!-- Tabela de Caixas -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Caixa</th>
                                        <th>Referência</th>
                                        <th>Marca</th>
                                        <th>Modelo</th>
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

    <!-- Modal para adicionar/editar caixa -->
    <div class="modal fade" id="addCaixaModal" tabindex="-1" aria-labelledby="addCaixaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Adicionar Caixa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Formulário -->
                    <form id="addCaixaForm">
                        <input type="hidden" id="isEditing" value="false">
                        <input type="hidden" id="caixaId" name="id">

                        <!-- Mensagem de erro de referência duplicada -->
                        <div class="reference-error-box" id="referenceErrorBox">
                            <div class="error-title">Referência Duplicada</div>
                            <div class="error-message">Essa referência já existe. Por favor, escolha outra.</div>
                        </div>

                        <div class="mb-3">
                            <label for="caixaRef" class="form-label">Referência</label>
                            <input type="text" class="form-control" id="caixaRef" name="caixaRef" required>
                        </div>
                        <div class="mb-3">
                            <label for="caixaMarca" class="form-label">Marca</label>
                            <select class="form-select" id="caixaMarca" name="caixaMarca" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($marcas as $marca): ?>
                                    <option value="<?= $marca['id'] ?>"><?= $marca['nome'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="caixaModelo" class="form-label">Modelo</label>
                            <select class="form-select" id="caixaModelo" name="caixaModelo" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($modelos as $modelo): ?>
                                    <option value="<?= $modelo['id'] ?>"><?= $modelo['nome'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="caixaCor" class="form-label">Cor</label>
                            <select class="form-select" id="caixaCor" name="caixaCor" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($cores as $cor): ?>
                                    <option value="<?= $cor['id'] ?>"><?= $cor['nome'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3" id="estadoContainer">
                            <label for="caixaEstado" class="form-label">Estado</label>
                            <select class="form-select" id="caixaEstado" name="caixaEstado" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($estados as $estado): ?>
                                    <option value="<?= $estado['id'] ?>"><?= $estado['nome'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveButton" onclick="salvarCaixa()">Adicionar</button>
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
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Estado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja marcar esta caixa como "Sem Reparo"? Esta ação não poderá ser desfeita.</p>
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
                    <input type="hidden" id="caixaIdToDelete">
                    <p class="mb-0">Tem a certeza que pretende apagar esta caixa?</p>
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
            loadCaixas();
            
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
                    pesquisarCaixas();
                }
            });
            
            // Resetar formulário quando o modal for fechado
            document.getElementById('addCaixaModal').addEventListener('hidden.bs.modal', function () {
                resetForm();
            });
        });

        // Funções da Sidebar
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

        // Variável global para armazenar dados temporários do formulário
        let formDataTemp = null;

        // Função para carregar a lista de caixas
        function loadCaixas() {
            console.log('Carregando lista de caixas...');
            // Adicionar timestamp para evitar cache
            const timestamp = new Date().getTime();
            fetch(`../stock/caixa/buscar_stock_caixas.php?nocache=${timestamp}`, {
                method: 'GET',
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Erro de rede: ${response.status} ${response.statusText}`);
                    }
                    return response.text();
                })
                .then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Resposta não é JSON válido:', text);
                        throw new Error('A resposta do servidor não é um JSON válido');
                    }
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
                                    <td colspan="9" class="text-center">
                                        <div class="alert alert-info mb-0">
                                            <i class="bi bi-info-circle me-2"></i>
                                            Nenhuma caixa encontrada
                                        </div>
                                    </td>
                                </tr>
                            `;
                            return;
                        }
                        
                        data.data.forEach(caixa => {
                            console.log('Renderizando caixa:', caixa);
                            const row = document.createElement('tr');
                            
                            // Determinar a classe do estado
                            let estadoClass = '';
                            if (caixa.estado === 'Disponível') {
                                estadoClass = 'estado-disponivel';
                            } else if (caixa.estado === 'Manutenção') {
                                estadoClass = 'estado-manutencao';
                            } else if (caixa.estado === 'Sem Reparo') {
                                estadoClass = 'estado-sem-reparo';
                            } else if (caixa.estado === 'Em Uso') {
                                estadoClass = 'estado-em-uso';
                            }

                            // Criar células da linha
                            row.innerHTML = `
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-pc-display me-2 text-primary"></i>
                                        <strong>CAIXA ${caixa.id}</strong>
                                    </div>
                                </td>
                                <td>${caixa.referencia}</td>
                                <td>${caixa.marca}</td>
                                <td>${caixa.modelo}</td>
                                <td>${caixa.cor}</td>
                                <td>
                                    ${caixa.computador ? 
                                        `<span class="computador-associado">${caixa.computador}</span>` : 
                                        '<span class="sem-computador">Nenhum Associado</span>'
                                    }
                                </td>
                                <td>
                                    ${caixa.sala ? 
                                        `<span class="sala-associada">${caixa.sala}</span>` : 
                                        '<span class="sem-sala">Nenhuma Associada</span>'
                                    }
                                </td>
                                <td><span class="${estadoClass}">${caixa.estado}</span></td>
                                <td class="inventory-actions">
                                    ${caixa.estado === 'Sem Reparo' 
                                        ? '<span class="sem-reparo-badge"><span>Sem</span> <span>Reparo</span></span>'
                                        : caixa.estado === 'Em Uso'
                                            ? `<button class="btn btn-sm btn-outline-danger" onclick="excluirCaixa(${caixa.id})">
                                                 <i class="bi bi-trash"></i>
                                               </button>`
                                            : `<button class="btn btn-sm btn-outline-primary me-1" onclick="editarCaixa(${caixa.id})">
                                                 <i class="bi bi-pencil"></i>
                                               </button>
                                               <button class="btn btn-sm btn-outline-danger" onclick="excluirCaixa(${caixa.id})">
                                                 <i class="bi bi-trash"></i>
                                               </button>`
                                    }
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    } else {
                        console.error('Erro ao carregar caixas:', data.message);
                        const tbody = document.querySelector('tbody');
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="9" class="text-center">
                                    <div class="alert alert-danger mb-0">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        Erro ao carregar caixas: ${data.message || 'Erro desconhecido'}
                                    </div>
                                </td>
                            </tr>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar caixas:', error);
                    const tbody = document.querySelector('tbody');
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="alert alert-danger mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Erro ao carregar caixas: ${error.message || 'Erro desconhecido'}
                                </div>
                            </td>
                        </tr>
                    `;
                    
                    Toastify({
                        text: "Erro ao carregar caixas: " + error.message,
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
        function adicionarMarcaCaixa() {
            const modal = new bootstrap.Modal(document.getElementById('addMarcaModal'));
            modal.show();
        }

        // Função para salvar nova marca
        function salvarNovaMarca() {
            const nomeMarca = document.getElementById('novaMarca').value;
            if (!nomeMarca) return;

            const formData = new FormData();
            formData.append('nome_marca', nomeMarca);

            fetch('../stock/caixa/adicionar_marca_caixa.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('caixaMarca');
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

        // Função para abrir modal de adicionar modelo
        function adicionarModeloCaixa() {
            const modal = new bootstrap.Modal(document.getElementById('addModeloModal'));
            modal.show();
        }

        // Função para salvar novo modelo
        function salvarNovoModelo() {
            const nomeModelo = document.getElementById('novoModelo').value;
            if (!nomeModelo) return;

            const formData = new FormData();
            formData.append('nome_modelo', nomeModelo);

            fetch('../stock/caixa/adicionar_modelo_caixa.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('caixaModelo');
                    const option = new Option(data.modelo.nome, data.modelo.id);
                    select.add(option);
                    select.value = data.modelo.id;

                    // Fechar o modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addModeloModal'));
                    modal.hide();
                    
                    // Limpar o campo
                    document.getElementById('novoModelo').value = '';

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
                    text: "Erro ao adicionar modelo",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        // Função para abrir modal de adicionar cor
        function adicionarCor() {
            const modal = new bootstrap.Modal(document.getElementById('addCorModal'));
            modal.show();
        }

        // Função para salvar nova cor
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
                    const select = document.getElementById('caixaCor');
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

        // Função para salvar/atualizar caixa
        function salvarCaixa() {
            const form = document.getElementById('addCaixaForm');
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
                const referencia = document.getElementById('caixaRef').value;
                
                // Verificar se a referência já existe
                fetch(`../stock/caixa/verificar_referencia.php?ref=${encodeURIComponent(referencia)}`)
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
            // Criar FormData e adicionar campos manualmente
            const formData = new FormData();
            
            // Adicionar o ID se estiver editando
            const isEditing = document.getElementById('isEditing').value === 'true';
            if (isEditing) {
                formData.append('id', document.getElementById('caixaId').value);
            }
            
            // Adicionar os outros campos
            formData.append('caixaRef', document.getElementById('caixaRef').value);
            formData.append('caixaMarca', document.getElementById('caixaMarca').value);
            formData.append('caixaModelo', document.getElementById('caixaModelo').value);
            formData.append('caixaCor', document.getElementById('caixaCor').value);
            formData.append('caixaEstado', document.getElementById('caixaEstado').value);
            
            const estado = formData.get('caixaEstado');
            
            // Se o estado for "Sem Reparo", mostrar confirmação
            if (estado === '4') { // ID 4 é "Sem Reparo"
                formDataTemp = formData;
                const modal = new bootstrap.Modal(document.getElementById('confirmSemReparoModal'));
                modal.show();
                return;
            }
            
            // Se não for "Sem Reparo", prosseguir normalmente
            enviarFormulario(formData);
        }

        // Função para confirmar estado "Sem Reparo"
        function confirmarSemReparo() {
            console.log('Confirmação de Sem Reparo');
            
            // Verificar se formDataTemp existe
            if (formDataTemp) {
                console.log('FormData antes da modificação:', formDataTemp);
                
                // Garantir que o estado seja 4 (Sem Reparo)
                formDataTemp.set('caixaEstado', '4');
                
                console.log('FormData após modificação:', formDataTemp);
                console.log('caixaEstado:', formDataTemp.get('caixaEstado'));
                
                // Ocultar o modal de confirmação
                const modal = bootstrap.Modal.getInstance(document.getElementById('confirmSemReparoModal'));
                modal.hide();
                
                // Enviar o formulário
                enviarFormulario(formDataTemp);
            } else {
                console.error('formDataTemp não está definido');
            }
        }

        // Função para enviar o formulário
        function enviarFormulario(formData) {
            const isEditing = document.getElementById('isEditing').value === 'true';
            const url = isEditing ? '../stock/caixa/atualizar_caixa.php' : '../stock/caixa/salvar_stock_caixa.php';
            
            // Log para depuração
            console.log('Enviando formulário:');
            console.log('isEditing:', isEditing);
            console.log('URL:', url);
            
            // Garantir que o estado seja incluído mesmo se o campo estiver desabilitado
            if (isEditing && document.getElementById('caixaEstado').disabled) {
                const caixaEstado = document.getElementById('caixaEstado').value;
                formData.set('caixaEstado', caixaEstado);
            }
            
            // Adicionar timestamp para evitar cache
            const timestamp = new Date().getTime();
            const urlWithTimestamp = `${url}?nocache=${timestamp}`;
            
            fetch(urlWithTimestamp, {
                method: 'POST',
                body: formData,
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            })
            .then(response => {
                console.log('Resposta recebida do servidor, status:', response.status);
                if (!response.ok) {
                    throw new Error(`Erro HTTP: ${response.status} ${response.statusText}`);
                }
                return response.text();
            })
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Resposta não é JSON válido:', text);
                    throw new Error('A resposta do servidor não é um JSON válido');
                }
            })
            .then(data => {
                console.log('Dados recebidos:', data);
                if (data.success) {
                    // Fechar modal primeiro
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addCaixaModal'));
                    modal.hide();
                    
                    // Resetar formulário
                    document.getElementById('addCaixaForm').reset();
                    document.getElementById('isEditing').value = 'false';
                    document.getElementById('modalTitle').textContent = 'Adicionar';
                    document.getElementById('saveButton').textContent = 'Adicionar';

                    // Mostrar mensagem de sucesso
                    Toastify({
                        text: data.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                    
                    // Adicionar um pequeno atraso antes de recarregar a tabela
                    setTimeout(() => {
                        loadCaixas();
                    }, 300);
                } else {
                    throw new Error(data.message || 'Erro ao processar caixa');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: error.message || "Erro ao processar caixa",
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
            document.getElementById('addCaixaForm').reset();
            document.getElementById('isEditing').value = 'false';
            document.getElementById('caixaId').value = '';
            document.getElementById('modalTitle').textContent = 'Adicionar';
            document.getElementById('saveButton').textContent = 'Adicionar';
            
            // Remover atributos readOnly e disabled
            document.getElementById('caixaRef').readOnly = false;
            document.getElementById('caixaEstado').disabled = false;
            
            // Resetar as opções desabilitadas no select de estado
            const estadoSelect = document.getElementById('caixaEstado');
            for (let i = 0; i < estadoSelect.options.length; i++) {
                estadoSelect.options[i].disabled = false;
            }
            
            // Mostrar o campo de estado para adição
            const estadoContainer = document.getElementById('estadoContainer');
            const caixaEstado = document.getElementById('caixaEstado');
            estadoContainer.style.display = 'block';
            caixaEstado.required = true;
            
            // Esconder a mensagem de erro de referência duplicada
            document.getElementById('referenceErrorBox').style.display = 'none';
        }

        // Função para pesquisar caixas
        function pesquisarCaixas() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            // Adicionar timestamp para evitar cache
            const timestamp = new Date().getTime();
            fetch(`../stock/caixa/buscar_stock_caixas.php?nocache=${timestamp}`, {
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Erro de rede: ${response.status} ${response.statusText}`);
                }
                return response.text();
            })
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Resposta não é JSON válido:', text);
                    throw new Error('A resposta do servidor não é um JSON válido');
                }
            })
            .then(response => {
                if (!response.success) {
                    throw new Error(response.message || 'Erro ao carregar caixas');
                }
                const filteredData = response.data.filter(caixa => {
                    const caixaId = `CAIXA ${caixa.id}`.toLowerCase();
                    return (
                        caixaId.includes(searchTerm) ||
                        caixa.referencia.toLowerCase().includes(searchTerm) ||
                        caixa.marca.toLowerCase().includes(searchTerm) ||
                        caixa.modelo.toLowerCase().includes(searchTerm) ||
                        caixa.cor.toLowerCase().includes(searchTerm) ||
                        caixa.estado.toLowerCase().includes(searchTerm)
                    );
                });

                const tbody = document.querySelector('table tbody');
                if (filteredData.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Nenhuma caixa encontrada com os critérios de pesquisa
                                </div>
                            </td>
                        </tr>
                    `;
                } else {
                    tbody.innerHTML = filteredData.map(caixa => {
                        // Determinar a classe do estado
                        let estadoClass = '';
                        if (caixa.estado === 'Disponível') {
                            estadoClass = 'estado-disponivel';
                        } else if (caixa.estado === 'Manutenção') {
                            estadoClass = 'estado-manutencao';
                        } else if (caixa.estado === 'Sem Reparo') {
                            estadoClass = 'estado-sem-reparo';
                        } else if (caixa.estado === 'Em Uso') {
                            estadoClass = 'estado-em-uso';
                        }

                        // Verificar se é "Sem Reparo" para mostrar badge em vez de botões
                        const isSemReparo = caixa.estado === 'Sem Reparo';

                        return `
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-pc-display me-2 text-primary"></i>
                                        <strong>CAIXA ${caixa.id}</strong>
                                    </div>
                                </td>
                                <td>${caixa.referencia || ''}</td>
                                <td>${caixa.marca || ''}</td>
                                <td>${caixa.modelo || ''}</td>
                                <td>${caixa.cor || ''}</td>
                                <td>
                                    ${caixa.computador ? 
                                        `<span class="computador-associado">${caixa.computador}</span>` : 
                                        '<span class="sem-computador">Nenhum Associado</span>'
                                    }
                                </td>
                                <td>
                                    ${caixa.sala ? 
                                        `<span class="sala-associada">${caixa.sala}</span>` : 
                                        '<span class="sem-sala">Nenhuma Associada</span>'
                                    }
                                </td>
                                <td><span class="${estadoClass}">${caixa.estado || ''}</span></td>
                                <td class="inventory-actions">
                                    ${isSemReparo ? 
                                        '<span class="sem-reparo-badge"><span>Sem</span> <span>Reparo</span></span>' :
                                        caixa.estado === 'Em Uso' ?
                                            `<button class="btn btn-sm btn-outline-danger" onclick="excluirCaixa(${caixa.id})">
                                                <i class="bi bi-trash"></i>
                                            </button>` :
                                            `<button class="btn btn-sm btn-outline-primary me-1" onclick="editarCaixa(${caixa.id})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="excluirCaixa(${caixa.id})">
                                                <i class="bi bi-trash"></i>
                                            </button>`
                                    }
                                </td>
                            </tr>
                        `;
                    }).join('');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: "Erro ao pesquisar caixas: " + error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        // Função para editar caixa
        function editarCaixa(id) {
            console.log('Iniciando edição da caixa:', id);
            
            // Adicionar timestamp para evitar cache
            const timestamp = new Date().getTime();
            fetch(`../stock/caixa/buscar_caixa.php?id=${id}&nocache=${timestamp}`, {
                headers: {
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                }
            })
                .then(response => {
                    console.log('Resposta recebida, status:', response.status);
                    if (!response.ok) {
                        throw new Error(`Erro de rede: ${response.status} ${response.statusText}`);
                    }
                    return response.text();
                })
                .then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Resposta não é JSON válido:', text);
                        throw new Error('A resposta do servidor não é um JSON válido');
                    }
                })
                .then(data => {
                    console.log('Dados recebidos:', data);
                    if (data.success) {
                        const caixa = data.data;
                        console.log('Dados da caixa:', caixa);
                        
                        // Verificar se os dados da caixa existem
                        if (!caixa) {
                            throw new Error('Dados da caixa não encontrados');
                        }
                        
                        // Preencher os campos do formulário
                        document.getElementById('caixaId').value = caixa.CAIXA_ID;
                        document.getElementById('caixaRef').value = caixa.CAIXA_REF;
                        document.getElementById('caixaRef').readOnly = true;
                        document.getElementById('caixaMarca').value = caixa.CAIXA_MARCA;
                        document.getElementById('caixaModelo').value = caixa.CAIXA_MODELO;
                        document.getElementById('caixaCor').value = caixa.CAIXA_COR;
                        document.getElementById('caixaEstado').value = caixa.CAIXA_ESTADO;
                        
                        // Verificar se o estado é "Manutenção" (ID 3)
                        if (caixa.CAIXA_ESTADO === '3') {
                            document.getElementById('caixaEstado').disabled = false;
                            
                            // Limitar as opções para apenas "Disponível" (1) e "Sem Reparo" (4)
                            const estadoSelect = document.getElementById('caixaEstado');
                            for (let i = 0; i < estadoSelect.options.length; i++) {
                                const option = estadoSelect.options[i];
                                if (option.value !== '1' && option.value !== '4' && option.value !== '3') {
                                    option.disabled = true;
                                } else {
                                    option.disabled = false;
                                }
                            }
                        } else {
                            document.getElementById('caixaEstado').disabled = true;
                        }
                        
                        console.log('Campo caixaId:', document.getElementById('caixaId').value);
                        console.log('Campo caixaEstado:', document.getElementById('caixaEstado').value);

                        // Atualizar o estado do formulário
                        document.getElementById('isEditing').value = 'true';
                        document.getElementById('modalTitle').textContent = 'Editar Caixa ' + caixa.CAIXA_ID;
                        document.getElementById('saveButton').textContent = 'Salvar Alterações';

                        // Manter o campo de estado visível e obrigatório
                        const estadoContainer = document.getElementById('estadoContainer');
                        const caixaEstado = document.getElementById('caixaEstado');
                        estadoContainer.style.display = 'block';
                        caixaEstado.required = true;

                        // Abrir o modal
                        const modal = new bootstrap.Modal(document.getElementById('addCaixaModal'));
                        modal.show();
                    } else {
                        throw new Error(data.message || 'Erro ao carregar dados da caixa');
                    }
                })
                .catch(error => {
                    console.error('Erro ao editar caixa:', error);
                    Toastify({
                        text: error.message || "Erro ao carregar dados da caixa",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                        }
                    }).showToast();
                });
        }

        // Função para excluir caixa
        function excluirCaixa(id) {
            document.getElementById('caixaIdToDelete').value = id;
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            confirmModal.show();
        }
        
        function confirmarExclusao() {
            const id = document.getElementById('caixaIdToDelete').value;
            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
            confirmModal.hide();
            
            const formData = new FormData();
            formData.append('id', id);
            
            // Adicionar timestamp para evitar cache
            const timestamp = new Date().getTime();

            fetch(`../stock/caixa/excluir_caixa.php?nocache=${timestamp}`, {
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
                    throw new Error(`Erro de rede: ${response.status} ${response.statusText}`);
                }
                return response.text();
            })
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Resposta não é JSON válido:', text);
                    throw new Error('A resposta do servidor não é um JSON válido');
                }
            })
            .then(data => {
                if (data.success) {
                    Toastify({
                        text: data.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                    
                    // Adicionar um pequeno atraso antes de recarregar a tabela
                    setTimeout(() => {
                        loadCaixas(); // Recarrega a lista
                    }, 300);
                } else {
                    Toastify({
                        text: data.message || 'Erro ao excluir caixa',
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
                console.error('Erro ao excluir caixa:', error);
                Toastify({
                    text: "Erro ao excluir caixa: " + error.message,
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