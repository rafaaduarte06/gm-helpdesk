<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido'
    ]);
    exit;
}

try {
    // Validar dados recebidos
    $id = $_POST['id'] ?? '';
    $referencia = $_POST['driveRef'] ?? '';
    $marca = $_POST['driveMarca'] ?? '';
    $cor = $_POST['driveCor'] ?? '';
    $estado = $_POST['driveEstado'] ?? '';

    if (empty($id) || empty($referencia) || empty($marca) || empty($cor) || empty($estado)) {
        throw new Exception('Todos os campos são obrigatórios');
    }

    // Atualizar drive mantendo DRVD_COMP inalterado
    $query = "UPDATE drivedvd 
              SET DRVD_REF = :ref,
                  DRVD_MARCA = :marca,
                  DRVD_COR = :cor,
                  DRVD_ESTADO = :estado
              WHERE DRVD_ID = :id";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':ref', $referencia);
    $stmt->bindParam(':marca', $marca);
    $stmt->bindParam(':cor', $cor);
    $stmt->bindParam(':estado', $estado);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Drive atualizado com sucesso'
        ]);
    } else {
        throw new Exception('Erro ao atualizar drive');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 