<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

// Validar campos obrigatórios
if (!isset($_POST['discoRef']) || empty($_POST['discoRef']) ||
    !isset($_POST['discoMarca']) || empty($_POST['discoMarca']) ||
    !isset($_POST['discoInterface']) || empty($_POST['discoInterface']) ||
    !isset($_POST['discoMemoria']) || empty($_POST['discoMemoria']) ||
    !isset($_POST['discoTipo']) || empty($_POST['discoTipo']) ||
    !isset($_POST['discoCor']) || empty($_POST['discoCor']) ||
    !isset($_POST['discoEstado']) || empty($_POST['discoEstado'])) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}

// Validar estado
$estadosPermitidos = [1, 3, 4]; // Disponível, Manutenção, Sem Reparo
if (!in_array((int)$_POST['discoEstado'], $estadosPermitidos)) {
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit;
}

try {
    $query = "INSERT INTO disco (
                DISCO_REF,
                DISCO_MARCA,
                DISCO_INTERFACE,
                DISCO_MEMORIA,
                DISCO_TIPO,
                DISCO_COR,
                DISCO_ESTADO,
                DISCO_COMP
            ) VALUES (
                :referencia,
                :marca,
                :interface,
                :memoria,
                :tipo,
                :cor,
                :estado,
                ''
            )";
            
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'referencia' => $_POST['discoRef'],
        'marca' => $_POST['discoMarca'],
        'interface' => $_POST['discoInterface'],
        'memoria' => $_POST['discoMemoria'],
        'tipo' => $_POST['discoTipo'],
        'cor' => $_POST['discoCor'],
        'estado' => $_POST['discoEstado']
    ]);
    
    if ($stmt->rowCount() > 0) {
        $message = 'Disco adicionado com sucesso';
        if ($_POST['discoEstado'] == 4) {
            $message = 'Disco adicionado e marcado como Sem Reparo';
        }
        
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'id' => $pdo->lastInsertId()
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao adicionar disco']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao adicionar disco: ' . $e->getMessage()]);
}
?> 