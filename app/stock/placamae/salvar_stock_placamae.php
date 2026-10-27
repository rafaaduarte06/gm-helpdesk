<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

// Debug log
error_log('Dados recebidos em salvar_stock_placamae.php: ' . print_r($_POST, true));

// Verificar campos obrigatórios
$required_fields = [
    'placaRef' => 'Referência',
    'placaMarca' => 'Marca',
    'placaTamanho' => 'Tamanho',
    'placaSocket' => 'Socket',
    'placaChipset' => 'Chipset',
    'placaFormato' => 'Formato',
    'placaTMemoria' => 'Tipo de Memória',
    'placaCor' => 'Cor',
    'placaEstado' => 'Estado'
];

$missing_fields = [];
foreach ($required_fields as $field => $label) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        $missing_fields[] = $label;
    }
}

if (!empty($missing_fields)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Campos obrigatórios não preenchidos: ' . implode(', ', $missing_fields)
    ]);
    exit;
}

// Validar estado permitido
$allowed_states = ['1', '3', '4']; // Disponível, Manutenção, Sem Reparo
if (!in_array($_POST['placaEstado'], $allowed_states)) {
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit;
}

try {
    $query = "INSERT INTO placamae (
                PM_REF,
                PM_MARCA,
                PM_TAM,
                PM_SOCKET,
                PM_CHIPSET,
                PM_FORMATO,
                PM_TMEMORIA,
                PM_COR,
                PM_ESTADO,
                PM_COMP
            ) VALUES (
                :referencia,
                :marca,
                :tamanho,
                :socket,
                :chipset,
                :formato,
                :tmemoria,
                :cor,
                :estado,
                ''
            )";
            
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':referencia', $_POST['placaRef']);
    $stmt->bindParam(':marca', $_POST['placaMarca']);
    $stmt->bindParam(':tamanho', $_POST['placaTamanho']);
    $stmt->bindParam(':socket', $_POST['placaSocket']);
    $stmt->bindParam(':chipset', $_POST['placaChipset']);
    $stmt->bindParam(':formato', $_POST['placaFormato']);
    $stmt->bindParam(':tmemoria', $_POST['placaTMemoria']);
    $stmt->bindParam(':cor', $_POST['placaCor']);
    $stmt->bindParam(':estado', $_POST['placaEstado']);
    
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Placa-mãe adicionada com sucesso',
        'id' => $pdo->lastInsertId()
    ]);
} catch (PDOException $e) {
    error_log('Erro em salvar_stock_placamae.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar placa-mãe: ' . $e->getMessage()]);
}
?> 