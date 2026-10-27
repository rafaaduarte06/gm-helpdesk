<!-- Modal -->
<div class="modal fade" id="modalAdicionarComputador" tabindex="-1" aria-labelledby="modalAdicionarComputadorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAdicionarComputadorLabel">Adicionar Novo Computador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAdicionarComputador">
                    <!-- Campos Principais -->
                    <div class="row mb-4">
                        <div class="col-md-6 col-12 mb-3 mb-md-0">
                            <label for="comp_ref" class="form-label">Referência do Computador*</label>
                            <input type="text" class="form-control" id="comp_ref" name="comp_ref" required 
                                   oninvalid="this.setCustomValidity('Por favor, preencha a referência do computador')"
                                   oninput="this.setCustomValidity('')">
                            <div class="invalid-feedback">
                                Por favor, preencha a referência do computador.
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <label for="comp_estado" class="form-label">Estado*</label>
                            <select class="form-select" id="comp_estado" name="comp_estado" required
                                    oninvalid="this.setCustomValidity('Por favor, selecione um estado')"
                                    oninput="this.setCustomValidity('')">
                                <option value="">Selecione...</option>
                            </select>
                            <div class="invalid-feedback">
                                Por favor, selecione um estado.
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-4">Componentes</h5>
                    <div class="component-grid">
                        <div class="component-item">
                            <label for="comp_rato" class="form-label">Rato</label>
                            <select class="form-select" id="comp_rato" name="comp_rato">
                                <option value="">Nenhum</option>
                            </select>
                        </div>
                        <div class="component-item">
                            <label for="comp_teclado" class="form-label">Teclado</label>
                            <select class="form-select" id="comp_teclado" name="comp_teclado">
                                <option value="">Nenhum</option>
                            </select>
                        </div>
                        <div class="component-item">
                            <label for="comp_monitor" class="form-label">Monitor</label>
                            <select class="form-select" id="comp_monitor" name="comp_monitor">
                                <option value="">Nenhum</option>
                            </select>
                        </div>
                        <div class="component-item">
                            <label for="comp_pm" class="form-label">Motherboard</label>
                            <select class="form-select" id="comp_pm" name="comp_pm">
                                <option value="">Nenhum</option>
                            </select>
                        </div>
                        <div class="component-item">
                            <label for="comp_proc" class="form-label">Processador</label>
                            <select class="form-select" id="comp_proc" name="comp_proc">
                                <option value="">Nenhum</option>
                            </select>
                        </div>
                        <div class="component-item">
                            <label for="comp_cooler" class="form-label">Cooler</label>
                            <select class="form-select" id="comp_cooler" name="comp_cooler">
                                <option value="">Nenhum</option>
                            </select>
                        </div>
                        <div class="component-item">
                            <label for="comp_pg" class="form-label">Placa Gráfica</label>
                            <select class="form-select" id="comp_pg" name="comp_pg">
                                <option value="">Nenhum</option>
                            </select>
                        </div>
                        <div class="component-item">
                            <label for="comp_disco" class="form-label">Disco</label>
                            <select class="form-select" id="comp_disco" name="comp_disco">
                                <option value="">Nenhum</option>
                            </select>
                        </div>
                        <div class="component-item">
                            <label for="comp_fonte" class="form-label">Fonte</label>
                            <select class="form-select" id="comp_fonte" name="comp_fonte">
                                <option value="">Nenhum</option>
                            </select>
                        </div>
                        <div class="component-item">
                            <label for="comp_memoriar" class="form-label">Memória RAM</label>
                            <select class="form-select" id="comp_memoriar" name="comp_memoriar">
                                <option value="">Nenhum</option>
                            </select>
                        </div>
                        <div class="component-item">
                            <label for="comp_drivedvd" class="form-label">Drive DVD</label>
                            <select class="form-select" id="comp_drivedvd" name="comp_drivedvd">
                                <option value="">Nenhum</option>
                            </select>
                        </div>
                        <div class="component-item">
                            <label for="comp_pr" class="form-label">Placa de Rede</label>
                            <select class="form-select" id="comp_pr" name="comp_pr">
                                <option value="">Nenhum</option>
                            </select>
                        </div>
                        <div class="component-item">
                            <label for="comp_ventoinha" class="form-label">Ventoinha</label>
                            <select class="form-select" id="comp_ventoinha" name="comp_ventoinha">
                                <option value="">Nenhum</option>
                            </select>
                        </div>
                        <div class="component-item">
                            <label for="comp_caixa" class="form-label">Caixa</label>
                            <select class="form-select" id="comp_caixa" name="comp_caixa">
                                <option value="">Nenhum</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btnSalvarComputador">
                    <i class="bi bi-check-circle me-2"></i>Salvar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.modal-lg {
    max-width: 1200px;
}

.modal {
    display: flex !important;
    align-items: center;
    justify-content: center;
}

.modal-dialog {
    margin: 1rem auto;
    max-height: calc(100vh - 2rem);
    overflow-y: auto;
    width: 95%;
}

.modal-body {
    padding: 2rem;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    display: inline-block;
    width: 100%;
    font-size: 1rem;
}

.form-label.no-wrap {
    white-space: nowrap;
}

.form-select, .form-control {
    border-radius: 6px;
    border-color: #dee2e6;
    height: 42px;
    font-size: 1rem;
    padding: 0.5rem 1rem;
}

.form-select:focus, .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
}

.form-control:required:invalid:focus,
.form-select:required:invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.form-control:required:valid:focus,
.form-select:required:valid:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
}

.form-control.is-invalid,
.form-select.is-invalid {
    border-color: #dc3545;
    padding-right: calc(1.5em + 0.75rem);
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.invalid-feedback {
    display: none;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
}

.form-control.is-invalid ~ .invalid-feedback,
.form-select.is-invalid ~ .invalid-feedback {
    display: block;
}

h5 {
    color: #333;
    font-weight: 600;
    margin-top: 0.5rem;
    font-size: 1.2rem;
}

.modal-content {
    border-radius: 12px;
    max-height: 95vh;
    overflow: hidden;
}

.modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1.5rem 2rem;
    position: sticky;
    top: 0;
    z-index: 5;
}

.modal-header .modal-title {
    font-weight: 700;
    color: #333;
    font-size: 1.4rem;
    display: block !important;
    visibility: visible !important;
}

.modal-footer {
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 1.5rem 2rem;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    position: sticky;
    bottom: 0;
    z-index: 5;
}

.modal-footer .btn {
    padding: 0.6rem 1.8rem;
    font-size: 1rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border-radius: 8px;
    min-width: 150px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.modal-footer .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.modal-footer .btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.modal-footer .btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.modal-footer .btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

.modal-footer .btn-danger:hover {
    background-color: #bb2d3b;
    border-color: #b02a37;
}

.component-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 25px;
    padding: 0.5rem;
}

.component-item {
    display: flex;
    flex-direction: column;
    padding: 0.5rem;
}

@media (max-width: 768px) {
    .component-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .modal-lg {
        max-width: 95%;
    }
    
    .modal-header {
        padding: 1.2rem;
    }
    
    .modal-header .modal-title {
        font-size: 1.3rem;
    }
    
    .modal-footer {
        flex-direction: row;
        justify-content: space-between;
        padding: 1.2rem;
    }
    
    .modal-footer .btn {
        min-width: 120px;
        padding: 0.5rem 1rem;
    }
    
    .row {
        margin-left: 0;
        margin-right: 0;
    }
    
    .col-sm-6 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
}

@media (max-width: 576px) {
    .component-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .form-label.no-wrap {
        white-space: normal;
    }
    
    .modal-dialog {
        margin: 0.5rem;
        max-height: calc(100vh - 1rem);
        width: 95%;
    }
    
    .modal-content {
        max-height: calc(100vh - 1rem);
        border-radius: 10px;
    }
    
    .modal-header {
        padding: 1rem;
    }
    
    .modal-header .modal-title {
        font-size: 1.2rem;
        margin-right: 10px;
    }
    
    .modal-body {
        padding: 1rem;
        overflow-y: auto;
    }
    
    .modal-footer {
        flex-direction: column-reverse;
        padding: 1rem;
    }
    
    .modal-footer .btn {
        width: 100%;
        margin: 5px 0;
        min-height: 50px;
        font-size: 1rem;
        font-weight: 600;
    }
    
    .btn i {
        font-size: 1.1rem;
    }
    
    .row.mb-4 {
        flex-direction: column;
        margin-bottom: 1rem !important;
    }
    
    .col-sm-6 {
        width: 100%;
        margin-bottom: 1rem;
    }
    
    h5.mb-4 {
        margin-bottom: 1rem !important;
        font-size: 1.1rem;
    }
}
</style> 