<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['nome_tmemoria']) || empty($_POST['nome_tmemoria'])) {
    echo json_encode(['success' => false, 'message' => 'Tipo de memória não fornecido']);
    exit;
}

$nomeTMemoria = $_POST['nome_tmemoria'];

try {
    // Verificar se o tipo de memória já existe
    $query = "SELECT PM_TMEMORIA FROM pmtmemoria WHERE PM_TMEMORIA = :nome";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeTMemoria);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Tipo de memória já existe',
            'tmemoria' => ['id' => $nomeTMemoria, 'nome' => $nomeTMemoria]
        ]);
        exit;
    }
    
    // Inserir novo tipo de memória
    $query = "INSERT INTO pmtmemoria (PM_TMEMORIA) VALUES (:nome)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeTMemoria);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Tipo de memória adicionado com sucesso',
        'tmemoria' => ['id' => $nomeTMemoria, 'nome' => $nomeTMemoria]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar tipo de memória: ' . $e->getMessage()]);
}
?> 