<x-admin.lang-field name="name" label="Nombre" :pt="$translationPt ?? ''" :es="$translationEs ?? ''" :en="$translationEn ?? ''" :required="true" :maxlength="255" />
<button type="button" class="btn btn-sm btn-outline-secondary my-2" data-attribute-translate>Sugerir traducciones con IA</button>
<p class="small text-muted">Completá ES, PT y EN. La clave técnica se genera automáticamente.</p>
<input type="hidden" name="is_active" value="0">
<label class="form-check my-2"><input class="form-check-input" name="is_active" type="checkbox" value="1" @checked($active ?? true)> Activo</label>
@if(!($optionForm ?? false))
    <label class="form-label">Tipo</label>
    <select class="form-select mb-2" name="data_type">
        @foreach(['single'=>'Selección única', 'multiple'=>'Selección múltiple', 'boolean'=>'Sí / No', 'number'=>'Número'] as $value=>$label)
            <option value="{{ $value }}" @selected(($dataType ?? 'single') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <label class="form-label">Unidad (solo números)</label><input class="form-control mb-2" name="unit" maxlength="20" value="{{ $unit ?? '' }}" placeholder="mm, dioptrías…">
    <input type="hidden" name="inherit_from_parent" value="0">
    <label class="form-check"><input class="form-check-input" type="checkbox" name="inherit_from_parent" value="1" @checked($inherit ?? false)> Heredar mediante parent_id</label>
    <p class="small text-muted">Dejá desmarcado para medidas, colores o propiedades específicas de cada variante. Nunca se hereda por familia de color.</p>
@endif
