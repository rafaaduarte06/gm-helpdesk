<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

// Log para debug
error_log('Dados recebidos em atualizar_ventoinha.php: ' . print_r($_POST, true));

// Verificar se o ID foi fornecido
if (!isset($_POST['id']) || empty($_POST['id'])) {
    error_log('ID da ventoinha não fornecido');
    echo json_encode(['success' => false, 'message' => 'ID da ventoinha não fornecido']);
    exit;
}

// Estados permitidos
$estadosPermitidos = ['1', '3', '4']; // Disponível, Manutenção, Sem Reparo

// Verificar se o estado é válido
if (!isset($_POST['ventoinhaEstado']) || !in_array($_POST['ventoinhaEstado'], $estadosPermitidos)) {
    error_log('Estado inválido: ' . ($_POST['ventoinhaEstado'] ?? 'não definido'));
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit;
}

// Verificar campos obrigatórios
$camposObrigatorios = [
    'ventoinhaRef' => 'Referência',
    'ventoinhaMarca' => 'Marca',
    'ventoinhaTamanho' => 'Tamanho',
    'ventoinhaCor' => 'Cor'
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
    // Verificar se a ventoinha existe e seu estado atual
    $queryVerificar = "SELECT VENT_ESTADO FROM ventoinha WHERE VENT_ID = :id";
    $stmtVerificar = $pdo->prepare($queryVerificar);
    $stmtVerificar->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
    $stmtVerificar->execute();
    
    $ventoinhaAtual = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
    
    if (!$ventoinhaAtual) {
        error_log('Ventoinha não encontrada: ID ' . $_POST['id']);
        echo json_encode(['success' => false, 'message' => 'Ventoinha não encontrada']);
        exit;
    }
    
    // Se a ventoinha já está marcada como "Sem Reparo", não permitir atualização
    if ($ventoinhaAtual['VENT_ESTADO'] === '4') {
        error_log('Tentativa de atualizar ventoinha marcada como Sem Reparo: ID ' . $_POST['id']);
        echo json_encode(['success' => false, 'message' => 'Não é possível atualizar uma ventoinha marcada como Sem Reparo']);
        exit;
    }

    // Atualizar a ventoinha
    $query = "UPDATE ventoinha SET 
                VENT_REF = :referencia,
                VENT_MARCA = :marca,
                VENT_TAM = :tamanho,
                VENT_COR = :cor,
                VENT_ESTADO = :estado
              WHERE VENT_ID = :id";
            
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':referencia', $_POST['ventoinhaRef'], PDO::PARAM_STR);
    $stmt->bindParam(':marca', $_POST['ventoinhaMarca'], PDO::PARAM_INT);
    $stmt->bindParam(':tamanho', $_POST['ventoinhaTamanho'], PDO::PARAM_STR);
    $stmt->bindParam(':cor', $_POST['ventoinhaCor'], PDO::PARAM_INT);
    $stmt->bindParam(':estado', $_POST['ventoinhaEstado'], PDO::PARAM_INT);
    $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
    
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $mensagem = $_POST['ventoinhaEstado'] === '4' ? 
            'Ventoinha marcada como Sem Reparo com sucesso' : 
            'Ventoinha atualizada com sucesso';
        error_log('Sucesso: ' . $mensagem . ' (ID: ' . $_POST['id'] . ')');
        echo json_encode(['success' => true, 'message' => $mensagem]);
    } else {
        error_log('Nenhuma alteração realizada para a ventoinha ID ' . $_POST['id']);
        echo json_encode(['success' => false, 'message' => 'Nenhuma alteração realizada']);
    }
} catch (PDOException $e) {
    error_log('Erro ao atualizar ventoinha: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar ventoinha: ' . $e->getMessage()]);
}
?> 