<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

// Log dos dados recebidos para debug
error_log("Dados recebidos em salvar_stock_cooler: " . print_r($_POST, true));

// Verificar se o ID foi enviado (isso não deveria acontecer na criação)
if (isset($_POST['id']) && !empty($_POST['id'])) {
    error_log("ALERTA: ID enviado na criação de cooler: " . $_POST['id']);
}

if (!isset($_POST['coolerRef']) || empty($_POST['coolerRef']) ||
    !isset($_POST['coolerMarca']) || empty($_POST['coolerMarca']) ||
    !isset($_POST['coolerPinos']) || empty($_POST['coolerPinos']) ||
    !isset($_POST['coolerSocket']) || empty($_POST['coolerSocket']) ||
    !isset($_POST['coolerTamanho']) || empty($_POST['coolerTamanho']) ||
    !isset($_POST['coolerTipo']) || empty($_POST['coolerTipo']) ||
    !isset($_POST['coolerCor']) || empty($_POST['coolerCor']) ||
    !isset($_POST['coolerEstado']) || empty($_POST['coolerEstado'])) {
    
    // Log dos campos faltantes
    $missing_fields = [];
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
    
    foreach ($required_fields as $field => $label) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missing_fields[] = $label;
        }
    }
    
    error_log("Campos faltantes em salvar_stock_cooler: " . implode(", ", $missing_fields));
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios. Faltando: ' . implode(", ", $missing_fields)]);
    exit;
}

try {
    // Verificar se esta é uma tentativa de edição redirecionada incorretamente
    if (isset($_POST['isEditing']) && $_POST['isEditing'] === 'true') {
        error_log("ALERTA: Tentativa de criar cooler com isEditing=true");
        // Redirecionar para o endpoint de atualização
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            error_log("Redirecionando para atualizar_cooler.php com ID: " . $_POST['id']);
            require_once('atualizar_cooler.php');
            exit;
        }
    }

    $query = "INSERT INTO cooler (
                COO_REF,
                COO_MARCA,
                COO_PINOS,
                COO_SOCKET,
                COO_TAM,
                COO_TIPO,
                COO_COR,
                COO_ESTADO,
                COO_COMP
            ) VALUES (
                :referencia,
                :marca,
                :pinos,
                :socket,
                :tamanho,
                :tipo,
                :cor,
                :estado,
                ''
            )";
            
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':referencia', $_POST['coolerRef'], PDO::PARAM_STR);
    $stmt->bindParam(':marca', $_POST['coolerMarca'], PDO::PARAM_INT);
    $stmt->bindParam(':pinos', $_POST['coolerPinos'], PDO::PARAM_STR);
    $stmt->bindParam(':socket', $_POST['coolerSocket'], PDO::PARAM_STR);
    $stmt->bindParam(':tamanho', $_POST['coolerTamanho'], PDO::PARAM_STR);
    $stmt->bindParam(':tipo', $_POST['coolerTipo'], PDO::PARAM_STR);
    $stmt->bindParam(':cor', $_POST['coolerCor'], PDO::PARAM_INT);
    $stmt->bindParam(':estado', $_POST['coolerEstado'], PDO::PARAM_INT);
    
    $stmt->execute();
    
    $new_id = $pdo->lastInsertId();
    error_log("Novo cooler criado com ID: " . $new_id);
    
    echo json_encode(['success' => true, 'message' => 'Cooler adicionado com sucesso', 'id' => $new_id]);
} catch (PDOException $e) {
    error_log("Erro em salvar_stock_cooler: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar cooler: ' . $e->getMessage()]);
}
?> 