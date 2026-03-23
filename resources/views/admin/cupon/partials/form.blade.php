<div class="row g-4">
    {{-- Seção: Identificação e Valor --}}
    <div class="col-12 mb-2">
        <span class="sax-section-title">Parámetros Principales</span>
    </div>

    <div class="col-md-4">
        <label class="sax-label">Código del Cupón *</label>
        <input type="text" name="codigo" class="form-control sax-input fw-bold text-uppercase" 
               value="{{ old('codigo', $cupon->codigo ?? '') }}" placeholder="EJ: PROMO2026" required>
    </div>

    <div class="col-md-4">
        <label class="sax-label">Tipo de Descuento *</label>
        <select name="tipo" class="form-select sax-input" required>
            <option value="percentual" {{ old('tipo', $cupon->tipo ?? '') == 'percentual' ? 'selected' : '' }}>PORCENTAJE (%)</option>
            <option value="valor_fixo" {{ old('tipo', $cupon->tipo ?? '') == 'valor_fixo' ? 'selected' : '' }}>VALOR FIJO ($)</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="sax-label">Monto / Montante *</label>
        <input type="number" step="0.01" name="montante" class="form-control sax-input font-monospace"
               value="{{ old('montante', $cupon->montante ?? '') }}" required>
    </div>

    {{-- Seção: Limites e Validade --}}
    <div class="col-12 mt-5 mb-2">
        <span class="sax-section-title">Vigencia y Límites</span>
    </div>

    <div class="col-md-3">
        <label class="sax-label">Cantidad Inicial</label>
        <input type="number" name="quantidade" class="form-control sax-input" 
               placeholder="∞" value="{{ old('quantidade', $cupon->quantidade ?? '') }}">
    </div>

    <div class="col-md-3">
        <label class="sax-label">Válido Desde *</label>
        <input type="date" name="data_inicio" class="form-control sax-input"
               value="{{ old('data_inicio', $cupon->data_inicio ?? '') }}" required>
    </div>

    <div class="col-md-3">
        <label class="sax-label">Válido Hasta *</label>
        <input type="date" name="data_final" class="form-control sax-input"
               value="{{ old('data_final', $cupon->data_final ?? '') }}" required>
    </div>

    <div class="col-md-3">
        <label class="sax-label">Compra Mínima ($)</label>
        <input type="number" step="0.01" name="valor_minimo" class="form-control sax-input"
               value="{{ old('valor_minimo', $cupon->valor_minimo ?? '') }}">
    </div>

    {{-- Seção: Escopo de Aplicação --}}
    <div class="col-12 mt-5 mb-2">
        <span class="sax-section-title">Reglas de Aplicación</span>
    </div>

    <div class="col-md-6">
        <label class="sax-label">Restringir Cupón por:</label>
        <select name="modelo" class="form-select sax-input border-dark">
            <option value="">APLICAR A TODO EL SITIO</option>
            <option value="categoria" {{ old('modelo', $cupon->modelo ?? '') == 'categoria' ? 'selected' : '' }}>CATEGORÍA ESPECÍFICA</option>
            <option value="marca" {{ old('modelo', $cupon->modelo ?? '') == 'marca' ? 'selected' : '' }}>MARCA ESPECÍFICA</option>
            <option value="produto" {{ old('modelo', $cupon->modelo ?? '') == 'produto' ? 'selected' : '' }}>PRODUCTO ESPECÍFICO</option>
        </select>
    </div>

    <div class="col-md-6" id="categoria-field">
        <label class="sax-label">Seleccionar Categoría</label>
        <select name="categoria_id" class="form-select sax-input">
            <option value="">-- Todas --</option>
            @foreach (\App\Models\Category::all() as $category)
                <option value="{{ $category->id }}" {{ old('categoria_id', $cupon->categoria_id ?? '') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6" id="produto-field">
        <label class="sax-label">Seleccionar Producto</label>
        <select name="produto_id" class="form-select sax-input">
            <option value="">-- Todos --</option>
            @foreach (\App\Models\Product::all() as $product)
                <option value="{{ $product->id }}" {{ old('produto_id', $cupon->produto_id ?? '') == $product->id ? 'selected' : '' }}>
                    {{ $product->external_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6" id="marca-field">
        <label class="sax-label">Seleccionar Marca</label>
        <select name="marca_id" class="form-select sax-input">
            <option value="">-- Todas --</option>
            @foreach (\App\Models\Brand::all() as $brand)
                <option value="{{ $brand->id }}" {{ old('marca_id', $cupon->marca_id ?? '') == $brand->id ? 'selected' : '' }}>
                    {{ $brand->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Botões --}}
    <div class="col-12 mt-5 pt-4 border-top d-flex gap-3">
        <button type="submit" class="btn btn-dark rounded-0 px-5 text-uppercase fw-bold x-small tracking-wider">
            {{ $button }}
        </button>
        <a href="{{ route('admin.cupons.index') }}" class="btn btn-outline-secondary rounded-0 px-4 text-uppercase fw-bold x-small tracking-wider">
            Cancelar
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modelo = document.querySelector('select[name="modelo"]');
    const categoriaWrapper = document.getElementById('categoria-field');
    const marcaWrapper = document.getElementById('marca-field');
    const produtoWrapper = document.getElementById('produto-field');

    function toggleFields() {
        const value = modelo.value;
        categoriaWrapper.style.display = value === 'categoria' ? 'block' : 'none';
        marcaWrapper.style.display = value === 'marca' ? 'block' : 'none';
        produtoWrapper.style.display = value === 'produto' ? 'block' : 'none';
    }

    modelo.addEventListener('change', toggleFields);
    toggleFields();
});
</script>