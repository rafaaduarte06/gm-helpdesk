<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['placaRef']) || empty($_POST['placaRef']) ||
    !isset($_POST['placaMarca']) || empty($_POST['placaMarca']) ||
    !isset($_POST['placaVelocidade']) || empty($_POST['placaVelocidade']) ||
    !isset($_POST['placaCor']) || empty($_POST['placaCor']) ||
    !isset($_POST['placaEstado']) || empty($_POST['placaEstado'])) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}

// Validar o estado
$estadoPermitido = in_array($_POST['placaEstado'], ['1', '3', '4']);
if (!$estadoPermitido) {
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit;
}

try {
    $query = "INSERT INTO placaderede (
                PR_REF,
                PR_MARCA,
                PR_LR,
                PG_COR,
                PR_ESTADO,
                PR_COMP
            ) VALUES (
                :referencia,
                :marca,
                :velocidade,
                :cor,
                :estado,
                ''
            )";
            
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':referencia', $_POST['placaRef'], PDO::PARAM_STR);
    $stmt->bindParam(':marca', $_POST['placaMarca'], PDO::PARAM_STR);
    $stmt->bindParam(':velocidade', $_POST['placaVelocidade'], PDO::PARAM_STR);
    $stmt->bindParam(':cor', $_POST['placaCor'], PDO::PARAM_STR);
    $stmt->bindParam(':estado', $_POST['placaEstado'], PDO::PARAM_INT);
    
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $message = $_POST['placaEstado'] === '4' 
            ? 'Placa de rede adicionada e marcada como Sem Reparo com sucesso'
            : 'Placa de rede adicionada com sucesso';
        
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'id' => $pdo->lastInsertId()
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao adicionar placa de rede']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar placa de rede: ' . $e->getMessage()]);
}
?> 