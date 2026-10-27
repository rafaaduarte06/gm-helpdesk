<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['memoriaRef']) || empty($_POST['memoriaRef']) ||
    !isset($_POST['memoriaMarca']) || empty($_POST['memoriaMarca']) ||
    !isset($_POST['memoriaModelo']) || empty($_POST['memoriaModelo']) ||
    !isset($_POST['memoriaTipo']) || empty($_POST['memoriaTipo']) ||
    !isset($_POST['memoriaVelocidade']) || empty($_POST['memoriaVelocidade']) ||
    !isset($_POST['memoriaCor']) || empty($_POST['memoriaCor']) ||
    !isset($_POST['memoriaEstado']) || empty($_POST['memoriaEstado'])) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}

// Validar o estado
$estadosPermitidos = ['1', '3', '4']; // Disponível, Manutenção, Sem Reparo
if (!in_array($_POST['memoriaEstado'], $estadosPermitidos)) {
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit;
}

try {
    $query = "INSERT INTO memoriaram (
                MR_REF,
                MR_MARCA,
                MR_MODELO,
                MR_TIPO,
                MR_VELOCIDADE,
                MR_COR,
                MR_ESTADO,
                MR_COMP
            ) VALUES (
                :referencia,
                :marca,
                :modelo,
                :tipo,
                :velocidade,
                :cor,
                :estado,
                ''
            )";
            
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':referencia', $_POST['memoriaRef'], PDO::PARAM_STR);
    $stmt->bindParam(':marca', $_POST['memoriaMarca'], PDO::PARAM_STR);
    $stmt->bindParam(':modelo', $_POST['memoriaModelo'], PDO::PARAM_STR);
    $stmt->bindParam(':tipo', $_POST['memoriaTipo'], PDO::PARAM_STR);
    $stmt->bindParam(':velocidade', $_POST['memoriaVelocidade'], PDO::PARAM_STR);
    $stmt->bindParam(':cor', $_POST['memoriaCor'], PDO::PARAM_STR);
    $stmt->bindParam(':estado', $_POST['memoriaEstado'], PDO::PARAM_INT);
    
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $mensagem = 'Memória RAM adicionada com sucesso';
        if ($_POST['memoriaEstado'] === '4') {
            $mensagem = 'Memória RAM adicionada e marcada como Sem Reparo';
        }
        echo json_encode([
            'success' => true, 
            'message' => $mensagem,
            'id' => $pdo->lastInsertId()
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao adicionar memória RAM']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar memória RAM: ' . $e->getMessage()]);
}
?> 