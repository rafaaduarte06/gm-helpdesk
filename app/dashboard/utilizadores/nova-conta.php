<?php
// Prevenir qualquer saída antes do JSON
ob_start();
include('../../ligacao_base_dados.php');
require_once('../../includes/sidebar.php');
require_once('../../includes/email_funcoes.php'); // Incluir funções de email
ob_clean(); // Limpa qualquer saída do include

// Remova ou comente esta linha se existir
// echo "Conexão estabelecida com sucesso!";

session_start();

// Verificar se o usuário está logado e é um técnico
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    
    // Verificar se o email já contém o domínio
    if (strpos($email, '@') === false) {
        $email = $email . '@' . obterConfig('APP_EMAIL_DOMAIN');
    }
    
    // Verifica se o email já existe em qualquer uma das tabelas
    $stmtEmailTecnico = $pdo->prepare("SELECT COUNT(*) FROM suporte_tecnico WHERE email_institucional = ?");
    $stmtEmailTecnico->execute([$email]);
    $emailExistsTecnico = $stmtEmailTecnico->fetchColumn() > 0;
    
    $stmtEmailComunidade = $pdo->prepare("SELECT COUNT(*) FROM comunidade_escolar WHERE email_institucional = ?");
    $stmtEmailComunidade->execute([$email]);
    $emailExistsComunidade = $stmtEmailComunidade->fetchColumn() > 0;
    
    if ($emailExistsTecnico || $emailExistsComunidade) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Este email já está em uso. Por favor, escolha outro email.']);
        exit;
    }
    
    // Gerar uma senha forte aleatória
    $plainPassword = gerarSenhaAleatoria();
    $senha = password_hash($plainPassword, PASSWORD_BCRYPT);

    if ($_POST['tipo'] === 'tecnico') {
            $numero_processo = $_POST['matricula'];
            
            // Verificar se o número de processo já existe para técnicos
            $stmtNumeroTecnico = $pdo->prepare("SELECT COUNT(*) FROM suporte_tecnico WHERE numero_processo = ?");
            $stmtNumeroTecnico->execute([$numero_processo]);
            $numeroExistsTecnico = $stmtNumeroTecnico->fetchColumn() > 0;
            
            if ($numeroExistsTecnico) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Este número de processo já está em uso por outro técnico. Por favor, escolha outro número.']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO suporte_tecnico (nome_completo, email_institucional, numero_processo, senha_hash, primeiro_login) VALUES (?, ?, ?, ?, TRUE)");
        $stmt->execute([$nome, $email, $numero_processo, $senha]);
        
        // Obter o ID da conta criada
        $novoId = $pdo->lastInsertId();
        
        // Enviar email de boas-vindas para suporte técnico
        $emailSent = enviarEmailBoasVindasTecnico($email, $nome, $plainPassword);
        
        $mensagem = 'Conta criada com sucesso!';
        if ($emailSent) {
            $mensagem .= ' Um email de boas-vindas foi enviado com as credenciais de acesso.';
        } else {
            $mensagem .= ' Não foi possível enviar o email de boas-vindas.';
        }
        
        } else {
            $numero_processo = $_POST['matriculaUsuario'];
            $cargo = $_POST['cargo'];
            
            // Verificar se o número de processo já existe para comunidade escolar
            $stmtNumeroComunidade = $pdo->prepare("SELECT COUNT(*) FROM comunidade_escolar WHERE numero_processo = ?");
            $stmtNumeroComunidade->execute([$numero_processo]);
            $numeroExistsComunidade = $stmtNumeroComunidade->fetchColumn() > 0;
            
            if ($numeroExistsComunidade) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Este número de processo já está em uso por outro membro da comunidade escolar. Por favor, escolha outro número.']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO comunidade_escolar (nome_completo, email_institucional, cargo, numero_processo, senha_hash, primeiro_login) VALUES (?, ?, ?, ?, ?, TRUE)");
        $stmt->execute([$nome, $email, $cargo, $numero_processo, $senha]);
        
        // Obter o ID da conta criada
        $novoId = $pdo->lastInsertId();
        
        // Enviar email de boas-vindas para comunidade escolar
        $emailSent = enviarEmailBoasVindasComunidade($email, $nome, $cargo, $plainPassword);
        
        $mensagem = 'Conta criada com sucesso!';
        if ($emailSent) {
            $mensagem .= ' Um email de boas-vindas foi enviado com as credenciais de acesso.';
        } else {
            $mensagem .= ' Não foi possível enviar o email de boas-vindas.';
        }
    }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $mensagem]);
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro ao criar conta: ' . $e->getMessage()]);
        exit;
    }
}

/**
 * Gera uma senha aleatória forte
 * 
 * @param int $length Comprimento da senha (padrão: 10)
 * @return string Senha gerada
 */
function gerarSenhaAleatoria($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()-_+=';
    $password = '';
    $max = strlen($characters) - 1;
    
    // Garantir pelo menos um caractere de cada tipo
    $password .= $characters[rand(0, 9)]; // número
    $password .= $characters[rand(10, 35)]; // letra minúscula
    $password .= $characters[rand(36, 61)]; // letra maiúscula
    $password .= $characters[rand(62, $max)]; // caractere especial
    
    // Preencher o resto da senha
    for ($i = 0; $i < $length - 4; $i++) {
        $password .= $characters[rand(0, $max)];
    }
    
    // Embaralhar a senha para não seguir um padrão
    return str_shuffle($password);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Conta - GM HelpDesk</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../assets/css/variables.css">
    <link rel="stylesheet" href="../../assets/css/custom.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <style>
        /* Estilo de fundo gradiente como na página de login */
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
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
            cursor: pointer;
            overflow: hidden;
            position: relative;
            display: none;
        }
        
        /* Correção específica para o ícone */
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
        
        .sidebar-toggle:hover {
            background-color: rgba(75, 134, 180, 0.25);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }

        .sidebar-toggle:active {
            transform: translateY(1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .sidebar {
            transition: all 0.3s ease;
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
                margin-left: 0;
                width: 100%;
                padding-left: 1rem;
                padding-right: 1rem;
                transition: all 0.3s ease;
            }
            
            .sidebar-toggle {
                display: flex;
                position: fixed;
                top: 15px;
                left: 15px;
                z-index: 1000;
            }
        }

        /* Estilo do cabeçalho da página */
        .stock-header {
            background: linear-gradient(135deg, #2a4d69, rgb(36, 110, 167));
            color: white;
            padding: 1.8rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            display: flex;
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

        /* Cards de seleção */
        .card-selection {
            background: #fff;
            border: 2px solid #0d6efd;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 100%;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-selection.active {
            background: #0d6efd;
            color: white;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
            transform: translateY(-3px);
        }

        .card-selection:not(.active) {
            background: white;
            color: #0d6efd;
        }

        .card-selection:hover:not(.active) {
            background: rgba(13, 110, 253, 0.05);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.12);
        }

        .card-selection .icon-wrapper {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .card-selection span {
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* Ajustes para formulário */
        .credenciais-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 2rem !important;
            width: 100%;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .credenciais-section:hover {
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .credenciais-section h5 {
            color: #2a4d69;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid rgba(42, 77, 105, 0.1);
        }

        .form-control, .form-select {
            width: 100%;
            border-radius: 8px;
            padding: 0.6rem 1rem;
            border: 1px solid #dee2e6;
            box-shadow: none;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .form-label {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
            transform: translateY(-2px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }

        .btn-outline-danger {
            border-radius: 8px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-outline-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .credenciais-section {
                padding: 1.5rem !important;
            }
            
            .stock-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                padding: 1.2rem;
            }
            
            .card-selection {
                padding: 1rem;
            }
            
            .card-selection .icon-wrapper {
                font-size: 2rem;
            }
            
            .main-content {
                padding-top: 60px;
            }
            
            .stock-title i {
                font-size: 1.5rem;
                padding: 8px;
            }
            
            .stock-title h4 {
                font-size: 1.2rem;
            }
        }
        
        @media (max-width: 576px) {
            .credenciais-section {
                padding: 1.2rem !important;
            }
            
            .form-control, .form-select {
                padding: 0.5rem 0.8rem;
                font-size: 0.9rem;
            }
            
            .form-label {
                font-size: 0.9rem;
            }
            
            .btn-primary, .btn-outline-danger {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
            
            .stock-header {
                padding: 1.2rem;
            }
            
            .stock-title i {
                font-size: 1.4rem;
                padding: 6px;
            }
            
            .stock-title h4 {
                font-size: 1.2rem;
            }
        }

        /* Estilo para conteúdo principal */
        .main-content {
            margin-left: 300px;
            width: calc(100% - 300px);
            transition: all 0.3s ease;
        }
        
        /* Media queries aprimoradas para melhorar responsividade */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                width: 100%;
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            .stock-header {
                padding: 1.5rem;
                margin-top: 1rem;
            }
            
            .stock-title i {
                font-size: 1.8rem;
                padding: 8px;
            }
            
            .stock-title h4 {
                font-size: 1.3rem;
            }
            
            .credenciais-section {
                padding: 1.5rem !important;
            }
            
            .container-fluid {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            
            .card-selection {
                padding: 1.25rem;
                min-height: 120px;
            }
            
            .card-selection .icon-wrapper {
                font-size: 2rem;
                margin-bottom: 0.75rem;
            }
            
            .card-selection span {
                font-size: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
            }
            
            .stock-title h4 {
                font-size: 1.2rem;
            }
            
            .stock-header {
                padding: 1.2rem;
            }
            
            .row {
                margin-left: -0.25rem;
                margin-right: -0.25rem;
            }
            
            .col-md-6 {
                padding-left: 0.25rem;
                padding-right: 0.25rem;
            }
            
            .card-selection {
                padding: 1rem;
                min-height: 100px;
            }
            
            .card-selection .icon-wrapper {
                font-size: 1.75rem;
                margin-bottom: 0.5rem;
            }
            
            .credenciais-section {
                padding: 1.25rem !important;
            }
            
            .form-label {
                margin-bottom: 0.25rem;
            }
            
            .mt-4 {
                margin-top: 1rem !important;
            }
            
            .mt-3 {
                margin-top: 0.75rem !important;
            }
        }

        /* Botão de voltar para contas */
        .back-to-contas {
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
        
        .back-to-contas.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .back-to-contas::before {
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

        .back-to-contas:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
            color: white;
        }

        .back-to-contas:hover::before {
            opacity: 1;
        }

        .back-to-contas .back-icon {
            margin-right: 10px;
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        .back-to-contas:hover .back-icon {
            transform: translateX(-4px);
        }

        .back-to-contas .back-text {
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .back-to-contas {
                padding: 0.6rem 1rem;
            }
            
            body.menu-active .back-to-contas {
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }
        }
        
        @media (max-width: 375px) {
            .back-to-contas .back-text {
                display: none;
            }
            
            .back-to-contas {
                padding: 0.5rem;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .back-to-contas .back-icon {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Botão de voltar para contas -->
    <a href="contas.php" class="back-to-contas">
        <i class="bi bi-arrow-left back-icon"></i>
        <span class="back-text">Voltar para Contas</span>
    </a>

    <button class="sidebar-toggle btn" onclick="toggleSidebar()">
        <span class="icon-container"><i class="bi bi-list"></i></span>
    </button>

    <div class="d-flex">
        <!-- Sidebar -->
        <?php renderSidebar('contas', $_SESSION['userType'], $usuario['nome_completo'], $usuario['foto_perfil']); ?>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <div class="container-fluid p-4" style="padding-top: 2rem !important;">
                <!-- Cabeçalho da Página -->
                <div class="stock-header">
                    <div class="stock-title">
                        <i class="bi bi-person-plus"></i>
                        <h4>Nova Conta</h4>
                    </div>
                </div>

                <!-- Conteúdo da Página -->
                <div class="mb-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card-selection" id="tecnicoCard" onclick="selectAccountType('tecnico')">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="icon-wrapper mb-3">
                                        <i class="bi bi-tools"></i>
                                    </div>
                                    <span>Suporte Técnico</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card-selection" id="usuarioCard" onclick="selectAccountType('usuario')">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="icon-wrapper mb-3">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <span>Comunidade Escolar</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulário de Criação de Conta -->
                <div class="credenciais-section">
                    <h5><i class="bi bi-shield-lock me-2"></i>Informações de Conta</h5>
                    <form id="novaContaForm" autocomplete="off">
                        <div class="row g-3">
                            <div class="col-md-6 col-12">
                                <label for="nome" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control" id="nome" name="nome" required autocomplete="off">
                            </div>
                            <div class="col-md-6 col-12">
                                <label for="email" class="form-label">Email Institucional</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="email" name="email" required autocomplete="off">
                                    <span class="input-group-text">@<?= htmlspecialchars(obterConfig('APP_EMAIL_DOMAIN')) ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Campos específicos para Técnico -->
                        <div id="tecnicoFields" style="display: none;">
                            <div class="row g-3 mt-3">
                                <div class="col-md-6 col-12">
                                    <label for="matricula" class="form-label">Nº Processo</label>
                                    <input type="text" class="form-control" id="matricula" name="matricula" required>
                                </div>
                            </div>
                        </div>

                        <!-- Campos específicos para Comunidade Escolar -->
                        <div id="usuarioFields" style="display: none;">
                            <div class="row g-3 mt-3">
                                <div class="col-md-6 col-12">
                                    <label for="cargo" class="form-label">Cargo/Função</label>
                                    <select class="form-select" id="cargo" name="cargo" required>
                                        <option value="" selected>Selecione o cargo</option>
                                        <option value="professor">Professor</option>
                                        <option value="aluno">Aluno</option>
                                        <option value="funcionario">Funcionário</option>
                                    </select>
                                </div>
                                <div class="col-md-6 col-12">
                                    <label for="matriculaUsuario" class="form-label">Nº Processo</label>
                                    <input type="text" class="form-control" id="matriculaUsuario" name="matriculaUsuario" required>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex flex-wrap justify-content-start gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Salvar Conta
                            </button>
                            <a href="contas.php" class="btn btn-outline-danger">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Controle do botão "Voltar para Contas"
            const backButton = document.querySelector('.back-to-contas');
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
            
            // Ajustar layout baseado no tamanho da tela inicial
            const mainContent = document.querySelector('.main-content');
            if (window.innerWidth <= 768) {
                mainContent.style.marginLeft = '0';
                mainContent.style.width = '100%';
            } else {
                mainContent.style.marginLeft = '300px';
                mainContent.style.width = 'calc(100% - 300px)';
            }
            
            // Selecionar tipo de conta técnico por padrão
            selectAccountType('tecnico');
            
            // Validação do campo de email
            const emailInput = document.getElementById('email');
            emailInput.addEventListener('input', function(e) {
                // Remover @ e qualquer caractere após ele
                if (e.target.value.includes('@')) {
                    e.target.value = e.target.value.split('@')[0];
                }
                
                // Permitir apenas caracteres válidos para username
                e.target.value = e.target.value.replace(/[^a-zA-Z0-9._-]/g, '');
                
                // Se estiver no modo comunidade escolar, sincroniza com o número de processo
                if (document.getElementById('usuarioCard').classList.contains('active')) {
                    document.getElementById('matriculaUsuario').value = e.target.value;
                }
            });
            
            // Adicionando sincronização do número de processo com email para comunidade escolar
            const matriculaUsuarioInput = document.getElementById('matriculaUsuario');
            matriculaUsuarioInput.addEventListener('input', function(e) {
                // Se estiver no modo comunidade escolar, sincroniza com o email
                if (document.getElementById('usuarioCard').classList.contains('active')) {
                    // Limitar caracteres válidos
                    e.target.value = e.target.value.replace(/[^a-zA-Z0-9._-]/g, '');
                    // Sincronizar com o email
                    document.getElementById('email').value = e.target.value;
                }
            });
            
            // Função para ajustar o botão de toggle com base no tamanho da tela
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
                const mainContent = document.querySelector('.main-content');
                
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
                const mainContent = document.querySelector('.main-content');
                const sidebar = document.querySelector('.sidebar');
                
                if (window.innerWidth > 768) {
                    document.querySelector('.sidebar').classList.remove('active');
                    document.body.classList.remove('menu-active');
                    const overlay = document.querySelector('.sidebar-overlay');
                    if (overlay) overlay.remove();
                    
                    // Ajusta o main content para telas grandes
                    mainContent.style.marginLeft = '300px';
                    mainContent.style.width = 'calc(100% - 300px)';
                } else {
                    // Ajusta o main content para telas pequenas
                    mainContent.style.marginLeft = '0';
                    mainContent.style.width = '100%';
                }
            });
            
            // Configurar o formulário
            document.getElementById('novaContaForm').addEventListener('submit', function(e) {
                e.preventDefault();
                submitForm();
            });
        });
        
        function selectAccountType(type) {
            document.getElementById('tecnicoCard').classList.remove('active');
            document.getElementById('usuarioCard').classList.remove('active');
            document.getElementById(`${type}Card`).classList.add('active');
            
            // Limpar qualquer validação anterior
            const form = document.getElementById('novaContaForm');
            form.classList.remove('was-validated');
            
            // Resetar obrigatoriedade dos campos que não estão visíveis
            if (type === 'tecnico') {
                // Técnico ativo - desativar campos de comunidade
                document.getElementById('matricula').setAttribute('required', 'required');
                document.getElementById('cargo').removeAttribute('required');
                document.getElementById('matriculaUsuario').removeAttribute('required');
                
                // Campos visíveis/invisíveis
                document.getElementById('tecnicoFields').style.display = 'block';
                document.getElementById('usuarioFields').style.display = 'none';
                
                // Limpar valores
                document.getElementById('email').value = '';
                document.getElementById('matricula').value = '';
            } else {
                // Comunidade ativa - desativar campos de técnico
                document.getElementById('matricula').removeAttribute('required');
                document.getElementById('cargo').setAttribute('required', 'required');
                document.getElementById('matriculaUsuario').setAttribute('required', 'required');
                
                // Campos visíveis/invisíveis
                document.getElementById('tecnicoFields').style.display = 'none';
                document.getElementById('usuarioFields').style.display = 'block';
                
                // Limpar valores
                document.getElementById('email').value = '';
                document.getElementById('matriculaUsuario').value = '';
                document.getElementById('cargo').value = '';
            }
        }

        function cargoChanged() {
            // Function simplified since we removed disciplina and curso fields
        }

        function submitForm() {
            // Validar formulário
            const form = document.getElementById('novaContaForm');
            
            // Ativar validação do Bootstrap
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Mostrar mensagem de erro
                Toastify({
                    text: "Por favor, preencha todos os campos obrigatórios.",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    stopOnFocus: true
                }).showToast();
                
                // Adicionar classe para exibir validações
                form.classList.add('was-validated');
                return;
            }
            
            // Determinar tipo de conta selecionado
            const isTecnico = document.getElementById('tecnicoCard').classList.contains('active');
            const tipo = isTecnico ? 'tecnico' : 'usuario';
            
            // Construir FormData
            const formData = new FormData(form);
            
            // Adicionar o domínio ao email
            const emailInput = document.getElementById('email').value;
            formData.set('email', emailInput + <?= json_encode('@' . obterConfig('APP_EMAIL_DOMAIN')) ?>);
            
            formData.append('tipo', tipo);
            
            // Enviar dados para o servidor
            fetch('nova-conta.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Toastify({
                        text: data.message,
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                        stopOnFocus: true,
                        callback: function() { window.location.href = 'contas.php'; }
                    }).showToast();
                    
                    // Redirecionar após um pequeno delay
                    setTimeout(() => {
                        window.location.href = 'contas.php';
                    }, 2000);
                } else {
                    Toastify({
                        text: data.message,
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
                        stopOnFocus: true
                    }).showToast();
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: "Erro ao processar o pedido. Tente novamente.",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    stopOnFocus: true
                }).showToast();
            });
        }
    </script>
</body>
</html>