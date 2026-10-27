<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

// Log dos dados recebidos
error_log("Dados recebidos para inserção: " . print_r($_POST, true));

// Verificar se todos os campos obrigatórios estão presentes
$required_fields = ['placaRef', 'placaMarca', 'placaMemoria', 'placaModelo', 'placaCor', 'placaEstado'];
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

// Validar o estado
$allowed_states = ['1', '3', '4']; // Disponível, Manutenção, Sem Reparo
if (!in_array($_POST['placaEstado'], $allowed_states)) {
    echo json_encode([
        'success' => false,
        'message' => 'Estado inválido'
    ]);
    exit;
}

try {
    $sql = "INSERT INTO placagrafica (
                PG_REF,
                PG_MARCA,
                PG_MEMORIA,
                PG_MODELO,
                PG_COR,
                PG_ESTADO,
                PG_COMP
            ) VALUES (
                :ref,
                :marca,
                :memoria,
                :modelo,
                :cor,
                :estado,
                ''
            )";

    $stmt = $pdo->prepare($sql);
    
    $stmt->bindParam(':ref', $_POST['placaRef']);
    $stmt->bindParam(':marca', $_POST['placaMarca']);
    $stmt->bindParam(':memoria', $_POST['placaMemoria']);
    $stmt->bindParam(':modelo', $_POST['placaModelo']);
    $stmt->bindParam(':cor', $_POST['placaCor']);
    $stmt->bindParam(':estado', $_POST['placaEstado']);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Placa gráfica adicionada com sucesso'
        ]);
    } else {
        throw new Exception('Erro ao adicionar placa gráfica');
    }
} catch (Exception $e) {
    error_log("Erro ao adicionar placa gráfica: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao adicionar placa gráfica: ' . $e->getMessage()
    ]);
}
?> 