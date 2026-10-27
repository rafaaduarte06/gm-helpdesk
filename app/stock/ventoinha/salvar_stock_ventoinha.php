<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

// Log para debug
error_log('Dados recebidos em salvar_stock_ventoinha.php: ' . print_r($_POST, true));

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
    // Verificar se já existe uma ventoinha com a mesma referência
    $queryVerificar = "SELECT COUNT(*) FROM ventoinha WHERE VENT_REF = :referencia";
    $stmtVerificar = $pdo->prepare($queryVerificar);
    $stmtVerificar->bindParam(':referencia', $_POST['ventoinhaRef'], PDO::PARAM_STR);
    $stmtVerificar->execute();
    
    if ($stmtVerificar->fetchColumn() > 0) {
        error_log('Referência já existe: ' . $_POST['ventoinhaRef']);
        echo json_encode(['success' => false, 'message' => 'Já existe uma ventoinha com esta referência']);
        exit;
    }

    // Inserir a nova ventoinha
    $query = "INSERT INTO ventoinha (VENT_REF, VENT_MARCA, VENT_TAM, VENT_COR, VENT_ESTADO, VENT_COMP) 
              VALUES (:referencia, :marca, :tamanho, :cor, :estado, '')";
            
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':referencia', $_POST['ventoinhaRef'], PDO::PARAM_STR);
    $stmt->bindParam(':marca', $_POST['ventoinhaMarca'], PDO::PARAM_INT);
    $stmt->bindParam(':tamanho', $_POST['ventoinhaTamanho'], PDO::PARAM_STR);
    $stmt->bindParam(':cor', $_POST['ventoinhaCor'], PDO::PARAM_INT);
    $stmt->bindParam(':estado', $_POST['ventoinhaEstado'], PDO::PARAM_INT);
    
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $mensagem = $_POST['ventoinhaEstado'] === '4' ? 
            'Ventoinha adicionada e marcada como Sem Reparo com sucesso' : 
            'Ventoinha adicionada com sucesso';
        error_log('Sucesso: ' . $mensagem);
        echo json_encode(['success' => true, 'message' => $mensagem]);
    } else {
        error_log('Nenhuma ventoinha foi adicionada');
        echo json_encode(['success' => false, 'message' => 'Erro ao adicionar ventoinha']);
    }
} catch (PDOException $e) {
    error_log('Erro ao adicionar ventoinha: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar ventoinha: ' . $e->getMessage()]);
}
?> 