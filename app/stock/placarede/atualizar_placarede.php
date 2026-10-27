<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['id']) || empty($_POST['id']) ||
    !isset($_POST['placaRef']) || empty($_POST['placaRef']) ||
    !isset($_POST['placaMarca']) || empty($_POST['placaMarca']) ||
    !isset($_POST['placaVelocidade']) || empty($_POST['placaVelocidade']) ||
    !isset($_POST['placaCor']) || empty($_POST['placaCor'])) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}

try {
    // Verificar o estado atual da placa
    $queryEstado = "SELECT PR_ESTADO FROM placaderede WHERE PR_ID = :id";
    $stmtEstado = $pdo->prepare($queryEstado);
    $stmtEstado->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
    $stmtEstado->execute();
    $estadoAtual = $stmtEstado->fetchColumn();

    // Se a placa já estiver marcada como "Sem Reparo", não permitir edição
    if ($estadoAtual === '4') {
        echo json_encode(['success' => false, 'message' => 'Não é possível editar uma placa de rede marcada como Sem Reparo']);
        exit;
    }

    // Construir a query de atualização
    $query = "UPDATE placaderede SET 
                PR_REF = :referencia,
                PR_MARCA = :marca,
                PR_LR = :velocidade,
                PG_COR = :cor";

    // Se um estado foi fornecido, validar e incluir na atualização
    if (isset($_POST['placaEstado']) && !empty($_POST['placaEstado'])) {
        $estadoPermitido = in_array($_POST['placaEstado'], ['1', '3', '4']);
        if (!$estadoPermitido) {
            echo json_encode(['success' => false, 'message' => 'Estado inválido']);
            exit;
        }
        $query .= ", PR_ESTADO = :estado";
    }

    $query .= " WHERE PR_ID = :id";
            
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':referencia', $_POST['placaRef'], PDO::PARAM_STR);
    $stmt->bindParam(':marca', $_POST['placaMarca'], PDO::PARAM_STR);
    $stmt->bindParam(':velocidade', $_POST['placaVelocidade'], PDO::PARAM_STR);
    $stmt->bindParam(':cor', $_POST['placaCor'], PDO::PARAM_STR);
    $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);

    if (isset($_POST['placaEstado']) && !empty($_POST['placaEstado'])) {
        $stmt->bindParam(':estado', $_POST['placaEstado'], PDO::PARAM_STR);
    }
    
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $message = isset($_POST['placaEstado']) && $_POST['placaEstado'] === '4' 
            ? 'Placa de rede marcada como Sem Reparo com sucesso'
            : 'Placa de rede atualizada com sucesso';
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nenhuma alteração realizada']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar placa de rede: ' . $e->getMessage()]);
}
?> 