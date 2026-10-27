<?php
header('Content-Type: application/json');
require_once('../../ligacao_base_dados.php');

try {
    $query = "SELECT 
        pm.PM_ID as id,
        pm.PM_REF as referencia,
        mpm.MPM_NOME as marca,
        pm.PM_TAM as tamanho,
        pm.PM_SOCKET as socket,
        pm.PM_CHIPSET as chipset,
        pm.PM_FORMATO as formato,
        pm.PM_TMEMORIA as tmemoria,
        c.COR as cor,
        pe.ESTADO as estado,
        pm.PM_ESTADO,
        pm.PM_SALA as PM_SALA,
        CASE 
            WHEN comp.COMP_REF IS NOT NULL THEN comp.COMP_REF
            ELSE pm.PM_COMP 
        END as computador_ref
    FROM placamae pm
    LEFT JOIN marcapm mpm ON pm.PM_MARCA = mpm.MPM_ID
    LEFT JOIN cor c ON pm.PM_COR = c.ID_COR
    LEFT JOIN placamae_estado pe ON pm.PM_ESTADO = pe.ID_ESTADO
    LEFT JOIN computadores comp ON pm.PM_COMP = comp.COMP_ID
    ORDER BY pm.PM_ID DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $placasMae = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $placasMae
    ]);
} catch (PDOException $e) {
    error_log('Erro em buscar_stock_placamae.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar placas-mãe: ' . $e->getMessage()
    ]);
}
?> 