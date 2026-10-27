<?php
session_start();
require_once('../../ligacao_base_dados.php');

header('Content-Type: application/json');

// Log para debug
error_log('Dados recebidos em atualizar_processador.php: ' . print_r($_POST, true));

if (!isset($_POST['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID do processador não fornecido'
    ]);
    exit;
}

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
    // Primeiro, buscar o PROC_COMP atual
    $queryBusca = "SELECT PROC_COMP FROM processador WHERE PROC_ID = :id";
    $stmtBusca = $pdo->prepare($queryBusca);
    $stmtBusca->execute(['id' => $_POST['id']]);
    $procAtual = $stmtBusca->fetch(PDO::FETCH_ASSOC);

    if (!$procAtual) {
        echo json_encode([
            'success' => false,
            'message' => 'Processador não encontrado'
        ]);
        exit;
    }

    $query = "UPDATE processador SET
                PROC_REF = :ref,
                PROC_MARCA = :marca,
                PROC_MODELO = :modelo,
                PROC_CORE = :core,
                PROC_THREADS = :threads,
                PROC_SOCKET = :socket,
                PROC_COR = :cor,
                PROC_ESTADO = :estado
            WHERE PROC_ID = :id";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'id' => $_POST['id'],
        'ref' => $_POST['processadorRef'],
        'marca' => $_POST['processadorMarca'],
        'modelo' => $_POST['processadorModelo'],
        'core' => $_POST['processadorCore'],
        'threads' => $_POST['processadorThreads'],
        'socket' => $_POST['processadorSocket'],
        'cor' => $_POST['processadorCor'],
        'estado' => $_POST['processadorEstado']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Processador atualizado com sucesso!'
    ]);
} catch (PDOException $e) {
    error_log('Erro ao atualizar processador: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar processador: ' . $e->getMessage()
    ]);
}
?> 