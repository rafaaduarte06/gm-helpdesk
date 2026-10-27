<?php
session_start();
require_once('../ligacao_base_dados.php');
require_once('../includes/sidebar.php');

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

// Verificar se foi fornecido um ID válido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: salas.php');
    exit;
}

$salaId = $_GET['id'];

// Buscar informações da sala
$sql = "SELECT * FROM salas WHERE id_sala = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$salaId]);
$sala = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sala) {
    header('Location: salas.php');
    exit;
}

// Arrays para mapear tabelas e campos
$componentesInfo = [
    'COMPUTADOR' => ['tabela' => 'computadores', 'id' => 'COMP_ID', 'ref' => 'COMP_REF', 'estado' => 'COMP_ESTADO', 'sala' => 'COMP_SALA', 'icon' => 'bi-pc-display'],
    'RATO' => ['tabela' => 'ratos', 'id' => 'RATOS_ID', 'ref' => 'RATOS_REF', 'estado' => 'RATOS_ESTADO', 'sala' => 'RATOS_SALA', 'comp' => 'RATOS_COMP', 'icon' => 'bi-mouse'],
    'TECLADO' => ['tabela' => 'teclados', 'id' => 'TECLADO_ID', 'ref' => 'TECLADO_REF', 'estado' => 'TECLADO_ESTADO', 'sala' => 'TECLADO_SALA', 'comp' => 'TECLADO_COMP', 'icon' => 'bi-keyboard'],
    'MONITOR' => ['tabela' => 'monitor', 'id' => 'MONITOR_ID', 'ref' => 'MONITOR_REF', 'estado' => 'MONITOR_ESTADO', 'sala' => 'MONITOR_SALA', 'comp' => 'MONITOR_COMP', 'icon' => 'bi-display']
];

// Função para obter a classe de estado
function getEstadoClass($estado) {
    switch ($estado) {
        case '1': return 'componente-disponivel';
        case '3': return 'componente-manutencao';
        case '2': return 'componente-em-uso';
        case '4': return 'componente-sem-reparo';
        default: return '';
    }
}

// Função para obter o texto do estado
function getEstadoText($estado) {
    switch ($estado) {
        case '1': return 'Disponível';
        case '3': return 'Em Manutenção';
        case '2': return 'Em Uso';
        case '4': return 'Sem Reparo';
        default: return 'Desconhecido';
    }
}

// Função para obter a classe do badge de estado
function getEstadoBadgeClass($estado) {
    switch ($estado) {
        case '1': return 'estado-disponivel';
        case '3': return 'estado-manutencao';
        case '2': return 'estado-em-uso';
        case '4': return 'estado-sem-reparo';
        default: return '';
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Sala - GM HelpDesk</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="../computadores/funcoes/componentes.css">
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

        /* Conteúdo principal */
        .main-content {
            margin-left: 300px;
            width: calc(100% - 300px);
            transition: all 0.3s ease;
            overflow-x: hidden;
            min-height: 100vh;
            padding: 2rem;
        }

        .details-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            padding: 0;
            margin-bottom: 2rem;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .details-card:hover {
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }

        .room-header-banner {
            background: linear-gradient(135deg, #2a4d69, rgb(36, 110, 167));
            color: white;
            padding: 2.5rem 2.2rem 2.2rem 2.2rem;
            position: relative;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1.3rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .room-header-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect x="0" y="0" width="100" height="100" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="2"/></svg>');
            background-size: 20px 20px;
            opacity: 0.3;
            z-index: 0;
        }

        .header-title-area {
            position: relative;
            z-index: 1;
            max-width: 40%;
        }

        .header-title-area h1 {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.3rem;
            text-transform: none;
            letter-spacing: 0.5px;
            margin: 0;
            color: white;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
            margin-top: 0.25rem;
        }

        .room-header-info {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1rem;
            position: relative;
            z-index: 1;
            width: 30%;
        }

        .room-id-container {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            background: rgba(255, 255, 255, 0.15);
            padding: 0.8rem 1.3rem;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .room-id-container:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.2);
        }

        .room-id-container i {
            font-size: 1.8rem;
            color: white;
        }

        .room-id {
            font-family: 'Courier New', monospace;
            font-size: 1.6rem;
            font-weight: 700;
            color: white;
            letter-spacing: 1px;
            margin: 0;
        }

        .room-details-main {
            padding: 2.5rem;
        }

        .back-button {
            position: absolute;
            top: 1.8rem;
            right: 1.3rem;
            cursor: pointer;
            padding: 0.45rem 1rem;
            border-radius: 22px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 0;
            z-index: 10;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        .back-button i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .btn-action {
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            min-width: 160px;
            height: 45px;
        }

        .btn-add {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: white;
        }

        .btn-edit {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }

        .btn-add:hover {
            background: linear-gradient(135deg, #0b5ed7, #094bac);
        }

        .btn-edit:hover {
            background: linear-gradient(135deg, #5a6268, #495057);
        }

        .btn-action i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .section-title {
            position: relative;
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1.8rem;
            padding-bottom: 1rem;
            border-bottom: none;
            display: inline-block;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, #0d6efd, rgba(13, 110, 253, 0.5));
            border-radius: 2px;
        }

        .component-box {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            padding: 20px;
            margin-bottom: 25px;
            display: flex;
            flex-direction: column;
            transition: all 0.3s;
            border-left: 5px solid transparent;
            overflow: hidden;
            height: 100%;
        }

        .component-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .component-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .component-icon {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.1), rgba(13, 110, 253, 0.2));
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.4rem;
            color: #0d6efd;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.15);
        }

        .component-box:hover .component-icon {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
        }

        .component-title {
            font-weight: 700;
            font-size: 1.15rem;
            color: #2c3e50;
            margin: 0;
            flex: 1;
        }

        .component-ref {
            font-family: 'Courier New', monospace;
            background-color: rgba(0, 0, 0, 0.05);
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #495057;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* Modal para adicionar componentes */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-bottom: none;
            padding: 25px 25px 15px;
        }

        .modal-body {
            padding: 25px 25px;
        }

        .modal-footer {
            border-top: none;
            padding: 15px 25px 25px;
            justify-content: space-between;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        }

        .modal-title {
            font-weight: 700;
            color: #2c3e50;
            font-size: 1.4rem;
        }

        /* Estilos específicos para estados de componentes */
        .componente-disponivel {
            border-left-color: #198754;
        }

        .componente-sem-reparo {
            border-left-color: #dc3545;
        }

        .componente-manutencao {
            border-left-color: #ffc107;
        }

        .componente-em-uso {
            border-left-color: #0d6efd;
        }

        .estado-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .estado-disponivel {
            background-color: rgba(25, 135, 84, 0.15);
            color: #198754;
            border: 1px solid rgba(25, 135, 84, 0.3);
        }

        .estado-sem-reparo {
            background-color: rgba(220, 53, 69, 0.15);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .estado-manutencao {
            background-color: rgba(255, 193, 7, 0.15);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }

        .estado-em-uso {
            background-color: rgba(13, 110, 253, 0.15);
            color: #0d6efd;
            border: 1px solid rgba(13, 110, 253, 0.3);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
        }

        .btn-outline-primary {
            border: 2px solid #0d6efd;
            color: #0d6efd;
        }

        .btn-outline-danger {
            border: 2px solid #dc3545;
            color: #dc3545;
        }

        .btn-sm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
        }

        .btn-outline-primary:hover {
            background-color: #0d6efd;
            color: white;
        }

        .btn-outline-danger:hover {
            background-color: #dc3545;
            color: white;
        }

        .alert {
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .alert-success {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
            border-left: 4px solid #198754;
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }

        .alert-info {
            background-color: rgba(13, 110, 253, 0.08);
            color: #0d6efd;
            border-left: 4px solid #0d6efd;
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 250px;
            }
            
            .main-content {
                margin-left: 250px;
                width: calc(100% - 250px);
                padding: 1.5rem;
            }

            .room-header-banner {
                padding: 2rem;
            }

            .header-title-area h1 {
                font-size: 1.8rem;
            }

            .room-id-container {
                padding: 0.8rem 1.2rem;
            }

            .room-id {
                font-size: 1.5rem;
            }

            .room-details-main {
                padding: 1.8rem;
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
                padding: 1rem;
                padding-top: 60px;
                transition: all 0.3s ease;
            }
            
            .sidebar-toggle {
                display: block !important;
            }

            .room-header-banner {
                padding: 1.5rem;
                flex-direction: column;
                padding-top: 5rem; /* Espaço para o botão de voltar */
            }

            .header-title-area {
                max-width: 100%;
                text-align: center;
                margin-bottom: 1rem;
            }

            .header-title-area h1 {
                font-size: 1.6rem;
            }

            .room-header-info {
                margin: 0 auto;
                margin-right: 0;
            }
            
            .back-button {
                top: 1rem;
                left: 1rem;
            }
            
            .action-buttons {
                top: 1rem;
                right: 1rem;
            }

            .room-details-main {
                padding: 1.5rem;
            }

            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
            }

            .d-flex.gap-3 {
                width: 100%;
                justify-content: center;
            }
        }

        .action-buttons {
            position: absolute;
            top: 5.3rem;
            right: 1.9rem;
            display: flex;
            gap: 0.7rem;
            z-index: 10;
            flex-direction: row;
        }

        .action-button {
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .action-button:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        .action-button.edit-btn {
            background: rgba(52, 152, 219, 0.5);
        }

        .action-button.add-btn {
            background: rgba(39, 174, 96, 0.7);
        }
    </style>
</head>
<body>
    <button class="sidebar-toggle btn" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>

    <div class="d-flex">
        <!-- Sidebar -->
        <?php 
        $userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : 'tecnico';
        $userName = isset($usuario['nome_completo']) ? $usuario['nome_completo'] : 'Usuário';
        $userPhoto = isset($usuario['foto_perfil']) ? $usuario['foto_perfil'] : '';
        renderSidebar('salas', $userType, $userName, $userPhoto);
        ?>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <div class="details-card">
                <div class="room-header-banner">
                    <div class="header-title-area">
                        <h1>Detalhes da Sala</h1>
                        <div class="page-subtitle">Informações e componentes da sala</div>
                    </div>
                    <div class="room-header-info">
                        <div class="room-id-container">
                            <i class="bi bi-building"></i>
                            <h2 class="room-id"><?= htmlspecialchars($sala['sala']) ?></h2>
                        </div>
                    </div>
                    <a href="salas.php" class="back-button">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                    <div class="action-buttons">
                        <button class="action-button add-btn" title="Adicionar Componente" data-bs-toggle="modal" data-bs-target="#addComponentModal">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                        <button class="action-button edit-btn" title="Editar Sala" data-bs-toggle="modal" data-bs-target="#editRoomModal">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                    </div>
                </div>

                <div class="room-details-main">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['success_message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['error_message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>

                    <!-- Seção de Computadores -->
                    <h4 class="section-title">Computadores na Sala</h4>
                    <div class="row">
                        <?php
                        // Buscar computadores na sala
                        $stmt = $pdo->prepare("SELECT c.* 
                            FROM computadores c 
                            WHERE c.COMP_SALA = ? AND c.COMP_ESTADO = '2'");
                        $stmt->execute([$sala['sala']]);
                        $computadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($computadores) > 0) {
                            foreach ($computadores as $computador) {
                                $estadoClass = getEstadoClass($computador['COMP_ESTADO']);
                                $estadoBadgeClass = getEstadoBadgeClass($computador['COMP_ESTADO']);
                                ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="component-box <?= $estadoClass ?>">
                                        <div class="component-header">
                                            <div class="component-icon">
                                                <i class="bi bi-pc-display"></i>
                                            </div>
                                            <h5 class="component-title">Computador</h5>
                                            <div class="component-ref"><?= htmlspecialchars($computador['COMP_REF']) ?></div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="estado-badge <?= $estadoBadgeClass ?>">
                                                <?= getEstadoText($computador['COMP_ESTADO']) ?>
                                            </span>
                                        </div>
                                        <div class="mt-auto pt-3">
                                            <a href="../computadores/detalhespc.php?id=<?= urlencode($computador['COMP_ID']) ?>" class="btn btn-sm btn-outline-primary mt-2">
                                                Ver Detalhes
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger mt-2 ms-2" data-bs-toggle="modal" data-bs-target="#removeComponentModal" 
                                                data-tipo="COMPUTADOR" 
                                                data-id="<?= htmlspecialchars($computador['COMP_ID']) ?>" 
                                                data-ref="<?= htmlspecialchars($computador['COMP_REF']) ?>">
                                                Remover da Sala
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<div class="col-12"><div class="alert alert-info">Nenhum computador nesta sala.</div></div>';
                        }
                        ?>
                    </div>

                    <!-- Seção de Ratos individuais (não em computadores) -->
                    <h4 class="section-title mt-4">Ratos na Sala</h4>
                    <div class="row">
                        <?php
                        // Buscar ratos individuais na sala (não associados a computadores)
                        $stmt = $pdo->prepare("SELECT r.* 
                            FROM ratos r 
                            WHERE r.RATOS_SALA = ? AND r.RATOS_COMP = '' AND r.RATOS_ESTADO = '2'");
                        $stmt->execute([$sala['sala']]);
                        $ratos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($ratos) > 0) {
                            foreach ($ratos as $rato) {
                                $estadoClass = getEstadoClass($rato['RATOS_ESTADO']);
                                $estadoBadgeClass = getEstadoBadgeClass($rato['RATOS_ESTADO']);
                                ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="component-box <?= $estadoClass ?>">
                                        <div class="component-header">
                                            <div class="component-icon">
                                                <i class="bi bi-mouse"></i>
                                            </div>
                                            <h5 class="component-title">Rato</h5>
                                            <div class="component-ref"><?= htmlspecialchars($rato['RATOS_REF']) ?></div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="estado-badge <?= $estadoBadgeClass ?>">
                                                <?= getEstadoText($rato['RATOS_ESTADO']) ?>
                                            </span>
                                        </div>
                                        <div class="mt-auto pt-3">
                                            <button class="btn btn-sm btn-outline-danger mt-2" data-bs-toggle="modal" data-bs-target="#removeComponentModal" 
                                                data-tipo="RATO" 
                                                data-id="<?= htmlspecialchars($rato['RATOS_ID']) ?>" 
                                                data-ref="<?= htmlspecialchars($rato['RATOS_REF']) ?>">
                                                Remover da Sala
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<div class="col-12"><div class="alert alert-info">Nenhum rato individual nesta sala.</div></div>';
                        }
                        ?>
                    </div>

                    <!-- Seção de Teclados individuais (não em computadores) -->
                    <h4 class="section-title mt-4">Teclados na Sala</h4>
                    <div class="row">
                        <?php
                        // Buscar teclados individuais na sala (não associados a computadores)
                        $stmt = $pdo->prepare("SELECT t.* 
                            FROM teclados t 
                            WHERE t.TECLADO_SALA = ? AND t.TECLADO_COMP = '' AND t.TECLADO_ESTADO = '2'");
                        $stmt->execute([$sala['sala']]);
                        $teclados = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($teclados) > 0) {
                            foreach ($teclados as $teclado) {
                                $estadoClass = getEstadoClass($teclado['TECLADO_ESTADO']);
                                $estadoBadgeClass = getEstadoBadgeClass($teclado['TECLADO_ESTADO']);
                                ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="component-box <?= $estadoClass ?>">
                                        <div class="component-header">
                                            <div class="component-icon">
                                                <i class="bi bi-keyboard"></i>
                                            </div>
                                            <h5 class="component-title">Teclado</h5>
                                            <div class="component-ref"><?= htmlspecialchars($teclado['TECLADO_REF']) ?></div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="estado-badge <?= $estadoBadgeClass ?>">
                                                <?= getEstadoText($teclado['TECLADO_ESTADO']) ?>
                                            </span>
                                        </div>
                                        <div class="mt-auto pt-3">
                                            <button class="btn btn-sm btn-outline-danger mt-2" data-bs-toggle="modal" data-bs-target="#removeComponentModal" 
                                                data-tipo="TECLADO" 
                                                data-id="<?= htmlspecialchars($teclado['TECLADO_ID']) ?>" 
                                                data-ref="<?= htmlspecialchars($teclado['TECLADO_REF']) ?>">
                                                Remover da Sala
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<div class="col-12"><div class="alert alert-info">Nenhum teclado individual nesta sala.</div></div>';
                        }
                        ?>
                    </div>

                    <!-- Seção de Monitores individuais (não em computadores) -->
                    <h4 class="section-title mt-4">Monitores na Sala</h4>
                    <div class="row">
                        <?php
                        // Buscar monitores individuais na sala (não associados a computadores)
                        $stmt = $pdo->prepare("SELECT m.* 
                            FROM monitor m 
                            WHERE m.MONITOR_SALA = ? AND m.MONITOR_COMP = '' AND m.MONITOR_ESTADO = '2'");
                        $stmt->execute([$sala['sala']]);
                        $monitores = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if (count($monitores) > 0) {
                            foreach ($monitores as $monitor) {
                                $estadoClass = getEstadoClass($monitor['MONITOR_ESTADO']);
                                $estadoBadgeClass = getEstadoBadgeClass($monitor['MONITOR_ESTADO']);
                                ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="component-box <?= $estadoClass ?>">
                                        <div class="component-header">
                                            <div class="component-icon">
                                                <i class="bi bi-display"></i>
                                            </div>
                                            <h5 class="component-title">Monitor</h5>
                                            <div class="component-ref"><?= htmlspecialchars($monitor['MONITOR_REF']) ?></div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="estado-badge <?= $estadoBadgeClass ?>">
                                                <?= getEstadoText($monitor['MONITOR_ESTADO']) ?>
                                            </span>
                                        </div>
                                        <div class="mt-auto pt-3">
                                            <button class="btn btn-sm btn-outline-danger mt-2" data-bs-toggle="modal" data-bs-target="#removeComponentModal" 
                                                data-tipo="MONITOR" 
                                                data-id="<?= htmlspecialchars($monitor['MONITOR_ID']) ?>" 
                                                data-ref="<?= htmlspecialchars($monitor['MONITOR_REF']) ?>">
                                                Remover da Sala
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<div class="col-12"><div class="alert alert-info">Nenhum monitor individual nesta sala.</div></div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php
    // Include the modals
    require_once('sala_modals.php');
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const body = document.body;
            
            sidebar.classList.toggle('active');
            
            if (sidebar.classList.contains('active')) {
                body.classList.add('menu-active');
                
                // Cria e adiciona o overlay
                const overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                overlay.style.position = 'fixed';
                overlay.style.top = '0';
                overlay.style.left = '0';
                overlay.style.width = '100%';
                overlay.style.height = '100%';
                overlay.style.background = 'rgba(0,0,0,0.7)';
                overlay.style.zIndex = '1000';
                overlay.style.transition = 'all 0.3s ease';
                document.body.appendChild(overlay);
                
                // Evento de clique para o overlay
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    body.classList.remove('menu-active');
                    this.remove();
                });
            } else {
                body.classList.remove('menu-active');
                
                const overlay = document.querySelector('.sidebar-overlay');
                if (overlay) overlay.remove();
            }
        }
    </script>
</body>
</html> 