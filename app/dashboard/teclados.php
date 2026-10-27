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
    $marcas = $pdo->query("SELECT MTECLADO_ID, MTECLADO_NOME FROM marcateclado ORDER BY MTECLADO_NOME")->fetchAll();
    $cores = $pdo->query("SELECT ID_COR, COR FROM cor ORDER BY COR")->fetchAll();
    $estados = $pdo->query("SELECT ID_ESTADO, ESTADO FROM teclados_estado WHERE ESTADO IN ('Disponível', 'Manutenção', 'Sem Reparo') ORDER BY ESTADO")->fetchAll();
    $tipos = $pdo->query("SELECT TECLADO_TIPO FROM tecladotipo ORDER BY TECLADO_TIPO")->fetchAll();
} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Teclados - GM HelpDesk</title>
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
                gap: 4px;
            }
            
            .inventory-actions .btn {
                padding: 0.2rem 0.4rem;
                font-size: 0.75rem;
            }
            
            .table td:last-child {
                padding: 0.5rem 0.3rem;
            }
            
            .sem-reparo-badge {
                padding: 7px 10px;
                font-size: 12px;
                min-width: 85px;
                letter-spacing: 0.2px;
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
            
            .sem-reparo-badge {
                padding: 6px 8px;
                font-size: 11px;
                min-width: 60px;
                min-height: 38px;
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

        /* Ocultar botão de sidebar toggle */
        .sidebar-toggle {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1000;
            background-color: rgba(75, 134, 180, 0.15);
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
            width: 45px;
            height: 45px;
            border: 2px solid #4b86b4;
            transition: all 0.3s ease;
            padding: 0;
            display: none; /* Botão oculto por padrão, será mostrado apenas em telas móveis via JavaScript */
            justify-content: center;
            align-items: center;
        }
        
        .sidebar-toggle:hover {
            background-color: rgba(75, 134, 180, 0.25);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }
        
        .sidebar-toggle:active {
            transform: translateY(1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-toggle i {
            font-size: 1.5rem;
            color: #2a4d69;
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

        /* Estilo para botão voltar ao inventário */
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
            margin-bottom: 0;
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
        
        .sem-reparo-badge {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 13px;
            border: 1px solid rgba(220, 53, 69, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            margin: 0 auto;
            white-space: nowrap;
            min-width: 90px;
            font-weight: 500;
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

        /* Estilo para as ações na tabela (botões e badges) */
        .inventory-actions {
            display: flex;
            gap: 8px;
            white-space: nowrap;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .inventory-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }

        /* Estilos para a tabela e células */
        .table {
            margin-bottom: 0;
        }

        .table th, .table td {
            vertical-align: middle;
            padding: 0.75rem;
        }

        /* Ajuste específico para a coluna de ações */
        .table td:last-child {
            text-align: center;
            padding: 0.5rem;
        }

        /* Estilo para os botões nas ações */
        .table td .d-flex.gap-1.justify-content-center {
            justify-content: center;
            margin: 0 auto;
        }

        @media (max-width: 576px) {
            .inventory-actions {
                gap: 4px;
            }
            
            .inventory-actions .btn {
                padding: 0.2rem 0.4rem;
                font-size: 0.75rem;
            }
            
            .table td:last-child {
                padding: 0.5rem 0.3rem;
            }
            
            .sem-reparo-badge {
                padding: 7px 10px;
                font-size: 12px;
                min-width: 85px;
                letter-spacing: 0.2px;
            }
        }

        @media (max-width: 375px) {
            .sem-reparo-badge {
                padding: 6px 8px;
                font-size: 11px;
                min-width: 60px;
                min-height: 38px;
            }
        }

        /* Estilo para centralizar o texto na coluna Teclado */
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

        .estado-em-uso {
            color: #0d6efd;
            font-weight: 500;
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
                        <i class="bi bi-keyboard"></i>
                        <div>
                            <h4>Gestão de Teclados</h4>
                            <p class="mb-0 mt-1 text-light">Stock atual: <span id="stock-count" class="fw-bold">0</span> teclados</p>
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
                                       placeholder="Pesquisar teclado...">
                            </div>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTecladoModal">
                            <i class="bi bi-plus-lg me-2"></i><span>Adicionar</span>
                        </button>
                    </div>
                </div>

                <!-- Tabela de Teclados -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Teclado</th>
                                        <th>Referência</th>
                                        <th>Marca</th>
                                        <th>Cor</th>
                                        <th>Tipo</th>
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

    <!-- Modal Adicionar/Editar Teclado -->
    <div class="modal fade" id="addTecladoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Adicionar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addTecladoForm">
                        <input type="hidden" id="isEditing" value="false">
                        <input type="hidden" id="tecladoId" name="tecladoId" value="">
                        
                        <!-- Mensagem de erro de referência duplicada -->
                        <div class="reference-error-box" id="referenceErrorBox">
                            <div class="error-title">Referência Duplicada</div>
                            <div class="error-message">Essa referência já existe. Por favor, escolha outra.</div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Referência</label>
                            <input type="text" class="form-control" id="tecladoRef" name="tecladoRef" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Marca</label>
                            <div class="input-group">
                                <select class="form-select" id="tecladoMarca" name="tecladoMarca" required>
                                    <option value="">Selecione a marca</option>
                                    <?php foreach ($marcas as $marca): ?>
                                        <option value="<?= $marca['MTECLADO_ID'] ?>"><?= $marca['MTECLADO_NOME'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary" onclick="adicionarMarcaTeclado()">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Cor</label>
                            <div class="input-group">
                                <select class="form-select" id="tecladoCor" name="tecladoCor" required>
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
                        <div class="form-group mb-3">
                            <label class="form-label">Tipo</label>
                            <div class="input-group">
                                <select class="form-select" id="tecladoTipo" name="tecladoTipo" required>
                                    <option value="">Selecione o tipo</option>
                                    <?php foreach ($tipos as $tipo): ?>
                                        <option value="<?= $tipo['TECLADO_TIPO'] ?>"><?= $tipo['TECLADO_TIPO'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-outline-primary" onclick="adicionarTipoTeclado()">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group mb-3" id="estadoContainer">
                            <label class="form-label">Estado</label>
                            <select class="form-select" id="tecladoEstado" name="tecladoEstado">
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
                    <button type="button" class="btn btn-primary" id="saveButton" onclick="salvarTeclado()">Adicionar</button>
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

    <!-- Modal Adicionar Tipo -->
    <div class="modal fade" id="addTipoModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Tipo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Nome do Tipo</label>
                        <input type="text" class="form-control" id="novoTipo" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarNovoTipo()">Adicionar</button>
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
                    <p class="mb-0">Confirma os registos corretos para o teclado que está sem reparo? Ao confirmar a ação não poderá ser desfeita.</p>
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
                    <input type="hidden" id="tecladoIdToDelete">
                    <p class="mb-0">Tem a certeza que pretende apagar este teclado?</p>
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
            loadTeclados();
            
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
                if (window.innerWidth > 768) {
                    document.querySelector('.sidebar').classList.remove('active');
                    document.body.classList.remove('menu-active');
                    const overlay = document.querySelector('.sidebar-overlay');
                    if (overlay) overlay.remove();
                }
                checkToggleDisplay();
            });
            
            // Pesquisa em tempo real
            document.getElementById('searchInput').addEventListener('input', function() {
                if (this.value.length >= 2 || this.value.length === 0) {
                    pesquisarTeclados();
                }
            });
            
            // Resetar formulário quando o modal for fechado
            document.getElementById('addTecladoModal').addEventListener('hidden.bs.modal', function () {
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

        function loadTeclados() {
            fetch('../stock/teclado/buscar_stock_teclados.php')
            .then(response => response.json())
            .then(response => {
                if (!response.success) {
                    throw new Error(response.message || 'Erro ao carregar teclados');
                }
                
                // Debug - Verificar a estrutura do primeiro item
                if (response.data && response.data.length > 0) {
                    console.log("Estrutura de dados do teclado:", response.data[0]);
                }
                
                const tbody = document.querySelector('table tbody');
                
                // Atualizar o contador de stock
                const stockCount = document.getElementById('stock-count');
                if (response.data && Array.isArray(response.data)) {
                    stockCount.textContent = response.data.length;
                } else {
                    stockCount.textContent = "0";
                }
                
                if (!response.data || response.data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Nenhum teclado encontrado
                                </div>
                            </td>
                        </tr>
                    `;
                    return;
                }
                
                tbody.innerHTML = response.data.map(teclado => {
                    // Determinar a classe do estado
                    let estadoClass = '';
                    if (teclado.estado === 'Disponível') {
                        estadoClass = 'estado-disponivel';
                    } else if (teclado.estado === 'Manutenção') {
                        estadoClass = 'estado-manutencao';
                    } else if (teclado.estado === 'Sem Reparo') {
                        estadoClass = 'estado-sem-reparo';
                    } else if (teclado.estado === 'Em Uso') {
                        estadoClass = 'estado-em-uso';
                    }

                    // Verificar se o teclado está sem reparo
                    const isSemReparo = teclado.estado === 'Sem Reparo';
                    
                    // Verificar se o teclado está em uso
                    const isEmUso = teclado.estado === 'Em Uso';
                    
                    // Informação da sala
                    const salaInfo = teclado.TECLADO_SALA ? 
                        `<strong>${teclado.TECLADO_SALA}</strong>` : 
                        'Nenhuma Associada';
                        
                    // Botões de ação conforme o estado
                    let acoesBotoes = '';
                    
                    if (isSemReparo) {
                        acoesBotoes = '<span class="sem-reparo-badge">Sem Reparo</span>';
                    } else if (isEmUso) {
                        // Para componentes "Em Uso", mostrar apenas o botão de excluir
                        acoesBotoes = `
                            <div class="d-flex gap-1 justify-content-center">
                                <button class="btn btn-sm btn-outline-danger" onclick="excluirTeclado(${teclado.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        `;
                    } else {
                        // Para outros estados, mostrar botões de editar e excluir
                        acoesBotoes = `
                            <div class="d-flex gap-1 justify-content-center">
                                <button class="btn btn-sm btn-outline-primary" onclick="editarTeclado(${teclado.id})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="excluirTeclado(${teclado.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        `;
                    }

                    return `
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-keyboard me-2 text-primary"></i>
                                    <strong>TECLADO ${teclado.id}</strong>
                                </div>
                            </td>
                            <td>${teclado.referencia}</td>
                            <td>${teclado.marca}</td>
                            <td>${teclado.cor}</td>
                            <td>${teclado.tipo || '-'}</td>
                            <td>
                                ${teclado.computador_ref ? 
                                    `<strong>${teclado.computador_ref}</strong>` : 
                                    'Nenhum Associado'
                                }
                            </td>
                            <td>${salaInfo}</td>
                            <td><span class="${estadoClass}">${teclado.estado}</span></td>
                            <td>${acoesBotoes}</td>
                        </tr>
                    `;
                }).join('');
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: "Erro ao carregar teclados: " + error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        function pesquisarTeclados() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            fetch('../stock/teclado/buscar_stock_teclados.php')
            .then(response => response.json())
            .then(response => {
                if (!response.success) {
                    throw new Error(response.message || 'Erro ao carregar teclados');
                }
                const filteredData = response.data.filter(teclado => {
                    const tecladoId = teclado.id ? `TECLADO ${teclado.id}`.toLowerCase() : '';
                    const computadorInfo = teclado.computador_ref ? `COMPUTADOR ${teclado.computador_ref}`.toLowerCase() : '';
                    return (
                        tecladoId.includes(searchTerm) ||
                        (teclado.referencia ? teclado.referencia.toLowerCase().includes(searchTerm) : false) ||
                        (teclado.marca ? teclado.marca.toLowerCase().includes(searchTerm) : false) ||
                        (teclado.cor ? teclado.cor.toLowerCase().includes(searchTerm) : false) ||
                        (teclado.tipo ? teclado.tipo.toLowerCase().includes(searchTerm) : false) ||
                        (teclado.TECLADO_SALA ? teclado.TECLADO_SALA.toLowerCase().includes(searchTerm) : false) ||
                        (teclado.estado ? teclado.estado.toLowerCase().includes(searchTerm) : false) ||
                        computadorInfo.includes(searchTerm)
                    );
                });

                const tbody = document.querySelector('table tbody');
                if (filteredData.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="9" class="text-center">
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Nenhum teclado encontrado com os critérios de pesquisa
                                </div>
                            </td>
                        </tr>
                    `;
                } else {
                    tbody.innerHTML = filteredData.map(teclado => {
                        // Determinar a classe do estado
                        let estadoClass = '';
                        if (teclado.estado === 'Disponível') {
                            estadoClass = 'estado-disponivel';
                        } else if (teclado.estado === 'Manutenção') {
                            estadoClass = 'estado-manutencao';
                        } else if (teclado.estado === 'Sem Reparo') {
                            estadoClass = 'estado-sem-reparo';
                        } else if (teclado.estado === 'Em Uso') {
                            estadoClass = 'estado-em-uso';
                        }

                        // Verificar se o teclado está sem reparo
                        const isSemReparo = teclado.estado === 'Sem Reparo';
                        
                        // Verificar se o teclado está em uso
                        const isEmUso = teclado.estado === 'Em Uso';
                        
                        // Informação da sala
                        const salaInfo = teclado.TECLADO_SALA ? 
                            `<strong>${teclado.TECLADO_SALA}</strong>` : 
                            'Nenhuma Associada';
                            
                        // Botões de ação conforme o estado
                        let acoesBotoes = '';
                        
                        if (isSemReparo) {
                            acoesBotoes = '<span class="sem-reparo-badge">Sem Reparo</span>';
                        } else if (isEmUso) {
                            // Para componentes "Em Uso", mostrar apenas o botão de excluir
                            acoesBotoes = `
                                <div class="d-flex gap-1 justify-content-center">
                                    <button class="btn btn-sm btn-outline-danger" onclick="excluirTeclado(${teclado.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            `;
                        } else {
                            // Para outros estados, mostrar botões de editar e excluir
                            acoesBotoes = `
                                <div class="d-flex gap-1 justify-content-center">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editarTeclado(${teclado.id})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="excluirTeclado(${teclado.id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            `;
                        }

                        return `
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-keyboard me-2 text-primary"></i>
                                        <strong>TECLADO ${teclado.id}</strong>
                                    </div>
                                </td>
                                <td>${teclado.referencia}</td>
                                <td>${teclado.marca}</td>
                                <td>${teclado.cor}</td>
                                <td>${teclado.tipo || '-'}</td>
                                <td>
                                    ${teclado.computador_ref ? 
                                        `<strong>${teclado.computador_ref}</strong>` : 
                                        'Nenhum Associado'
                                    }
                                </td>
                                <td>${salaInfo}</td>
                                <td><span class="${estadoClass}">${teclado.estado}</span></td>
                                <td>${acoesBotoes}</td>
                            </tr>
                        `;
                    }).join('');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: "Erro ao pesquisar teclados: " + error.message,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        function editarTeclado(id) {
            fetch(`../stock/teclado/buscar_teclado.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Verificar se o teclado está em estado "Sem Reparo"
                    if (data.data.TECLADO_ESTADO === '4') { // ID 4 é "Sem Reparo"
                        Toastify({
                            text: "Não é possível editar um teclado marcado como Sem Reparo",
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            style: {
                                background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                            }
                        }).showToast();
                        return;
                    }

                    // Mostrar notificação informativa quando editar um teclado disponível
                    if (data.data.TECLADO_ESTADO === '1') { // ID 1 é "Disponível"
                        Toastify({
                            text: "Teclado está Disponível. O estado não pode ser alterado.",
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            style: {
                                background: "linear-gradient(to right, #007bff, #6610f2)",
                            }
                        }).showToast();
                    }

                    document.getElementById('tecladoId').value = data.data.TECLADO_ID;
                    document.getElementById('tecladoRef').value = data.data.TECLADO_REF;
                    document.getElementById('tecladoRef').readOnly = true;
                    document.getElementById('tecladoMarca').value = data.data.TECLADO_MARCA;
                    document.getElementById('tecladoCor').value = data.data.TECLADO_COR;
                    document.getElementById('tecladoTipo').value = data.data.TECLADO_TIPO || '';
                    document.getElementById('tecladoEstado').value = data.data.TECLADO_ESTADO || '';
                    
                    // Verificar se o teclado está em estado de manutenção (ID 3)
                    if (data.data.TECLADO_ESTADO === '3') {
                        // Se estiver em manutenção, permitir a alteração do estado
                        document.getElementById('tecladoEstado').disabled = false;
                        
                        // Limitar as opções de estado para apenas "Disponível" (1) e "Sem Reparo" (4)
                        const estadoSelect = document.getElementById('tecladoEstado');
                        for (let i = 0; i < estadoSelect.options.length; i++) {
                            const option = estadoSelect.options[i];
                            if (option.value !== '1' && option.value !== '4' && option.value !== '3') {
                                option.disabled = true;
                            } else {
                                option.disabled = false;
                            }
                        }
                    } else {
                        // Se não estiver em manutenção, desabilitar a alteração do estado
                        document.getElementById('tecladoEstado').disabled = true;
                    }
                    
                    document.getElementById('isEditing').value = 'true';
                    document.getElementById('modalTitle').textContent = 'Editar Teclado ' + data.data.TECLADO_ID;
                    document.getElementById('saveButton').textContent = 'Salvar Alterações';

                    // Mostrar o campo de estado na edição
                    const estadoContainer = document.getElementById('estadoContainer');
                    const tecladoEstado = document.getElementById('tecladoEstado');
                    estadoContainer.style.display = 'block';
                    tecladoEstado.required = true;
                    
                    const modal = new bootstrap.Modal(document.getElementById('addTecladoModal'));
                    modal.show();
                } else {
                    throw new Error(data.message || 'Erro ao carregar dados do teclado');
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

        // Variável global para armazenar dados temporários do formulário
        let formDataTemp = null;

        function salvarTeclado() {
            const form = document.getElementById('addTecladoForm');
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
                const referencia = document.getElementById('tecladoRef').value;
                
                // Verificar se a referência já existe
                fetch(`../stock/teclado/verificar_referencia.php?ref=${encodeURIComponent(referencia)}`)
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
            const form = document.getElementById('addTecladoForm');
            const formData = new FormData(form);
            
            // Garantir que o estado seja incluído mesmo que o campo esteja desabilitado
            const isEditing = document.getElementById('isEditing').value === 'true';
            if (isEditing && document.getElementById('tecladoEstado').disabled) {
                formData.set('tecladoEstado', document.getElementById('tecladoEstado').value);
            }
            
            const estado = document.getElementById('tecladoEstado').value;
            const tecladoId = document.getElementById('tecladoId').value;
            
            // Se o estado for "Sem Reparo", mostrar confirmação
            if (estado === '4') { // ID 4 é "Sem Reparo"
                formDataTemp = formData;
                const modal = new bootstrap.Modal(document.getElementById('confirmSemReparoModal'));
                modal.show();
                return;
            }
            
            // Se estiver alterando de manutenção para disponível, informar o usuário
            if (isEditing && document.getElementById('tecladoEstado').disabled === false && estado === '1') {
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
            
            // Se não for "Sem Reparo", prosseguir normalmente
            enviarFormulario(formData);
        }

        function enviarFormulario(formData) {
            const isEditing = document.getElementById('isEditing').value === 'true';
            const url = isEditing ? '../stock/teclado/atualizar_teclado.php' : '../stock/teclado/salvar_stock_teclado.php';
            
            // Garantir que todos os campos sejam enviados corretamente
            const newFormData = new FormData();
            
            if (isEditing) {
                newFormData.append('tecladoId', document.getElementById('tecladoId').value);
            }
            
            newFormData.append('tecladoRef', document.getElementById('tecladoRef').value);
            newFormData.append('tecladoMarca', document.getElementById('tecladoMarca').value);
            newFormData.append('tecladoCor', document.getElementById('tecladoCor').value);
            newFormData.append('tecladoTipo', document.getElementById('tecladoTipo').value);
            
            // Garantir que o estado seja incluído mesmo que o campo esteja desabilitado
            newFormData.append('tecladoEstado', document.getElementById('tecladoEstado').value);
            
            fetch(url, {
                method: 'POST',
                body: newFormData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadTeclados();
                    resetForm();

                    const modal = bootstrap.Modal.getInstance(document.getElementById('addTecladoModal'));
                    modal.hide();

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
                    throw new Error(data.message || 'Erro ao processar teclado');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: error.message || "Erro ao processar teclado",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }

        function excluirTeclado(id) {
            document.getElementById('tecladoIdToDelete').value = id;
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            confirmModal.show();
        }
        
        function confirmarExclusao() {
            const id = document.getElementById('tecladoIdToDelete').value;
            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
            confirmModal.hide();
            
            const formData = new FormData();
            formData.append('id', id);
            
            fetch('../stock/teclado/excluir_teclado.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Toastify({
                        text: "Teclado excluído com sucesso!",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                    
                    loadTeclados();
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

        function resetForm() {
            document.getElementById('addTecladoForm').reset();
            document.getElementById('isEditing').value = 'false';
            document.getElementById('tecladoId').value = '';
            document.getElementById('modalTitle').textContent = 'Adicionar';
            document.getElementById('saveButton').textContent = 'Adicionar';
            document.getElementById('tecladoRef').readOnly = false;
            document.getElementById('tecladoEstado').disabled = false;
            
            // Esconder a mensagem de erro
            document.getElementById('referenceErrorBox').style.display = 'none';
            
            // Mostrar o campo de estado e ajustar required
            const estadoContainer = document.getElementById('estadoContainer');
            const tecladoEstado = document.getElementById('tecladoEstado');
            estadoContainer.style.display = 'block';
            tecladoEstado.required = true;
        }

        // Adicionar evento para controlar visibilidade do campo estado
        document.getElementById('addTecladoModal').addEventListener('show.bs.modal', function () {
            const estadoContainer = document.getElementById('estadoContainer');
            const tecladoEstado = document.getElementById('tecladoEstado');
            estadoContainer.style.display = 'block';
            tecladoEstado.required = true;
        });

        function adicionarMarcaTeclado() {
            const modal = new bootstrap.Modal(document.getElementById('addMarcaModal'));
            modal.show();
        }

        function salvarNovaMarca() {
            const nomeMarca = document.getElementById('novaMarca').value;
            if (!nomeMarca) return;

            const formData = new FormData();
            formData.append('nome_marca', nomeMarca);

            fetch('../stock/teclado/adicionar_marca_teclado.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('tecladoMarca');
                    const option = new Option(data.marca.MTECLADO_NOME, data.marca.MTECLADO_ID);
                    select.add(option);
                    select.value = data.marca.MTECLADO_ID;

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
                    const select = document.getElementById('tecladoCor');
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

        function adicionarTipoTeclado() {
            const modal = new bootstrap.Modal(document.getElementById('addTipoModal'));
            modal.show();
        }

        function salvarNovoTipo() {
            const novoTipo = document.getElementById('novoTipo').value;
            if (!novoTipo) return;

            const formData = new FormData();
            formData.append('nome_tipo', novoTipo);

            fetch('../stock/teclado/adicionar_tipo_teclado.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('tecladoTipo');
                    const option = new Option(data.tipo, data.tipo);
                    select.add(option);
                    select.value = data.tipo;

                    // Fechar o modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addTipoModal'));
                    modal.hide();
                    
                    // Limpar o campo
                    document.getElementById('novoTipo').value = '';

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
                    text: "Erro ao adicionar tipo",
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
            if (formDataTemp) {
                // Garantir que o estado seja 4 (Sem Reparo)
                formDataTemp.set('tecladoEstado', '4');
                
                // Ocultar o modal de confirmação
                const modal = bootstrap.Modal.getInstance(document.getElementById('confirmSemReparoModal'));
                modal.hide();
                
                // Enviar o formulário
                enviarFormulario(formDataTemp);
            }
        }
    </script>
</body>
</html> 