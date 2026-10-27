<?php
session_start();
require_once('../ligacao_base_dados.php');
require_once('../includes/sidebar.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['userId'])) {
    header('Location: ../login.php');
    exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senhaAtual = $_POST['senha_atual'];
    $novaSenha = $_POST['nova_senha'];
    $confirmarSenha = $_POST['confirmar_senha'];

    try {
        // Verificar senha atual
        if ($_SESSION['userType'] === 'tecnico') {
            $stmt = $pdo->prepare("SELECT senha_hash FROM suporte_tecnico WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("SELECT senha_hash FROM comunidade_escolar WHERE id = ?");
        }
        
        $stmt->execute([$_SESSION['userId']]);
        $user = $stmt->fetch();

        if (!password_verify($senhaAtual, $user['senha_hash'])) {
            $error = "A senha atual está incorreta";
        } elseif (strlen($novaSenha) < 8) {
            $error = "A nova senha deve ter pelo menos 8 caracteres";
        } elseif ($novaSenha !== $confirmarSenha) {
            $error = "As senhas não coincidem";
        } else {
            $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            
            // Selecionar a tabela correta baseado no tipo de usuário
            $tabela = $_SESSION['userType'] === 'tecnico' ? 'suporte_tecnico' : 'comunidade_escolar';
            
            $stmt = $pdo->prepare("UPDATE {$tabela} SET senha_hash = ? WHERE id = ?");
            $stmt->execute([$senhaHash, $_SESSION['userId']]);
            
            $success = "Senha alterada com sucesso!";
        }
    } catch (Exception $e) {
        $error = "Erro ao alterar a senha: " . $e->getMessage();
    }
}

// Buscar dados do usuário
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
    <title>Alterar Senha - GM HelpDesk</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/custom.css">
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

        /* Estilo para conteúdo principal */
        .main-content {
            margin-left: 300px;
            width: calc(100% - 300px);
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Responsividade */
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
            }
            
            .sidebar-toggle {
                display: block !important;
            }
        }

        /* Estilos específicos para a página de alteração de senha */
        .reset-container {
            width: 100%;
            max-width: 500px;
            padding: 2rem;
        }

        .reset-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            animation: fadeIn 0.5s ease-out;
        }

        .reset-header {
            text-align: center;
            padding: 1.5rem;
        }

        .icon-container {
            background: linear-gradient(135deg, #e0f7fa, #bbdefb);
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.15);
        }

        .reset-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
        }

        .user-info {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-control {
            padding: 0.75rem 1rem;
        }

        .progress {
            height: 8px;
            margin-top: 5px;
            background-color: #e9ecef;
        }

        .btn-reset {
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 500;
        }

        .btn-back {
            margin-top: 15px;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 500;
            background-color: #2a4d69;
            color: white;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background-color: #1a3c5e;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(42, 77, 105, 0.2);
            color: white;
        }

        .toggle-password {
            cursor: pointer;
            border-color: #ced4da;
        }
        
        .toggle-password:hover {
            background-color: #f8f9fa;
        }
        
        .toggle-password:focus {
            box-shadow: none;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- Botão toggle para sidebar em dispositivos móveis -->
    <button class="sidebar-toggle btn" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>

    <div class="d-flex">
        <!-- Sidebar -->
        <?php renderSidebar('perfil', $_SESSION['userType'], $usuario['nome_completo'], $usuario['foto_perfil']); ?>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <div class="reset-container">
                <div class="reset-card">
                    <div class="card-body p-4">
                        <?php if ($success): ?>
                            <div class="text-center mb-4">
                                <div class="icon-container">
                                    <i class="bi bi-check-circle-fill reset-icon text-success"></i>
                                </div>
                                <h2 class="h4 mb-3">Senha Alterada com Sucesso!</h2>
                                <p>A sua senha foi alterada com sucesso. Agora pode usar a sua nova senha para fazer login.</p>
                                <a href="perfil.php" class="btn btn-primary mt-3">Voltar para Perfil</a>
                            </div>
                        <?php else: ?>
                            <div class="reset-header">
                                <div class="icon-container">
                                    <i class="bi bi-lock reset-icon"></i>
                                </div>
                                <h2 class="h4 mb-3">Alterar Palavra-passe</h2>
                                <p>Defina uma nova palavra-passe para a sua conta. Esta senha será usada para entrar na plataforma em conjunto com o seu email institucional.</p>
                            </div>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>
                            
                            <div class="user-info">
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label text-muted">Nome</label>
                                        <p class="mb-0 fw-bold"><?= htmlspecialchars($_SESSION['userName']) ?></p>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <label class="form-label text-muted">Tipo</label>
                                        <p class="mb-0 fw-bold"><?= htmlspecialchars($_SESSION['userType'] === 'tecnico' ? 'Suporte Técnico' : $_SESSION['cargo']) ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="senha_atual" class="form-label">Senha Atual</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="senha_atual" name="senha_atual" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="senha_atual">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="nova_senha" class="form-label">Nova Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="nova_senha" name="nova_senha" required minlength="8">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="nova_senha">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="confirmar_senha" class="form-label">Confirmar Nova Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirmar_senha">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100 btn-reset">Alterar Palavra-passe</button>
                                <a href="perfil.php" class="btn btn-back w-100 mt-3">
                                    <i class="bi bi-arrow-left me-2"></i>
                                    Voltar para Perfil
                                </a>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle para mostrar/ocultar senha
            const toggleButtons = document.querySelectorAll('.toggle-password');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const passwordInput = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    
                    // Alternar tipo do input
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('bi-eye');
                        icon.classList.add('bi-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('bi-eye-slash');
                        icon.classList.add('bi-eye');
                    }
                });
            });
            
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
        
        // Função para toggle da sidebar em dispositivos móveis
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const toggleButton = document.querySelector('.sidebar-toggle');
            const body = document.body;
            
            sidebar.classList.toggle('active');
            
            // Se o sidebar estiver ativo, esconde o botão de toggle e mostra o overlay
            if (sidebar.classList.contains('active')) {
                // Esconde imediatamente o botão de toggle
                if (toggleButton) toggleButton.style.display = 'none';
                
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
                        if (toggleButton) toggleButton.style.display = 'block';
                    }, 50);
                });
            } else {
                // Remove classe do body
                body.classList.remove('menu-active');
                
                // Esconde o overlay se existir
                const overlay = document.querySelector('.sidebar-overlay');
                if (overlay) overlay.remove();
                
                // Certifica-se de que o botão de toggle está visível
                if (toggleButton) toggleButton.style.display = 'block';
            }
        }
    </script>
</body>
</html> 