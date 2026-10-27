<?php
session_start();
require_once('../ligacao_base_dados.php');
require_once('../includes/sidebar.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['userId'])) {
    header('Location: ../login.php');
    exit;
}

try {
    // Buscar dados do usuário baseado no tipo
    if ($_SESSION['userType'] === 'tecnico') {
        $stmt = $pdo->prepare("SELECT * FROM suporte_tecnico WHERE id = ?");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM comunidade_escolar WHERE id = ?");
    }
    
    $stmt->execute([$_SESSION['userId']]);
    $usuario = $stmt->fetch();
} catch (Exception $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - GM HelpDesk</title>
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

        @media (max-width: 992px) {
            .sidebar {
                width: 250px;
            }
            
            .main-content {
                margin-left: 250px;
                width: calc(100% - 250px);
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
        }

        @media (max-width: 576px) {
            .container-fluid {
                padding: 3rem 1rem 1rem 1rem !important;
            }
        }

        @media (max-width: 375px) {
            .container-fluid {
                padding: 1.5rem 15px !important;
            }
        }

        /* Estilo para conteúdo principal */
        .main-content {
            margin-left: 300px;
            width: calc(100% - 300px);
            transition: all 0.3s ease;
        }

        .profile-header {
            background: linear-gradient(135deg, #2a4d69, rgb(36, 110, 167));
            padding: 2.5rem;
            color: white;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .profile-header::before {
            display: none;
        }

        .profile-header::after {
            display: none;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border: 4px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }

        .profile-avatar:hover {
            transform: scale(1.05);
            border: 4px solid rgba(255, 255, 255, 0.5);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            transition: all 0.3s ease;
        }

        .profile-avatar .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
            color: white;
            z-index: 2;
        }

        .profile-avatar:hover .overlay {
            opacity: 1;
        }

        .profile-header h4 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.6rem;
            position: relative;
            z-index: 2;
        }

        .profile-header p {
            opacity: 0.9;
            font-weight: 500;
            position: relative;
            z-index: 2;
        }

        .info-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border-top: 5px solid #2a4d69;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .info-item {
            margin-bottom: 1.8rem;
            transition: all 0.3s ease;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2a4d69;
        }

        .action-button {
            width: 100%;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .action-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .action-button i {
            font-size: 1.1rem;
            margin-right: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2a4d69, rgb(36, 110, 167));
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #234663, rgb(32, 100, 152));
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
            border: none;
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #c82333, #bd2130);
        }

        /* Melhorando modais */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, #2a4d69, rgb(36, 110, 167));
            color: white;
            border-bottom: none;
            padding: 1.5rem;
        }

        .modal-title {
            font-weight: 700;
            font-size: 1.2rem;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-footer {
            border-top: none;
            padding: 1.5rem;
        }

        #previewFoto {
            max-width: 200px;
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 50%;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 3px solid #f8f9fa;
            transition: all 0.3s ease;
        }

        #fotoModal .btn {
            border-radius: 8px;
            padding: 0.8rem 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        #fotoModal .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .container-fluid {
            max-width: 1200px;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .profile-header {
                padding: 2rem;
            }
            
            .profile-avatar {
                width: 100px;
                height: 100px;
            }
            
            .info-card {
                padding: 1.8rem;
            }
            
            .modal-body {
                padding: 1.5rem;
            }
        }

        /* Adicionando animações */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .profile-header, .info-card {
            animation: fadeIn 0.6s ease-out forwards;
        }

        .info-item {
            opacity: 0;
            animation: fadeIn 0.4s ease-out forwards;
        }

        .info-item:nth-child(1) { animation-delay: 0.1s; }
        .info-item:nth-child(2) { animation-delay: 0.2s; }
        .info-item:nth-child(3) { animation-delay: 0.3s; }
        .info-item:nth-child(4) { animation-delay: 0.4s; }
        .info-item:nth-child(5) { animation-delay: 0.5s; }
    </style>
</head>
<body>
    <!-- Botão toggle para sidebar em dispositivos móveis -->
    <button class="sidebar-toggle btn" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>
    
    <div class="d-flex">
        <!-- Sidebar -->
        <?php 
        renderSidebar('perfil', $_SESSION['userType'], $usuario['nome_completo'], $usuario['foto_perfil']); 
        ?>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <div class="container-fluid p-4">
                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <!-- Cabeçalho do Perfil -->
                        <div class="profile-header text-center">
                            <div class="profile-avatar mx-auto" style="cursor: pointer;" onclick="abrirModalFoto()">
                                <?php if ($usuario['foto_perfil']): ?>
                                    <img src="../assets/img/perfil/<?php echo $usuario['foto_perfil']; ?>" 
                                         class="rounded-circle" 
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <img src="../assets/img/perfil/default.jpg" 
                                         class="rounded-circle" 
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                <?php endif; ?>
                                <div class="overlay">
                                    <i class="bi bi-camera"></i>
                                </div>
                            </div>
                            <h4 class="mb-1"><?php echo $usuario['nome_completo']; ?></h4>
                            <p class="mb-0">
                                <?php echo $_SESSION['userType'] === 'tecnico' ? 'Suporte Técnico' : 'Comunidade Escolar'; ?>
                            </p>
                        </div>

                        <!-- Informações do Perfil -->
                        <div class="info-card">
                            <div class="info-item">
                                <div class="info-label">Nome Completo</div>
                                <div class="info-value"><?php echo $usuario['nome_completo']; ?></div>
                            </div>

                            <div class="info-item">
                                <div class="info-label">Email Institucional</div>
                                <div class="info-value"><?php echo $usuario['email_institucional']; ?></div>
                            </div>

                            <?php if ($_SESSION['userType'] === 'tecnico'): ?>
                                <div class="info-item">
                                    <div class="info-label">Número de Processo</div>
                                    <div class="info-value"><?php echo $usuario['numero_processo']; ?></div>
                                </div>
                            <?php else: ?>
                                <div class="info-item">
                                    <div class="info-label">Cargo</div>
                                    <div class="info-value"><?php echo ucfirst($usuario['cargo']); ?></div>
                                </div>
                            <?php endif; ?>

                            <div class="info-item">
                                <div class="info-label">Data de Criação</div>
                                <div class="info-value">
                                    <?php 
                                        $data = new DateTime($usuario['data_criacao']);
                                        echo $data->format('d/m/Y H:i:s');
                                    ?>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Botões de Ação -->
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="alterar_senha_perfil.php" class="btn btn-primary action-button">
                                        <i class="bi bi-key me-2"></i>
                                        Alterar Senha
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <button onclick="handleLogout()" class="btn btn-danger action-button">
                                        <i class="bi bi-box-arrow-right me-2"></i>
                                        Terminar Sessão
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de Confirmação de Logout -->
    <div class="modal fade" id="logoutModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terminar Sessão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <i class="bi bi-exclamation-circle text-warning display-1 mb-4"></i>
                    <h5 class="mb-3">Tens a certeza que queres terminar a sessão?</h5>
                    <p class="text-muted">Terás que fazer login novamente para aceder à tua conta.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-danger px-4" onclick="confirmLogout()">
                        <i class="bi bi-box-arrow-right me-2"></i>Terminar Sessão
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Foto -->
    <div class="modal fade" id="fotoModal" tabindex="-1" aria-labelledby="fotoModalLabel" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fotoModalLabel">Foto de Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="previewFoto" 
                         src="../assets/img/perfil/<?php echo $usuario['foto_perfil'] ? $usuario['foto_perfil'] : 'default.jpg'; ?>" 
                         alt="Preview" 
                         class="mb-3 rounded-circle">
                    
                    <!-- Botões Iniciais -->
                    <div id="initialButtons">
                        <input type="file" id="inputFoto" accept="image/*" onchange="previewFoto(this)" style="display: none;">
                        <button type="button" class="btn btn-primary w-100 mb-3" onclick="document.getElementById('inputFoto').click()">
                            <i class="bi bi-image me-2"></i>Escolher Foto
                        </button>
                        <button type="button" class="btn btn-outline-primary w-100 mb-3" onclick="abrirCamera()">
                            <i class="bi bi-camera me-2"></i>Tirar Foto
                        </button>
                        <?php if ($usuario['foto_perfil'] && $usuario['foto_perfil'] !== 'default.jpg'): ?>
                        <button type="button" class="btn btn-outline-danger w-100" onclick="apagarFoto()">
                            <i class="bi bi-trash me-2"></i>Apagar Foto
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Botões de Confirmação -->
                    <div id="confirmButtons" style="display: none;">
                        <button type="button" class="btn btn-primary w-100 mb-3" onclick="salvarFoto()">
                            <i class="bi bi-check-lg me-2"></i>Confirmar
                        </button>
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="cancelarEdicao()">
                            <i class="bi bi-x-lg me-2"></i>Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal da Câmera -->
    <div class="modal fade" id="cameraModal" tabindex="-1" aria-labelledby="cameraModalLabel" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cameraModalLabel">Tirar Foto</h5>
                    <button type="button" class="btn-close" onclick="fecharCamera()" aria-label="Fechar" tabindex="0"></button>
                </div>
                <div class="modal-body">
                    <div class="camera-container text-center">
                        <video id="video" width="100%" autoplay playsinline class="rounded shadow-sm mb-3"></video>
                        <canvas id="canvas" style="display:none;"></canvas>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="fecharCamera()">
                        <i class="bi bi-x-lg me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="tirarFoto()">
                        <i class="bi bi-camera me-2"></i>Capturar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação para Apagar Foto -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Apagar Foto de Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="bi bi-exclamation-triangle-fill text-warning display-1 mb-4"></i>
                    <h5 class="mb-3">Tens a certeza que desejas apagar a tua foto de perfil?</h5>
                    <p class="text-muted">Esta ação não pode ser desfeita. A tua foto será substituída pela imagem padrão.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-danger px-4" onclick="confirmarApagarFoto()">
                        <i class="bi bi-trash me-2"></i>Apagar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function handleLogout() {
            const modal = new bootstrap.Modal(document.getElementById('logoutModal'));
            modal.show();
        }

        function confirmLogout() {
            fetch('../logout.php')
                .then(() => {
                    window.location.href = '../login.php';
                })
                .catch(error => {
                    console.error('Erro ao fazer logout:', error);
                    window.location.href = '../login.php';
                });
        }

        let currentRotation = 0;
        let currentScale = 1;
        let currentX = 0;
        let currentY = 0;
        let isDragging = false;
        let startX;
        let startY;

        // Função para mostrar/esconder botões
        function mostrarBotoesEscolha() {
            const initialButtons = document.getElementById('initialButtons');
            const confirmButtons = document.getElementById('confirmButtons');
            
            if (initialButtons && confirmButtons) {
                initialButtons.style.display = 'block';
                confirmButtons.style.display = 'none';
            }
        }

        function mostrarBotoesConfirmacao() {
            const initialButtons = document.getElementById('initialButtons');
            const confirmButtons = document.getElementById('confirmButtons');
            
            if (initialButtons && confirmButtons) {
                initialButtons.style.display = 'none';
                confirmButtons.style.display = 'block';
            }
        }

        // Função para preview da foto
        function previewFoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('previewFoto');
                    img.dataset.originalSrc = img.src; // Guardar src original
                    img.src = e.target.result;
                    mostrarBotoesConfirmacao();
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Função para cancelar edição
        function cancelarEdicao() {
            const img = document.getElementById('previewFoto');
            img.src = img.dataset.originalSrc || '../assets/img/perfil/default.jpg';
            mostrarBotoesEscolha();
        }

        // Função para salvar foto
        function salvarFoto() {
            const img = document.getElementById('previewFoto');
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            canvas.width = 500;
            canvas.height = 500;
            
            const newImg = new Image();
            newImg.onload = function() {
                ctx.drawImage(newImg, 0, 0, canvas.width, canvas.height);
                
                canvas.toBlob(blob => {
                    const formData = new FormData();
                    formData.append('foto', blob, 'profile_photo.jpg');
                    
                    fetch('../conta/upload_foto.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Atualizar todas as instâncias da foto
                            const novaFoto = '../assets/img/perfil/' + data.foto;
                            document.querySelector('.profile-avatar img').src = novaFoto;
                            document.querySelector('.sidebar .avatar img').src = novaFoto;
                            document.getElementById('previewFoto').src = novaFoto;
                            
                            // Fechar modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('fotoModal'));
                            modal.hide();
                            
                            // Mostrar mensagem de sucesso
                            Toastify({
                                text: "Foto atualizada com sucesso",
                                duration: 3000,
                                gravity: "top",
                                position: "right",
                                style: {
                                    background: "linear-gradient(to right, #00b09b, #96c93d)",
                                }
                            }).showToast();
                            
                            // Recarregar a página após um breve delay
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        }
                    });
                }, 'image/jpeg', 0.8);
            };
            newImg.src = img.src;
        }

        // Função para tirar foto
        function tirarFoto() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Fechar câmera e modal
            fecharCamera();
            bootstrap.Modal.getInstance(document.getElementById('cameraModal')).hide();

            // Mostrar foto no preview
            const img = document.getElementById('previewFoto');
            img.dataset.originalSrc = img.src;
            img.src = canvas.toDataURL('image/jpeg');

            // Mostrar botões de confirmação
            mostrarBotoesConfirmacao();
        }

        // Função para abrir o modal
        function abrirModalFoto() {
            const modal = new bootstrap.Modal(document.getElementById('fotoModal'));
            const img = document.getElementById('previewFoto');
            const currentProfilePic = document.querySelector('.profile-avatar img').src;
            
            // Resetar a imagem para o estado atual
            img.src = currentProfilePic;
            img.dataset.originalSrc = currentProfilePic;
            
            // Limpar input file
            document.getElementById('inputFoto').value = '';
            
            // Garantir que apenas os botões de escolha estejam visíveis
            mostrarBotoesEscolha();
            
            // Abrir o modal
            modal.show();

            // Focar no primeiro botão quando o modal abrir
            document.getElementById('fotoModal').addEventListener('shown.bs.modal', function () {
                document.querySelector('#initialButtons .btn').focus();
            });
        }

        let stream = null;

        // Função para abrir câmera
        function abrirCamera() {
            const cameraModal = new bootstrap.Modal(document.getElementById('cameraModal'));
            cameraModal.show();
            
            navigator.mediaDevices.getUserMedia({ 
                video: { 
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                } 
            })
            .then(videoStream => {
                stream = videoStream;
                const video = document.getElementById('video');
                video.srcObject = stream;
                video.play();
            })
            .catch(err => {
                console.error('Erro ao acessar câmera:', err);
                alert('Erro ao acessar câmera. Verifique as permissões.');
            });
        }

        // Função para fechar câmera
        function fecharCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
        }

        // Atualizar os event listeners dos modais
        document.addEventListener('DOMContentLoaded', function() {
            const fotoModal = document.getElementById('fotoModal');
            const confirmDeleteModal = document.getElementById('confirmDeleteModal');
            const profileAvatar = document.querySelector('.profile-avatar');

            if (fotoModal) {
                fotoModal.addEventListener('hidden.bs.modal', function () {
                    fecharCamera();
                    if (profileAvatar) {
                        setTimeout(() => {
                            profileAvatar.focus();
                        }, 0);
                    }
                });
            }

            if (confirmDeleteModal) {
                confirmDeleteModal.addEventListener('hidden.bs.modal', function () {
                    if (profileAvatar) {
                        setTimeout(() => {
                            profileAvatar.focus();
                        }, 0);
                    }
                });
            }
        });

        function apagarFoto() {
            const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
            modal.show();
        }

        function confirmarApagarFoto() {
            fetch('../conta/atualizar_foto.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ action: 'delete' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar todas as instâncias da foto para default.jpg
                    const defaultFoto = '../assets/img/perfil/default.jpg';
                    const avatarImg = document.querySelector('.profile-avatar img');
                    const sidebarImg = document.querySelector('.sidebar .avatar img');
                    const previewImg = document.getElementById('previewFoto');

                    if (avatarImg) avatarImg.src = defaultFoto;
                    if (sidebarImg) sidebarImg.src = defaultFoto;
                    if (previewImg) previewImg.src = defaultFoto;
                    
                    // Fechar os modais com segurança
                    const confirmModal = bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal'));
                    const fotoModal = bootstrap.Modal.getInstance(document.getElementById('fotoModal'));
                    
                    if (confirmModal) confirmModal.hide();
                    if (fotoModal) fotoModal.hide();
                    
                    // Mostrar mensagem de sucesso
                    Toastify({
                        text: "Foto removida com sucesso",
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)",
                        }
                    }).showToast();
                    
                    // Recarregar a página após um breve delay
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Toastify({
                    text: "Erro ao remover foto",
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            });
        }
        
        // Função para toggle da sidebar em dispositivos móveis
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const body = document.body;
            
            sidebar.classList.toggle('active');
            
            // Se o sidebar estiver ativo, esconde o botão de toggle e mostra o overlay
            if (sidebar.classList.contains('active')) {
                // Esconde imediatamente o botão de toggle
                document.querySelector('.sidebar-toggle').style.display = 'none';
                
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
                overlay.style.background = 'rgba(0,0,0,0.7)';
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
                        document.querySelector('.sidebar-toggle').style.display = 'block';
                    }, 50);
                });
            } else {
                // Remove classe do body
                body.classList.remove('menu-active');
                
                // Esconde o overlay se existir
                const overlay = document.querySelector('.sidebar-overlay');
                if (overlay) overlay.remove();
                
                // Certifica-se de que o botão de toggle está visível
                document.querySelector('.sidebar-toggle').style.display = 'block';
            }
        }

        // Verificar na carga inicial e quando redimensionar
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
        });
    </script>
</body>
</html> 