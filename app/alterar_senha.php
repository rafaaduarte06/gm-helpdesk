<?php
session_start();

// Verificar se o usuário está logado e precisa mudar a senha
if (!isset($_SESSION['userId']) || !isset($_SESSION['require_password_change'])) {
    header('Location: login.php');
    exit;
}

require_once('ligacao_base_dados.php');

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novaSenha = $_POST['nova_senha'];
    $confirmarSenha = $_POST['confirmar_senha'];

    if (strlen($novaSenha) < 8) {
        $error = "A senha deve ter pelo menos 8 caracteres";
    } elseif ($novaSenha !== $confirmarSenha) {
        $error = "As senhas não coincidem";
    } else {
        try {
            $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            
            // Selecionar a tabela correta baseado no tipo de usuário
            $tabela = $_SESSION['userType'] === 'tecnico' ? 'suporte_tecnico' : 'comunidade_escolar';
            
            $stmt = $pdo->prepare("
                UPDATE {$tabela}
                SET senha_hash = ?, primeiro_login = FALSE 
                WHERE id = ?
            ");
            
            $stmt->execute([$senhaHash, $_SESSION['userId']]);
            
            // Limpar a flag de alteração obrigatória
            unset($_SESSION['require_password_change']);
            
            $success = "A tua senha foi alterada com sucesso! A redirecionar para a página principal...";
            
            // Redirecionar após 2 segundos
            if ($_SESSION['userType'] === 'tecnico') {
                header("Refresh: 2; URL=dashboard/tickets/dashboard.php");
            } else if ($_SESSION['userType'] === 'comunidade_escolar') {
                header("Refresh: 2; URL=dashboard/dashboardce.php");
            } else {
                header("Refresh: 2; URL=dashboard/" . $_SESSION['userType'] . ".php");
            }
        } catch (Exception $e) {
            $error = "Erro ao alterar a senha: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Senha - Primeiro Acesso</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/variables.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .password-container {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            z-index: 10;
            color: #6c757d;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 0 10px;
            margin-top: 17px;
        }
        
        .toggle-password:hover {
            color: #0d6efd;
        }
        
        .card-header {
            background: linear-gradient(135deg, #2a4d69, rgb(36, 110, 167));
            color: white;
            text-align: center;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }
        
        .card-header .icon-container {
            background-color: rgba(255, 255, 255, 0.15);
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .card-header i {
            font-size: 2.5rem;
            color: white;
        }
        
        .card-header h4 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .card-header p {
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .form-label {
            font-weight: 500;
            color: #495057;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 0.6rem 1rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: none;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            border: none;
            border-radius: 10px;
            padding: 0.8rem 1.5rem;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.25);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0b5ed7, #0a58ca);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(13, 110, 253, 0.35);
        }
        
        .invalid-feedback {
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }
        
        .alert {
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .alert-success {
            background-color: #d1e7dd;
            border-color: #badbcc;
            color: #0f5132;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c2c7;
            color: #842029;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0">
                    <div class="card-header border-0">
                        <div class="icon-container">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <h4>ALTERAR SENHA</h4>
                        <p>Por favor, define uma nova senha</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php else: ?>
                            <form method="POST" class="needs-validation" novalidate>
                                <div class="mb-4 password-container">
                                    <label class="form-label">Nova Senha</label>
                                    <input type="password" 
                                           class="form-control" 
                                           name="nova_senha" 
                                           required 
                                           minlength="8">
                                    <i class="bi bi-eye-slash toggle-password"></i>
                                    <div class="invalid-feedback">
                                        A senha deve ter pelo menos 8 caracteres
                                    </div>
                                </div>

                                <div class="mb-4 password-container">
                                    <label class="form-label">Confirmar Nova Senha</label>
                                    <input type="password" 
                                           class="form-control" 
                                           name="confirmar_senha" 
                                           required>
                                    <i class="bi bi-eye-slash toggle-password"></i>
                                    <div class="invalid-feedback">
                                        As senhas devem coincidir
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-check-circle me-2"></i>Alterar Senha
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                if (input.type === 'password') {
                    input.type = 'text';
                    this.classList.remove('bi-eye-slash');
                    this.classList.add('bi-eye');
                } else {
                    input.type = 'password';
                    this.classList.remove('bi-eye');
                    this.classList.add('bi-eye-slash');
                }
            });
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    </script>
</body>
</html> 