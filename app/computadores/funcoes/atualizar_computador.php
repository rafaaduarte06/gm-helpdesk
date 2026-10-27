<?php
require_once('../../ligacao_base_dados.php');

// Verificar se o usuário está logado e é um técnico
session_start();
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'tecnico') {
    header('Location: ../../login.php');
    exit;
}

// Processar o formulário quando submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Iniciar transação para garantir integridade dos dados
        $conn->beginTransaction();
        
        // Obter e validar os dados do formulário
        $compId = trim($_POST['comp_id']);
        $compRef = trim($_POST['comp_ref']);
        $estado = trim($_POST['comp_estado']);
        
        // Validar a referência do computador (não pode estar vazia)
        if (empty($compRef)) {
            throw new Exception("A referência do computador é obrigatória!");
        }
        
        // Verificar se a referência já existe em outro computador
        $stmt = $conn->prepare("SELECT COUNT(*) FROM computadores WHERE COMP_REF = ? AND COMP_ID != ?");
        $stmt->execute([$compRef, $compId]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Esta referência de computador já existe em outro computador!");
        }
        
        // Buscar estado atual dos componentes do computador para posterior comparação
        $stmt = $conn->prepare("SELECT * FROM computadores WHERE COMP_ID = ?");
        $stmt->execute([$compId]);
        $computerAtual = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$computerAtual) {
            throw new Exception("Computador não encontrado!");
        }
        
        // Obter dados dos componentes
        $componentes = [
            'rato' => isset($_POST['comp_rato']) ? $_POST['comp_rato'] : null,
            'teclado' => isset($_POST['comp_teclado']) ? $_POST['comp_teclado'] : null,
            'monitor' => isset($_POST['comp_monitor']) ? $_POST['comp_monitor'] : null,
            'placamae' => isset($_POST['comp_pm']) ? $_POST['comp_pm'] : null,
            'processador' => isset($_POST['comp_proc']) ? $_POST['comp_proc'] : null,
            'cooler' => isset($_POST['comp_cooler']) ? $_POST['comp_cooler'] : null,
            'placagrafica' => isset($_POST['comp_pg']) ? $_POST['comp_pg'] : null,
            'disco' => isset($_POST['comp_disco']) ? $_POST['comp_disco'] : null,
            'fonte' => isset($_POST['comp_fonte']) ? $_POST['comp_fonte'] : null,
            'memoriaram' => isset($_POST['comp_memoriar']) ? $_POST['comp_memoriar'] : null,
            'drivedvd' => isset($_POST['comp_drivedvd']) ? $_POST['comp_drivedvd'] : null,
            'placaderede' => isset($_POST['comp_pr']) ? $_POST['comp_pr'] : null,
            'ventoinha' => isset($_POST['comp_ventoinha']) ? $_POST['comp_ventoinha'] : null,
            'caixa' => isset($_POST['comp_caixa']) ? $_POST['comp_caixa'] : null
        ];
        
        // Verificar estado dos componentes selecionados
        $componentesPermitidos = true;
        $componentesIndisponiveis = [];
        
        // Arrays para mapear tabelas e campos
        $tabelasComponentes = [
            'rato' => 'ratos',
            'teclado' => 'teclados',
            'monitor' => 'monitor',
            'placamae' => 'placamae',
            'processador' => 'processador',
            'cooler' => 'cooler',
            'placagrafica' => 'placagrafica',
            'disco' => 'disco',
            'fonte' => 'fonte',
            'memoriaram' => 'memoriaram',
            'drivedvd' => 'drivedvd',
            'placaderede' => 'placaderede',
            'ventoinha' => 'ventoinha',
            'caixa' => 'caixa'
        ];
        
        $camposId = [
            'ratos' => 'RATOS_ID',
            'teclados' => 'TECLADO_ID',
            'monitor' => 'MONITOR_ID',
            'placamae' => 'PM_ID',
            'processador' => 'PROC_ID',
            'cooler' => 'COO_ID',
            'placagrafica' => 'PG_ID',
            'disco' => 'DISCO_ID',
            'fonte' => 'FONTE_ID',
            'memoriaram' => 'MR_ID',
            'drivedvd' => 'DRVD_ID',
            'placaderede' => 'PR_ID',
            'ventoinha' => 'VENT_ID',
            'caixa' => 'CAIXA_ID'
        ];
        
        $camposEstado = [
            'ratos' => 'RATOS_ESTADO',
            'teclados' => 'TECLADO_ESTADO',
            'monitor' => 'MONITOR_ESTADO',
            'placamae' => 'PM_ESTADO',
            'processador' => 'PROC_ESTADO',
            'cooler' => 'COO_ESTADO',
            'placagrafica' => 'PG_ESTADO',
            'disco' => 'DISCO_ESTADO',
            'fonte' => 'FONTE_ESTADO',
            'memoriaram' => 'MR_ESTADO',
            'drivedvd' => 'DRVD_ESTADO',
            'placaderede' => 'PR_ESTADO',
            'ventoinha' => 'VENT_ESTADO',
            'caixa' => 'CAIXA_ESTADO'
        ];
        
        // Componentes a serem liberados (removidos do computador)
        $componentesALiberar = [];
        
        // Analisar componentes atuais vs novos
        foreach ($tabelasComponentes as $tipo => $tabela) {
            $campoComp = 'COMP_' . strtoupper(substr($tipo, 0, 1)) . substr($tipo, 1);
            
            // Componente atual
            $componenteAtual = $computerAtual[$campoComp];
            
            // Componente novo
            $componenteNovo = $componentes[$tipo] ? $componentes[$tipo] : '';
            
            // Se o componente mudou
            if ($componenteAtual != $componenteNovo) {
                // Se havia um componente anterior, liberá-lo
                if (!empty($componenteAtual)) {
                    $componentesALiberar[$tipo] = $componenteAtual;
                }
                
                // Se há um novo componente, verificar disponibilidade
                if (!empty($componenteNovo)) {
                    // Verificar se o componente já está associado a outro computador
                    $stmt = $conn->prepare("SELECT c.COMP_REF 
                                             FROM computadores c 
                                             WHERE c.COMP_ID != ? AND c.$campoComp = ?");
                    $stmt->execute([$compId, $componenteNovo]);
                    $compExistente = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($compExistente) {
                        $componentesIndisponiveis[] = "O componente $tipo já está associado ao computador {$compExistente['COMP_REF']}";
                        $componentesPermitidos = false;
                        continue;
                    }
                    
                    // Verificar o estado do componente
                    $campoId = $camposId[$tabela];
                    $campoEstado = $camposEstado[$tabela];
                    $stmt = $conn->prepare("SELECT $campoEstado FROM $tabela WHERE $campoId = ?");
                    $stmt->execute([$componenteNovo]);
                    $estadoComponente = $stmt->fetchColumn();
                    
                    // Verificar se o estado permite adicionar ao computador
                    if ($estadoComponente == '2' || $estadoComponente == '4') {  // 2: Manutenção, 4: Sem Reparo
                        $nomeEstado = ($estadoComponente == '2') ? 'Manutenção' : 'Sem Reparo';
                        $componentesIndisponiveis[] = "O componente $tipo está em estado de $nomeEstado e não pode ser adicionado";
                        $componentesPermitidos = false;
                    }
                }
            }
        }
        
        // Se algum componente não estiver disponível, abortar
        if (!$componentesPermitidos) {
            throw new Exception("Alguns componentes não podem ser adicionados: " . implode(", ", $componentesIndisponiveis));
        }
        
        // Atualizar o computador
        $stmt = $conn->prepare("UPDATE computadores SET 
            COMP_REF = ?, 
            COMP_RATO = ?, 
            COMP_TECLADO = ?, 
            COMP_MONITOR = ?, 
            COMP_PM = ?, 
            COMP_PROC = ?, 
            COMP_COOLER = ?, 
            COMP_PG = ?, 
            COMP_DISCO = ?, 
            COMP_FONTE = ?, 
            COMP_MEMORIAR = ?, 
            COMP_DRIVEDVD = ?, 
            COMP_PR = ?, 
            COMP_VENTOINHA = ?, 
            COMP_CAIXA = ?, 
            COMP_ESTADO = ?
            WHERE COMP_ID = ?");
        
        $stmt->execute([
            $compRef,
            $componentes['rato'] ?: '',
            $componentes['teclado'] ?: '',
            $componentes['monitor'] ?: '',
            $componentes['placamae'] ?: '',
            $componentes['processador'] ?: '',
            $componentes['cooler'] ?: '',
            $componentes['placagrafica'] ?: '',
            $componentes['disco'] ?: '',
            $componentes['fonte'] ?: '',
            $componentes['memoriaram'] ?: '',
            $componentes['drivedvd'] ?: '',
            $componentes['placaderede'] ?: '',
            $componentes['ventoinha'] ?: '',
            $componentes['caixa'] ?: '',
            $estado,
            $compId
        ]);
        
        // Se o computador está sendo colocado em manutenção (estado 3), atualizar todos os componentes
        // associados para o estado de manutenção também
        if ($estado == 3) {
            foreach ($componentes as $tipo => $id) {
                if (!empty($id)) {
                    $tabela = $tabelasComponentes[$tipo];
                    $campoId = $camposId[$tabela];
                    $campoEstado = $camposEstado[$tabela];
                    
                    // Atualizar o estado do componente para manutenção (3)
                    $stmt = $conn->prepare("UPDATE $tabela SET $campoEstado = 3 WHERE $campoId = ?");
                    $stmt->execute([$id]);
                }
            }
        }
        
        // Liberar componentes removidos (colocar como disponíveis)
        foreach ($componentesALiberar as $tipo => $componenteId) {
            $tabela = $tabelasComponentes[$tipo];
            $campoId = $camposId[$tabela];
            $campoEstado = $camposEstado[$tabela];
            
            // Verificar o estado atual do componente
            $stmt = $conn->prepare("SELECT $campoEstado FROM $tabela WHERE $campoId = ?");
            $stmt->execute([$componenteId]);
            $estadoAtual = $stmt->fetchColumn();
            
            // Se não estiver em manutenção ou sem reparo, colocar como disponível
            if ($estadoAtual != '2' && $estadoAtual != '4') {
                $stmt = $conn->prepare("UPDATE $tabela SET $campoEstado = '1' WHERE $campoId = ?");
                $stmt->execute([$componenteId]);
            }
        }
        
        // Atualizar o estado dos componentes adicionados para "Em Uso" (3)
        foreach ($componentes as $tipo => $id) {
            if (!empty($id)) {
                $campoComp = 'COMP_' . strtoupper(substr($tipo, 0, 1)) . substr($tipo, 1);
                $componenteAtual = $computerAtual[$campoComp];
                
                // Se é um componente novo no computador
                if ($id != $componenteAtual) {
                    $tabela = $tabelasComponentes[$tipo];
                    $campoId = $camposId[$tabela];
                    $campoEstado = $camposEstado[$tabela];
                    
                    // Verificar o estado atual
                    $stmt = $conn->prepare("SELECT $campoEstado FROM $tabela WHERE $campoId = ?");
                    $stmt->execute([$id]);
                    $estadoAtual = $stmt->fetchColumn();
                    
                    // Se não estiver em manutenção ou sem reparo, colocar como em uso
                    if ($estadoAtual != '2' && $estadoAtual != '4') {
                        $stmt = $conn->prepare("UPDATE $tabela SET $campoEstado = '3' WHERE $campoId = ?");
                        $stmt->execute([$id]);
                    }
                }
            }
        }
        
        // Commit da transação
        $conn->commit();
        
        // Resposta de sucesso
        $response = [
            'status' => 'success',
            'message' => 'Computador atualizado com sucesso!'
        ];
    } catch (Exception $e) {
        // Rollback em caso de erro
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        // Resposta de erro
        $response = [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
    
    // Retornar resposta em JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!-- Formulário para editar computador -->
<div class="modal fade" id="modalEditarComputador" tabindex="-1" aria-labelledby="modalEditarComputadorLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarComputadorLabel">Editar Computador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarComputador" method="post">
                    <input type="hidden" id="edit_comp_id" name="comp_id">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_comp_ref" class="form-label">Referência do Computador*</label>
                            <input type="text" class="form-control" id="edit_comp_ref" name="comp_ref" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_comp_estado" class="form-label">Estado*</label>
                            <select class="form-select" id="edit_comp_estado" name="comp_estado" required>
                                <option value="">Selecione...</option>
                                <!-- Estados serão carregados via AJAX -->
                            </select>
                        </div>
                    </div>
                    
                    <h5 class="mt-4 mb-3">Componentes</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="edit_comp_rato" class="form-label">Rato</label>
                            <select class="form-select" id="edit_comp_rato" name="comp_rato">
                                <option value="">Nenhum</option>
                                <!-- Opções serão carregadas via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_comp_teclado" class="form-label">Teclado</label>
                            <select class="form-select" id="edit_comp_teclado" name="comp_teclado">
                                <option value="">Nenhum</option>
                                <!-- Opções serão carregadas via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_comp_monitor" class="form-label">Monitor</label>
                            <select class="form-select" id="edit_comp_monitor" name="comp_monitor">
                                <option value="">Nenhum</option>
                                <!-- Opções serão carregadas via AJAX -->
                            </select>
                        </div>
                    </div>
                    
                    <!-- Repetir o padrão para os outros componentes -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="edit_comp_pm" class="form-label">Placa Mãe</label>
                            <select class="form-select" id="edit_comp_pm" name="comp_pm">
                                <option value="">Nenhum</option>
                                <!-- Opções serão carregadas via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_comp_proc" class="form-label">Processador</label>
                            <select class="form-select" id="edit_comp_proc" name="comp_proc">
                                <option value="">Nenhum</option>
                                <!-- Opções serão carregadas via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_comp_cooler" class="form-label">Cooler</label>
                            <select class="form-select" id="edit_comp_cooler" name="comp_cooler">
                                <option value="">Nenhum</option>
                                <!-- Opções serão carregadas via AJAX -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="edit_comp_pg" class="form-label">Placa Gráfica</label>
                            <select class="form-select" id="edit_comp_pg" name="comp_pg">
                                <option value="">Nenhum</option>
                                <!-- Opções serão carregadas via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_comp_disco" class="form-label">Disco</label>
                            <select class="form-select" id="edit_comp_disco" name="comp_disco">
                                <option value="">Nenhum</option>
                                <!-- Opções serão carregadas via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_comp_fonte" class="form-label">Fonte</label>
                            <select class="form-select" id="edit_comp_fonte" name="comp_fonte">
                                <option value="">Nenhum</option>
                                <!-- Opções serão carregadas via AJAX -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="edit_comp_memoriar" class="form-label">Memória RAM</label>
                            <select class="form-select" id="edit_comp_memoriar" name="comp_memoriar">
                                <option value="">Nenhum</option>
                                <!-- Opções serão carregadas via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_comp_drivedvd" class="form-label">Drive DVD</label>
                            <select class="form-select" id="edit_comp_drivedvd" name="comp_drivedvd">
                                <option value="">Nenhum</option>
                                <!-- Opções serão carregadas via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_comp_pr" class="form-label">Placa de Rede</label>
                            <select class="form-select" id="edit_comp_pr" name="comp_pr">
                                <option value="">Nenhum</option>
                                <!-- Opções serão carregadas via AJAX -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="edit_comp_ventoinha" class="form-label">Ventoinha</label>
                            <select class="form-select" id="edit_comp_ventoinha" name="comp_ventoinha">
                                <option value="">Nenhum</option>
                                <!-- Opções serão carregadas via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_comp_caixa" class="form-label">Caixa</label>
                            <select class="form-select" id="edit_comp_caixa" name="comp_caixa">
                                <option value="">Nenhum</option>
                                <!-- Opções serão carregadas via AJAX -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <small>* Campos obrigatórios</small><br>
                        <small>Nota: Apenas componentes com estado "Disponível" ou "Em Uso" podem ser adicionados a um computador.</small><br>
                        <small>Componentes em "Manutenção" permanecerão no computador mas aparecerão destacados no inventário.</small><br>
                        <small>Componentes marcados como "Sem Reparo" serão automaticamente removidos do computador.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnAtualizarComputador">Salvar Alterações</button>
            </div>
        </div>
    </div>
</div> 