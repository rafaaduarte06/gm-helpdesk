<?php
require_once('../../ligacao_base_dados.php');

header('Content-Type: text/html; charset=utf-8');

// Debug para rastrear o que está acontecendo
error_log("Requisição recebida em editar_computador_info.php: " . print_r($_GET, true));

// Verificar se foi fornecido o ID do computador
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do computador não fornecido']);
    exit;
}

$computerId = $_GET['id']; // Não converter para intval para aceitar IDs alfanuméricos

// Debug para mostrar valores após o processamento
error_log("Valor processado: computerId: $computerId");

// Buscar informações do computador
try {
    $stmt = $pdo->prepare("SELECT c.*
                            FROM computadores c 
                            WHERE c.COMP_ID = ?");
    $stmt->execute([$computerId]);
    $computador = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$computador) {
        error_log("Computador não encontrado para ID: $computerId");
        echo json_encode(['success' => false, 'message' => 'Computador não encontrado']);
        exit;
    }

    // Lista de estados disponíveis (sem consultar a tabela comp_estado)
    $estados = [
        ['ID_ESTADO' => '1', 'ESTADO' => 'Disponível'],
        ['ID_ESTADO' => '3', 'ESTADO' => 'Em Manutenção'],
        ['ID_ESTADO' => '4', 'ESTADO' => 'Sem Reparo']
    ];
    
    // Função para obter texto do estado
    function getEstadoText($estado) {
        switch ($estado) {
            case '1': return 'Disponível';
            case '3': return 'Em Manutenção';
            case '2': return 'Em Uso';
            case '4': return 'Sem Reparo';
            default: return 'Desconhecido';
        }
    }
} catch (PDOException $e) {
    error_log("Erro de banco de dados em editar_computador_info.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar dados: ' . $e->getMessage()]);
    exit;
}
?>

<!-- Modal para editar informações básicas do computador -->
<div class="modal fade" id="modalEditarComputadorInfo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Informações do Computador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarComputadorInfo">
                    <input type="hidden" name="comp_id" value="<?php echo htmlspecialchars($computador['COMP_ID']); ?>">
                    
                    <div class="mb-3">
                        <label for="comp_ref" class="form-label">Referência</label>
                        <input type="text" class="form-control" id="comp_ref" name="comp_ref" value="<?php echo htmlspecialchars($computador['COMP_REF']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comp_estado" class="form-label">Estado</label>
                        <select class="form-select" id="comp_estado" name="comp_estado" required>
                            <?php foreach ($estados as $estado): ?>
                                <option value="<?php echo $estado['ID_ESTADO']; ?>" <?php echo ($estado['ID_ESTADO'] == $computador['COMP_ESTADO']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($estado['ESTADO']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 