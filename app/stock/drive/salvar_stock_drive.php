<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

// Log dos dados recebidos para debug
error_log("Dados recebidos em salvar_stock_drive: " . print_r($_POST, true));

// Verificar se o ID foi enviado (isso não deveria acontecer na criação)
if (isset($_POST['id']) && !empty($_POST['id'])) {
    error_log("ALERTA: ID enviado na criação de drive: " . $_POST['id']);
}

// Validar estado permitido
$estados_permitidos = ['1', '3', '4']; // Disponível, Manutenção, Sem Reparo
if (!isset($_POST['driveEstado']) || !in_array($_POST['driveEstado'], $estados_permitidos)) {
    $error = 'Estado inválido: ' . (isset($_POST['driveEstado']) ? $_POST['driveEstado'] : 'não definido');
    error_log($error);
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit;
}

// Verificar campos obrigatórios
$required_fields = [
    'driveRef' => 'Referência',
    'driveMarca' => 'Marca',
    'driveCor' => 'Cor',
    'driveEstado' => 'Estado'
];

$missing_fields = [];
foreach ($required_fields as $field => $label) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        $missing_fields[] = $label;
    }
}

if (!empty($missing_fields)) {
    error_log("Campos faltantes em salvar_stock_drive: " . implode(", ", $missing_fields));
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios. Faltando: ' . implode(", ", $missing_fields)]);
    exit;
}

try {
    // Verificar se esta é uma tentativa de edição redirecionada incorretamente
    if (isset($_POST['isEditing']) && $_POST['isEditing'] === 'true') {
        error_log("ALERTA: Tentativa de criar drive com isEditing=true");
        // Redirecionar para o endpoint de atualização
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            error_log("Redirecionando para atualizar_drive.php com ID: " . $_POST['id']);
            require_once('atualizar_drive.php');
            exit;
        }
    }

    // Inserir novo drive com DRVD_COMP vazio por padrão
    $query = "INSERT INTO drivedvd (DRVD_REF, DRVD_MARCA, DRVD_COR, DRVD_ESTADO, DRVD_COMP) 
              VALUES (:referencia, :marca, :cor, :estado, '')";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':referencia', $_POST['driveRef']);
    $stmt->bindParam(':marca', $_POST['driveMarca']);
    $stmt->bindParam(':cor', $_POST['driveCor']);
    $stmt->bindParam(':estado', $_POST['driveEstado']);
    
    if ($stmt->execute()) {
        $new_id = $pdo->lastInsertId();
        error_log("Novo drive criado com ID: " . $new_id);
        
        echo json_encode(['success' => true, 'message' => 'Drive adicionado com sucesso', 'id' => $new_id]);
    } else {
        throw new Exception('Erro ao adicionar drive');
    }
} catch (Exception $e) {
    error_log("Erro em salvar_stock_drive: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 