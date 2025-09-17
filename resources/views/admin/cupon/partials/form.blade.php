<div class="row gy-3">
    <div class="col-md-6">
        <label class="form-label">Código *</label>
        <input type="text" name="codigo" class="form-control" value="{{ old('codigo', $cupon->codigo ?? '') }}"
            required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Tipo *</label>
        <select name="tipo" class="form-control" required>
            <option value="percentual" {{ old('tipo', $cupon->tipo ?? '') == 'percentual' ? 'selected' : '' }}>
                Percentual</option>
            <option value="valor_fixo" {{ old('tipo', $cupon->tipo ?? '') == 'valor_fixo' ? 'selected' : '' }}>Valor
                Fixo</option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Montante *</label>
        <input type="number" step="0.01" name="montante" class="form-control"
            value="{{ old('montante', $cupon->montante ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Quantidade (deixe vazio para ilimitado)</label>
        <input type="number" name="quantidade" class="form-control"
            value="{{ old('quantidade', $cupon->quantidade ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">Data Início *</label>
        <input type="date" name="data_inicio" class="form-control"
            value="{{ old('data_inicio', $cupon->data_inicio ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Data Final *</label>
        <input type="date" name="data_final" class="form-control"
            value="{{ old('data_final', $cupon->data_final ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Valor Mínimo</label>
        <input type="number" step="0.01" name="valor_minimo" class="form-control"
            value="{{ old('valor_minimo', $cupon->valor_minimo ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">Valor Máximo</label>
        <input type="number" step="0.01" name="valor_maximo" class="form-control"
            value="{{ old('valor_maximo', $cupon->valor_maximo ?? '') }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">Aplicar Cupom em</label>
        <select name="modelo" class="form-control">
            <option value="">Todos</option>
            <option value="categoria" {{ old('modelo', $cupon->modelo ?? '') == 'categoria' ? 'selected' : '' }}>
                Categoria</option>
            <option value="marca" {{ old('modelo', $cupon->modelo ?? '') == 'marca' ? 'selected' : '' }}>Marca
            </option>
            <option value="produto" {{ old('modelo', $cupon->modelo ?? '') == 'produto' ? 'selected' : '' }}>Produto
            </option>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Categoria</label>
        <select name="categoria_id" class="form-control">
            <option value="">-- Todas --</option>
            @foreach (\App\Models\Category::all() as $category)
                <option value="{{ $category->id }}"
                    {{ old('categoria_id', $cupon->categoria_id ?? '') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6" id="produto-field">
        <label class="form-label">Produto</label>
        <select name="produto_id" class="form-control">
            <option value="">-- Todos --</option>
            @foreach (\App\Models\Product::all() as $product)
                <option value="{{ $product->id }}"
                    {{ old('produto_id', $cupon->produto_id ?? '') == $product->id ? 'selected' : '' }}>
                    {{ $product->external_name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Marca</label>
        <select name="marca_id" class="form-control">
            <option value="">-- Todas --</option>
            @foreach (\App\Models\Brand::all() as $brand)
                <option value="{{ $brand->id }}"
                    {{ old('marca_id', $cupon->marca_id ?? '') == $brand->id ? 'selected' : '' }}>
                    {{ $brand->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-12 mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-success">{{ $button }}</button>
        <a href="{{ route('admin.cupons.index') }}" class="btn btn-secondary">Cancelar</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modelo = document.querySelector('select[name="modelo"]');
        const categoriaField = document.querySelector('select[name="categoria_id"]').closest('.col-md-6');
        const marcaField = document.querySelector('select[name="marca_id"]').closest('.col-md-6');
        const produtoField = document.querySelector('select[name="produto_id"]').closest('.col-md-6');
        const form = document.querySelector('form'); // pega o form pai
    
        if (!modelo || !form) return;
    
        // Função para mostrar/esconder os campos conforme o modelo
        function toggleFields() {
            const value = modelo.value;
            categoriaField.style.display = value === 'categoria' ? 'block' : 'none';
            marcaField.style.display = value === 'marca' ? 'block' : 'none';
            produtoField.style.display = value === 'produto' ? 'block' : 'none';
        }
    
        modelo.addEventListener('change', toggleFields);
        toggleFields(); // inicializa na carga
    
        // Função para logar os dados do formulário no submit
        form.addEventListener('submit', function(e) {
            const data = {
                codigo: form.querySelector('input[name="codigo"]').value,
                tipo: form.querySelector('select[name="tipo"]').value,
                montante: form.querySelector('input[name="montante"]').value,
                quantidade: form.querySelector('input[name="quantidade"]').value,
                data_inicio: form.querySelector('input[name="data_inicio"]').value,
                data_final: form.querySelector('input[name="data_final"]').value,
                valor_minimo: form.querySelector('input[name="valor_minimo"]').value,
                valor_maximo: form.querySelector('input[name="valor_maximo"]').value,
                modelo: form.querySelector('select[name="modelo"]').value,
                categoria_id: form.querySelector('select[name="categoria_id"]').value,
                marca_id: form.querySelector('select[name="marca_id"]').value,
                produto_id: form.querySelector('select[name="produto_id"]').value,
            };
    
            console.log('Dados do formulário enviados:', data);
            // e.preventDefault(); // descomente só se quiser segurar o envio pra debugar
        });
    });
    </script>
    