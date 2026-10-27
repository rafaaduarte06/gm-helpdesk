<?php
session_start();
require_once('../ligacao_base_dados.php');
require_once('../includes/sidebar.php');

// Verificar se o usuário está logado e é um técnico
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    header('Location: ../login.php');
    exit;
}

// Buscar dados do usuário
try {
    $stmt = $pdo->prepare("SELECT * FROM suporte_tecnico WHERE id = ?");
    $stmt->execute([$_SESSION['userId']]);
    $usuario = $stmt->fetch();
} catch (Exception $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

// Buscar contagens de stock para cada componente
try {
    // Ratos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM ratos");
    $ratos_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Teclados
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM teclados");
    $teclados_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Monitores
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM monitor");
    $monitores_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Coolers
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cooler");
    $coolers_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Discos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM disco");
    $discos_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Fontes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM fonte");
    $fontes_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Memórias RAM
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM memoriaram");
    $ram_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Placas de Rede
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM placaderede");
    $placas_rede_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Placas Gráficas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM placagrafica");
    $placas_graficas_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Motherboards
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM placamae");
    $placas_mae_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Processadores
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM processador");
    $processadores_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Caixas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM caixa");
    $caixas_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Drives DVD
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM drivedvd");
    $drives_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Ventoinhas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM ventoinha");
    $ventoinhas_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Calcular o stock total
    $stock_total = $ratos_count + $teclados_count + $monitores_count + $coolers_count + 
                  $discos_count + $fontes_count + $ram_count + $placas_rede_count + 
                  $placas_graficas_count + $placas_mae_count + $processadores_count + 
                  $caixas_count + $drives_count + $ventoinhas_count;
    
} catch (Exception $e) {
    // Em caso de erro, definir todas as contagens como 0
    $ratos_count = $teclados_count = $monitores_count = $coolers_count = $discos_count = 
    $fontes_count = $ram_count = $placas_rede_count = $placas_graficas_count = 
    $placas_mae_count = $processadores_count = $caixas_count = $drives_count = 
    $ventoinhas_count = 0;
    $stock_total = 0;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventário - GM HelpDesk</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
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
            
            .search-container {
                max-width: 100%;
                margin-top: 15px;
                width: 100%;
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
            }
            
            .sidebar-toggle {
                display: block !important;
            }
            
            .stock-header {
                flex-direction: column;
                padding: 1.2rem;
                align-items: center;
            }
            
            .stock-title {
                margin-bottom: 15px;
                width: 100%;
                justify-content: center;
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
            
            .search-container {
                width: 100%;
            }
        }

        /* Estilo do cabeçalho da página */
        .stock-header {
            background: linear-gradient(135deg, #2a4d69, rgb(36, 110, 167));
            color: white;
            padding: 1.8rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            margin-top: 0;
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
            line-height: 1.2;
        }
        
        .stock-title p {
            font-size: 0.95rem;
            opacity: 0.85;
            letter-spacing: 0.3px;
        }

        .search-container {
            width: 300px;
            max-width: 100%;
        }
        
        /* Estilos para o grupo de input de pesquisa */
        .search-input-group {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .search-input-group:focus-within {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        .search-icon-container {
            background-color: white;
            border-right: none;
            padding-left: 15px;
            color: #4b86b4;
        }
        
        .search-input {
            border-left: none;
            padding-left: 5px;
            box-shadow: none !important;
        }
        
        .search-input:focus {
            border-color: #ced4da;
        }

        /* Melhorias para os cards de componentes */
        .component-card {
            transition: all 0.3s ease;
            cursor: pointer;
            height: 100%;
            background: white;
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            text-align: center;
            position: relative;
        }

        .component-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .component-icon {
            font-size: 2.8rem;
            margin-bottom: 1.2rem;
            color: #2a4d69;
            background-color: rgba(75, 134, 180, 0.1);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.2rem auto;
            transition: all 0.3s ease;
        }

        .component-card:hover .component-icon {
            background-color: rgba(75, 134, 180, 0.2);
            transform: scale(1.1);
        }

        .component-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.7rem;
            color: #2c3e50;
        }

        .component-description {
            font-size: 0.95rem;
            color: #6c757d;
            margin-bottom: 0.8rem;
        }
        
        /* Estilos para o contador de estoque */
        .stock-counter {
            font-size: 0.9rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 8px;
            display: inline-block;
            margin-top: 8px;
            color: #2a4d69;
            background-color: rgba(75, 134, 180, 0.08);
            border: 1px solid #2a4d69;
        }
        
        .stock-high {
            background-color: #28a745;
        }
        
        .stock-medium {
            background-color: #ffc107;
            color: #343a40;
        }
        
        .stock-low {
            background-color: #dc3545;
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
                <!-- Cabeçalho da Página -->
                <div class="stock-header">
                    <div class="stock-title">
                        <i class="bi bi-box"></i>
                        <div>
                            <h4>Inventário de Componentes</h4>
                            <p class="mb-0 mt-1 text-light">Stock Total Atual: <span class="fw-bold"><?php echo $stock_total; ?></span> componentes</p>
                        </div>
                    </div>
                    <div class="search-container">
                        <div class="input-group search-input-group">
                            <span class="input-group-text search-icon-container">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control search-input" 
                                   id="searchInput" 
                                   placeholder="Pesquisar componente...">
                        </div>
                    </div>
                </div>

                <!-- Componentes Grid -->
                <div id="componentsGrid" class="mb-5">
                    <div class="row g-4" id="componentsContainer">
                        <!-- Ratos -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 component-item" data-name="ratos">
                            <a href="ratos.php" class="text-decoration-none">
                                <div class="component-card">
                                    <div class="component-icon">
                                        <i class="bi bi-mouse"></i>
                                    </div>
                                    <h5 class="component-title">Ratos</h5>
                                    <p class="component-description">Gestão de stock de ratos</p>
                                    <div class="stock-counter">
                                        Stock atual: <?php echo $ratos_count; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                        
                        <!-- Teclados -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 component-item" data-name="teclados">
                            <a href="teclados.php" class="text-decoration-none">
                                <div class="component-card">
                                    <div class="component-icon">
                                        <i class="bi bi-keyboard"></i>
                                    </div>
                                    <h5 class="component-title">Teclados</h5>
                                    <p class="component-description">Gestão de stock de teclados</p>
                                    <div class="stock-counter">
                                        Stock atual: <?php echo $teclados_count; ?>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Monitores -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 component-item" data-name="monitores">
                            <a href="monitor.php" class="text-decoration-none">
                                <div class="component-card">
                                    <div class="component-icon">
                                        <i class="bi bi-display"></i>
                                    </div>
                                    <h5 class="component-title">Monitores</h5>
                                    <p class="component-description">Gestão de stock de monitores</p>
                                    <div class="stock-counter">
                                        Stock atual: <?php echo $monitores_count; ?>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Coolers -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 component-item" data-name="coolers">
                            <a href="coolers.php" class="text-decoration-none">
                                <div class="component-card">
                                    <div class="component-icon">
                                        <i class="bi bi-fan"></i>
                                    </div>
                                    <h5 class="component-title">Coolers</h5>
                                    <p class="component-description">Gestão de stock de coolers</p>
                                    <div class="stock-counter">
                                        Stock atual: <?php echo $coolers_count; ?>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Discos -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 component-item" data-name="discos discos rígidos hdd ssd">
                            <a href="discos.php" class="text-decoration-none">
                                <div class="component-card">
                                    <div class="component-icon">
                                        <i class="bi bi-hdd"></i>
                                    </div>
                                    <h5 class="component-title">Discos</h5>
                                    <p class="component-description">Gestão de stock de discos</p>
                                    <div class="stock-counter">
                                        Stock atual: <?php echo $discos_count; ?>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Fontes -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 component-item" data-name="fontes alimentação power supply">
                            <a href="fontes.php" class="text-decoration-none">
                                <div class="component-card">
                                    <div class="component-icon">
                                        <i class="bi bi-plug"></i>
                                    </div>
                                    <h5 class="component-title">Fontes</h5>
                                    <p class="component-description">Gestão de stock de fontes</p>
                                    <div class="stock-counter">
                                        Stock atual: <?php echo $fontes_count; ?>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Memória RAM -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 component-item" data-name="memória ram memórias">
                            <a href="memoriaram.php" class="text-decoration-none">
                                <div class="component-card">
                                    <div class="component-icon">
                                        <i class="bi bi-memory"></i>
                                    </div>
                                    <h5 class="component-title">Memórias RAM</h5>
                                    <p class="component-description">Gestão de stock de memórias</p>
                                    <div class="stock-counter">
                                        Stock atual: <?php echo $ram_count; ?>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Placa de Rede -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 component-item" data-name="placa rede internet wifi ethernet">
                            <a href="placarede.php" class="text-decoration-none">
                                <div class="component-card">
                                    <div class="component-icon">
                                        <i class="bi bi-wifi"></i>
                                    </div>
                                    <h5 class="component-title">Placas de Rede</h5>
                                    <p class="component-description">Gestão de stock de placas de rede</p>
                                    <div class="stock-counter">
                                        Stock atual: <?php echo $placas_rede_count; ?>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Placa Gráfica -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 component-item" data-name="placa gráfica gpu placas">
                            <a href="placagrafica.php" class="text-decoration-none">
                                <div class="component-card">
                                    <div class="component-icon">
                                        <i class="bi bi-gpu-card"></i>
                                    </div>
                                    <h5 class="component-title">Placas Gráfica</h5>
                                    <p class="component-description">Gestão de stock de GPUs</p>
                                    <div class="stock-counter">
                                        Stock atual: <?php echo $placas_graficas_count; ?>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Placa Mãe -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 component-item" data-name="placa mãe mother board placas">
                            <a href="placamae.php" class="text-decoration-none">
                                <div class="component-card">
                                    <div class="component-icon">
                                        <i class="bi bi-motherboard"></i>
                                    </div>
                                    <h5 class="component-title">Motherboards</h5>
                                    <p class="component-description">Gestão de stock de motherboards</p>
                                    <div class="stock-counter">
                                        Stock atual: <?php echo $placas_mae_count; ?>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Processador -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 component-item" data-name="processador cpu processadores">
                            <a href="processador.php" class="text-decoration-none">
                                <div class="component-card">
                                    <div class="component-icon">
                                        <i class="bi bi-cpu"></i>
                                    </div>
                                    <h5 class="component-title">Processadores</h5>
                                    <p class="component-description">Gestão de stock de CPUs</p>
                                    <div class="stock-counter">
                                        Stock atual: <?php echo $processadores_count; ?>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Caixa -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 component-item" data-name="caixa chassis gabinete">
                            <a href="caixas.php" class="text-decoration-none">
                                <div class="component-card">
                                    <div class="component-icon">
                                        <i class="bi bi-pc"></i>
                                    </div>
                                    <h5 class="component-title">Caixas</h5>
                                    <p class="component-description">Gestão de stock de caixas</p>
                                    <div class="stock-counter">
                                        Stock atual: <?php echo $caixas_count; ?>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Drive DVD -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 component-item" data-name="drive dvd cd leitor">
                            <a href="drives.php" class="text-decoration-none">
                                <div class="component-card">
                                    <div class="component-icon">
                                        <i class="bi bi-disc"></i>
                                    </div>
                                    <h5 class="component-title">Drives DVD</h5>
                                    <p class="component-description">Gestão de stock de drives</p>
                                    <div class="stock-counter">
                                        Stock atual: <?php echo $drives_count; ?>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Ventoinha -->
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 component-item" data-name="ventoinha ventoinhas fans">
                            <a href="ventoinhas.php" class="text-decoration-none">
                                <div class="component-card">
                                    <div class="component-icon">
                                        <i class="bi bi-fan"></i>
                                    </div>
                                    <h5 class="component-title">Ventoinhas</h5>
                                    <p class="component-description">Gestão de stock de ventoinhas</p>
                                    <div class="stock-counter">
                                        Stock atual: <?php echo $ventoinhas_count; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
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
            
            // Adicionar evento de pesquisa
            document.getElementById('searchInput').addEventListener('input', function(e) {
                searchComponents(e.target.value);
            });
            
            document.getElementById('searchInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchComponents(e.target.value);
                }
            });
        });
        
        // Função para pesquisar componentes
        function searchComponents(term) {
            term = term.toLowerCase().trim();
            const items = document.querySelectorAll('.component-item');
            
            if (term === '') {
                // Se a busca estiver vazia, mostrar todos os componentes
                items.forEach(item => {
                    item.style.display = 'block';
                });
                return;
            }
            
            items.forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                const title = item.querySelector('.component-title').textContent.toLowerCase();
                const description = item.querySelector('.component-description').textContent.toLowerCase();
                
                // Verificar se o termo de busca corresponde ao nome, título ou descrição
                if (name.includes(term) || title.includes(term) || description.includes(term)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>