<?php
// Iniciar sessão e verificar autenticação
session_start();
// require_once('../config.php');
// require_once('../funcoes/conexao.php');
require_once '../../ligacao_base_dados.php';
// require_once('../funcoes/verificar_sessao.php');
require_once('funcoes/tickets_funcoes.php');

// Verificar se o usuário está logado - aceitar diferentes formatos de sessão
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['userId'])) {
    header('Location: ../../login.php');
    exit;
}

// Identificar qual formato está sendo usado
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : $_SESSION['userId'];
$usuario_tipo = isset($_SESSION['usuario_tipo']) ? $_SESSION['usuario_tipo'] : (isset($_SESSION['userType']) ? $_SESSION['userType'] : '');

// Guardar em formato padrão para uso consistente
$_SESSION['usuario_id'] = $usuario_id;
$_SESSION['usuario_tipo'] = $usuario_tipo;

// Verifica se o usuário é técnico ou administrador
if ($usuario_tipo !== 'tecnico' && $usuario_tipo !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

// Buscar dados necessários
try {
    $stmt = $pdo->prepare("SELECT * FROM suporte_tecnico WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();
    
    // Carregar as salas da base de dados
    $salas = obterSalas($pdo);
    
    // Carregar tipos de componentes
    $tipos_componentes = obterTiposComponentes();
    
    // Obter todos os departamentos para o filtro
    $departamentos = obterDepartamentos($pdo);
    
} catch (Exception $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

// Verificar se tem mensagem de erro ou sucesso
$mensagem_erro = isset($_GET['erro']) ? $_GET['erro'] : '';
$mensagem_sucesso = isset($_GET['sucesso']) ? $_GET['sucesso'] : '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Ticket</title>
    
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/variables.css">
    <link rel="stylesheet" href="../../assets/css/custom.css">
    <style>
        /* Estilo de fundo gradiente como na página de login */
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
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
        
        /* Estilo para botões responsivos */
        @media (max-width: 767px) {
            .w-md-auto {
                width: 100%;
                margin-bottom: 10px;
            }
            
            .d-flex.justify-content-end {
                flex-direction: column;
            }
            
            .me-2 {
                margin-right: 0 !important;
            }
        }
        
        @media (min-width: 768px) {
            .w-md-auto {
                width: auto;
            }
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

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
            border-radius: 15px 15px 0 0 !important;
            font-weight: 600;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            border-color: #4b86b4;
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
        
        .btn-secondary {
            background-color: #6c757d;
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }
        
        /* Form styling */
        .form-group label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 8px;
        }
        
        /* Alert styling */
        .alert {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        /* Form group spacing */
        .form-group {
            margin-bottom: 20px;
        }
        
        /* File input styling */
        .form-control-file {
            padding: 8px 0;
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
                align-items: center;
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
        }

        @media (max-width: 375px) {
            .container-fluid {
                padding: 1.5rem 15px !important;
            }
            
            .stock-header {
                padding: 1.2rem 15px !important;
            }
        }

        .file-upload-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            border: 2px dashed #dee2e6;
            transition: all 0.3s ease;
        }

        .file-upload-container:hover {
            border-color: #0d6efd;
            background-color: #f0f7ff;
        }

        .custom-file-upload {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .file-input {
            position: absolute;
            width: 0.1px;
            height: 0.1px;
            opacity: 0;
            overflow: hidden;
            z-index: -1;
        }

        .upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: white;
            color: #0d6efd;
            border: 1px solid #0d6efd;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .upload-label.btn-primary {
            background-color: #0d6efd !important;
            color: white !important;
            border-color: #0d6efd !important;
        }

        .selected-files {
            margin-top: 10px;
            padding: 10px;
            border-radius: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        
        #file-list {
            max-height: 200px;
            overflow-y: auto;
        }
        
        #file-list > .file-item {
            transition: all 0.2s;
            border-radius: 4px !important;
            border: 2px solid #4b86b4 !important;
            margin-bottom: 8px !important;
            padding: 12px !important;
            background-color: #f0f7ff !important;
        }
        
        .file-name {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: inline-block;
        }
        
        /* Assegurar que o conteúdo esteja visível mesmo se o nome do arquivo for longo */
        .file-item .d-flex.justify-content-between {
            flex-wrap: nowrap !important;
            width: 100% !important;
        }
        
        .file-item:last-child {
            margin-bottom: 0 !important;
        }
        
        #selected-files-container {
            padding: 15px !important;
            border: 2px solid #b8daff !important;
            background-color: #f0f7ff !important;
            border-radius: 6px !important;
            margin-top: 15px !important;
        }
        
        .remove-file {
            padding: 3px 7px !important;
            line-height: 1;
            background-color: #ff5c5c !important;
            border-color: #ff5c5c !important;
            color: white !important;
            visibility: visible !important;
            opacity: 1 !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            margin-left: 5px;
            border-radius: 50% !important;
            min-width: 25px;
            min-height: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 10;
        }
        
        .remove-file:hover {
            background-color: #ff3333 !important;
            border-color: #ff3333 !important;
            transform: scale(1.1);
            box-shadow: 0 3px 7px rgba(0,0,0,0.3);
        }
        
        .remove-file i {
            font-size: 0.9rem;
            visibility: visible !important;
            display: block !important;
            color: white !important;
        }
        
        #upload-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        #upload-btn.btn-primary {
            color: white !important;
        }
        
        /* Estilos para spinner durante envio */
        @keyframes spinner {
            to {transform: rotate(360deg);}
        }
        
        .spinner {
            display: inline-block;
            animation: spinner 1s linear infinite;
        }

        /* Novos estilos para o formulário de tickets */
        .card-header h5 {
            display: flex;
            align-items: center;
            font-weight: 600;
            color: #2a4d69;
        }
        
        .card-header h5 i {
            margin-right: 8px;
            color: #0d6efd;
        }
        
        .card-header {
            background-color: #f5f9ff !important;
            border-bottom: 1px solid #e1ecff !important;
        }
        
        .card.border-0.shadow-sm {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
            border-radius: 12px !important;
            overflow: hidden;
        }
        
        .card.border-0.shadow-sm:hover {
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.12) !important;
        }
        
        .form-label {
            font-weight: 500;
            color: #2a4d69;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .form-control {
            border: 1px solid #e1ecff;
            background-color: #fafcff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
            padding: 0.6rem 1rem;
        }
        
        .form-control:focus {
            background-color: #ffffff;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
            border-color: #a9c6ff;
        }
        
        .form-control::placeholder {
            color: #a9b7c6;
            font-size: 0.875rem;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #0d6efd, #0b5ed7);
            border: none;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, #0b5ed7, #094bac);
            box-shadow: 0 6px 15px rgba(13, 110, 253, 0.4);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: linear-gradient(to right, #6c757d, #5a6268);
            border: none;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(108, 117, 125, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(to right, #5a6268, #495057);
            box-shadow: 0 6px 15px rgba(108, 117, 125, 0.4);
            transform: translateY(-2px);
        }
        
        .file-upload-container {
            background-color: #f5f9ff;
            border-radius: 10px;
            padding: 25px;
            border: 2px dashed #c9d8f4;
            transition: all 0.3s ease;
        }
        
        .file-upload-container:hover {
            border-color: #0d6efd;
            background-color: #f0f7ff;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.1);
        }
        
        #upload-btn {
            padding: 0.6rem 1.5rem;
            background: linear-gradient(to right, #ffffff, #f5f9ff);
            border: 1px solid #c9d8f4;
            color: #0d6efd;
            font-weight: 600;
            border-radius: 8px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        #upload-btn:hover {
            background: linear-gradient(to right, #f5f9ff, #e9f0ff);
            border-color: #0d6efd;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
            transform: translateY(-2px);
        }
        
        #upload-btn.btn-primary {
            background: linear-gradient(to right, #0d6efd, #0b5ed7);
            border-color: #0d6efd;
            color: white !important;
        }
        
        #selected-files-container {
            background-color: #f0f7ff !important;
            border: 1px solid #c9d8f4 !important;
            border-radius: 8px !important;
            box-shadow: 0 2px 6px rgba(13, 110, 253, 0.08) !important;
        }
        
        #file-list > .file-item {
            background-color: #ffffff !important;
            border: 1px solid #c9d8f4 !important;
            border-radius: 6px !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.03) !important;
            transition: all 0.2s ease;
        }
        
        #file-list > .file-item:hover {
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08) !important;
            transform: translateY(-1px);
        }
        
        /* Estilos para os boxes de prioridade */
        .prioridade-container {
            display: flex;
            gap: 10px;
            margin-top: 0; /* Reduzido de 5px para 0 para subir os botões */
            width: 100%;
            margin-bottom: 0;
        }
        
        .prioridade-box {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 12px 5px; /* Aumentei de 10px para 12px */
            min-height: 45px; /* Aumentei de 42px para 45px */
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            /* Default styling with no color */
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
            color: #495057;
        }
        
        .prioridade-box:hover {
            transform: translateY(-1px); /* Reduced transform */
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
            background-color: #e9ecef;
        }
        
        /* Styling for when selected (checked state) */
        .btn-check:checked + .prioridade-baixa {
            border: 2px solid #28a745;
            background-color: rgba(40, 167, 69, 0.25);
            color: #28a745;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(40, 167, 69, 0.2);
        }
        
        .btn-check:checked + .prioridade-media {
            border: 2px solid #ffc107;
            background-color: rgba(255, 193, 7, 0.25);
            color: #d39e00;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(255, 193, 7, 0.2);
        }
        
        .btn-check:checked + .prioridade-alta {
            border: 2px solid #dc3545;
            background-color: rgba(220, 53, 69, 0.25);
            color: #dc3545;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(220, 53, 69, 0.2);
        }
        
        /* Estilo para quando selecionado */
        .btn-check:checked + .prioridade-baixa {
            background-color: rgba(40, 167, 69, 0.25);
            border-width: 3px;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.2);
        }
        
        .btn-check:checked + .prioridade-media {
            background-color: rgba(255, 193, 7, 0.25);
            border-width: 3px;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.2);
        }
        
        .btn-check:checked + .prioridade-alta {
            background-color: rgba(220, 53, 69, 0.25);
            border-width: 3px;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
        }
        
        /* Estilos para o modal de alerta personalizado */
        .custom-alert-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes scaleIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        .custom-alert-content {
            background: #ffffff;
            width: 100%;
            max-width: 400px;
            border-radius: 12px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: scaleIn 0.3s ease;
        }
        
        .custom-alert-header {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            background: #dc3545;
            color: white;
        }
        
        .custom-alert-header i {
            font-size: 1.6rem;
            margin-right: 10px;
            color: white;
        }
        
        .custom-alert-header h5 {
            margin: 0;
            font-weight: 600;
        }
        
        .custom-alert-body {
            padding: 20px;
            text-align: center;
        }
        
        .custom-alert-body p {
            margin: 0;
            font-size: 1.1rem;
            color: #dc3545;
            font-weight: 500;
        }
        
        .custom-alert-footer {
            display: flex;
            justify-content: center;
            padding: 0 20px 20px;
        }
        
        .custom-alert-footer button {
            min-width: 120px;
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        .custom-alert-footer button:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        
        /* Estilos para validação de formulário */
        .form-control.is-invalid {
            border-color: #dc3545 !important;
            background-image: none !important; /* Remove o ícone padrão do Bootstrap */
            padding-right: 0.75rem !important; /* Restaura o padding padrão */
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }
        
        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            display: none;
            margin-top: 0.25rem;
            font-weight: 500;
        }
        
        .is-invalid ~ .invalid-feedback {
            display: block;
        }
        
        /* Estilo para prioridade inválida */
        .prioridade-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }
        
        /* Mensagem de erro para prioridade */
        .prioridade-container.has-error::after {
            content: "Campo obrigatório";
            display: block;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            font-weight: 500;
        }
        
        /* Animação para os ícones de erro */
        @keyframes pulse {
            0% { transform: translateY(-50%) scale(1); }
            50% { transform: translateY(-50%) scale(1.2); }
            100% { transform: translateY(-50%) scale(1); }
        }
        
        .error-icon {
            animation: pulse 1s infinite;
        }
        
        .error-icon i {
            font-size: 1.2rem;
        }
        
        /* Estilo para mensagem de sucesso personalizada */
        .alert-success {
            background: linear-gradient(145deg, #d4edda, #c3e6cb);
            border: none;
            border-left: 5px solid #28a745;
            color: #155724;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            animation: slideInRight 0.5s ease, fadeOut 0.5s ease 5s forwards;
            position: relative;
            overflow: hidden;
        }
        
        .alert-success::before {
            content: "✓";
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #28a745;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            margin-right: 15px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .alert-success::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            width: 100%;
            background-color: #28a745;
            animation: timer 5s linear forwards;
        }
        
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; visibility: hidden; }
        }
        
        @keyframes timer {
            from { width: 100%; }
            to { width: 0%; }
        }
        
        .alert .close {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            font-size: 1.5rem;
            font-weight: 700;
            color: #155724;
            opacity: 0.5;
            transition: opacity 0.3s ease;
        }
        
        .alert .close:hover {
            opacity: 1;
        }

        /* Certifique-se de que o CSS dos ícones de erro está correto */
        .error-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            pointer-events: none; /* Para não interferir nos cliques */
            animation: pulse 1s infinite;
        }

        .error-icon i {
            font-size: 1.2rem;
            color: #dc3545;
        }

        @keyframes pulse {
            0% { transform: translateY(-50%) scale(1); }
            50% { transform: translateY(-50%) scale(1.2); }
            100% { transform: translateY(-50%) scale(1); }
        }

        /* Garantir que a mensagem de erro apareça */
        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            font-weight: 500;
        }

        .is-invalid ~ .invalid-feedback {
            display: block !important;
        }

        /* Melhorar a exibição do erro no campo */
        .form-control.is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
            background-image: none !important;
            padding-right: 2.25rem !important; /* Espaço para o ícone */
        }

        /* Estilos mais fortes para os campos com erro */
        .form-control.is-invalid {
            border: 2px solid #dc3545 !important;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
            background-image: none !important;
        }

        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-weight: 500;
        }

        .is-invalid ~ .invalid-feedback {
            display: block !important;
        }

        /* Ícone de erro bem visível */
        .error-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            pointer-events: none;
        }

        .error-icon i {
            font-size: 1.25rem;
            color: #dc3545;
            filter: drop-shadow(0 0 2px rgba(255,255,255,0.7));
        }

        /* Estilo para prioridade inválida - mais visível */
        .prioridade-invalid {
            border: 2px solid #dc3545 !important;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.25) !important;
        }

        /* Mensagem de erro para prioridade - mais visível */
        .prioridade-container.has-error::after {
            content: "Campo obrigatório";
            display: block;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 5px;
            font-weight: 700;
        }

        /* Estilo para os campos com erro - Muito mais visível */
        .form-control.is-invalid {
            border: 3px solid #dc3545 !important;
            background-color: #fff8f8 !important;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.3) !important;
        }

        /* Mensagem de erro personalizada */
        .campo-erro {
            display: block;
            color: #dc3545;
            font-weight: 600;
            background-color: rgba(220, 53, 69, 0.1);
            padding: 5px 12px;
            border-radius: 4px;
            margin-top: 5px;
            margin-bottom: 10px;
            border-left: 4px solid #dc3545;
        }

        /* Estilo para prioridade com erro */
        .prioridade-invalid {
            border: 3px solid #dc3545 !important;
            background-color: #fff8f8 !important;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.3) !important;
        }

        /* Mensagem de erro para container de prioridade */
        .prioridade-container.has-error::after {
            content: "Este campo é obrigatório";
            display: block;
            color: #dc3545;
            font-weight: 600;
            background-color: rgba(220, 53, 69, 0.1);
            padding: 5px 12px;
            border-radius: 4px;
            margin-top: 5px;
            margin-bottom: 10px;
            border-left: 4px solid #dc3545;
        }

        /* Botão de voltar para tickets */
        .back-to-tickets {
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
            opacity: 1;
            visibility: visible;
        }
        
        .back-to-tickets.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .back-to-tickets::before {
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

        .back-to-tickets:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .back-to-tickets:hover::before {
            opacity: 1;
        }

        .back-to-tickets .back-icon {
            margin-right: 10px;
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .back-to-tickets:hover .back-icon {
            transform: translateX(-4px);
        }

        .back-to-tickets .back-text {
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .back-to-tickets {
                padding: 0.6rem 1rem;
            }
            
            /* Hide the back button when menu is active */
            body.menu-active .back-to-tickets {
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }
        }
        
        @media (max-width: 375px) {
            .back-to-tickets .back-text {
                display: none;
            }
            
            .back-to-tickets {
                padding: 0.5rem;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .back-to-tickets .back-icon {
                margin-right: 0;
            }
        }
        
        /* Estilos para o popup de sucesso */
        .ticket-success-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .success-popup {
            background-color: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 350px;
            max-width: 90%;
            text-align: center;
            opacity: 0;
            transform: scale(0.7);
            animation: popupFadeIn 0.5s ease forwards;
        }

        @keyframes popupFadeIn {
            0% {
                opacity: 0;
                transform: scale(0.7);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .check-icon {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background-color: rgba(40, 167, 69, 0.1);
            border: 3px solid #28a745;
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
            animation: pulseIcon 0.6s ease-out;
        }

        @keyframes pulseIcon {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.7;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .check-icon svg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
        }

        .check-icon svg path {
            stroke-dasharray: 80;
            stroke-dashoffset: 80;
            animation: drawCheck 0.6s ease-in-out forwards 0.3s;
        }

        @keyframes drawCheck {
            0% {
                stroke-dashoffset: 80;
            }
            100% {
                stroke-dashoffset: 0;
            }
        }

        .success-popup h3 {
            font-size: 24px;
            color: #333;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .success-popup p {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        .ticket-id {
            font-size: 20px;
            font-weight: 700;
            color: #28a745;
            margin-bottom: 20px;
            display: inline-block;
        }

        .redirect-text {
            font-size: 14px;
            color: #6c757d;
            margin-top: 15px;
        }
        
        #countdown {
            font-weight: 700;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <!-- Botão de voltar para tickets -->
    <a href="dashboard.php" class="back-to-tickets">
        <i class="bi bi-arrow-left back-icon"></i>
        <span class="back-text">Voltar para Tickets</span>
    </a>
    
    <button class="sidebar-toggle btn" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>

    <div class="d-flex">
        <!-- Sidebar -->
        <?php require_once('../../includes/sidebar.php'); renderSidebar('dashboard', $_SESSION['usuario_tipo'], $usuario['nome_completo'] ?? 'Usuário', $usuario['foto_perfil'] ?? null); ?>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <div class="container-fluid p-4">
                <!-- Cabeçalho -->
                <div class="stock-header">
                    <div class="stock-title">
                        <i class="bi bi-ticket-perforated"></i>
                        <h4>Criar Novo Ticket</h4>
                    </div>
                </div>
                
                <!-- Mensagens de alerta -->
                <?php if (isset($_SESSION['mensagem'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['mensagem']['tipo']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['mensagem']['texto']; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php unset($_SESSION['mensagem']); ?>
                <?php endif; ?>
                
                <?php if (!empty($mensagem_erro)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $mensagem_erro; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($mensagem_sucesso)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $mensagem_sucesso; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <!-- Formulário para criar ticket -->
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <form id="ticket-form" action="processa_ticket.php" method="post" enctype="multipart/form-data" onsubmit="return false;">
                            <!-- Seção 1: Informações Básicas -->
                            <div class="card mb-4 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bi bi-info-circle me-3"></i>Informações Básicas</h5>
                                </div>
                                <div class="card-body pt-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="assunto" class="form-label">Assunto: <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="assunto" name="assunto" required>
                                                <div class="invalid-feedback">* Campo obrigatório</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-3">
                                                <label for="data_criacao" class="form-label">Data: <span class="text-danger">*</span></label>
                                                <input type="datetime-local" class="form-control" id="data_criacao" name="data_criacao" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                                                <div class="invalid-feedback">* Campo obrigatório</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group mb-3">
                                                <label for="prioridade" class="form-label">Prioridade: <span class="text-danger">*</span></label>
                                                <div class="prioridade-container d-flex justify-content-between">
                                                    <input type="radio" class="btn-check" name="prioridade" id="prioridade-baixa" value="3" required>
                                                    <label class="prioridade-box prioridade-baixa" for="prioridade-baixa">
                                                        <span>Baixa</span>
                                                    </label>
                                                    
                                                    <input type="radio" class="btn-check" name="prioridade" id="prioridade-media" value="2">
                                                    <label class="prioridade-box prioridade-media" for="prioridade-media">
                                                        <span>Média</span>
                                                    </label>
                                                    
                                                    <input type="radio" class="btn-check" name="prioridade" id="prioridade-alta" value="1">
                                                    <label class="prioridade-box prioridade-alta" for="prioridade-alta">
                                                        <span>Alta</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Seção 2: Informações do Equipamento -->
                            <div class="card mb-4 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bi bi-pc-display me-3"></i>Informações do Equipamento</h5>
                                </div>
                                <div class="card-body pt-4">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label for="sala" class="form-label">Sala: <span class="text-danger">*</span></label>
                                                <select class="form-control" id="sala" name="sala_id" required>
                                                    <option value="">Selecione...</option>
                                                    <?php foreach ($salas as $sala): ?>
                                                        <option value="<?php echo $sala['id']; ?>"><?php echo htmlspecialchars($sala['nome']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="invalid-feedback">* Campo obrigatório</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label for="tipo_equipamento" class="form-label">Equipamento:</label>
                                                <select class="form-control" id="tipo_equipamento" name="tipo_equipamento">
                                                    <option value="">Selecione...</option>
                                                    <option value="computador">Computador</option>
                                                    <option value="componente">Componente</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-3">
                                                <label for="id_equipamento" id="label_id_equipamento" class="form-label">Referência do Equipamento:</label>
                                                <select class="form-control" id="id_equipamento" name="componente_id" disabled>
                                                    <option value="">Selecione o tipo de equipamento primeiro</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row" id="row_tipo_componente" style="display: none;">
                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label for="componente_problema" class="form-label">Componente:</label>
                                                <select class="form-control" id="componente_problema" name="componente_problema">
                                                    <option value="">Selecione...</option>
                                                    <?php foreach ($tipos_componentes as $key => $value): ?>
                                                        <?php if ($key != 'computador'): ?>
                                                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Seção 3: Detalhes do Problema -->
                            <div class="card mb-4 border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-3"></i>Detalhes do Problema</h5>
                                </div>
                                <div class="card-body pt-4">
                                    <div class="form-group mb-4">
                                        <label for="descricao" class="form-label">Descrição do Problema: <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="descricao" name="descricao" rows="4" required></textarea>
                                        <div class="invalid-feedback">* Campo obrigatório</div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="mensagem_erro" class="form-label">Mensagem de Erro (se aplicável):</label>
                                                <input type="text" class="form-control" id="mensagem_erro" name="mensagem_erro" placeholder="ex: Error 404, Disco cheio, etc.">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="observacoes" class="form-label">Observações Adicionais:</label>
                                                <input type="text" class="form-control" id="observacoes" name="observacoes" placeholder="Informações complementares">
                                            </div>
                                        </div>
                                    </div>
                                
                                    <div class="form-group mt-4">
                                        <label for="anexo" class="d-block mb-2 form-label">Anexo:</label>
                                        <div class="file-upload-container">
                                            <div class="custom-file-upload">
                                                <input type="file" class="file-input d-none" id="anexo" name="anexo" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                                                <button type="button" id="upload-btn" class="btn btn-outline-primary">
                                                    <i class="bi bi-paperclip me-2"></i>
                                                    <span>Selecionar arquivo...</span>
                                                </button>
                                            </div>
                                            <div id="selected-files-container" class="selected-files mt-2 d-none">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-muted">Arquivo selecionado:</small>
                                                    <button type="button" id="clear-files" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i> Limpar
                                                    </button>
                                                </div>
                                                <div id="file-list"></div>
                                            </div>
                                            <small class="form-text text-muted mt-2">Anexe um documento ou imagem que ajude a entender o problema.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2 mt-4 border-top pt-4">
                                <button type="reset" class="btn btn-secondary me-2 w-md-auto">
                                    <i class="bi bi-arrow-counterclockwise me-3"></i>Limpar
                                </button>
                                <button type="button" id="submit-btn" class="btn btn-primary w-md-auto d-flex justify-content-center">
                                    <i class="bi bi-check-circle me-3"></i> Criar Ticket
                                </button>
                            </div>
                            <!-- Campo hidden para garantir que o ID do componente seja enviado -->
                            <input type="hidden" name="componente_id" value="">
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Modal de Alerta Personalizado -->
    <div class="custom-alert-modal" id="customAlertModal">
        <div class="custom-alert-content">
            <div class="custom-alert-header">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <h5>Atenção</h5>
            </div>
            <div class="custom-alert-body">
                <p id="customAlertMessage"></p>
            </div>
            <div class="custom-alert-footer">
                <button type="button" id="customAlertCloseBtn" class="btn btn-primary">Entendi</button>
            </div>
        </div>
    </div>
    
    <!-- Popup de sucesso -->
    <div id="success-popup" class="ticket-success-overlay" style="display: none;">
        <div class="success-popup">
            <div class="check-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <path fill="none" stroke="#28a745" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" d="M14,27 L22,35 L38,17" />
                </svg>
            </div>
            <h3>Ticket criado com sucesso!</h3>
            <p>O seu ticket foi registado no sistema e será atendido em breve.</p>
            <div class="ticket-id">Ticket #<span id="ticket-id-display"></span></div>
            <div class="redirect-text">
                A redirecionar para o Dashboard em <span id="countdown">7</span> segundos...
            </div>
        </div>
    </div>
    
    <!-- Script para carregamento dinâmico de componentes -->
    <script>
    $(document).ready(function() {
        // IMPORTANTE: Garantir que o formulário nunca seja enviado de forma tradicional
        document.getElementById('ticket-form').addEventListener('submit', function(e) {
            e.preventDefault(); // Impedir o envio normal do formulário
            return false;
        }, true); // true para garantir que este listener execute primeiro (fase de captura)
        
        // Adicionar evento de clique ao botão de submissão para processar via AJAX
        $('#submit-btn').on('click', function() {
            // Disparar a validação e envio do formulário via AJAX
            processarFormulario();
        });
        
        // Para depuração
        const mapTipoComponente = {
            'cooler': 'cooler',
            'caixa': 'caixa',
            'disco': 'disco',
            'drivedvd': 'drivedvd',
            'fonte': 'fonte',
            'memoriaram': 'memoriaram',
            'monitor': 'monitor',
            'placagrafica': 'placagrafica',
            'placamae': 'placamae',
            'placarede': 'placarede', 
            'processador': 'processador',
            'rato': 'rato',
            'teclado': 'teclado',
            'ventoinha': 'ventoinha'
        };
        
        // Verificar se há uma mensagem de sucesso de ticket e mostrar o popup
        function verificarMensagemTicket() {
            const alertSuccess = document.querySelector('.alert-success');
            if (alertSuccess && alertSuccess.textContent.includes('Ticket criado com sucesso')) {
                // Extrair o ID do ticket da mensagem
                const texto = alertSuccess.textContent;
                const regex = /Ticket #(\d+)/;
                const match = texto.match(regex);
                const ticketId = match ? match[1] : '---';
                
                // Esconder o alerta padrão
                alertSuccess.style.display = 'none';
                
                // Mostrar o popup personalizado
                const popup = document.getElementById('success-popup');
                document.getElementById('ticket-id-display').textContent = ticketId;
                
                // Adicionar classe show para fade in
                popup.style.display = 'flex';
                setTimeout(() => {
                    popup.classList.add('show');
                    document.querySelector('.success-popup').style.opacity = '1';
                    document.querySelector('.success-popup').style.transform = 'translateY(0)';
                }, 10);
                
                // Iniciar o countdown
                let secondsLeft = 7;
                const countdownElement = document.getElementById('countdown');
                
                const countdownInterval = setInterval(function() {
                    secondsLeft--;
                    countdownElement.textContent = secondsLeft;
                    
                    if (secondsLeft <= 0) {
                        clearInterval(countdownInterval);
                        // Redirecionar para dashboard
                        window.location.href = '../tickets/dashboard.php';
                    }
                }, 1000);
            }
        }
        
        // Verificar ao carregar a página
        verificarMensagemTicket();
        
        // Controle do botão "Voltar para Tickets"
        const backButton = document.querySelector('.back-to-tickets');
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
        
        // Variável para controlar se o componente já tem ticket
        let componenteComTicket = false;
        
        // Função para mostrar o alerta personalizado
        function showCustomAlert(message) {
            // Definir a mensagem
            $('#customAlertMessage').text(message);
            
            // Mostrar o modal
            $('#customAlertModal').css('display', 'flex');
            
            // Fechar ao clicar no botão
            $('#customAlertCloseBtn').off('click').on('click', function() {
                $('#customAlertModal').css('display', 'none');
            });
            
            // Fechar ao clicar fora do modal
            $('#customAlertModal').off('click').on('click', function(e) {
                if (e.target === this) {
                    $(this).css('display', 'none');
                }
            });
        }
        
        // Função para mostrar o popup de sucesso
        function showSuccessPopup(ticketId) {
            $('#ticket-id-display').text(ticketId);
            $('#success-popup').fadeIn();
            
            // Resetar o formulário
            $('#ticket-form')[0].reset();
            
            // Iniciar contagem regressiva
            let seconds = 7;
            const countdownElement = $('#countdown');
            
            const countdownInterval = setInterval(() => {
                seconds--;
                countdownElement.text(seconds);
                
                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = '../tickets/dashboard.php';
                }
            }, 1000);
            
            // Redirecionar após 7 segundos, caso o contador falhe
            setTimeout(() => {
                window.location.href = '../tickets/dashboard.php';
            }, 7000);
        }

        // Função para verificar se o componente já tem um ticket ativo
        function verificarComponenteTicket(tipoComponente, idComponente) {
            // Resetar o estado
            componenteComTicket = false;
            
            // Remover avisos anteriores
            $('#aviso-componente').remove();
            
            // Se não tiver tipo ou id, não faz nada
            if (!tipoComponente || !idComponente) return;
            
            // Fazer a verificação via AJAX
            $.ajax({
                url: 'ajax/verificar_componente.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    tipo_componente: tipoComponente,
                    id_componente: idComponente
                },
                success: function(response) {
                    if (response.status === 'ok') {
                        if (response.ticket_existente) {
                            // Componente tem ticket ativo
                            componenteComTicket = true;
                            
                            // Mostrar aviso vermelho
                            $('#id_equipamento').after(
                                `<div id="aviso-componente" class="alert alert-danger mt-2">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    ${response.mensagem}
                                </div>`
                            );
                        }
                    } else if (response.error) {
                        console.error('Erro na verificação:', response.error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro na requisição AJAX:', error);
                }
            });
        }
        
        // Quando um componente é selecionado, verificar se já tem ticket
        $('#id_equipamento').change(function() {
            const tipoEquipamento = $('#tipo_equipamento').val();
            const idEquipamento = $(this).val();
            
            if (tipoEquipamento === 'componente') {
                const tipoComponente = $('#componente_problema').val();
                verificarComponenteTicket(tipoComponente, idEquipamento);
            } else if (tipoEquipamento === 'computador') {
                verificarComponenteTicket('computador', idEquipamento);
            }
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
        };
        
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

        // Manipular seleção de arquivos para mostrar os arquivos selecionados
        $('#anexo').change(function(e) {
            const files = e.target.files;
            // Limitar a 5 arquivos
            if (files.length > 5) {
                showCustomAlert('Você pode selecionar no máximo 5 arquivos.');
                // Limpar o input para permitir nova seleção
                $(this).val('');
                $('.upload-label span').text('Selecionar arquivos...');
                $('.selected-files').remove();
                return;
            }
            
            const fileNames = Array.from(files).map(file => file.name);
            
            // Atualizar o texto do botão para mostrar quantos arquivos foram selecionados
            if (files.length > 0) {
                $('.upload-label span').text(files.length + ' arquivo(s) selecionado(s)');
                
                // Criar uma div para mostrar os nomes dos arquivos
                let fileList = '<div class="selected-files mt-2"><small class="text-muted">Arquivos selecionados:</small>';
                fileNames.forEach(name => {
                    fileList += `<div><small class="text-primary"><i class="bi bi-file-earmark me-1"></i>${name}</small></div>`;
                });
                fileList += '</div>';
                
                // Remover lista anterior se existir
                $('.selected-files').remove();
                
                // Adicionar a nova lista após o botão de upload
                $('.file-upload-container').append(fileList);
                
                // Mudar cor do botão para indicar seleção - garantir que o texto seja branco e visível
                $('.upload-label').addClass('btn-primary').removeClass('btn-outline-primary');
                $('.upload-label').css('color', 'white');
            } else {
                // Resetar o texto e remover a lista
                $('.upload-label span').text('Selecionar arquivos...');
                $('.selected-files').remove();
                $('.upload-label').removeClass('btn-primary').addClass('btn-outline-primary');
                $('.upload-label').css('color', '');
            }
        });
    
        // Quando o tipo de equipamento for alterado (computador ou componente)
        $('#tipo_equipamento').change(function() {
            var tipoEquipamento = $(this).val();
            var rowTipoComponente = $('#row_tipo_componente');
            var selectComponente = $('#id_equipamento');
            var labelComponente = $('#label_id_equipamento');
            
            // Limpar e desabilitar o select de referência
            selectComponente.empty().append('<option value="">Selecione...</option>').prop('disabled', true);
            
            // Reset do select de componente_problema
            $('#componente_problema').val('');
            
            // Limpar o valor do campo hidden componente_id
            $('input[name="componente_id"]').val('');
            
            if (tipoEquipamento == 'computador') {
                // Esconder a linha de tipos de componentes
                rowTipoComponente.hide();
                
                // Atualizar o label
                labelComponente.text('Referência do Computador:');
                
                var salaId = $('#sala').val();
                
                // Verificar se uma sala foi selecionada
                if (!salaId) {
                    showCustomAlert('Por favor, selecione uma sala primeiro.');
                    $('#tipo_equipamento').val('');
                    return;
                }
                
                // Habilitar o select de referência
                selectComponente.prop('disabled', false);
                
                // Log para depuração
                console.log('Buscando computadores para sala ID:', salaId);
                
                // Carregar computadores usando fetch API
                fetch('funcoes/buscar_computadores.php?sala_id=' + salaId)
                .then(response => response.json())
                .then(data => {
                    console.log('Resposta da busca de computadores:', data);
                    
                    if (data.success && data.data && data.data.length > 0) {
                        // Adicionar computadores ao select
                        data.data.forEach(function(item) {
                            selectComponente.append(
                                $('<option></option>')
                                    .attr('value', item.id)
                                    .text(item.texto)
                            );
                        });
                        
                        // Se tiver apenas um computador, selecionar automaticamente e atualizar o hidden field
                        if (data.data.length === 1) {
                            selectComponente.val(data.data[0].id);
                            $('input[name="componente_id"]').val(data.data[0].id);
                        }
                    } else {
                        selectComponente.append('<option value="">Nenhum computador disponível (podem estar em manutenção ou com tickets ativos)</option>');
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar computadores:', error);
                    selectComponente.append('<option value="">Erro ao carregar computadores</option>');
                });
                
            } else if (tipoEquipamento == 'componente') {
                // Mostrar a linha de tipos de componentes
                rowTipoComponente.show();
                
                // Atualizar o label
                labelComponente.text('Referência do Componente:');
            } else {
                // Esconder a linha de tipos de componentes
                rowTipoComponente.hide();
                
                // Atualizar o label
                labelComponente.text('Referência do Equipamento:');
            }
        });
        
        // Quando o tipo de componente for alterado
        $('#componente_problema').change(function() {
            var tipoComponente = $(this).val();
            var salaId = $('#sala').val(); // Obtém o ID da sala selecionada
            var selectComponente = $('#id_equipamento');
            
            // Limpar select de componentes
            selectComponente.empty().append('<option value="">Selecione...</option>');
            
            // Limpar o valor do campo hidden componente_id
            $('input[name="componente_id"]').val('');
            
            // Habilitar ou desabilitar select de componentes
            if (tipoComponente) {
                selectComponente.prop('disabled', false);
                
                // Verificar se uma sala foi selecionada
                if (!salaId) {
                    showCustomAlert('Por favor, selecione uma sala primeiro.');
                    $('#componente_problema').val('');
                    return;
                }
                
                // Log para depuração
                console.log('Buscando componentes do tipo:', tipoComponente, 'para sala ID:', salaId);
                
                // Carregar componentes usando fetch API
                fetch('funcoes/buscar_componentes.php?tipo=' + tipoComponente + '&sala_id=' + salaId)
                .then(response => response.json())
                .then(data => {
                    console.log('Resposta da busca de componentes:', data);
                    
                    if (data.success && data.data && data.data.length > 0) {
                        // Adicionar componentes ao select
                        data.data.forEach(function(item) {
                            selectComponente.append(
                                $('<option></option>')
                                    .attr('value', item.id)
                                    .text(item.texto)
                            );
                        });
                        
                        // Se tiver apenas um componente, selecionar automaticamente e atualizar o hidden field
                        if (data.data.length === 1) {
                            selectComponente.val(data.data[0].id);
                            $('input[name="componente_id"]').val(data.data[0].id);
                        }
                    } else {
                        selectComponente.append('<option value="">Nenhum componente disponível (podem estar em manutenção ou com tickets ativos)</option>');
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar componentes:', error);
                    selectComponente.append('<option value="">Erro ao carregar componentes</option>');
                });
                
            } else {
                selectComponente.prop('disabled', true);
            }
        });
        
        // Quando a sala for alterada, resetar os seletores
        $('#sala').change(function() {
            $('#tipo_equipamento').val('');
            $('#row_tipo_componente').hide();
            $('#componente_problema').val('');
            $('#id_equipamento').empty().append('<option value="">Selecione o tipo de equipamento primeiro</option>').prop('disabled', true);
            
            // Limpar o valor do campo hidden componente_id
            $('input[name="componente_id"]').val('');
            
            // Remover possíveis avisos anteriores
            $('#aviso-componente').remove();
            
            // Se existir um campo sala_id como input hidden, atualizá-lo também
            const salaId = $(this).val();
            if ($('input[name="sala_id"]').length > 0) {
                $('input[name="sala_id"]').val(salaId);
            }
        });

        // ----------- Gerenciamento de arquivos anexos -----------
        // Variável para armazenar o arquivo selecionado
        let selectedFile = null;
        const maxFiles = 1;
        
        // Referências aos elementos DOM
        const fileInput = document.getElementById('anexo');
        const uploadBtn = document.getElementById('upload-btn');
        const fileList = document.getElementById('file-list');
        const clearFilesBtn = document.getElementById('clear-files');
        const filesContainer = document.getElementById('selected-files-container');
        
        // Ao clicar no botão customizado, acionar o input file
        uploadBtn.addEventListener('click', function() {
            fileInput.click();
        });
        
        // Quando um arquivo é selecionado
        fileInput.addEventListener('change', function(e) {
            console.log('Arquivo selecionado:', e.target.files);
            
            // Se nenhum arquivo foi selecionado, sair da função
            if (e.target.files.length === 0) return;
            
            // Armazenar o arquivo selecionado
            selectedFile = e.target.files[0];
            console.log('Arquivo adicionado:', selectedFile.name);
            
            // Atualizar a interface
            updateFileList();
        });
        
        // Limpar o arquivo
        clearFilesBtn.addEventListener('click', function() {
            console.log('Limpando arquivo');
            selectedFile = null;
            fileInput.value = '';
            updateFileList();
        });
        
        // Atualizar a lista de arquivos na interface
        function updateFileList() {
            console.log('Atualizando interface com arquivo:', selectedFile ? selectedFile.name : 'nenhum');
            
            // Atualizar o texto do botão
            if (selectedFile) {
                uploadBtn.querySelector('span').textContent = 'Arquivo selecionado';
                uploadBtn.classList.remove('btn-outline-primary');
                uploadBtn.classList.add('btn-primary');
                filesContainer.classList.remove('d-none');
            } else {
                uploadBtn.querySelector('span').textContent = 'Selecionar arquivo...';
                uploadBtn.classList.remove('btn-primary');
                uploadBtn.classList.add('btn-outline-primary');
                filesContainer.classList.add('d-none');
            }
            
            // Limpar a lista atual
            fileList.innerHTML = '';
            
            // Se tem arquivo selecionado, exibir suas informações
            if (selectedFile) {
                // Determinar ícone baseado no tipo
                let fileIcon = 'bi-file-earmark';
                if (selectedFile.type.includes('image')) fileIcon = 'bi-file-earmark-image';
                else if (selectedFile.type.includes('pdf')) fileIcon = 'bi-file-earmark-pdf';
                else if (selectedFile.type.includes('word')) fileIcon = 'bi-file-earmark-word';
                else if (selectedFile.type.includes('excel') || selectedFile.type.includes('sheet')) fileIcon = 'bi-file-earmark-excel';
                else if (selectedFile.type.includes('text')) fileIcon = 'bi-file-earmark-text';
                
                // Criar o HTML para este arquivo
                const fileItemHTML = `
                    <div class="file-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="bi ${fileIcon} text-primary me-2"></i>
                                <span class="file-name">${selectedFile.name}</span>
                                <small class="text-muted ms-2">(${formatFileSize(selectedFile.size)})</small>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm remove-file">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                // Inserir o HTML
                fileList.innerHTML = fileItemHTML;
                
                // Adicionar event listener para o botão de remoção
                const removeFileBtn = document.querySelector('.remove-file');
                if (removeFileBtn) {
                    removeFileBtn.addEventListener('click', function() {
                        selectedFile = null;
                        fileInput.value = '';
                        updateFileList();
                    });
                }
            }
        }
        
        // Formatar o tamanho do arquivo para exibição
        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' bytes';
            else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            else return (bytes / 1048576).toFixed(1) + ' MB';
        }
        
        // Atualizar o formulário antes do envio
        $('form').on('submit', function() {
            // Se temos um arquivo selecionado, não precisa fazer nada especial
            // pois o input file já contém o arquivo correto
            console.log('Formulário sendo enviado, arquivo:', selectedFile ? selectedFile.name : 'nenhum');
            return true; // Permitir que o formulário continue com o envio
        });
        
        // Adicionar código de validação do formulário
        $(document).ready(function() {
            // Primeiro, vamos adicionar o atributo required aos campos obrigatórios do HTML
            $('#assunto, #data_criacao, #sala_id, #descricao').attr('required', 'required');
            
            // Verificar se existem outras funções interferindo nos eventos de submit
            // Remover todos os handlers de submit anteriores
            $('form').off('submit');
            
            // Adicionar nosso handler de submit personalizado
            $('form').on('submit', function(e) {
                // Verificar a validade do formulário usando a API de validação do HTML5
                var form = this;
                
                // Limpar validações anteriores personalizadas
                $('.campo-erro').remove();
                $('.prioridade-invalid').removeClass('prioridade-invalid');
                $('.has-error').removeClass('has-error');
                $('.is-invalid-custom').removeClass('is-invalid-custom');
                
                // Flag para verificar se o formulário é válido
                let formValido = true;
                
                // Verificar cada campo obrigatório manualmente
                const camposObrigatorios = [
                    { id: 'assunto', mensagem: 'Este campo é obrigatório' },
                    { id: 'data_criacao', mensagem: 'Este campo é obrigatório' },
                    { id: 'sala', mensagem: 'Por favor, selecione uma sala' },
                    { id: 'descricao', mensagem: 'Este campo é obrigatório' }
                ];
                
                camposObrigatorios.forEach(function(campo) {
                    const elemento = $('#' + campo.id);
                    if (!elemento.val() || elemento.val().trim() === '') {
                        formValido = false;
                        elemento.addClass('is-invalid-custom');
                        
                        // Adicionar mensagem de erro visível após o campo se ainda não existir
                        if (elemento.next('.campo-erro').length === 0) {
                            const mensagemErro = $('<div class="campo-erro" style="color: #dc3545; font-weight: bold; margin-top: 5px; padding: 5px; background-color: #ffe0e3; border-left: 4px solid #dc3545; border-radius: 4px;">' + campo.mensagem + '</div>');
                            elemento.after(mensagemErro);
                        }
                    }
                });
                

                
                // Verificar se a prioridade foi selecionada
                if ($('input[name="prioridade"]:checked').length === 0) {
                    formValido = false;
                    $('.prioridade-box').addClass('prioridade-invalid');
                    $('.prioridade-container').addClass('has-error');
                    
                    // Adicionar mensagem de erro visível após o container
                    if ($('.prioridade-container').next('.campo-erro').length === 0) {
                        const mensagemErro = $('<div class="campo-erro" style="color: #dc3545; font-weight: bold; margin-top: 5px; padding: 5px; background-color: #ffe0e3; border-left: 4px solid #dc3545; border-radius: 4px;">Selecione uma prioridade</div>');
                        $('.prioridade-container').after(mensagemErro);
                    }
                }
                
                // Se o formulário não for válido, impedir o envio
                if (!formValido) {
                    e.preventDefault();
                    
                    // Rolar até o primeiro erro
                    const primeiroErro = $('.is-invalid-custom, .prioridade-invalid').first();
                    if (primeiroErro.length > 0) {
                        $('html, body').animate({
                            scrollTop: primeiroErro.offset().top - 100
                        }, 200);
                    }
                    
                    return false;
                }
                
                // Se o formulário for válido, garantir que os campos estão atualizados
                // Atualizar o valor do campo sala_id se necessário
                const salaVal = $('#sala').val();
                if (salaVal) {
                    if ($('input[name="sala_id"]').length > 0) {
                        $('input[name="sala_id"]').val(salaVal);
                    }
                }
                
                // Atualizar o valor do campo componente_id
                const componenteVal = $('#id_equipamento').val();
                if (componenteVal) {
                    $('input[name="componente_id"]').val(componenteVal);
                }
                
                // Se tudo estiver válido, permitir o envio normal do formulário
                return true;
            });
            
            // Adicionar handler para o botão de reset
            $('button[type="reset"]').on('click', function(e) {
                e.preventDefault();
                
                // Resetar o formulário
                $('form')[0].reset();
                
                // Limpar o arquivo selecionado
                selectedFile = null;
                updateFileList();
                
                // Remover estilos de validação
                $('.is-invalid-custom').removeClass('is-invalid-custom');
                $('.prioridade-invalid').removeClass('prioridade-invalid');
                $('.has-error').removeClass('has-error');
                $('.campo-erro').remove();
                
                // Resetar UI personalizada
                $('#upload-btn').removeClass('btn-primary').addClass('btn-outline-primary');
                $('#upload-btn span').text('Selecionar arquivo...');
                $('#selected-files-container').addClass('d-none');
                $('#file-list').html('');
                
                // Resetar selects dinâmicos
                $('#tipo_equipamento').val('');
                $('#componente_problema').val('');
                $('#row_tipo_componente').hide();
                $('#id_equipamento').empty().append('<option value="">Selecione o tipo de equipamento primeiro</option>').prop('disabled', true);
                $('#aviso-componente').remove();
            });
            
            // Adicionar CSS inline para garantir que seja aplicado
            $('<style type="text/css">')
                .text(`
                    .is-invalid-custom {
                        border: 3px solid #dc3545 !important;
                        background-color: #fff8f8 !important;
                        box-shadow: 0 0 10px rgba(220, 53, 69, 0.3) !important;
                    }
                    
                    .prioridade-invalid {
                        border: 3px solid #dc3545 !important;
                        background-color: #fff8f8 !important;
                        box-shadow: 0 0 10px rgba(220, 53, 69, 0.3) !important;
                    }
                `)
                .appendTo('head');
        });

        // Função para processar o formulário via AJAX
        function processarFormulario() {
            // Validar o formulário
            let formValido = true;
            
            // Verificar cada campo obrigatório manualmente
            const camposObrigatorios = [
                { id: 'assunto', mensagem: 'Este campo é obrigatório' },
                { id: 'data_criacao', mensagem: 'Este campo é obrigatório' },
                { id: 'sala', mensagem: 'Por favor, selecione uma sala' },
                { id: 'descricao', mensagem: 'Este campo é obrigatório' }
            ];
            
            // Limpar validações anteriores
            $('.campo-erro').remove();
            $('.is-invalid-custom').removeClass('is-invalid-custom');
            $('.prioridade-invalid').removeClass('prioridade-invalid');
            $('.has-error').removeClass('has-error');
            
            camposObrigatorios.forEach(function(campo) {
                const elemento = $('#' + campo.id);
                if (!elemento.val() || elemento.val().trim() === '') {
                    formValido = false;
                    elemento.addClass('is-invalid-custom');
                    
                    // Adicionar mensagem de erro visível após o campo se ainda não existir
                    if (elemento.next('.campo-erro').length === 0) {
                        const mensagemErro = $('<div class="campo-erro" style="color: #dc3545; font-weight: bold; margin-top: 5px; padding: 5px; background-color: #ffe0e3; border-left: 4px solid #dc3545; border-radius: 4px;">' + campo.mensagem + '</div>');
                        elemento.after(mensagemErro);
                    }
                }
            });
            
            // Verificar se a prioridade foi selecionada
            if ($('input[name="prioridade"]:checked').length === 0) {
                formValido = false;
                $('.prioridade-box').addClass('prioridade-invalid');
                $('.prioridade-container').addClass('has-error');
                
                // Adicionar mensagem de erro visível após o container
                if ($('.prioridade-container').next('.campo-erro').length === 0) {
                    const mensagemErro = $('<div class="campo-erro" style="color: #dc3545; font-weight: bold; margin-top: 5px; padding: 5px; background-color: #ffe0e3; border-left: 4px solid #dc3545; border-radius: 4px;">Selecione uma prioridade</div>');
                    $('.prioridade-container').after(mensagemErro);
                }
            }
            
            // Se o formulário não for válido, impedir o envio
            if (!formValido) {
                // Rolar até o primeiro erro
                const primeiroErro = $('.is-invalid-custom, .prioridade-invalid').first();
                if (primeiroErro.length > 0) {
                    $('html, body').animate({
                        scrollTop: primeiroErro.offset().top - 100
                    }, 200);
                }
                
                return false;
            }
            
            // Preparar o formulário para envio
            const formData = new FormData(document.getElementById('ticket-form'));
            
            // Desabilitar o botão de enviar para prevenir múltiplos envios
            $('#submit-btn').prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i> A criar...');
            
            // Enviar o formulário via AJAX
            $.ajax({
                url: $('#ticket-form').attr('action'),
                type: 'POST',
                data: formData,
                processData: false,  // Necessário para FormData
                contentType: false,  // Necessário para FormData
                dataType: 'json',    // Esperamos uma resposta JSON
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                },
                success: function(response) {
                    console.log('Resposta do servidor:', response);
                    
                    if (response.success) {
                        // Mostrar o popup de sucesso
                        showSuccessPopup(response.ticket_id);
                        
                    } else {
                        // Mostrar mensagem de erro
                        showCustomAlert(response.message || 'Erro ao criar ticket');
                        
                        // Reativar o botão de envio
                        $('#submit-btn').prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Criar Ticket');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro AJAX:', error);
                    
                    // Mostrar mensagem de erro
                    showCustomAlert('Erro ao processar a requisição. Por favor, tente novamente.');
                    
                    // Reativar o botão de envio
                    $('#submit-btn').prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Criar Ticket');
                }
            });
        }
    });
  </script>
  
  <script>
    // Controle do botão "Voltar para Dashboard"
    const backButton = document.querySelector('.back-to-tickets');
    let lastScrollTop = 0;
    let scrollingUp = false;
    let scrollTimer = null;
    
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimer);
        
        const st = window.pageYOffset || document.documentElement.scrollTop;
        
        // Determinar a direção do scroll
        if (st > lastScrollTop) {
            // Scroll para baixo
            scrollingUp = false;
            if (backButton) backButton.classList.add('hidden');
        } else if (st < lastScrollTop) {
            // Scroll para cima
            scrollingUp = true;
            if (backButton) backButton.classList.remove('hidden');
        }
        
        lastScrollTop = st <= 0 ? 0 : st;
        
        // Definir timeout para verificar se o scroll parou
        scrollTimer = setTimeout(function() {
            // Se estiver no topo da página, sempre mostrar o botão
            if (st <= 50) {
                if (backButton) backButton.classList.remove('hidden');
            }
        }, 150);
    });
    
    // Quando o valor do select id_equipamento for alterado, atualizar também o hidden input
    $('#id_equipamento').change(function() {
        const componenteId = $(this).val();
        console.log('Componente ID selecionado:', componenteId);
        // Garantir que o valor seja passado no envio do formulário
        if (componenteId) {
            $('input[name="componente_id"]').val(componenteId);
        }
    });
  </script>
</body>
</html> 