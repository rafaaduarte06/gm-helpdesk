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
    header('Location: inventariopc.php');
    exit;
}

$compId = $_GET['id'];

// Buscar informações do computador
$sql = "SELECT c.*
        FROM computadores c 
        WHERE c.COMP_ID = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$compId]);
$computador = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$computador) {
    header('Location: inventariopc.php');
    exit;
}

// Arrays para mapear tabelas e campos
$componentesInfo = [
    'RATO' => ['tabela' => 'ratos', 'id' => 'RATOS_ID', 'ref' => 'RATOS_REF', 'estado' => 'RATOS_ESTADO', 'icon' => 'bi-mouse'],
    'TECLADO' => ['tabela' => 'teclados', 'id' => 'TECLADO_ID', 'ref' => 'TECLADO_REF', 'estado' => 'TECLADO_ESTADO', 'icon' => 'bi-keyboard'],
    'MONITOR' => ['tabela' => 'monitor', 'id' => 'MONITOR_ID', 'ref' => 'MONITOR_REF', 'estado' => 'MONITOR_ESTADO', 'icon' => 'bi-display'],
    'PM' => ['tabela' => 'placamae', 'id' => 'PM_ID', 'ref' => 'PM_REF', 'estado' => 'PM_ESTADO', 'icon' => 'bi-motherboard'],
    'PROC' => ['tabela' => 'processador', 'id' => 'PROC_ID', 'ref' => 'PROC_REF', 'estado' => 'PROC_ESTADO', 'icon' => 'bi-cpu'],
    'COOLER' => ['tabela' => 'cooler', 'id' => 'COO_ID', 'ref' => 'COO_REF', 'estado' => 'COO_ESTADO', 'icon' => 'bi-fan'],
    'PG' => ['tabela' => 'placagrafica', 'id' => 'PG_ID', 'ref' => 'PG_REF', 'estado' => 'PG_ESTADO', 'icon' => 'bi-gpu-card'],
    'DISCO' => ['tabela' => 'disco', 'id' => 'DISCO_ID', 'ref' => 'DISCO_REF', 'estado' => 'DISCO_ESTADO', 'icon' => 'bi-hdd'],
    'FONTE' => ['tabela' => 'fonte', 'id' => 'FONTE_ID', 'ref' => 'FONTE_REF', 'estado' => 'FONTE_ESTADO', 'icon' => 'bi-lightning'],
    'MEMORIAR' => ['tabela' => 'memoriaram', 'id' => 'MR_ID', 'ref' => 'MR_REF', 'estado' => 'MR_ESTADO', 'icon' => 'bi-memory'],
    'DRIVEDVD' => ['tabela' => 'drivedvd', 'id' => 'DRVD_ID', 'ref' => 'DRVD_REF', 'estado' => 'DRVD_ESTADO', 'icon' => 'bi-disc'],
    'PR' => ['tabela' => 'placaderede', 'id' => 'PR_ID', 'ref' => 'PR_REF', 'estado' => 'PR_ESTADO', 'icon' => 'bi-ethernet'],
    'VENTOINHA' => ['tabela' => 'ventoinha', 'id' => 'VENT_ID', 'ref' => 'VENT_REF', 'estado' => 'VENT_ESTADO', 'icon' => 'bi-fan'],
    'CAIXA' => ['tabela' => 'caixa', 'id' => 'CAIXA_ID', 'ref' => 'CAIXA_REF', 'estado' => 'CAIXA_ESTADO', 'icon' => 'bi-box']
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

// Função para obter o nome formatado do componente
function getComponentName($tipo) {
    switch (strtoupper($tipo)) {
        case 'PM': return 'MOTHER BOARD';
        case 'PROC': return 'PROCESSADOR';
        case 'PG': return 'PLACA GRÁFICA';
        case 'MEMORIAR': return 'MEMÓRIA RAM';
        case 'PR': return 'PLACA REDE';
        case 'RATO': return 'RATO';
        case 'TECLADO': return 'TECLADO';
        case 'MONITOR': return 'MONITOR';
        case 'COOLER': return 'COOLER';
        case 'DISCO': return 'DISCO';
        case 'FONTE': return 'FONTE';
        case 'VENTOINHA': return 'VENTOINHA';
        case 'CAIXA': return 'CAIXA';
        case 'DRIVEDVD': return 'DRIVEDVD';
        default: return strtoupper(str_replace(['COMP_', '_'], ['', ' '], $tipo));
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Computador - GM HelpDesk</title>
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
        }

        .details-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            padding: 0;
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .computer-header-banner {
            background: linear-gradient(135deg, #2a4d69,rgb(36, 110, 167));
            color: white;
            padding: 2.5rem 2rem 2rem 2rem;
            position: relative;
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            flex-wrap: wrap;
            gap: 1.5rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        /* Special styling for Sem Reparo computers */
        .computer-header-banner.sem-reparo-header {
            background: linear-gradient(135deg, #7a1414, #e74c3c);
        }

        /* Special styling for Disponível computers */
        .computer-header-banner.disponivel-header {
            background: linear-gradient(135deg, #0f5132,rgb(5, 161, 65));
        }

        /* Special styling for Manutenção computers */
        .computer-header-banner.manutencao-header {
            background: linear-gradient(135deg, #856404, #ffc107);
        }

        .header-title-area {
            max-width: 60%;
        }

        .computer-header-info {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-left: auto;
            margin-right: 100px;
        }

        .computer-ref-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(255, 255, 255, 0.15);
            padding: 0.8rem 1.2rem;
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }

        .computer-ref-container i {
            font-size: 1.8rem;
            color: white;
        }

        .computer-ref {
            font-family: monospace;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            letter-spacing: 1px;
            margin: 0;
        }

        .computer-details-main {
            padding: 2rem;
        }

        .page-title {
            margin: 0;
            font-weight: 700;
            color: white;
            font-size: 1.8rem;
            letter-spacing: 0.5px;
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
            margin-top: 0.3rem;
        }

        .computer-status-pill {
            position: relative;
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem 0.5rem 0.8rem;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.85rem;
            gap: 6px;
            color: white;
        }

        .status-disponivel {
            background-color: #27ae60;
        }

        .status-manutencao {
            background-color: #f39c12;
        }

        .status-em-uso {
            background-color: #3498db;
            border: 2px solid #1c6ea4;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .status-sem-reparo {
            background-color: #a93226;
            border: 2px solid #570000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: white;
            display: inline-block;
        }

        .action-buttons {
            position: absolute;
            top: 3.4rem;
            right: 1.5rem;
            display: flex;
            gap: 0.8rem;
            z-index: 10;
            flex-direction: column;
        }

        .action-button {
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
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
        
        /* Special color for edit button on Disponível (Available) computers */
        .disponivel-header .action-button.edit-btn {
            background: rgba(39, 174, 96, 0.7);
        }
        
        /* Special color for edit button on Manutenção (Maintenance) computers */
        .manutencao-header .action-button.edit-btn {
            background: rgba(243, 156, 18, 0.7);
        }

        .action-button.delete-btn {
            background: rgba(231, 76, 60, 0.5);
        }

        .components-section {
            margin-top: 1rem;
        }

        .components-section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .components-section-title i {
            color: #0d6efd;
        }

        .components-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .component-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .component-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .component-icon {
            width: 46px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 8px;
            font-size: 1.4rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
        }

        .component-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .component-name {
            font-weight: 700;
            color: #333;
            font-size: 0.95rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
            line-height: 1.3;
        }

        .component-ref {
            font-size: 0.95rem;
            margin: 0;
            font-weight: 700;
            color: #333;
        }

        .component-ref.text-muted {
            font-weight: 400;
            font-style: italic;
            color: #6c757d;
        }

        .component-status {
            font-size: 0.77rem;
            padding: 4px 10px;
            border-radius: 15px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
            margin-top: 4px;
            width: fit-content;
        }

        .component-status i {
            font-size: 0.6rem;
        }

        /* Cores para os componentes de acordo com o estado */
        .componente-disponivel i.component-icon-inner {
            color: #198754;
        }

        .componente-manutencao i.component-icon-inner {
            color: rgb(219, 164, 0);
        }

        .componente-em-uso i.component-icon-inner {
            color: #0d6efd;
        }

        .componente-sem-reparo i.component-icon-inner {
            color: #dc3545;
        }

        /* Borda esquerda colorida para os componentes */
        .component-card.componente-disponivel {
            border-left-color: #198754;
        }

        .component-card.componente-manutencao {
            border-left-color: rgb(219, 164, 0);
        }

        .component-card.componente-em-uso {
            border-left-color: #0d6efd;
        }

        .component-card.componente-sem-reparo {
            border-left-color: #dc3545;
        }

        /* Borda colorida para os status de componentes */
        .component-status.componente-disponivel {
            background-color: rgba(25, 135, 84, 0.1);
            border: 1px solid #198754;
            color: #198754;
        }

        .component-status.componente-manutencao {
            background-color: rgba(219, 164, 0, 0.1);
            border: 1px solid rgb(219, 164, 0);
            color: rgb(219, 164, 0);
        }

        .component-status.componente-em-uso {
            background-color: rgba(13, 110, 253, 0.1);
            border: 1px solid #0d6efd;
            color: #0d6efd;
        }

        .component-status.componente-sem-reparo {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid #dc3545;
            color: #dc3545;
        }

        /* Estilo redesenhado para o botão voltar */
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

        @media (max-width: 768px) {
            .computer-header-banner {
                flex-direction: column;
                padding: 1.5rem;
            }
            
            .computer-header-info {
                margin-top: 1rem;
                width: 100%;
                justify-content: space-between;
                margin-right: 0;
            }
            
            .action-buttons {
                position: static;
                margin-top: 1.5rem;
                align-self: flex-end;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .computer-ref {
                font-size: 1.3rem;
            }
        }

        .component-card.no-component {
            opacity: 0.7;
            border-left-color: #adb5bd;
        }

        .component-card.no-component .component-icon i {
            color: #adb5bd;
        }

        .component-card.no-component .component-ref {
            font-style: italic;
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
            
            .components-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
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
                margin-left: 0;
                width: 100%;
                padding-top: 60px;
                transition: all 0.3s ease;
            }
            
            .sidebar-toggle {
                display: block !important;
            }
            
            .computer-header-banner {
                flex-direction: column;
                padding: 1.5rem;
            }
            
            .computer-header-info {
                margin-top: 1rem;
                width: 100%;
                justify-content: space-between;
                margin-right: 0;
            }
            
            .action-buttons {
                flex-direction: row;
                position: relative;
                top: auto;
                right: auto;
                margin-top: 1.5rem;
                align-self: flex-end;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .computer-ref {
                font-size: 1.3rem;
            }
        }
        
        @media (max-width: 576px) {
            .computer-header-banner {
                padding: 1.2rem;
            }
            
            .computer-header-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .computer-ref-container, .computer-status-pill {
                width: 100%;
            }
            
            .computer-details-main {
                padding: 1.5rem 1rem;
            }
            
            .components-section-title {
                font-size: 1.1rem;
            }
            
            .component-card {
                padding: 0.75rem;
            }
            
            .action-buttons {
                width: 100%;
                justify-content: flex-end;
                gap: 10px;
            }
        }
        
        @media (max-width: 375px) {
            .computer-header-banner {
                padding: 1rem;
            }
            
            .page-title {
                font-size: 1.3rem;
            }
            
            .page-subtitle {
                font-size: 0.9rem;
            }
            
            .computer-ref {
                font-size: 1.1rem;
            }
            
            .computer-details-main {
                padding: 1rem;
            }
            
            .component-name {
                font-size: 0.85rem;
            }
            
            .component-ref {
                font-size: 0.85rem;
            }
            
            .component-icon {
                width: 38px;
                height: 38px;
                font-size: 1.2rem;
            }
            
            .component-status {
                font-size: 0.7rem;
                padding: 3px 8px;
            }
        }

        .component-actions {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-left: 8px;
        }
        
        .component-action {
            background: transparent;
            border: 1px solid;
            width: 28px;
            height: 28px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 0;
            font-size: 0.9rem;
        }
        
        .edit-comp-btn {
            color: #0d6efd;
            border-color: #0d6efd;
        }
        
        .edit-comp-btn:hover {
            background-color: rgba(13, 110, 253, 0.1);
            transform: translateY(-1px);
        }
        
        .delete-comp-btn {
            color: #dc3545;
            border-color: #dc3545;
        }
        
        .delete-comp-btn:hover {
            background-color: rgba(220, 53, 69, 0.1);
            transform: translateY(-1px);
        }
        
        .component-card {
            position: relative;
            justify-content: space-between;
        }

        .back-to-inventory .back-text {
            font-size: 0.95rem;
        }
        
        /* Badge para componentes sem reparo */
        .sem-reparo-badge {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            border: 1px solid rgba(220, 53, 69, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1.2;
            text-align: center;
            white-space: nowrap;
        }

        /* Enhanced style for Sem Reparo status pill to make it stand out */
        .sem-reparo-header .computer-status-pill.status-sem-reparo {
            background-color: #a93226;
            border: 2px solid #570000;
            box-shadow: 0 3px 5px rgba(121, 119, 119, 0.4);
            padding: 8px 15px;
            font-weight: 700;
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
                <!-- Card de Detalhes -->
                <div class="details-card">
                    <!-- Cabeçalho do Computador -->
                    <div class="computer-header-banner 
                        <?php if ($computador['COMP_ESTADO'] == '4' || $computador['COMP_ESTADO'] == '9'): ?>
                            sem-reparo-header
                        <?php elseif ($computador['COMP_ESTADO'] == '1'): ?>
                            disponivel-header
                        <?php elseif ($computador['COMP_ESTADO'] == '3'): ?>
                            manutencao-header
                        <?php endif; ?>">
                        <div class="header-title-area">
                            <h1 class="page-title">Detalhes do Computador</h1>
                            <div class="page-subtitle">Informações e componentes do sistema</div>
                        </div>
                        <div class="computer-header-info">
                            <div class="computer-ref-container">
                                <i class="bi bi-pc-display"></i>
                                <span class="computer-ref"><?php echo htmlspecialchars($computador['COMP_REF']); ?></span>
                            </div>
                            <?php if ($computador['COMP_ESTADO'] == '4' || $computador['COMP_ESTADO'] == '9'): // Sem Reparo ?>
                                <div class="computer-status-pill status-sem-reparo">
                                    <span class="status-indicator"></span>
                                    <span>SEM REPARO</span>
                                </div>
                            <?php else: ?>
                            <div class="computer-status-pill status-<?php echo strtolower(str_replace(['componente-', '-'], ['', ''], getEstadoClass($computador['COMP_ESTADO']))); ?>">
                                <span class="status-indicator"></span>
                                <span><?php echo getEstadoText($computador['COMP_ESTADO']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="action-buttons">
                            <?php /* Remove the edit button for computer reference and state as requested */ ?>
                        </div>
                    </div>

                    <!-- Conteúdo Principal -->
                    <div class="computer-details-main">
                        <!-- Grid de Componentes -->
                        <div class="components-section">
                            <div class="components-section-title">
                                <i class="bi bi-cpu" 
                                <?php if ($computador['COMP_ESTADO'] == '4' || $computador['COMP_ESTADO'] == '9'): ?>
                                    style="color: #dc3545;"
                                <?php elseif ($computador['COMP_ESTADO'] == '1'): ?>
                                    style="color: #198754;"
                                <?php elseif ($computador['COMP_ESTADO'] == '3'): ?>
                                    style="color: #ffc107;"
                                <?php else: ?>
                                    style="color: #0d6efd;"
                                <?php endif; ?>></i>
                                <span>Componentes</span>
                            </div>
                            <div class="components-grid">
                                <?php
                                foreach ($componentesInfo as $tipo => $info) {
                                    $componenteId = $computador['COMP_' . $tipo];
                                    $tipoFormatado = getComponentName($tipo);
                                    
                                    // Se não houver componente associado
                                    if (empty($componenteId)) {
                                        ?>
                                        <div class="component-card no-component">
                                            <div class="component-icon">
                                                <i class="bi <?php echo $info['icon']; ?> component-icon-inner"></i>
                                            </div>
                                            <div class="component-info">
                                                <div class="component-name">
                                                    <?php echo $tipoFormatado; ?>
                                                </div>
                                                <span class="component-ref text-muted"><?php echo $tipoFormatado; ?> Sem Componente</span>
                                            </div>
                                            <div class="component-actions">
                                                <?php if ($computador['COMP_ESTADO'] != '4' && $computador['COMP_ESTADO'] != '9'): // Não mostrar edição para "Sem Reparo" ?>
                                                <button class="component-action edit-comp-btn" title="Adicionar Componente" 
                                                    onclick="editarComponente('<?php echo htmlspecialchars($computador['COMP_ID']); ?>', '<?php echo $tipo; ?>')">
                                                    <i class="bi bi-plus-circle"></i>
                                                </button>
                                                <?php else: ?>
                                                <span class="sem-reparo-badge" title="Componentes não podem ser editados">Sem Reparo</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php
                                        continue;
                                    }

                                    // Se houver componente, buscar suas informações
                                    $stmt = $pdo->prepare("SELECT {$info['ref']}, {$info['estado']} FROM {$info['tabela']} WHERE {$info['id']} = ?");
                                    $stmt->execute([$componenteId]);
                                    $componente = $stmt->fetch(PDO::FETCH_ASSOC);

                                    if ($componente) {
                                        $estadoClass = getEstadoClass($componente[$info['estado']]);
                                        $estadoText = getEstadoText($componente[$info['estado']]);
                                        ?>
                                        <div class="component-card <?php echo $estadoClass; ?>">
                                            <div class="component-icon">
                                                <i class="bi <?php echo $info['icon']; ?> component-icon-inner"></i>
                                            </div>
                                            <div class="component-info">
                                                <div class="component-name">
                                                    <?php echo $tipoFormatado; ?> <span class="component-ref"><?php echo isset($componente[$info['ref']]) ? htmlspecialchars($componente[$info['ref']]) : ''; ?></span>
                                                </div>
                                                <div class="component-status <?php echo $estadoClass; ?>">
                                                    <i class="bi bi-circle-fill"></i>
                                                    <?php echo $estadoText; ?>
                                                </div>
                                            </div>
                                            <div class="component-actions">
                                                <?php if ($computador['COMP_ESTADO'] != '4' && $computador['COMP_ESTADO'] != '9'): // Não mostrar edição para "Sem Reparo" ?>
                                                <button class="component-action edit-comp-btn" title="Editar Componente" 
                                                    onclick="editarComponente('<?php echo $computador['COMP_ID']; ?>', '<?php echo $tipo; ?>', '<?php echo $componenteId; ?>')">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <?php endif; ?>
                                                <button class="component-action delete-comp-btn" title="Remover Componente" 
                                                    onclick="removerComponente('<?php echo $computador['COMP_ID']; ?>', '<?php echo $tipo; ?>', '<?php echo $componenteId; ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <?php
                                    } else {
                                        // O componente com este ID não foi encontrado no banco de dados (foi excluído)
                                        // Exibir como "Sem Componente"
                                        ?>
                                        <div class="component-card no-component">
                                            <div class="component-icon">
                                                <i class="bi <?php echo $info['icon']; ?> component-icon-inner"></i>
                                            </div>
                                            <div class="component-info">
                                                <div class="component-name">
                                                    <?php echo $tipoFormatado; ?>
                                                </div>
                                                <span class="component-ref text-muted"><?php echo $tipoFormatado; ?> Sem Componente</span>
                                            </div>
                                            <div class="component-actions">
                                                <?php if ($computador['COMP_ESTADO'] != '4' && $computador['COMP_ESTADO'] != '9'): // Não mostrar edição para "Sem Reparo" ?>
                                                <button class="component-action edit-comp-btn" title="Adicionar Componente" 
                                                    onclick="editarComponente('<?php echo htmlspecialchars($computador['COMP_ID']); ?>', '<?php echo $tipo; ?>')">
                                                    <i class="bi bi-plus-circle"></i>
                                                </button>
                                                <?php else: ?>
                                                <span class="sem-reparo-badge" title="Componentes não podem ser editados">Sem Reparo</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php
                                        
                                        // Remover a referência do componente excluído do computador
                                        try {
                                            $updateStmt = $pdo->prepare("UPDATE computadores SET COMP_{$tipo} = NULL WHERE COMP_ID = ?");
                                            $updateStmt->execute([$computador['COMP_ID']]);
                                        } catch (Exception $e) {
                                            error_log("Erro ao remover referência do componente excluído: " . $e->getMessage());
                                        }
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Verifica se deve mostrar o botão de toggle com base no tamanho da tela
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
            
            // Definir as funções para o escopo global
            window.editarComponente = editarComponente;
            window.removerComponente = removerComponente;
            
            // Função para o toggle do sidebar em telas pequenas
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
        });

        // Função para editar um componente
        function editarComponente(computerId, tipoComponente, componenteId = null) {
            // Verificar se o computador está em estado "Sem Reparo"
            const computadorEstado = '<?php echo $computador['COMP_ESTADO']; ?>';
            
            if (computadorEstado === '4' || computadorEstado === '9') {
                console.log('Computador em estado "Sem Reparo". Componentes não podem ser editados.');
                return; // Não permitir edição para computadores "Sem Reparo"
            }
            
            // Construir URL para buscar o formulário
            const url = `funcoes/editar_componente.php?comp_id=${encodeURIComponent(computerId)}&tipo=${encodeURIComponent(tipoComponente)}${componenteId ? `&id=${componenteId}` : ''}`;
            
            console.log(`Carregando formulário de: ${url}`);
            
            // Remover modal existente se houver
            const existingModal = document.getElementById('modalEditarComponente');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Carregar o modal para editar/adicionar componente
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Status: ${response.status}, Texto: ${response.statusText}`);
                    }
                    return response.text();
                })
                .then(html => {
                    // Verificar se é uma resposta JSON de erro
                    if (html.trim().startsWith('{')) {
                        try {
                            const jsonResponse = JSON.parse(html);
                            if (!jsonResponse.success) {
                                throw new Error(jsonResponse.message || 'Erro desconhecido');
                            }
                        } catch (error) {
                            console.error('Resposta JSON com erro:', error);
                            alert('Erro: ' + error.message);
                            return; // Não continuar com a criação do modal
                        }
                    }
                    
                    // Verificar se o HTML está vazio
                    if (!html.trim()) {
                        console.error('HTML recebido está vazio');
                        alert('Erro ao carregar formulário: resposta vazia');
                        return;
                    }
                    
                    // Adicionar o HTML diretamente ao body
                    document.body.insertAdjacentHTML('beforeend', html);
                    
                    // Verificar imediatamente se o modal foi adicionado
                    const modalElement = document.getElementById('modalEditarComponente');
                    if (!modalElement) {
                        console.error('Modal não encontrado após inserir HTML');
                        console.log('HTML recebido:', html);
                        
                        // Verificar se o HTML contém o ID correto
                        if (!html.includes('id="modalEditarComponente"')) {
                            console.error('HTML não contém o ID modalEditarComponente');
                        }
                        
                        // Tentar criar o modal manualmente se não foi encontrado
                        if (html.includes('Não há') && html.includes('disponíveis para adicionar')) {
                            console.log('Parece ser uma mensagem de alerta sobre componentes não disponíveis');
                            
                            // Criar um modal simples com a mensagem
                            const modalHtml = `
                            <div class="modal fade" id="modalEditarComponente" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Adicionar Componente</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-warning">
                                                ${html.includes('<div class="alert') ? html.match(/<div class="alert.*?">(.*?)<\/div>/s)[1] : 'Não há componentes disponíveis para adicionar'}
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `;
                            
                            document.body.insertAdjacentHTML('beforeend', modalHtml);
                            const newModal = document.getElementById('modalEditarComponente');
                            if (newModal) {
                                try {
                                    const modal = new bootstrap.Modal(newModal);
                                    modal.show();
                                    return;
                                } catch (error) {
                                    console.error('Erro ao criar modal manualmente:', error);
                                }
                            }
                        }
                        
                        alert('Erro ao criar modal de edição. Verifique o console para detalhes.');
                        return;
                    }
                    
                    // Usar setTimeout para garantir que o DOM foi atualizado antes de inicializar o modal
                    setTimeout(() => {
                        try {
                            const modal = new bootstrap.Modal(modalElement);
                            modal.show();
                            
                            // Configurar eventos do formulário
                            const form = document.getElementById('formEditarComponente');
                            if (form) {
                                form.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    
                                    const formData = new FormData(this);
                                    
                                    fetch('funcoes/salvar_componente.php', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            window.location.reload();
                                        } else {
                                            console.error('Erro detalhado:', data);
                                            alert('Erro ao atualizar: ' + data.message);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Erro:', error);
                                        alert('Erro ao processar solicitação: ' + error.message);
                                    });
                                });
                            } else {
                                console.error('Formulário não encontrado após carregar modal');
                                // Se não há formulário, pode ser a mensagem de alerta de não haver componentes
                                // Verificamos se o modal já tem o alerta
                                const alertElement = modalElement.querySelector('.alert-warning');
                                if (alertElement) {
                                    console.log('Mostrou alerta de não haver componentes disponíveis');
                                }
                            }
                        } catch (error) {
                            console.error('Erro ao inicializar modal:', error);
                            alert('Erro ao abrir formulário. Contate o administrador do sistema.');
                        }
                    }, 100);
                })
                .catch(error => {
                    console.error('Erro ao carregar editar_componente.php:', error);
                    alert('Erro ao carregar formulário de edição de componente: ' + error.message);
                });
        }
        
        // Função para remover um componente
        function removerComponente(computerId, tipoComponente, componenteId) {
            // Criar um modal de confirmação de exclusão
            const modalId = 'modalConfirmarRemocao';
            
            // Remover qualquer modal de confirmação existente
            const existingModal = document.getElementById(modalId);
            if (existingModal) {
                existingModal.remove();
            }
            
            // Criar HTML do modal de confirmação
            const modalHtml = `
            <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirmar Remoção</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <p>Tem a certeza que pretende remover este componente do computador?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-danger" id="btnConfirmarRemocao">Remover</button>
                        </div>
                    </div>
                </div>
            </div>
            `;
            
            // Adicionar o modal ao body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Inicializar e mostrar o modal
            const modalElement = document.getElementById(modalId);
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            
            // Adicionar evento ao botão de confirmação
            document.getElementById('btnConfirmarRemocao').addEventListener('click', function() {
                // Fechar o modal
                modal.hide();
                
                // Mostrar dados para debug
                console.log("Removendo componente:", {
                    computerId: computerId,
                    tipoComponente: tipoComponente,
                    componenteId: componenteId
                });
                
                const formData = new FormData();
                formData.append('comp_id', computerId);
                formData.append('tipo', tipoComponente);
                formData.append('componente_id', componenteId);
                
                fetch('funcoes/remover_componente.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log("Resposta:", data); // Debug
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Erro ao remover: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao processar solicitação.');
                });
            });
        }
    </script>
</body>
</html> 