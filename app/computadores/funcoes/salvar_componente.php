<?php
session_start();
require_once('../../ligacao_base_dados.php');

// IMPORTANTE: Os componentes devem sempre permanecer com estado 1 (Disponível),
// independentemente de estarem associados a um computador ou não.

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
error_log("POST data salvar_componente: " . print_r($_POST, true));

// Obter dados do formulário
$compId = isset($_POST['comp_id']) ? $_POST['comp_id'] : ''; // Removido intval para aceitar IDs alfanuméricos
$tipoComponente = isset($_POST['tipo_componente']) ? strtoupper($_POST['tipo_componente']) : '';
$componenteId = isset($_POST['componente_id']) ? intval($_POST['componente_id']) : 0;

// Debug para mostrar valores após o processamento
error_log("Valores processados: compId: $compId, tipoComponente: $tipoComponente, componenteId: $componenteId");

// Validar dados
if (empty($compId) || empty($tipoComponente) || empty($componenteId)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Todos os campos são obrigatórios',
        'data' => [
            'comp_id' => $compId,
            'tipo_componente' => $tipoComponente,
            'componente_id' => $componenteId
        ]
    ]);
    exit;
}

// Mapeamento de tipos de componentes
$componentesInfo = [
    'RATO' => ['tabela' => 'ratos', 'id' => 'RATOS_ID', 'ref' => 'RATOS_REF', 'estado' => 'RATOS_ESTADO', 'comp' => 'RATOS_COMP', 'sala' => 'RATOS_SALA'],
    'TECLADO' => ['tabela' => 'teclados', 'id' => 'TECLADO_ID', 'ref' => 'TECLADO_REF', 'estado' => 'TECLADO_ESTADO', 'comp' => 'TECLADO_COMP', 'sala' => 'TECLADO_SALA'],
    'MONITOR' => ['tabela' => 'monitor', 'id' => 'MONITOR_ID', 'ref' => 'MONITOR_REF', 'estado' => 'MONITOR_ESTADO', 'comp' => 'MONITOR_COMP', 'sala' => 'MONITOR_SALA'],
    'PM' => ['tabela' => 'placamae', 'id' => 'PM_ID', 'ref' => 'PM_REF', 'estado' => 'PM_ESTADO', 'comp' => 'PM_COMP', 'sala' => 'PM_SALA'],
    'PROC' => ['tabela' => 'processador', 'id' => 'PROC_ID', 'ref' => 'PROC_REF', 'estado' => 'PROC_ESTADO', 'comp' => 'PROC_COMP', 'sala' => 'PROC_SALA'],
    'COOLER' => ['tabela' => 'cooler', 'id' => 'COO_ID', 'ref' => 'COO_REF', 'estado' => 'COO_ESTADO', 'comp' => 'COO_COMP', 'sala' => 'COO_SALA'],
    'PG' => ['tabela' => 'placagrafica', 'id' => 'PG_ID', 'ref' => 'PG_REF', 'estado' => 'PG_ESTADO', 'comp' => 'PG_COMP', 'sala' => 'PG_SALA'],
    'DISCO' => ['tabela' => 'disco', 'id' => 'DISCO_ID', 'ref' => 'DISCO_REF', 'estado' => 'DISCO_ESTADO', 'comp' => 'DISCO_COMP', 'sala' => 'DISCO_SALA'],
    'FONTE' => ['tabela' => 'fonte', 'id' => 'FONTE_ID', 'ref' => 'FONTE_REF', 'estado' => 'FONTE_ESTADO', 'comp' => 'FONTE_COMP', 'sala' => 'FONTE_SALA'],
    'MEMORIAR' => ['tabela' => 'memoriaram', 'id' => 'MR_ID', 'ref' => 'MR_REF', 'estado' => 'MR_ESTADO', 'comp' => 'MR_COMP', 'sala' => 'MR_SALA'],
    'DRIVEDVD' => ['tabela' => 'drivedvd', 'id' => 'DRVD_ID', 'ref' => 'DRVD_REF', 'estado' => 'DRVD_ESTADO', 'comp' => 'DRVD_COMP', 'sala' => 'DRVD_SALA'],
    'PR' => ['tabela' => 'placaderede', 'id' => 'PR_ID', 'ref' => 'PR_REF', 'estado' => 'PR_ESTADO', 'comp' => 'PR_COMP', 'sala' => 'PR_SALA'],
    'VENTOINHA' => ['tabela' => 'ventoinha', 'id' => 'VENT_ID', 'ref' => 'VENT_REF', 'estado' => 'VENT_ESTADO', 'comp' => 'VENT_COMP', 'sala' => 'VENT_SALA'],
    'CAIXA' => ['tabela' => 'caixa', 'id' => 'CAIXA_ID', 'ref' => 'CAIXA_REF', 'estado' => 'CAIXA_ESTADO', 'comp' => 'CAIXA_COMP', 'sala' => 'CAIXA_SALA']
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
        error_log("Computador não encontrado para ID: $compId");
        echo json_encode(['success' => false, 'message' => 'Computador não encontrado']);
        exit;
    }
    
    // Verificar se o componente existe
    $stmt = $pdo->prepare("SELECT * FROM {$info['tabela']} WHERE {$info['id']} = ?");
    $stmt->execute([$componenteId]);
    $componente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$componente) {
        echo json_encode(['success' => false, 'message' => 'Componente não encontrado']);
        exit;
    }
    
    // Verificar se a tabela tem o campo de computador (comp)
    $hasCompField = false;
    try {
        $checkStmt = $pdo->prepare("SELECT {$info['comp']} FROM {$info['tabela']} LIMIT 1");
        $checkStmt->execute();
        $hasCompField = true;
    } catch (PDOException $ex) {
        $hasCompField = false;
    }
    
    // Verificar se o componente não está associado a outro computador (apenas se o campo existir)
    if ($hasCompField && !empty($componente[$info['comp']]) && $componente[$info['comp']] != $compId) {
        echo json_encode(['success' => false, 'message' => 'Este componente já está associado a outro computador']);
        exit;
    }
    
    // Começar transação
    $pdo->beginTransaction();
    
    // Verificar se a tabela tem o campo de computador (comp)
    $hasCompField = false;
    try {
        $checkStmt = $pdo->prepare("SELECT {$info['comp']} FROM {$info['tabela']} LIMIT 1");
        $checkStmt->execute();
        $hasCompField = true;
        error_log("Tabela {$info['tabela']} tem o campo {$info['comp']}");
    } catch (PDOException $ex) {
        error_log("Tabela {$info['tabela']} não tem o campo {$info['comp']}: " . $ex->getMessage());
        $hasCompField = false;
    }
    
    // Determinar o estado apropriado para o componente baseado no estado do computador
    $novoEstadoComponente = '1'; // Default: Disponível
    
    if ($computador['COMP_ESTADO'] == '2') {
        // Computador em uso (numa sala)
        $novoEstadoComponente = '2'; // Em uso
        
        // Também vamos obter a sala do computador para atribuir ao componente
        $salaComputador = '';
        try {
            // Verificar se o computador tem sala definida
            $compSalaStmt = $pdo->prepare("SELECT COMP_SALA FROM computadores WHERE COMP_ID = ?");
            $compSalaStmt->execute([$compId]);
            $salaComputador = $compSalaStmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Erro ao verificar a sala do computador: " . $e->getMessage());
        }
    } else if ($computador['COMP_ESTADO'] == '3') {
        // Computador em manutenção
        $novoEstadoComponente = '3'; // Em manutenção
    }
    
    // Atualizar o componente com a referência do computador e o estado adequado
    if ($hasCompField) {
        // Primeiro, apenas atualizar a referência do computador
        $stmt = $pdo->prepare("UPDATE {$info['tabela']} SET {$info['comp']} = ? WHERE {$info['id']} = ?");
        $stmt->execute([$compId, $componenteId]);
        
        // Depois, atualizar o estado do componente
        if ($novoEstadoComponente != '1') { // Se não for disponível (caso padrão)
            $stmt = $pdo->prepare("UPDATE {$info['tabela']} SET {$info['estado']} = ? WHERE {$info['id']} = ?");
            $stmt->execute([$novoEstadoComponente, $componenteId]);
            error_log("Componente {$tipoComponente} (ID: {$componenteId}) atualizado para estado {$novoEstadoComponente}");
        }
        
        // Se o computador está numa sala, atualizar também a sala do componente
        if (isset($salaComputador) && !empty($salaComputador) && isset($info['sala'])) {
            try {
                $salaStmt = $pdo->prepare("UPDATE {$info['tabela']} SET {$info['sala']} = ? WHERE {$info['id']} = ?");
                $salaStmt->execute([$salaComputador, $componenteId]);
                error_log("Componente {$tipoComponente} (ID: {$componenteId}) atualizado com a sala {$salaComputador}");
            } catch (PDOException $e) {
                error_log("Erro ao atualizar a sala do componente: " . $e->getMessage());
            }
        }
    }
    
    // Se havia um componente antigo, atualizar seu estado para disponível e remover associação
    $componenteAntigoId = $computador['COMP_' . $tipoComponente];
    if ($componenteAntigoId && $componenteAntigoId != $componenteId) {
        if ($hasCompField) {
            // Primeiro verificar se o computador está em uma sala
            $compEmSala = false;
            $nomeSala = '';
            try {
                $salaStmt = $pdo->prepare("SELECT COMP_SALA FROM computadores WHERE COMP_ID = ? AND COMP_SALA != ''");
                $salaStmt->execute([$compId]);
                $nomeSala = $salaStmt->fetchColumn();
                $compEmSala = !empty($nomeSala);
            } catch (PDOException $e) {
                error_log("Erro ao verificar sala do computador: " . $e->getMessage());
            }
            
            // Remover a associação com o computador
            $stmt = $pdo->prepare("UPDATE {$info['tabela']} SET {$info['comp']} = '' WHERE {$info['id']} = ?");
            $stmt->execute([$componenteAntigoId]);
            
            // Verificar o estado atual do componente antigo para decidir se atualiza para disponível
            $estadoStmt = $pdo->prepare("SELECT {$info['estado']} FROM {$info['tabela']} WHERE {$info['id']} = ?");
            $estadoStmt->execute([$componenteAntigoId]);
            $estadoAtual = $estadoStmt->fetchColumn();
            
            // Atualizar para disponível (1) apenas se não estiver em manutenção (3) ou sem reparo (4)
            if ($estadoAtual != '3' && $estadoAtual != '4') {
                $stmt = $pdo->prepare("UPDATE {$info['tabela']} SET {$info['estado']} = '1' WHERE {$info['id']} = ?");
                $stmt->execute([$componenteAntigoId]);
                error_log("Componente anterior {$tipoComponente} (ID: {$componenteAntigoId}) atualizado para estado disponível");
            }
            
            // Limpar também o campo sala se existir
            if (isset($info['sala'])) {
                try {
                    // Sempre limpa o campo sala quando um componente é removido do computador
                    // independente se o computador está numa sala ou não
                    $stmt = $pdo->prepare("UPDATE {$info['tabela']} SET {$info['sala']} = '' WHERE {$info['id']} = ?");
                    $stmt->execute([$componenteAntigoId]);
                    error_log("Removido componente {$tipoComponente} (ID: {$componenteAntigoId}) da sala");
                } catch (PDOException $e) {
                    error_log("Erro ao limpar a sala do componente antigo: " . $e->getMessage());
                }
            }
        }
    }
    
    // Atualizar o computador com o novo componente
    $stmt = $pdo->prepare("UPDATE computadores SET COMP_{$tipoComponente} = ? WHERE COMP_ID = ?");
    $result = $stmt->execute([$componenteId, $compId]);
    
    // Finalizar transação
    if ($result) {
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Componente atualizado com sucesso']);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar componente']);
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erro de banco de dados em salvar_componente.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro de banco de dados: ' . $e->getMessage()]);
} 