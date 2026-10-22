<div class="modal fade" id="catalogAttributeModal" tabindex="-1" aria-labelledby="catalogAttributeModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <form id="catalogAttributeDefinitionForm">
            <div class="modal-header"><h5 class="modal-title" id="catalogAttributeModalTitle">Crear atributo</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body">
                <div data-attribute-manager class="d-none">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3"><p class="small text-muted mb-0">Administrá atributos, opciones y traducciones sin salir del producto.</p><button type="button" class="btn btn-sm btn-outline-secondary" data-manager-create>Crear atributo</button></div>
                    <label class="visually-hidden" for="catalogAttributeManagerSearch">Buscar en el diccionario</label>
                    <input id="catalogAttributeManagerSearch" type="search" class="form-control mb-3" data-manager-search placeholder="Buscar nombre o código" autocomplete="off">
                    <div data-manager-list></div>
                </div>
                <button type="button" class="btn btn-sm btn-link px-0 mb-2 d-none" data-attribute-manager-back><i class="fas fa-arrow-left me-1" aria-hidden="true"></i>Volver al diccionario</button>
                <div class="alert alert-danger d-none" data-attribute-modal-error role="alert"></div>
                <div data-attribute-definition-fields>@include('admin.catalog-attributes.fields')</div>
                <p class="small text-muted mt-3" data-attribute-save-hint>El diccionario se guarda al pulsar Guardar. La asignación al producto se guarda con el producto.</p>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
        </form>
    </div></div>
</div>
