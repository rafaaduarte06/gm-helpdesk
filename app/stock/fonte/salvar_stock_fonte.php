<?php
require_once('../../ligacao_base_dados.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    // Validar campos obrigatórios
    $campos_obrigatorios = [
        'fonteRef' => 'Referência',
        'fonteMarca' => 'Marca',
        'fonteModelo' => 'Modelo',
        'fontePotencia' => 'Potência',
        'fonteFrequencia' => 'Frequência',
        'fonteCor' => 'Cor',
        'fonteEstado' => 'Estado'
    ];

    foreach ($campos_obrigatorios as $campo => $nome) {
        if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
            throw new Exception("Campo {$nome} é obrigatório");
        }
    }

    // Validar se o estado é permitido (1, 3 ou 4)
    if (!in_array($_POST['fonteEstado'], ['1', '3', '4'])) {
        throw new Exception('Estado inválido');
    }

    $query = "INSERT INTO fonte (
                FONTE_REF,
                FONTE_MARCA,
                FONTE_MODELO,
                FONTE_POTENCIA,
                FONTE_FREQUENCIA,
                FONTE_COR,
                FONTE_ESTADO,
                FONTE_COMP
              ) VALUES (
                :referencia,
                :marca,
                :modelo,
                :potencia,
                :frequencia,
                :cor,
                :estado,
                ''
              )";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'referencia' => $_POST['fonteRef'],
        'marca' => $_POST['fonteMarca'],
        'modelo' => $_POST['fonteModelo'],
        'potencia' => $_POST['fontePotencia'],
        'frequencia' => $_POST['fonteFrequencia'],
        'cor' => $_POST['fonteCor'],
        'estado' => $_POST['fonteEstado']
    ]);
    
    $mensagem = 'Fonte adicionada com sucesso';
    if ($_POST['fonteEstado'] === '4') {
        $mensagem = 'Fonte adicionada como Sem Reparo com sucesso';
    }

    echo json_encode([
        'success' => true,
        'message' => $mensagem,
        'id' => $pdo->lastInsertId()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 