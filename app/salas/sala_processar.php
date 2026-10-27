<?php
// Habilitar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar a sessão antes de qualquer output
session_start();

// Verificar se o usuário está logado e é um técnico
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    header('Location: ../login.php');
    exit;
}

// Incluir arquivo de conexão com o banco de dados
require_once('../ligacao_base_dados.php');

// Verificar se a conexão foi estabelecida
if (!isset($pdo) || $pdo === null) {
    die("Erro: Não foi possível conectar ao banco de dados. Verifique o arquivo ligacao_base_dados.php");
}

// Arrays para mapear tabelas e campos
$componentesInfo = [
    'COMPUTADOR' => ['tabela' => 'computadores', 'id' => 'COMP_ID', 'ref' => 'COMP_REF', 'estado' => 'COMP_ESTADO', 'sala' => 'COMP_SALA', 'estado_em_uso' => '2', 'estado_disponivel' => '1'],
    'RATO' => ['tabela' => 'ratos', 'id' => 'RATOS_ID', 'ref' => 'RATOS_REF', 'estado' => 'RATOS_ESTADO', 'sala' => 'RATOS_SALA', 'comp' => 'RATOS_COMP', 'estado_em_uso' => '2', 'estado_disponivel' => '1'],
    'TECLADO' => ['tabela' => 'teclados', 'id' => 'TECLADO_ID', 'ref' => 'TECLADO_REF', 'estado' => 'TECLADO_ESTADO', 'sala' => 'TECLADO_SALA', 'comp' => 'TECLADO_COMP', 'estado_em_uso' => '2', 'estado_disponivel' => '1'],
    'MONITOR' => ['tabela' => 'monitor', 'id' => 'MONITOR_ID', 'ref' => 'MONITOR_REF', 'estado' => 'MONITOR_ESTADO', 'sala' => 'MONITOR_SALA', 'comp' => 'MONITOR_COMP', 'estado_em_uso' => '2', 'estado_disponivel' => '1']
];

// Verificar a ação solicitada
$action = isset($_POST['action']) ? $_POST['action'] : '';
$salaId = isset($_POST['sala_id']) ? intval($_POST['sala_id']) : 0;

// Buscar informações da sala
try {
    $stmt = $pdo->prepare("SELECT * FROM salas WHERE id_sala = ?");
    $stmt->execute([$salaId]);
    $sala = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sala) {
        $_SESSION['error_message'] = "Sala não encontrada.";
        header('Location: salas.php');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erro ao buscar informações da sala: " . $e->getMessage();
    header('Location: salas.php');
    exit;
}

// Processar as diferentes ações
switch ($action) {
    case 'add_component':
        adicionarComponente();
        break;
    case 'edit_sala':
        editarSala();
        break;
    case 'remove_component':
        removerComponente();
        break;
    default:
        $_SESSION['error_message'] = "Ação inválida.";
        header('Location: detalhessalas.php?id=' . $salaId);
        exit;
}

// Função para adicionar componente à sala
function adicionarComponente() {
    global $pdo, $salaId, $sala, $componentesInfo;
    
    $componenteTipo = isset($_POST['componente_tipo']) ? $_POST['componente_tipo'] : '';
    
    // Verificar se o tipo de componente é válido
    if (!isset($componentesInfo[$componenteTipo])) {
        $_SESSION['error_message'] = "Tipo de componente inválido.";
        header('Location: detalhessalas.php?id=' . $salaId);
        exit;
    }
    
    $info = $componentesInfo[$componenteTipo];
    $componenteId = '';
    
    // Obter o ID do componente com base no tipo
    switch ($componenteTipo) {
        case 'COMPUTADOR':
            $componenteId = isset($_POST['computador_id']) ? $_POST['computador_id'] : '';
            break;
        case 'RATO':
            $componenteId = isset($_POST['rato_id']) ? $_POST['rato_id'] : '';
            break;
        case 'TECLADO':
            $componenteId = isset($_POST['teclado_id']) ? $_POST['teclado_id'] : '';
            break;
        case 'MONITOR':
            $componenteId = isset($_POST['monitor_id']) ? $_POST['monitor_id'] : '';
            break;
    }
    
    if (empty($componenteId)) {
        $_SESSION['error_message'] = "Componente não selecionado.";
        header('Location: detalhessalas.php?id=' . $salaId);
        exit;
    }
    
    try {
        // Verificar se o componente existe e está disponível
        $stmt = $pdo->prepare("SELECT * FROM " . $info['tabela'] . " WHERE " . $info['id'] . " = ? AND " . $info['estado'] . " = '1'");
        $stmt->execute([$componenteId]);
        $componente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$componente) {
            $_SESSION['error_message'] = "Componente não encontrado ou não está disponível.";
            header('Location: detalhessalas.php?id=' . $salaId);
            exit;
        }
        
        // Se for um componente que pode estar em um computador, verificar se está livre
        if ($componenteTipo !== 'COMPUTADOR' && isset($info['comp'])) {
            if (!empty($componente[$info['comp']])) {
                $_SESSION['error_message'] = "Este componente está associado a um computador e não pode ser adicionado individualmente.";
                header('Location: detalhessalas.php?id=' . $salaId);
                exit;
            }
        }
        
        // Atualizar o componente com a sala e estado "em uso"
        $sql = "UPDATE " . $info['tabela'] . " SET " . $info['sala'] . " = ?, " . $info['estado'] . " = ? WHERE " . $info['id'] . " = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sala['sala'], $info['estado_em_uso'], $componenteId]);
        
        // Se for um computador, também atualizar todos os seus componentes
        if ($componenteTipo === 'COMPUTADOR') {
            // Atualizar rato
            if (!empty($componente['COMP_RATO'])) {
                $stmt = $pdo->prepare("UPDATE ratos SET RATOS_SALA = ?, RATOS_ESTADO = ? WHERE RATOS_ID = ?");
                $stmt->execute([$sala['sala'], '2', $componente['COMP_RATO']]);
            }
            
            // Atualizar teclado
            if (!empty($componente['COMP_TECLADO'])) {
                $stmt = $pdo->prepare("UPDATE teclados SET TECLADO_SALA = ?, TECLADO_ESTADO = ? WHERE TECLADO_ID = ?");
                $stmt->execute([$sala['sala'], '2', $componente['COMP_TECLADO']]);
            }
            
            // Atualizar monitor
            if (!empty($componente['COMP_MONITOR'])) {
                $stmt = $pdo->prepare("UPDATE monitor SET MONITOR_SALA = ?, MONITOR_ESTADO = ? WHERE MONITOR_ID = ?");
                $stmt->execute([$sala['sala'], '2', $componente['COMP_MONITOR']]);
            }
            
            // Atualizar placa mãe, processador, etc.
            $outrosComponentes = [
                'COMP_PM' => ['tabela' => 'placamae', 'id' => 'PM_ID', 'sala' => 'PM_SALA', 'estado' => 'PM_ESTADO'],
                'COMP_PROC' => ['tabela' => 'processador', 'id' => 'PROC_ID', 'sala' => 'PROC_SALA', 'estado' => 'PROC_ESTADO'],
                'COMP_COOLER' => ['tabela' => 'cooler', 'id' => 'COO_ID', 'sala' => 'COO_SALA', 'estado' => 'COO_ESTADO'],
                'COMP_PG' => ['tabela' => 'placagrafica', 'id' => 'PG_ID', 'sala' => 'PG_SALA', 'estado' => 'PG_ESTADO'],
                'COMP_DISCO' => ['tabela' => 'disco', 'id' => 'DISCO_ID', 'sala' => 'DISCO_SALA', 'estado' => 'DISCO_ESTADO'],
                'COMP_FONTE' => ['tabela' => 'fonte', 'id' => 'FONTE_ID', 'sala' => 'FONTE_SALA', 'estado' => 'FONTE_ESTADO'],
                'COMP_MEMORIAR' => ['tabela' => 'memoriaram', 'id' => 'MR_ID', 'sala' => 'MR_SALA', 'estado' => 'MR_ESTADO'],
                'COMP_DRIVEDVD' => ['tabela' => 'drivedvd', 'id' => 'DRVD_ID', 'sala' => 'DRVD_SALA', 'estado' => 'DRVD_ESTADO'],
                'COMP_PR' => ['tabela' => 'placaderede', 'id' => 'PR_ID', 'sala' => 'PR_SALA', 'estado' => 'PR_ESTADO'],
                'COMP_VENTOINHA' => ['tabela' => 'ventoinha', 'id' => 'VENT_ID', 'sala' => 'VENT_SALA', 'estado' => 'VENT_ESTADO'],
                'COMP_CAIXA' => ['tabela' => 'caixa', 'id' => 'CAIXA_ID', 'sala' => 'CAIXA_SALA', 'estado' => 'CAIXA_ESTADO']
            ];
            
            foreach ($outrosComponentes as $compField => $compInfo) {
                if (!empty($componente[$compField])) {
                    $stmt = $pdo->prepare("UPDATE " . $compInfo['tabela'] . " SET " . $compInfo['sala'] . " = ?, " . $compInfo['estado'] . " = ? WHERE " . $compInfo['id'] . " = ?");
                    $stmt->execute([$sala['sala'], '2', $componente[$compField]]);
                }
            }
        }
        
        $_SESSION['success_message'] = ucfirst(strtolower($componenteTipo)) . " adicionado à sala com sucesso.";
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erro ao adicionar componente: " . $e->getMessage();
    }
    
    header('Location: detalhessalas.php?id=' . $salaId);
    exit;
}

// Função para editar sala
function editarSala() {
    global $pdo, $salaId, $sala;
    
    $nomeSala = isset($_POST['nome_sala']) ? trim($_POST['nome_sala']) : '';
    
    if (empty($nomeSala)) {
        $_SESSION['error_message'] = "Nome da sala não pode estar vazio.";
        header('Location: detalhessalas.php?id=' . $salaId);
        exit;
    }
    
    try {
        // Verificar se já existe uma sala com esse nome (exceto a sala atual)
        $stmt = $pdo->prepare("SELECT * FROM salas WHERE sala = ? AND id_sala != ?");
        $stmt->execute([$nomeSala, $salaId]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['error_message'] = "Já existe uma sala com este nome.";
            header('Location: detalhessalas.php?id=' . $salaId);
            exit;
        }
        
        // Obter o nome atual da sala para atualizar os componentes
        $nomeAtual = $sala['sala'];
        
        // Atualizar o nome da sala
        $stmt = $pdo->prepare("UPDATE salas SET sala = ? WHERE id_sala = ?");
        $stmt->execute([$nomeSala, $salaId]);
        
        // Atualizar todos os componentes que estavam na sala com o nome antigo
        $tabelas = [
            ['nome' => 'computadores', 'campo' => 'COMP_SALA'],
            ['nome' => 'ratos', 'campo' => 'RATOS_SALA'],
            ['nome' => 'teclados', 'campo' => 'TECLADO_SALA'],
            ['nome' => 'monitor', 'campo' => 'MONITOR_SALA'],
            ['nome' => 'placamae', 'campo' => 'PM_SALA'],
            ['nome' => 'processador', 'campo' => 'PROC_SALA'],
            ['nome' => 'cooler', 'campo' => 'COO_SALA'],
            ['nome' => 'placagrafica', 'campo' => 'PG_SALA'],
            ['nome' => 'disco', 'campo' => 'DISCO_SALA'],
            ['nome' => 'fonte', 'campo' => 'FONTE_SALA'],
            ['nome' => 'memoriaram', 'campo' => 'MR_SALA'],
            ['nome' => 'drivedvd', 'campo' => 'DRVD_SALA'],
            ['nome' => 'placaderede', 'campo' => 'PR_SALA'],
            ['nome' => 'ventoinha', 'campo' => 'VENT_SALA'],
            ['nome' => 'caixa', 'campo' => 'CAIXA_SALA']
        ];
        
        foreach ($tabelas as $tabela) {
            $stmt = $pdo->prepare("UPDATE " . $tabela['nome'] . " SET " . $tabela['campo'] . " = ? WHERE " . $tabela['campo'] . " = ?");
            $stmt->execute([$nomeSala, $nomeAtual]);
        }
        
        $_SESSION['success_message'] = "Sala atualizada com sucesso.";
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erro ao editar sala: " . $e->getMessage();
    }
    
    header('Location: detalhessalas.php?id=' . $salaId);
    exit;
}

// Função para remover componente da sala
function removerComponente() {
    global $pdo, $salaId, $sala, $componentesInfo;
    
    $componenteTipo = isset($_POST['componente_tipo']) ? $_POST['componente_tipo'] : '';
    $componenteId = isset($_POST['componente_id']) ? $_POST['componente_id'] : '';
    
    // Verificar se o tipo de componente é válido
    if (!isset($componentesInfo[$componenteTipo])) {
        $_SESSION['error_message'] = "Tipo de componente inválido.";
        header('Location: detalhessalas.php?id=' . $salaId);
        exit;
    }
    
    if (empty($componenteId)) {
        $_SESSION['error_message'] = "Componente não selecionado.";
        header('Location: detalhessalas.php?id=' . $salaId);
        exit;
    }
    
    $info = $componentesInfo[$componenteTipo];
    
    try {
        // Verificar se o componente existe e está na sala
        $stmt = $pdo->prepare("SELECT * FROM " . $info['tabela'] . " WHERE " . $info['id'] . " = ? AND " . $info['sala'] . " = ?");
        $stmt->execute([$componenteId, $sala['sala']]);
        $componente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$componente) {
            $_SESSION['error_message'] = "Componente não encontrado ou não está na sala.";
            header('Location: detalhessalas.php?id=' . $salaId);
            exit;
        }
        
        // Remover da sala (definir sala como vazia) e alterar para estado "Disponível"
        $sql = "UPDATE " . $info['tabela'] . " SET " . $info['sala'] . " = '', " . $info['estado'] . " = ? WHERE " . $info['id'] . " = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$info['estado_disponivel'], $componenteId]);
        
        // Se for um computador, também atualizar todos os seus componentes
        if ($componenteTipo === 'COMPUTADOR') {
            // Atualizar rato
            if (!empty($componente['COMP_RATO'])) {
                $stmt = $pdo->prepare("UPDATE ratos SET RATOS_SALA = '', RATOS_ESTADO = '1' WHERE RATOS_ID = ?");
                $stmt->execute([$componente['COMP_RATO']]);
            }
            
            // Atualizar teclado
            if (!empty($componente['COMP_TECLADO'])) {
                $stmt = $pdo->prepare("UPDATE teclados SET TECLADO_SALA = '', TECLADO_ESTADO = '1' WHERE TECLADO_ID = ?");
                $stmt->execute([$componente['COMP_TECLADO']]);
            }
            
            // Atualizar monitor
            if (!empty($componente['COMP_MONITOR'])) {
                $stmt = $pdo->prepare("UPDATE monitor SET MONITOR_SALA = '', MONITOR_ESTADO = '1' WHERE MONITOR_ID = ?");
                $stmt->execute([$componente['COMP_MONITOR']]);
            }
            
            // Atualizar placa mãe, processador, etc.
            $outrosComponentes = [
                'COMP_PM' => ['tabela' => 'placamae', 'id' => 'PM_ID', 'sala' => 'PM_SALA', 'estado' => 'PM_ESTADO'],
                'COMP_PROC' => ['tabela' => 'processador', 'id' => 'PROC_ID', 'sala' => 'PROC_SALA', 'estado' => 'PROC_ESTADO'],
                'COMP_COOLER' => ['tabela' => 'cooler', 'id' => 'COO_ID', 'sala' => 'COO_SALA', 'estado' => 'COO_ESTADO'],
                'COMP_PG' => ['tabela' => 'placagrafica', 'id' => 'PG_ID', 'sala' => 'PG_SALA', 'estado' => 'PG_ESTADO'],
                'COMP_DISCO' => ['tabela' => 'disco', 'id' => 'DISCO_ID', 'sala' => 'DISCO_SALA', 'estado' => 'DISCO_ESTADO'],
                'COMP_FONTE' => ['tabela' => 'fonte', 'id' => 'FONTE_ID', 'sala' => 'FONTE_SALA', 'estado' => 'FONTE_ESTADO'],
                'COMP_MEMORIAR' => ['tabela' => 'memoriaram', 'id' => 'MR_ID', 'sala' => 'MR_SALA', 'estado' => 'MR_ESTADO'],
                'COMP_DRIVEDVD' => ['tabela' => 'drivedvd', 'id' => 'DRVD_ID', 'sala' => 'DRVD_SALA', 'estado' => 'DRVD_ESTADO'],
                'COMP_PR' => ['tabela' => 'placaderede', 'id' => 'PR_ID', 'sala' => 'PR_SALA', 'estado' => 'PR_ESTADO'],
                'COMP_VENTOINHA' => ['tabela' => 'ventoinha', 'id' => 'VENT_ID', 'sala' => 'VENT_SALA', 'estado' => 'VENT_ESTADO'],
                'COMP_CAIXA' => ['tabela' => 'caixa', 'id' => 'CAIXA_ID', 'sala' => 'CAIXA_SALA', 'estado' => 'CAIXA_ESTADO']
            ];
            
            foreach ($outrosComponentes as $compField => $compInfo) {
                if (!empty($componente[$compField])) {
                    $stmt = $pdo->prepare("UPDATE " . $compInfo['tabela'] . " SET " . $compInfo['sala'] . " = '', " . $compInfo['estado'] . " = '1' WHERE " . $compInfo['id'] . " = ?");
                    $stmt->execute([$componente[$compField]]);
                }
            }
        }
        
        $_SESSION['success_message'] = ucfirst(strtolower($componenteTipo)) . " removido da sala com sucesso.";
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erro ao remover componente: " . $e->getMessage();
    }
    
    header('Location: detalhessalas.php?id=' . $salaId);
    exit;
} 