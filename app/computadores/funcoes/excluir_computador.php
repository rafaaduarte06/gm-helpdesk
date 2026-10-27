<?php
// Desativar a exibição de erros no output, para não interferir no JSON
error_reporting(0);
ini_set('display_errors', 0);

require_once('../../ligacao_base_dados.php');

// Verificar se o usuário está logado e é um técnico
session_start();
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Não autorizado']);
    exit;
}

// Verificar se o ID do computador foi informado
if (!isset($_POST['comp_id']) || empty($_POST['comp_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'ID do computador não informado']);
    exit;
}

$compId = trim($_POST['comp_id']);

try {
    // Iniciar transação para garantir integridade dos dados
    $pdo->beginTransaction();
    
    // Buscar informações do computador para liberar os componentes
    $stmt = $pdo->prepare("SELECT * FROM computadores WHERE COMP_ID = ?");
    $stmt->execute([$compId]);
    $computador = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$computador) {
        throw new Exception("Computador não encontrado!");
    }
    
    // Arrays para mapear tabelas e campos
    $tabelasComponentes = [
        'RATO' => ['tabela' => 'ratos', 'comp' => 'RATOS_COMP', 'id' => 'RATOS_ID', 'estado' => 'RATOS_ESTADO'],
        'TECLADO' => ['tabela' => 'teclados', 'comp' => 'TECLADO_COMP', 'id' => 'TECLADO_ID', 'estado' => 'TECLADO_ESTADO'],
        'MONITOR' => ['tabela' => 'monitor', 'comp' => 'MONITOR_COMP', 'id' => 'MONITOR_ID', 'estado' => 'MONITOR_ESTADO'],
        'PM' => ['tabela' => 'placamae', 'comp' => 'PM_COMP', 'id' => 'PM_ID', 'estado' => 'PM_ESTADO'],
        'PROC' => ['tabela' => 'processador', 'comp' => 'PROC_COMP', 'id' => 'PROC_ID', 'estado' => 'PROC_ESTADO'],
        'COOLER' => ['tabela' => 'cooler', 'comp' => 'COO_COMP', 'id' => 'COO_ID', 'estado' => 'COO_ESTADO'],
        'PG' => ['tabela' => 'placagrafica', 'comp' => 'PG_COMP', 'id' => 'PG_ID', 'estado' => 'PG_ESTADO'],
        'DISCO' => ['tabela' => 'disco', 'comp' => 'DISCO_COMP', 'id' => 'DISCO_ID', 'estado' => 'DISCO_ESTADO'],
        'FONTE' => ['tabela' => 'fonte', 'comp' => 'FONTE_COMP', 'id' => 'FONTE_ID', 'estado' => 'FONTE_ESTADO'],
        'MEMORIAR' => ['tabela' => 'memoriaram', 'comp' => 'MR_COMP', 'id' => 'MR_ID', 'estado' => 'MR_ESTADO'],
        'DRIVEDVD' => ['tabela' => 'drivedvd', 'comp' => 'DRVD_COMP', 'id' => 'DRVD_ID', 'estado' => 'DRVD_ESTADO'],
        'PR' => ['tabela' => 'placaderede', 'comp' => 'PR_COMP', 'id' => 'PR_ID', 'estado' => 'PR_ESTADO'],
        'VENTOINHA' => ['tabela' => 'ventoinha', 'comp' => 'VENT_COMP', 'id' => 'VENT_ID', 'estado' => 'VENT_ESTADO'],
        'CAIXA' => ['tabela' => 'caixa', 'comp' => 'CAIXA_COMP', 'id' => 'CAIXA_ID', 'estado' => 'CAIXA_ESTADO']
    ];
    
    // Liberar todos os componentes do computador
    foreach ($tabelasComponentes as $tipoComp => $info) {
        $campo = "COMP_$tipoComp";
        $componenteId = $computador[$campo];
        
        if (!empty($componenteId)) {
            try {
                // Testar se o campo comp existe na tabela
                $testStmt = $pdo->prepare("SELECT {$info['comp']} FROM {$info['tabela']} LIMIT 1");
                $testStmt->execute();
                
                // Verificar o estado atual do componente
                $estadoStmt = $pdo->prepare("SELECT {$info['estado']} FROM {$info['tabela']} WHERE {$info['id']} = ?");
                $estadoStmt->execute([$componenteId]);
                $estadoAtual = $estadoStmt->fetchColumn();
                
                // Verificar se o componente está em uma sala (estado em uso - 2)
                $estaEmSala = ($estadoAtual == '2');
                
                // Atualizar o componente: remover referência do computador
                $updateStmt = $pdo->prepare("UPDATE {$info['tabela']} SET {$info['comp']} = NULL WHERE {$info['id']} = ?");
                $updateStmt->execute([$componenteId]);
                
                // Atualizar estado para disponível (1) apenas se não estiver em manutenção (3) ou sem reparo (4)
                if ($estadoAtual != '3' && $estadoAtual != '4') {
                    $estadoStmt = $pdo->prepare("UPDATE {$info['tabela']} SET {$info['estado']} = '1' WHERE {$info['id']} = ?");
                    $estadoStmt->execute([$componenteId]);
                }
                
                // Também atualizar o campo sala para vazio se existir e o componente estiver em uso
                if ($estaEmSala) {
                    try {
                        // Primeiro verificar se o campo sala existe na tabela
                        $salaField = '';
                        switch ($info['tabela']) {
                            case 'ratos': $salaField = 'RATOS_SALA'; break;
                            case 'teclados': $salaField = 'TECLADO_SALA'; break;
                            case 'monitor': $salaField = 'MONITOR_SALA'; break;
                            case 'placamae': $salaField = 'PM_SALA'; break;
                            case 'processador': $salaField = 'PROC_SALA'; break;
                            case 'cooler': $salaField = 'COO_SALA'; break;
                            case 'placagrafica': $salaField = 'PG_SALA'; break;
                            case 'disco': $salaField = 'DISCO_SALA'; break;
                            case 'fonte': $salaField = 'FONTE_SALA'; break;
                            case 'memoriaram': $salaField = 'MR_SALA'; break;
                            case 'drivedvd': $salaField = 'DRVD_SALA'; break;
                            case 'placaderede': $salaField = 'PR_SALA'; break;
                            case 'ventoinha': $salaField = 'VENT_SALA'; break;
                            case 'caixa': $salaField = 'CAIXA_SALA'; break;
                            default: $salaField = ''; break;
                        }
                        
                        if (!empty($salaField)) {
                            $updateSalaStmt = $pdo->prepare("UPDATE {$info['tabela']} SET $salaField = '' WHERE {$info['id']} = ?");
                            $updateSalaStmt->execute([$componenteId]);
                        }
                    } catch (PDOException $e) {
                        // Ignorar erro se não conseguir atualizar o campo sala
                        error_log("Erro ao atualizar campo sala para o componente {$tipoComp}: " . $e->getMessage());
                    }
                }
                
                error_log("Componente {$tipoComp} ID {$componenteId} dissociado do computador {$compId} e estado atualizado");
            } catch (PDOException $e) {
                // Ignorar erro e continuar com o próximo componente
                error_log("Erro ao processar componente {$tipoComp}: " . $e->getMessage());
            }
        }
    }
    
    // Excluir o computador
    $stmt = $pdo->prepare("DELETE FROM computadores WHERE COMP_ID = ?");
    $stmt->execute([$compId]);
    
    // Commit da transação
    $pdo->commit();
    
    // Resposta de sucesso
    $response = [
        'status' => 'success',
        'message' => 'Computador excluído com sucesso!'
    ];
} catch (Exception $e) {
    // Rollback em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
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