<?php
// Iniciar buffer de saída
ob_start();

session_start();
include('ligacao_base_dados.php');
include('includes/email_funcoes.php');

header('Content-Type: application/json');

// Verificar se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Obter o email da requisição
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email não fornecido']);
    exit;
}

try {
    // Verificar se o email existe na tabela comunidade_escolar
    $stmt = $pdo->prepare("SELECT id, nome_completo, cargo FROM comunidade_escolar WHERE email_institucional = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    $tipo_usuario = 'comunidade_escolar';
    
    // Se não encontrou na comunidade_escolar, verificar na tabela suporte_tecnico
    if (!$usuario) {
        $stmt = $pdo->prepare("SELECT id, nome_completo FROM suporte_tecnico WHERE email_institucional = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        $tipo_usuario = 'tecnico';
        
        // Definir o cargo como "Suporte Técnico" para todos os técnicos
        if ($usuario) {
            $usuario['cargo'] = 'Suporte Técnico';
        }
    }
    
    // Se não encontrou em nenhuma tabela
    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Email não encontrado']);
        exit;
    }
    
    // Gerar um token único
    $token = bin2hex(random_bytes(32));
    $validade = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Armazenar o token no banco de dados
    $stmt = $pdo->prepare("INSERT INTO recuperacao_senha (usuario_id, tipo_usuario, token, validade) 
                          VALUES (?, ?, ?, ?)");
    $stmt->execute([$usuario['id'], $tipo_usuario, $token, $validade]);
    
    // Enviar email com o link de recuperação
    $nome = $usuario['nome_completo'];
    $tipo = $usuario['cargo'];
    // URL base: APP_URL da configuração ou, se vazio, a pasta onde a aplicação está a correr
    $baseUrl = obterConfig('APP_URL') ?: "http://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $link = rtrim($baseUrl, '/') . "/recuperar_acesso.php?token=$token";
    
    // Verificar se o envio do email foi bem-sucedido
    if (enviarEmailRecuperacao($email, $nome, $tipo, $link)) {
        echo json_encode(['success' => true, 'message' => 'Email de recuperação enviado com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao enviar email de recuperação']);
    }
    
} catch (Exception $e) {
    error_log("Erro na recuperação de senha: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao processar a solicitação']);
}

// Limpar e enviar o buffer
ob_end_flush(); 