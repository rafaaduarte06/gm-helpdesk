<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

// Log dos dados recebidos
error_log("Dados recebidos para atualização: " . print_r($_POST, true));

// Verificar se todos os campos obrigatórios estão presentes
$required_fields = ['id', 'placaRef', 'placaMarca', 'placaMemoria', 'placaModelo', 'placaCor'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    echo json_encode([
        'success' => false,
        'message' => 'Campos obrigatórios não preenchidos: ' . implode(', ', $missing_fields)
    ]);
    exit;
}

// Validar o estado se estiver presente
if (isset($_POST['placaEstado']) && !empty($_POST['placaEstado'])) {
    $allowed_states = ['1', '3', '4']; // Disponível, Manutenção, Sem Reparo
    if (!in_array($_POST['placaEstado'], $allowed_states)) {
        echo json_encode([
            'success' => false,
            'message' => 'Estado inválido'
        ]);
        exit;
    }
}

try {
    $sql = "UPDATE placagrafica SET 
                PG_REF = :ref,
                PG_MARCA = :marca,
                PG_MEMORIA = :memoria,
                PG_MODELO = :modelo,
                PG_COR = :cor";
    
    // Adicionar o estado à query se estiver presente
    if (isset($_POST['placaEstado']) && !empty($_POST['placaEstado'])) {
        $sql .= ", PG_ESTADO = :estado";
    }
    
    // Não modificamos o PG_COMP aqui pois ele só deve ser alterado quando um computador é associado/desassociado
    
    $sql .= " WHERE PG_ID = :id";

    $stmt = $pdo->prepare($sql);
    
    $stmt->bindParam(':ref', $_POST['placaRef']);
    $stmt->bindParam(':marca', $_POST['placaMarca']);
    $stmt->bindParam(':memoria', $_POST['placaMemoria']);
    $stmt->bindParam(':modelo', $_POST['placaModelo']);
    $stmt->bindParam(':cor', $_POST['placaCor']);
    $stmt->bindParam(':id', $_POST['id']);
    
    if (isset($_POST['placaEstado']) && !empty($_POST['placaEstado'])) {
        $stmt->bindParam(':estado', $_POST['placaEstado']);
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Placa gráfica atualizada com sucesso'
        ]);
    } else {
        throw new Exception('Erro ao atualizar placa gráfica');
    }
} catch (Exception $e) {
    error_log("Erro ao atualizar placa gráfica: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar placa gráfica: ' . $e->getMessage()
    ]);
}
?> 