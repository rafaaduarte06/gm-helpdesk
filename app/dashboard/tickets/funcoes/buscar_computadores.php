<?php
// Iniciar a sessão para verificar autenticação
session_start();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['userId'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Incluir conexão com o banco de dados
require_once '../../../ligacao_base_dados.php';

// Verificar se o ID da sala foi informado
if (!isset($_GET['sala_id']) || empty($_GET['sala_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID da sala não informado']);
    exit;
}

$sala_id = $_GET['sala_id'];

try {
    // Log para debug
    error_log("Iniciando busca por computadores para sala ID: $sala_id");
    
    // Obter o nome da sala
    $stmtSala = $pdo->prepare("SELECT sala FROM salas WHERE id_sala = ?");
    $stmtSala->execute([$sala_id]);
    $sala = $stmtSala->fetch(PDO::FETCH_ASSOC);
    
    if (!$sala) {
        error_log("Sala não encontrada com ID: $sala_id");
        throw new Exception('Sala não encontrada');
    }
    
    $sala_nome = $sala['sala'];
    error_log("Nome da sala encontrado: '$sala_nome' para ID: $sala_id");
    
    // Primeiro buscar todos os computadores da sala para verificar se existem
    $stmt_todos = $pdo->prepare("SELECT COUNT(*) as total FROM computadores WHERE COMP_SALA = ?");
    $stmt_todos->execute([$sala_nome]);
    $total_computadores = $stmt_todos->fetch(PDO::FETCH_ASSOC)['total'];
    error_log("Total de computadores na sala '$sala_nome': $total_computadores");
    
    // Buscar todos os computadores da sala primeiro
    $sql_computadores = "
        SELECT c.COMP_ID as id, 
               c.COMP_REF as ref, 
               c.COMP_ESTADO as estado
        FROM computadores c
        WHERE c.COMP_SALA = ?
        ORDER BY c.COMP_REF
    ";
    
    error_log("Buscando todos os computadores da sala: " . $sala_nome);
    $stmt_comp = $pdo->prepare($sql_computadores);
    $stmt_comp->execute([$sala_nome]);
    $todos_computadores = $stmt_comp->fetchAll(PDO::FETCH_ASSOC);
    error_log("Encontrados " . count($todos_computadores) . " computadores na sala");
    
    // Buscar IDs de computadores que já estão em tickets ativos
    // Convertemos o collation explicitamente para evitar o erro de mismatch
    $sql_tickets = "
        SELECT CONVERT(tc.ref_equipamento USING utf8mb4) COLLATE utf8mb4_general_ci as comp_id
        FROM tickets t 
        JOIN ticket_componentes tc ON t.id = tc.ticket_id 
        WHERE tc.equipamento = 'computador' 
        AND t.status IN ('aberto', 'em_andamento')
    ";
    
    error_log("Buscando computadores em tickets ativos");
    $stmt_tickets = $pdo->prepare($sql_tickets);
    $stmt_tickets->execute();
    $computadores_em_tickets = $stmt_tickets->fetchAll(PDO::FETCH_COLUMN);
    
    error_log("Computadores em tickets ativos: " . json_encode($computadores_em_tickets));
    
    // Filtramos os computadores manualmente para evitar problemas de collation
    $computadores = [];
    foreach ($todos_computadores as $comp) {
        if (!in_array($comp['id'], $computadores_em_tickets)) {
            $computadores[] = $comp;
            error_log("Adicionando computador disponível: ID=" . $comp['id'] . ", REF=" . $comp['ref']);
        } else {
            error_log("Ignorando computador em ticket ativo: ID=" . $comp['id'] . ", REF=" . $comp['ref']);
        }
    }
    
    // Debug information
    if (count($computadores) == 0) {
        error_log("Nenhum computador disponível encontrado para a sala: '$sala_nome' (ID: $sala_id)");
    } else {
        error_log("Encontrados " . count($computadores) . " computadores disponíveis para a sala: '$sala_nome' (ID: $sala_id)");
        foreach ($computadores as $comp) {
            error_log("Computador disponível: ID=" . $comp['id'] . ", REF=" . $comp['ref'] . ", ESTADO=" . $comp['estado']);
        }
    }
    
    // Formatar os dados para o retorno
    $resultado = [];
    foreach ($computadores as $computador) {
        // Obter texto do estado
        $estado_texto = '';
        switch ($computador['estado']) {
            case '1':
                $estado_texto = '';
                break;
            case '2':
                $estado_texto = ''; 
                break;
            case '3':
                $estado_texto = '';
                break;
            case '4':
                $estado_texto = '';
                break;
            default:
                $estado_texto = '';
        }
        
        $resultado[] = [
            'id' => $computador['id'],
            'texto' => $computador['ref'] . ($estado_texto ? ' (' . $estado_texto . ')' : '')
        ];
    }
    
    // Retornar os dados como JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $resultado
    ]);
    
} catch (Exception $e) {
    error_log("Erro na busca de computadores: " . $e->getMessage());
    // Retornar mensagem de erro
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar computadores: ' . $e->getMessage()
    ]);
}
?> 