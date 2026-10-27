<?php
session_start();
require_once('../../ligacao_base_dados.php');

header('Content-Type: application/json');

try {
    $query = "SELECT 
                p.PROC_ID as id,
                p.PROC_REF as referencia,
                mp.MP_NOME as marca,
                p.PROC_MODELO as modelo,
                p.PROC_CORE as core,
                p.PROC_THREADS as threads,
                p.PROC_SOCKET as socket,
                c.COR as cor,
                pe.ESTADO as estado,
                p.PROC_ESTADO as estado_id,
                p.PROC_SALA as PROC_SALA,
                CASE 
                    WHEN comp.COMP_REF IS NOT NULL THEN comp.COMP_REF
                    ELSE p.PROC_COMP 
                END as computador_ref
            FROM processador p
            LEFT JOIN marcaproc mp ON p.PROC_MARCA = mp.MP_ID
            LEFT JOIN cor c ON p.PROC_COR = c.ID_COR
            LEFT JOIN processadores_estado pe ON p.PROC_ESTADO = pe.ID_ESTADO
            LEFT JOIN computadores comp ON p.PROC_COMP = comp.COMP_ID
            ORDER BY p.PROC_ID DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $processadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $processadores
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar processadores: ' . $e->getMessage()
    ]);
}
?> 