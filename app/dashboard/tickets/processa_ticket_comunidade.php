<?php
// Iniciar sessão e verificar autenticação
session_start();
require_once('../../ligacao_base_dados.php');
require_once('funcoes/tickets_funcoes.php');

// Determinar se é uma requisição AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Ativar exibição de erros para depuração - apenas para log, não para exibição na tela
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Buffer de saída para suprimir qualquer saída antes do resultado final
ob_start();

// Função para log de debug - agora escreve para error_log em vez de exibir na tela
function debug_output($message, $data = null) {
    $log_message = "Debug: " . $message;
    
    if ($data !== null) {
        $log_message .= "\n" . print_r($data, true);
    }
    
    error_log($log_message);
}

// Checar a conexão com o banco de dados
if (!$pdo) {
    debug_output("ERRO CRÍTICO: Conexão PDO não está disponível!");
    exit;
} else {
    debug_output("Conexão PDO estabelecida", [
        'driver' => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
        'version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION)
    ]);
}

// Log para debug - registrar início do processamento
debug_output("Iniciando processamento do ticket da comunidade escolar");
debug_output("Dados POST", $_POST);
debug_output("Dados FILES", $_FILES);

// Teste de acesso às tabelas necessárias
try {
    $tables_to_check = ['tickets', 'ticket_componentes', 'ticket_historico', 'comunidade_escolar'];
    $table_status = [];
    
    foreach ($tables_to_check as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $table_status[$table] = ($stmt && $stmt->rowCount() > 0) ? "Existe" : "NÃO ENCONTRADA";
    }
    
    debug_output("Status das tabelas", $table_status);
} catch (Exception $e) {
    debug_output("Erro ao verificar tabelas", $e->getMessage());
}

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['userId'])) {
    debug_output("Erro de autenticação: usuário não está logado");
    header('Location: ../../login.php');
    exit;
}

// Identificar qual formato está sendo usado
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : $_SESSION['userId'];
$usuario_tipo = isset($_SESSION['usuario_tipo']) ? $_SESSION['usuario_tipo'] : (isset($_SESSION['userType']) ? $_SESSION['userType'] : '');
$usuario_nome = isset($_SESSION['userName']) ? $_SESSION['userName'] : '';

debug_output("Dados do usuário", [
    'ID' => $usuario_id,
    'Tipo' => $usuario_tipo,
    'Nome' => $usuario_nome
]);

// Verificar se o usuário é da comunidade escolar
if ($usuario_tipo !== 'comunidade_escolar') {
    debug_output("Erro de permissão: usuário não é da comunidade escolar");
    header('Location: ../../login.php');
    exit;
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    debug_output("Método inválido: não é um POST");
    header('Location: ticket_comunidade.php');
    exit;
}

// Validar campos obrigatórios
$campos_obrigatorios = ['assunto', 'sala_id', 'prioridade', 'descricao'];
$erro = false;

foreach ($campos_obrigatorios as $campo) {
    if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
        $erro = true;
        $mensagem_erro = "O campo $campo é obrigatório";
        debug_output("Erro de validação", $mensagem_erro);
        break;
    }
}

if ($erro) {
    // Redirecionar de volta com mensagem de erro
    $_SESSION['mensagem'] = [
        'tipo' => 'danger',
        'texto' => $mensagem_erro
    ];
    header('Location: ticket_comunidade.php');
    exit;
}

try {
    // Receber dados do formulário
    $assunto = $_POST['assunto'];
    $sala_id = $_POST['sala_id'];
    $prioridade = $_POST['prioridade'];
    $descricao = $_POST['descricao'];
    $data_criacao = $_POST['data_criacao'] ?? date('Y-m-d H:i:s');
    $mensagem_erro = $_POST['mensagem_erro'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';
    
    // Logs para debug
    debug_output("Dados validados com sucesso", [
        "Assunto" => $assunto,
        "Sala ID" => $sala_id,
        "Prioridade" => $prioridade,
        "Data criação" => $data_criacao
    ]);
    
    // Identificar o tipo de equipamento
    $tipo_equipamento = $_POST['tipo_equipamento'] ?? '';
    $componente_tipo = null;
    $componente_id = null;
    
    // Adicionar logs para debug
    debug_output("Informações do equipamento", [
        "Tipo" => $tipo_equipamento,
        "POST" => $_POST
    ]);
    
    if ($tipo_equipamento == 'computador') {
        $componente_tipo = 'computador';
        $componente_id = $_POST['componente_id'] ?? null;
        debug_output("Componente selecionado", [
            "Tipo" => "computador",
            "ID" => $componente_id
        ]);
    } elseif ($tipo_equipamento == 'componente') {
        $componente_tipo = $_POST['componente_problema'] ?? null;
        $componente_id = $_POST['componente_id'] ?? null;
        debug_output("Componente selecionado", [
            "Tipo" => $componente_tipo,
            "ID" => $componente_id
        ]);
    }
    
    // Verificar conexão com banco de dados
    if (!$pdo) {
        throw new Exception("Erro na conexão com o banco de dados");
    }
    
    // Testar query direto aqui para ver se funciona
    try {
        debug_output("Testando query simples no banco de dados");
        $test_stmt = $pdo->query("SELECT 1 AS test");
        if ($test_stmt) {
            $test_result = $test_stmt->fetch(PDO::FETCH_ASSOC);
            debug_output("Resultado do teste", $test_result);
        } else {
            debug_output("Erro no teste de query", $pdo->errorInfo());
        }
    } catch (Exception $e) {
        debug_output("Exceção no teste de query", $e->getMessage());
    }
    
    // Verificar se o componente já possui um ticket ativo
    if ($componente_tipo && $componente_id) {
        try {
        $query = "SELECT t.id, t.assunto, t.status 
                 FROM tickets t 
                 INNER JOIN ticket_componentes tc ON t.id = tc.ticket_id 
                 WHERE tc.equipamento = ? AND tc.ref_equipamento = ? 
                 AND t.status IN ('aberto', 'em_andamento')";
        
            debug_output("Verificando componente com query", [
                "Query" => $query,
                "Parâmetros" => [$componente_tipo, $componente_id]
            ]);
        
        $stmt = $pdo->prepare($query);
            if (!$stmt) {
                debug_output("Erro na preparação da query", $pdo->errorInfo());
                throw new Exception("Erro na preparação da consulta");
            }
            
            $result = $stmt->execute([$componente_tipo, $componente_id]);
            if (!$result) {
                debug_output("Erro na execução da query", $stmt->errorInfo());
                throw new Exception("Erro ao executar a consulta");
            }
        
        if ($ticket_existente = $stmt->fetch(PDO::FETCH_ASSOC)) {
                debug_output("Já existe um ticket ativo para este componente", $ticket_existente);
            throw new Exception("Já existe um ticket ativo (#{$ticket_existente['id']} - {$ticket_existente['assunto']}) para este componente. 
                                Não é possível criar um novo ticket até que o atual seja resolvido ou fechado.");
        }
        } catch (PDOException $e) {
            debug_output("Erro PDO ao verificar componente", [
                "Mensagem" => $e->getMessage(),
                "Código" => $e->getCode(),
                "Trace" => $e->getTraceAsString()
            ]);
            throw new Exception("Erro ao verificar o componente: " . $e->getMessage());
        }
    }
    
    // Verificar a definição da tabela tickets
    try {
        debug_output("Verificando estrutura da tabela tickets");
        $stmt = $pdo->query("DESCRIBE tickets");
        if ($stmt) {
            $ticket_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            debug_output("Estrutura da tabela tickets", $ticket_columns);
        } else {
            debug_output("Erro ao verificar estrutura da tabela tickets", $pdo->errorInfo());
        }
    } catch (Exception $e) {
        debug_output("Exceção ao verificar estrutura da tabela", $e->getMessage());
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
    
    debug_output("Dados preparados para criar ticket", $dados_ticket);
    
    // Criar o ticket - versão manual para debug
    debug_output("Tentando criar o ticket manualmente para debug");
    
    try {
        // Iniciar transação
        $pdo->beginTransaction();
        debug_output("Transação iniciada");
        
        // Buscar dados do solicitante
        $stmt = $pdo->prepare("SELECT * FROM comunidade_escolar WHERE id = ?");
        if (!$stmt) {
            debug_output("Erro ao preparar query para buscar solicitante", $pdo->errorInfo());
            throw new Exception("Erro na preparação da consulta de solicitante");
        }
        
        $result = $stmt->execute([$usuario_id]);
        if (!$result) {
            debug_output("Erro ao executar query para buscar solicitante", $stmt->errorInfo());
            throw new Exception("Erro ao buscar dados do solicitante");
        }
        
        $dados_solicitante = $stmt->fetch(PDO::FETCH_ASSOC);
        $solicitante = $dados_solicitante ? $dados_solicitante['nome_completo'] : 'Usuário Comunidade #' . $usuario_id;
        
        debug_output("Dados do solicitante", [
            "Nome" => $solicitante,
            "Dados" => $dados_solicitante
        ]);
        
        // Inserir o ticket diretamente
        $query = "INSERT INTO tickets (assunto, sala, descricao, mensagem_erro, observacoes_adicionais, 
                                     prioridade, id_usuario, solicitante, status, criado_em, entrada) 
                  VALUES (:assunto, :sala, :descricao, :mensagem_erro, :observacoes, 
                        :prioridade, :usuario_id, :solicitante, 'aberto', NOW(), :entrada)";
        
        debug_output("Query para inserção do ticket", $query);
        
        $stmt = $pdo->prepare($query);
        if (!$stmt) {
            debug_output("Erro ao preparar query para inserir ticket", $pdo->errorInfo());
            throw new Exception("Erro na preparação da inserção do ticket");
        }
        
        // Obter a hora atual para o campo 'entrada'
        $entrada_atual = date('H:i:s');
        
        $params = [
            ':assunto' => $assunto,
            ':sala' => $sala_id,
            ':descricao' => $descricao,
            ':mensagem_erro' => $mensagem_erro,
            ':observacoes' => $observacoes,
            ':prioridade' => $prioridade,
            ':usuario_id' => $usuario_id,
            ':solicitante' => $solicitante,
            ':entrada' => $entrada_atual
        ];
        
        debug_output("Parâmetros para inserção", $params);
        
        $result = $stmt->execute($params);
        if (!$result) {
            debug_output("Erro ao executar query para inserir ticket", $stmt->errorInfo());
            throw new Exception("Erro ao inserir o ticket");
        }
        
        $ticket_id = $pdo->lastInsertId();
    if (!$ticket_id) {
            debug_output("Erro: lastInsertId() retornou valor vazio");
            throw new Exception("Erro ao obter o ID do ticket inserido");
        }
        
        debug_output("Ticket inserido com sucesso", ["ID" => $ticket_id]);
        
        // Inserir no histórico
        $stmt = $pdo->prepare("INSERT INTO ticket_historico (ticket_id, acao, descricao, status_novo, data_acao) 
                              VALUES (:ticket_id, 'criado', 'Ticket criado', 'aberto', NOW())");
        
        if (!$stmt) {
            debug_output("Erro ao preparar query para histórico", $pdo->errorInfo());
            throw new Exception("Erro na preparação do histórico");
        }
        
        $result = $stmt->execute([':ticket_id' => $ticket_id]);
        if (!$result) {
            debug_output("Erro ao executar query para histórico", $stmt->errorInfo());
            throw new Exception("Erro ao inserir o histórico");
        }
        
        debug_output("Histórico registrado com sucesso");
        
        // Se tiver componente, inserir na tabela ticket_componentes
        if ($componente_tipo && $componente_id) {
            $stmt = $pdo->prepare("INSERT INTO ticket_componentes (ticket_id, equipamento, ref_equipamento) 
                                VALUES (:ticket_id, :equipamento, :ref_equipamento)");
            
            if (!$stmt) {
                debug_output("Erro ao preparar query para componente", $pdo->errorInfo());
                throw new Exception("Erro na preparação do componente");
            }
            
            $params = [
                ':ticket_id' => $ticket_id,
                ':equipamento' => $componente_tipo,
                ':ref_equipamento' => $componente_id
            ];
            
            $result = $stmt->execute($params);
            if (!$result) {
                debug_output("Erro ao executar query para componente", $stmt->errorInfo());
                throw new Exception("Erro ao inserir o componente");
    }
            
            debug_output("Componente registrado com sucesso");
        }
        
        // Confirmar a transação
        $pdo->commit();
        debug_output("Transação confirmada com sucesso");
        
        // Buscar os dados completos do ticket recém-criado
        $ticket_completo = null;
        try {
            $stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ?");
            $stmt->execute([$ticket_id]);
            $ticket_completo = $stmt->fetch(PDO::FETCH_ASSOC);
            debug_output("Dados completos do ticket obtidos", $ticket_completo);
        } catch (Exception $e) {
            debug_output("Erro ao buscar dados completos do ticket", $e->getMessage());
            // Não interromper por falha aqui
        }
        
        // Enviar emails de notificação
        try {
            // Carregar as funções de email
            $email_functions_path = __DIR__ . '/../../includes/email_funcoes.php';
            debug_output("Verificando arquivo de funções de email", $email_functions_path);
            
            if (file_exists($email_functions_path)) {
                require_once $email_functions_path;
                debug_output("Arquivo de funções de email carregado com sucesso");
                
                if (function_exists('enviarEmailNovoTicket') && function_exists('enviarConfirmacaoTicket')) {
                    // Determinar o tipo de solicitante
                    $tipo_solicitante = isset($dados_solicitante['cargo']) ? ucfirst($dados_solicitante['cargo']) : 'Comunidade Escolar';
                    
                    // Equipamento (se houver)
                    $equipamento_tipo_email = $componente_tipo ?? null;
                    $equipamento_ref_email = $componente_id ?? null;
                    
                    debug_output("Enviando email para suporte técnico", [
                        'destinatários' => 'Suporte Técnico',
                        'ticket_id' => $ticket_id,
                        'solicitante' => $solicitante
                    ]);
                    
                    // Enviar email para o suporte
                    $resultado_email_suporte = enviarEmailNovoTicket(
                        $pdo, 
                        $ticket_completo, 
                        $dados_solicitante, 
                        $tipo_solicitante,
                        $equipamento_ref_email,
                        $equipamento_tipo_email
                    );
                    
                    debug_output("Resultado do envio para suporte", $resultado_email_suporte ? "Sucesso" : "Falha");
                    
                    debug_output("Enviando email de confirmação para o solicitante", [
                        'destinatário' => $dados_solicitante['email_institucional'] ?? 'Email não disponível',
                        'ticket_id' => $ticket_id
                    ]);
                    
                    // Enviar email para o solicitante
                    $resultado_email_solicitante = enviarConfirmacaoTicket(
                        $pdo,
                        $ticket_completo,
                        $dados_solicitante
                    );
                    
                    debug_output("Resultado do envio para solicitante", $resultado_email_solicitante ? "Sucesso" : "Falha");
                } else {
                    debug_output("As funções de email necessárias não foram encontradas");
                }
            } else {
                debug_output("Arquivo de funções de email não encontrado", $email_functions_path);
            }
        } catch (Exception $e) {
            debug_output("Erro ao enviar emails de notificação", $e->getMessage());
            // Não interromper o processo por falha nos emails
    }
    
    // Processar anexos, se houver
    if (isset($_FILES['anexo']) && !empty($_FILES['anexo']['name'])) {
            debug_output("Anexo encontrado para processamento", $_FILES['anexo']);
        
        // Converter o anexo único para o formato de array esperado pela função
        $arquivo = [
            'name' => $_FILES['anexo']['name'],
            'type' => $_FILES['anexo']['type'],
            'tmp_name' => $_FILES['anexo']['tmp_name'],
            'error' => $_FILES['anexo']['error'],
            'size' => $_FILES['anexo']['size']
        ];
        
        // Colocar em um array para manter compatibilidade com a função existente
        $arquivos = [$arquivo];
        
            try {
        $resultado = salvarAnexosTicket($pdo, $ticket_id, $arquivos);
                debug_output("Resultado do salvamento de anexo", ($resultado ? 'Sucesso' : 'Falha'));
            } catch (Exception $e) {
                debug_output("Erro ao salvar anexo", $e->getMessage());
                // Não interromper o processo por erro em anexos
            }
        }
        
        // Redirecionar com mensagem de sucesso
        $_SESSION['mensagem'] = [
            'tipo' => 'success',
            'texto' => 'Ticket criado com sucesso!'
        ];
        
        // Limpar qualquer saída anterior para exibir apenas o popup bonito
        ob_end_clean();
        
        // Para requisições AJAX, retornar JSON
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'ticket_id' => $ticket_id,
                'message' => 'Ticket criado com sucesso!'
            ]);
            exit;
        }
        
        // Para requisições normais, mostrar o HTML
        // Substituir o debug output com um popup de sucesso estilizado
        ?>
        <!DOCTYPE html>
        <html lang="pt">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Ticket Criado - GM HelpDesk</title>
            <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%230d6efd' class='bi bi-tools' viewBox='0 0 16 16'><path d='M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.27 3.27a.997.997 0 0 0 1.414 0l1.586-1.586a.997.997 0 0 0 0-1.414l-3.27-3.27a1 1 0 0 0-1.023-.242L10.5 9.5l-.96-.96 2.68-2.643A3.005 3.005 0 0 0 16 3c0-.269-.035-.53-.102-.777l-2.14 2.141L12 4l-.364-1.757L13.777.102a3 3 0 0 0-3.675 3.68L7.462 6.46 4.793 3.793a1 1 0 0 1-.293-.707v-.071a1 1 0 0 0-.419-.814zm9.646 10.646a.5.5 0 0 1 .708 0l2.914 2.915a.5.5 0 0 1-.707.707l-2.915-2.914a.5.5 0 0 1 0-.708M3 11l.471.242.529.026.287.445.445.287.026.529L5 13l-.242.471-.026.529-.445.287-.287.445-.529.026L3 15l-.471-.242L2 14.732l-.287-.445L1.268 14l-.026-.529L1 13l.242-.471.026-.529.445-.287.287-.445.529-.026z'/></svg>">
            <!-- Bootstrap CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <!-- Bootstrap Icons -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
            <!-- Custom CSS -->
            <style>
                body {
                    min-height: 100vh;
                    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                    margin: 0;
                    padding: 0;
                    overflow-x: hidden;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }
                
                .success-popup {
                    background-color: white;
                    border-radius: 15px;
                    padding: 30px;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                    width: 350px;
                    text-align: center;
                    opacity: 0;
                    transform: scale(0.7);
                    animation: popupFadeIn 0.5s ease forwards;
                }
                
                @keyframes popupFadeIn {
                    0% {
                        opacity: 0;
                        transform: scale(0.7);
                    }
                    100% {
                        opacity: 1;
                        transform: scale(1);
                    }
                }
                
                .check-icon {
                    position: relative;
                    width: 80px;
                    height: 80px;
                    margin: 0 auto 20px;
                    border-radius: 50%;
                    background-color: rgba(40, 167, 69, 0.1);
                    border: 3px solid #28a745;
                    box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
                    animation: pulseIcon 0.6s ease-out;
                }
                
                @keyframes pulseIcon {
                    0% {
                        transform: scale(0);
                        opacity: 0;
                    }
                    50% {
                        transform: scale(1.1);
                        opacity: 0.7;
                    }
                    100% {
                        transform: scale(1);
                        opacity: 1;
                    }
                }
                
                .check-icon svg {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    width: 40px;
                    height: 40px;
                }
                
                .check-icon svg path {
                    stroke-dasharray: 80;
                    stroke-dashoffset: 80;
                    animation: drawCheck 0.6s ease-in-out forwards 0.3s;
                }
                
                @keyframes drawCheck {
                    0% {
                        stroke-dashoffset: 80;
                    }
                    100% {
                        stroke-dashoffset: 0;
                    }
                }
                
                .success-popup h3 {
                    font-size: 24px;
                    color: #333;
                    font-weight: 600;
                    margin-bottom: 15px;
                }
                
                .success-popup p {
                    font-size: 16px;
                    color: #666;
                    margin-bottom: 20px;
                }
                
                .ticket-id {
                    font-size: 20px;
                    font-weight: 700;
                    color: #28a745;
                    margin-bottom: 20px;
                    display: inline-block;
                }
                
                .redirect-text {
                    font-size: 14px;
                    color: #6c757d;
                    margin-top: 15px;
                }
            </style>
        </head>
        <body>
            <div class="success-popup">
                <div class="check-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <path fill="none" stroke="#28a745" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" d="M14,27 L22,35 L38,17" />
                    </svg>
                </div>
                <h3>Ticket criado com sucesso!</h3>
                <p>O seu ticket foi registrado no sistema e será atendido em breve.</p>
                <div class="ticket-id">Ticket #<?php echo $ticket_id; ?></div>
                <div class="redirect-text">
                    A redirecionar para o Dashboard em <span id="countdown">7</span> segundos...
                </div>
            </div>
            
            <script>
                // Função para contar regressivamente
                let seconds = 7;
                const countdownElement = document.getElementById('countdown');
                
                const countdownInterval = setInterval(() => {
                    seconds--;
                    countdownElement.textContent = seconds;
                    
                    if (seconds <= 0) {
                        clearInterval(countdownInterval);
                        window.location.href = '../dashboardce.php';
                    }
                }, 1000);
                
                // Redirecionar após 7 segundos, caso o contador falhe
                setTimeout(() => {
                    window.location.href = '../dashboardce.php';
                }, 7000);
            </script>
        </body>
        </html>
        <?php
        exit;
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
            debug_output("Transação revertida devido a erro");
        }
        
        debug_output("PDOException durante criação manual do ticket", [
            "Mensagem" => $e->getMessage(),
            "Código" => $e->getCode(),
            "Trace" => $e->getTraceAsString(),
            "ErrorInfo" => $pdo->errorInfo()
        ]);
        
        throw new Exception("Erro de banco de dados: " . $e->getMessage());
    }
    
} catch (PDOException $e) {
    // Log detalhado de erro PDO
    debug_output("PDOException ao processar ticket", [
        "Mensagem" => $e->getMessage(),
        "Código" => $e->getCode(),
        "Trace" => $e->getTraceAsString()
    ]);
    
    // Gerar mensagem de erro detalhada para depuração
    $erro_detalhado = "Erro de banco de dados: " . $e->getMessage() . " (Código: " . $e->getCode() . ")";
    
    // Redirecionar com mensagem de erro
    $_SESSION['mensagem'] = [
        'tipo' => 'danger',
        'texto' => 'Erro ao criar ticket: ' . $erro_detalhado
    ];
    
    // Limpar qualquer output anterior
    ob_end_clean();
    
    // Para requisições AJAX, retornar JSON
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao criar ticket: ' . $e->getMessage()
        ]);
    exit;
    }
    
    // Mostrar página de erro amigável
    ?>
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erro ao Criar Ticket - GM HelpDesk</title>
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23dc3545' class='bi bi-exclamation-triangle' viewBox='0 0 16 16'><path d='M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z'/><path d='M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z'/></svg>">
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <!-- Custom CSS -->
        <style>
            body {
                min-height: 100vh;
                background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                margin: 0;
                padding: 0;
                overflow-x: hidden;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            .error-container {
                background-color: white;
                border-radius: 15px;
                padding: 30px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                width: 500px;
                max-width: 90%;
                text-align: center;
                animation: fadeIn 0.5s ease forwards;
            }
            
            @keyframes fadeIn {
                0% {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .error-icon {
                width: 80px;
                height: 80px;
                margin: 0 auto 20px;
                border-radius: 50%;
                background-color: rgba(220, 53, 69, 0.1);
                border: 3px solid #dc3545;
                display: flex;
                align-items: center;
                justify-content: center;
                animation: pulseIcon 0.6s ease-out;
            }
            
            @keyframes pulseIcon {
                0% {
                    transform: scale(0);
                    opacity: 0;
                }
                50% {
                    transform: scale(1.1);
                    opacity: 0.7;
                }
                100% {
                    transform: scale(1);
                    opacity: 1;
                }
            }
            
            .error-icon i {
                color: #dc3545;
                font-size: 40px;
            }
            
            .error-container h3 {
                font-size: 24px;
                color: #333;
                font-weight: 600;
                margin-bottom: 15px;
            }
            
            .error-container p {
                font-size: 16px;
                color: #666;
                margin-bottom: 20px;
            }
            
            .error-message {
                background-color: rgba(220, 53, 69, 0.1);
                border-left: 4px solid #dc3545;
                padding: 15px;
                text-align: left;
                margin-bottom: 20px;
                border-radius: 5px;
            }
            
            .btn-custom {
                padding: 10px 20px;
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.3s ease;
            }
            
            .btn-custom:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            
            .redirect-text {
                font-size: 14px;
                color: #6c757d;
                margin-top: 15px;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <h3>Erro ao criar o ticket</h3>
            <p>Desculpe, ocorreu um erro durante a criação do ticket. Nossa equipe técnica foi notificada.</p>
            
            <div class="error-message">
                <small>Detalhes técnicos:</small>
                <p class="mb-0"><?php echo htmlspecialchars($e->getMessage()); ?></p>
            </div>
            
            <div class="d-grid gap-2">
                <a href="ticket_comunidade.php" class="btn btn-primary btn-custom">
                    <i class="bi bi-arrow-left me-2"></i>Voltar ao formulário
                </a>
                <a href="../dashboardce.php" class="btn btn-outline-secondary btn-custom">
                    <i class="bi bi-house me-2"></i>Ir para o Dashboard
                </a>
            </div>
            
            <div class="redirect-text">
                A redirecionar para o Dashboard em <span id="countdown">7</span> segundos...
            </div>
        </div>
        
        <script>
            // Função para contar regressivamente
            let seconds = 7;
            const countdownElement = document.getElementById('countdown');
            
            const countdownInterval = setInterval(() => {
                seconds--;
                countdownElement.textContent = seconds;
                
                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = '../dashboardce.php';
                }
            }, 1000);
            
            // Redirecionar após 7 segundos, caso o contador falhe
            setTimeout(() => {
                window.location.href = '../dashboardce.php';
            }, 7000);
        </script>
    </body>
    </html>
    <?php
    exit;
} catch (Exception $e) {
    // Log detalhado do erro
    debug_output("Exception ao processar ticket", [
        "Mensagem" => $e->getMessage(),
        "Trace" => $e->getTraceAsString()
    ]);
    
    // Redirecionar com mensagem de erro
    $_SESSION['mensagem'] = [
        'tipo' => 'danger',
        'texto' => 'Erro ao criar ticket: ' . $e->getMessage()
    ];
    
    // Limpar qualquer output anterior
    ob_end_clean();
    
    // Para requisições AJAX, retornar JSON
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao criar ticket: ' . $e->getMessage()
        ]);
        exit;
    }
    
    // Mostrar página de erro amigável
    ?>
    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erro ao Criar Ticket - GM HelpDesk</title>
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23dc3545' class='bi bi-exclamation-triangle' viewBox='0 0 16 16'><path d='M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z'/><path d='M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z'/></svg>">
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <!-- Custom CSS -->
        <style>
            body {
                min-height: 100vh;
                background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                margin: 0;
                padding: 0;
                overflow-x: hidden;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            .error-container {
                background-color: white;
                border-radius: 15px;
                padding: 30px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                width: 500px;
                max-width: 90%;
                text-align: center;
                animation: fadeIn 0.5s ease forwards;
            }
            
            @keyframes fadeIn {
                0% {
                    opacity: 0;
                    transform: translateY(-20px);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .error-icon {
                width: 80px;
                height: 80px;
                margin: 0 auto 20px;
                border-radius: 50%;
                background-color: rgba(220, 53, 69, 0.1);
                border: 3px solid #dc3545;
                display: flex;
                align-items: center;
                justify-content: center;
                animation: pulseIcon 0.6s ease-out;
            }
            
            @keyframes pulseIcon {
                0% {
                    transform: scale(0);
                    opacity: 0;
                }
                50% {
                    transform: scale(1.1);
                    opacity: 0.7;
                }
                100% {
                    transform: scale(1);
                    opacity: 1;
                }
            }
            
            .error-icon i {
                color: #dc3545;
                font-size: 40px;
            }
            
            .error-container h3 {
                font-size: 24px;
                color: #333;
                font-weight: 600;
                margin-bottom: 15px;
            }
            
            .error-container p {
                font-size: 16px;
                color: #666;
                margin-bottom: 20px;
            }
            
            .error-message {
                background-color: rgba(220, 53, 69, 0.1);
                border-left: 4px solid #dc3545;
                padding: 15px;
                text-align: left;
                margin-bottom: 20px;
                border-radius: 5px;
            }
            
            .btn-custom {
                padding: 10px 20px;
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.3s ease;
            }
            
            .btn-custom:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            
            .redirect-text {
                font-size: 14px;
                color: #6c757d;
                margin-top: 15px;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <h3>Erro ao criar o ticket</h3>
            <p>Desculpe, ocorreu um erro durante a criação do ticket.</p>
            
            <div class="error-message">
                <small>Detalhes técnicos:</small>
                <p class="mb-0"><?php echo htmlspecialchars($e->getMessage()); ?></p>
            </div>
            
            <div class="d-grid gap-2">
                <a href="ticket_comunidade.php" class="btn btn-primary btn-custom">
                    <i class="bi bi-arrow-left me-2"></i>Voltar ao formulário
                </a>
                <a href="../dashboardce.php" class="btn btn-outline-secondary btn-custom">
                    <i class="bi bi-house me-2"></i>Ir para o Dashboard
                </a>
            </div>
            
            <div class="redirect-text">
                A redirecionar para o Dashboard em <span id="countdown">7</span> segundos...
            </div>
        </div>
        
        <script>
            // Função para contar regressivamente
            let seconds = 7;
            const countdownElement = document.getElementById('countdown');
            
            const countdownInterval = setInterval(() => {
                seconds--;
                countdownElement.textContent = seconds;
                
                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = '../dashboardce.php';
                }
            }, 1000);
            
            // Redirecionar após 7 segundos, caso o contador falhe
            setTimeout(() => {
                window.location.href = '../dashboardce.php';
            }, 7000);
        </script>
    </body>
    </html>
    <?php
    exit;
} 