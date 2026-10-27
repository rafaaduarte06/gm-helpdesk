<?php
session_start();
require_once('../../ligacao_base_dados.php');

header('Content-Type: application/json');

// Verificar se o usuário está logado e é um técnico
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Debug: Mostrar todos os dados recebidos
error_log("POST data: " . print_r($_POST, true));

// Obter dados do formulário
$compId = isset($_POST['comp_id']) ? $_POST['comp_id'] : '';
$tipoComponente = isset($_POST['tipo']) ? strtoupper($_POST['tipo']) : '';
$componenteId = isset($_POST['componente_id']) ? $_POST['componente_id'] : '';

// Log de debug dos parâmetros recebidos
error_log("Valores recebidos - compId: $compId, tipoComponente: $tipoComponente, componenteId: $componenteId");

// Validar dados
if (empty($compId) || empty($tipoComponente) || empty($componenteId)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Todos os campos são obrigatórios',
        'debug' => [
            'comp_id' => $compId,
            'tipo' => $tipoComponente,
            'componente_id' => $componenteId
        ]
    ]);
    exit;
}

// Mapeamento de tipos de componentes
$componentesInfo = [
    'RATO' => ['tabela' => 'ratos', 'id' => 'RATOS_ID', 'estado' => 'RATOS_ESTADO', 'comp' => 'RATOS_COMP', 'sala' => 'RATOS_SALA'],
    'TECLADO' => ['tabela' => 'teclados', 'id' => 'TECLADO_ID', 'estado' => 'TECLADO_ESTADO', 'comp' => 'TECLADO_COMP', 'sala' => 'TECLADO_SALA'],
    'MONITOR' => ['tabela' => 'monitor', 'id' => 'MONITOR_ID', 'estado' => 'MONITOR_ESTADO', 'comp' => 'MONITOR_COMP', 'sala' => 'MONITOR_SALA'],
    'PM' => ['tabela' => 'placamae', 'id' => 'PM_ID', 'estado' => 'PM_ESTADO', 'comp' => 'PM_COMP', 'sala' => 'PM_SALA'],
    'PROC' => ['tabela' => 'processador', 'id' => 'PROC_ID', 'estado' => 'PROC_ESTADO', 'comp' => 'PROC_COMP', 'sala' => 'PROC_SALA'],
    'COOLER' => ['tabela' => 'cooler', 'id' => 'COO_ID', 'estado' => 'COO_ESTADO', 'comp' => 'COO_COMP', 'sala' => 'COO_SALA'],
    'PG' => ['tabela' => 'placagrafica', 'id' => 'PG_ID', 'estado' => 'PG_ESTADO', 'comp' => 'PG_COMP', 'sala' => 'PG_SALA'],
    'DISCO' => ['tabela' => 'disco', 'id' => 'DISCO_ID', 'estado' => 'DISCO_ESTADO', 'comp' => 'DISCO_COMP', 'sala' => 'DISCO_SALA'],
    'FONTE' => ['tabela' => 'fonte', 'id' => 'FONTE_ID', 'estado' => 'FONTE_ESTADO', 'comp' => 'FONTE_COMP', 'sala' => 'FONTE_SALA'],
    'MEMORIAR' => ['tabela' => 'memoriaram', 'id' => 'MR_ID', 'estado' => 'MR_ESTADO', 'comp' => 'MR_COMP', 'sala' => 'MR_SALA'],
    'DRIVEDVD' => ['tabela' => 'drivedvd', 'id' => 'DRVD_ID', 'estado' => 'DRVD_ESTADO', 'comp' => 'DRVD_COMP', 'sala' => 'DRVD_SALA'],
    'PR' => ['tabela' => 'placaderede', 'id' => 'PR_ID', 'estado' => 'PR_ESTADO', 'comp' => 'PR_COMP', 'sala' => 'PR_SALA'],
    'VENTOINHA' => ['tabela' => 'ventoinha', 'id' => 'VENT_ID', 'estado' => 'VENT_ESTADO', 'comp' => 'VENT_COMP', 'sala' => 'VENT_SALA'],
    'CAIXA' => ['tabela' => 'caixa', 'id' => 'CAIXA_ID', 'estado' => 'CAIXA_ESTADO', 'comp' => 'CAIXA_COMP', 'sala' => 'CAIXA_SALA']
];

// Verificar se o tipo de componente é válido
if (!array_key_exists($tipoComponente, $componentesInfo)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de componente inválido: ' . $tipoComponente]);
    exit;
}

$info = $componentesInfo[$tipoComponente];

try {
    // Verificar se o computador existe
    $stmt = $pdo->prepare("SELECT * FROM computadores WHERE COMP_ID = ?");
    $stmt->execute([$compId]);
    $computador = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$computador) {
        echo json_encode(['success' => false, 'message' => 'Computador não encontrado']);
        exit;
    }
    
    // Verificar se o componente está realmente atribuído a este computador
    if ($computador['COMP_' . $tipoComponente] != $componenteId) {
        echo json_encode([
            'success' => false, 
            'message' => 'Este componente não está atribuído a este computador',
            'debug' => [
                'computer_component' => $computador['COMP_' . $tipoComponente],
                'requested_component' => $componenteId
            ]
        ]);
        exit;
    }
    
    // Começar transação
    $pdo->beginTransaction();
    
    // Verificar se o computador está em uma sala
    $compEmSala = false;
    try {
        $stmt = $pdo->prepare("SELECT COMP_SALA FROM computadores WHERE COMP_ID = ? AND COMP_SALA != ''");
        $stmt->execute([$compId]);
        $compEmSala = $stmt->rowCount() > 0;
        if ($compEmSala) {
            error_log("Computador está em uma sala - vamos remover o componente da sala também");
        }
    } catch (PDOException $e) {
        error_log("Erro ao verificar se o computador está em sala: " . $e->getMessage());
    }
    
    // Construir a query para atualizar o componente
    $updateFields = [
        "{$info['estado']} = 1", 
        "{$info['comp']} = ''"
    ];
    
    // Adicionar atualização do campo sala se existir
    if (isset($info['sala'])) {
        $updateFields[] = "{$info['sala']} = ''";
    }
    
    $updateFieldsStr = implode(", ", $updateFields);
    $sql = "UPDATE {$info['tabela']} SET $updateFieldsStr WHERE {$info['id']} = ?";
    
    // Atualizar o componente: remover referência, remover da sala e colocar disponível
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$componenteId]);
    
    // Definir o valor do componente no computador como vazio em vez de NULL
    $stmt = $pdo->prepare("UPDATE computadores SET COMP_{$tipoComponente} = '' WHERE COMP_ID = ?");
    $result = $stmt->execute([$compId]);
    
    // Finalizar transação
    if ($result) {
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Componente removido com sucesso']);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao remover componente']);
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Erro de banco de dados: ' . $e->getMessage()]);
} 