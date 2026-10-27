<?php
// Modal de adição de componentes
?>
<!-- Modal para adicionar componente à sala -->
<div class="modal fade" id="addComponentModal" tabindex="-1" aria-labelledby="addComponentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addComponentModalLabel">Adicionar Componente à Sala</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="sala_processar.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_component">
                    <input type="hidden" name="sala_id" value="<?= $sala['id_sala'] ?>">
                    <input type="hidden" name="sala_nome" value="<?= $sala['sala'] ?>">
                    
                    <div class="mb-3">
                        <label for="componente_tipo" class="form-label">Tipo de Componente</label>
                        <select class="form-select" id="componente_tipo" name="componente_tipo" required>
                            <option value="">Selecione o tipo...</option>
                            <option value="COMPUTADOR">Computador</option>
                            <option value="RATO">Rato</option>
                            <option value="TECLADO">Teclado</option>
                            <option value="MONITOR">Monitor</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="componenteComputadorSelect" style="display: none;">
                        <label for="computador_id" class="form-label">Selecione o Computador</label>
                        <select class="form-select" id="computador_id" name="computador_id">
                            <option value="">Selecione o computador...</option>
                            <?php
                            // Buscar computadores disponíveis
                            $stmt = $pdo->prepare("SELECT COMP_ID, COMP_REF FROM computadores WHERE COMP_ESTADO = '1' AND COMP_SALA = ''");
                            $stmt->execute();
                            $computadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($computadores as $computador) {
                                echo '<option value="' . htmlspecialchars($computador['COMP_ID']) . '">' . htmlspecialchars($computador['COMP_REF']) . '</option>';
                            }
                            ?>
                        </select>
                        <div class="form-text">Apenas computadores com estado "Disponível" são mostrados.</div>
                    </div>
                    
                    <div class="mb-3" id="componenteRatoSelect" style="display: none;">
                        <label for="rato_id" class="form-label">Selecione o Rato</label>
                        <select class="form-select" id="rato_id" name="rato_id">
                            <option value="">Selecione o rato...</option>
                            <?php
                            // Buscar ratos disponíveis e não associados a computadores
                            $stmt = $pdo->prepare("SELECT RATOS_ID, RATOS_REF FROM ratos WHERE RATOS_ESTADO = '1' AND RATOS_SALA = '' AND RATOS_COMP = ''");
                            $stmt->execute();
                            $ratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($ratos as $rato) {
                                echo '<option value="' . htmlspecialchars($rato['RATOS_ID']) . '">' . htmlspecialchars($rato['RATOS_REF']) . '</option>';
                            }
                            ?>
                        </select>
                        <div class="form-text">Apenas ratos com estado "Disponível" e não associados a computadores são mostrados.</div>
                    </div>
                    
                    <div class="mb-3" id="componenteTecladoSelect" style="display: none;">
                        <label for="teclado_id" class="form-label">Selecione o Teclado</label>
                        <select class="form-select" id="teclado_id" name="teclado_id">
                            <option value="">Selecione o teclado...</option>
                            <?php
                            // Buscar teclados disponíveis e não associados a computadores
                            $stmt = $pdo->prepare("SELECT TECLADO_ID, TECLADO_REF FROM teclados WHERE TECLADO_ESTADO = '1' AND TECLADO_SALA = '' AND TECLADO_COMP = ''");
                            $stmt->execute();
                            $teclados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($teclados as $teclado) {
                                echo '<option value="' . htmlspecialchars($teclado['TECLADO_ID']) . '">' . htmlspecialchars($teclado['TECLADO_REF']) . '</option>';
                            }
                            ?>
                        </select>
                        <div class="form-text">Apenas teclados com estado "Disponível" e não associados a computadores são mostrados.</div>
                    </div>
                    
                    <div class="mb-3" id="componenteMonitorSelect" style="display: none;">
                        <label for="monitor_id" class="form-label">Selecione o Monitor</label>
                        <select class="form-select" id="monitor_id" name="monitor_id">
                            <option value="">Selecione o monitor...</option>
                            <?php
                            // Buscar monitores disponíveis e não associados a computadores
                            $stmt = $pdo->prepare("SELECT MONITOR_ID, MONITOR_REF FROM monitor WHERE MONITOR_ESTADO = '1' AND MONITOR_SALA = '' AND MONITOR_COMP = ''");
                            $stmt->execute();
                            $monitores = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($monitores as $monitor) {
                                echo '<option value="' . htmlspecialchars($monitor['MONITOR_ID']) . '">' . htmlspecialchars($monitor['MONITOR_REF']) . '</option>';
                            }
                            ?>
                        </select>
                        <div class="form-text">Apenas monitores com estado "Disponível" e não associados a computadores são mostrados.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Adicionar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar sala -->
<div class="modal fade" id="editRoomModal" tabindex="-1" aria-labelledby="editRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRoomModalLabel">Editar Sala</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="sala_processar.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_sala">
                    <input type="hidden" name="sala_id" value="<?= $sala['id_sala'] ?>">
                    
                    <div class="mb-3">
                        <label for="nome_sala" class="form-label">Nome da Sala</label>
                        <input type="text" class="form-control" id="nome_sala" name="nome_sala" value="<?= htmlspecialchars($sala['sala']) ?>" required>
                        <div class="form-text">Ex.: 5.7, A1, Laboratório 3</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para remover componente -->
<div class="modal fade" id="removeComponentModal" tabindex="-1" aria-labelledby="removeComponentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeComponentModalLabel">Remover Componente da Sala</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="sala_processar.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="remove_component">
                    <input type="hidden" name="sala_id" value="<?= $sala['id_sala'] ?>">
                    <input type="hidden" name="componente_tipo" id="remove_componente_tipo">
                    <input type="hidden" name="componente_id" id="remove_componente_id">
                    
                    <p>Deseja remover <span id="componente_ref_display"></span> desta sala?</p>
                    <p>O componente voltará ao estado "Disponível".</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Remover</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mostrar seleção de componente de acordo com o tipo selecionado
        const componenteTipoSelect = document.getElementById('componente_tipo');
        
        componenteTipoSelect.addEventListener('change', function() {
            // Esconder todos os selects
            document.getElementById('componenteComputadorSelect').style.display = 'none';
            document.getElementById('componenteRatoSelect').style.display = 'none';
            document.getElementById('componenteTecladoSelect').style.display = 'none';
            document.getElementById('componenteMonitorSelect').style.display = 'none';
            
            // Mostrar o select correspondente ao tipo selecionado
            const tipoSelecionado = this.value;
            if (tipoSelecionado === 'COMPUTADOR') {
                document.getElementById('componenteComputadorSelect').style.display = 'block';
            } else if (tipoSelecionado === 'RATO') {
                document.getElementById('componenteRatoSelect').style.display = 'block';
            } else if (tipoSelecionado === 'TECLADO') {
                document.getElementById('componenteTecladoSelect').style.display = 'block';
            } else if (tipoSelecionado === 'MONITOR') {
                document.getElementById('componenteMonitorSelect').style.display = 'block';
            }
        });
        
        // Preencher dados no modal de remoção
        const removeComponentModal = document.getElementById('removeComponentModal');
        if (removeComponentModal) {
            removeComponentModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const tipo = button.getAttribute('data-tipo');
                const id = button.getAttribute('data-id');
                const ref = button.getAttribute('data-ref');
                
                document.getElementById('remove_componente_tipo').value = tipo;
                document.getElementById('remove_componente_id').value = id;
                document.getElementById('componente_ref_display').textContent = `${tipo} (${ref})`;
            });
        }
    });
</script> 