<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

try {
    $sql = "SELECT 
                pg.PG_ID as id,
                pg.PG_REF as referencia,
                m.MPG_NOME as marca,
                pg.PG_MEMORIA as memoria,
                pg.PG_MODELO as modelo,
                c.COR as cor,
                e.ESTADO as estado,
                pg.PG_ESTADO,
                pg.PG_SALA as PG_SALA,
                CASE 
                    WHEN comp.COMP_REF IS NOT NULL THEN comp.COMP_REF
                    ELSE pg.PG_COMP 
                END as computador_ref
            FROM placagrafica pg
            LEFT JOIN marcapg m ON pg.PG_MARCA = m.MPG_ID
            LEFT JOIN cor c ON pg.PG_COR = c.ID_COR
            LEFT JOIN placagrafica_estado e ON pg.PG_ESTADO = e.ID_ESTADO
            LEFT JOIN computadores comp ON pg.PG_COMP = comp.COMP_ID
            ORDER BY pg.PG_ID DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $placas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $placas
    ]);
} catch (PDOException $e) {
    error_log("Erro ao buscar placas gráficas: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar placas gráficas: ' . $e->getMessage()
    ]);
}
?> 