<?php
session_start();
require_once('../../ligacao_base_dados.php');
require_once('../../includes/sidebar.php'); // Incluir o arquivo do sidebar

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
    // Buscar dados da conta
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
    <title>Editar Conta - GM HelpDesk</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/variables.css">
    <link rel="stylesheet" href="../../assets/css/custom.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
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

        /* Icon container específico para o botão do menu */
        .sidebar-toggle .icon-container {
            position: absolute;
            top: 0;
            left: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
        }

        /* Estilo específico para o ícone do menu */
        .sidebar-toggle i.bi-list {
            position: relative;
            font-size: 1.3rem;
            color: #2a4d69;
            text-align: center;
            margin: 0;
            padding: 0 0 2px 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            transform: translateY(3px);
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
            overflow: hidden;
            position: relative;
            display: none; /* Hide by default, will be shown in mobile view via JavaScript */
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

        /* Ajuste para adicionar espaço acima do cabeçalho principal */
        .main-content .container-fluid {
            padding-top: 15px;
        }

        /* Cabeçalho e navegação superior */
        .page-header-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 15px;
            position: relative;
        }

        /* Estilo do cabeçalho da página */
        .stock-header {
            background: linear-gradient(135deg, #2a4d69, rgb(36, 110, 167));
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: flex-start;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            transform: translateY(0);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: auto;
            min-width: 250px;
            margin: 0;
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
            margin-top: -8px;
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
                margin-top: -22px;
            }
        }

        /* Alinhamento específico para telas muito grandes */
        @media (min-width: 1200px) {
            .back-to-accounts {
                margin-top: -25px;
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
                margin-left: 0 !important;
                width: 100% !important;
                transition: all 0.3s ease;
                padding: 0 15px;
                overflow-x: hidden;
            }
            
            .page-header-container {
                flex-direction: column;
                align-items: flex-start;
                width: 100%;
            }
            
            .stock-header {
                order: 1;
                padding: 1rem 1.5rem;
                min-width: 200px;
                width: 100%;
                max-width: 100%;
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
            
            .d-grid .btn-primary {
                width: 100%;
                justify-content: center;
            }
            
            .card {
                width: 100%;
                margin-left: 0;
                margin-right: 0;
            }
            
            .container-fluid {
                padding-left: 10px;
                padding-right: 10px;
            }
            
            .row {
                margin-left: 0;
                margin-right: 0;
            }
            
            .col-md-8 {
                padding-left: 0;
                padding-right: 0;
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
            
            .card-body {
                padding: 1rem;
            }
            
            .btn-action {
                font-size: 0.85rem;
                padding: 0.35rem 1rem;
            }
            
            .back-to-accounts {
                padding: 0.5rem 0.9rem;
                font-size: 0.9rem;
            }
            
            .back-to-accounts .back-text {
                font-size: 0.85rem;
            }
            
            .form-label {
                font-size: 0.9rem;
            }
            
            .form-control, .form-select {
                padding: 0.5rem 0.8rem;
                font-size: 0.9rem;
            }
            
            .page-header-container {
                gap: 10px;
            }
            
            .stock-header {
                padding: 0.8rem 1.2rem;
            }
            
            .d-grid {
                display: flex;
                justify-content: center;
            }
            
            .d-grid .btn-primary {
                padding: 0.6rem 2rem;
                width: auto;
                min-width: 200px;
            }
            
            .page-header-container {
                margin-bottom: 2.5rem;
            }
        }

        /* Estilos do formulário */
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.65rem 1rem;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus, .form-select:focus {
            border-color: #4b86b4;
            box-shadow: 0 0 0 0.25rem rgba(75, 134, 180, 0.25);
            background-color: white;
        }

        .mb-3 {
            margin-bottom: 1.5rem !important;
        }

        /* Botões */
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            border: none;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            background: linear-gradient(135deg, #0b5ed7, #0a58ca);
        }

        .btn-outline-light {
            border: 2px solid white;
            color: white;
            font-weight: 500;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-outline-light:hover {
            background-color: white;
            color: #2a4d69;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-action {
            padding: 0.5rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }

        .btn-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .btn i {
            margin-right: 0.5rem;
        }

        /* Card de formulário */
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

        /* Estilo para conteúdo principal */
        .main-content {
            margin-left: 300px;
            width: calc(100% - 300px);
            transition: all 0.3s ease;
            padding-right: 15px;
            padding-left: 15px;
        }

        /* Input group styling for email domain */
        .input-group-text {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 500;
            border-left: 0;
        }
        
        .input-group .form-control:focus + .input-group-text {
            border-color: #86b7fe;
        }

        .sidebar-header, .sidebar .user-info, .sidebar .nav {
            position: relative;
            z-index: 10;
        }
    </style>
</head>
<body>
    <button class="sidebar-toggle btn" onclick="toggleSidebar()">
        <span class="icon-container"><i class="bi bi-list"></i></span>
    </button>

    <div class="d-flex">
        <!-- Sidebar -->
        <?php renderSidebar('contas', $_SESSION['userType'], $usuario['nome_completo'], $usuario['foto_perfil']); ?>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <div class="container-fluid py-4" style="padding-top: 4rem !important;">
                <!-- Área do cabeçalho com navegação -->
                <div class="page-header-container">
                    <!-- Título da página -->
                    <div class="stock-header">
                        <div class="stock-title">
                            <i class="bi bi-pencil-square"></i>
                            <h4>Editar Conta</h4>
                        </div>
                    </div>
                    
                    <!-- Botão de voltar para contas -->
                    <a href="contas.php" class="back-to-accounts">
                        <span class="icon-container"><i class="bi bi-arrow-left back-icon"></i></span>
                        <span class="back-text">Voltar para Contas</span>
                    </a>
                </div>

                <!-- Conteúdo da Página -->
                <div class="row justify-content-center" style="margin-top: 2rem;">
                    <div class="col-md-8 col-12">
                        <div class="card mb-4">
                            <div class="card-body p-4">
                                <form id="editForm" onsubmit="handleSubmit(event)">
                                    <input type="hidden" name="id" value="<?php echo $conta['id']; ?>">
                                    <input type="hidden" name="tipo" value="<?php echo $tipo; ?>">

                                    <div class="mb-3">
                                        <label class="form-label">Nome Completo</label>
                                        <input type="text" class="form-control" name="nome_completo" 
                                               value="<?php echo $conta['nome_completo']; ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Email Institucional</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="email_institucional" 
                                                value="<?php echo strstr($conta['email_institucional'], '@', true) ?: $conta['email_institucional']; ?>" required>
                                            <span class="input-group-text">@<?= htmlspecialchars(obterConfig('APP_EMAIL_DOMAIN')) ?></span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Número de Processo</label>
                                        <input type="text" class="form-control" name="numero_processo" 
                                               value="<?php echo $conta['numero_processo']; ?>" required>
                                    </div>

                                    <?php if ($tipo === 'tecnico'): ?>
                                        <!-- No additional fields for tecnico -->
                                    <?php else: ?>
                                        <div class="mb-3">
                                            <label class="form-label">Cargo</label>
                                            <select class="form-select" name="cargo" required>
                                                <option value="professor" <?php echo $conta['cargo'] === 'professor' ? 'selected' : ''; ?>>Professor</option>
                                                <option value="aluno" <?php echo $conta['cargo'] === 'aluno' ? 'selected' : ''; ?>>Aluno</option>
                                                <option value="funcionario" <?php echo $conta['cargo'] === 'funcionario' ? 'selected' : ''; ?>>Funcionário</option>
                                            </select>
                                        </div>
                                    <?php endif; ?>

                                    <div class="d-grid mt-4">
                                        <button type="submit" class="btn btn-primary btn-action">
                                            <i class="bi bi-check-lg me-2"></i>
                                            Salvar Alterações
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS and Toastify -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                    // Em dispositivos maiores, sempre esconde o botão
                    toggleButton.style.display = 'none';
                }
            }
            
            // Verificar na carga inicial e quando redimensionar
            checkToggleDisplay();
            window.addEventListener('resize', checkToggleDisplay);
            
            // Função para garantir que o layout esteja correto após o redimensionamento
            function handleResize() {
                if (window.innerWidth > 768) {
                    document.querySelector('.main-content').style.marginLeft = '300px';
                    document.querySelector('.main-content').style.width = 'calc(100% - 300px)';
                } else {
                    document.querySelector('.main-content').style.marginLeft = '0';
                    document.querySelector('.main-content').style.width = '100%';
                }
            }
            
            // Executar o verificador de tamanho quando a página carrega e quando redimensiona
            handleResize();
            window.addEventListener('resize', handleResize);
            
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
        });
        
        function handleSubmit(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            // Concatenate the email with domain
            const emailInput = formData.get('email_institucional');
            if (!emailInput.includes('@')) {
                formData.set('email_institucional', emailInput + <?= json_encode('@' . obterConfig('APP_EMAIL_DOMAIN')) ?>);
            }
            
            fetch('atualizar_conta.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Toastify({
                        text: "Conta atualizada com sucesso!",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                    
                    setTimeout(() => {
                        window.location.href = 'contas.php';
                    }, 1000);
                } else {
                    Toastify({
                        text: data.message || "Erro ao atualizar conta",
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