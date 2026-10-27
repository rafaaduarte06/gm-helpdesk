<?php
require_once('../../ligacao_base_dados.php');

// Verificar se o usuário está logado e é um técnico
session_start();
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Não autorizado']);
    exit;
}

// Verificar se o método é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Método inválido']);
    exit;
}

// Verificar se todos os dados necessários foram informados
if (!isset($_POST['tipo']) || empty($_POST['tipo']) || 
    !isset($_POST['id']) || empty($_POST['id']) || 
    !isset($_POST['estado']) || empty($_POST['estado'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Dados incompletos']);
    exit;
}

$tipo = strtolower(trim($_POST['tipo']));
$id = trim($_POST['id']);
$estado = trim($_POST['estado']);

try {
    // Iniciar transação para garantir integridade dos dados
    $conn->beginTransaction();
    
    // Arrays para mapear tabelas e campos
    $tabelasComponentes = [
        'rato' => 'ratos',
        'teclado' => 'teclados',
        'monitor' => 'monitor',
        'placamae' => 'placamae',
        'processador' => 'processador',
        'cooler' => 'cooler',
        'placagrafica' => 'placagrafica',
        'disco' => 'disco',
        'fonte' => 'fonte',
        'memoriaram' => 'memoriaram',
        'drivedvd' => 'drivedvd',
        'placaderede' => 'placaderede',
        'ventoinha' => 'ventoinha',
        'caixa' => 'caixa'
    ];
    
    // Verificar se o tipo especificado existe
    if (!array_key_exists($tipo, $tabelasComponentes)) {
        throw new Exception("Tipo de componente não suportado");
    }
    
    $tabela = $tabelasComponentes[$tipo];
    
    // Mapear campos de ID e estado de acordo com a tabela
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
    
    $campoId = $camposId[$tabela];
    $campoEstado = $camposEstado[$tabela];
    
    // Verificar se o componente existe
    $stmt = $conn->prepare("SELECT COUNT(*) FROM $tabela WHERE $campoId = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception("Componente não encontrado");
    }
    
    // Verificar se o componente está associado a algum computador
    $campoComp = 'COMP_' . strtoupper(substr($tipo, 0, 1)) . substr($tipo, 1);
    $stmt = $conn->prepare("SELECT COMP_ID, COMP_REF FROM computadores WHERE $campoComp = ?");
    $stmt->execute([$id]);
    $computador = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Ações dependentes do novo estado
    if ($estado == '4') { // Sem Reparo
        // Se o componente estiver associado a um computador, removê-lo
        if ($computador) {
            $stmt = $conn->prepare("UPDATE computadores SET $campoComp = '' WHERE COMP_ID = ?");
            $stmt->execute([$computador['COMP_ID']]);
        }
    } else if ($estado == '2') { // Manutenção
        // O componente em manutenção permanece associado ao computador, mas deve ser visualmente destacado
        // Isso será tratado na interface através do CSS
    }
    
    // Atualizar o estado do componente
    $stmt = $conn->prepare("UPDATE $tabela SET $campoEstado = ? WHERE $campoId = ?");
    $stmt->execute([$estado, $id]);
    
    // Commit da transação
    $conn->commit();
    
    // Preparar resposta
    $estadoDesc = '';
    switch ($estado) {
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
    
    $response = [
        'status' => 'success',
        'message' => 'Estado do componente atualizado com sucesso!',
        'computador' => $computador ? $computador['COMP_REF'] : null,
        'acaoExecutada' => $estado == '4' && $computador ? 'removido' : 'atualizado',
        'novoEstado' => [
            'id' => $estado,
            'descricao' => $estadoDesc
        ]
    ];
} catch (Exception $e) {
    // Rollback em caso de erro
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
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