<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if (!isset($_POST['id']) || empty($_POST['id']) ||
    !isset($_POST['memoriaRef']) || empty($_POST['memoriaRef']) ||
    !isset($_POST['memoriaMarca']) || empty($_POST['memoriaMarca']) ||
    !isset($_POST['memoriaModelo']) || empty($_POST['memoriaModelo']) ||
    !isset($_POST['memoriaTipo']) || empty($_POST['memoriaTipo']) ||
    !isset($_POST['memoriaVelocidade']) || empty($_POST['memoriaVelocidade']) ||
    !isset($_POST['memoriaCor']) || empty($_POST['memoriaCor'])) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}

try {
    // Primeiro, verificar o estado atual da memória
    $query = "SELECT MR_ESTADO FROM memoriaram WHERE MR_ID = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
    $stmt->execute();
    
    $memoriaAtual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Se a memória já estiver marcada como "Sem Reparo", não permitir edição
    if ($memoriaAtual && $memoriaAtual['MR_ESTADO'] === '4') {
        echo json_encode(['success' => false, 'message' => 'Não é possível editar uma memória RAM marcada como Sem Reparo']);
        exit;
    }

    // Se um estado foi fornecido, validar se é um dos permitidos
    $estadoPermitido = true;
    if (isset($_POST['memoriaEstado']) && !empty($_POST['memoriaEstado'])) {
        $estadosPermitidos = ['1', '3', '4']; // Disponível, Manutenção, Sem Reparo
        if (!in_array($_POST['memoriaEstado'], $estadosPermitidos)) {
            $estadoPermitido = false;
        }
    }

    // Construir a query de atualização
    $query = "UPDATE memoriaram SET 
                MR_REF = :referencia,
                MR_MARCA = :marca,
                MR_MODELO = :modelo,
                MR_TIPO = :tipo,
                MR_VELOCIDADE = :velocidade,
                MR_COR = :cor";
    
    // Adicionar o estado à query apenas se for fornecido e permitido
    if (isset($_POST['memoriaEstado']) && !empty($_POST['memoriaEstado']) && $estadoPermitido) {
        $query .= ", MR_ESTADO = :estado";
    }
    
    $query .= " WHERE MR_ID = :id";
            
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':referencia', $_POST['memoriaRef'], PDO::PARAM_STR);
    $stmt->bindParam(':marca', $_POST['memoriaMarca'], PDO::PARAM_STR);
    $stmt->bindParam(':modelo', $_POST['memoriaModelo'], PDO::PARAM_STR);
    $stmt->bindParam(':tipo', $_POST['memoriaTipo'], PDO::PARAM_STR);
    $stmt->bindParam(':velocidade', $_POST['memoriaVelocidade'], PDO::PARAM_STR);
    $stmt->bindParam(':cor', $_POST['memoriaCor'], PDO::PARAM_STR);
    $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
    
    // Bind do estado apenas se for fornecido e permitido
    if (isset($_POST['memoriaEstado']) && !empty($_POST['memoriaEstado']) && $estadoPermitido) {
        $stmt->bindParam(':estado', $_POST['memoriaEstado'], PDO::PARAM_INT);
    }
    
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $mensagem = 'Memória RAM atualizada com sucesso';
        if (isset($_POST['memoriaEstado']) && $_POST['memoriaEstado'] === '4') {
            $mensagem = 'Memória RAM marcada como Sem Reparo';
        }
        echo json_encode(['success' => true, 'message' => $mensagem]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nenhuma alteração realizada']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar memória RAM: ' . $e->getMessage()]);
}
?> 