<?php
// Habilitar exibição de erros (apenas para desenvolvimento)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Função para registrar logs
function logError($message, $data = []) {
    error_log(date('Y-m-d H:i:s') . " - " . $message . " - " . json_encode($data));
}

// Iniciar sessão e verificar autenticação
session_start();
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Não autorizado']);
    exit;
}

// Incluir conexão com o banco de dados
require_once('../../ligacao_base_dados.php');

// Se for uma requisição GET, retornar o HTML do modal
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    include 'modal_adicionar_computador.php';
    exit;
}

// Se for uma requisição POST, processar a adição do computador
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar dados obrigatórios
        if (empty($_POST['comp_ref']) || empty($_POST['comp_estado'])) {
            throw new Exception('Referência e estado são obrigatórios');
        }

        // Verificar se o estado é válido (não pode ser "Em Uso")
        if ($_POST['comp_estado'] == '2') { // 2 = Em Uso
            throw new Exception('Não é possível criar um computador com o estado "Em Uso"');
        }

        // Iniciar transação
        $pdo->beginTransaction();
        logError("Iniciando transação para adicionar computador", $_POST);

        // Gerar ID único para o computador
        $compId = uniqid('COMP_');

        // Preparar a query de inserção
        $sql = "INSERT INTO computadores (
            COMP_ID, COMP_REF, COMP_ESTADO, 
            COMP_RATO, COMP_TECLADO, COMP_MONITOR, COMP_PM, COMP_PROC, 
            COMP_COOLER, COMP_PG, COMP_DISCO, COMP_FONTE, COMP_MEMORIAR, 
            COMP_DRIVEDVD, COMP_PR, COMP_VENTOINHA, COMP_CAIXA, COMP_SALA
        ) VALUES (
            :id, :ref, :estado,
            '', '', '', '', '',
            '', '', '', '', '',
            '', '', '', '', ''
        )";

        $stmt = $pdo->prepare($sql);
        
        // Executar a query com os valores básicos
        $params = [
            ':id' => $compId,
            ':ref' => $_POST['comp_ref'],
            ':estado' => $_POST['comp_estado']
        ];
        
        $stmt->execute($params);
        logError("Computador inserido com sucesso", ['comp_id' => $compId]);

        // Mapeamento dos campos de componentes
        $componentesParaAtualizar = [
            'rato' => ['campo' => 'COMP_RATO', 'tabela' => 'ratos', 'id' => 'RATOS_ID', 'comp' => 'RATOS_COMP'],
            'teclado' => ['campo' => 'COMP_TECLADO', 'tabela' => 'teclados', 'id' => 'TECLADO_ID', 'comp' => 'TECLADO_COMP'],
            'monitor' => ['campo' => 'COMP_MONITOR', 'tabela' => 'monitor', 'id' => 'MONITOR_ID', 'comp' => 'MONITOR_COMP'],
            'pm' => ['campo' => 'COMP_PM', 'tabela' => 'placamae', 'id' => 'PM_ID', 'comp' => 'PM_COMP'],
            'proc' => ['campo' => 'COMP_PROC', 'tabela' => 'processador', 'id' => 'PROC_ID', 'comp' => 'PROC_COMP'],
            'cooler' => ['campo' => 'COMP_COOLER', 'tabela' => 'cooler', 'id' => 'COO_ID', 'comp' => 'COO_COMP'],
            'pg' => ['campo' => 'COMP_PG', 'tabela' => 'placagrafica', 'id' => 'PG_ID', 'comp' => 'PG_COMP'],
            'disco' => ['campo' => 'COMP_DISCO', 'tabela' => 'disco', 'id' => 'DISCO_ID', 'comp' => 'DISCO_COMP'],
            'fonte' => ['campo' => 'COMP_FONTE', 'tabela' => 'fonte', 'id' => 'FONTE_ID', 'comp' => 'FONTE_COMP'],
            'memoriar' => ['campo' => 'COMP_MEMORIAR', 'tabela' => 'memoriaram', 'id' => 'MR_ID', 'comp' => 'MR_COMP'],
            'drivedvd' => ['campo' => 'COMP_DRIVEDVD', 'tabela' => 'drivedvd', 'id' => 'DRVD_ID', 'comp' => 'DRVD_COMP'],
            'pr' => ['campo' => 'COMP_PR', 'tabela' => 'placaderede', 'id' => 'PR_ID', 'comp' => 'PR_COMP'],
            'ventoinha' => ['campo' => 'COMP_VENTOINHA', 'tabela' => 'ventoinha', 'id' => 'VENT_ID', 'comp' => 'VENT_COMP'],
            'caixa' => ['campo' => 'COMP_CAIXA', 'tabela' => 'caixa', 'id' => 'CAIXA_ID', 'comp' => 'CAIXA_COMP']
        ];

        // Atualizar cada componente selecionado
        foreach ($componentesParaAtualizar as $tipo => $info) {
            if (!empty($_POST["comp_$tipo"])) {
                // Atualizar o computador com o ID do componente
                $sql = "UPDATE computadores SET {$info['campo']} = ? WHERE COMP_ID = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_POST["comp_$tipo"], $compId]);

                // Atualizar o componente com a referência do computador
                $sql = "UPDATE {$info['tabela']} SET {$info['comp']} = ? WHERE {$info['id']} = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_POST['comp_ref'], $_POST["comp_$tipo"]]);
                
                // Se o computador está sendo criado com estado de manutenção (3),
                // atualizar também o estado do componente para manutenção
                if ($_POST['comp_estado'] == '3') {
                    // Obter o nome do campo de estado na tabela do componente
                    $estadoField = str_replace('_COMP', '_ESTADO', $info['comp']);
                    
                    // Atualizar o estado do componente para manutenção (3)
                    $sql = "UPDATE {$info['tabela']} SET $estadoField = 3 WHERE {$info['id']} = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$_POST["comp_$tipo"]]);
                    
                    logError("Componente {$tipo} atualizado para estado de manutenção", [
                        'componente_id' => $_POST["comp_$tipo"],
                        'tabela' => $info['tabela']
                    ]);
                }
            }
        }

        // Confirmar transação
        $pdo->commit();
        logError("Transação concluída com sucesso");

        // Retornar sucesso sem mensagem adicional
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success']);

    } catch (Exception $e) {
        // Em caso de erro, reverter transação
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
            logError("Transação revertida devido a erro");
        }

        // Log detalhado do erro
        logError("Erro ao adicionar computador", [
            'erro' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'post' => $_POST
        ]);

        // Retornar erro
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    exit;
} 