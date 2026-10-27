<?php
/**
 * Funções para envio de emails usando PHPMailer
 */

// Incluir o autoload do Composer para carregar o PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';

// Importar classes do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../config.php';

/**
 * Aplica a configuração SMTP (lida de config.php) a uma instância do PHPMailer
 *
 * @param PHPMailer $mail Instância a configurar
 */
function configurarSMTP(PHPMailer $mail)
{
    $mail->isSMTP();
    $mail->Host = obterConfig('SMTP_HOST');
    $mail->Port = (int) obterConfig('SMTP_PORT');
    $mail->SMTPAuth = filter_var(obterConfig('SMTP_AUTH'), FILTER_VALIDATE_BOOLEAN);
    $mail->Username = obterConfig('SMTP_USER');
    $mail->Password = obterConfig('SMTP_PASS');

    $seguranca = obterConfig('SMTP_SECURE');
    if ($seguranca === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } elseif ($seguranca === 'tls') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } else {
        $mail->SMTPSecure = '';
        $mail->SMTPAutoTLS = false;
    }

    $mail->CharSet = 'UTF-8';
}

/**
 * Envia um email para o suporte técnico quando um novo ticket é criado
 * 
 * @param PDO $pdo Conexão com o banco de dados
 * @param array $ticket Dados do ticket criado
 * @param array $solicitante Dados do solicitante
 * @param string $tipo_solicitante Tipo do solicitante (Aluno/Professor/Funcionário)
 * @param string $equipamento_ref Referência do equipamento (opcional)
 * @param string $equipamento_tipo Tipo do equipamento (opcional)
 * @return bool True se o email foi enviado com sucesso, False caso contrário
 */
function enviarEmailNovoTicket($pdo, $ticket, $solicitante, $tipo_solicitante, $equipamento_ref = null, $equipamento_tipo = null)
{
    $mail = new PHPMailer(true);

    try {
        // Habilitar debug para diagnóstico
        $mail->SMTPDebug = 0; // 0 = sem saída, 1 = comandos, 2 = comandos e dados, 4 = baixo nível
        error_log("Iniciando configuração de PHPMailer para email de novo ticket");
        
        // Configuração do servidor SMTP
        configurarSMTP($mail);

        // Configuração do remetente
        $mail->setFrom(obterConfig('MAIL_FROM'), obterConfig('MAIL_FROM_NAME'));
        error_log("Remetente configurado: " . obterConfig('MAIL_FROM'));
        
        // Buscar todos os emails de suporte técnico da base de dados
        $tecnicos_emails = array(); // Array para armazenar emails dos técnicos
        
        // Garantir que o PDO está disponível e executar a consulta
        if ($pdo) {
            try {
                error_log("Buscando emails dos técnicos no banco de dados");
                $stmt = $pdo->query("SELECT email_institucional, nome_completo FROM suporte_tecnico");
                if ($stmt) {
                    $tecnicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Adicionar técnicos como destinatários se houver resultados
                    if (count($tecnicos) > 0) {
                        error_log("Encontrados " . count($tecnicos) . " técnicos no banco de dados");
                        foreach ($tecnicos as $tecnico) {
                            $mail->addAddress($tecnico['email_institucional'], $tecnico['nome_completo']);
                            $tecnicos_emails[] = $tecnico['email_institucional']; // Armazenar para log
                        }
                        error_log("Emails de técnicos adicionados: " . implode(", ", $tecnicos_emails));
                    } else {
                        error_log("Nenhum técnico encontrado na consulta. Usando email de fallback.");
                        // Adicionar ao menos um email para testes
                        $mail->addAddress(obterConfig('MAIL_FROM'), 'Suporte Técnico'); // Fallback
                        error_log("Email de fallback adicionado: " . obterConfig('MAIL_FROM'));
                    }
                } else {
                    error_log("Falha ao executar consulta SQL para técnicos. Erro: " . print_r($pdo->errorInfo(), true));
                    $mail->addAddress(obterConfig('MAIL_FROM'), 'Suporte Técnico'); // Fallback
                    error_log("Email de fallback adicionado: " . obterConfig('MAIL_FROM'));
                }
            } catch (Exception $e) {
                error_log("Erro ao buscar emails dos técnicos: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
                $mail->addAddress(obterConfig('MAIL_FROM'), 'Suporte Técnico'); // Fallback
                error_log("Email de fallback adicionado: " . obterConfig('MAIL_FROM'));
            }
        } else {
            error_log("Objeto PDO não disponível para consulta de técnicos. Usando email de fallback.");
            $mail->addAddress(obterConfig('MAIL_FROM'), 'Suporte Técnico'); // Fallback
            error_log("Email de fallback adicionado: " . obterConfig('MAIL_FROM'));
        }

        // Verificar se recebemos um ID em vez da referência e buscar a referência correta
        if ($equipamento_ref && $equipamento_tipo && is_numeric($equipamento_ref)) {
            // Obtém a referência correta do banco de dados com base no tipo e ID
            $referencia_correta = buscarReferenciaEquipamento($pdo, $equipamento_tipo, $equipamento_ref);
            
            // Se encontrou uma referência, substitui
            if ($referencia_correta) {
                error_log("Substituindo ID ({$equipamento_ref}) pela referência correta: {$referencia_correta}");
                $equipamento_ref = $referencia_correta;
            } else {
                error_log("Não foi possível encontrar a referência para o equipamento: tipo={$equipamento_tipo}, id={$equipamento_ref}");
            }
        }

        // Conteúdo do email
        error_log("Configurando conteúdo do email");
        $mail->isHTML(true);
        $mail->Subject = 'Novo Ticket: ' . $ticket['assunto'];
        error_log("Assunto do email: " . $mail->Subject);
        
        // Formatar a data de criação do ticket
        $data_criacao = isset($ticket['criado_em']) ? date('d/m/Y H:i', strtotime($ticket['criado_em'])) : date('d/m/Y H:i');
        
        // Preparar informações do equipamento
        $info_equipamento = '';
        if ($equipamento_ref && $equipamento_tipo) {
            $info_equipamento = "<p><b>Equipamento:</b> " . htmlspecialchars($equipamento_tipo) . " (Ref: " . htmlspecialchars($equipamento_ref) . ")</p>";
            error_log("Informações de equipamento adicionadas: Tipo=$equipamento_tipo, Ref=$equipamento_ref");
        }
        
        // Formatar status e prioridade com primeira letra maiúscula
        $status_formatado = formatarStatusParaExibicao($ticket['status']);
        $prioridade_formatada = formatarPrioridadeParaExibicao($ticket['prioridade']);
        
        // Buscar o nome da sala em vez do ID
        $sala_nome = $ticket['sala'];
        if (is_numeric($ticket['sala'])) {
            try {
                $stmt = $pdo->prepare("SELECT sala FROM salas WHERE id_sala = ?");
                $stmt->execute([$ticket['sala']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result) {
                    $sala_nome = $result['sala'];
                    error_log("Nome da sala encontrado: " . $sala_nome);
                } else {
                    error_log("Nome da sala não encontrado para ID: " . $ticket['sala']);
                }
            } catch (Exception $e) {
                // Em caso de erro, manter o valor original
                error_log("Erro ao buscar nome da sala: " . $e->getMessage());
            }
        }
        
        // Corpo do email com CSS
        $mail->Body = "
        <html>
        <head>
            <title>Novo Ticket Criado</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
                .header {
                    background-color: #0d6efd;
                    color: white;
                    padding: 15px;
                    text-align: center;
                    border-radius: 5px 5px 0 0;
                    margin-bottom: 20px;
                }
                .info {
                    margin-bottom: 20px;
                }
                .info-block {
                    background-color: #f9f9f9;
                    padding: 15px;
                    border-radius: 5px;
                    margin-bottom: 15px;
                }
                .footer {
                    margin-top: 30px;
                    font-size: 12px;
                    color: #777;
                    text-align: center;
                    border-top: 1px solid #ddd;
                    padding-top: 10px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Novo Ticket Criado</h2>
                </div>
                
                <div class='info'>
                    <div class='info-block'>
                        <h3>Detalhes do Ticket</h3>
                        <p><b>ID do Ticket:</b> #{$ticket['id']}</p>
                        <p><b>Assunto:</b> " . htmlspecialchars($ticket['assunto']) . "</p>
                        <p><b>Estado:</b> " . $status_formatado . "</p>
                        <p><b>Prioridade:</b> " . $prioridade_formatada . "</p>
                        <p><b>Data de Criação:</b> $data_criacao</p>
                        <p><b>Sala:</b> " . htmlspecialchars($sala_nome) . "</p>
                    </div>
                    
                    <div class='info-block'>
                        <h3>Informações do Utilizador</h3>
                        <p><b>Nome:</b> " . htmlspecialchars($solicitante['nome_completo']) . "</p>
                        <p><b>Tipo:</b> " . htmlspecialchars($tipo_solicitante) . "</p>
                        <p><b>Email:</b> " . htmlspecialchars($solicitante['email_institucional'] ?? '') . "</p>
                    </div>
                    
                    " . ($info_equipamento ? "<div class='info-block'>$info_equipamento</div>" : "") . "
                    
                    <div class='info-block'>
                        <h3>Descrição do Problema</h3>
                        <p>" . nl2br(htmlspecialchars($ticket['descricao'])) . "</p>
                    </div>
                    
                    " . (isset($ticket['mensagem_erro']) && !empty($ticket['mensagem_erro']) ? "<div class='info-block'><h3>Mensagem de Erro</h3><p>" . nl2br(htmlspecialchars($ticket['mensagem_erro'])) . "</p></div>" : "") . "
                    
                    " . (isset($ticket['observacoes_adicionais']) && !empty($ticket['observacoes_adicionais']) ? "<div class='info-block'><h3>Observações Adicionais</h3><p>" . nl2br(htmlspecialchars($ticket['observacoes_adicionais'])) . "</p></div>" : "") . "
                    
                    <p style='text-align: center; margin-top: 20px;'>Para atender a este ticket, acesse o sistema.</p>
                </div>
                
                <div class='footer'>
                    <p>Este é um email automático, por favor não responda.</p>
                    <p>&copy; " . date('Y') . " GM HelpDesk - Sistema de Tickets</p>
                </div>
            </div>
        </body>
        </html>";
        
        // Versão em texto simples
        $mail->AltBody = "Novo Ticket Criado\n\n" .
            "ID: #{$ticket['id']}\n" .
            "Assunto: {$ticket['assunto']}\n" .
            "Utilizador: {$solicitante['nome_completo']} ({$tipo_solicitante})\n" .
            "Criado em: $data_criacao\n\n" .
            "Para mais detalhes, acesse o sistema.";

        error_log("Corpo do email preparado, tentando enviar...");
        
        // Enviar o email
        $enviado = $mail->send();
        if ($enviado) {
            error_log("Email para o suporte técnico enviado com sucesso!");
            return true;
        } else {
            error_log("Falha ao enviar email para o suporte técnico");
            return false;
        }
    } catch (Exception $e) {
        error_log("Erro ao enviar email para suporte: " . $mail->ErrorInfo);
        error_log("Exceção: " . $e->getMessage());
        error_log("Trace: " . $e->getTraceAsString());
        return false;
    }
}

/**
 * Função auxiliar para buscar a referência correta do equipamento com base no tipo e ID
 * 
 * @param PDO $pdo Conexão com o banco de dados
 * @param string $tipo Tipo do equipamento (computador, rato, teclado, monitor, etc.)
 * @param string|int $id ID do equipamento
 * @return string|null A referência do equipamento ou null se não encontrada
 */
function buscarReferenciaEquipamento($pdo, $tipo, $id) {
    if (!$pdo || !$tipo || !$id) {
        return null;
    }
    
    try {
        $sql = '';
        $tipo = strtolower($tipo);
        
        // Define a SQL adequada para cada tipo de equipamento
        switch ($tipo) {
            case 'computador':
                $sql = "SELECT COMP_REF FROM computadores WHERE COMP_ID = ?";
                break;
            case 'rato':
                $sql = "SELECT RATOS_REF FROM ratos WHERE RATOS_ID = ?";
                break;
            case 'teclado':
                $sql = "SELECT TECLADO_REF FROM teclados WHERE TECLADO_ID = ?";
                break;
            case 'monitor':
                $sql = "SELECT MONITOR_REF FROM monitor WHERE MONITOR_ID = ?";
                break;
            case 'cooler':
                $sql = "SELECT COO_REF FROM cooler WHERE COO_ID = ?";
                break;
            case 'disco':
                $sql = "SELECT DISCO_REF FROM disco WHERE DISCO_ID = ?";
                break;
            case 'fonte':
                $sql = "SELECT FONTE_REF FROM fonte WHERE FONTE_ID = ?";
                break;
            case 'memoriaram':
                $sql = "SELECT MR_REF FROM memoriaram WHERE MR_ID = ?";
                break;
            case 'placarede':
                $sql = "SELECT PR_REF FROM placarede WHERE PR_ID = ?";
                break;
            case 'placagrafica':
                $sql = "SELECT PG_REF FROM placagrafica WHERE PG_ID = ?";
                break;
            case 'placamae':
                $sql = "SELECT PM_REF FROM placamae WHERE PM_ID = ?";
                break;
            case 'processador':
                $sql = "SELECT PROC_REF FROM processador WHERE PROC_ID = ?";
                break;
            case 'caixa':
                $sql = "SELECT CAIXA_REF FROM caixa WHERE CAIXA_ID = ?";
                break;
            case 'drivedvd':
                $sql = "SELECT DRVD_REF FROM drivedvd WHERE DRVD_ID = ?";
                break;
            case 'ventoinha':
                $sql = "SELECT VENT_REF FROM ventoinha WHERE VENT_ID = ?";
                break;
            default:
                error_log("Tipo de equipamento desconhecido: $tipo");
                return null;
        }
        
        if ($sql) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                // Extrair o campo de referência (primeiro campo do resultado)
                $ref_field = array_keys($result)[0];
                return $result[$ref_field];
            }
        }
    } catch (Exception $e) {
        error_log("Erro ao buscar referência do equipamento: " . $e->getMessage());
    }
    
    return null;
}

/**
 * Envia uma confirmação por email para o solicitante quando um ticket é criado
 * 
 * @param PDO $pdo Conexão com o banco de dados
 * @param array $ticket Dados do ticket criado
 * @param array $solicitante Dados do solicitante
 * @return bool True se o email foi enviado com sucesso, False caso contrário
 */
function enviarConfirmacaoTicket($pdo, $ticket, $solicitante)
{
    $mail = new PHPMailer(true);

    try {
        // Habilitar debug para diagnóstico
        $mail->SMTPDebug = 0; // 0 = sem saída, 1 = comandos, 2 = comandos e dados, 4 = baixo nível
        error_log("Iniciando configuração de PHPMailer para email de confirmação de ticket");
        
        // Configuração do servidor SMTP
        configurarSMTP($mail);

        // Configuração do remetente e destinatário
        $mail->setFrom(obterConfig('MAIL_FROM'), obterConfig('MAIL_FROM_NAME'));
        
        // Verificar se o email do solicitante está disponível
        if (empty($solicitante['email_institucional'])) {
            error_log("ALERTA: Email do utilizador não disponível: " . print_r($solicitante, true));
            return false;
        }
        
        $mail->addAddress($solicitante['email_institucional'], $solicitante['nome_completo']);
        error_log("Destinatário configurado: {$solicitante['email_institucional']} ({$solicitante['nome_completo']})");

        // Conteúdo do email
        error_log("Configurando conteúdo do email de confirmação");
        $mail->isHTML(true);
        $mail->Subject = 'Confirmação de Abertura de Ticket #' . $ticket['id'];
        error_log("Assunto do email: " . $mail->Subject);
        
        // Formatar a data de criação do ticket
        $data_criacao = isset($ticket['criado_em']) ? date('d/m/Y H:i', strtotime($ticket['criado_em'])) : date('d/m/Y H:i');
        
        // Formatar status e prioridade
        $status_formatado = formatarStatusParaExibicao($ticket['status']);
        
        // Buscar o nome da sala em vez do ID
        $sala_nome = $ticket['sala'];
        if (is_numeric($ticket['sala'])) {
            try {
                $stmt = $pdo->prepare("SELECT sala FROM salas WHERE id_sala = ?");
                $stmt->execute([$ticket['sala']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result) {
                    $sala_nome = $result['sala'];
                    error_log("Nome da sala encontrado: " . $sala_nome);
                } else {
                    error_log("Nome da sala não encontrado para ID: " . $ticket['sala']);
                }
            } catch (Exception $e) {
                // Em caso de erro, manter o valor original
                error_log("Erro ao buscar nome da sala: " . $e->getMessage());
            }
        }
        
        // Corpo do email com CSS
        $mail->Body = "
        <html>
        <head>
            <title>Confirmação de Abertura de Ticket</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
                .header {
                    background-color: #28a745;
                    color: white;
                    padding: 15px;
                    text-align: center;
                    border-radius: 5px 5px 0 0;
                    margin-bottom: 20px;
                }
                .info {
                    margin-bottom: 20px;
                }
                .info-block {
                    background-color: #f9f9f9;
                    padding: 15px;
                    border-radius: 5px;
                    margin-bottom: 15px;
                }
                .message {
                    padding: 15px;
                    margin-bottom: 15px;
                    border-left: 4px solid #28a745;
                }
                .footer {
                    margin-top: 30px;
                    font-size: 12px;
                    color: #777;
                    text-align: center;
                    border-top: 1px solid #ddd;
                    padding-top: 10px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Confirmação de Abertura de Ticket</h2>
                </div>
                
                <div class='info'>
                    <div class='message'>
                        <p>Olá <b>" . htmlspecialchars($solicitante['nome_completo']) . "</b>,</p>
                        <p>O seu ticket foi aberto com sucesso e será atendido em breve pela nossa equipa.</p>
                    </div>
                    
                    <div class='info-block'>
                        <h3>Detalhes do Ticket</h3>
                        <p><b>ID do Ticket:</b> #{$ticket['id']}</p>
                        <p><b>Assunto:</b> " . htmlspecialchars($ticket['assunto']) . "</p>
                        <p><b>Estado:</b> " . $status_formatado . "</p>
                        <p><b>Sala:</b> " . htmlspecialchars($sala_nome) . "</p>
                        <p><b>Data de Criação:</b> $data_criacao</p>
                    </div>
                    
                    <div class='message'>
                        <p>Irá receber atualizações sobre o andamento do ticket por email, mas pode consultar o sistema para mais informações.</p>
                        <p>Caso tenha mais alguma dúvida, entre em contacto com o suporte técnico.</p>
                    </div>
                    
                    <p style='margin-top: 20px;'>Atenciosamente,<br>
                    Suporte Técnico da Escola Demo</p>
                </div>
                
                <div class='footer'>
                    <p>Este é um email automático, por favor não responda.</p>
                    <p>&copy; " . date('Y') . " GM HelpDesk - Sistema de Tickets</p>
                </div>
            </div>
        </body>
        </html>";
        
        // Versão em texto simples
        $mail->AltBody = "Confirmação de Abertura de Ticket\n\n" .
            "Olá {$solicitante['nome_completo']},\n\n" .
            "O seu ticket foi aberto com sucesso e será atendido em breve pela nossa equipa.\n\n" .
            "ID: #{$ticket['id']}\n" .
            "Assunto: {$ticket['assunto']}\n" .
            "Criado em: $data_criacao\n\n" .
            "Receberá atualizações sobre o andamento do ticket por email.";

        error_log("Corpo do email de confirmação preparado, tentando enviar...");
        
        // Enviar o email
        $enviado = $mail->send();
        if ($enviado) {
            error_log("Email de confirmação para o utilizador enviado com sucesso!");
            return true;
        } else {
            error_log("Falha ao enviar email de confirmação para o utilizador");
            return false;
        }
    } catch (Exception $e) {
        error_log("Erro ao enviar email de confirmação: " . $mail->ErrorInfo);
        error_log("Exceção: " . $e->getMessage());
        error_log("Trace: " . $e->getTraceAsString());
        return false;
    }
}

/**
 * Envia uma notificação por email para o solicitante quando o status do ticket é atualizado
 * 
 * @param PDO $pdo Conexão com o banco de dados
 * @param array $ticket Dados do ticket atualizado
 * @param array $solicitante Dados do solicitante
 * @param string $status_anterior Status anterior do ticket
 * @param string $status_novo Novo status do ticket
 * @param string $comentario Comentário opcional sobre a atualização
 * @return bool True se o email foi enviado com sucesso, False caso contrário
 */
function enviarNotificacaoAtualizacaoTicket($pdo, $ticket, $solicitante, $status_anterior, $status_novo, $comentario = '')
{
    $mail = new PHPMailer(true);

    try {
        // Configuração do servidor SMTP
        configurarSMTP($mail);

        // Configuração do remetente e destinatário
        $mail->setFrom(obterConfig('MAIL_FROM'), obterConfig('MAIL_FROM_NAME'));
        $mail->addAddress($solicitante['email_institucional'], $solicitante['nome_completo']);

        // Conteúdo do email
        $mail->isHTML(true);
        
        // Definir assunto com base no novo status
        $assunto = '';
        switch ($status_novo) {
            case 'em_andamento':
                $assunto = 'Ticket #' . $ticket['id'] . ' em Atendimento';
                break;
            case 'resolvido':
                $assunto = 'Ticket #' . $ticket['id'] . ' Resolvido';
                break;
            case 'fechado':
                $assunto = 'Ticket #' . $ticket['id'] . ' Fechado';
                break;
            default:
                $assunto = 'Ticket #' . $ticket['id'] . ' Atualizado';
                break;
        }
        $mail->Subject = $assunto;
        
        // Formatar a data de atualização
        $data_atualizacao = date('d/m/Y H:i');
        
        // Preparar mensagem com base no novo status
        $mensagem_status = '';
        $cor_status = '#0d6efd'; // Azul padrão
        
        switch ($status_novo) {
            case 'em_andamento':
                $mensagem_status = 'O seu ticket está a ser atendido pela nossa equipa de suporte.';
                $cor_status = '#fd7e14'; // Laranja
                break;
            case 'resolvido':
                $mensagem_status = 'O seu ticket foi resolvido pela nossa equipa de suporte.';
                $cor_status = '#28a745'; // Verde
                break;
            case 'fechado':
                $mensagem_status = 'O seu ticket foi fechado.';
                $cor_status = '#6c757d'; // Cinza
                break;
            default:
                $mensagem_status = 'O seu ticket foi atualizado de "' . formatarStatusParaExibicao($status_anterior) . '" para "' . formatarStatusParaExibicao($status_novo) . '".';
                break;
        }
        
        // Buscar o nome da sala em vez do ID
        $sala_nome = $ticket['sala'];
        if (is_numeric($ticket['sala'])) {
            try {
                $stmt = $pdo->prepare("SELECT sala FROM salas WHERE id_sala = ?");
                $stmt->execute([$ticket['sala']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result) {
                    $sala_nome = $result['sala'];
                }
            } catch (Exception $e) {
                // Em caso de erro, manter o valor original
                error_log("Erro ao buscar nome da sala: " . $e->getMessage());
            }
        }
        
        // Formatação do comentário
        $comentario_html = '';
        if (!empty($comentario)) {
            $comentario_html = "
                <div class='info-block'>
                    <h3>Comentário do Suporte</h3>
                    <p>" . nl2br(htmlspecialchars($comentario)) . "</p>
                </div>
            ";
        }
        
        // Corpo do email com CSS
        $mail->Body = "
        <html>
        <head>
            <title>Atualização de Ticket</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
                .header {
                    background-color: " . $cor_status . ";
                    color: white;
                    padding: 15px;
                    text-align: center;
                    border-radius: 5px 5px 0 0;
                    margin-bottom: 20px;
                }
                .info {
                    margin-bottom: 20px;
                }
                .info-block {
                    background-color: #f9f9f9;
                    padding: 15px;
                    border-radius: 5px;
                    margin-bottom: 15px;
                }
                .status-update {
                    padding: 15px;
                    margin-bottom: 15px;
                    border-left: 4px solid " . $cor_status . ";
                    background-color: #f8f9fa;
                }
                .status-badge {
                    display: inline-block;
                    padding: 5px 10px;
                    background-color: " . $cor_status . ";
                    color: white;
                    border-radius: 3px;
                    font-size: 14px;
                    margin-right: 5px;
                }
                .footer {
                    margin-top: 30px;
                    font-size: 12px;
                    color: #777;
                    text-align: center;
                    border-top: 1px solid #ddd;
                    padding-top: 10px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Atualização de Ticket</h2>
                </div>
                
                <div class='info'>
                    <div class='status-update'>
                        <p>Olá <b>" . htmlspecialchars($solicitante['nome_completo']) . "</b>,</p>
                        <p>$mensagem_status</p>
                    </div>
                    
                    <div class='info-block'>
                        <h3>Detalhes do Ticket</h3>
                        <p><b>ID do Ticket:</b> #{$ticket['id']}</p>
                        <p><b>Assunto:</b> " . htmlspecialchars($ticket['assunto']) . "</p>
                        <p><b>Estado Anterior:</b> <span class='status-badge' style='background-color: #6c757d;'>" . formatarStatusParaExibicao($status_anterior) . "</span></p>
                        <p><b>Estado Atual:</b> <span class='status-badge'>" . formatarStatusParaExibicao($status_novo) . "</span></p>
                        <p><b>Data da Atualização:</b> $data_atualizacao</p>
                    </div>
                    
                    $comentario_html
                    
                    " . ($status_novo === 'resolvido' ? "<div class='status-update'><p>Se considerar que o problema foi resolvido satisfatoriamente, nenhuma ação adicional é necessária.</p></div>" : "") . "
                    
                    <p style='margin-top: 20px;'>Caso tenha alguma dúvida, entre em contato com o suporte técnico.</p>
                    
                    <p>Atenciosamente,<br>
                    Suporte Técnico da Escola Demo</p>
                </div>
                
                <div class='footer'>
                    <p>Este é um email automático, por favor não responda.</p>
                    <p>&copy; " . date('Y') . " GM HelpDesk - Sistema de Tickets</p>
                </div>
            </div>
        </body>
        </html>";
        
        // Versão em texto simples
        $mail->AltBody = "Atualização de Ticket\n\n" .
            "Olá {$solicitante['nome_completo']},\n\n" .
            "$mensagem_status\n\n" .
            "ID: #{$ticket['id']}\n" .
            "Assunto: {$ticket['assunto']}\n" .
            "Estado Anterior: " . formatarStatusParaExibicao($status_anterior) . "\n" .
            "Estado Atual: " . formatarStatusParaExibicao($status_novo) . "\n" .
            "Data da Atualização: $data_atualizacao\n\n" .
            (!empty($comentario) ? "Comentário do Suporte:\n$comentario\n\n" : "") .
            ($status_novo === 'resolvido' ? "Se você considerar que o problema foi resolvido satisfatoriamente, nenhuma ação adicional é necessária.\n\n" : "");

        // Enviar o email
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar notificação de atualização: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Formata o status do ticket para exibição em emails
 * 
 * @param string $status Status do ticket (aberto, em_andamento, resolvido, fechado)
 * @return string Status formatado para exibição
 */
function formatarStatusParaExibicao($status)
{
    switch ($status) {
        case 'aberto':
            return 'Aberto';
        case 'em_andamento':
            return 'Em Atendimento';
        case 'resolvido':
            return 'Resolvido';
        case 'fechado':
            return 'Fechado';
        default:
            return ucfirst($status);
    }
}

/**
 * Formata a prioridade do ticket para exibição em emails
 * 
 * @param string|int $prioridade Prioridade do ticket (1, 2, 3 ou alta, média, baixa)
 * @return string Prioridade formatada para exibição
 */
function formatarPrioridadeParaExibicao($prioridade)
{
    // Se for numérica, converter para texto
    if (is_numeric($prioridade)) {
        switch ($prioridade) {
            case 1:
                return 'Alta';
            case 2:
                return 'Média';
            case 3:
                return 'Baixa';
            default:
                return ucfirst($prioridade);
        }
    }
    
    // Se já for texto
    switch (strtolower($prioridade)) {
        case 'baixa':
            return 'Baixa';
        case 'média':
            return 'Média';
        case 'alta':
            return 'Alta';
        default:
            return ucfirst($prioridade);
    }
}

/**
 * Envia email de boas-vindas para uma nova conta de suporte técnico
 * 
 * @param string $email Email do novo técnico
 * @param string $nome Nome completo do técnico
 * @param string $senha Senha gerada para o novo usuário
 * @return bool True se o email foi enviado com sucesso, False caso contrário
 */
function enviarEmailBoasVindasTecnico($email, $nome, $senha)
{
    $mail = new PHPMailer(true);

    try {
        // Configuração do servidor SMTP
        configurarSMTP($mail);

        // Configuração do remetente e destinatário
        $mail->setFrom(obterConfig('MAIL_FROM'), obterConfig('MAIL_FROM_NAME'));
        $mail->addAddress($email, $nome);

        // Conteúdo do email
        $mail->isHTML(true);
        $mail->Subject = 'Bem-vindo(a) à Equipa de Suporte Técnico';
        
        // Corpo do email com CSS
        $mail->Body = "
        <html>
        <head>
            <title>Bem-vindo(a) à Equipa de Suporte Técnico</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
                .header {
                    background-color: #17a2b8;
                    color: white;
                    padding: 15px;
                    text-align: center;
                    border-radius: 5px 5px 0 0;
                    margin-bottom: 20px;
                }
                .info {
                    margin-bottom: 20px;
                }
                .welcome-message {
                    padding: 15px;
                    margin-bottom: 15px;
                    border-left: 4px solid #17a2b8;
                    background-color: #f8f9fa;
                }
                .credentials-box {
                    background-color: #f9f9f9;
                    padding: 15px;
                    border-radius: 5px;
                    margin-bottom: 15px;
                    border: 1px dashed #ccc;
                }
                .info-section {
                    background-color: #f0f7fa;
                    padding: 15px;
                    border-radius: 5px;
                    margin-bottom: 15px;
                }
                .footer {
                    margin-top: 30px;
                    font-size: 12px;
                    color: #777;
                    text-align: center;
                    border-top: 1px solid #ddd;
                    padding-top: 10px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Bem-vindo(a) à Equipa de Suporte Técnico</h2>
                </div>
                
                <div class='info'>
                    <div class='welcome-message'>
                        <p>Olá <b>" . htmlspecialchars($nome) . "</b>,</p>
                        <p>A sua conta na plataforma de Suporte Técnico foi criada com sucesso!</p>
                    </div>
                    
                    <div class='credentials-box'>
                        <h3>Credenciais de Acesso</h3>
                        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                        <p><strong>Senha:</strong> " . htmlspecialchars($senha) . "</p>
                    </div>
                    
                    <div class='info-section'>
                        <h3>Informações Importantes</h3>
                        <p>Como membro da equipa de suporte técnico, irá receber notificações por email sempre que um novo ticket for aberto. Essas notificações contêm todas as informações necessárias para atender às solicitações da comunidade escolar.</p>
                        
                        <p>Para acessar o sistema de tickets, utilize o seu email institucional e a senha fornecida. Irá ter que alterar a sua senha no primeiro acesso.</p>
                        
                        <p>Se tiver dúvidas sobre o uso do sistema, entre em contato com o administrador.</p>
                    </div>
                    
                    <p style='margin-top: 20px;'>Atenciosamente,<br>
                    Administração do Sistema<br>
                    Escola Demo</p>
                </div>
                
                <div class='footer'>
                    <p>Este é um email automático, por favor não responda.</p>
                    <p>&copy; " . date('Y') . " GM HelpDesk - Sistema de Tickets</p>
                </div>
            </div>
        </body>
        </html>";
        
        // Versão em texto simples
        $mail->AltBody = "Bem-vindo(a) à Equipa de Suporte Técnico\n\n" .
            "Olá {$nome},\n\n" .
            "A sua conta na plataforma de Suporte Técnico foi criada com sucesso!\n\n" .
            "Credenciais de Acesso:\n" .
            "Email: {$email}\n" .
            "Senha: {$senha}\n\n" .
            "Como membro da equipa de suporte técnico, irá receber notificações por email sempre que um novo ticket for aberto.\n\n" .
            "Para acessar o sistema de tickets, utilize o seu email institucional e a senha fornecida. Irá ter que alterar a sua senha no primeiro acesso.\n\n" .
            "Atenciosamente,\nAdministração do Sistema";

        // Enviar o email
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar email de boas-vindas para técnico: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Envia email de boas-vindas para uma nova conta da comunidade escolar
 * 
 * @param string $email Email do novo membro da comunidade escolar
 * @param string $nome Nome completo do membro
 * @param string $cargo Cargo/função do membro (aluno, professor, funcionário)
 * @param string $senha Senha gerada para o novo usuário
 * @return bool True se o email foi enviado com sucesso, False caso contrário
 */
function enviarEmailBoasVindasComunidade($email, $nome, $cargo, $senha)
{
    $mail = new PHPMailer(true);

    try {
        // Configuração do servidor SMTP
        configurarSMTP($mail);

        // Configuração do remetente e destinatário
        $mail->setFrom(obterConfig('MAIL_FROM'), obterConfig('MAIL_FROM_NAME'));
        $mail->addAddress($email, $nome);

        // Conteúdo do email
        $mail->isHTML(true);
        $mail->Subject = 'Bem-vindo(a) ao Sistema GM HelpDesk';
        
        // Formatação do cargo com primeira letra maiúscula
        $cargoFormatado = ucfirst($cargo);
        
        // Corpo do email com CSS
        $mail->Body = "
        <html>
        <head>
            <title>Bem-vindo(a) ao Sistema GM HelpDesk</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
                .header {
                    background-color: #6610f2;
                    color: white;
                    padding: 15px;
                    text-align: center;
                    border-radius: 5px 5px 0 0;
                    margin-bottom: 20px;
                }
                .info {
                    margin-bottom: 20px;
                }
                .welcome-message {
                    padding: 15px;
                    margin-bottom: 15px;
                    border-left: 4px solid #6610f2;
                    background-color: #f8f9fa;
                }
                .credentials-box {
                    background-color: #f9f9f9;
                    padding: 15px;
                    border-radius: 5px;
                    margin-bottom: 15px;
                    border: 1px dashed #ccc;
                }
                .info-section {
                    background-color: #f5f0fa;
                    padding: 15px;
                    border-radius: 5px;
                    margin-bottom: 15px;
                }
                ul.features {
                    margin-left: 20px;
                    padding-left: 15px;
                }
                ul.features li {
                    margin-bottom: 8px;
                }
                .footer {
                    margin-top: 30px;
                    font-size: 12px;
                    color: #777;
                    text-align: center;
                    border-top: 1px solid #ddd;
                    padding-top: 10px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Bem-vindo(a) ao Sistema GM HelpDesk</h2>
                </div>
                
                <div class='info'>
                    <div class='welcome-message'>
                        <p>Olá <b>" . htmlspecialchars($nome) . "</b>,</p>
                        <p>A sua conta na plataforma GM HelpDesk foi criada com sucesso!</p>
                    </div>
                    
                    <div class='credentials-box'>
                        <h3>Credenciais de Acesso</h3>
                        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                        <p><strong>Senha:</strong> " . htmlspecialchars($senha) . "</p>
                    </div>
                    
                    <div class='info-section'>
                        <h3>Informações Importantes</h3>
                        <p>Como " . htmlspecialchars($cargoFormatado) . " da nossa comunidade escolar, agora pode abrir tickets para solicitar suporte técnico sempre que necessário.</p>
                        
                        <p>Através do sistema, poderá:</p>
                        <ul class='features'>
                            <li>Abrir novos tickets de suporte</li>
                            <li>Acompanhar o estado dos seus tickets</li>
                            <li>Receber atualizações por email sobre o andamento das suas solicitações</li>
                        </ul>
                        
                        <p>Para acessar o sistema de tickets, utilize o seu email institucional e a senha fornecida. Irá ter que alterar a sua senha no primeiro acesso.</p>
                    </div>
                    
                    <p style='margin-top: 20px;'>Atenciosamente,<br>
                    Equipa de Suporte Técnico<br>
                    Escola Demo</p>
                </div>
                
                <div class='footer'>
                    <p>Este é um email automático, por favor não responda.</p>
                    <p>&copy; " . date('Y') . " GM HelpDesk - Sistema de Tickets</p>
                </div>
            </div>
        </body>
        </html>";
        
        // Versão em texto simples
        $mail->AltBody = "Bem-vindo(a) ao Sistema GM HelpDesk\n\n" .
            "Olá {$nome},\n\n" .
            "A sua conta na plataforma GM HelpDesk foi criada com sucesso!\n\n" .
            "Credenciais de Acesso:\n" .
            "Email: {$email}\n" .
            "Senha: {$senha}\n\n" .
            "Como {$cargoFormatado} da nossa comunidade escolar, agora pode abrir tickets para solicitar suporte técnico sempre que necessário.\n\n" .
            "Para acessar o sistema de tickets, utilize o seu email institucional e a senha fornecida. Irá ter que alterar a sua senha no primeiro acesso.\n\n" .
            "Atenciosamente,\nEquipa de Suporte Técnico";

        // Enviar o email
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erro ao enviar email de boas-vindas para comunidade: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Envia um email de recuperação de senha
 * 
 * @param string $email Email do destinatário
 * @param string $nome Nome completo do usuário
 * @param string $tipo Tipo/cargo do usuário
 * @param string $link Link para recuperação de senha
 * @return bool True se o email foi enviado com sucesso, False caso contrário
 */
function enviarEmailRecuperacao($email, $nome, $tipo, $link) {
    $mail = new PHPMailer(true);

    try {
        // Configuração do servidor SMTP
        configurarSMTP($mail);

        // Configuração do remetente e destinatário
        $mail->setFrom(obterConfig('MAIL_FROM'), obterConfig('MAIL_FROM_NAME'));
        $mail->addAddress($email, $nome);

        // Conteúdo do email
        $mail->isHTML(true);
        $mail->Subject = "Recuperação de Password - GM HelpDesk";
        
        // Corpo do email
        $mail->Body = "
        <html>
        <head>
            <title>Recuperação de Password</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
                .header {
                    background-color: #0d6efd;
                    color: white;
                    padding: 15px;
                    text-align: center;
                    border-radius: 5px 5px 0 0;
                    margin-bottom: 20px;
                }
                .info {
                    margin-bottom: 20px;
                }
                .button {
                    display: inline-block;
                    background-color: #0d6efd;
                    color: white;
                    padding: 12px 20px;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                    margin: 20px 0;
                }
                .footer {
                    margin-top: 30px;
                    font-size: 12px;
                    color: #777;
                    text-align: center;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Recuperação de Password</h2>
                </div>
                
                <div class='info'>
                    <p>Olá, recebemos uma solicitação para recuperar a senha da sua conta no sistema GM HelpDesk.</p>
                    
                    <p><strong>Nome Completo:</strong> $nome</p>
                    <p><strong>Tipo:</strong> $tipo</p>
                    
                    <p>Para criar uma nova senha, clique no botão abaixo:</p>
                    
                    <div style='text-align: center;'>
                        <a href='$link' class='button'>Recuperar Password</a>
                    </div>
                    
                    <p>Se não solicitou esta recuperação, por favor ignore este email.</p>
                    <p>Este link expirará em 24 horas por motivos de segurança.</p>
                </div>
                
                <div class='footer'>
                    <p>Este é um email automático, por favor não responda.</p>
                    <p>&copy; " . date('Y') . " GM HelpDesk - Sistema de Tickets</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Versão em texto simples
        $mail->AltBody = "
        Recuperação de Password - GM HelpDesk
        
        Olá, recebemos uma solicitação para recuperar a senha da sua conta no sistema GM HelpDesk.
        
        Nome Completo: $nome
        Tipo: $tipo
        
        Para criar uma nova senha, acesse o link abaixo:
        http://$link
        
        Se não solicitou esta recuperação, por favor ignore este email.
        Este link expirará em 24 horas por motivos de segurança.
        
        Este é um email automático, por favor não responda.
        ";
        
        // Registrar tentativa de envio
        $enviado = $mail->send();
        if ($enviado) {
            error_log("Email de recuperação enviado com sucesso para: $email");
        } else {
            error_log("Falha ao enviar email de recuperação para: $email");
        }
        
        return $enviado;
    } catch (Exception $e) {
        error_log("Erro ao enviar email de recuperação: " . $mail->ErrorInfo);
        error_log("Exceção: " . $e->getMessage());
        return false;
    }
} 