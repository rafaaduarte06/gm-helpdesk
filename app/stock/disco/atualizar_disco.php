<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido'
    ]);
    exit;
}

if (!isset($_POST['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID não fornecido'
    ]);
    exit;
}

try {
    // Primeiro, buscar o disco atual para manter o DISCO_COMP
    $queryBusca = "SELECT DISCO_COMP FROM disco WHERE DISCO_ID = :id";
    $stmtBusca = $pdo->prepare($queryBusca);
    $stmtBusca->execute(['id' => $_POST['id']]);
    $discoAtual = $stmtBusca->fetch(PDO::FETCH_ASSOC);

    if (!$discoAtual) {
        echo json_encode([
            'success' => false,
            'message' => 'Disco não encontrado'
        ]);
        exit;
    }

    $query = "UPDATE disco SET
                DISCO_REF = :referencia,
                DISCO_MARCA = :marca,
                DISCO_INTERFACE = :interface,
                DISCO_MEMORIA = :memoria,
                DISCO_TIPO = :tipo,
                DISCO_COR = :cor,
                DISCO_ESTADO = :estado
              WHERE DISCO_ID = :id";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'id' => $_POST['id'],
        'referencia' => $_POST['discoRef'],
        'marca' => $_POST['discoMarca'],
        'interface' => $_POST['discoInterface'],
        'memoria' => $_POST['discoMemoria'],
        'tipo' => $_POST['discoTipo'],
        'cor' => $_POST['discoCor'],
        'estado' => $_POST['discoEstado']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Disco atualizado com sucesso'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar disco: ' . $e->getMessage()
    ]);
}
?> 