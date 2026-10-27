<?php
// Iniciar buffer de saída para prevenir saídas antes do JSON
ob_start();

session_start();
include('ligacao_base_dados.php');

// Se já estiver logado, redireciona para o dashboard apropriado
if (isset($_SESSION['userType'])) {
    if ($_SESSION['userType'] === 'tecnico') {
        header("Location: dashboard/tickets/dashboard.php");
    } else if ($_SESSION['userType'] === 'comunidade_escolar') {
        header("Location: dashboard/dashboardce.php");
    } else {
        header("Location: dashboard/{$_SESSION['userType']}.php");
    }
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpar qualquer saída anterior
    ob_clean();
    
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $tipo = $_POST['userType'];

    try {
        if ($tipo === 'tecnico') {
            $stmt = $pdo->prepare("SELECT * FROM suporte_tecnico WHERE email_institucional = ?");
        } else {
            $stmt = $pdo->prepare("SELECT * FROM comunidade_escolar WHERE email_institucional = ?");
        }

        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha_hash'])) {
            // Login bem-sucedido
            $_SESSION['userId'] = $user['id'];
            $_SESSION['userEmail'] = $user['email_institucional'];
            $_SESSION['userName'] = $user['nome_completo'];
            $_SESSION['userType'] = $tipo;
            
            // Debug
            error_log("User logged in: {$user['nome_completo']} with type: {$tipo}");
            
            if ($tipo === 'tecnico') {
                $_SESSION['departamento'] = $user['departamento_tecnico'];
            } else {
                $_SESSION['cargo'] = $user['cargo'];
            }

            // Verificar se é primeiro login
            if ($user['primeiro_login']) {
                $_SESSION['require_password_change'] = true;
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'redirect' => 'alterar_senha.php']);
                exit;
            }

            header('Content-Type: application/json');
            if ($tipo === 'tecnico') {
                echo json_encode(['success' => true, 'redirect' => "dashboard/tickets/dashboard.php"]);
            } else if ($tipo === 'comunidade_escolar') {
                echo json_encode(['success' => true, 'redirect' => "dashboard/dashboardce.php"]);
            } else {
                echo json_encode(['success' => true, 'redirect' => "dashboard/{$tipo}.php"]);
            }
            exit;
        } else {
            throw new Exception('Credenciais inválidas');
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Email ou senha incorretos']);
        exit;
    }
}

// Se não for POST, limpa o buffer e continua com o HTML
ob_end_clean();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GM HelpDesk</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <style>
        /* Estilos base */
        body {
            min-height: 100vh;
            /* Fundo gerado em CSS: gradiente nas cores da app com um padrão subtil de pontos */
            background-color: #1e40af;
            background-image:
                radial-gradient(rgba(255, 255, 255, 0.09) 1px, transparent 1.5px),
                linear-gradient(135deg, #1e3a8a 0%, #1e40af 40%, #2563eb 75%, #3b82f6 100%);
            background-size: 22px 22px, cover;
            background-repeat: repeat, no-repeat;
            background-attachment: fixed;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            padding: 1rem;
        }

        .login-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
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
        
        .welcome-title {
            color: #1a3c5e;
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
        }

        .user-type-selector {
            margin-bottom: 2rem;
        }

        .user-type-selector label {
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .user-type-selector i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            padding: 0.75rem 1rem;
        }

        .input-group-text {
            padding: 0.75rem 1rem;
            background-color: transparent;
        }

        .btn-login {
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .btn-home {
            margin-top: 15px;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 500;
            background-color: #2a4d69;
            color: white;
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .btn-home:hover {
            background-color: #1a3c5e;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(42, 77, 105, 0.2);
            color: white;
        }

        /* Media Queries */
        @media (max-width: 767.98px) {
            .login-container {
                padding: 1rem;
            }

            .login-card {
                border-radius: 0.5rem;
            }

            .login-header {
                padding: 1.5rem;
            }

            .login-icon {
                font-size: 2.5rem;
            }

            .user-type-selector label {
                padding: 0.75rem;
            }

            .user-type-selector i {
                font-size: 1.25rem;
            }

            .form-control, .form-select, .input-group-text {
                padding: 0.625rem 0.875rem;
            }
        }

        @media (max-width: 575.98px) {
            .login-container {
                padding: 0.5rem;
            }

            .login-header {
                padding: 1rem;
            }

            .login-icon {
                font-size: 2rem;
            }

            .card-body {
                padding: 1rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            .user-type-selector {
                margin-bottom: 1.5rem;
            }

            .alert {
                padding: 0.75rem;
                margin-bottom: 1rem;
            }
        }

        /* Animações */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            animation: fadeIn 0.5s ease-out;
        }

        /* Melhorias visuais */
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
        }

        .alert {
            border: none;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="card-body p-4">
                <div class="login-header">
                    <div class="icon-container">
                        <i class="bi bi-ticket-detailed-fill login-icon"></i>
                    </div>
                    <h2 class="welcome-title">GM HelpDesk</h2>
                </div>
                
                <!-- Seleção de Tipo de Usuário -->
                <div class="user-type-selector">
                    <div class="row g-2">
                        <div class="col-6">
                            <!-- Radio button para Comunidade Escolar - valor DEVE ser 'comunidade_escolar' -->
                            <input type="radio" class="btn-check" name="userType" id="comunidadeEscolar" value="comunidade_escolar">
                            <label class="btn btn-outline-primary w-100" for="comunidadeEscolar">
                                <i class="bi bi-people d-block"></i>
                                Comunidade Escolar
                            </label>
                        </div>
                        <div class="col-6">
                            <!-- Radio button para Suporte Técnico -->
                            <input type="radio" class="btn-check" name="userType" id="suporteTecnico" value="tecnico" checked>
                            <label class="btn btn-outline-primary w-100" for="suporteTecnico">
                                <i class="bi bi-tools d-block"></i>
                                Suporte Técnico
                            </label>
                        </div>
                    </div>
                </div>
                
                <form id="loginForm" class="needs-validation" novalidate method="POST">
                    <div class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text border-end-0">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" class="form-control border-start-0" id="email" 
                                   placeholder="E-mail institucional" required name="email">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text border-end-0">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control border-start-0" id="password" 
                                   placeholder="Senha" required name="senha">
                        </div>
                    </div>
                    
                    <input type="hidden" name="userType" id="userType" value="tecnico">
                    
                    <button type="submit" class="btn btn-primary w-100 btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Entrar
                    </button>
                </form>
                
                <a href="#" class="btn btn-home w-100" data-bs-toggle="modal" data-bs-target="#recuperarAcessoModal">
                    <i class="bi bi-house-door me-2"></i>
                    Recuperar Acesso
                </a>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger mt-3"><?= $error ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Recuperar Acesso -->
    <div class="modal fade" id="recuperarAcessoModal" tabindex="-1" aria-labelledby="recuperarAcessoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="recuperarAcessoModalLabel">Recuperar Acesso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Por favor, introduza o email associado à conta que deseja recuperar a palavra passe e siga as instruções que irá receber no seu email.</p>
                    
                    <div id="recuperacaoAlerta" class="alert d-none" role="alert"></div>
                    
                    <form id="recuperarForm">
                        <div class="mb-3">
                            <label for="recuperarEmail" class="form-label">Email Institucional</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="recuperarEmail" placeholder="Insere o teu email institucional" required>
                                <span class="input-group-text">@<?= htmlspecialchars(obterConfig('APP_EMAIL_DOMAIN')) ?></span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Continuar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const recuperarForm = document.getElementById('recuperarForm');
            const recuperacaoAlerta = document.getElementById('recuperacaoAlerta');

            // Função para exibir alerta no modal
            function mostrarAlertaRecuperacao(mensagem, tipo) {
                recuperacaoAlerta.className = `alert alert-${tipo}`;
                recuperacaoAlerta.innerHTML = mensagem;
                recuperacaoAlerta.classList.remove('d-none');
            }

            // Tratamento do formulário de recuperação de senha
            if (recuperarForm) {
                recuperarForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const emailPrefixo = document.getElementById('recuperarEmail').value.trim();
                    const email = emailPrefixo + <?= json_encode('@' . obterConfig('APP_EMAIL_DOMAIN')) ?>;
                    
                    // Remove qualquer alerta existente
                    recuperacaoAlerta.classList.add('d-none');
                    
                    // Enviar solicitação de recuperação
                    fetch('processar_recuperacao.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'email=' + encodeURIComponent(email)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            mostrarAlertaRecuperacao('O pedido de recuperação foi efetuado com sucesso. Consulte o seu email e siga os passos indicados.', 'success');
                            recuperarForm.reset();
                        } else {
                            mostrarAlertaRecuperacao('Não existem utilizadores associados a este email.', 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        mostrarAlertaRecuperacao('Ocorreu um erro ao processar o pedido. Por favor, tente novamente.', 'danger');
                    });
                });
            }

            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Remove qualquer alerta existente
                const existingAlert = document.querySelector('.alert');
                if (existingAlert) {
                    existingAlert.remove();
                }

                const formData = new FormData(this);
                
                fetch('login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Armazena informações no localStorage
                        localStorage.setItem('userType', formData.get('userType'));
                        localStorage.setItem('userEmail', formData.get('email'));
                        
                        // Redireciona para o dashboard
                        window.location.href = data.redirect;
                    } else {
                        showError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showError('Erro ao processar o login. Por favor, tente novamente.');
                });
            });

            function showError(message) {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
                alertDiv.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                loginForm.insertAdjacentElement('beforebegin', alertDiv);
            }

            // Atualiza o campo hidden quando o tipo de usuário muda
            document.querySelectorAll('input[name="userType"]').forEach(input => {
                input.addEventListener('change', function() {
                    document.getElementById('userType').value = this.value;
                    console.log('userType changed to:', this.value); // Debug log
                });
            });
            
            // Garantir que o valor inicial do campo hidden é o mesmo do radio selecionado
            const selectedUserType = document.querySelector('input[name="userType"]:checked');
            if (selectedUserType) {
                document.getElementById('userType').value = selectedUserType.value;
                console.log('Initial userType set to:', selectedUserType.value); // Debug log
            }
        });
    </script>
</body>
</html> 