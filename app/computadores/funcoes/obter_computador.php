<?php
require_once('../../ligacao_base_dados.php');

// Verificar se o usuário está logado e é um técnico
session_start();
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Não autorizado']);
    exit;
}

// Verificar se o ID do computador foi informado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'ID do computador não informado']);
    exit;
}

$compId = trim($_GET['id']);

try {
    // Buscar informações do computador
    $stmt = $conn->prepare("SELECT * FROM computadores WHERE COMP_ID = ?");
    $stmt->execute([$compId]);
    $computador = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$computador) {
        throw new Exception("Computador não encontrado!");
    }
    
    // Arrays para mapear tabelas e campos
    $tabelasComponentes = [
        'RATO' => 'ratos',
        'TECLADO' => 'teclados',
        'MONITOR' => 'monitor',
        'PM' => 'placamae',
        'PROC' => 'processador',
        'COOLER' => 'cooler',
        'PG' => 'placagrafica',
        'DISCO' => 'disco',
        'FONTE' => 'fonte',
        'MEMORIAR' => 'memoriaram',
        'DRIVEDVD' => 'drivedvd',
        'PR' => 'placaderede',
        'VENTOINHA' => 'ventoinha',
        'CAIXA' => 'caixa'
    ];
    
    $camposId = [
        'ratos' => 'RATOS_ID',
        'teclados' => 'TECLADO_ID',
        'monitor' => 'MONITOR_ID',
        'placamae' => 'PM_ID',
        'processador' => 'PROC_ID',
        'cooler' => 'COO_ID',
        'placagrafica' => 'PG_ID',
        'disco' => 'DISCO_ID',
        'fonte' => 'FONTE_ID',
        'memoriaram' => 'MR_ID',
        'drivedvd' => 'DRVD_ID',
        'placaderede' => 'PR_ID',
        'ventoinha' => 'VENT_ID',
        'caixa' => 'CAIXA_ID'
    ];
    
    $camposRef = [
        'ratos' => 'RATOS_REF',
        'teclados' => 'TECLADO_REF',
        'monitor' => 'MONITOR_REF',
        'placamae' => 'PM_REF',
        'processador' => 'PROC_REF',
        'cooler' => 'COO_REF',
        'placagrafica' => 'PG_REF',
        'disco' => 'DISCO_REF',
        'fonte' => 'FONTE_REF',
        'memoriaram' => 'MR_REF',
        'drivedvd' => 'DRVD_REF',
        'placaderede' => 'PR_REF',
        'ventoinha' => 'VENT_REF',
        'caixa' => 'CAIXA_REF'
    ];
    
    $camposEstado = [
        'ratos' => 'RATOS_ESTADO',
        'teclados' => 'TECLADO_ESTADO',
        'monitor' => 'MONITOR_ESTADO',
        'placamae' => 'PM_ESTADO',
        'processador' => 'PROC_ESTADO',
        'cooler' => 'COO_ESTADO',
        'placagrafica' => 'PG_ESTADO',
        'disco' => 'DISCO_ESTADO',
        'fonte' => 'FONTE_ESTADO',
        'memoriaram' => 'MR_ESTADO',
        'drivedvd' => 'DRVD_ESTADO',
        'placaderede' => 'PR_ESTADO',
        'ventoinha' => 'VENT_ESTADO',
        'caixa' => 'CAIXA_ESTADO'
    ];
    
    // Informações detalhadas dos componentes
    $componentesDetalhados = [];
    
    foreach ($tabelasComponentes as $tipoComp => $tabela) {
        $campo = "COMP_$tipoComp";
        $componenteId = $computador[$campo];
        
        if (!empty($componenteId)) {
            $campoId = $camposId[$tabela];
            $campoRef = $camposRef[$tabela];
            $campoEstado = $camposEstado[$tabela];
            
            $stmt = $conn->prepare("SELECT $campoId, $campoRef, $campoEstado FROM $tabela WHERE $campoId = ?");
            $stmt->execute([$componenteId]);
            $componente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($componente) {
                $estadoDesc = '';
                switch ($componente[$campoEstado]) {
                    case '1':
                        $estadoDesc = 'Disponível';
                        break;
                    case '2':
                        $estadoDesc = 'Manutenção';
                        break;
                    case '3':
                        $estadoDesc = 'Em Uso';
                        break;
                    case '4':
                        $estadoDesc = 'Sem Reparo';
                        break;
                    default:
                        $estadoDesc = 'Desconhecido';
                }
                
                $tipoNormalizado = strtolower($tipoComp);
                $componentesDetalhados[$tipoNormalizado] = [
                    'id' => $componente[$campoId],
                    'ref' => $componente[$campoRef],
                    'estado' => $componente[$campoEstado],
                    'estadoDesc' => $estadoDesc
                ];
            }
        }
    }
    
    // Obter informação do estado do computador
    $stmt = $conn->prepare("SELECT ESTADO FROM comp_estado WHERE ID_ESTADO = ?");
    $stmt->execute([$computador['COMP_ESTADO']]);
    $estadoDesc = $stmt->fetchColumn();
    
    // Preparar resposta
    $resposta = [
        'id' => $computador['COMP_ID'],
        'ref' => $computador['COMP_REF'],
        'estado' => [
            'id' => $computador['COMP_ESTADO'],
            'descricao' => $estadoDesc ?: 'Desconhecido'
        ],
        'componentes' => $componentesDetalhados
    ];
    
    // Resposta de sucesso
    $response = [
        'status' => 'success',
        'computador' => $resposta
    ];
} catch (Exception $e) {
    // Resposta de erro
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Retornar resposta em JSON
header('Content-Type: application/json');
echo json_encode($response);
exit; 