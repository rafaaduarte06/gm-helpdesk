<?php
// Iniciar buffer de saída
ob_start();

session_start();
include('ligacao_base_dados.php');

// Verificar se existe um token na URL
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header('Location: login.php');
    exit;
}

$token = $_GET['token'];
$tokenValido = false;
$usuarioInfo = null;
$mensagemErro = '';
$sucesso = false;

try {
    // Verificar se o token existe e não expirou
    $stmt = $pdo->prepare("
        SELECT r.usuario_id, r.tipo_usuario, r.validade, 
               CASE 
                   WHEN r.tipo_usuario = 'comunidade_escolar' THEN ce.nome_completo 
                   WHEN r.tipo_usuario = 'tecnico' THEN st.nome_completo 
               END as nome_completo,
               CASE 
                   WHEN r.tipo_usuario = 'comunidade_escolar' THEN ce.cargo 
                   WHEN r.tipo_usuario = 'tecnico' THEN 'Suporte Técnico' 
               END as tipo_cargo
        FROM recuperacao_senha r
        LEFT JOIN comunidade_escolar ce ON r.usuario_id = ce.id AND r.tipo_usuario = 'comunidade_escolar'
        LEFT JOIN suporte_tecnico st ON r.usuario_id = st.id AND r.tipo_usuario = 'tecnico'
        WHERE r.token = ? AND r.usado = 0 AND r.validade > NOW()
    ");
    $stmt->execute([$token]);
    $usuarioInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuarioInfo) {
        $tokenValido = true;
    } else {
        $mensagemErro = 'Token inválido ou expirado. Por favor, solicite um novo link de recuperação.';
    }
    
} catch (Exception $e) {
    error_log('Erro ao verificar token: ' . $e->getMessage());
    $mensagemErro = 'Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente.';
}

// Processar o formulário de alteração de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValido) {
    $senha = $_POST['senha'] ?? '';
    $confirmaSenha = $_POST['confirma_senha'] ?? '';
    
    // Validar a senha
    if (empty($senha)) {
        $mensagemErro = 'A senha é obrigatória.';
    } elseif (strlen($senha) < 8) {
        $mensagemErro = 'A senha deve ter pelo menos 8 caracteres.';
    } elseif ($senha !== $confirmaSenha) {
        $mensagemErro = 'As senhas não coincidem.';
    } else {
        try {
            // Atualizar a senha
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            
            // Determinar qual tabela atualizar
            if ($usuarioInfo['tipo_usuario'] === 'comunidade_escolar') {
                $tabela = 'comunidade_escolar';
            } else {
                $tabela = 'suporte_tecnico';
            }
            
            // Atualizar a senha na tabela correta
            $stmt = $pdo->prepare("UPDATE {$tabela} SET senha_hash = ?, primeiro_login = 0 WHERE id = ?");
            $stmt->execute([$senhaHash, $usuarioInfo['usuario_id']]);
            
            // Marcar o token como usado
            $stmt = $pdo->prepare("UPDATE recuperacao_senha SET usado = 1 WHERE token = ?");
            $stmt->execute([$token]);
            
            $sucesso = true;
            
        } catch (Exception $e) {
            error_log('Erro ao atualizar senha: ' . $e->getMessage());
            $mensagemErro = 'Ocorreu um erro ao atualizar sua senha. Por favor, tente novamente.';
        }
    }
}

// Limpar buffer para HTML
ob_end_clean();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Acesso - GM HelpDesk</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <style>
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

        .reset-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .reset-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
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

        .btn-login {
            margin-top: 15px;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 500;
            background-color: #2a4d69;
            color: white;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
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

        @media (max-width: 576px) {
            .reset-container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="card-body p-4">
                <?php if ($sucesso): ?>
                    <div class="text-center mb-4">
                        <div class="icon-container">
                            <i class="bi bi-check-circle-fill reset-icon text-success"></i>
                        </div>
                        <h2 class="h4 mb-3">Senha Alterada com Sucesso!</h2>
                        <p>A sua senha foi alterada com sucesso. Agora pode fazer login com a sua nova senha.</p>
                        <a href="login.php" class="btn btn-primary mt-3">Ir para Login</a>
                    </div>
                <?php elseif ($tokenValido): ?>
                    <div class="reset-header">
                        <div class="icon-container">
                            <i class="bi bi-lock reset-icon"></i>
                        </div>
                        <h2 class="h4 mb-3">Recuperar Palavra-passe</h2>
                        <p>Defina uma nova palavra-passe para a sua conta. Esta senha será usada para entrar na plataforma em conjunto com o seu email institucional.</p>
                    </div>
                    
                    <?php if (!empty($mensagemErro)): ?>
                        <div class="alert alert-danger"><?= $mensagemErro ?></div>
                    <?php endif; ?>
                    
                    <div class="user-info">
                        <div class="row">
                            <div class="col-sm-6 mb-2">
                                <label class="form-label text-muted">Nome</label>
                                <p class="mb-0 fw-bold"><?= htmlspecialchars($usuarioInfo['nome_completo']) ?></p>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <label class="form-label text-muted">Tipo</label>
                                <p class="mb-0 fw-bold"><?= htmlspecialchars($usuarioInfo['tipo_cargo']) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="senha" class="form-label">Nova Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="senha" name="senha" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="senha">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="confirma_senha" class="form-label">Confirmar Nova Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirma_senha" name="confirma_senha" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirma_senha">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 btn-reset">Alterar Palavra-passe</button>
                    </form>
                <?php else: ?>
                    <div class="text-center mb-4">
                        <div class="icon-container">
                            <i class="bi bi-exclamation-circle-fill reset-icon text-warning"></i>
                        </div>
                        <h2 class="h4 mb-3">Link Inválido ou Expirado</h2>
                        <p><?= $mensagemErro ?></p>
                        <a href="login.php" class="btn btn-login mt-3">Voltar para Login</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Redirecionar para login após 5 segundos se a senha foi alterada com sucesso
            <?php if ($sucesso): ?>
            setTimeout(function() {
                window.location.href = 'login.php';
            }, 5000);
            <?php endif; ?>
            
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
        });
    </script>
</body>
</html> 