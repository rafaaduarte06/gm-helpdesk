<?php
/**
 * Funções para gerenciamento de tickets
 */

/**
 * Obtém todas as salas da base de dados
 * @param PDO $pdo Conexão com a base de dados
 * @return array Lista de salas
 */
function obterSalas($pdo) {
    try {
        $stmt = $pdo->query("SELECT id_sala as id, sala as nome FROM salas ORDER BY sala");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Erro ao obter salas: ' . $e->getMessage());
        return [];
    }
}

/**
 * Obtém todos os tipos de componentes
 * @return array Lista de tipos de componentes
 */
function obterTiposComponentes() {
    return [
        'rato' => 'Rato',
        'teclado' => 'Teclado',
        'monitor' => 'Monitor',
        'placamae' => 'Placa Mãe',
        'processador' => 'Processador',
        'cooler' => 'Cooler',
        'placagrafica' => 'Placa Gráfica',
        'disco' => 'Disco',
        'fonte' => 'Fonte',
        'memoriaram' => 'Memória RAM',
        'drivedvd' => 'Drive DVD',
        'placarede' => 'Placa de Rede',
        'ventoinha' => 'Ventoinha',
        'caixa' => 'Caixa'
    ];
}

/**
 * Obtém componentes disponíveis por tipo (em uso ou disponível)
 * @param PDO $pdo Conexão com a base de dados
 * @param string $tipo Tipo de componente
 * @param int|null $sala_id ID da sala (opcional)
 * @return array Lista de componentes
 */
function obterComponentesPorTipo($pdo, $tipo, $sala_id = null) {
    try {
        // Obter mapeamento de tabelas e colunas para este tipo de componente
        $mapeamento = obterMapeamentoTabelaComponente($tipo);
        
        if (!$mapeamento) {
            error_log("Tipo de componente não mapeado: $tipo");
            return [];
        }
        
        $tabela = $mapeamento['tabela'];
        $id_coluna = $mapeamento['campo_id'];
        $ref_coluna = $mapeamento['campo_ref'];
        
        // Determinar a tabela de estado
        $tabela_estado = $tipo . "_estado";
        
        // Verificar se as tabelas existem
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tabela]);
        if ($stmt->rowCount() == 0) {
            error_log("Tabela $tabela não existe");
            return [];
        }
        
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tabela_estado]);
        if ($stmt->rowCount() == 0) {
            error_log("Tabela $tabela_estado não existe");
            return [];
        }
        
        // Construir a consulta - IMPORTANTE: Apenas 'em uso' e 'disponivel'
        $query = "SELECT c.*, e.estado 
                  FROM $tabela c 
                  JOIN $tabela_estado e ON c.$id_coluna = e.id_$tipo 
                  WHERE e.estado IN ('em uso', 'disponivel') ";
        
        $params = [];
        
        // Adicionar filtro por sala se fornecido e se a tabela tiver coluna de sala
        if ($sala_id !== null) {
            $sala_coluna = $tipo . "_SALA"; // Assumindo que a coluna de sala segue este padrão
            $query .= "AND c.$sala_coluna = ? ";
            $params[] = $sala_id;
        }
        
        $query .= "ORDER BY c.$ref_coluna";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $componentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Registrar para debug
        error_log("Obtidos " . count($componentes) . " componentes do tipo $tipo");
        
        // Adicionar uma descrição completa para cada componente
        foreach ($componentes as &$componente) {
            $descricao = isset($componente[$ref_coluna]) ? $componente[$ref_coluna] : 'N/D';
            
            // Se tiver marca e modelo, incluir na descrição
            if (isset($componente[$tipo . '_MARCA'])) {
                $descricao = $componente[$tipo . '_MARCA'] . ' ' . $descricao;
            }
            
            if (isset($componente[$tipo . '_MODELO'])) {
                $descricao .= ' ' . $componente[$tipo . '_MODELO'];
            }
            
            $componente['descricao_completa'] = $descricao . 
                                               ' (ID: ' . $componente[$id_coluna] . ', Estado: ' . $componente['estado'] . ')';
            
            // Registrar para debug
            error_log("Componente encontrado: Tipo=$tipo, ID=" . $componente[$id_coluna] . ", Ref=" . $descricao);
        }
        
        return $componentes;
    } catch (PDOException $e) {
        error_log("Erro ao obter componentes do tipo $tipo: " . $e->getMessage());
        return [];
    }
}

/**
 * Atualiza o estado do componente para manutenção
 * @param PDO $pdo Conexão com a base de dados
 * @param string $tipo Tipo de componente
 * @param int $id ID do componente
 * @return bool Sucesso ou falha
 */
function marcarComponenteParaManutencao($pdo, $tipo, $id) {
    try {
        $tabela_estado = $tipo . "s_estado";
        
        // Ajustes específicos para tabelas que não seguem o padrão
        if ($tipo == 'memoriaram') {
            $tabela_estado = 'memoriaram_estado';
        } elseif ($tipo == 'caixa') {
            $tabela_estado = 'caixas_estado';
        } elseif ($tipo == 'placagrafica') {
            $tabela_estado = 'placagrafica_estado';
        } elseif ($tipo == 'placamae') {
            $tabela_estado = 'placamae_estado';
        } elseif ($tipo == 'placarede') {
            $tabela_estado = 'placarede_estado';
        } elseif ($tipo == 'rato') {
            $tabela_estado = 'rato_estado';
        }
        
        // Verificar se a tabela existe
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tabela_estado]);
        if ($stmt->rowCount() == 0) {
            error_log("Tabela $tabela_estado não existe");
            return false;
        }
        
        // Atualizar o estado para 'manutenção'
        $stmt = $pdo->prepare("UPDATE $tabela_estado SET estado = 'manutenção' WHERE id_$tipo = ?");
        $resultado = $stmt->execute([$id]);
        
        if ($resultado) {
            error_log("Componente $tipo com ID $id marcado para manutenção com sucesso");
        } else {
            error_log("Falha ao marcar componente $tipo com ID $id para manutenção");
        }
        
        return $resultado;
    } catch (PDOException $e) {
        error_log("Erro ao marcar componente para manutenção: " . $e->getMessage());
        return false;
    }
}

/**
 * Cria um novo ticket no sistema
 * @param PDO $pdo Conexão com a base de dados
 * @param array $dados Dados do ticket a ser criado
 * @return int|bool ID do ticket criado ou false em caso de erro
 */
function criarTicket($pdo, $dados)
{
    try {
        error_log("Iniciando criação de ticket com dados: " . print_r($dados, true));
        
        // Verificar se o PDO é válido
        if (!$pdo) {
            error_log("ERRO CRÍTICO: Objeto PDO não é válido");
            return false;
        }
        
        // Extrair dados
        $usuario_id = $dados['usuario_id'];
        $sala_id = $dados['sala'];
        $assunto = $dados['assunto'];
        $descricao = $dados['descricao'];
        $mensagem_erro = isset($dados['mensagem_erro']) ? $dados['mensagem_erro'] : '';
        $observacoes = isset($dados['observacoes']) ? $dados['observacoes'] : '';
        $prioridade = $dados['prioridade'];
        $tipo_componente = isset($dados['tipo_componente']) ? $dados['tipo_componente'] : null;
        $componente_id = isset($dados['componente_id']) ? $dados['componente_id'] : null;
        
        // Validar dados obrigatórios
        if (!$usuario_id || !$sala_id || !$assunto || !$descricao || !$prioridade) {
            error_log("Erro: Dados obrigatórios não fornecidos para criar o ticket");
            error_log("usuario_id: " . ($usuario_id ? "OK" : "FALTANDO"));
            error_log("sala_id: " . ($sala_id ? "OK" : "FALTANDO"));
            error_log("assunto: " . ($assunto ? "OK" : "FALTANDO"));
            error_log("descricao: " . ($descricao ? "OK" : "FALTANDO"));
            error_log("prioridade: " . ($prioridade ? "OK" : "FALTANDO"));
            throw new Exception("Dados obrigatórios não fornecidos para criar o ticket");
        }
        
        // Iniciar transação
        try {
            error_log("Iniciando transação no banco de dados");
        $pdo->beginTransaction();
        } catch (PDOException $e) {
            error_log("Erro ao iniciar transação: " . $e->getMessage());
            return false;
        }
        
        // Obter nome do solicitante com base no tipo de usuário
        $solicitante = '';
        $tipo_usuario = isset($_SESSION['usuario_tipo']) ? $_SESSION['usuario_tipo'] : '';
        $dados_solicitante = null;
        
        try {
            error_log("Verificando tipo de usuário: $tipo_usuario");
        
        if ($tipo_usuario === 'comunidade_escolar') {
                error_log("Buscando dados do usuário comunidade escolar ID: $usuario_id");
            $stmt = $pdo->prepare("SELECT * FROM comunidade_escolar WHERE id = ?");
                if (!$stmt) {
                    error_log("Erro na preparação da query para buscar comunidade_escolar: " . print_r($pdo->errorInfo(), true));
                    throw new Exception("Erro na preparação da consulta");
                }
                
                $result = $stmt->execute([$usuario_id]);
                if (!$result) {
                    error_log("Erro na execução da query para buscar comunidade_escolar: " . print_r($stmt->errorInfo(), true));
                    throw new Exception("Erro ao executar a consulta");
                }
                
            $dados_solicitante = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("Dados do solicitante: " . ($dados_solicitante ? print_r($dados_solicitante, true) : "Não encontrado"));
            $solicitante = $dados_solicitante ? $dados_solicitante['nome_completo'] : 'Usuário Comunidade #' . $usuario_id;
        } else {
                error_log("Buscando dados do suporte técnico ID: $usuario_id");
            $stmt = $pdo->prepare("SELECT * FROM suporte_tecnico WHERE id = ?");
                if (!$stmt) {
                    error_log("Erro na preparação da query para buscar suporte_tecnico: " . print_r($pdo->errorInfo(), true));
                    throw new Exception("Erro na preparação da consulta");
                }
                
                $result = $stmt->execute([$usuario_id]);
                if (!$result) {
                    error_log("Erro na execução da query para buscar suporte_tecnico: " . print_r($stmt->errorInfo(), true));
                    throw new Exception("Erro ao executar a consulta");
                }
                
            $dados_solicitante = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("Dados do solicitante: " . ($dados_solicitante ? print_r($dados_solicitante, true) : "Não encontrado"));
            $solicitante = $dados_solicitante ? $dados_solicitante['nome_completo'] : 'Suporte Técnico #' . $usuario_id;
            }
            
            error_log("Nome do solicitante definido: $solicitante");
        } catch (PDOException $e) {
            error_log("Erro ao buscar dados do solicitante: " . $e->getMessage());
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
        
        // Inserir o ticket
        try {
        $query = "INSERT INTO tickets (assunto, sala, descricao, mensagem_erro, observacoes_adicionais, 
                                         prioridade, id_usuario, solicitante, status, criado_em, entrada) 
                  VALUES (:assunto, :sala, :descricao, :mensagem_erro, :observacoes, 
                            :prioridade, :usuario_id, :solicitante, 'aberto', NOW(), :entrada)";
            
            error_log("Preparando query para inserir ticket: $query");
        
        $stmt = $pdo->prepare($query);
            if (!$stmt) {
                error_log("Erro na preparação da query para inserir ticket: " . print_r($pdo->errorInfo(), true));
                throw new Exception("Erro na preparação da consulta de inserção do ticket");
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
        
            error_log("Parâmetros para inserção do ticket: " . print_r($params, true));
            
            // Executar a query
            $result = $stmt->execute($params);
            if (!$result) {
                error_log("Erro na execução da query para inserir ticket: " . print_r($stmt->errorInfo(), true));
                throw new Exception("Erro ao executar a consulta de inserção do ticket");
            }
            
        $ticket_id = $pdo->lastInsertId();
            error_log("Ticket inserido com sucesso. ID: $ticket_id");
            
            if (!$ticket_id) {
                error_log("Erro: lastInsertId() retornou valor vazio após inserção do ticket");
                throw new Exception("Erro ao obter o ID do ticket inserido");
            }
        } catch (PDOException $e) {
            error_log("Erro PDO ao inserir ticket: " . $e->getMessage());
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
        
        // Variáveis para armazenar informações do equipamento para o email
        $equipamento_ref = null;
        $equipamento_tipo = null;
        
        // Se tiver informações de componente, registrar no ticket_componentes
        if ($tipo_componente && $componente_id) {
            try {
            // Mapear o tipo de componente para o nome correto da coluna na base de dados
            $equipamento_tipo_mapeado = mapearTipoComponenteParaTabela($tipo_componente);
            
            // ID e referência para armazenar no ticket_componentes
            $equipamento_ref = $componente_id;
            
            // Para computadores, o ID já está correto (COMP_ID)
            if ($tipo_componente === 'computador') {
                $equipamento_tipo = 'computador';
            } else {
                // Para outros componentes, usar o tipo_componente fornecido
                $equipamento_tipo = $tipo_componente;
            }
            
            // Logs detalhados para depuração
            error_log("INSERINDO NO TICKET_COMPONENTES - Ticket: $ticket_id");
            error_log("Tipo de equipamento: $equipamento_tipo");
            error_log("Referência do equipamento: $equipamento_ref");
            
            // Inserir na tabela ticket_componentes
            $stmt = $pdo->prepare("INSERT INTO ticket_componentes (ticket_id, equipamento, ref_equipamento) 
                                VALUES (:ticket_id, :equipamento, :ref_equipamento)");
                
                if (!$stmt) {
                    error_log("Erro na preparação da query para inserir componente: " . print_r($pdo->errorInfo(), true));
                    throw new Exception("Erro na preparação da consulta de inserção do componente");
                }
                
            $params = [
                ':ticket_id' => $ticket_id,
                ':equipamento' => $equipamento_tipo,
                ':ref_equipamento' => $equipamento_ref
            ];
            error_log("Parâmetros da query: " . print_r($params, true));
            
            $result = $stmt->execute($params);
            
                if (!$result) {
                error_log("ERRO ao inserir componente: " . print_r($stmt->errorInfo(), true));
                    throw new Exception("Erro ao inserir o componente no ticket");
                } else {
                    error_log("Componente inserido com sucesso na tabela ticket_componentes");
            }
            
            // Armazenar informações do equipamento para o email
            $equipamento_tipo_para_email = $equipamento_tipo;
            $equipamento_ref_para_email = $equipamento_ref;
            
            // Registrar em log para debug
            error_log("Componente registrado para ticket #$ticket_id: Tipo=$equipamento_tipo, ID=$equipamento_ref");
            
            // Tentar obter a referência real do equipamento para o email
            try {
                if ($tipo_componente === 'computador') {
                    // Para computadores, buscar a referência na tabela computadores
                    $stmt_ref = $pdo->prepare("SELECT COMP_REF FROM computadores WHERE COMP_ID = ?");
                        if (!$stmt_ref) {
                            error_log("Erro na preparação da query para buscar referência do computador: " . print_r($pdo->errorInfo(), true));
                        } else {
                            $result_ref = $stmt_ref->execute([$componente_id]);
                            if (!$result_ref) {
                                error_log("Erro na execução da query para buscar referência do computador: " . print_r($stmt_ref->errorInfo(), true));
                            } else {
                    $result_ref = $stmt_ref->fetch(PDO::FETCH_ASSOC);
                    
                    if ($result_ref) {
                        $equipamento_ref_para_email = $result_ref['COMP_REF'];
                                }
                            }
                    }
                } else {
                    // Para outros componentes, tentar mapear e buscar a referência
                    $tabela_info = obterMapeamentoTabelaComponente($tipo_componente);
                    
                    if ($tabela_info) {
                        $stmt_ref = $pdo->prepare("SELECT {$tabela_info['campo_ref']} FROM {$tabela_info['tabela']} WHERE {$tabela_info['campo_id']} = ?");
                            if (!$stmt_ref) {
                                error_log("Erro na preparação da query para buscar referência do componente: " . print_r($pdo->errorInfo(), true));
                            } else {
                                $result_ref = $stmt_ref->execute([$componente_id]);
                                if (!$result_ref) {
                                    error_log("Erro na execução da query para buscar referência do componente: " . print_r($stmt_ref->errorInfo(), true));
                                } else {
                        $result_ref = $stmt_ref->fetch(PDO::FETCH_ASSOC);
                        
                        if ($result_ref && isset($result_ref[$tabela_info['campo_ref']])) {
                            $equipamento_ref_para_email = $result_ref[$tabela_info['campo_ref']];
                                    }
                                }
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("Erro ao obter referência real do equipamento: " . $e->getMessage());
                // Continua usando o ID como referência
            }
            
            // Atualizar as variáveis para o email com os valores obtidos
            $equipamento_tipo = $equipamento_tipo_para_email;
            $equipamento_ref = $equipamento_ref_para_email;
            } catch (PDOException $e) {
                error_log("Erro PDO ao registrar componente: " . $e->getMessage());
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                return false;
            }
        }
        
        // Registrar no histórico
        try {
            error_log("Registrando no histórico do ticket");
        $stmt = $pdo->prepare("INSERT INTO ticket_historico (ticket_id, acao, descricao, status_novo, data_acao) 
                              VALUES (:ticket_id, 'criado', 'Ticket criado', 'aberto', NOW())");
            
            if (!$stmt) {
                error_log("Erro na preparação da query para o histórico: " . print_r($pdo->errorInfo(), true));
                throw new Exception("Erro na preparação da consulta de histórico");
            }
            
            $result = $stmt->execute([':ticket_id' => $ticket_id]);
            if (!$result) {
                error_log("Erro na execução da query para o histórico: " . print_r($stmt->errorInfo(), true));
                throw new Exception("Erro ao registrar histórico do ticket");
            }
            
            error_log("Histórico registrado com sucesso");
        } catch (PDOException $e) {
            error_log("Erro PDO ao registrar histórico: " . $e->getMessage());
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
        
        // Confirmar a transação
        try {
            error_log("Confirmando transação");
        $pdo->commit();
            error_log("Transação confirmada com sucesso");
        } catch (PDOException $e) {
            error_log("Erro ao confirmar transação: " . $e->getMessage());
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
        
        // Buscar os dados completos do ticket recém-criado
        try {
            error_log("Buscando dados completos do ticket #$ticket_id");
        $ticket = obterTicketPorId($pdo, $ticket_id);
            
            if (!$ticket) {
                error_log("Aviso: Não foi possível obter os dados completos do ticket #$ticket_id após criação");
            } else {
                error_log("Dados do ticket obtidos com sucesso");
            }
        } catch (Exception $e) {
            error_log("Erro ao obter dados do ticket após criação: " . $e->getMessage());
            // Não retornar false aqui, pois o ticket já foi criado
        }
        
        // Se tiver dados do ticket e do solicitante, enviar emails
        if (isset($ticket) && $ticket && $dados_solicitante) {
            try {
                error_log("Preparando para enviar emails de notificação");
                // Incluir as funções de email
                $email_functions_path = __DIR__ . '/../../../includes/email_funcoes.php';
                error_log("Caminho para as funções de email: $email_functions_path");
                
                if (!file_exists($email_functions_path)) {
                    error_log("AVISO: Arquivo de funções de email não encontrado: $email_functions_path");
                    // Tentar caminho alternativo
                    $email_functions_path_alt = realpath(__DIR__ . '/../../../') . '/includes/email_funcoes.php';
                    error_log("Tentando caminho alternativo: $email_functions_path_alt");
                    
                    if (!file_exists($email_functions_path_alt)) {
                        error_log("AVISO: Arquivo de funções de email também não encontrado no caminho alternativo");
                        throw new Exception("Arquivo de funções de email não encontrado");
                    } else {
                        $email_functions_path = $email_functions_path_alt;
                    }
                }
                
                // Incluir o arquivo de funções de email
                require_once $email_functions_path;
                error_log("Arquivo de funções de email incluído com sucesso");
                
                // Identificar o tipo de solicitante
                $tipo_solicitante = '';
                if ($tipo_usuario === 'comunidade_escolar' && isset($dados_solicitante['cargo'])) {
                    $tipo_solicitante = ucfirst($dados_solicitante['cargo']); // Aluno, Professor, Funcionário, etc.
                } else {
                    $tipo_solicitante = 'Suporte Técnico';
                }
                
                // Verificar se as funções de email existem
                if (function_exists('enviarEmailNovoTicket')) {
                    error_log("Enviando email para o suporte");
                    $resultado_email_suporte = enviarEmailNovoTicket(
                        $pdo, 
                        $ticket, 
                        $dados_solicitante, 
                        $tipo_solicitante, 
                        $equipamento_ref, 
                        $equipamento_tipo
                    );
                    error_log("Resultado do envio para suporte: " . ($resultado_email_suporte ? "Sucesso" : "Falha"));
                } else {
                    error_log("AVISO: Função enviarEmailNovoTicket não encontrada");
                }
                
                if (function_exists('enviarConfirmacaoTicket')) {
                    error_log("Enviando email de confirmação para o solicitante");
                    $resultado_email_solicitante = enviarConfirmacaoTicket($pdo, $ticket, $dados_solicitante);
                    error_log("Resultado do envio para solicitante: " . ($resultado_email_solicitante ? "Sucesso" : "Falha"));
                } else {
                    error_log("AVISO: Função enviarConfirmacaoTicket não encontrada");
                }
            } catch (Exception $e) {
                error_log("Erro ao enviar emails: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
                // Não retornar false aqui, pois o ticket já foi criado
            }
        }
        
        error_log("Ticket #$ticket_id criado com sucesso");
        return $ticket_id;
    } catch (Exception $e) {
        // Reverter em caso de erro
        if ($pdo && $pdo->inTransaction()) {
            error_log("Revertendo transação devido a erro: " . $e->getMessage());
            $pdo->rollBack();
        }
        error_log("Erro ao criar ticket: " . $e->getMessage());
        error_log("Trace: " . $e->getTraceAsString());
        return false;
    }
}

/**
 * Salva anexos para um ticket
 * @param PDO $pdo Conexão com a base de dados
 * @param int $ticket_id ID do ticket
 * @param array $arquivos Array de arquivos para upload
 * @return bool Sucesso ou falha
 */
function salvarAnexosTicket($pdo, $ticket_id, $arquivos) {
    try {
        error_log("Iniciando processo de salvamento de anexos para o ticket ID: $ticket_id");
        error_log("Dados recebidos: " . print_r($arquivos, true));
        
        if (empty($arquivos)) {
            error_log("Nenhum anexo encontrado para o ticket ID: $ticket_id");
            return true; // Não há anexos para salvar
        }
        
        // Diretório para salvar os anexos
        $diretorio_base = realpath(__DIR__ . '/../../../') . '/uploads/tickets/';
        error_log("Diretório base para uploads: $diretorio_base");
        
        // Criar diretório base se não existir
        if (!file_exists($diretorio_base)) {
            error_log("Criando diretório base para uploads: $diretorio_base");
            if (!mkdir($diretorio_base, 0777, true)) {
                error_log("Falha ao criar diretório base: $diretorio_base - " . error_get_last()['message']);
                return false;
            }
            chmod($diretorio_base, 0777); // Garantir permissões após criar
        }
        
        $diretorio_ticket = $diretorio_base . $ticket_id . '/';
        error_log("Diretório para o ticket: $diretorio_ticket");
        
        // Criar o diretório do ticket se não existir
        if (!file_exists($diretorio_ticket)) {
            error_log("Criando diretório para o ticket: $diretorio_ticket");
            if (!mkdir($diretorio_ticket, 0777, true)) {
                error_log("Falha ao criar diretório do ticket: $diretorio_ticket - " . error_get_last()['message']);
                return false;
            }
            chmod($diretorio_ticket, 0777); // Garantir permissões após criar
        }
        
        $sucesso = true;
        
        error_log("Processando " . count($arquivos) . " anexos para o ticket ID: $ticket_id");
        
        foreach ($arquivos as $arquivo) {
            error_log("Processando arquivo: " . $arquivo['name'] . ", tamanho: " . $arquivo['size'] . " bytes");
            
            if ($arquivo['error'] === UPLOAD_ERR_OK) {
                $nome_temp = $arquivo['tmp_name'];
                $nome_arquivo = $arquivo['name'];
                $tipo_arquivo = $arquivo['type'];
                $tamanho = $arquivo['size'];
                
                // Gerar um nome único para o arquivo
                $nome_unico = uniqid() . '_' . $nome_arquivo;
                $caminho = $diretorio_ticket . $nome_unico;
                
                error_log("Tentando mover arquivo de $nome_temp para $caminho");
                
                // Mover o arquivo para o diretório
                if (move_uploaded_file($nome_temp, $caminho)) {
                    error_log("Arquivo movido com sucesso para: $caminho");
                    
                    // Caminho relativo para armazenar no banco de dados
                    $caminho_relativo = 'uploads/tickets/' . $ticket_id . '/' . $nome_unico;
                    
                    // Registrar o anexo na base de dados
                    $stmt = $pdo->prepare("INSERT INTO ticket_anexos (
                        ticket_id, 
                        nome_arquivo, 
                        tipo_arquivo, 
                        tamanho, 
                        caminho, 
                        data_upload
                    ) VALUES (
                        :ticket_id, 
                        :nome_arquivo, 
                        :tipo_arquivo, 
                        :tamanho, 
                        :caminho, 
                        :data_upload
                    )");
                    
                    $now = date('Y-m-d H:i:s');
                    
                    $stmt->bindParam(':ticket_id', $ticket_id);
                    $stmt->bindParam(':nome_arquivo', $nome_arquivo);
                    $stmt->bindParam(':tipo_arquivo', $tipo_arquivo);
                    $stmt->bindParam(':tamanho', $tamanho);
                    $stmt->bindParam(':caminho', $caminho_relativo);
                    $stmt->bindParam(':data_upload', $now);
                    
                    if ($stmt->execute()) {
                        error_log("Anexo registrado com sucesso no banco de dados: $nome_arquivo, ID: " . $pdo->lastInsertId());
                    } else {
                        $sucesso = false;
                        error_log("Erro ao registrar anexo na base de dados: " . $nome_arquivo);
                        error_log("Erro SQL: " . implode(', ', $stmt->errorInfo()));
                    }
                } else {
                    $sucesso = false;
                    error_log("Erro ao mover arquivo para o diretório: " . $caminho);
                    error_log("Código de erro: " . print_r(error_get_last(), true));
                }
            } else {
                $sucesso = false;
                $erro_mensagem = "";
                switch ($arquivo['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                        $erro_mensagem = "O arquivo excede o tamanho máximo permitido no php.ini";
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $erro_mensagem = "O arquivo excede o tamanho máximo permitido no formulário";
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $erro_mensagem = "O arquivo foi apenas parcialmente carregado";
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $erro_mensagem = "Nenhum arquivo foi enviado";
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $erro_mensagem = "Falta uma pasta temporária";
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $erro_mensagem = "Falha ao escrever o arquivo no disco";
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $erro_mensagem = "Upload interrompido por extensão";
                        break;
                    default:
                        $erro_mensagem = "Erro desconhecido";
                }
                error_log("Erro no upload do arquivo " . $arquivo['name'] . ": " . $erro_mensagem . " (código " . $arquivo['error'] . ")");
            }
        }
        
        if ($sucesso) {
            error_log("Todos os anexos foram processados com sucesso para o ticket ID: $ticket_id");
        } else {
            error_log("Houve erros ao processar anexos para o ticket ID: $ticket_id");
        }
        
        return $sucesso;
    } catch (PDOException $e) {
        error_log("Erro PDO ao salvar anexos: " . $e->getMessage());
        return false;
    } catch (Exception $e) {
        error_log("Erro geral ao salvar anexos: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtém tickets do sistema com base em filtros
 * @param PDO $pdo Conexão com a base de dados
 * @param int|null $usuario_id ID do usuário (para filtrar tickets de um técnico específico)
 * @param string $status Status para filtrar (vazio = todos)
 * @param string $prioridade Prioridade para filtrar (vazio = todas)
 * @param int $departamento_id ID da sala para filtrar (0 = todas)
 * @return array Lista de tickets
 */
function obterTickets($pdo, $usuario_id = null, $status = '', $prioridade = '', $departamento_id = 0) {
    // Consulta base para obter tickets
    $sql = "SELECT t.*,
                  COALESCE(s.sala, t.sala) as nome_sala,
                  tecnico.nome_completo as nome_tecnico
           FROM tickets t 
           LEFT JOIN salas s ON t.sala = s.id_sala OR t.sala = s.sala
           LEFT JOIN suporte_tecnico tecnico ON t.id_tecnico = tecnico.id
           WHERE 1=1";
    
    $params = [];

    // Filtrar por usuário técnico
    if ($usuario_id !== null) {
        $sql .= " AND t.id_tecnico = ?";
        $params[] = $usuario_id;
    }

    // Filtrar por status
    if (!empty($status)) {
        $sql .= " AND t.status = ?";
        $params[] = $status;
    }

    // Filtrar por prioridade
    if (!empty($prioridade)) {
        $sql .= " AND t.prioridade = ?";
        $params[] = $prioridade;
    }

    // Filtrar por sala
    if (!empty($departamento_id) && $departamento_id > 0) {
        $sql .= " AND t.sala = ?";
        $params[] = $departamento_id;
    }

    // Ordenar por status, prioridade e data de atualização
    $sql .= " ORDER BY 
              CASE 
                WHEN t.status = 'aberto' THEN 1
                WHEN t.status = 'em_andamento' THEN 2
                WHEN t.status = 'aguardando' THEN 3
                WHEN t.status = 'resolvido' THEN 4
                WHEN t.status = 'fechado' THEN 5
                ELSE 6
              END,
              CASE
                WHEN t.prioridade = '1' THEN 1  -- Alta prioridade
                WHEN t.prioridade = '2' THEN 2  -- Média prioridade
                WHEN t.prioridade = '3' THEN 3  -- Baixa prioridade
                ELSE 4
              END,
              t.ultima_atualizacao DESC";
              
    error_log("SQL de obterTickets: " . $sql);

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Transformar prioridade numérica em texto para facilitar o uso no frontend
        foreach ($tickets as &$ticket) {
            switch ($ticket['prioridade']) {
                case '1':
                    $ticket['prioridade'] = 'alta';
                    break;
                case '2':
                    $ticket['prioridade'] = 'media';
                    break;
                case '3':
                    $ticket['prioridade'] = 'baixa';
                    break;
                default:
                    $ticket['prioridade'] = 'baixa';
            }
            
            // Obter informações do componente associado ao ticket
            $ticket['tipo_componente'] = '';
            try {
                $stmt_comp = $pdo->prepare("SELECT equipamento, ref_equipamento FROM ticket_componentes WHERE ticket_id = ?");
                $stmt_comp->execute([$ticket['id']]);
                $componente = $stmt_comp->fetch(PDO::FETCH_ASSOC);
                
                if ($componente) {
                    $ticket['tipo_componente'] = $componente['equipamento'];
                    $ticket['ref_equipamento'] = $componente['ref_equipamento'];
                }
            } catch (PDOException $e) {
                error_log("Erro ao obter componente do ticket #" . $ticket['id'] . ": " . $e->getMessage());
            }
        }
        
        return $tickets;
    } catch (PDOException $e) {
        error_log("Erro ao obter tickets: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtém todas as salas/departamentos disponíveis
 * @param PDO $pdo Conexão com a base de dados
 * @return array Lista de departamentos/salas
 */
function obterDepartamentos($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id_sala as id, sala as nome FROM salas ORDER BY sala");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao obter departamentos: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtém detalhes de um ticket específico
 * @param PDO $pdo Conexão com a base de dados
 * @param int $ticket_id ID do ticket
 * @return array|false Dados do ticket ou falso se não encontrado
 */
function obterTicketPorId($pdo, $ticket_id) {
    try {
        // Obter informações básicas do ticket
        $sql = "SELECT t.*, 
                       COALESCE(s.sala, t.sala) as nome_sala,
                       tecnico.nome_completo as nome_tecnico,
                       COALESCE(ce.nome_completo, st.nome_completo, t.solicitante) as solicitante_nome
                FROM tickets t
                LEFT JOIN salas s ON t.sala = s.id_sala OR t.sala = s.sala
                LEFT JOIN suporte_tecnico tecnico ON t.id_tecnico = tecnico.id
                LEFT JOIN comunidade_escolar ce ON t.solicitante = ce.numero_processo OR (ce.id = t.solicitante AND t.solicitante REGEXP '^[0-9]+$')
                LEFT JOIN suporte_tecnico st ON t.solicitante = st.numero_processo OR (st.id = t.solicitante AND t.solicitante REGEXP '^[0-9]+$')
                WHERE t.id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ticket_id]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ticket) {
            error_log("Ticket não encontrado: ID=$ticket_id");
            return false;
        }

        // Transformar prioridade numérica em texto para facilitar o uso no frontend
        switch ($ticket['prioridade']) {
            case '1':
                $ticket['prioridade'] = 'alta';
                break;
            case '2':
                $ticket['prioridade'] = 'media';
                break;
            case '3':
                $ticket['prioridade'] = 'baixa';
                break;
            default:
                $ticket['prioridade'] = 'baixa';
        }
        
        // Obter informações do componente associado ao ticket
        try {
            $stmt_comp = $pdo->prepare("SELECT equipamento, ref_equipamento FROM ticket_componentes WHERE ticket_id = ?");
            $stmt_comp->execute([$ticket_id]);
            $componente = $stmt_comp->fetch(PDO::FETCH_ASSOC);
            
            if ($componente) {
                $ticket['tipo_componente'] = $componente['equipamento'];
                $ticket['ref_equipamento'] = $componente['ref_equipamento'];
                
                // Se for um computador, buscar informações mais detalhadas
                if ($componente['equipamento'] == 'computador') {
                    $stmt_comp_info = $pdo->prepare("SELECT * FROM computadores WHERE COMP_ID = ?");
                    $stmt_comp_info->execute([$componente['ref_equipamento']]);
                    $comp_info = $stmt_comp_info->fetch(PDO::FETCH_ASSOC);
                    
                    if ($comp_info) {
                        $ticket['equipamento_detalhes'] = $comp_info;
                    }
                } else {
                    // Para outros tipos de componentes, tentar buscar informações baseado no tipo
                    $tipo = $componente['equipamento'];
                    $tabela = mapearTipoComponenteParaTabela($tipo);
                    
                    if ($tabela) {
                        $stmt_comp_info = $pdo->prepare("SELECT * FROM $tabela WHERE id = ?");
                        $stmt_comp_info->execute([$componente['ref_equipamento']]);
                        $comp_info = $stmt_comp_info->fetch(PDO::FETCH_ASSOC);
                        
                        if ($comp_info) {
                            $ticket['equipamento_detalhes'] = $comp_info;
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Erro ao obter componente do ticket #$ticket_id: " . $e->getMessage());
        }
        
        return $ticket;
    } catch (PDOException $e) {
        error_log("Erro ao obter ticket por ID: " . $e->getMessage());
        return false;
    }
}

/**
 * Mapeia um tipo de componente para sua tabela correspondente
 * @param string $tipo Tipo do componente
 * @return string|null Nome da tabela ou null se não encontrado
 */
function mapearTipoComponenteParaTabela($tipo) {
    $mapeamento = obterMapeamentoTabelaComponente($tipo);
    return $mapeamento ? $mapeamento['tabela'] : null;
}

/**
 * Obtém todos os tipos de componentes
 *
 * @param PDO $pdo Conexão com o banco de dados
 * @return array Array com os tipos de componentes
 */
function obterTiposComponentesBD($pdo) {
    $stmt = $pdo->prepare("SELECT DISTINCT tipo FROM componentes ORDER BY tipo");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Obtém as referências de componentes por tipo
 *
 * @param PDO $pdo Conexão com o banco de dados
 * @param string $tipo Tipo do componente
 * @param int|null $sala_id ID da sala (opcional)
 * @return array Array com as referências
 */
function obterReferenciasComponentes($pdo, $tipo, $sala_id = null) {
    $sql = "SELECT id, referencia FROM componentes WHERE tipo = :tipo AND estado = 'disponível'";
    $params = [':tipo' => $tipo];
    
    if ($sala_id !== null) {
        $sql .= " AND sala_id = :sala_id";
        $params[':sala_id'] = $sala_id;
    }
    
    $sql .= " ORDER BY referencia";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Faz upload de um arquivo anexo
 *
 * @param PDO $pdo Conexão com o banco de dados
 * @param int $ticket_id ID do ticket
 * @param array $arquivo Dados do arquivo ($_FILES)
 * @return bool True se o upload foi bem-sucedido, false caso contrário
 */
function uploadAnexo($pdo, $ticket_id, $arquivo) {
    // Verifica se é um upload válido
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Diretório de upload
    $diretorio_upload = __DIR__ . '/../anexos/';
    
    // Cria o diretório se não existir
    if (!file_exists($diretorio_upload)) {
        mkdir($diretorio_upload, 0755, true);
    }
    
    // Gera um nome único para o arquivo
    $nome_arquivo = $arquivo['name'];
    $nome_arquivo_sanitizado = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $nome_arquivo);
    $extensao = pathinfo($nome_arquivo_sanitizado, PATHINFO_EXTENSION);
    $nome_arquivo_unico = uniqid('anexo_') . '_' . time() . '.' . $extensao;
    $caminho_arquivo = $diretorio_upload . $nome_arquivo_unico;
    
    // Move o arquivo para o diretório de upload
    if (move_uploaded_file($arquivo['tmp_name'], $caminho_arquivo)) {
        // Registra o anexo no banco de dados
        $stmt = $pdo->prepare("INSERT INTO ticket_anexos (ticket_id, nome_arquivo, caminho_arquivo, data_upload) 
                               VALUES (:ticket_id, :nome_arquivo, :caminho_arquivo, NOW())");
        $stmt->bindValue(':ticket_id', $ticket_id);
        $stmt->bindValue(':nome_arquivo', $nome_arquivo);
        $stmt->bindValue(':caminho_arquivo', $nome_arquivo_unico);
        
        if ($stmt->execute()) {
            return true;
        } else {
            // Se falhar em registrar no banco, remove o arquivo
            unlink($caminho_arquivo);
            return false;
        }
    }
    
    return false;
}

/**
 * Verifica se um usuário tem permissão para acessar um ticket
 *
 * @param PDO $pdo Conexão com o banco de dados
 * @param int $ticket_id ID do ticket
 * @param array $usuario Dados do usuário
 * @return bool True se tem permissão, false caso contrário
 */
function verificarPermissaoTicket($pdo, $ticket_id, $usuario) {
    // Administradores e técnicos têm acesso a todos os tickets
    if ($usuario['tipo'] === 'admin' || $usuario['tipo'] === 'tecnico') {
        return true;
    }
    
    // Usuários comuns só têm acesso aos seus próprios tickets
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE id = :ticket_id AND id_usuario = :usuario_id");
    $stmt->bindValue(':ticket_id', $ticket_id);
    $stmt->bindValue(':usuario_id', $usuario['id']);
    $stmt->execute();
    
    return $stmt->fetchColumn() > 0;
}

/**
 * Obtém os anexos de um ticket
 *
 * @param PDO $pdo Conexão com o banco de dados
 * @param int $ticket_id ID do ticket
 * @return array Array com os anexos
 */
function obterAnexosTicket($pdo, $ticket_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM ticket_anexos WHERE ticket_id = ? ORDER BY data_upload");
        $stmt->execute([$ticket_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Erro ao obter anexos do ticket: ' . $e->getMessage());
        return [];
    }
}

/**
 * Obtém os comentários de um ticket
 * 
 * @param PDO $pdo Conexão com o banco de dados
 * @param int $ticket_id ID do ticket
 * @return array Retorna um array com os comentários do ticket
 */
function obterComentariosTicket($pdo, $ticket_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, 
                  COALESCE(
                    (SELECT nome_completo FROM suporte_tecnico WHERE id = c.tecnico_id),
                    (SELECT nome_completo FROM comunidade_escolar WHERE id = c.tecnico_id),
                    'Usuário'
                  ) AS nome_autor
            FROM ticket_comentarios c
            WHERE c.ticket_id = ?
            ORDER BY c.data_comentario ASC
        ");
        $stmt->execute([$ticket_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Erro ao obter comentários do ticket: " . $e->getMessage());
        return [];
    }
}

/**
 * Adiciona um comentário a um ticket
 * @param PDO $pdo Conexão com o banco de dados
 * @param int $ticket_id ID do ticket
 * @param int $usuario_id ID do usuário que está fazendo o comentário
 * @param string $conteudo Conteúdo do comentário
 * @param bool $sistema Se é um comentário do sistema ou do usuário
 * @return bool Sucesso ou falha
 */
function adicionarComentario($pdo, $ticket_id, $usuario_id, $conteudo, $sistema = false) {
    try {
        // Inserir o comentário
        $stmt = $pdo->prepare("INSERT INTO ticket_comentarios 
                                (ticket_id, tecnico_id, comentario, data_comentario) 
                                VALUES (?, ?, ?, NOW())");
        $resultado = $stmt->execute([$ticket_id, $usuario_id, $conteudo]);
        
        if ($resultado) {
            // Atualizar a data de atualização do ticket
            $stmt = $pdo->prepare("UPDATE tickets SET ultima_atualizacao = NOW() WHERE id = ?");
            $stmt->execute([$ticket_id]);
            
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Erro ao adicionar comentário: " . $e->getMessage());
        return false;
    }
}

/**
 * Adiciona um comentário de sistema a um ticket
 *
 * @param PDO $pdo Conexão com o banco de dados
 * @param int $ticket_id ID do ticket
 * @param string $conteudo Conteúdo do comentário
 * @return bool True se o comentário foi adicionado com sucesso, false caso contrário
 */
function adicionarComentarioSistema($pdo, $ticket_id, $conteudo) {
    try {
        $stmt = $pdo->prepare("INSERT INTO ticket_comentarios (ticket_id, tecnico_id, comentario, data_comentario)
                                VALUES (:ticket_id, 0, :conteudo, NOW())");
        $stmt->bindValue(':ticket_id', $ticket_id);
        $stmt->bindValue(':conteudo', $conteudo);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Erro ao adicionar comentário do sistema: " . $e->getMessage());
        return false;
    }
}

/**
 * Atualiza o status de um ticket
 * @param PDO $pdo Conexão com o banco de dados
 * @param int $ticket_id ID do ticket
 * @param string $novo_status Novo status para o ticket
 * @param int $usuario_id ID do usuário que está realizando a mudança
 * @return bool Sucesso ou falha
 */
function atualizarStatusTicket($pdo, $ticket_id, $novo_status, $usuario_id) {
    try {
        // Verificar se o ticket existe
        $stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ?");
        $stmt->execute([$ticket_id]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ticket) {
            error_log("Erro: Ticket #{$ticket_id} não encontrado");
            return false;
        }
        
        $status_antigo = $ticket['status'];
        
        // Verificar se o novo status é válido
        $status_validos = ['aberto', 'em_andamento', 'resolvido', 'fechado', 'cancelado'];
        if (!in_array($novo_status, $status_validos)) {
            error_log("Erro: Status '{$novo_status}' inválido");
            return false;
        }
        
        // Atualizar o status do ticket
        $stmt = $pdo->prepare("UPDATE tickets 
                              SET status = ?, ultima_atualizacao = NOW() 
                              WHERE id = ?");
        $resultado = $stmt->execute([$novo_status, $ticket_id]);
        
        if ($resultado) {
            // Registrar mudança no histórico
            registrarHistoricoTicket($pdo, $ticket_id, $usuario_id, 'status alterado', 
                                   "Status alterado de {$status_antigo} para {$novo_status}", 
                                   $status_antigo, $novo_status);
            
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Erro ao atualizar status do ticket: " . $e->getMessage());
        return false;
    }
}

/**
 * Formata uma data no padrão português
 *
 * @param string $data Data em formato SQL
 * @return string Data formatada
 */
function formatarData($data) {
    if (empty($data)) return '';
    
    $timestamp = strtotime($data);
    return date('d/m/Y H:i:s', $timestamp);
}

/**
 * Obtém o tamanho máximo do arquivo (5MB)
 */
function verificarTamanhoArquivo($tamanho) {
    $tamanho_maximo = 5 * 1024 * 1024; // 5MB
    return $tamanho <= $tamanho_maximo;
}

/**
 * Obtém uma lista dos tipos de equipamentos disponíveis
 */
function obterTiposEquipamentos() {
    return [
        'cooler' => 'Coolers',
        'caixa' => 'Caixas',
        'disco' => 'Discos',
        'drive' => 'Drives',
        'fonte' => 'Fontes',
        'memoriaram' => 'Memória RAM',
        'monitor' => 'Monitor',
        'placagrafica' => 'Placa Gráfica',
        'placamae' => 'Placa Mãe',
        'placarede' => 'Placa de Rede',
        'processador' => 'Processador',
        'rato' => 'Ratos',
        'teclado' => 'Teclados',
        'ventoinha' => 'Ventoinhas'
    ];
}

/**
 * Verifica se um componente está disponível para manutenção
 * (estado deve ser 'em uso' ou 'disponivel')
 * 
 * @param PDO $pdo Conexão com o banco de dados
 * @param string $tipo Tipo do componente
 * @param int $id ID do componente
 * @return bool Retorna true se o componente está disponível, false caso contrário
 */
function verificarComponenteDisponivel($pdo, $tipo, $id) {
    try {
        $tabela_estado = $tipo . "s_estado";
        
        // Ajustes específicos para tabelas que não seguem o padrão
        if ($tipo == 'memoriaram') {
            $tabela_estado = 'memoriaram_estado';
        } elseif ($tipo == 'caixa') {
            $tabela_estado = 'caixas_estado';
        } elseif ($tipo == 'placagrafica') {
            $tabela_estado = 'placagrafica_estado';
        } elseif ($tipo == 'placamae') {
            $tabela_estado = 'placamae_estado';
        } elseif ($tipo == 'placarede') {
            $tabela_estado = 'placarede_estado';
        } elseif ($tipo == 'rato') {
            $tabela_estado = 'rato_estado';
        }
        
        // Verificar se a tabela existe
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tabela_estado]);
        if ($stmt->rowCount() == 0) {
            error_log("Tabela $tabela_estado não existe");
            return false;
        }
        
        // Verificar o estado do componente
        $stmt = $pdo->prepare("SELECT estado FROM $tabela_estado WHERE id_$tipo = ?");
        $stmt->execute([$id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$resultado) {
            return false; // Componente não encontrado
        }
        
        // Verificar se o estado é 'em uso' ou 'disponivel'
        return in_array($resultado['estado'], ['em uso', 'disponivel']);
        
    } catch (PDOException $e) {
        error_log("Erro ao verificar componente: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtém os computadores disponíveis (estado 1 ou 2)
 * @param PDO $pdo Conexão com a base de dados
 * @param int|null $sala_id ID da sala (opcional)
 * @return array Lista de computadores
 */
function obterComputadoresDisponiveis($pdo, $sala_id = null) {
    try {
        // Primeiro, vamos converter o sala_id para o formato correto, se fornecido
        $sala_nome = null;
        if ($sala_id !== null) {
            $stmt = $pdo->prepare("SELECT sala FROM salas WHERE id_sala = ?");
            $stmt->execute([$sala_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $sala_nome = $result['sala'];
            }
        }

        $sql = "SELECT COMP_ID as id, COMP_REF as referencia, COMP_SALA as sala, COMP_ESTADO as estado 
                FROM computadores 
                WHERE COMP_ESTADO IN ('1', '2')";
        
        $params = [];
        
        // Adicionar filtro por sala se fornecido
        if ($sala_nome !== null) {
            $sql .= " AND COMP_SALA = ?";
            $params[] = $sala_nome;
        }
        
        $sql .= " ORDER BY COMP_REF";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $computadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Registrar em log para debug
        error_log("Obtidos " . count($computadores) . " computadores disponíveis");
        
        // Adicionar descrição completa para cada computador
        foreach ($computadores as &$computador) {
            $estado_texto = ($computador['estado'] == '1') ? 'Disponível' : 'Em uso';
            $computador['descricao_completa'] = "Computador " . $computador['referencia'] . 
                                               " (ID: " . $computador['id'] . ", Estado: " . $estado_texto . ")";
            
            // Registrar detalhes de cada computador para debug
            error_log("Computador encontrado: ID=" . $computador['id'] . ", REF=" . $computador['referencia']);
        }
        
        return $computadores;
    } catch (PDOException $e) {
        error_log("Erro ao obter computadores disponíveis: " . $e->getMessage());
        return [];
    }
}

/**
 * Retorna a cor de status para cada tipo de prioridade
 * 
 * @param string $prioridade Prioridade do ticket (baixa, media, alta)
 * @return string Classe CSS para a cor
 */
function getCorPrioridade($prioridade) {
    switch ($prioridade) {
        case '1':
        case 'alta':
            return 'danger';
        case '2':
        case 'media':
            return 'warning';
        case '3':
        case 'baixa':
            return 'success';
        default:
            return 'secondary';
    }
}

/**
 * Formata o texto da prioridade para exibição
 * 
 * @param string $prioridade Prioridade do ticket (1, 2, 3)
 * @return string Prioridade formatada
 */
function formatarPrioridade($prioridade) {
    switch ($prioridade) {
        case '1':
            return 'Alta';
        case '2':
            return 'Média';
        case '3':
            return 'Baixa';
        default:
            return 'Não definida';
    }
}

/**
 * Formata o status do ticket para exibição
 * 
 * @param string $status Status do ticket
 * @return string Status formatado
 */
function formatarStatus($status) {
    switch ($status) {
        case 'aberto':
            return 'Aberto';
        case 'em_andamento':
            return 'Em Andamento';
        case 'resolvido':
            return 'Resolvido';
        case 'fechado':
            return 'Fechado';
        default:
            return 'Desconhecido';
    }
}

/**
 * Retorna a cor de fundo para cada tipo de status
 * 
 * @param string $status Status do ticket
 * @return string Classe CSS para a cor
 */
function getCorStatus($status) {
    switch ($status) {
        case 'aberto':
            return 'danger';
        case 'em_andamento':
            return 'warning';
        case 'resolvido':
            return 'success';
        case 'fechado':
            return 'secondary';
        default:
            return 'light';
    }
}

/**
 * Obtém o equipamento relacionado a um ticket
 * 
 * @param PDO $pdo Conexão com o banco de dados
 * @param int $ticketId ID do ticket
 * @return array|null Dados do equipamento ou null se não existir
 */
function obterEquipamentoTicket($pdo, $ticketId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM ticket_componentes WHERE ticket_id = ?");
        $stmt->execute([$ticketId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Erro ao obter equipamento do ticket: ' . $e->getMessage());
        return null;
    }
}

/**
 * Obtém o histórico de um ticket
 * 
 * @param PDO $pdo Conexão com o banco de dados
 * @param int $ticketId ID do ticket
 * @return array Array com o histórico
 */
function obterHistoricoTicket($pdo, $ticketId) {
    try {
        $sql = "SELECT h.*, 
                CASE 
                    WHEN h.tecnico_id IS NOT NULL THEN (SELECT nome_completo FROM suporte_tecnico WHERE id = h.tecnico_id)
                    ELSE 'Sistema'
                END as nome_tecnico
                FROM ticket_historico h
                WHERE h.ticket_id = ?
                ORDER BY h.data_acao DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Erro ao obter histórico do ticket: ' . $e->getMessage());
        return [];
    }
}

/**
 * Adiciona um anexo ao ticket
 * 
 * @param PDO $pdo Conexão com o banco de dados
 * @param int $ticket_id ID do ticket
 * @param int $usuario_id ID do usuário
 * @param string $nome_arquivo Nome original do arquivo
 * @param int $tamanho Tamanho do arquivo em bytes
 * @param string $tipo Tipo MIME do arquivo
 * @param string $caminho Nome único do arquivo no sistema
 * @return bool
 */
function adicionarAnexo($pdo, $ticket_id, $usuario_id, $nome_arquivo, $tamanho, $tipo, $caminho) {
    try {
        // Verificar se o caminho precisa ser modificado para remover prefixos redundantes
        if (strpos($caminho, 'uploads/tickets/') === 0) {
            // Se o caminho já inclui 'uploads/tickets/', não duplicamos
            $caminho_limpo = $caminho;
        } else {
            // Caso contrário, garantimos que o caminho está apenas com o arquivo
            $caminho_limpo = basename($caminho);
        }
        
        $stmt = $pdo->prepare("INSERT INTO ticket_anexos (ticket_id, nome_arquivo, tipo_arquivo, tamanho, caminho, data_upload) 
                               VALUES (?, ?, ?, ?, ?, NOW())");
        return $stmt->execute([$ticket_id, $nome_arquivo, $tipo, $tamanho, $caminho_limpo]);
    } catch (PDOException $e) {
        error_log("Erro ao adicionar anexo: " . $e->getMessage());
        return false;
    }
}

/**
 * Mapeia o tipo de componente para as tabelas e colunas corretas da base de dados
 * @param string $tipo Tipo do componente (rato, teclado, monitor, etc.)
 * @return array|null Mapeamento com tabela e campos ou null se não encontrado
 */
function obterMapeamentoTabelaComponente($tipo) {
    $mapeamento = [
        'rato' => [
            'tabela' => 'rato',
            'campo_id' => 'RATOS_ID',
            'campo_ref' => 'RATOS_REF'
        ],
        'teclado' => [
            'tabela' => 'teclado',
            'campo_id' => 'TECLADO_ID',
            'campo_ref' => 'TECLADO_REF'
        ],
        'monitor' => [
            'tabela' => 'monitor',
            'campo_id' => 'MONITOR_ID',
            'campo_ref' => 'MONITOR_REF'
        ],
        'cooler' => [
            'tabela' => 'cooler',
            'campo_id' => 'COO_ID',
            'campo_ref' => 'COO_REF'
        ],
        'disco' => [
            'tabela' => 'disco',
            'campo_id' => 'DISCO_ID',
            'campo_ref' => 'DISCO_REF'
        ],
        'fonte' => [
            'tabela' => 'fonte',
            'campo_id' => 'FONTE_ID',
            'campo_ref' => 'FONTE_REF'
        ],
        'memoriaram' => [
            'tabela' => 'memoriaram',
            'campo_id' => 'MR_ID',
            'campo_ref' => 'MR_REF'
        ],
        'placarede' => [
            'tabela' => 'placarede',
            'campo_id' => 'PR_ID',
            'campo_ref' => 'PR_REF'
        ],
        'placagrafica' => [
            'tabela' => 'placagrafica',
            'campo_id' => 'PG_ID',
            'campo_ref' => 'PG_REF'
        ],
        'placamae' => [
            'tabela' => 'placamae',
            'campo_id' => 'PM_ID',
            'campo_ref' => 'PM_REF'
        ],
        'processador' => [
            'tabela' => 'processador',
            'campo_id' => 'PROC_ID',
            'campo_ref' => 'PROC_REF'
        ],
        'caixa' => [
            'tabela' => 'caixa',
            'campo_id' => 'CAIXA_ID',
            'campo_ref' => 'CAIXA_REF'
        ],
        'drivedvd' => [
            'tabela' => 'drivedvd',
            'campo_id' => 'DRVD_ID',
            'campo_ref' => 'DRVD_REF'
        ],
        'ventoinha' => [
            'tabela' => 'ventoinha',
            'campo_id' => 'VENT_ID',
            'campo_ref' => 'VENT_REF'
        ],
        'computador' => [
            'tabela' => 'computadores',
            'campo_id' => 'COMP_ID',
            'campo_ref' => 'COMP_REF'
        ]
    ];
    
    return isset($mapeamento[$tipo]) ? $mapeamento[$tipo] : null;
}

?> 