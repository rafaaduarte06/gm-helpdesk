<?php
session_start();
require_once('../../ligacao_base_dados.php');
require_once('../../includes/sidebar.php');

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

if (!isset($_GET['tipo']) || !isset($_GET['id'])) {
    header('Location: contas.php');
    exit;
}

$tipo = $_GET['tipo'];
$id = $_GET['id'];

try {
    if ($tipo === 'tecnico') {
        $stmt = $pdo->prepare("SELECT * FROM suporte_tecnico WHERE id = ?");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM comunidade_escolar WHERE id = ?");
    }
    
    $stmt->execute([$id]);
    $conta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$conta) {
        header('Location: contas.php');
        exit;
    }
} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Conta - GM HelpDesk</title>
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
            background: linear-gradient(135deg, rgba(245, 247, 250, 1), rgba(195, 207, 226, 1));
            background-attachment: fixed;
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
            cursor: pointer;
            display: none; /* Hide by default, will be shown in mobile view via JavaScript */
            justify-content: center;
            align-items: center;
        }
        
        /* Correção específica para o ícone de seta */
        .bi-arrow-left {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1em;
            height: 1em;
            vertical-align: middle;
            line-height: 1;
        }
        
        .icon-container {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        
        .back-to-accounts .icon-container {
            margin-right: 10px;
        }
        
        .back-to-accounts.btn-icon-only .icon-container {
            margin: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .sidebar-toggle i {
            font-size: 1.3rem;
            color: #2a4d69;
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
        
        .sidebar.active + .main-content .sidebar-toggle {
            display: none;
        }

        /* Estilo do cabeçalho da página */
        .stock-header {
            background: linear-gradient(135deg, #2a4d69, rgb(36, 110, 167));
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            margin-top: 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: flex-start;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            transform: translateY(0);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: auto;
            min-width: 250px;
        }
        
        .stock-header:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
            transform: translateY(-2px);
        }

        .stock-title {
            display: flex;
            align-items: center;
            gap: 15px;
            white-space: nowrap;
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

        /* Adicionando um pequeno espaço após o cabeçalho */
        .header-container {
            margin-bottom: 1.5rem;
        }

        /* Card de detalhes da conta */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.9);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .detail-row {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.2rem 0;
            transition: all 0.3s ease;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-row:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .detail-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }

        .detail-value {
            font-size: 1.1rem;
            color: #343a40;
            font-weight: 500;
        }

        .btn-action {
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-right: 0.5rem;
            display: inline-flex;
            align-items: center;
        }

        .btn-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .btn-action i {
            margin-right: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            border: none;
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
        }

        .btn-outline-secondary {
            border: 2px solid #6c757d;
            color: #6c757d;
            background-color: transparent;
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: white;
        }

        /* Ajuste para adicionar espaço acima do cabeçalho principal */
        .main-content .container-fluid {
            padding-top: 15px; /* Reduzido para melhorar o layout geral */
        }

        /* Cabeçalho e navegação superior */
        .page-header-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start; /* Alterado de center para flex-start para melhor alinhamento vertical */
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 15px;
            position: relative; /* Adicionando posição relativa para referência */
        }

        /* Estilo para o botão de voltar */
        .back-to-accounts {
            position: relative;
            z-index: 990;
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
            transition: all 0.3s ease;
            overflow: hidden;
            margin-top: -8px; /* Valor base ajustado */
        }

        .back-to-accounts::before {
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

        .back-to-accounts:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .back-to-accounts:hover::before {
            opacity: 1;
        }

        .back-to-accounts .back-icon {
            margin-right: 10px;
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .back-to-accounts:hover .back-icon {
            transform: translateX(-4px);
        }

        .back-to-accounts .back-text {
            font-size: 0.95rem;
        }

        /* Media query para ajustar o botão em telas médias */
        @media (min-width: 768px) and (max-width: 991px) {
            .back-to-accounts {
                margin-top: -10px;
            }
        }

        /* Media query para ajustar o botão em telas maiores (PC) */
        @media (min-width: 992px) {
            .back-to-accounts {
                margin-top: -22px; /* Valor mais negativo para empurrar mais para cima em PCs */
            }
        }

        /* Alinhamento específico para telas muito grandes */
        @media (min-width: 1200px) {
            .back-to-accounts {
                margin-top: -25px; /* Ainda mais para cima em telas maiores */
            }
        }

        /* Estilo para o botão quando está apenas com ícone */
        .back-to-accounts.btn-icon-only {
            position: fixed;
            top: 15px;
            right: 15px;
            padding: 0;
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            z-index: 1000;
            background-color: rgba(75, 134, 180, 0.15) !important;
            background: none !important;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
            border: 2px solid #4b86b4;
            transition: all 0.3s ease;
            cursor: pointer;
            overflow: hidden;
            text-decoration: none;
        }

        .back-to-accounts.btn-icon-only .back-icon {
            margin: 0;
            padding: 0;
            font-size: 1.2rem;
            color: #2a4d69;
            position: static;
        }
        
        .back-to-accounts.btn-icon-only .back-text {
            display: none;
        }
        
        .back-to-accounts.btn-icon-only::before {
            display: none;
        }
        
        /* Hover especial para o botão em forma de ícone */
        .back-to-accounts.btn-icon-only:hover {
            background-color: rgba(75, 134, 180, 0.25);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }
        
        /* Efeito de clique */
        .back-to-accounts.btn-icon-only:active {
            transform: translateY(1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Garantir visibilidade quando o menu está aberto */
        body.menu-active .back-to-accounts.btn-icon-only {
            opacity: 1;
            pointer-events: auto;
            z-index: 1002; /* Acima do overlay */
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
                transition: all 0.3s ease;
            }
            
            .page-header-container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .stock-header {
                order: 1;
                padding: 1rem 1.5rem;
                min-width: 200px;
                width: 100%;
            }
            
            .back-to-accounts:not(.btn-icon-only) {
                order: 2;
                margin-top: 15px;
                margin-bottom: 15px;
                justify-content: flex-start;
                padding: 0.6rem 1rem;
                width: auto;
                display: inline-flex;
                align-self: flex-start;
            }
            
            body.menu-active .back-to-accounts {
                opacity: 0.2;
                pointer-events: none;
            }
            
            .d-flex.justify-content-end {
                justify-content: center !important;
            }
            
            .btn-action {
                min-width: 120px;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            
            .stock-header {
                padding: 1rem;
            }
            
            .stock-title h4 {
                font-size: 1.2rem;
            }
            
            .stock-title i {
                font-size: 1.5rem;
                padding: 8px;
            }
            
            .detail-row {
                padding: 1rem 0;
            }
            
            .detail-label {
                font-size: 0.8rem;
                margin-bottom: 0.2rem;
            }
            
            .detail-value {
                font-size: 0.9rem;
            }
            
            .btn-action {
                padding: 0.4rem 1rem;
                font-size: 0.9rem;
                margin-bottom: 0.5rem;
                width: 100%;
                justify-content: center;
            }
            
            .d-flex.justify-content-end {
                justify-content: center !important;
                width: 100%;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .row.detail-row .col-md-6:not(:last-child) {
                margin-bottom: 1rem;
            }
            
            .row.justify-content-center.align-items-center {
                min-height: auto !important;
                margin-top: 1rem;
            }
        }

        /* Estilo para conteúdo principal */
        .main-content {
            margin-left: 300px;
            width: calc(100% - 300px);
            transition: all 0.3s ease;
        }

        /* Media queries aprimoradas para melhorar responsividade */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 250px;
                width: calc(100% - 250px);
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            .detail-value {
                font-size: 1rem;
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
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            .sidebar-toggle {
                display: flex !important;
            }
            
            .page-header-container {
                flex-direction: column;
                align-items: flex-start;
                margin-bottom: 1.5rem;
            }
            
            .stock-header {
                width: 100%;
                padding: 1.2rem 1.5rem;
            }
            
            .container-fluid {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            
            .row.justify-content-center {
                margin-left: -0.5rem;
                margin-right: -0.5rem;
            }
            
            .col-md-8 {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            
            .card {
                margin-bottom: 1rem;
            }
            
            .back-to-accounts:not(.btn-icon-only) {
                margin-top: 0.5rem;
                margin-bottom: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            
            .stock-header {
                padding: 1rem;
            }
            
            .stock-title h4 {
                font-size: 1.2rem;
            }
            
            .stock-title i {
                font-size: 1.5rem;
                padding: 8px;
            }
            
            .detail-row {
                padding: 1rem 0;
            }
            
            .detail-label {
                font-size: 0.8rem;
                margin-bottom: 0.2rem;
            }
            
            .detail-value {
                font-size: 0.9rem;
            }
            
            .btn-action {
                padding: 0.4rem 1rem;
                font-size: 0.9rem;
                margin-bottom: 0.5rem;
                width: 100%;
                justify-content: center;
            }
            
            .d-flex.justify-content-end {
                justify-content: center !important;
                width: 100%;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .row.detail-row .col-md-6:not(:last-child) {
                margin-bottom: 1rem;
            }
            
            .row.justify-content-center.align-items-center {
                min-height: auto !important;
                margin-top: 1rem;
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
            <div class="container-fluid p-4" style="padding-top: 3rem !important;">
                <!-- Área do cabeçalho com navegação -->
                <div class="page-header-container">
                    <!-- Título da página -->
                    <div class="stock-header">
                        <div class="stock-title">
                            <i class="bi bi-person-circle"></i>
                            <h4>Detalhes da Conta</h4>
                        </div>
                    </div>
                    
                    <!-- Botão de voltar para contas -->
                    <a href="contas.php" class="back-to-accounts">
                        <span class="icon-container"><i class="bi bi-arrow-left back-icon"></i></span>
                        <span class="back-text">Voltar para Contas</span>
                    </a>
                </div>

                <!-- Conteúdo da Página -->
                <div class="row justify-content-center align-items-center" style="min-height: 70vh;">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-body p-4">
                                <div class="row detail-row">
                                    <div class="col-md-6 col-12 mb-md-0 mb-3">
                                        <div class="detail-label">ID</div>
                                        <div class="detail-value"><?php echo $conta['id']; ?></div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="detail-label">Nome Completo</div>
                                        <div class="detail-value"><?php echo $conta['nome_completo']; ?></div>
                                    </div>
                                </div>

                                <div class="row detail-row">
                                    <div class="col-md-6 col-12 mb-md-0 mb-3">
                                        <div class="detail-label">Email Institucional</div>
                                        <div class="detail-value"><?php echo $conta['email_institucional']; ?></div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="detail-label">Número de Processo</div>
                                        <div class="detail-value"><?php echo $conta['numero_processo']; ?></div>
                                    </div>
                                </div>

                                <?php if ($tipo === 'tecnico'): ?>
                                <div class="row detail-row">
                                    <div class="col-md-6 col-12">
                                        <div class="detail-label">Tipo de Conta</div>
                                        <div class="detail-value">Suporte Técnico</div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="row detail-row">
                                    <div class="col-md-6 col-12 mb-md-0 mb-3">
                                        <div class="detail-label">Cargo</div>
                                        <div class="detail-value"><?php echo ucfirst($conta['cargo']); ?></div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="detail-label">Tipo de Conta</div>
                                        <div class="detail-value">Comunidade Escolar</div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="d-flex justify-content-end flex-wrap mt-4 gap-2">
                                    <button class="btn btn-primary btn-action" onclick="editarConta('<?php echo $tipo; ?>', <?php echo $id; ?>)">
                                        <i class="bi bi-pencil"></i>
                                        Editar
                                    </button>
                                    <button class="btn btn-danger btn-action" onclick="apagarConta('<?php echo $tipo; ?>', <?php echo $id; ?>)">
                                        <i class="bi bi-trash"></i>
                                        Apagar
                                    </button>
                                </div>
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

    <!-- Bootstrap JS -->
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
            // Ajustar layout baseado no tamanho da tela inicial
            const mainContent = document.querySelector('.main-content');
            if (window.innerWidth <= 768) {
                mainContent.style.marginLeft = '0';
                mainContent.style.width = '100%';
                
                // Ajustar botões em telas pequenas
                const actionButtons = document.querySelectorAll('.btn-action');
                if (window.innerWidth <= 576) {
                    actionButtons.forEach(btn => {
                        btn.style.width = '100%';
                        btn.style.justifyContent = 'center';
                        btn.style.marginBottom = '0.5rem';
                    });
                }
            } else if (window.innerWidth <= 992) {
                mainContent.style.marginLeft = '250px';
                mainContent.style.width = 'calc(100% - 250px)';
            } else {
                mainContent.style.marginLeft = '300px';
                mainContent.style.width = 'calc(100% - 300px)';
            }
            
            // Função para ajustar o botão "Voltar para Contas" em telas pequenas
            function adjustBackButton() {
                const backButton = document.querySelector('.back-to-accounts');
                if (window.innerWidth <= 650) {
                    backButton.setAttribute('title', 'Voltar para Contas');
                    backButton.setAttribute('aria-label', 'Voltar para Contas');
                    backButton.classList.add('btn-icon-only');
                } else {
                    backButton.classList.remove('btn-icon-only');
                }
            }
            
            // Executar na carga inicial e sempre que a tela for redimensionada
            adjustBackButton();
            window.addEventListener('resize', adjustBackButton);
            
            // Ajustar o layout quando a tela é redimensionada
            window.addEventListener('resize', function() {
                // Ajustar o layout principal
                if (window.innerWidth <= 768) {
                    mainContent.style.marginLeft = '0';
                    mainContent.style.width = '100%';
                } else if (window.innerWidth <= 992) {
                    mainContent.style.marginLeft = '250px';
                    mainContent.style.width = 'calc(100% - 250px)';
                } else {
                    mainContent.style.marginLeft = '300px';
                    mainContent.style.width = 'calc(100% - 300px)';
                }
                
                // Ajustar botões em telas pequenas
                const actionButtons = document.querySelectorAll('.btn-action');
                if (window.innerWidth <= 576) {
                    actionButtons.forEach(btn => {
                        btn.style.width = '100%';
                        btn.style.justifyContent = 'center';
                        btn.style.marginBottom = '0.5rem';
                    });
                } else {
                    actionButtons.forEach(btn => {
                        btn.style.width = 'auto';
                        btn.style.marginBottom = '0';
                    });
                }
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
            
            // Verificar o tamanho da tela ao redimensionar e atualizar a interface
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    document.querySelector('.sidebar').classList.remove('active');
                    document.body.classList.remove('menu-active');
                    const overlay = document.querySelector('.sidebar-overlay');
                    if (overlay) overlay.remove();
                }
            });

            // Configurar o modal de confirmação
            const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            document.getElementById('btnConfirmDelete').addEventListener('click', function() {
                confirmarExclusao();
                deleteModal.hide();
            });
        });
        
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
                        },
                        callback: function() {
                            window.location.href = 'contas.php';
                        }
                    }).showToast();
                    
                    // Redirecionar após 1.5 segundos para dar tempo de ver a notificação
                    setTimeout(() => {
                        window.location.href = 'contas.php';
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