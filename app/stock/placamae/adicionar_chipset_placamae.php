<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['nome_chipset']) || empty($_POST['nome_chipset'])) {
    echo json_encode(['success' => false, 'message' => 'Chipset não fornecido']);
    exit;
}

$nomeChipset = $_POST['nome_chipset'];

try {
    // Verificar se o chipset já existe
    $query = "SELECT PM_CHIPSET FROM pmchipset WHERE PM_CHIPSET = :nome";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeChipset);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Chipset já existe',
            'chipset' => ['id' => $nomeChipset, 'nome' => $nomeChipset]
        ]);
        exit;
    }
    
    // Inserir novo chipset
    $query = "INSERT INTO pmchipset (PM_CHIPSET) VALUES (:nome)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeChipset);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Chipset adicionado com sucesso',
        'chipset' => ['id' => $nomeChipset, 'nome' => $nomeChipset]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar chipset: ' . $e->getMessage()]);
}
?> 