<?php
// Iniciar sessão para verificar autenticação
session_start();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['userId'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Incluir conexão com o banco de dados
require_once '../../../ligacao_base_dados.php';

// Verificar se o tipo do componente e o ID da sala foram informados
if (!isset($_GET['tipo']) || empty($_GET['tipo']) || !isset($_GET['sala_id']) || empty($_GET['sala_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Tipo de componente ou ID da sala não informado']);
    exit;
}

$tipo = $_GET['tipo'];
$sala_id = $_GET['sala_id'];

// Mapear o tipo de componente para a tabela e campos corretos no banco de dados
$tabelaMap = [
    'rato' => ['tabela' => 'ratos', 'id' => 'RATOS_ID', 'ref' => 'RATOS_REF', 'estado' => 'RATOS_ESTADO', 'sala' => 'RATOS_SALA'],
    'teclado' => ['tabela' => 'teclados', 'id' => 'TECLADO_ID', 'ref' => 'TECLADO_REF', 'estado' => 'TECLADO_ESTADO', 'sala' => 'TECLADO_SALA'],
    'monitor' => ['tabela' => 'monitor', 'id' => 'MONITOR_ID', 'ref' => 'MONITOR_REF', 'estado' => 'MONITOR_ESTADO', 'sala' => 'MONITOR_SALA'],
    'placamae' => ['tabela' => 'motherboard', 'id' => 'MB_ID', 'ref' => 'MB_REF', 'estado' => 'MB_ESTADO', 'sala' => 'MB_SALA'],
    'processador' => ['tabela' => 'processador', 'id' => 'PROC_ID', 'ref' => 'PROC_REF', 'estado' => 'PROC_ESTADO', 'sala' => 'PROC_SALA'],
    'cooler' => ['tabela' => 'cooler', 'id' => 'COOLER_ID', 'ref' => 'COOLER_REF', 'estado' => 'COOLER_ESTADO', 'sala' => 'COOLER_SALA'],
    'placagrafica' => ['tabela' => 'placagrafica', 'id' => 'PG_ID', 'ref' => 'PG_REF', 'estado' => 'PG_ESTADO', 'sala' => 'PG_SALA'],
    'disco' => ['tabela' => 'disco', 'id' => 'DISCO_ID', 'ref' => 'DISCO_REF', 'estado' => 'DISCO_ESTADO', 'sala' => 'DISCO_SALA'],
    'fonte' => ['tabela' => 'fonte', 'id' => 'FONTE_ID', 'ref' => 'FONTE_REF', 'estado' => 'FONTE_ESTADO', 'sala' => 'FONTE_SALA'],
    'memoriaram' => ['tabela' => 'memoriaram', 'id' => 'RAM_ID', 'ref' => 'RAM_REF', 'estado' => 'RAM_ESTADO', 'sala' => 'RAM_SALA'],
    'drivedvd' => ['tabela' => 'drivedvd', 'id' => 'DVD_ID', 'ref' => 'DVD_REF', 'estado' => 'DVD_ESTADO', 'sala' => 'DVD_SALA'],
    'placarede' => ['tabela' => 'placaderede', 'id' => 'PR_ID', 'ref' => 'PR_REF', 'estado' => 'PR_ESTADO', 'sala' => 'PR_SALA'],
    'ventoinha' => ['tabela' => 'ventoinha', 'id' => 'VENT_ID', 'ref' => 'VENT_REF', 'estado' => 'VENT_ESTADO', 'sala' => 'VENT_SALA'],
    'caixa' => ['tabela' => 'caixa', 'id' => 'CAIXA_ID', 'ref' => 'CAIXA_REF', 'estado' => 'CAIXA_ESTADO', 'sala' => 'CAIXA_SALA']
];

// Verificar se o tipo informado é válido
if (!isset($tabelaMap[$tipo])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Tipo de componente inválido']);
    exit;
}

try {
    // Obter o nome da sala
    $stmtSala = $pdo->prepare("SELECT sala as nome FROM salas WHERE id_sala = ?");
    $stmtSala->execute([$sala_id]);
    $sala = $stmtSala->fetch(PDO::FETCH_ASSOC);
    
    if (!$sala) {
        throw new Exception('Sala não encontrada');
    }
    
    $sala_nome = $sala['nome'];
    $campos = $tabelaMap[$tipo];
    
    // Debug para log
    error_log("Buscando componentes do tipo $tipo na sala $sala_id (nome: $sala_nome)");
    
    // Buscar componentes da sala especificada
    $sql = "SELECT 
                comp.{$campos['id']} as id, 
                comp.{$campos['ref']} as ref, 
                comp.{$campos['estado']} as estado
            FROM {$campos['tabela']} comp
            WHERE comp.{$campos['sala']} = ?
            AND NOT EXISTS (
                SELECT 1 FROM tickets t 
                JOIN ticket_componentes tc ON t.id = tc.ticket_id 
                WHERE tc.equipamento = ? AND tc.ref_equipamento = comp.{$campos['id']} 
                AND t.status IN ('aberto', 'em_andamento')
            )
            ORDER BY comp.{$campos['ref']}";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sala_nome, $tipo]);
    $componentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug para verificar quantos resultados foram encontrados
    error_log("Encontrados " . count($componentes) . " componentes disponíveis do tipo $tipo para a sala $sala_nome");
    
    // Formatar os dados para o retorno
    $resultado = [];
    foreach ($componentes as $componente) {
        $resultado[] = [
            'id' => $componente['id'],
            'texto' => $componente['ref']
        ];
    }
    
    // Retornar os dados como JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $resultado
    ]);
    
} catch (Exception $e) {
    // Retornar mensagem de erro
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar componentes: ' . $e->getMessage()
    ]);
}
?> 