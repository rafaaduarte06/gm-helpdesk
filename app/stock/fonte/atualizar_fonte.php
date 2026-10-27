<?php
require_once('../../ligacao_base_dados.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    // Validar ID
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception('ID da fonte não fornecido');
    }

    // Validar campos obrigatórios
    $campos_obrigatorios = [
        'fonteRef' => 'Referência',
        'fonteMarca' => 'Marca',
        'fonteModelo' => 'Modelo',
        'fontePotencia' => 'Potência',
        'fonteFrequencia' => 'Frequência',
        'fonteCor' => 'Cor'
    ];

    foreach ($campos_obrigatorios as $campo => $nome) {
        if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
            throw new Exception("Campo {$nome} é obrigatório");
        }
    }

    // Verificar se a fonte existe e seu estado atual
    $stmt = $pdo->prepare("SELECT FONTE_ESTADO FROM fonte WHERE FONTE_ID = ?");
    $stmt->execute([$_POST['id']]);
    $fonte = $stmt->fetch();
    
    if (!$fonte) {
        throw new Exception('Fonte não encontrada');
    }

    // Se a fonte já estiver em estado "Sem Reparo", não permitir edição
    if ($fonte['FONTE_ESTADO'] === '4') {
        throw new Exception('Não é possível editar uma fonte marcada como Sem Reparo');
    }

    $query = "UPDATE fonte SET 
                FONTE_REF = :referencia,
                FONTE_MARCA = :marca,
                FONTE_MODELO = :modelo,
                FONTE_POTENCIA = :potencia,
                FONTE_FREQUENCIA = :frequencia,
                FONTE_COR = :cor,
                FONTE_ESTADO = :estado
              WHERE FONTE_ID = :id";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'referencia' => $_POST['fonteRef'],
        'marca' => $_POST['fonteMarca'],
        'modelo' => $_POST['fonteModelo'],
        'potencia' => $_POST['fontePotencia'],
        'frequencia' => $_POST['fonteFrequencia'],
        'cor' => $_POST['fonteCor'],
        'estado' => $_POST['fonteEstado'],
        'id' => $_POST['id']
    ]);
    
    if ($stmt->rowCount() > 0) {
        $mensagem = 'Fonte atualizada com sucesso';
        if (isset($_POST['fonteEstado']) && $_POST['fonteEstado'] === '4') {
            $mensagem = 'Fonte marcada como Sem Reparo com sucesso';
        }
        echo json_encode([
            'success' => true,
            'message' => $mensagem
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Nenhuma alteração realizada'
        ]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 