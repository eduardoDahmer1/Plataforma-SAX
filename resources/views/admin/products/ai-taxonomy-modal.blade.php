<div class="modal fade" id="aiTaxonomyModal" tabindex="-1" aria-labelledby="aiTaxonomyTitle" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <form id="aiTaxonomyForm" data-url="{{ route('admin.products.ai-taxonomy.store') }}">
            <div class="modal-header"><h5 class="modal-title" id="aiTaxonomyTitle">Crear clasificación</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body">
                <div id="aiTaxonomyError" class="alert alert-danger d-none" role="alert"></div>
                <label for="aiTaxonomyType" class="form-label">Tipo de clasificación</label>
                <select id="aiTaxonomyType" class="form-select mb-3"><option value="subcategory">Subcategoría</option><option value="childcategory">Categoría hija</option></select>
                <label for="aiTaxonomyName" class="form-label">Nombre</label>
                <input id="aiTaxonomyName" class="form-control mb-3" required maxlength="255">
                <label for="aiTaxonomyCategory" class="form-label">Categoría padre</label>
                <select id="aiTaxonomyCategory" class="form-select mb-3" required></select>
                <div id="aiTaxonomySubcategoryGroup"><label for="aiTaxonomySubcategory" class="form-label">Subcategoría padre</label><select id="aiTaxonomySubcategory" class="form-select mb-3"></select></div>
                <label for="aiTaxonomyAssignment" class="form-label">Asignación al producto</label>
                <select id="aiTaxonomyAssignment" class="form-select"><option value="none">Solo crear</option><option value="primary">Clasificación principal</option><option value="additional">Clasificación adicional</option></select>
                <p class="small text-muted mt-2 mb-0">La clasificación se crea al confirmar. La asignación al producto se guarda al guardar sus cambios.</p>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Crear</button></div>
        </form>
    </div></div>
</div>
