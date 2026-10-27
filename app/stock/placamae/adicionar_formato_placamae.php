<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['nome_formato']) || empty($_POST['nome_formato'])) {
    echo json_encode(['success' => false, 'message' => 'Formato não fornecido']);
    exit;
}

$nomeFormato = $_POST['nome_formato'];

try {
    // Verificar se o formato já existe
    $query = "SELECT PM_FORMATO FROM pmformato WHERE PM_FORMATO = :nome";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeFormato);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Formato já existe',
            'formato' => ['id' => $nomeFormato, 'nome' => $nomeFormato]
        ]);
        exit;
    }
    
    // Inserir novo formato
    $query = "INSERT INTO pmformato (PM_FORMATO) VALUES (:nome)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeFormato);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Formato adicionado com sucesso',
        'formato' => ['id' => $nomeFormato, 'nome' => $nomeFormato]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar formato: ' . $e->getMessage()]);
}
?> 