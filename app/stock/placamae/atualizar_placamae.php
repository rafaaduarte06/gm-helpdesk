<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

// Debug log
error_log('Dados recebidos em atualizar_placamae.php: ' . print_r($_POST, true));

try {
    // Validar ID
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception('ID não fornecido');
    }

    // Validar campos obrigatórios
    $requiredFields = ['placaRef', 'placaMarca', 'placaTamanho', 'placaSocket', 'placaChipset', 
                      'placaFormato', 'placaTMemoria', 'placaCor', 'placaEstado'];
    
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Campo obrigatório não preenchido: $field");
        }
    }

    // Preparar a query de atualização
    $query = "UPDATE placamae SET 
        PM_REF = :ref,
        PM_MARCA = :marca,
        PM_TAM = :tamanho,
        PM_SOCKET = :socket,
        PM_CHIPSET = :chipset,
        PM_FORMATO = :formato,
        PM_TMEMORIA = :tmemoria,
        PM_COR = :cor,
        PM_ESTADO = :estado
        WHERE PM_ID = :id";

    $stmt = $pdo->prepare($query);
    
    // Bind dos parâmetros
    $stmt->bindParam(':id', $_POST['id']);
    $stmt->bindParam(':ref', $_POST['placaRef']);
    $stmt->bindParam(':marca', $_POST['placaMarca']);
    $stmt->bindParam(':tamanho', $_POST['placaTamanho']);
    $stmt->bindParam(':socket', $_POST['placaSocket']);
    $stmt->bindParam(':chipset', $_POST['placaChipset']);
    $stmt->bindParam(':formato', $_POST['placaFormato']);
    $stmt->bindParam(':tmemoria', $_POST['placaTMemoria']);
    $stmt->bindParam(':cor', $_POST['placaCor']);
    $stmt->bindParam(':estado', $_POST['placaEstado']);

    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Placa-mãe atualizada com sucesso'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Nenhuma alteração realizada'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar placa-mãe: ' . $e->getMessage()
    ]);
}
?> 