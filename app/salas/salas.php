<?php
// Habilitar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar output buffering to prevent "headers already sent" errors
ob_start();

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

// Incluir o sidebar para ter acesso à função renderSidebar()
require_once('../includes/sidebar.php');

// Criar tabela salas se não existir
try {
    $checkTableExists = $pdo->query("SHOW TABLES LIKE 'salas'");
    if ($checkTableExists->rowCount() == 0) {
        $createTableSQL = "CREATE TABLE IF NOT EXISTS `salas` (
            `id_sala` INT AUTO_INCREMENT PRIMARY KEY,
            `sala` VARCHAR(10) NOT NULL
        )";
        $pdo->exec($createTableSQL);
    }
} catch (Exception $e) {
    die("Erro ao verificar/criar tabela: " . $e->getMessage());
}

// Adicionar sala
if (isset($_POST['add_sala'])) {
    try {
        $nomeSala = trim($_POST['nome_sala']);
        if (!empty($nomeSala)) {
            // Verificar o comprimento do nome da sala
            if (strlen($nomeSala) > 10) {
                $_SESSION['error_message'] = "O nome da sala não pode ter mais de 10 caracteres.";
            } else {
                // Verificar se a sala já existe
                $checkSala = $pdo->prepare("SELECT * FROM salas WHERE sala = ?");
                $checkSala->execute([$nomeSala]);
                
                if ($checkSala->rowCount() == 0) {
                    $stmt = $pdo->prepare("INSERT INTO salas (sala) VALUES (?)");
                    $stmt->execute([$nomeSala]);
                    $_SESSION['success_message'] = "Sala adicionada com sucesso!";
                } else {
                    $_SESSION['error_message'] = "Esta sala já existe!";
                }
            }
        } else {
            $_SESSION['error_message'] = "Por favor, digite um nome para a sala.";
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erro ao adicionar sala: " . $e->getMessage();
    }
    
    // Redirecionar para evitar reenvio do formulário
    header('Location: salas.php');
    exit;
}

// Incluir o sidebar e passar as informações do usuário
$userType = isset($_SESSION['userType']) ? $_SESSION['userType'] : 'tecnico';
$userName = isset($usuario['nome_completo']) ? $usuario['nome_completo'] : 'Usuário';
$userPhoto = isset($usuario['foto_perfil']) ? $usuario['foto_perfil'] : '';
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventário Salas - GM HelpDesk</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <style>
        /* Estilo de fundo gradiente como na página de login */
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
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

        .room-box {
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
            border-left: 5px solid #0d6efd;
            min-height: 180px;
            width: 100%;
        }

        .room-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .room-id {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #2c3e50;
        }

        .room-content {
            display: flex;
            flex-direction: column;
            margin-top: auto;
        }

        .device-count {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
            color: #555;
        }

        .device-count i {
            margin-right: 10px;
            color: #0d6efd;
        }

        /* Modal para adicionar sala */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            border-bottom: none;
            padding: 20px 25px 0;
        }

        .modal-body {
            padding: 20px 25px;
        }

        .modal-footer {
            border-top: none;
            padding: 0 25px 20px;
            justify-content: center;
        }

        .modal-title {
            font-weight: 700;
            color: #2c3e50;
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

        /* Conteúdo principal */
        .main-content {
            margin-left: 300px;
            width: calc(100% - 300px);
            transition: all 0.3s ease;
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* Novo estilo para o cabeçalho baseado no inventario.php */
        .stock-header {
            background: linear-gradient(135deg, #2a4d69, rgb(36, 110, 167));
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
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
            color: white;
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
    </style>
</head>
<body>
    <button class="sidebar-toggle btn" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>

    <div class="d-flex">
        <!-- Sidebar -->
        <?php renderSidebar('salas', $userType, $userName, $userPhoto); ?>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <div class="container-fluid p-4">
                <!-- Cabeçalho -->
                <div class="stock-header">
                    <div class="stock-title">
                        <i class="bi bi-building"></i>
                        <h4>Inventário Salas</h4>
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
                                       placeholder="Pesquisar sala...">
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                            <i class="bi bi-plus-circle-fill"></i> Adicionar
                        </button>
                    </div>
                </div>

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

                <div class="row" id="roomsContainer">
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT s.*, 
                            (SELECT COUNT(*) FROM computadores c WHERE c.COMP_SALA = s.sala AND c.COMP_ESTADO = '2') as num_computadores,
                            (SELECT COUNT(*) FROM ratos r WHERE r.RATOS_SALA = s.sala AND r.RATOS_ESTADO = '2') as num_ratos,
                            (SELECT COUNT(*) FROM teclados t WHERE t.TECLADO_SALA = s.sala AND t.TECLADO_ESTADO = '2') as num_teclados,
                            (SELECT COUNT(*) FROM monitor m WHERE m.MONITOR_SALA = s.sala AND m.MONITOR_ESTADO = '2') as num_monitores
                        FROM salas s
                        ORDER BY sala");
                        
                        $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (count($salas) > 0) {
                            foreach ($salas as $sala) {
                                // Contar os dispositivos totais (computadores e periféricos individuais)
                                $totalDispositivos = $sala['num_computadores'] + $sala['num_ratos'] + $sala['num_teclados'] + $sala['num_monitores'];
                                ?>
                                <div class="col-md-4 col-lg-3 mb-4 room-item">
                                    <div class="room-box" onclick="window.location.href='detalhessalas.php?id=<?= $sala['id_sala'] ?>'">
                                        <div class="room-id">Sala <?= htmlspecialchars($sala['sala']) ?></div>
                                        <div class="room-content">
                                            <div class="device-count">
                                                <i class="bi bi-pc-display"></i>
                                                <span><?= $sala['num_computadores'] ?> Computadores</span>
                                            </div>
                                            <div class="device-count">
                                                <i class="bi bi-mouse"></i>
                                                <span><?= $sala['num_ratos'] ?> Ratos</span>
                                            </div>
                                            <div class="device-count">
                                                <i class="bi bi-keyboard"></i>
                                                <span><?= $sala['num_teclados'] ?> Teclados</span>
                                            </div>
                                            <div class="device-count">
                                                <i class="bi bi-display"></i>
                                                <span><?= $sala['num_monitores'] ?> Monitores</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<div class="col-12"><div class="alert alert-info">Nenhuma sala registrada.</div></div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="col-12"><div class="alert alert-danger">Erro ao carregar salas: ' . $e->getMessage() . '</div></div>';
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal para adicionar sala -->
    <div class="modal fade" id="addRoomModal" tabindex="-1" aria-labelledby="addRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRoomModalLabel">Adicionar Nova Sala</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="salas.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nome_sala" class="form-label">Nome da Sala</label>
                            <input type="text" class="form-control" id="nome_sala" name="nome_sala" required>
                            <div class="form-text">Ex.: 5.7, A1, Laboratório 3</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="add_sala" class="btn btn-primary">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
            
            // Filtrar salas
            document.getElementById('searchInput').addEventListener('keyup', function() {
                const searchValue = this.value.toLowerCase();
                const rooms = document.querySelectorAll('.room-item');

                rooms.forEach(room => {
                    const roomId = room.querySelector('.room-id').textContent.toLowerCase();
                    if (roomId.includes(searchValue)) {
                        room.style.display = 'block';
                    } else {
                        room.style.display = 'none';
                    }
                });
            });
        });
        
        // Função que permite o toggle do sidebar
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
    </script>
</body>
</html> 
<?php
// Flush the output buffer
ob_end_flush();
?> 