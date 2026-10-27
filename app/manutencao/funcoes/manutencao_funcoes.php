<?php
/**
 * Funções para gerenciamento de tickets em manutenção
 */

/**
 * Obtém os tickets atribuídos a um técnico específico
 */
function obterTicketsAtribuidos($pdo, $tecnico_id) {
    $sql = "SELECT t.*, 
                  (SELECT COUNT(*) FROM ticket_comentarios WHERE ticket_id = t.id) AS total_comentarios
           FROM tickets t 
           WHERE t.id_tecnico = ? AND t.status != 'fechado'
           ORDER BY 
                CASE 
                    WHEN t.status = 'aberto' THEN 1
                    WHEN t.status = 'em_andamento' THEN 2
                    WHEN t.status = 'resolvido' THEN 3
                    ELSE 4
                END,
                CASE 
                    WHEN t.prioridade = '3' THEN 1
                    WHEN t.prioridade = '2' THEN 2
                    WHEN t.prioridade = '1' THEN 3
                END,
                t.criado_em DESC";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tecnico_id]);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Processa cada ticket para substituir IDs por nomes
        foreach ($tickets as &$ticket) {
            // Tratar sala - verificar se é um ID numérico
            if (is_numeric($ticket['sala'])) {
                try {
                    $salaStmt = $pdo->prepare("SELECT sala FROM salas WHERE id_sala = ?");
                    $salaStmt->execute([$ticket['sala']]);
                    $salaResult = $salaStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($salaResult) {
                        $ticket['sala'] = $salaResult['sala'];
                    }
                } catch (PDOException $e) {
                    error_log("Erro ao buscar nome da sala: " . $e->getMessage());
                    // Mantém o valor original
                }
            }
            
            // Tratar solicitante - verificar se é um ID numérico
            if (is_numeric($ticket['solicitante'])) {
                try {
                    // Primeiro tenta buscar na tabela comunidade_escolar
                    $userStmt = $pdo->prepare("SELECT nome_completo FROM comunidade_escolar WHERE id = ?");
                    $userStmt->execute([$ticket['solicitante']]);
                    $userResult = $userStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($userResult) {
                        $ticket['solicitante'] = $userResult['nome_completo'];
                    } else {
                        // Se não encontrar, tenta na tabela suporte_tecnico
                        $tecnicoStmt = $pdo->prepare("SELECT nome_completo FROM suporte_tecnico WHERE id = ?");
                        $tecnicoStmt->execute([$ticket['solicitante']]);
                        $tecnicoResult = $tecnicoStmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($tecnicoResult) {
                            $ticket['solicitante'] = $tecnicoResult['nome_completo'];
                        }
                    }
                } catch (PDOException $e) {
                    error_log("Erro ao buscar nome do solicitante: " . $e->getMessage());
                    // Mantém o valor original
                }
            }
        }
        
        return $tickets;
    } catch (PDOException $e) {
        error_log("Erro ao obter tickets atribuídos: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtém detalhes de um ticket específico, incluindo nome do solicitante e técnico
 */
function obterDetalhesTicket($pdo, $ticket_id) {
    error_log("DEBUG: obterDetalhesTicket - Iniciando busca para ticket_id = $ticket_id");
    
    try {
        // Primeiro, vamos verificar se o ticket existe com uma consulta simples
        $basicQuery = "SELECT * FROM tickets WHERE id = :ticket_id";
        $basicStmt = $pdo->prepare($basicQuery);
        $basicStmt->bindParam(':ticket_id', $ticket_id, PDO::PARAM_INT);
        $basicStmt->execute();
        $basicResult = $basicStmt->fetch(PDO::FETCH_ASSOC);
        
        error_log("DEBUG: obterDetalhesTicket - Consulta básica: " . ($basicResult ? "Ticket existe na tabela" : "Ticket NÃO existe na tabela"));
        
        if (!$basicResult) {
            error_log("DEBUG: obterDetalhesTicket - O ticket #$ticket_id não existe na tabela tickets");
            return false;
        }
        
        // Vamos verificar se a tabela salas existe e obter o nome da sala
        $sala_nome = $basicResult['sala']; // Valor padrão (pode ser ID ou já o nome)
        
        try {
            // Verificar sempre se a sala é um ID numérico, independentemente da tabela
            if (is_numeric($basicResult['sala'])) {
                $salaQuery = "SELECT sala FROM salas WHERE id_sala = :sala_id";
                $salaStmt = $pdo->prepare($salaQuery);
                $salaStmt->bindParam(':sala_id', $basicResult['sala'], PDO::PARAM_INT);
                $salaStmt->execute();
                $salaDetail = $salaStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($salaDetail) {
                    $sala_nome = $salaDetail['sala'];
                }
            }
        } catch (PDOException $e) {
            error_log("Aviso: Erro ao buscar detalhes da sala: " . $e->getMessage());
            // Continua usando o valor original de sala
        }
        
        // Verificar se o solicitante é um ID numérico
        $solicitante_nome = $basicResult['solicitante']; // Valor padrão
        
        if (is_numeric($basicResult['solicitante'])) {
            // Primeiro tentar na tabela comunidade_escolar
            try {
                $userQuery = "SELECT nome_completo FROM comunidade_escolar WHERE id = :user_id";
                $userStmt = $pdo->prepare($userQuery);
                $userStmt->bindParam(':user_id', $basicResult['solicitante'], PDO::PARAM_INT);
                $userStmt->execute();
                $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($userData && !empty($userData['nome_completo'])) {
                    $solicitante_nome = $userData['nome_completo'];
                } else {
                    // Se não encontrar na comunidade_escolar, tentar na suporte_tecnico
                    $tecnicoQuery = "SELECT nome_completo FROM suporte_tecnico WHERE id = :user_id";
                    $tecnicoStmt = $pdo->prepare($tecnicoQuery);
                    $tecnicoStmt->bindParam(':user_id', $basicResult['solicitante'], PDO::PARAM_INT);
                    $tecnicoStmt->execute();
                    $tecnicoData = $tecnicoStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($tecnicoData && !empty($tecnicoData['nome_completo'])) {
                        $solicitante_nome = $tecnicoData['nome_completo'];
                    }
                }
            } catch (PDOException $e) {
                error_log("Aviso: Erro ao buscar nome do solicitante: " . $e->getMessage());
                // Continua usando o valor original
            }
        }
        
        // Agora que sabemos que o ticket existe, vamos obter os dados completos
        // Para o técnico, vamos usar a tabela suporte_tecnico que existe
        $query = "SELECT t.*, 
                  COALESCE(st.nome_completo, 'Não atribuído') AS tecnico_nome
                  FROM tickets t
                  LEFT JOIN suporte_tecnico st ON t.id_tecnico = st.id
                  WHERE t.id = :ticket_id";
        
        error_log("DEBUG: obterDetalhesTicket - Query completa: $query");
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':ticket_id', $ticket_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Se a consulta com JOINs falhar, use os dados básicos
        if (!$result) {
            error_log("DEBUG: obterDetalhesTicket - JOINs falharam, usando dados básicos do ticket");
            $basicResult['sala_nome'] = $sala_nome;
            $basicResult['solicitante_nome'] = $solicitante_nome;
            return $basicResult;
        }
        
        // Adicionar o nome da sala e solicitante ao resultado
        $result['sala_nome'] = $sala_nome;
        $result['solicitante_nome'] = $solicitante_nome;
        
        error_log("DEBUG: obterDetalhesTicket - Dados completos obtidos com sucesso");
        return $result;
    } catch (PDOException $e) {
        error_log("ERRO: obterDetalhesTicket - " . $e->getMessage());
        return false;
    }
}

/**
 * Obtém os componentes associados a um ticket
 */
if (!function_exists('obterComponentesTicket')) {
    function obterComponentesTicket($ticket_id, $pdo = null) {
        // Se não for fornecido o objeto PDO, usar a variável global
        if ($pdo === null) {
            global $pdo;
        }
        
        try {
            // Obter todos os componentes associados ao ticket
            $stmt = $pdo->prepare("SELECT * FROM ticket_componentes WHERE ticket_id = ?");
            $stmt->execute([$ticket_id]);
            $componentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Mapeamento de tipos de equipamento para suas tabelas e campos
            $mapeamento = [
                'computador' => ['tabela' => 'computadores', 'campo_id' => 'COMP_ID', 'campo_ref' => 'COMP_REF'],
                'rato' => ['tabela' => 'ratos', 'campo_id' => 'RATOS_ID', 'campo_ref' => 'RATOS_REF'],
                'teclado' => ['tabela' => 'teclados', 'campo_id' => 'TECLADO_ID', 'campo_ref' => 'TECLADO_REF'],
                'monitor' => ['tabela' => 'monitor', 'campo_id' => 'MONITOR_ID', 'campo_ref' => 'MONITOR_REF'],
                'cooler' => ['tabela' => 'cooler', 'campo_id' => 'COO_ID', 'campo_ref' => 'COO_REF'],
                'disco' => ['tabela' => 'disco', 'campo_id' => 'DISCO_ID', 'campo_ref' => 'DISCO_REF'],
                'fonte' => ['tabela' => 'fonte', 'campo_id' => 'FONTE_ID', 'campo_ref' => 'FONTE_REF'],
                'memoriaram' => ['tabela' => 'memoriaram', 'campo_id' => 'MR_ID', 'campo_ref' => 'MR_REF'],
                'placaderede' => ['tabela' => 'placaderede', 'campo_id' => 'PR_ID', 'campo_ref' => 'PR_REF'],
                'placagrafica' => ['tabela' => 'placagrafica', 'campo_id' => 'PG_ID', 'campo_ref' => 'PG_REF'],
                'placamae' => ['tabela' => 'placamae', 'campo_id' => 'PM_ID', 'campo_ref' => 'PM_REF'],
                'processador' => ['tabela' => 'processador', 'campo_id' => 'PROC_ID', 'campo_ref' => 'PROC_REF'],
                'caixa' => ['tabela' => 'caixa', 'campo_id' => 'CAIXA_ID', 'campo_ref' => 'CAIXA_REF'],
                'drivedvd' => ['tabela' => 'drivedvd', 'campo_id' => 'DRVD_ID', 'campo_ref' => 'DRVD_REF'],
                'ventoinha' => ['tabela' => 'ventoinha', 'campo_id' => 'VENT_ID', 'campo_ref' => 'VENT_REF']
            ];
            
            // Para cada componente, obter informações detalhadas se necessário
            foreach ($componentes as &$componente) {
                // Verificar se ref_equipamento é um ID numérico
                if (is_numeric($componente['ref_equipamento']) || strpos($componente['ref_equipamento'], 'COMP_') === 0) {
                    $equipamento_tipo = strtolower($componente['equipamento']);
                    $ref_id = $componente['ref_equipamento'];
                    
                    // Caso especial para computadores
                    if ($equipamento_tipo == 'computador') {
                        try {
                            $query = "SELECT COMP_REF FROM computadores WHERE COMP_ID = ?";
                            $stmt_ref = $pdo->prepare($query);
                            $stmt_ref->execute([$ref_id]);
                            $result = $stmt_ref->fetch(PDO::FETCH_ASSOC);
                            
                            if ($result && !empty($result['COMP_REF'])) {
                                $componente['ref_equipamento_texto'] = $result['COMP_REF'];
                            }
                        } catch (Exception $e) {
                            // Em caso de erro, manteremos o ID original
                            error_log("Erro ao buscar referência de computador: " . $e->getMessage());
                        }
                    } else {
                        // Para outros tipos de componentes
                        // Percorrer o mapeamento e procurar uma correspondência
                        foreach ($mapeamento as $tipo => $info) {
                            if (strpos($equipamento_tipo, $tipo) !== false) {
                                // Verificar se a tabela existe
                                $checkTable = $pdo->query("SHOW TABLES LIKE '{$info['tabela']}'");
                                if ($checkTable->rowCount() > 0) {
                                    // Verificar se a coluna de referência existe
                                    $checkColumn = $pdo->query("SHOW COLUMNS FROM {$info['tabela']} LIKE '{$info['campo_ref']}'");
                                    if ($checkColumn->rowCount() > 0) {
                                        // Fazer a consulta usando o campo ID e buscando o campo REF
                                        $query = "SELECT {$info['campo_ref']} FROM {$info['tabela']} WHERE {$info['campo_id']} = ?";
                                        $stmt_ref = $pdo->prepare($query);
                                        $stmt_ref->execute([$ref_id]);
                                        $result = $stmt_ref->fetch(PDO::FETCH_ASSOC);
                                        
                                        if ($result && !empty($result[$info['campo_ref']])) {
                                            // Armazenar a referência textual
                                            $componente['ref_equipamento_texto'] = $result[$info['campo_ref']];
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                
                // Se não encontrou nenhuma referência, usar o valor original
                if (!isset($componente['ref_equipamento_texto'])) {
                    $componente['ref_equipamento_texto'] = $componente['ref_equipamento'];
                }
            }
            
            return $componentes;
        } catch (Exception $e) {
            error_log('Erro ao obter componentes do ticket: ' . $e->getMessage());
            return [];
        }
    }
}

/**
 * Adiciona um comentário ao ticket
 */
if (!function_exists('adicionarComentarioTicket')) {
    function adicionarComentarioTicket($pdo, $ticket_id, $tecnico_id, $comentario) {
        $sql = "INSERT INTO ticket_comentarios (ticket_id, tecnico_id, comentario, data_comentario) 
                VALUES (?, ?, ?, NOW())";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$ticket_id, $tecnico_id, $comentario]);
            
            // Atualizar a data de última atualização do ticket
            $update = $pdo->prepare("UPDATE tickets SET ultima_atualizacao = NOW() WHERE id = ?");
            $update->execute([$ticket_id]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao adicionar comentário: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Atualiza o status de um ticket
 */
if (!function_exists('atualizarStatusTicket')) {
    function atualizarStatusTicket($pdo, $ticket_id, $novo_status, $tecnico_id, $comentario = '') {
        // Primeiro obtém o status atual e dados do ticket
        $stmt = $pdo->prepare("SELECT t.*, u.id as solicitante_id 
                               FROM tickets t 
                               LEFT JOIN comunidade_escolar u ON t.id_usuario = u.id 
                               WHERE t.id = ?");
        $stmt->execute([$ticket_id]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ticket) {
            error_log("Ticket não encontrado: ID " . $ticket_id);
            return false;
        }
        
        $status_anterior = $ticket['status'];
        
        // Se o status não mudou, não faz nada
        if ($status_anterior === $novo_status) {
            return true;
        }
        
        // Atualiza o status
        $sql = "UPDATE tickets SET status = ?, ultima_atualizacao = NOW() WHERE id = ?";
        
        try {
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$novo_status, $ticket_id]);
            
            if ($result) {
                // Registra no histórico a mudança de status
                $descricao = !empty($comentario) ? $comentario : "Status alterado de '{$status_anterior}' para '{$novo_status}'";
                registrarHistoricoTicket($pdo, $ticket_id, $tecnico_id, 'status_alterado', $descricao, $status_anterior, $novo_status);
                
                // Se o ticket foi aberto por um membro da comunidade escolar, enviar email de notificação
                if (isset($ticket['id_usuario']) && is_numeric($ticket['id_usuario'])) {
                    // Verificar se o usuário é da comunidade escolar
                    $stmt = $pdo->prepare("SELECT * FROM comunidade_escolar WHERE id = ?");
                    $stmt->execute([$ticket['id_usuario']]);
                    $solicitante = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Se o solicitante for encontrado e tiver email, enviar notificação
                    if ($solicitante && isset($solicitante['email_institucional']) && !empty($solicitante['email_institucional'])) {
                        // Carregar as funções de email
                        require_once __DIR__ . '/../../includes/email_funcoes.php';
                        
                        // Enviar email de notificação
                        enviarNotificacaoAtualizacaoTicket($pdo, $ticket, $solicitante, $status_anterior, $novo_status, $comentario);
                    }
                }
                
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar status do ticket: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Registra uma ação no histórico do ticket
 */
if (!function_exists('registrarHistoricoTicket')) {
    function registrarHistoricoTicket($pdo, $ticket_id, $tecnico_id, $acao, $descricao, $status_anterior = null, $status_novo = null) {
        $sql = "INSERT INTO ticket_historico (ticket_id, tecnico_id, acao, descricao, status_anterior, status_novo, data_acao)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$ticket_id, $tecnico_id, $acao, $descricao, $status_anterior, $status_novo]);
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao registrar histórico: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Obtém o histórico de um ticket
 */
if (!function_exists('obterHistoricoTicket')) {
    function obterHistoricoTicket($pdo, $ticket_id) {
        $sql = "SELECT h.*, 
                      CASE 
                          WHEN h.tecnico_id IS NULL THEN 'Sistema' 
                          ELSE COALESCE((SELECT nome_completo FROM suporte_tecnico WHERE id = h.tecnico_id), 'Técnico') 
                      END AS nome_tecnico
               FROM ticket_historico h
               WHERE h.ticket_id = ?
               ORDER BY h.data_acao DESC";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$ticket_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao obter histórico do ticket: " . $e->getMessage());
            return [];
        }
    }
}

/**
 * Marcar componente como "Em Manutenção"
 */
function marcarComponenteEmManutencao($pdo, $ticket_id, $componente_id, $tecnico_id, $sala = null) {
    try {
        // Primeiro, obter informações do componente
        $stmt = $pdo->prepare("SELECT * FROM ticket_componentes WHERE id = ?");
        $stmt->execute([$componente_id]);
        $componente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$componente) {
            error_log("Componente não encontrado: ID " . $componente_id);
            return false;
        }
        
        $equipamento_tipo = strtolower($componente['equipamento']);
        $ref_id = $componente['ref_equipamento'];
        
        // Mapeamento de tipos de equipamento para suas tabelas e campos
        $mapeamento = [
            'computador' => ['tabela' => 'computadores', 'campo_id' => 'COMP_ID', 'campo_estado' => 'COMP_ESTADO', 'valor_manutencao' => '3'],
            'rato' => ['tabela' => 'ratos', 'campo_id' => 'RATOS_ID', 'campo_estado' => 'RATOS_ESTADO', 'valor_manutencao' => '3'],
            'teclado' => ['tabela' => 'teclados', 'campo_id' => 'TECLADO_ID', 'campo_estado' => 'TECLADO_ESTADO', 'valor_manutencao' => '3'],
            'monitor' => ['tabela' => 'monitor', 'campo_id' => 'MONITOR_ID', 'campo_estado' => 'MONITOR_ESTADO', 'valor_manutencao' => '3'],
            'cooler' => ['tabela' => 'cooler', 'campo_id' => 'COO_ID', 'campo_estado' => 'COO_ESTADO', 'valor_manutencao' => '3'],
            'disco' => ['tabela' => 'disco', 'campo_id' => 'DISCO_ID', 'campo_estado' => 'DISCO_ESTADO', 'valor_manutencao' => '3'],
            'fonte' => ['tabela' => 'fonte', 'campo_id' => 'FONTE_ID', 'campo_estado' => 'FONTE_ESTADO', 'valor_manutencao' => '3'],
            'memoriaram' => ['tabela' => 'memoriaram', 'campo_id' => 'MR_ID', 'campo_estado' => 'MR_ESTADO', 'valor_manutencao' => '3'],
            'placaderede' => ['tabela' => 'placaderede', 'campo_id' => 'PR_ID', 'campo_estado' => 'PR_ESTADO', 'valor_manutencao' => '3'],
            'placagrafica' => ['tabela' => 'placagrafica', 'campo_id' => 'PG_ID', 'campo_estado' => 'PG_ESTADO', 'valor_manutencao' => '3'],
            'placamae' => ['tabela' => 'placamae', 'campo_id' => 'PM_ID', 'campo_estado' => 'PM_ESTADO', 'valor_manutencao' => '3'],
            'processador' => ['tabela' => 'processador', 'campo_id' => 'PROC_ID', 'campo_estado' => 'PROC_ESTADO', 'valor_manutencao' => '3'],
            'caixa' => ['tabela' => 'caixa', 'campo_id' => 'CAIXA_ID', 'campo_estado' => 'CAIXA_ESTADO', 'valor_manutencao' => '3'],
            'drivedvd' => ['tabela' => 'drivedvd', 'campo_id' => 'DRVD_ID', 'campo_estado' => 'DRVD_ESTADO', 'valor_manutencao' => '3'],
            'ventoinha' => ['tabela' => 'ventoinha', 'campo_id' => 'VENT_ID', 'campo_estado' => 'VENT_ESTADO', 'valor_manutencao' => '3']
        ];
        
        // Encontrar a tabela e campos corretos para o tipo de equipamento
        $tabela_info = null;
        foreach ($mapeamento as $tipo => $info) {
            if (strpos($equipamento_tipo, $tipo) !== false) {
                $tabela_info = $info;
                break;
            }
        }
        
        if ($tabela_info === null) {
            error_log("Tipo de equipamento não reconhecido: " . $equipamento_tipo);
            return false;
        }
        
        // Verificar se a tabela existe
        $check_table = $pdo->query("SHOW TABLES LIKE '{$tabela_info['tabela']}'");
        if ($check_table->rowCount() == 0) {
            error_log("Tabela não encontrada: " . $tabela_info['tabela']);
            return false;
        }
        
        // Determinar o nome do campo de sala para esta tabela
        $campo_sala = null;
        switch ($tabela_info['tabela']) {
            case 'computadores':
                $campo_sala = 'COMP_SALA';
                break;
            case 'ratos':
                $campo_sala = 'RATOS_SALA';
                break;
            case 'teclados':
                $campo_sala = 'TECLADO_SALA';
                break;
            case 'monitor':
                $campo_sala = 'MONITOR_SALA';
                break;
            case 'cooler':
                $campo_sala = 'COO_SALA';
                break;
            case 'disco':
                $campo_sala = 'DISCO_SALA';
                break;
            case 'fonte':
                $campo_sala = 'FONTE_SALA';
                break;
            case 'memoriaram':
                $campo_sala = 'MR_SALA';
                break;
            case 'placaderede':
                $campo_sala = 'PR_SALA';
                break;
            case 'placagrafica':
                $campo_sala = 'PG_SALA';
                break;
            case 'placamae':
                $campo_sala = 'PM_SALA';
                break;
            case 'processador':
                $campo_sala = 'PROC_SALA';
                break;
            case 'caixa':
                $campo_sala = 'CAIXA_SALA';
                break;
            case 'drivedvd':
                $campo_sala = 'DRVD_SALA';
                break;
            case 'ventoinha':
                $campo_sala = 'VENT_SALA';
                break;
            default:
                error_log("Campo de sala não definido para tabela: " . $tabela_info['tabela']);
                break;
        }
        
        // Atualizar o estado do componente e limpar o campo sala
        $sql = "UPDATE {$tabela_info['tabela']} SET {$tabela_info['campo_estado']} = ?";
        $params = [$tabela_info['valor_manutencao']];
        
        // Se o campo sala for encontrado, limpar ele também
        if ($campo_sala !== null) {
            $sql .= ", {$campo_sala} = ''";
        }
        
        $sql .= " WHERE {$tabela_info['campo_id']} = ?";
        $params[] = $ref_id;
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);
        
        // Se for um computador, também atualizar o estado de todos os componentes associados para manutenção
        if ($equipamento_tipo === 'computador' && $result) {
            // Buscar dados do computador para obter os componentes associados
            $componentesInfo = [
                'RATO' => ['tabela' => 'ratos', 'id' => 'RATOS_ID', 'estado' => 'RATOS_ESTADO', 'sala' => 'RATOS_SALA'],
                'TECLADO' => ['tabela' => 'teclados', 'id' => 'TECLADO_ID', 'estado' => 'TECLADO_ESTADO', 'sala' => 'TECLADO_SALA'],
                'MONITOR' => ['tabela' => 'monitor', 'id' => 'MONITOR_ID', 'estado' => 'MONITOR_ESTADO', 'sala' => 'MONITOR_SALA'],
                'PM' => ['tabela' => 'placamae', 'id' => 'PM_ID', 'estado' => 'PM_ESTADO', 'sala' => 'PM_SALA'],
                'PROC' => ['tabela' => 'processador', 'id' => 'PROC_ID', 'estado' => 'PROC_ESTADO', 'sala' => 'PROC_SALA'],
                'COOLER' => ['tabela' => 'cooler', 'id' => 'COO_ID', 'estado' => 'COO_ESTADO', 'sala' => 'COO_SALA'],
                'PG' => ['tabela' => 'placagrafica', 'id' => 'PG_ID', 'estado' => 'PG_ESTADO', 'sala' => 'PG_SALA'],
                'DISCO' => ['tabela' => 'disco', 'id' => 'DISCO_ID', 'estado' => 'DISCO_ESTADO', 'sala' => 'DISCO_SALA'],
                'FONTE' => ['tabela' => 'fonte', 'id' => 'FONTE_ID', 'estado' => 'FONTE_ESTADO', 'sala' => 'FONTE_SALA'],
                'MEMORIAR' => ['tabela' => 'memoriaram', 'id' => 'MR_ID', 'estado' => 'MR_ESTADO', 'sala' => 'MR_SALA'],
                'DRIVEDVD' => ['tabela' => 'drivedvd', 'id' => 'DRVD_ID', 'estado' => 'DRVD_ESTADO', 'sala' => 'DRVD_SALA'],
                'PR' => ['tabela' => 'placaderede', 'id' => 'PR_ID', 'estado' => 'PR_ESTADO', 'sala' => 'PR_SALA'],
                'VENTOINHA' => ['tabela' => 'ventoinha', 'id' => 'VENT_ID', 'estado' => 'VENT_ESTADO', 'sala' => 'VENT_SALA'],
                'CAIXA' => ['tabela' => 'caixa', 'id' => 'CAIXA_ID', 'estado' => 'CAIXA_ESTADO', 'sala' => 'CAIXA_SALA']
            ];
            
            try {
                // Buscar o computador para obter seus componentes
                $stmtComp = $pdo->prepare("SELECT * FROM computadores WHERE COMP_ID = ?");
                $stmtComp->execute([$ref_id]);
                $computador = $stmtComp->fetch(PDO::FETCH_ASSOC);
                
                if ($computador) {
                    // Para cada componente associado, atualizá-lo para o estado de "Em Manutenção" (3)
                    foreach ($componentesInfo as $tipo => $info) {
                        $componenteId = $computador["COMP_$tipo"];
                        
                        if (!empty($componenteId)) {
                            // Preparar SQL para atualizar componente
                            $updateSql = "UPDATE {$info['tabela']} SET {$info['estado']} = 3";
                            $updateParams = [];
                            
                            // Se o campo sala for encontrado, adicionar ao SQL para o componente também
                            if ($sala !== null && isset($info['sala'])) {
                                $updateSql .= ", {$info['sala']} = ''";
                            }
                            
                            $updateSql .= " WHERE {$info['id']} = ?";
                            $updateParams[] = $componenteId;
                            
                            $updateStmt = $pdo->prepare($updateSql);
                            $updateStmt->execute($updateParams);
                            error_log("Componente $tipo (ID: $componenteId) do computador $ref_id atualizado para estado Em Manutenção");
                        }
                    }
                    
                    // Adicionar ao comentário e histórico que os componentes também foram colocados em manutenção
                    adicionarComentarioTicket($pdo, $ticket_id, $tecnico_id, "Todos os componentes do computador foram colocados em manutenção");
                }
            } catch (PDOException $e) {
                error_log("Erro ao atualizar componentes do computador para Em Manutenção: " . $e->getMessage());
                // Não falhar completamente se apenas a atualização dos componentes falhar
            }
        }
        
        if ($result) {
            // Registrar no histórico
            $descricao = "Componente marcado como 'Em Manutenção'";
            registrarHistoricoTicket($pdo, $ticket_id, $tecnico_id, 'componente_manutencao', $descricao);
            
            // Adicionar comentário do sistema
            adicionarComentarioTicket($pdo, $ticket_id, $tecnico_id, "Componente posto em manutenção");
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Erro ao marcar componente em manutenção: " . $e->getMessage());
        return false;
    }
}

/**
 * Marcar componente como "Sem Reparo"
 */
function marcarComponenteSemReparo($pdo, $ticket_id, $componente_id, $tecnico_id, $motivo) {
    try {
        // Primeiro, obter informações do componente
        $stmt = $pdo->prepare("SELECT * FROM ticket_componentes WHERE id = ?");
        $stmt->execute([$componente_id]);
        $componente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$componente) {
            error_log("Componente não encontrado: ID " . $componente_id);
            return false;
        }
        
        $equipamento_tipo = strtolower($componente['equipamento']);
        $ref_id = $componente['ref_equipamento'];
        
        // Mapeamento de tipos de equipamento para suas tabelas e campos
        $mapeamento = [
            'computador' => ['tabela' => 'computadores', 'campo_id' => 'COMP_ID', 'campo_estado' => 'COMP_ESTADO', 'valor_sem_reparo' => '4'],
            'rato' => ['tabela' => 'ratos', 'campo_id' => 'RATOS_ID', 'campo_estado' => 'RATOS_ESTADO', 'valor_sem_reparo' => '4'],
            'teclado' => ['tabela' => 'teclados', 'campo_id' => 'TECLADO_ID', 'campo_estado' => 'TECLADO_ESTADO', 'valor_sem_reparo' => '4'],
            'monitor' => ['tabela' => 'monitor', 'campo_id' => 'MONITOR_ID', 'campo_estado' => 'MONITOR_ESTADO', 'valor_sem_reparo' => '4'],
            'cooler' => ['tabela' => 'cooler', 'campo_id' => 'COO_ID', 'campo_estado' => 'COO_ESTADO', 'valor_sem_reparo' => '4'],
            'disco' => ['tabela' => 'disco', 'campo_id' => 'DISCO_ID', 'campo_estado' => 'DISCO_ESTADO', 'valor_sem_reparo' => '4'],
            'fonte' => ['tabela' => 'fonte', 'campo_id' => 'FONTE_ID', 'campo_estado' => 'FONTE_ESTADO', 'valor_sem_reparo' => '4'],
            'memoriaram' => ['tabela' => 'memoriaram', 'campo_id' => 'MR_ID', 'campo_estado' => 'MR_ESTADO', 'valor_sem_reparo' => '4'],
            'placaderede' => ['tabela' => 'placaderede', 'campo_id' => 'PR_ID', 'campo_estado' => 'PR_ESTADO', 'valor_sem_reparo' => '4'],
            'placagrafica' => ['tabela' => 'placagrafica', 'campo_id' => 'PG_ID', 'campo_estado' => 'PG_ESTADO', 'valor_sem_reparo' => '4'],
            'placamae' => ['tabela' => 'placamae', 'campo_id' => 'PM_ID', 'campo_estado' => 'PM_ESTADO', 'valor_sem_reparo' => '4'],
            'processador' => ['tabela' => 'processador', 'campo_id' => 'PROC_ID', 'campo_estado' => 'PROC_ESTADO', 'valor_sem_reparo' => '4'],
            'caixa' => ['tabela' => 'caixa', 'campo_id' => 'CAIXA_ID', 'campo_estado' => 'CAIXA_ESTADO', 'valor_sem_reparo' => '4'],
            'drivedvd' => ['tabela' => 'drivedvd', 'campo_id' => 'DRVD_ID', 'campo_estado' => 'DRVD_ESTADO', 'valor_sem_reparo' => '4'],
            'ventoinha' => ['tabela' => 'ventoinha', 'campo_id' => 'VENT_ID', 'campo_estado' => 'VENT_ESTADO', 'valor_sem_reparo' => '4']
        ];
        
        // Encontrar a tabela e campos corretos para o tipo de equipamento
        $tabela_info = null;
        foreach ($mapeamento as $tipo => $info) {
            if (strpos($equipamento_tipo, $tipo) !== false) {
                $tabela_info = $info;
                break;
            }
        }
        
        if ($tabela_info === null) {
            error_log("Tipo de equipamento não reconhecido: " . $equipamento_tipo);
            return false;
        }
        
        // Verificar se a tabela existe
        $check_table = $pdo->query("SHOW TABLES LIKE '{$tabela_info['tabela']}'");
        if ($check_table->rowCount() == 0) {
            error_log("Tabela não encontrada: " . $tabela_info['tabela']);
            return false;
        }
        
        // Atualizar o estado do componente
        $sql = "UPDATE {$tabela_info['tabela']} SET {$tabela_info['campo_estado']} = ? WHERE {$tabela_info['campo_id']} = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$tabela_info['valor_sem_reparo'], $ref_id]);
        
        if ($result) {
            // Registrar no histórico
            $descricao = "Componente marcado como 'Sem Reparo'. Motivo: " . $motivo;
            registrarHistoricoTicket($pdo, $ticket_id, $tecnico_id, 'componente_sem_reparo', $descricao);
            
            // Adicionar comentário do sistema
            adicionarComentarioTicket($pdo, $ticket_id, $tecnico_id, "Componente marcado como sem possibilidade de reparo. Motivo: " . $motivo);
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Erro ao marcar componente sem reparo: " . $e->getMessage());
        return false;
    }
}

/**
 * Marcar componente como "Em Uso" (resolvido)
 */
function marcarComponenteEmUso($pdo, $ticket_id, $componente_id, $tecnico_id, $sala = null) {
    try {
        // Primeiro, obter informações do componente
        $stmt = $pdo->prepare("SELECT * FROM ticket_componentes WHERE id = ?");
        $stmt->execute([$componente_id]);
        $componente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$componente) {
            error_log("Componente não encontrado: ID " . $componente_id);
            return false;
        }
        
        $equipamento_tipo = strtolower($componente['equipamento']);
        $ref_id = $componente['ref_equipamento'];
        
        // Mapeamento de tipos de equipamento para suas tabelas e campos
        $mapeamento = [
            'computador' => ['tabela' => 'computadores', 'campo_id' => 'COMP_ID', 'campo_estado' => 'COMP_ESTADO', 'campo_sala' => 'COMP_SALA', 'valor_em_uso' => '2'],
            'rato' => ['tabela' => 'ratos', 'campo_id' => 'RATOS_ID', 'campo_estado' => 'RATOS_ESTADO', 'campo_sala' => 'RATOS_SALA', 'valor_em_uso' => '2'],
            'teclado' => ['tabela' => 'teclados', 'campo_id' => 'TECLADO_ID', 'campo_estado' => 'TECLADO_ESTADO', 'campo_sala' => 'TECLADO_SALA', 'valor_em_uso' => '2'],
            'monitor' => ['tabela' => 'monitor', 'campo_id' => 'MONITOR_ID', 'campo_estado' => 'MONITOR_ESTADO', 'campo_sala' => 'MONITOR_SALA', 'valor_em_uso' => '2'],
            'cooler' => ['tabela' => 'cooler', 'campo_id' => 'COO_ID', 'campo_estado' => 'COO_ESTADO', 'campo_sala' => 'COO_SALA', 'valor_em_uso' => '2'],
            'disco' => ['tabela' => 'disco', 'campo_id' => 'DISCO_ID', 'campo_estado' => 'DISCO_ESTADO', 'campo_sala' => 'DISCO_SALA', 'valor_em_uso' => '2'],
            'fonte' => ['tabela' => 'fonte', 'campo_id' => 'FONTE_ID', 'campo_estado' => 'FONTE_ESTADO', 'campo_sala' => 'FONTE_SALA', 'valor_em_uso' => '2'],
            'memoriaram' => ['tabela' => 'memoriaram', 'campo_id' => 'MR_ID', 'campo_estado' => 'MR_ESTADO', 'campo_sala' => 'MR_SALA', 'valor_em_uso' => '2'],
            'placaderede' => ['tabela' => 'placaderede', 'campo_id' => 'PR_ID', 'campo_estado' => 'PR_ESTADO', 'campo_sala' => 'PR_SALA', 'valor_em_uso' => '2'],
            'placagrafica' => ['tabela' => 'placagrafica', 'campo_id' => 'PG_ID', 'campo_estado' => 'PG_ESTADO', 'campo_sala' => 'PG_SALA', 'valor_em_uso' => '2'],
            'placamae' => ['tabela' => 'placamae', 'campo_id' => 'PM_ID', 'campo_estado' => 'PM_ESTADO', 'campo_sala' => 'PM_SALA', 'valor_em_uso' => '2'],
            'processador' => ['tabela' => 'processador', 'campo_id' => 'PROC_ID', 'campo_estado' => 'PROC_ESTADO', 'campo_sala' => 'PROC_SALA', 'valor_em_uso' => '2'],
            'caixa' => ['tabela' => 'caixa', 'campo_id' => 'CAIXA_ID', 'campo_estado' => 'CAIXA_ESTADO', 'campo_sala' => 'CAIXA_SALA', 'valor_em_uso' => '2'],
            'drivedvd' => ['tabela' => 'drivedvd', 'campo_id' => 'DRVD_ID', 'campo_estado' => 'DRVD_ESTADO', 'campo_sala' => 'DRVD_SALA', 'valor_em_uso' => '2'],
            'ventoinha' => ['tabela' => 'ventoinha', 'campo_id' => 'VENT_ID', 'campo_estado' => 'VENT_ESTADO', 'campo_sala' => 'VENT_SALA', 'valor_em_uso' => '2']
        ];
        
        // Encontrar a tabela e campos corretos para o tipo de equipamento
        $tabela_info = null;
        foreach ($mapeamento as $tipo => $info) {
            if (strpos($equipamento_tipo, $tipo) !== false) {
                $tabela_info = $info;
                break;
            }
        }
        
        if ($tabela_info === null) {
            error_log("Tipo de equipamento não reconhecido: " . $equipamento_tipo);
            return false;
        }
        
        // Verificar se a tabela existe
        $check_table = $pdo->query("SHOW TABLES LIKE '{$tabela_info['tabela']}'");
        if ($check_table->rowCount() == 0) {
            error_log("Tabela não encontrada: " . $tabela_info['tabela']);
            return false;
        }
        
        // Preparar a consulta SQL para atualizar o componente
        $sql = "UPDATE {$tabela_info['tabela']} SET {$tabela_info['campo_estado']} = ?";
        $params = [$tabela_info['valor_em_uso']];
        
        // Se a sala foi fornecida, adicionar ao SQL
        if ($sala !== null && isset($tabela_info['campo_sala'])) {
            $sql .= ", {$tabela_info['campo_sala']} = ?";
            $params[] = $sala;
        }
        
        $sql .= " WHERE {$tabela_info['campo_id']} = ?";
        $params[] = $ref_id;
        
        // Executar a atualização
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($params);
        
        // Se for um computador, também atualizar o estado de todos os componentes associados para "Em Uso"
        if ($equipamento_tipo === 'computador' && $result) {
            // Buscar dados do computador para obter os componentes associados
            $componentesInfo = [
                'RATO' => ['tabela' => 'ratos', 'id' => 'RATOS_ID', 'estado' => 'RATOS_ESTADO', 'sala' => 'RATOS_SALA'],
                'TECLADO' => ['tabela' => 'teclados', 'id' => 'TECLADO_ID', 'estado' => 'TECLADO_ESTADO', 'sala' => 'TECLADO_SALA'],
                'MONITOR' => ['tabela' => 'monitor', 'id' => 'MONITOR_ID', 'estado' => 'MONITOR_ESTADO', 'sala' => 'MONITOR_SALA'],
                'PM' => ['tabela' => 'placamae', 'id' => 'PM_ID', 'estado' => 'PM_ESTADO', 'sala' => 'PM_SALA'],
                'PROC' => ['tabela' => 'processador', 'id' => 'PROC_ID', 'estado' => 'PROC_ESTADO', 'sala' => 'PROC_SALA'],
                'COOLER' => ['tabela' => 'cooler', 'id' => 'COO_ID', 'estado' => 'COO_ESTADO', 'sala' => 'COO_SALA'],
                'PG' => ['tabela' => 'placagrafica', 'id' => 'PG_ID', 'estado' => 'PG_ESTADO', 'sala' => 'PG_SALA'],
                'DISCO' => ['tabela' => 'disco', 'id' => 'DISCO_ID', 'estado' => 'DISCO_ESTADO', 'sala' => 'DISCO_SALA'],
                'FONTE' => ['tabela' => 'fonte', 'id' => 'FONTE_ID', 'estado' => 'FONTE_ESTADO', 'sala' => 'FONTE_SALA'],
                'MEMORIAR' => ['tabela' => 'memoriaram', 'id' => 'MR_ID', 'estado' => 'MR_ESTADO', 'sala' => 'MR_SALA'],
                'DRIVEDVD' => ['tabela' => 'drivedvd', 'id' => 'DRVD_ID', 'estado' => 'DRVD_ESTADO', 'sala' => 'DRVD_SALA'],
                'PR' => ['tabela' => 'placaderede', 'id' => 'PR_ID', 'estado' => 'PR_ESTADO', 'sala' => 'PR_SALA'],
                'VENTOINHA' => ['tabela' => 'ventoinha', 'id' => 'VENT_ID', 'estado' => 'VENT_ESTADO', 'sala' => 'VENT_SALA'],
                'CAIXA' => ['tabela' => 'caixa', 'id' => 'CAIXA_ID', 'estado' => 'CAIXA_ESTADO', 'sala' => 'CAIXA_SALA']
            ];
            
            try {
                // Buscar o computador para obter seus componentes
                $stmtComp = $pdo->prepare("SELECT * FROM computadores WHERE COMP_ID = ?");
                $stmtComp->execute([$ref_id]);
                $computador = $stmtComp->fetch(PDO::FETCH_ASSOC);
                
                if ($computador) {
                    // Para cada componente associado, atualizá-lo para o estado de "Em Uso" (2)
                    foreach ($componentesInfo as $tipo => $info) {
                        $componenteId = $computador["COMP_$tipo"];
                        
                        if (!empty($componenteId)) {
                            // Preparar SQL para atualizar componente
                            $updateSql = "UPDATE {$info['tabela']} SET {$info['estado']} = 2";
                            $updateParams = [];
                            
                            // Se a sala foi fornecida, adicionar ao SQL para o componente também
                            if ($sala !== null && isset($info['sala'])) {
                                $updateSql .= ", {$info['sala']} = ?";
                                $updateParams[] = $sala;
                            }
                            
                            $updateSql .= " WHERE {$info['id']} = ?";
                            $updateParams[] = $componenteId;
                            
                            $updateStmt = $pdo->prepare($updateSql);
                            $updateStmt->execute($updateParams);
                            error_log("Componente $tipo (ID: $componenteId) do computador $ref_id atualizado para estado Em Uso");
                        }
                    }
                    
                    // Adicionar ao comentário e histórico que os componentes também foram colocados em uso
                    adicionarComentarioTicket($pdo, $ticket_id, $tecnico_id, "Todos os componentes do computador foram colocados em uso" . ($sala ? " na sala $sala" : ""));
                }
            } catch (PDOException $e) {
                error_log("Erro ao atualizar componentes do computador para Em Uso: " . $e->getMessage());
                // Não falhar completamente se apenas a atualização dos componentes falhar
            }
        }
        
        if ($result) {
            // Registrar no histórico
            $descricao = "Componente marcado como 'Em Uso'" . ($sala ? " e retornado para a sala {$sala}" : "");
            registrarHistoricoTicket($pdo, $ticket_id, $tecnico_id, 'componente_em_uso', $descricao);
            
            // Adicionar comentário do sistema
            adicionarComentarioTicket($pdo, $ticket_id, $tecnico_id, "Componente retornado ao estado de uso normal" . ($sala ? " na sala {$sala}" : ""));
            
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Erro ao marcar componente em uso: " . $e->getMessage());
        return false;
    }
}

/**
 * Resolver ticket (marcar como resolvido)
 */
function resolverTicket($pdo, $ticket_id, $tecnico_id, $solucao) {
    // Atualizar status do ticket e incluir a solução como comentário na notificação
    $resultado = atualizarStatusTicket($pdo, $ticket_id, 'resolvido', $tecnico_id, "SOLUÇÃO: " . $solucao);
    
    if ($resultado) {
        // Adicionar comentário com a solução também no sistema
        adicionarComentarioTicket($pdo, $ticket_id, $tecnico_id, "SOLUÇÃO: " . $solucao);
        
        // Registrar no histórico
        registrarHistoricoTicket($pdo, $ticket_id, $tecnico_id, 'ticket_resolvido', "Ticket resolvido. Solução: " . $solucao);
        
        return true;
    }
    
    return false;
}

/**
 * Obter comentários do ticket
 */
if (!function_exists('obterComentariosTicket')) {
    function obterComentariosTicket($pdo, $ticket_id) {
        $sql = "SELECT c.*, 
                      COALESCE((SELECT nome_completo FROM suporte_tecnico WHERE id = c.tecnico_id), 'Técnico') AS nome_autor,
                      COALESCE((SELECT foto_perfil FROM suporte_tecnico WHERE id = c.tecnico_id), 'default.jpg') AS foto_autor
               FROM ticket_comentarios c
               WHERE c.ticket_id = ?
               ORDER BY c.data_comentario ASC";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$ticket_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao obter comentários do ticket: " . $e->getMessage());
            return [];
        }
    }
}

/**
 * Agendar manutenção
 */
function agendarManutencao($pdo, $ticket_id, $tecnico_id, $data_agenda, $descricao) {
    // Registrar no histórico
    $descricao_historico = "Agendada manutenção para " . date('d/m/Y H:i', strtotime($data_agenda)) . ": " . $descricao;
    $resultado = registrarHistoricoTicket($pdo, $ticket_id, $tecnico_id, 'manutencao_agendada', $descricao_historico);
    
    if ($resultado) {
        // Adicionar comentário do sistema
        adicionarComentarioTicket($pdo, $ticket_id, $tecnico_id, $descricao_historico);
        return true;
    }
    
    return false;
}

/**
 * Obter todas as salas disponíveis
 */
function obterTodasSalas($pdo) {
    try {
        $stmt = $pdo->query("SELECT id_sala, sala FROM salas ORDER BY sala ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao obter salas: " . $e->getMessage());
        return [];
    }
} 