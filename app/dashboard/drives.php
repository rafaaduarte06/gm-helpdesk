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
    $marcas = $pdo->query("SELECT MD_ID as id, MD_NOME as nome FROM marcadrdvd ORDER BY MD_NOME")->fetchAll();
    $cores = $pdo->query("SELECT ID_COR as id, COR as nome FROM cor ORDER BY COR")->fetchAll();
    $estados = $pdo->query("SELECT ID_ESTADO as id, ESTADO as nome FROM drives_estado ORDER BY ESTADO")->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Drives DVD - GM HelpDesk</title>
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

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .table td {
            vertical-align: middle;
        }

        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.7) !important;
        }
        
        .modal {
            padding: 0 !important;
        }
        
        .modal-dialog {
            margin: 1.75rem auto;
            display: flex;
            align-items: center;
            min-height: calc(100% - 3.5rem);
        }
        
        .modal.fade .modal-dialog {
            transform: translate(0, -50px) scale(0.8);
            transition: transform 0.3s ease-in-out;
        }
        
        .modal.show .modal-dialog {
            transform: translate(0, 0) scale(1);
        }
        
        .modal-sm {
            max-width: 300px;
            width: 100%;
        }
        
        .modal .btn {
            transition: all 0.3s ease;
        }
        
        .modal .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
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

        .computador-associado {
            font-weight: bold;
            color: #000;
        }

        .sem-computador {
            color: #000;
            font-weight: normal;
        }

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

        .inventory-actions {
            display: flex;
            gap: 5px;
            white-space: nowrap;
            justify-content: center;
        }

        .inventory-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
        }

        .inventory-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
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

        table th:first-child, 
        table td:first-child {
            text-align: center;
        }

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
            <div class="container-fluid p-4">
                <!-- Botão Voltar -->
                <a href="inventario.php" class="back-to-inventory">
                    <i class="bi bi-arrow-left back-icon"></i>
                    <span class="back-text">Voltar para Inventário</span>
                </a>

                <!-- Cabeçalho -->
                <div class="stock-header">
                    <div class="stock-title">
                        <i class="bi bi-device-hdd"></i>
                        <div>
                            <h4>Gestão de Drives</h4>
                            <p class="mb-0 mt-1 text-light">Stock atual: <span id="stock-count" class="fw-bold">0</span> drives</p>
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
                                       placeholder="Pesquisar drive...">
                            </div>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDriveModal">
                            <i class="bi bi-plus-lg me-2"></i><span>Adicionar</span>
                        </button>
                    </div>
                </div>

                <!-- Tabela de Drives -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Drive</th>
                                        <th>Referência</th>
                                        <th>Marca</th>
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

    <!-- Modal Adicionar/Editar Drive -->
    <div class="modal fade" id="addDriveModal" tabindex="-1" aria-labelledby="modalTitle" aria-modal="true" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Adicionar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form id="addDriveForm">
                        <input type="hidden" id="isEditing" value="false">
                        <input type="hidden" id="driveId" name="driveId" value="">
                        
                        <!-- Mensagem de erro de referência duplicada -->
                        <div class="reference-error-box" id="referenceErrorBox" style="display: none; background-color: #ffe0e0; color: #d8000c; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                            <div style="font-weight: bold; margin-bottom: 5px;">Referência Duplicada</div>
                            <div>Essa referência já existe. Por favor, escolha outra.</div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Referência</label>
                            <input type="text" class="form-control" id="driveRef" name="driveRef" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Marca</label>
                            <div class="input-group">
                                <select class="form-select" id="driveMarca" name="driveMarca" required>
                                    <option value="">Selecione a marca</option>
                                    <?php foreach ($marcas as $marca): ?>
                                        <option value="<?= $marca['id'] ?>"><?= $marca['nome'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary" onclick="adicionarMarcaDrive()">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Cor</label>
                            <div class="input-group">
                                <select class="form-select" id="driveCor" name="driveCor" required>
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
                            <select class="form-select" id="driveEstado" name="driveEstado" required>
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
                    <button type="button" class="btn btn-primary" id="saveButton" onclick="salvarDrive()">Adicionar</button>
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
                    <p class="mb-0">Confirma os registos corretos para o drive que está sem reparo? Ao confirmar a ação não poderá ser desfeita.</p>
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
                    <input type="hidden" id="driveIdToDelete">
                    <p class="mb-0">Tem a certeza que pretende apagar este drive?</p>
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
            loadDrives();
            
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
                    pesquisarDrives();
                }
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

        let formDataTemp = null;
        let isEditingTemp = false;
        let driveIdTemp = null;

        function salvarDrive() {
            const form = document.getElementById('addDriveForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Esconder a mensagem de erro se estiver visível
            document.getElementById('referenceErrorBox').style.display = 'none';

            const isEditing = document.getElementById('isEditing').value === 'true';
            const driveId = document.getElementById('driveId').value;
            const driveRef = document.getElementById('driveRef').value;
            
            // Se não for edição, verificar se a referência já existe
            if (!isEditing) {
                fetch(`../stock/drive/verificar_referencia.php?ref=${encodeURIComponent(driveRef)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        // Mostrar a mensagem de erro
                        document.getElementById('referenceErrorBox').style.display = 'block';
                    } else {
                        // Referência não existe, podemos continuar
                        const formData = new FormData(form);
                        formDataTemp = formData;
                        isEditingTemp = isEditing;
                        driveIdTemp = driveId;
                        
                        if (formData.get('driveEstado') === '4') {
                            const confirmModal = new bootstrap.Modal(document.getElementById('confirmSemReparoModal'));
                            confirmModal.show();
                            return;
                        }
                        
                        enviarFormulario();
                    }
                })
                .catch(error => {
                    console.error("Erro ao verificar referência:", error);
                    // Em caso de erro na verificação, prosseguir normalmente
                    const formData = new FormData(form);
                    formDataTemp = formData;
                    isEditingTemp = isEditing;
                    driveIdTemp = driveId;
                    
                    if (formData.get('driveEstado') === '4') {
                        const confirmModal = new bootstrap.Modal(document.getElementById('confirmSemReparoModal'));
                        confirmModal.show();
                        return;
                    }
                    
                    enviarFormulario();
                });
            } else {
                // Se for edição, não verificar referência (pois ela já existe)
                const formData = new FormData(form);
                formDataTemp = formData;
                isEditingTemp = isEditing;
                driveIdTemp = driveId;
                
                if (formData.get('driveEstado') === '4') {
                    const confirmModal = new bootstrap.Modal(document.getElementById('confirmSemReparoModal'));
                    confirmModal.show();
                    return;
                }
                
                enviarFormulario();
            }
        }

        function enviarFormulario() {
            const url = isEditingTemp ? '../stock/drive/atualizar_drive.php' : '../stock/drive/salvar_stock_drive.php';
            
            // Se estiver editando, adiciona o ID ao formData
            if (isEditingTemp && driveIdTemp) {
                formDataTemp.append('id', driveIdTemp);
            }
            
            // Garantir que o estado seja incluído mesmo se o campo estiver desabilitado
            if (isEditingTemp && document.getElementById('driveEstado').disabled) {
                const driveEstado = document.getElementById('driveEstado').value;
                formDataTemp.set('driveEstado', driveEstado);
            }
            
            // Log para debug
            console.log('Enviando dados para:', url);
            console.log('É edição?', isEditingTemp);
            console.log('ID do drive:', driveIdTemp);
            console.log('Dados do formulário:', Object.fromEntries(formDataTemp));
            
            // Adicionar timestamp para evitar cache
            const timestamp = new Date().getTime();
            const urlWithTimestamp = `${url}?nocache=${timestamp}`;
            
            fetch(urlWithTimestamp, {
                method: 'POST',
                body: formDataTemp,
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
                console.log('Dados da resposta:', data);
                if (data.success) {
                    // Resetar o formulário e fechar o modal apenas se a operação for bem-sucedida
                    const form = document.getElementById('addDriveForm');
                    form.reset();
                    document.getElementById('isEditing').value = 'false';
                    document.getElementById('driveId').value = '';
                    document.getElementById('modalTitle').textContent = 'Adicionar';
                    document.getElementById('saveButton').textContent = 'Adicionar';

                    const modal = bootstrap.Modal.getInstance(document.getElementById('addDriveModal'));
                    if (modal) {
                        modal.hide();
                    }

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
                        loadDrives();
                    }, 300);
                } else {
                    throw new Error(data.message || 'Erro ao processar drive');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: error.message || "Erro ao processar drive",
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
            
            const form = document.getElementById('addDriveForm');
            const formData = new FormData(form);
            
            formData.set('driveEstado', '4');
            
            if (isEditingTemp) {
                formData.set('id', driveIdTemp);
                console.log('ID definido para atualização em Sem Reparo:', driveIdTemp);
            }
            
            console.log('FormData do Sem Reparo:');
            console.log('isEditing:', isEditingTemp);
            console.log('driveId:', driveIdTemp);
            console.log('id no formData:', formData.get('id'));
            console.log('Todos os dados:', Object.fromEntries(formData));
            
            formDataTemp = formData;
            enviarFormulario();
        }

        function editarDrive(id) {
            console.log('Iniciando edição do drive:', id);
            
            // Adicionar timestamp para evitar cache
            const timestamp = new Date().getTime();
            fetch(`../stock/drive/buscar_drive.php?id=${id}&nocache=${timestamp}`, {
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
                        const drive = data.data;
                        console.log('Dados do drive:', drive);

                        // Verificar se o drive existe
                        if (!drive) {
                            throw new Error('Dados do drive não encontrados');
                        }

                        // Verificar se o drive está em estado "Sem Reparo" ou "Em Uso"
                        if (drive.DRVD_ESTADO === '4') {
                            Toastify({
                                text: "Não é possível editar um drive marcado como Sem Reparo",
                                duration: 3000,
                                gravity: "top",
                                position: "right",
                                style: {
                                    background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                                }
                            }).showToast();
                            return;
                        }
                        
                        if (drive.DRVD_ESTADO === '2') {
                            Toastify({
                                text: "Não é possível editar um drive que está em uso",
                                duration: 3000,
                                gravity: "top",
                                position: "right",
                                style: {
                                    background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                                }
                            }).showToast();
                            return;
                        }
                        
                        document.getElementById('driveId').value = drive.DRVD_ID;
                        document.getElementById('isEditing').value = 'true';
                        
                        document.getElementById('driveRef').value = drive.DRVD_REF;
                        document.getElementById('driveRef').readOnly = true;
                        document.getElementById('driveMarca').value = drive.DRVD_MARCA;
                        document.getElementById('driveCor').value = drive.DRVD_COR;
                        document.getElementById('driveEstado').value = drive.DRVD_ESTADO;
                        
                        // Verificar se o estado é "Manutenção" (ID 3)
                        if (drive.DRVD_ESTADO === '3') {
                            document.getElementById('driveEstado').disabled = false;
                            
                            // Limitar as opções para apenas "Disponível" (1) e "Sem Reparo" (4)
                            const estadoSelect = document.getElementById('driveEstado');
                            for (let i = 0; i < estadoSelect.options.length; i++) {
                                const option = estadoSelect.options[i];
                                // Desabilitar todas as opções exceto "Disponível" (1) e "Sem Reparo" (4)
                                if (option.value !== '1' && option.value !== '4' && option.value !== '') {
                                    option.disabled = true;
                                }
                            }
                        } else {
                            document.getElementById('driveEstado').disabled = true;
                        }

                        document.getElementById('modalTitle').textContent = 'Editar Drive';
                        document.getElementById('saveButton').textContent = 'Salvar Alterações';

                        const estadoContainer = document.getElementById('estadoContainer');
                        estadoContainer.style.display = 'block';

                        const modal = new bootstrap.Modal(document.getElementById('addDriveModal'));
                        modal.show();
                    } else {
                        throw new Error(data.message || 'Erro ao carregar dados do drive');
                    }
                })
                .catch(error => {
                    console.error('Erro ao editar drive:', error);
                    Toastify({
                        text: error.message || "Erro ao carregar dados do drive",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                        }
                    }).showToast();
                });
        }

        // Função para excluir drive
        function excluirDrive(id) {
            document.getElementById('driveIdToDelete').value = id;
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            confirmModal.show();
        }
        
        function confirmarExclusao() {
            const id = document.getElementById('driveIdToDelete').value;
            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
            confirmModal.hide();
            
            const formData = new FormData();
            formData.append('id', id);
            
            // Adicionar timestamp para evitar cache
            const timestamp = new Date().getTime();

            fetch(`../stock/drive/excluir_drive.php?nocache=${timestamp}`, {
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
                        text: data.message || "Drive excluído com sucesso!",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                    
                    // Adicionar um pequeno atraso antes de recarregar a tabela
                    setTimeout(() => {
                        loadDrives();
                    }, 300);
                } else {
                    throw new Error(data.message || "Erro ao excluir drive");
                }
            })
            .catch(error => {
                console.error('Erro ao excluir drive:', error);
                Toastify({
                    text: error.message || "Erro ao excluir drive",
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
        function adicionarMarcaDrive() {
            const modal = new bootstrap.Modal(document.getElementById('addMarcaModal'));
            modal.show();
        }

        // Função para salvar nova marca com prevenção de cache
        function salvarNovaMarca() {
            const nomeMarca = document.getElementById('novaMarca').value;
            if (!nomeMarca) return;

            const formData = new FormData();
            formData.append('nome_marca', nomeMarca);
            
            const timestamp = new Date().getTime();
            fetch(`../stock/drive/adicionar_marca_drive.php?nocache=${timestamp}`, {
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
                    const select = document.getElementById('driveMarca');
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
                console.error('Erro ao adicionar marca:', error);
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

        // Função para abrir modal de adicionar cor
        function adicionarCor() {
            const modal = new bootstrap.Modal(document.getElementById('addCorModal'));
            modal.show();
        }

        // Função para salvar nova cor com prevenção de cache
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
                    const select = document.getElementById('driveCor');
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
                } else {
                    throw new Error(data.message || 'Erro ao adicionar cor');
                }
            })
            .catch(error => {
                console.error('Erro ao adicionar cor:', error);
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

        // Função para pesquisar drives
        function pesquisarDrives() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            // Adicionar timestamp para evitar cache
            const timestamp = new Date().getTime();
            fetch(`../stock/drive/buscar_stock_drives.php?nocache=${timestamp}`, {
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
                    throw new Error(response.message || 'Erro ao carregar drives');
                }
                const filteredData = response.data.filter(drive => {
                    const driveId = `DRIVE ${drive.id}`.toLowerCase();
                    return (
                        driveId.includes(searchTerm) ||
                        drive.referencia.toLowerCase().includes(searchTerm) ||
                        drive.marca.toLowerCase().includes(searchTerm) ||
                        drive.cor.toLowerCase().includes(searchTerm) ||
                        drive.estado.toLowerCase().includes(searchTerm) ||
                        (drive.DRVD_SALA ? drive.DRVD_SALA.toLowerCase().includes(searchTerm) : false) ||
                        (drive.computador_ref ? drive.computador_ref.toLowerCase().includes(searchTerm) : false)
                    );
                });
                
                // Ordenar os itens do mais recente para o mais antigo
                const sortedFilteredData = [...filteredData].sort((a, b) => b.id - a.id);

                const tbody = document.querySelector('table tbody');
                if (sortedFilteredData.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Nenhum drive encontrado com os critérios de pesquisa
                                </div>
                            </td>
                        </tr>
                    `;
                } else {
                    tbody.innerHTML = sortedFilteredData.map(drive => {
                        const isSemReparo = drive.estado === 'Sem Reparo' || drive.DRVD_ESTADO === '4';
                        const isEmUso = drive.estado === 'Em Uso' || drive.estado_id === '2';
                        
                        let estadoClass = '';
                        switch(drive.estado) {
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
                        
                        // Sala info
                        const salaInfo = drive.DRVD_SALA ? 
                            `<strong>${drive.DRVD_SALA}</strong>` : 
                            'Nenhuma Associada';
                        
                        let actionButtons = '';
                        if (isSemReparo) {
                            actionButtons = '<span class="sem-reparo-badge">Sem Reparo</span>';
                        } else if (isEmUso) {
                            actionButtons = `
                                <button class="btn btn-sm btn-outline-danger" onclick="excluirDrive(${drive.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            `;
                        } else {
                            actionButtons = `
                                <button class="btn btn-sm btn-outline-primary me-1" onclick="editarDrive(${drive.id})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="excluirDrive(${drive.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            `;
                        }
                        
                        return `
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-device-hdd me-2 text-primary"></i>
                                        <strong>DRIVE ${drive.id}</strong>
                                    </div>
                                </td>
                                <td>${drive.referencia || ''}</td>
                                <td>${drive.marca || ''}</td>
                                <td>${drive.cor || ''}</td>
                                <td>
                                    ${drive.computador_ref ? 
                                        `<span class="computador-associado">${drive.computador_ref}</span>` : 
                                        '<span class="sem-computador">Nenhum Associado</span>'
                                    }
                                </td>
                                <td>${salaInfo}</td>
                                <td><span class="${estadoClass}">${drive.estado || ''}</span></td>
                                <td class="inventory-actions">
                                    ${actionButtons}
                                </td>
                            </tr>
                        `;
                    }).join('');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: "Erro ao pesquisar drives: " + error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        // Função para carregar a lista de drives
        function loadDrives() {
            console.log('Carregando lista de drives...');
            // Adicionar timestamp para evitar cache
            const timestamp = new Date().getTime();
            fetch(`../stock/drive/buscar_stock_drives.php?nocache=${timestamp}`, {
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
                                    <td colspan="8" class="text-center">
                                        <div class="alert alert-info mb-0">
                                            <i class="bi bi-info-circle me-2"></i>
                                            Nenhum drive encontrado
                                        </div>
                                    </td>
                                </tr>
                            `;
                            return;
                        }
                        
                        // Ordenar os itens do mais recente para o mais antigo (assumindo que ID maior = mais recente)
                        const sortedData = [...data.data].sort((a, b) => b.id - a.id);
                        
                        sortedData.forEach(drive => {
                            console.log('Renderizando drive:', drive);
                            const isSemReparo = drive.estado === 'Sem Reparo' || drive.DRVD_ESTADO === '4';
                            const isEmUso = drive.estado === 'Em Uso' || drive.estado_id === '2';
                            
                            let estadoClass = '';
                            switch(drive.estado) {
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
                            
                            // Sala info
                            const salaInfo = drive.DRVD_SALA ? 
                                `<strong>${drive.DRVD_SALA}</strong>` : 
                                'Nenhuma Associada';
                            
                            let actionButtons = '';
                            if (isSemReparo) {
                                actionButtons = '<span class="sem-reparo-badge">Sem Reparo</span>';
                            } else if (isEmUso) {
                                actionButtons = `
                                    <button class="btn btn-sm btn-outline-danger" onclick="excluirDrive(${drive.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                `;
                            } else {
                                actionButtons = `
                                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editarDrive(${drive.id})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="excluirDrive(${drive.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                `;
                            }
                            
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-device-hdd me-2 text-primary"></i>
                                        <strong>DRIVE ${drive.id}</strong>
                                    </div>
                                </td>
                                <td>${drive.referencia || ''}</td>
                                <td>${drive.marca || ''}</td>
                                <td>${drive.cor || ''}</td>
                                <td>
                                    ${drive.computador_ref ? 
                                        `<span class="computador-associado">${drive.computador_ref}</span>` : 
                                        '<span class="sem-computador">Nenhum Associado</span>'
                                    }
                                </td>
                                <td>${salaInfo}</td>
                                <td><span class="${estadoClass}">${drive.estado || ''}</span></td>
                                <td class="inventory-actions">
                                    ${actionButtons}
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                    } else {
                        console.error('Erro ao carregar drives:', data.message);
                        const tbody = document.querySelector('tbody');
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="alert alert-danger mb-0">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        Erro ao carregar drives: ${data.message || 'Erro desconhecido'}
                                    </div>
                                </td>
                            </tr>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar drives:', error);
                    const tbody = document.querySelector('tbody');
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="alert alert-danger mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Erro ao carregar drives: ${error.message || 'Erro desconhecido'}
                                </div>
                            </td>
                        </tr>
                    `;
                    
                    Toastify({
                        text: "Erro ao carregar drives: " + error.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                        }
                    }).showToast();
                });
        }

        // Reset form function
        function resetForm() {
            document.getElementById('addDriveForm').reset();
            document.getElementById('isEditing').value = 'false';
            document.getElementById('driveId').value = '';
            document.getElementById('modalTitle').textContent = 'Adicionar';
            document.getElementById('saveButton').textContent = 'Adicionar';
            
            // Remover atributos readOnly e disabled
            document.getElementById('driveRef').readOnly = false;
            document.getElementById('driveEstado').disabled = false;
            
            // Resetar as opções desabilitadas no select de estado
            const estadoSelect = document.getElementById('driveEstado');
            for (let i = 0; i < estadoSelect.options.length; i++) {
                estadoSelect.options[i].disabled = false;
            }
            
            // Esconder a mensagem de erro de referência
            document.getElementById('referenceErrorBox').style.display = 'none';
        }

        // Add a reset form call when modal is closed
        document.getElementById('addDriveModal').addEventListener('hidden.bs.modal', function () {
            resetForm();
        });
        
        // Verificar referência ao sair do campo
        document.getElementById('driveRef').addEventListener('blur', function() {
            const isEditing = document.getElementById('isEditing').value === 'true';
            
            // Não verificar se estiver editando (pois a referência já existe)
            if (isEditing) return;
            
            const ref = this.value.trim();
            if (ref) {
                fetch(`../stock/drive/verificar_referencia.php?ref=${encodeURIComponent(ref)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        document.getElementById('referenceErrorBox').style.display = 'block';
                    } else {
                        document.getElementById('referenceErrorBox').style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error("Erro ao verificar referência:", error);
                    document.getElementById('referenceErrorBox').style.display = 'none';
                });
            } else {
                document.getElementById('referenceErrorBox').style.display = 'none';
            }
        });
    </script>
</body>
</html>