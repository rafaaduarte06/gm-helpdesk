<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['nome_interface']) || empty($_POST['nome_interface'])) {
    echo json_encode(['success' => false, 'message' => 'Nome da interface não fornecido']);
    exit;
}

$nomeInterface = $_POST['nome_interface'];

try {
    // Verificar se a interface já existe
    $query = "SELECT DISCO_INTERFACE FROM discointerface WHERE DISCO_INTERFACE = :nome";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeInterface, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Interface já existe', 
            'interface' => [
                'id' => $nomeInterface,
                'nome' => $nomeInterface
            ]
        ]);
        exit;
    }
    
    // Inserir a nova interface
    $query = "INSERT INTO discointerface (DISCO_INTERFACE) VALUES (:nome)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nome', $nomeInterface, PDO::PARAM_STR);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Interface adicionada com sucesso', 
        'interface' => [
            'id' => $nomeInterface,
            'nome' => $nomeInterface
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar interface: ' . $e->getMessage()]);
}
?> 