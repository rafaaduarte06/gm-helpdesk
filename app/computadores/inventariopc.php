<?php
// Habilitar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar a sessão antes de qualquer output
session_start();

// Verificar se o usuário está logado e é um técnico
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    header('Location: ../login.php');
    exit;
}

// Incluir arquivo de conexão com o banco de dados
require_once('../ligacao_base_dados.php');

// Verificar se a conexão foi estabelecida
if (!isset($pdo) || $pdo === null) {
    die("Erro: Não foi possível conectar ao banco de dados. Verifique o arquivo ligacao_base_dados.php");
}

// Buscar dados do usuário
try {
    $stmt = $pdo->prepare("SELECT * FROM suporte_tecnico WHERE id = ?");
    $stmt->execute([$_SESSION['userId']]);
    $usuario = $stmt->fetch();
} catch (Exception $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

// Incluir o sidebar
require_once('../includes/sidebar.php');
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventário Computadores - GM HelpDesk</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="funcoes/componentes.css">
    <style>
        /* Estilo de fundo gradiente como na página de login */
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .back-button {
            cursor: pointer;
            padding: 0.7rem 1.5rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            background-color: #fff;
            border: none;
            color: #2c3e50;
            text-decoration: none;
            font-weight: 500;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .back-button:hover {
            background: #2c3e50;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .back-button i {
            margin-right: 8px;
            font-size: 1.1rem;
        }

        .search-container {
            flex-grow: 1;
            max-width: 450px;
            width: 100%;
        }
        
        .search-input-group {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
            transition: all 0.3s ease;
            width: 100%;
            border: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
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
            flex-grow: 1;
            width: 100%;
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
            background-color: #0d6efd;
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
            background-color: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary i {
            margin-right: 8px;
        }

        .computer-box {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            margin: 0;
            padding: 22px;
            transition: all 0.3s ease;
            height: auto;
            display: flex;
            flex-direction: column;
            cursor: pointer;
            position: relative;
            border-left: 5px solid transparent;
            min-height: 180px;
            width: 100%;
        }

        .computer-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .computer-box.status-available {
            border-left-color: #198754;
            background-color: #ffffff;
        }

        .computer-box.status-maintenance {
            border-left-color: rgb(219, 164, 0); /* Amarelo dourado */
            background-color: #ffffff;
        }

        .computer-box.status-inuse {
            border-left-color: #0d6efd;
            background-color: #ffffff;
        }

        .computer-box.status-obsolete {
            border-left-color: #dc3545;
            background-color: #ffffff;
        }

        .computer-icon-container {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 50px;
            height: 50px;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin-right: 0;
        }

        .computer-box .pc-icon {
            font-size: 1.8rem;
            color: var(--primary-color);
        }

        .computer-content {
            display: flex;
            flex-direction: column;
            flex: 1;
            gap: 15px;
        }

        .computer-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 15px;
            width: 100%;
        }

        .computer-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin: 0;
            display: flex;
            align-items: center;
            flex-shrink: 0;
        }

        .computer-title strong {
            background-color: rgba(13, 110, 253, 0.05);
            padding: 6px 10px;
            border-radius: 8px;
            font-family: monospace;
            color: #0d6efd;
            font-size: 0.9rem;
            border: 1px solid rgba(13, 110, 253, 0.3);
            font-weight: 500;
            letter-spacing: 0.5px;
            box-shadow: 0 1px 3px rgba(13, 110, 253, 0.05);
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 1px;
            transition: all 0.3s ease;
        }
        
        .computer-box:hover .computer-title strong {
            background-color: rgba(13, 110, 253, 0.08);
            box-shadow: 0 2px 5px rgba(13, 110, 253, 0.1);
            transform: translateY(-1px);
        }

        .computer-title strong i {
            font-size: 1.5rem;
            color: #0d6efd;
            margin-bottom: 0;
        }

        .computer-title strong span {
            padding: 0;
        }

        .computer-body {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin-bottom: 5px;
        }

        .computer-specs {
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            margin-top: 10px;
            width: 100%;
            display: flex;
        }

        .computer-specs span {
            background-color: rgba(0, 0, 0, 0.02);
            padding: 5px 10px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            margin-right: 12px;
            margin-bottom: 12px;
            white-space: nowrap;
            border: 1px solid rgba(0, 0, 0, 0.08);
            transition: all 0.2s ease;
        }
        
        .computer-specs span strong {
            font-weight: 500;
        }

        .computer-specs span i {
            color: var(--primary-color);
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: inline-block;
            transform-origin: center center;
        }

        .computer-specs span:hover i {
            transform: rotate(-20deg) translateX(-3px);
            animation: iconFall 0.5s ease;
        }

        @keyframes iconFall {
            0% { transform: rotate(0) translateX(0); }
            50% { transform: rotate(-25deg) translateX(-4px); }
            100% { transform: rotate(-20deg) translateX(-3px); }
        }

        /* Estilos personalizados para os diferentes estados de componentes */
        .component-available {
            background-color: rgba(25, 135, 84, 0.05) !important;
            border: 1px solid #198754 !important;
        }

        .component-available i, .component-available strong {
            color: #198754 !important;
        }
        
        .component-available strong {
            font-weight: 500 !important;
        }

        .component-maintenance {
            background-color: rgba(219, 164, 0, 0.05) !important;
            border: 1px solid rgb(219, 164, 0) !important;
        }

        .component-maintenance i, .component-maintenance strong {
            color: rgb(219, 164, 0) !important;
        }
        
        .component-maintenance strong {
            font-weight: 500 !important;
        }

        .component-inuse {
            background-color: rgba(13, 110, 253, 0.05) !important;
            border: 1px solid #0d6efd !important;
        }

        .component-inuse i, .component-inuse strong {
            color: #0d6efd !important;
        }
        
        .component-inuse strong {
            font-weight: 500 !important;
        }

        .component-repair {
            background-color: rgba(220, 53, 69, 0.05) !important;
            border: 1px solid #dc3545 !important;
        }

        .component-repair i, .component-repair strong {
            color: #dc3545 !important;
        }
        
        .component-repair strong {
            font-weight: 500 !important;
        }

        .computer-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 2;
            margin-top: auto;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        /* Estilo para o texto de gestão PC */
        .management-text {
            font-weight: 600;
            color: #000 !important;
        }

        .management-text strong {
            color: #000 !important;
            font-weight: 700;
        }

        .computer-status {
            display: flex;
            align-items: center;
            font-size: 0.8rem;
            font-weight: 500;
            padding: 3px 10px;
            border-radius: 15px;
            background-color: #ffffff;
            border: 1px solid #eee;
            gap: 4px;
            margin-left: auto;
            margin-bottom: 3px; /* Ajustando o alinhamento vertical com a parte inferior da referência */
        }

        /* Estilos específicos para cada status */
        .status-available {
            color: #198754;
            font-weight: 500;
        }

        /* Estilo específico para o badge de status disponível */
        .computer-status.status-available {
            background-color: rgba(25, 135, 84, 0.05); /* Fundo verde muito claro */
            border: 1px solid rgba(25, 135, 84, 0.3); /* Borda verde suave */
        }

        .status-maintenance {
            color: rgb(219, 164, 0);
            font-weight: 500;
        }

        /* Estilo específico para o badge de status em manutenção */
        .computer-status.status-maintenance {
            background-color: rgba(219, 164, 0, 0.05); /* Fundo amarelo muito claro */
            border: 1px solid rgba(219, 164, 0, 0.3); /* Borda amarela suave */
        }

        .status-inuse {
            color: #0d6efd;
            font-weight: 500;
        }

        /* Estilo específico para o badge de status em uso */
        .computer-status.status-inuse {
            background-color: rgba(13, 110, 253, 0.05); /* Fundo azul muito claro */
            border: 1px solid rgba(13, 110, 253, 0.3); /* Borda azul suave */
        }

        .status-obsolete {
            color: #dc3545;
            font-weight: 500;
        }

        /* Estilo específico para o badge de status sem reparo */
        .computer-status.status-obsolete {
            background-color: rgba(220, 53, 69, 0.05); /* Fundo vermelho muito claro */
            border: 1px solid rgba(220, 53, 69, 0.3); /* Borda vermelha suave */
        }

        .computer-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
            position: relative;
            z-index: 3;  /* Higher z-index to ensure buttons are clickable */
        }

        .edit-btn, .delete-btn {
            background-color: transparent;
            border: none;
            transition: all 0.3s ease;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
        }

        .edit-btn {
            color: #0d6efd;
            font-size: 1.1rem;
        }

        .edit-btn:hover {
            background-color: rgba(253, 13, 13, 0.1);
            color: #0a58ca;
        }

        .delete-btn {
            background-color: rgba(220, 53, 69, 0.15);
            border: 1px solid #dc3545;
            transition: all 0.3s ease;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            color: #dc3545;
            font-size: 1rem;
            width: 32px;
            height: 32px;
            position: relative;
            z-index: 5; /* Ensure button is above stretched-link */
        }
        
        .delete-btn:hover {
            background-color: rgba(220, 53, 69, 0.25);
            color: #bd2130;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(220, 53, 69, 0.2);
        }

        .row {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            padding: 0;
            margin: 0 auto;
            max-width: 100%;
        }

        .computer-box-container {
            width: 100%;
            padding: 0;
            margin-bottom: 25px;
        }

        .computer-box {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            margin: 0;
            padding: 22px;
            transition: all 0.3s ease;
            height: auto;
            display: flex;
            flex-direction: column;
            cursor: pointer;
            position: relative;
            border-left: 5px solid transparent;
            min-height: 180px;
            width: 100%;
        }
        
        @media (max-width: 768px) {
            .computer-box {
                padding: 20px;
            }
            
            .computer-box-container {
                margin-bottom: 20px;
            }
        }
        
        @media (max-width: 576px) {
            .computer-box-container {
                margin-bottom: 15px;
            }
        }

        .computer-specs {
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
            margin-top: 10px;
            width: 100%;
            display: flex;
        }

        .computer-specs span {
            background-color: rgba(0, 0, 0, 0.02);
            padding: 5px 10px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            margin-right: 12px;
            margin-bottom: 12px;
            white-space: nowrap;
            border: 1px solid rgba(0, 0, 0, 0.08);
            transition: all 0.2s ease;
        }

        /* Estilo do cabeçalho da página */
        .stock-header {
            background: linear-gradient(135deg, #2a4d69, rgb(36, 110, 167));
            color: white;
            padding: 1.8rem;
            border-radius: 15px;
            margin: 0 0 2rem 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            transform: translateY(0);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 100%;
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

        .stock-header .d-flex.gap-3.align-items-center {
            display: flex;
            align-items: center;
            gap: 15px;
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
            
            .stock-header {
                padding: 1.5rem;
                flex-direction: column;
                align-items: center;
            }
            
            .d-flex.gap-3.align-items-center {
                width: 100%;
                margin-top: 15px;
                justify-content: center;
            }
            
            .search-container {
                max-width: 65%;
                flex-basis: 65%;
                width: 65%;
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
            
            .stock-header {
                padding: 1.2rem;
                margin: 0 0 1.5rem 0;
                align-items: center;
            }
            
            .stock-title {
                margin-bottom: 15px;
                width: 100%;
                justify-content: center;
                display: flex;
            }
            
            .d-flex.gap-3.align-items-center {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
                width: 100%;
                gap: 15px !important;
            }
            
            .search-container {
                max-width: 65%;
                flex-basis: 65%;
                width: 65%;
                min-width: 220px;
            }
            
            .btn-primary {
                min-width: 120px;
            }
            
            .row {
                display: flex;
                flex-direction: column;
                padding: 0;
                margin: 0 auto;
                width: 100%;
                gap: 20px;
            }
            
            .computer-box-container {
                width: 100%;
                padding: 0;
                margin-bottom: 15px;
            }
            
            .computer-box {
                min-height: auto;
                padding: 20px;
                margin-bottom: 15px;
                width: 100%;
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
            
            .container-fluid {
                padding: 3rem 1rem 1rem 1rem !important;
            }
            
            .search-container {
                max-width: 100%;
                flex-basis: 100%;
                width: 100%; 
                min-width: 100%;
                margin: 0 0 15px 0;
            }
            
            .btn-primary {
                flex-basis: 100%;
                width: 100%;
                justify-content: center;
                margin: 0;
                height: 50px;
                font-size: 1rem;
                display: flex !important;
                align-items: center;
                visibility: visible !important;
                opacity: 1 !important;
            }
            
            .btn-primary i {
                margin-right: 8px;
                display: inline-block;
            }
            
            #btnAdicionarComputador {
                display: flex !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
            
            .row {
                gap: 15px;
            }
            
            .computer-box-container {
                padding: 0;
                margin-bottom: 15px;
            }
        }

        @media (max-width: 375px) {
            .container-fluid {
                padding: 1.5rem 15px !important;
            }
            
            .computer-box {
                padding: 15px;
            }
            
            .stock-header {
                padding: 1.2rem 15px !important;
            }
            
            .computer-specs span {
                font-size: 0.8rem;
                padding: 5px 8px;
                margin-right: 4px;
                margin-bottom: 4px;
            }
            
            .computer-title strong {
                padding: 4px 8px;
                font-size: 0.85rem;
            }
            
            .computer-specs {
                gap: 6px;
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

        /* Badge para componentes sem reparo */
        .sem-reparo-badge {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 13px;
            border: 1px solid rgba(220, 53, 69, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1.2;
            text-align: center;
        }
    </style>
</head>
<body>
    <button class="sidebar-toggle btn" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>

    <div class="d-flex">
        <!-- Sidebar -->
        <?php renderSidebar('inventariopc', $_SESSION['userType'], $usuario['nome_completo'], $usuario['foto_perfil']); ?>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <div class="container-fluid p-4">
                <!-- Cabeçalho -->
                <div class="stock-header">
                    <div class="stock-title">
                        <i class="bi bi-pc-display"></i>
                        <h4>Inventário Computadores</h4>
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
                                       placeholder="Pesquisar computador...">
                            </div>
                        </div>
                        <button class="btn btn-primary" id="btnAdicionarComputador">
                            <i class="bi bi-plus-circle-fill"></i> Adicionar
                        </button>
                    </div>
                </div>

                <!-- Boxes de Computadores -->
                <div class="row">
                    <?php
                    // Buscar todos os computadores da base de dados
                    $sql = "SELECT c.*
                            FROM computadores c 
                            ORDER BY c.COMP_REF";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    $computadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Função para determinar a classe de cor com base no estado do componente
                    function getComponentColorClass($estado) {
                        switch ($estado) {
                            case '1': // Disponível
                                return 'component-available';
                            case '3': // Em Manutenção
                                return 'component-maintenance';
                            case '2': // Em Uso
                                return 'component-inuse';
                            case '4': // Sem Reparo
                            case '9': // Sem Reparo (alternativo)
                                return 'component-repair';
                            default:
                                return '';
                        }
                    }
                    
                    // Função para obter texto do estado
                    function getEstadoText($estado) {
                        switch ($estado) {
                            case '1': return 'Disponível';
                            case '3': return 'Em Manutenção';
                            case '2': return 'Em Uso';
                            case '4': return 'Sem Reparo';
                            default: return 'Desconhecido';
                        }
                    }

                    foreach ($computadores as $computador) {
                        // Determinar a classe de status baseada no estado
                        $statusClass = '';
                        $statusText = getEstadoText($computador['COMP_ESTADO']);
                        switch ($computador['COMP_ESTADO']) {
                            case '1': // Disponível
                                $statusClass = 'status-available';
                                break;
                            case '3': // Manutenção
                                $statusClass = 'status-maintenance';
                                break;
                            case '2': // Em uso
                                $statusClass = 'status-inuse';
                                break;
                            case '4': // Sem Reparo
                            case '9': // Sem Reparo (alternativo)
                                $statusClass = 'status-obsolete';
                                break;
                        }

                        // Buscar informações dos componentes principais
                        $rato = '';
                        $ratoEstado = '';
                        if (!empty($computador['COMP_RATO'])) {
                            $stmt = $pdo->prepare("SELECT RATOS_REF, RATOS_ESTADO FROM ratos WHERE RATOS_ID = ?");
                            $stmt->execute([$computador['COMP_RATO']]);
                            $ratoData = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($ratoData) {
                                $rato = $ratoData['RATOS_REF'];
                                $ratoEstado = $ratoData['RATOS_ESTADO'];
                            }
                        }

                        $teclado = '';
                        $tecladoEstado = '';
                        if (!empty($computador['COMP_TECLADO'])) {
                            $stmt = $pdo->prepare("SELECT TECLADO_REF, TECLADO_ESTADO FROM teclados WHERE TECLADO_ID = ?");
                            $stmt->execute([$computador['COMP_TECLADO']]);
                            $tecladoData = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($tecladoData) {
                                $teclado = $tecladoData['TECLADO_REF'];
                                $tecladoEstado = $tecladoData['TECLADO_ESTADO'];
                            }
                        }

                        $monitor = '';
                        $monitorEstado = '';
                        if (!empty($computador['COMP_MONITOR'])) {
                            $stmt = $pdo->prepare("SELECT MONITOR_REF, MONITOR_ESTADO FROM monitor WHERE MONITOR_ID = ?");
                            $stmt->execute([$computador['COMP_MONITOR']]);
                            $monitorData = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($monitorData) {
                                $monitor = $monitorData['MONITOR_REF'];
                                $monitorEstado = $monitorData['MONITOR_ESTADO'];
                            }
                        }
                        ?>
                    <div class="computer-box-container">
                        <div class="computer-box <?php echo $statusClass; ?>" data-id="<?php echo $computador['COMP_ID']; ?>">
                            <div class="computer-content">
                                <div class="computer-header">
                                    <h5 class="computer-title">
                                        <strong>
                                <i class="bi bi-pc-display"></i>
                                            <span><?php echo htmlspecialchars($computador['COMP_REF']); ?></span>
                                        </strong>
                                    </h5>
                                    <div class="computer-status <?php echo $statusClass; ?>">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.6rem;"></i> 
                                        <?php echo $statusText; ?>
                            </div>
                                </div>
                                <div class="computer-body">
                            <div class="computer-specs">
                                        <?php if ($rato): ?>
                                            <span class="<?php echo getComponentColorClass($ratoEstado); ?>">
                                                <i class="bi bi-mouse"></i> <strong><?php echo htmlspecialchars($rato); ?></strong>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($teclado): ?>
                                            <span class="<?php echo getComponentColorClass($tecladoEstado); ?>">
                                                <i class="bi bi-keyboard"></i> <strong><?php echo htmlspecialchars($teclado); ?></strong>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($monitor): ?>
                                            <span class="<?php echo getComponentColorClass($monitorEstado); ?>">
                                                <i class="bi bi-display"></i> <strong><?php echo htmlspecialchars($monitor); ?></strong>
                                            </span>
                                        <?php endif; ?>
                            </div>
                                </div>
                                <div class="computer-footer">
                                    <div class="management-text"><strong>Gestão do Computador <?php echo htmlspecialchars($computador['COMP_REF']); ?></strong></div>
                                <div class="computer-actions">
                                    <?php if ($computador['COMP_ESTADO'] == '4' || $computador['COMP_ESTADO'] == '9'): // Sem Reparo ?>
                                        <span class="sem-reparo-badge">Sem Reparo</span>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-danger delete-btn" title="Apagar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de confirmação para exclusão de computador -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p>Tem a certeza que deseja apagar este computador?</p>
                    <p class="text-muted">Os componentes associados serão mantidos no inventário e ficarão disponíveis para outro computador.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmDelete">
                        <i class="bi bi-trash me-2"></i>Apagar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Carregar dados iniciais
        document.addEventListener('DOMContentLoaded', function() {
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
            
            // Função que permite o toggle do sidebar
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
            
            // Adicionar funcionalidade ao botão de adicionar computador
            document.getElementById('btnAdicionarComputador').addEventListener('click', function() {
                // Carregar o modal de adicionar computador
                carregarModal();
            });
            
            // Usar delegação de eventos para os botões de deletar
            document.querySelector('.main-content').addEventListener('click', function(e) {
                // Verificar se o clique foi em um botão de deletar ou em um dos seus elementos filhos
                const deleteBtn = e.target.closest('.delete-btn');
                if (deleteBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const computerId = deleteBtn.closest('.computer-box').getAttribute('data-id');
                    confirmarExclusaoComputador(computerId);
                }
            });
            
            // Botão de pesquisa
            document.getElementById('searchInput').addEventListener('input', function(e) {
                pesquisarComputadores(e.target.value);
            });
            
            // Adicionar a classe de clique para o box do computador
            // Ativando o clique em toda a área do computador
            document.querySelectorAll('.computer-box').forEach(computerBox => {
                const componenteId = computerBox.getAttribute('data-id');
                const linkDetalhesPc = document.createElement('a');
                
                // Corrigindo o URL para apontar para detalhespc.php com o caminho completo
                linkDetalhesPc.href = 'detalhespc.php?id=' + componenteId;
                
                linkDetalhesPc.className = 'stretched-link';
                linkDetalhesPc.setAttribute('aria-label', 'Ver detalhes do computador');
                computerBox.appendChild(linkDetalhesPc);
                
                const divComputerActions = computerBox.querySelector('.component-actions');
                if (divComputerActions) {
                    divComputerActions.style.zIndex = '2';
                }
            });

            // Adicionar evento para limpar o modal quando ele for fechado
            document.addEventListener('hidden.bs.modal', function (event) {
                if (event.target.id === 'modalAdicionarComputador') {
                    const modalElement = document.getElementById('modalAdicionarComputador');
                    if (modalElement) {
                        modalElement.remove();
                    }
                    // Remover backdrop manualmente se ainda existir
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                    // Remover classes do body
                    document.body.classList.remove('modal-open');
                    document.body.classList.remove('menu-active');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }
            });
        });

        // Função para carregar estados no select
        function carregarEstados(selectId = 'comp_estado', estadoSelecionado = null) {
            fetch('funcoes/carregar_componentes.php?tipo=estado')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const select = document.getElementById(selectId);
                        if (select) {
                            select.innerHTML = '<option value="">Selecione...</option>';
                            
                            data.componentes.forEach(estado => {
                                // Não incluir o estado "Em Uso" (2) nas opções
                                if (estado.ID_ESTADO != '2') {
                                    const option = document.createElement('option');
                                    option.value = estado.ID_ESTADO;
                                    option.textContent = estado.ESTADO;
                                    
                                    if (estadoSelecionado && estado.ID_ESTADO == estadoSelecionado) {
                                        option.selected = true;
                                    }
                                    
                                    select.appendChild(option);
                                }
                            });
                        }
                    } else {
                        console.error('Erro ao carregar estados:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar estados:', error);
                });
        }
        
        // Função para carregar componentes no select (formulário de adição)
        function carregarComponentes() {
            const componentes = [
                { id: 'comp_rato', tipo: 'rato', nome: 'RATO' },
                { id: 'comp_teclado', tipo: 'teclado', nome: 'TECLADO' },
                { id: 'comp_monitor', tipo: 'monitor', nome: 'MONITOR' },
                { id: 'comp_pm', tipo: 'placamae', nome: 'MOTHERBOARD' },
                { id: 'comp_proc', tipo: 'processador', nome: 'PROCESSADOR' },
                { id: 'comp_cooler', tipo: 'cooler', nome: 'COOLER' },
                { id: 'comp_pg', tipo: 'placagrafica', nome: 'PLACA GRÁFICA' },
                { id: 'comp_disco', tipo: 'disco', nome: 'DISCO' },
                { id: 'comp_fonte', tipo: 'fonte', nome: 'FONTE' },
                { id: 'comp_memoriar', tipo: 'memoriaram', nome: 'MEMÓRIA RAM' },
                { id: 'comp_drivedvd', tipo: 'drivedvd', nome: 'DRIVE DVD' },
                { id: 'comp_pr', tipo: 'placaderede', nome: 'PLACA DE REDE' },
                { id: 'comp_ventoinha', tipo: 'ventoinha', nome: 'VENTOINHA' },
                { id: 'comp_caixa', tipo: 'caixa', nome: 'CAIXA' }
            ];

            componentes.forEach(comp => {
                fetch(`funcoes/carregar_componentes.php?tipo=${comp.tipo}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const select = document.getElementById(comp.id);
                            if (select) {
                                select.innerHTML = '<option value="">Nenhum</option>';
                                
                                data.componentes.forEach(componente => {
                                    // Apenas componentes disponíveis podem ser adicionados
                                    if (componente.estado == '1') {
                                        const option = document.createElement('option');
                                        option.value = componente.id;
                                        option.textContent = `${comp.nome} ${componente.ref}`;
                                        select.appendChild(option);
                                    }
                                });
                            }
                        } else {
                            console.error(`Erro ao carregar componentes do tipo ${comp.tipo}:`, data.message);
                        }
                    })
                    .catch(error => {
                        console.error(`Erro ao carregar componentes do tipo ${comp.tipo}:`, error);
                    });
            });
        }

        // Função para carregar o modal de adicionar computador
        function carregarModal() {
            fetch('funcoes/adicionar_computador.php')
                .then(response => response.text())
                .then(html => {
                    // Remover o modal existente se houver
                    const modalExistente = document.getElementById('modalAdicionarComputador');
                    if (modalExistente) {
                        modalExistente.remove();
                    }
                    // Remover backdrop se houver
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                    // Remover classes do body
                    document.body.classList.remove('modal-open');
                    document.body.classList.remove('menu-active');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                    
                    // Adicionar o novo modal ao body
                    document.body.insertAdjacentHTML('beforeend', html);
                    
                    // Preparar os scripts de bootstrap
                    const bootstrapScript = document.createElement('script');
                    if (!document.querySelector('script[src*="bootstrap.bundle.min.js"]')) {
                        bootstrapScript.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js';
                        document.body.appendChild(bootstrapScript);
                    }
                    
                    // Carregar os estados e componentes após o modal estar no DOM
                    const modal = document.getElementById('modalAdicionarComputador');
                    if (modal) {
                        carregarEstados();
                        carregarComponentes();
                        
                        // Configurar evento de salvar
                        const btnSalvar = modal.querySelector('#btnSalvarComputador');
                        if (btnSalvar) {
                            btnSalvar.addEventListener('click', salvarComputador);
                        }
                        
                        // Garantir que o modal se ajuste corretamente em dispositivos móveis
                        if (window.innerWidth <= 576) {
                            modal.querySelector('.modal-dialog').style.margin = '0.5rem auto';
                            modal.querySelector('.modal-content').style.maxHeight = 'calc(100vh - 1rem)';
                            modal.querySelector('.modal-body').style.maxHeight = 'calc(100vh - 10rem)';
                            modal.querySelector('.modal-body').style.overflowY = 'auto';
                        }
                        
                        // Inicializar o modal Bootstrap
                        const modalBootstrap = new bootstrap.Modal(modal);
                        modalBootstrap.show();
                        
                        // Verificar se o título está visível após o modal abrir
                        modal.addEventListener('shown.bs.modal', function() {
                            const title = modal.querySelector('.modal-title');
                            if (title) {
                                title.style.display = 'block';
                                title.style.visibility = 'visible';
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar modal:', error);
                    alert('Erro ao carregar o modal. Por favor, tente novamente.');
                });
        }
        
        // Função para carregar o modal de editar computador
        function carregarModalEditarComputador(computerId) {
            // Carregar o conteúdo do modal via AJAX
            fetch('funcoes/atualizar_computador.php')
                .then(response => response.text())
                .then(html => {
                    // Criar elemento temporário para inserir o HTML
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    
                    // Verificar se o modal já existe no DOM
                    let modalElement = document.getElementById('modalEditarComputador');
                    if (!modalElement) {
                        // Adicionar o modal ao DOM
                        document.body.appendChild(tempDiv.querySelector('#modalEditarComputador'));
                        modalElement = document.getElementById('modalEditarComputador');
                    }
                    
                    // Inicializar o modal Bootstrap
                    const modalBS = new bootstrap.Modal(modalElement);
                    
                    // Carregar dados do computador
                    fetch(`funcoes/obter_computador.php?id=${computerId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                const computador = data.computador;
                                
                                // Preencher campos do formulário
                                document.getElementById('edit_comp_id').value = computador.id;
                                document.getElementById('edit_comp_ref').value = computador.ref;
                                
                                // Carregar estados para o select
                                carregarEstados('edit_comp_estado', computador.estado.id);
                                
                                // Carregar componentes disponíveis
                                const componentes = computador.componentes;
                                
                                // Arrays para armazenar componentes atuais
                                const componentesAtuais = {
                                    rato: componentes.rato ? componentes.rato.id : null,
                                    teclado: componentes.teclado ? componentes.teclado.id : null,
                                    monitor: componentes.monitor ? componentes.monitor.id : null,
                                    placamae: componentes.pm ? componentes.pm.id : null,
                                    processador: componentes.proc ? componentes.proc.id : null,
                                    cooler: componentes.cooler ? componentes.cooler.id : null,
                                    placagrafica: componentes.pg ? componentes.pg.id : null,
                                    disco: componentes.disco ? componentes.disco.id : null,
                                    fonte: componentes.fonte ? componentes.fonte.id : null,
                                    memoriaram: componentes.memoriar ? componentes.memoriar.id : null,
                                    drivedvd: componentes.drivedvd ? componentes.drivedvd.id : null,
                                    placaderede: componentes.pr ? componentes.pr.id : null,
                                    ventoinha: componentes.ventoinha ? componentes.ventoinha.id : null,
                                    caixa: componentes.caixa ? componentes.caixa.id : null
                                };
                                
                                // Carregar componentes disponíveis, incluindo o atual
                                carregarComponentesEdicao('edit_comp_rato', 'rato', componentesAtuais.rato);
                                carregarComponentesEdicao('edit_comp_teclado', 'teclado', componentesAtuais.teclado);
                                carregarComponentesEdicao('edit_comp_monitor', 'monitor', componentesAtuais.monitor);
                                carregarComponentesEdicao('edit_comp_pm', 'placamae', componentesAtuais.placamae);
                                carregarComponentesEdicao('edit_comp_proc', 'processador', componentesAtuais.processador);
                                carregarComponentesEdicao('edit_comp_cooler', 'cooler', componentesAtuais.cooler);
                                carregarComponentesEdicao('edit_comp_pg', 'placagrafica', componentesAtuais.placagrafica);
                                carregarComponentesEdicao('edit_comp_disco', 'disco', componentesAtuais.disco);
                                carregarComponentesEdicao('edit_comp_fonte', 'fonte', componentesAtuais.fonte);
                                carregarComponentesEdicao('edit_comp_memoriar', 'memoriaram', componentesAtuais.memoriaram);
                                carregarComponentesEdicao('edit_comp_drivedvd', 'drivedvd', componentesAtuais.drivedvd);
                                carregarComponentesEdicao('edit_comp_pr', 'placaderede', componentesAtuais.placaderede);
                                carregarComponentesEdicao('edit_comp_ventoinha', 'ventoinha', componentesAtuais.ventoinha);
                                carregarComponentesEdicao('edit_comp_caixa', 'caixa', componentesAtuais.caixa);
                                
                                // Configurar evento de salvar
                                document.getElementById('btnAtualizarComputador').addEventListener('click', atualizarComputador);
                                
                                // Abrir o modal
                                modalBS.show();
                            } else {
                                alert('Erro ao carregar dados do computador: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Erro ao carregar dados do computador:', error);
                            alert('Erro ao carregar dados do computador. Tente novamente.');
                        });
                })
                .catch(error => {
                    console.error('Erro ao carregar modal:', error);
                    alert('Erro ao carregar o formulário. Tente novamente.');
                });
        }
        
        // Função para confirmar e excluir um computador
        function confirmarExclusaoComputador(computerId) {
            // Armazenar o ID do computador para uso posterior
            document.getElementById('btnConfirmDelete').setAttribute('data-id', computerId);
            
            // Mostrar o modal de confirmação
            const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            modal.show();
            
            // Adicionar evento de clique ao botão de confirmação
            document.getElementById('btnConfirmDelete').onclick = function() {
                const compId = this.getAttribute('data-id');
                
                // Enviar solicitação para excluir o computador
                const formData = new FormData();
                formData.append('comp_id', compId);
                
                fetch('funcoes/excluir_computador.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Fechar o modal
                    modal.hide();
                    
                    if (data.status === 'success') {
                        // Recarregar a página para atualizar a lista
                        window.location.reload();
                    } else {
                        alert('Erro ao excluir computador: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro ao excluir computador:', error);
                    modal.hide();
                    alert('Erro ao excluir computador. Tente novamente.');
                });
            };
        }
        
        // Função para carregar componentes no select (formulário de edição)
        function carregarComponentesEdicao(selectId, tipoComponente, componenteAtual) {
            // Mapeamento de tipos para nomes
            const tiposParaNomes = {
                'rato': 'RATO',
                'teclado': 'TECLADO',
                'monitor': 'MONITOR',
                'placamae': 'MOTHERBOARD',
                'processador': 'PROCESSADOR',
                'cooler': 'COOLER',
                'placagrafica': 'PLACA GRÁFICA',
                'disco': 'DISCO',
                'fonte': 'FONTE',
                'memoriaram': 'MEMÓRIA RAM',
                'drivedvd': 'DRIVE DVD',
                'placaderede': 'PLACA DE REDE',
                'ventoinha': 'VENTOINHA',
                'caixa': 'CAIXA'
            };
            
            const nomeTipo = tiposParaNomes[tipoComponente] || tipoComponente.toUpperCase();
            
            fetch(`funcoes/carregar_componentes.php?tipo=${tipoComponente}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const select = document.getElementById(selectId);
                        select.innerHTML = '<option value="">Nenhum</option>';
                        
                        data.componentes.forEach(componente => {
                            // Incluir o componente atual e os disponíveis
                            if (componente.id == componenteAtual || componente.estado == '1') {
                                const option = document.createElement('option');
                                option.value = componente.id;
                                option.textContent = `${nomeTipo} ${componente.ref}`;
                                if (componente.id == componenteAtual) {
                                    option.selected = true;
                                }
                                select.appendChild(option);
                            }
                        });
                    } else {
                        console.error(`Erro ao carregar componentes: ${data.message}`);
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar componentes:', error);
                });
        }

        // Função para salvar um novo computador
        function salvarComputador() {
            // Obter o formulário
            const form = document.getElementById('formAdicionarComputador');
            
            // Usar validação HTML5 nativa
            if (!form.checkValidity()) {
                // Isso irá mostrar os avisos nativos (triângulo amarelo) nos campos obrigatórios
                form.reportValidity();
                return;
            }
            
            // Coletar os dados do formulário
            const formData = new FormData(form);
            
            // Enviar para o servidor via AJAX
            fetch('funcoes/adicionar_computador.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Fechar o modal
                    const modalBootstrap = bootstrap.Modal.getInstance(document.getElementById('modalAdicionarComputador'));
                    modalBootstrap.hide();
                    
                    // Recarregar a página para mostrar o novo computador sem mostrar mensagem
                    setTimeout(() => {
                    window.location.reload();
                    }, 100);
                } else {
                    // Exibir mensagem de erro apenas se for um erro diferente dos campos obrigatórios
                    if (data.message !== 'Referência e estado são obrigatórios') {
                        alert(`Erro ao adicionar computador: ${data.message}`);
                    } else {
                        // Indica visualmente quais campos estão faltando
                        if (!document.getElementById('comp_ref').value) {
                            document.getElementById('comp_ref').classList.add('is-invalid');
                        }
                        if (!document.getElementById('comp_estado').value) {
                            document.getElementById('comp_estado').classList.add('is-invalid');
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Erro ao adicionar computador:', error);
            });
        }

        // Função para atualizar um computador existente
        function atualizarComputador() {
            // Obter todos os valores do formulário
            const formData = new FormData();
            formData.append('comp_id', document.getElementById('edit_comp_id').value);
            formData.append('comp_ref', document.getElementById('edit_comp_ref').value);
            formData.append('comp_estado', document.getElementById('edit_comp_estado').value);
            
            // Adicionar componentes
            const componentes = [
                'rato', 'teclado', 'monitor', 'pm', 'proc', 'cooler', 'pg', 'disco',
                'fonte', 'memoriar', 'drivedvd', 'pr', 'ventoinha', 'caixa'
            ];
            
            componentes.forEach(comp => {
                const value = document.getElementById(`edit_comp_${comp}`).value;
                if (value) {
                    formData.append(`comp_${comp}`, value);
                }
            });
            
            // Enviar requisição para salvar
            fetch('funcoes/atualizar_computador.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Computador atualizado com sucesso!');
                    window.location.reload();
                } else {
                    alert('Erro ao atualizar computador: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro ao atualizar computador:', error);
                alert('Erro ao atualizar computador. Tente novamente.');
                });
        }

        // Função para pesquisar computadores
        function pesquisarComputadores(searchTerm) {
            searchTerm = searchTerm.toLowerCase();
            const computerBoxes = document.querySelectorAll('.computer-box-container');
            let hasVisibleComputers = false;
            
            computerBoxes.forEach(container => {
                const box = container.querySelector('.computer-box');
                const text = box.textContent.toLowerCase();
                const match = text.includes(searchTerm);
                container.style.display = match ? 'block' : 'none';
                
                if (match) {
                    hasVisibleComputers = true;
                }
            });
            
            // Mostrar uma mensagem se nenhum computador corresponder à pesquisa
            const noResultsMessage = document.getElementById('noResultsMessage');
            if (!hasVisibleComputers && searchTerm.length > 0) {
                if (!noResultsMessage) {
                    const message = document.createElement('div');
                    message.id = 'noResultsMessage';
                    message.className = 'alert alert-info mt-3';
                    message.textContent = 'Nenhum computador encontrado com esse termo.';
                    document.querySelector('.row').appendChild(message);
                }
            } else if (noResultsMessage) {
                noResultsMessage.remove();
            }
        }
    </script>
</body>
</html> 