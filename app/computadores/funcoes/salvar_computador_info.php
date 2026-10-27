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
error_log("POST data salvar_computador_info: " . print_r($_POST, true));

// Obter dados do formulário
$compId = isset($_POST['comp_id']) ? $_POST['comp_id'] : ''; // Não converter para intval para aceitar IDs alfanuméricos
$compRef = isset($_POST['comp_ref']) ? trim($_POST['comp_ref']) : '';
$compEstado = isset($_POST['comp_estado']) ? intval($_POST['comp_estado']) : 0;

// Validar estado do computador
if (!in_array($compEstado, [1, 3, 4])) {
    echo json_encode(['success' => false, 'message' => 'Estado do computador inválido']);
    exit;
}

// Debug para mostrar valores após o processamento
error_log("Valores processados: compId: $compId, compRef: $compRef, compEstado: $compEstado");

// Validar dados
if (empty($compId) || empty($compRef) || empty($compEstado)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Todos os campos são obrigatórios',
        'data' => [
            'comp_id' => $compId,
            'comp_ref' => $compRef,
            'comp_estado' => $compEstado
        ]
    ]);
    exit;
}

try {
    // Verificar se o computador existe e obter seu estado atual
    $stmt = $pdo->prepare("SELECT * FROM computadores WHERE COMP_ID = ?");
    $stmt->execute([$compId]);
    $computador = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$computador) {
        error_log("Computador não encontrado para ID: $compId");
        echo json_encode(['success' => false, 'message' => 'Computador não encontrado']);
        exit;
    }
    
    // Mapeamento de tipos de componentes
    $componentesInfo = [
        'RATO' => ['tabela' => 'ratos', 'id' => 'RATOS_ID', 'estado' => 'RATOS_ESTADO'],
        'TECLADO' => ['tabela' => 'teclados', 'id' => 'TECLADO_ID', 'estado' => 'TECLADO_ESTADO'],
        'MONITOR' => ['tabela' => 'monitor', 'id' => 'MONITOR_ID', 'estado' => 'MONITOR_ESTADO'],
        'PM' => ['tabela' => 'placamae', 'id' => 'PM_ID', 'estado' => 'PM_ESTADO'],
        'PROC' => ['tabela' => 'processador', 'id' => 'PROC_ID', 'estado' => 'PROC_ESTADO'],
        'COOLER' => ['tabela' => 'cooler', 'id' => 'COO_ID', 'estado' => 'COO_ESTADO'],
        'PG' => ['tabela' => 'placagrafica', 'id' => 'PG_ID', 'estado' => 'PG_ESTADO'],
        'DISCO' => ['tabela' => 'disco', 'id' => 'DISCO_ID', 'estado' => 'DISCO_ESTADO'],
        'FONTE' => ['tabela' => 'fonte', 'id' => 'FONTE_ID', 'estado' => 'FONTE_ESTADO'],
        'MEMORIAR' => ['tabela' => 'memoriaram', 'id' => 'MR_ID', 'estado' => 'MR_ESTADO'],
        'DRIVEDVD' => ['tabela' => 'drivedvd', 'id' => 'DRVD_ID', 'estado' => 'DRVD_ESTADO'],
        'PR' => ['tabela' => 'placaderede', 'id' => 'PR_ID', 'estado' => 'PR_ESTADO'],
        'VENTOINHA' => ['tabela' => 'ventoinha', 'id' => 'VENT_ID', 'estado' => 'VENT_ESTADO'],
        'CAIXA' => ['tabela' => 'caixa', 'id' => 'CAIXA_ID', 'estado' => 'CAIXA_ESTADO']
    ];

    $pdo->beginTransaction();
    
    // Atualizar informações do computador
    $stmt = $pdo->prepare("UPDATE computadores SET COMP_REF = ?, COMP_ESTADO = ? WHERE COMP_ID = ?");
    $result = $stmt->execute([$compRef, $compEstado, $compId]);

    // Se o computador está sendo colocado em manutenção (estado 3), atualizar todos os componentes
    // associados para estado de manutenção também
    if ($compEstado == 3) {
        foreach ($componentesInfo as $tipo => $info) {
            $componenteId = $computador["COMP_$tipo"];
            
            if (!empty($componenteId)) {
                // Atualizar o estado do componente para manutenção (3)
                $stmt = $pdo->prepare("UPDATE {$info['tabela']} SET {$info['estado']} = 3 WHERE {$info['id']} = ?");
                $stmt->execute([$componenteId]);
                error_log("Componente $tipo (ID: $componenteId) atualizado para estado de manutenção");
            }
        }
    }

    $pdo->commit();
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Informações atualizadas com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar informações']);
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erro de banco de dados em salvar_computador_info.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro de banco de dados: ' . $e->getMessage()]);
} 