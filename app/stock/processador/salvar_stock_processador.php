<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

// Log para debug
error_log('Dados recebidos em salvar_stock_processador.php: ' . print_r($_POST, true));

// Estados permitidos
$estadosPermitidos = ['1', '3', '4']; // Disponível, Manutenção, Sem Reparo

// Verificar se o estado é válido
if (!isset($_POST['processadorEstado']) || !in_array($_POST['processadorEstado'], $estadosPermitidos)) {
    error_log('Estado inválido: ' . ($_POST['processadorEstado'] ?? 'não definido'));
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit;
}

// Verificar campos obrigatórios
$camposObrigatorios = [
    'processadorRef' => 'Referência',
    'processadorMarca' => 'Marca',
    'processadorModelo' => 'Modelo',
    'processadorCore' => 'Core',
    'processadorThreads' => 'Threads',
    'processadorSocket' => 'Socket',
    'processadorCor' => 'Cor'
];

$camposFaltantes = [];
foreach ($camposObrigatorios as $campo => $nome) {
    if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
        $camposFaltantes[] = $nome;
    }
}

if (!empty($camposFaltantes)) {
    error_log('Campos obrigatórios faltando: ' . implode(', ', $camposFaltantes));
    echo json_encode(['success' => false, 'message' => 'Campos obrigatórios faltando: ' . implode(', ', $camposFaltantes)]);
    exit;
}

try {
    // Verificar se já existe um processador com a mesma referência
    $queryVerificar = "SELECT COUNT(*) FROM processador WHERE PROC_REF = :referencia";
    $stmtVerificar = $pdo->prepare($queryVerificar);
    $stmtVerificar->bindParam(':referencia', $_POST['processadorRef'], PDO::PARAM_STR);
    $stmtVerificar->execute();
    
    if ($stmtVerificar->fetchColumn() > 0) {
        error_log('Referência já existe: ' . $_POST['processadorRef']);
        echo json_encode(['success' => false, 'message' => 'Já existe um processador com esta referência']);
        exit;
    }

    // Inserir o novo processador
    $query = "INSERT INTO processador (
                PROC_REF,
                PROC_MARCA,
                PROC_MODELO,
                PROC_CORE,
                PROC_THREADS,
                PROC_SOCKET,
                PROC_COR,
                PROC_ESTADO,
                PROC_COMP
            ) VALUES (
                :referencia,
                :marca,
                :modelo,
                :core,
                :threads,
                :socket,
                :cor,
                :estado,
                ''
            )";
            
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':referencia', $_POST['processadorRef'], PDO::PARAM_STR);
    $stmt->bindParam(':marca', $_POST['processadorMarca'], PDO::PARAM_STR);
    $stmt->bindParam(':modelo', $_POST['processadorModelo'], PDO::PARAM_STR);
    $stmt->bindParam(':core', $_POST['processadorCore'], PDO::PARAM_STR);
    $stmt->bindParam(':threads', $_POST['processadorThreads'], PDO::PARAM_STR);
    $stmt->bindParam(':socket', $_POST['processadorSocket'], PDO::PARAM_STR);
    $stmt->bindParam(':cor', $_POST['processadorCor'], PDO::PARAM_STR);
    $stmt->bindParam(':estado', $_POST['processadorEstado'], PDO::PARAM_INT);
    
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $mensagem = $_POST['processadorEstado'] === '4' ? 
            'Processador adicionado e marcado como Sem Reparo com sucesso' : 
            'Processador adicionado com sucesso';
        error_log('Sucesso: ' . $mensagem);
        echo json_encode(['success' => true, 'message' => $mensagem]);
    } else {
        error_log('Nenhum processador foi adicionado');
        echo json_encode(['success' => false, 'message' => 'Erro ao adicionar processador']);
    }
} catch (PDOException $e) {
    error_log('Erro ao adicionar processador: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar processador: ' . $e->getMessage()]);
}
?> 