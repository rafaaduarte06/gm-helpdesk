<?php
// Iniciar sessão e verificar autenticação
session_start();
require_once('../../ligacao_base_dados.php');
require_once('funcoes/tickets_funcoes.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['userId'])) {
    // Se for uma solicitação AJAX, retornar erro em JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
        exit;
    } else {
        header('Location: ../../login.php');
        exit;
    }
}

// Identificar qual formato está sendo usado
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : $_SESSION['userId'];
$usuario_tipo = isset($_SESSION['usuario_tipo']) ? $_SESSION['usuario_tipo'] : (isset($_SESSION['userType']) ? $_SESSION['userType'] : '');
$usuario_nome = isset($_SESSION['userName']) ? $_SESSION['userName'] : '';

// Verificar se o usuário tem permissão (técnico, admin ou comunidade escolar)
if ($usuario_tipo !== 'tecnico' && $usuario_tipo !== 'admin' && $usuario_tipo !== 'comunidade_escolar') {
    // Se for uma solicitação AJAX, retornar erro em JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Você não tem permissão para executar esta ação.']);
        exit;
    } else {
        header('Location: ../../login.php');
        exit;
    }
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirecionar para página adequada com base no tipo de usuário
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
        exit;
    } else {
        if ($usuario_tipo === 'comunidade_escolar') {
            header('Location: ticket_comunidade.php');
        } else {
            header('Location: tickets.php');
        }
        exit;
    }
}

// Detectar se é uma solicitação AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Verificar se foi solicitado formato ajax via form
$usingAjaxFormat = isset($_POST['formato']) && $_POST['formato'] === 'ajax';

try {
    // Validar campos obrigatórios
    $campos_obrigatorios = ['assunto', 'sala_id', 'prioridade', 'descricao'];
    $erro = false;
    $error_message = '';

    foreach ($campos_obrigatorios as $campo) {
        if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
            $erro = true;
            $error_message = "O campo $campo é obrigatório";
            break;
        }
    }

    if ($erro) {
        // Retornar erro em formato apropriado
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $error_message]);
            exit;
        } else {
            if ($usuario_tipo === 'comunidade_escolar') {
                header('Location: ticket_comunidade.php?erro=' . urlencode($error_message));
            } else {
                header('Location: tickets.php?acao=criar&erro=' . urlencode($error_message));
            }
            exit;
        }
    }

    // Receber dados do formulário
    $assunto = $_POST['assunto'];
    $sala_id = $_POST['sala_id'];
    $prioridade = $_POST['prioridade'];
    $descricao = $_POST['descricao'];
    $data_criacao = $_POST['data_criacao'] ?? date('Y-m-d H:i:s');
    $mensagem_erro = $_POST['mensagem_erro'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';
    
    // Adicionar logs para debug
    error_log("Dados do POST para criação de ticket: " . print_r($_POST, true));
    error_log("Sala ID recebido: " . $sala_id);
    
    // Identificar o tipo de equipamento
    $tipo_equipamento = $_POST['tipo_equipamento'] ?? '';
    $componente_tipo = null;
    $componente_id = null;
    
    if ($tipo_equipamento == 'computador') {
        $componente_tipo = 'computador';
        $componente_id = $_POST['componente_id'] ?? null;
    } elseif ($tipo_equipamento == 'componente') {
        $componente_tipo = $_POST['componente_problema'] ?? null;
        $componente_id = $_POST['componente_id'] ?? null;
    }
    
    // Verificar se o componente já possui um ticket ativo
    if ($componente_tipo && $componente_id) {
        $query = "SELECT t.id, t.assunto, t.status 
                 FROM tickets t 
                 INNER JOIN ticket_componentes tc ON t.id = tc.ticket_id 
                 WHERE tc.equipamento = ? AND tc.ref_equipamento = ? 
                 AND t.status IN ('aberto', 'em_andamento')";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$componente_tipo, $componente_id]);
        
        if ($ticket_existente = $stmt->fetch(PDO::FETCH_ASSOC)) {
            throw new Exception("Já existe um ticket ativo (#{$ticket_existente['id']} - {$ticket_existente['assunto']}) para este componente. 
                               Não é possível criar um novo ticket até que o atual seja resolvido ou fechado.");
        }
    }
    
    // Preparar dados para inserção
    $dados_ticket = [
        'usuario_id' => $usuario_id,
        'sala' => $sala_id,
        'assunto' => $assunto,
        'descricao' => $descricao,
        'mensagem_erro' => $mensagem_erro,
        'observacoes' => $observacoes,
        'prioridade' => $prioridade,
        'tipo_componente' => $componente_tipo,
        'componente_id' => $componente_id
    ];
    
    // Criar o ticket
    $ticket_id = criarTicket($pdo, $dados_ticket);
    
    if (!$ticket_id) {
        throw new Exception("Não foi possível criar o ticket");
    }
    
    // Processar anexos, se houver
    if (isset($_FILES['anexo']) && !empty($_FILES['anexo']['name'])) {
        // Logar informações para debug
        error_log("Anexo encontrado no formulário!");
        error_log("Dados do anexo: " . print_r($_FILES['anexo'], true));
        
        // Converter o anexo único para o formato de array esperado pela função
        $arquivo = [
            'name' => $_FILES['anexo']['name'],
            'type' => $_FILES['anexo']['type'],
            'tmp_name' => $_FILES['anexo']['tmp_name'],
            'error' => $_FILES['anexo']['error'],
            'size' => $_FILES['anexo']['size']
        ];
        
        error_log("Anexo preparado para processamento: " . $arquivo['name']);
        
        // Colocar em um array para manter compatibilidade com a função existente
        $arquivos = [$arquivo];
        
        $resultado = salvarAnexosTicket($pdo, $ticket_id, $arquivos);
        error_log("Resultado do salvamento de anexo: " . ($resultado ? 'Sucesso' : 'Falha'));
    } else {
        error_log("Nenhum anexo encontrado no formulário para o ticket ID: $ticket_id");
        if (isset($_FILES['anexo'])) {
            error_log("Array de anexo existe, mas está vazio: " . print_r($_FILES['anexo'], true));
        }
    }
    
    // Responder de acordo com o tipo de solicitação
    if ($isAjax) {
        // Resposta em formato JSON para solicitações AJAX
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Ticket criado com sucesso!',
            'ticket_id' => $ticket_id
        ]);
        exit;
    } elseif ($usingAjaxFormat) {
        // Redirecionar de volta para tickets.php com parâmetro de sucesso
        header('Location: tickets.php?sucesso=1&ticket_id=' . $ticket_id);
        exit;
    } else {
        // Redirecionar com mensagem de sucesso para página apropriada
        $_SESSION['mensagem'] = [
            'tipo' => 'success',
            'texto' => "Ticket criado com sucesso! <br>O seu ticket foi registrado no sistema e será atendido em breve.<br>Ticket #{$ticket_id}"
        ];
        
        if ($usuario_tipo === 'comunidade_escolar') {
            header('Location: ../dashboardce.php');
        } else {
            header('Location: ../tickets/dashboard.php');
        }
        exit;
    }
    
} catch (Exception $e) {
    // Lidar com erros
    $error_message = 'Erro ao criar ticket: ' . $e->getMessage();
    error_log($error_message);
    
    if ($isAjax) {
        // Resposta em formato JSON para solicitações AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error_message]);
        exit;
    } else {
        // Redirecionar com mensagem de erro para página apropriada
        $_SESSION['mensagem'] = [
            'tipo' => 'danger',
            'texto' => $error_message
        ];
        
        if ($usuario_tipo === 'comunidade_escolar') {
            header('Location: ticket_comunidade.php');
        } else {
            header('Location: tickets.php');
        }
        exit;
    }
} 