<?php
session_start();
require_once('../../ligacao_base_dados.php');

// IMPORTANTE: Os componentes devem sempre permanecer com estado 1 (Disponível),
// independentemente de estarem associados a um computador ou não.

header('Content-Type: text/html; charset=utf-8');

// Debug para rastrear o que está acontecendo
error_log("Requisição recebida em editar_componente.php: " . print_r($_GET, true));

// Verificar se o usuário está logado e é um técnico
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

// Verificar parâmetros necessários
if (!isset($_GET['comp_id']) || !isset($_GET['tipo'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros insuficientes']);
    exit;
}

$computerId = $_GET['comp_id']; // Removido intval para aceitar IDs alfanuméricos
$tipoComponente = strtoupper($_GET['tipo']);
$componenteId = isset($_GET['id']) ? intval($_GET['id']) : null;

// Debug para mostrar valores após o processamento
error_log("Valores processados: computerId: $computerId, tipoComponente: $tipoComponente, componenteId: $componenteId");

// Mapeamento de tipos de componentes para nomes e tabelas
$componentesInfo = [
    'RATO' => ['tabela' => 'ratos', 'id' => 'RATOS_ID', 'ref' => 'RATOS_REF', 'estado' => 'RATOS_ESTADO', 'nome' => 'RATO', 'comp' => 'RATOS_COMP'],
    'TECLADO' => ['tabela' => 'teclados', 'id' => 'TECLADO_ID', 'ref' => 'TECLADO_REF', 'estado' => 'TECLADO_ESTADO', 'nome' => 'TECLADO', 'comp' => 'TECLADO_COMP'],
    'MONITOR' => ['tabela' => 'monitor', 'id' => 'MONITOR_ID', 'ref' => 'MONITOR_REF', 'estado' => 'MONITOR_ESTADO', 'nome' => 'MONITOR', 'comp' => 'MONITOR_COMP'],
    'PM' => ['tabela' => 'placamae', 'id' => 'PM_ID', 'ref' => 'PM_REF', 'estado' => 'PM_ESTADO', 'nome' => 'MOTHERBOARD', 'comp' => 'PM_COMP'],
    'PROC' => ['tabela' => 'processador', 'id' => 'PROC_ID', 'ref' => 'PROC_REF', 'estado' => 'PROC_ESTADO', 'nome' => 'PROCESSADOR', 'comp' => 'PROC_COMP'],
    'COOLER' => ['tabela' => 'cooler', 'id' => 'COO_ID', 'ref' => 'COO_REF', 'estado' => 'COO_ESTADO', 'nome' => 'COOLER', 'comp' => 'COO_COMP'],
    'PG' => ['tabela' => 'placagrafica', 'id' => 'PG_ID', 'ref' => 'PG_REF', 'estado' => 'PG_ESTADO', 'nome' => 'PLACA GRÁFICA', 'comp' => 'PG_COMP'],
    'DISCO' => ['tabela' => 'disco', 'id' => 'DISCO_ID', 'ref' => 'DISCO_REF', 'estado' => 'DISCO_ESTADO', 'nome' => 'DISCO', 'comp' => 'DISCO_COMP'],
    'FONTE' => ['tabela' => 'fonte', 'id' => 'FONTE_ID', 'ref' => 'FONTE_REF', 'estado' => 'FONTE_ESTADO', 'nome' => 'FONTE', 'comp' => 'FONTE_COMP'],
    'MEMORIAR' => ['tabela' => 'memoriaram', 'id' => 'MR_ID', 'ref' => 'MR_REF', 'estado' => 'MR_ESTADO', 'nome' => 'MEMÓRIA RAM', 'comp' => 'MR_COMP'],
    'DRIVEDVD' => ['tabela' => 'drivedvd', 'id' => 'DRVD_ID', 'ref' => 'DRVD_REF', 'estado' => 'DRVD_ESTADO', 'nome' => 'DRIVE DVD', 'comp' => 'DRVD_COMP'],
    'PR' => ['tabela' => 'placaderede', 'id' => 'PR_ID', 'ref' => 'PR_REF', 'estado' => 'PR_ESTADO', 'nome' => 'PLACA DE REDE', 'comp' => 'PR_COMP'],
    'VENTOINHA' => ['tabela' => 'ventoinha', 'id' => 'VENT_ID', 'ref' => 'VENT_REF', 'estado' => 'VENT_ESTADO', 'nome' => 'VENTOINHA', 'comp' => 'VENT_COMP'],
    'CAIXA' => ['tabela' => 'caixa', 'id' => 'CAIXA_ID', 'ref' => 'CAIXA_REF', 'estado' => 'CAIXA_ESTADO', 'nome' => 'CAIXA', 'comp' => 'CAIXA_COMP']
];

// Verificar se o tipo de componente é válido
if (!array_key_exists($tipoComponente, $componentesInfo)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de componente inválido']);
    exit;
}

$info = $componentesInfo[$tipoComponente];

try {
    // Verificar se o computador existe
    $stmt = $pdo->prepare("SELECT * FROM computadores WHERE COMP_ID = ?");
    $stmt->execute([$computerId]);
    $computador = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$computador) {
        error_log("Computador não encontrado para ID: $computerId");
        echo json_encode(['success' => false, 'message' => 'Computador não encontrado']);
        exit;
    }
    
    // Se for uma edição, buscar dados do componente atual
    $componenteAtual = null;
    if ($componenteId) {
        $stmt = $pdo->prepare("SELECT * FROM {$info['tabela']} WHERE {$info['id']} = ?");
        $stmt->execute([$componenteId]);
        $componenteAtual = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Verificar se a tabela tem o campo de computador (comp)
    $hasCompField = false;
    try {
        $checkStmt = $pdo->prepare("SELECT {$info['comp']} FROM {$info['tabela']} LIMIT 1");
        $checkStmt->execute();
        $hasCompField = true;
        error_log("Tabela {$info['tabela']} tem o campo {$info['comp']}");
    } catch (PDOException $ex) {
        error_log("Tabela {$info['tabela']} não tem o campo {$info['comp']}: " . $ex->getMessage());
        $hasCompField = false;
    }
    
    // Buscar componentes disponíveis dependendo se a tabela tem o campo comp ou não
    if ($hasCompField) {
        // Se tiver o campo comp, usar a query completa
        $stmt = $pdo->prepare("SELECT * FROM {$info['tabela']} WHERE 
                              ({$info['estado']} = 1 AND ({$info['comp']} IS NULL OR {$info['comp']} = '')) 
                              OR {$info['comp']} = ? 
                              OR {$info['id']} = ?");
        $stmt->execute([$computerId, $componenteId ?: 0]);
    } else {
        // Se não tiver campo comp, usar apenas o estado
        $stmt = $pdo->prepare("SELECT * FROM {$info['tabela']} WHERE 
                              {$info['estado']} = 1 
                              OR {$info['id']} = ?");
        $stmt->execute([$componenteId ?: 0]);
    }
    
    $componentesDisponiveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug para mostrar componentes disponíveis
    error_log("Componentes disponíveis para $tipoComponente: " . print_r($componentesDisponiveis, true));
    
    // Título do modal
    $tituloModal = $componenteId ? "Editar {$info['nome']}" : "Adicionar {$info['nome']}";
    
} catch (PDOException $e) {
    error_log("Erro de banco de dados em editar_componente.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro de banco de dados: ' . $e->getMessage()]);
    exit;
}

// Remover componentes que já estão em uso em outros computadores
if (!empty($componentesDisponiveis) && $hasCompField) {
    foreach ($componentesDisponiveis as $key => $componente) {
        // Se o componente for o mesmo que já está sendo usado neste computador, não remover
        if ($componenteId && $componente[$info['id']] == $componenteId) {
            continue;
        }
        
        // Verificar se o componente está em uso em outro computador
        if (!empty($componente[$info['comp']]) && $componente[$info['comp']] != $computerId) {
            error_log("Removendo componente {$componente[$info['id']]} porque está associado ao computador {$componente[$info['comp']]}");
            unset($componentesDisponiveis[$key]);
        }
    }
    
    // Re-indexar o array
    $componentesDisponiveis = array_values($componentesDisponiveis);
} else if (!empty($componentesDisponiveis)) {
    // Remover componentes que já estão em uso em outros computadores (método alternativo)
    foreach ($componentesDisponiveis as $key => $componente) {
        // Se o componente for o mesmo que já está sendo usado neste computador, não remover
        if ($componenteId && $componente[$info['id']] == $componenteId) {
            continue;
        }
        
        // Verificar se o componente está em uso em outro computador pela tabela de computadores
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM computadores WHERE COMP_{$tipoComponente} = ? AND COMP_ID != ?");
        $stmt->execute([$componente[$info['id']], $computerId]);
        if ($stmt->fetchColumn() > 0) {
            error_log("Removendo componente {$componente[$info['id']]} porque está em uso em outro computador");
            unset($componentesDisponiveis[$key]);
        }
    }
    
    // Re-indexar o array
    $componentesDisponiveis = array_values($componentesDisponiveis);
}
?>

<!-- Modal para editar componente -->
<div class="modal fade" id="modalEditarComponente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $tituloModal; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <?php if (empty($componentesDisponiveis)): ?>
                    <div class="alert alert-warning">
                        Não há <?php echo strtolower($info['nome']); ?>s disponíveis para adicionar ou editar.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                <?php else: ?>
                    <form id="formEditarComponente">
                        <input type="hidden" name="comp_id" value="<?php echo htmlspecialchars($computerId); ?>">
                        <input type="hidden" name="tipo_componente" value="<?php echo htmlspecialchars($tipoComponente); ?>">
                        
                        <div class="mb-3">
                            <label for="componente_id" class="form-label">Selecione o <?php echo htmlspecialchars($info['nome']); ?></label>
                            <select class="form-select" id="componente_id" name="componente_id" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($componentesDisponiveis as $componente): ?>
                                    <option value="<?php echo htmlspecialchars($componente[$info['id']]); ?>" 
                                            <?php echo ($componenteId && $componente[$info['id']] == $componenteId) ? 'selected' : ''; ?>>
                                        <?php 
                                        // Mostrar o nome do componente e sua referência, em vez de referência e estado
                                        echo htmlspecialchars($info['nome'] . ' ' . $componente[$info['ref']]);
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div> 