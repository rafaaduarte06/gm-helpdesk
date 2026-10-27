<?php
require_once('../../ligacao_base_dados.php');
header('Content-Type: application/json');

try {
    $query = "SELECT 
                f.FONTE_ID,
                f.FONTE_REF,
                mf.MF_NOME as marca,
                f.FONTE_MODELO,
                f.FONTE_POTENCIA,
                f.FONTE_FREQUENCIA,
                c.COR,
                fe.ESTADO,
                fe.ID_ESTADO,
                f.FONTE_SALA,
                CASE 
                    WHEN comp.COMP_REF IS NOT NULL THEN comp.COMP_REF
                    ELSE f.FONTE_COMP 
                END as computador_ref
              FROM fonte f 
              LEFT JOIN marcafonte mf ON f.FONTE_MARCA = mf.MF_ID
              LEFT JOIN cor c ON f.FONTE_COR = c.ID_COR
              LEFT JOIN fontes_estado fe ON f.FONTE_ESTADO = fe.ID_ESTADO
              LEFT JOIN computadores comp ON f.FONTE_COMP = comp.COMP_ID
              ORDER BY f.FONTE_ID DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $fontes = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $fonte = array(
            'id' => $row['FONTE_ID'],
            'referencia' => $row['FONTE_REF'],
            'marca' => $row['marca'],
            'modelo' => $row['FONTE_MODELO'],
            'potencia' => $row['FONTE_POTENCIA'],
            'frequencia' => $row['FONTE_FREQUENCIA'],
            'cor' => $row['COR'],
            'computador_ref' => $row['computador_ref'],
            'estado' => $row['ESTADO'],
            'estado_id' => $row['ID_ESTADO'],
            'FONTE_SALA' => $row['FONTE_SALA']
        );
        $fontes[] = $fonte;
    }
    
    echo json_encode(array('success' => true, 'data' => $fontes));
} catch (PDOException $e) {
    echo json_encode(array('success' => false, 'message' => 'Erro ao buscar fontes: ' . $e->getMessage()));
}
?> 