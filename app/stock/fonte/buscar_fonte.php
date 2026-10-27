<?php
require_once('../../ligacao_base_dados.php');
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
    exit;
}

try {
    $query = "SELECT 
                f.*,
                mf.MF_NOME as marca_nome,
                c.COR as cor_nome,
                fe.ESTADO as estado_nome,
                comp.COMP_REF as computador_ref
              FROM fonte f 
              LEFT JOIN marcafonte mf ON f.FONTE_MARCA = mf.MF_ID
              LEFT JOIN cor c ON f.FONTE_COR = c.ID_COR
              LEFT JOIN fontes_estado fe ON f.FONTE_ESTADO = fe.ID_ESTADO
              LEFT JOIN computadores comp ON f.FONTE_COMP = comp.COMP_ID
              WHERE f.FONTE_ID = :id";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $_GET['id']]);
    
    if ($fonte = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode(['success' => true, 'data' => $fonte]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Fonte não encontrada']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar fonte: ' . $e->getMessage()]);
}
?> 