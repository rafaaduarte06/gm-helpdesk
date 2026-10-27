<?php
require_once('../../ligacao_base_dados.php');

// Verificar se o usuário está logado e é um técnico
session_start();
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Não autorizado']);
    exit;
}

// Tipo de componente solicitado via GET (rato, teclado, monitor, etc.)
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';

// Arrays para mapear as tabelas e campos
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
    'caixa' => 'caixa',
    'estado' => 'comp_estado'
];

// Campos de ID para cada tabela
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
    'caixa' => 'CAIXA_ID',
    'comp_estado' => 'ID_ESTADO'
];

// Campos de referência para cada tabela
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
    'caixa' => 'CAIXA_REF',
    'comp_estado' => 'ESTADO'
];

// Campos de estado para cada tabela
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

try {
    $response = [];
    
    // Verificar se o tipo especificado existe
    if ($tipo === 'estado') {
        // Caso especial para estados - excluir o estado "Em Uso"
        $sql = "SELECT ID_ESTADO, ESTADO FROM comp_estado 
                WHERE ID_ESTADO != 2  -- Excluir estado 'Em Uso' (ID 2)
                ORDER BY ESTADO";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        $componentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response = [
            'status' => 'success',
            'componentes' => $componentes
        ];
    } elseif (array_key_exists($tipo, $tabelasComponentes)) {
        $tabela = $tabelasComponentes[$tipo];
        $campoId = $camposId[$tabela];
        $campoRef = $camposRef[$tabela];
        $campoEstado = $camposEstado[$tabela];
        
        // Obter apenas componentes disponíveis (estado = '1')
        // E que não estejam já associados a computadores
        $sql = "SELECT $campoId as id, $campoRef as ref, $campoEstado as estado FROM $tabela 
                WHERE $campoEstado = '1' -- Apenas componentes disponíveis
                AND $campoId NOT IN (
                    SELECT DISTINCT CASE '$tipo'
                        WHEN 'processador' THEN COMP_PROC
                        WHEN 'placamae' THEN COMP_PM
                        WHEN 'placagrafica' THEN COMP_PG
                        WHEN 'memoriaram' THEN COMP_MEMORIAR
                        WHEN 'placaderede' THEN COMP_PR
                        WHEN 'rato' THEN COMP_RATO
                        WHEN 'teclado' THEN COMP_TECLADO
                        WHEN 'monitor' THEN COMP_MONITOR
                        WHEN 'cooler' THEN COMP_COOLER
                        WHEN 'disco' THEN COMP_DISCO
                        WHEN 'fonte' THEN COMP_FONTE
                        WHEN 'drivedvd' THEN COMP_DRIVEDVD
                        WHEN 'ventoinha' THEN COMP_VENTOINHA
                        WHEN 'caixa' THEN COMP_CAIXA
                    END
                    FROM computadores 
                    WHERE CASE '$tipo'
                        WHEN 'processador' THEN COMP_PROC
                        WHEN 'placamae' THEN COMP_PM
                        WHEN 'placagrafica' THEN COMP_PG
                        WHEN 'memoriaram' THEN COMP_MEMORIAR
                        WHEN 'placaderede' THEN COMP_PR
                        WHEN 'rato' THEN COMP_RATO
                        WHEN 'teclado' THEN COMP_TECLADO
                        WHEN 'monitor' THEN COMP_MONITOR
                        WHEN 'cooler' THEN COMP_COOLER
                        WHEN 'disco' THEN COMP_DISCO
                        WHEN 'fonte' THEN COMP_FONTE
                        WHEN 'drivedvd' THEN COMP_DRIVEDVD
                        WHEN 'ventoinha' THEN COMP_VENTOINHA
                        WHEN 'caixa' THEN COMP_CAIXA
                    END != ''
                )
                ORDER BY $campoRef";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        
        $componentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatando a resposta para incluir informações sobre o estado
        $componentesFormatados = [];
        foreach ($componentes as $componente) {
            $estadoDesc = '';
            switch ($componente['estado']) {
                case '1':
                    $estadoDesc = 'Disponível';
                    break;
                case '2':
                    $estadoDesc = 'Em Uso';
                    break;
                case '3':
                    $estadoDesc = 'Manutenção';
                    break;
                case '4':
                    $estadoDesc = 'Sem Reparo';
                    break;
                default:
                    $estadoDesc = 'Desconhecido';
            }
            
            // Apenas adicionar componentes disponíveis
            if ($componente['estado'] == '1') {
                $componentesFormatados[] = [
                    'id' => $componente['id'],
                    'ref' => $componente['ref'],
                    'estado' => $componente['estado'],
                    'estadoDesc' => $estadoDesc
                ];
            }
        }
        
        $response = [
            'status' => 'success',
            'componentes' => $componentesFormatados
        ];
    } else {
        throw new Exception("Tipo de componente não suportado");
    }
} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
}

// Retornar resposta em JSON
header('Content-Type: application/json');
echo json_encode($response);
exit; 