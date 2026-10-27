<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

// Log dos dados recebidos para debug
error_log("Dados recebidos em atualizar_cooler: " . print_r($_POST, true));

// Verificar se o ID foi fornecido
if (!isset($_POST['id']) || empty($_POST['id'])) {
    $error = 'ID do cooler não fornecido';
    error_log($error);
    echo json_encode(['success' => false, 'message' => $error]);
    exit;
}

// Validar estado permitido
$estados_permitidos = ['1', '3', '4']; // Disponível, Manutenção, Sem Reparo
if (!isset($_POST['coolerEstado']) || !in_array($_POST['coolerEstado'], $estados_permitidos)) {
    $error = 'Estado inválido: ' . (isset($_POST['coolerEstado']) ? $_POST['coolerEstado'] : 'não definido');
    error_log($error);
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit;
}

// Verificar campos obrigatórios
$required_fields = [
    'coolerRef' => 'Referência',
    'coolerMarca' => 'Marca',
    'coolerPinos' => 'Pinos',
    'coolerSocket' => 'Socket',
    'coolerTamanho' => 'Tamanho',
    'coolerTipo' => 'Tipo',
    'coolerCor' => 'Cor',
    'coolerEstado' => 'Estado'
];

$missing_fields = [];
foreach ($required_fields as $field => $label) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        $missing_fields[] = $label;
    }
}

if (!empty($missing_fields)) {
    error_log("Campos faltantes em atualizar_cooler: " . implode(", ", $missing_fields));
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios. Faltando: ' . implode(", ", $missing_fields)]);
    exit;
}

try {
    // Verificar se o cooler existe
    $check_exists = $pdo->prepare("SELECT COO_ID, COO_ESTADO FROM cooler WHERE COO_ID = ?");
    $check_exists->execute([$_POST['id']]);
    $cooler = $check_exists->fetch(PDO::FETCH_ASSOC);
    
    if (!$cooler) {
        $error = 'Cooler não encontrado com ID: ' . $_POST['id'];
        error_log($error);
        echo json_encode(['success' => false, 'message' => 'Cooler não encontrado']);
        exit;
    }

    // Verificar se o cooler já está marcado como "Sem Reparo"
    if ($cooler['COO_ESTADO'] === '4') {
        $error = 'Tentativa de editar cooler marcado como Sem Reparo. ID: ' . $_POST['id'];
        error_log($error);
        echo json_encode(['success' => false, 'message' => 'Não é possível editar um cooler marcado como Sem Reparo']);
        exit;
    }

    // Log de informação sobre a atualização
    error_log("Atualizando cooler ID: " . $_POST['id'] . " com estado: " . $_POST['coolerEstado']);

    // Atualizar o cooler
    $query = "UPDATE cooler SET 
                COO_REF = :referencia,
                COO_MARCA = :marca,
                COO_PINOS = :pinos,
                COO_SOCKET = :socket,
                COO_TAM = :tamanho,
                COO_TIPO = :tipo,
                COO_COR = :cor,
                COO_ESTADO = :estado
              WHERE COO_ID = :id";
            
    $stmt = $pdo->prepare($query);
    
    $params = [
        ':referencia' => $_POST['coolerRef'],
        ':marca' => $_POST['coolerMarca'],
        ':pinos' => $_POST['coolerPinos'],
        ':socket' => $_POST['coolerSocket'],
        ':tamanho' => $_POST['coolerTamanho'],
        ':tipo' => $_POST['coolerTipo'],
        ':cor' => $_POST['coolerCor'],
        ':estado' => $_POST['coolerEstado'],
        ':id' => $_POST['id']
    ];
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        error_log("Cooler atualizado com sucesso. ID: " . $_POST['id']);
        echo json_encode(['success' => true, 'message' => 'Cooler atualizado com sucesso']);
    } else {
        // Se nenhuma linha foi afetada, pode ser porque os dados são idênticos
        error_log("Nenhuma alteração necessária para o cooler ID: " . $_POST['id']);
        echo json_encode(['success' => true, 'message' => 'Nenhuma alteração necessária']);
    }
} catch (PDOException $e) {
    error_log("Erro em atualizar_cooler: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar cooler: ' . $e->getMessage()]);
}
?> 