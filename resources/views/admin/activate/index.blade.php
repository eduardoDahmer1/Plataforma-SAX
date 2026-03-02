@extends('layout.admin')

@section('content')
<style>
    /* Estilos anteriores mantidos com melhorias */
    .sections-wrapper { display: flex; flex-direction: column; gap: 25px; padding: 15px; margin-bottom: 80px; }
    .card-sax { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); padding: 20px; }
    .items-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px; }
    .list-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #fcfcfc; border: 1px solid #eee; border-radius: 6px; }
    
    .list-item span { font-size: 0.85rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; }

    /* Botões */
    .status-badge {
        padding: 5px 8px; border-radius: 4px; color: white; font-weight: 700; font-size: 0.7rem;
        text-transform: uppercase; display: inline-flex; align-items: center; justify-content: center;
        border: none; cursor: pointer; min-width: 75px; gap: 4px; transition: 0.3s;
    }
    .status-active { background-color: #198754; } 
    .status-inactive { background-color: #212529; }

    /* Botão Salvar Flutuante */
    .footer-actions {
        position: fixed; bottom: 0; left: 0; right: 0; 
        background: rgba(255,255,255,0.9); padding: 15px;
        box-shadow: 0 -5px 15px rgba(0,0,0,0.1);
        display: flex; justify-content: center; z-index: 1000;
        backdrop-filter: blur(5px);
    }
    .btn-save-all {
        background: #dc3545; color: white; border: none; padding: 12px 40px;
        border-radius: 50px; font-weight: bold; font-size: 1rem; cursor: pointer;
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
    }
    .btn-save-all:hover { transform: translateY(-2px); background: #bb2d3b; }
</style>

<form action="{{ route('admin.activate.updateAll') }}" method="POST">
    @csrf
    <div class="container-fluid">
        <div class="sections-wrapper">
            
            {{-- Seção de Categorias --}}
            <div class="card-sax">
                <h2><i class="fa-solid fa-layer-group"></i> Categorias</h2>
                <div class="items-grid">
                    @foreach($categories as $category)
                    <div class="list-item">
                        <span title="{{ $category->name }}">{{ $category->name }}</span>
                        {{-- O nome do input cria um array associativo no PHP: categories[id] = status --}}
                        <input type="hidden" name="categories[{{ $category->id }}]" value="{{ $category->status }}" class="status-input">
                        <button type="button" class="status-badge {{ $category->status == 1 ? 'status-active' : 'status-inactive' }}" onclick="toggleUI(this)">
                            {{ $category->status == 1 ? 'Ativo' : 'Inativo' }}
                            <span>{{ $category->status == 1 ? '▴' : '▾' }}</span>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Seção de Marcas --}}
            <div class="card-sax">
                <h2><i class="fa-solid fa-tag"></i> Marcas</h2>
                <div class="items-grid">
                    @foreach($brands as $brand)
                    <div class="list-item">
                        <span title="{{ $brand->name }}">{{ $brand->name }}</span>
                        <input type="hidden" name="brands[{{ $brand->id }}]" value="{{ $brand->status }}" class="status-input">
                        <button type="button" class="status-badge {{ $brand->status == 1 ? 'status-active' : 'status-inactive' }}" onclick="toggleUI(this)">
                            {{ $brand->status == 1 ? 'Ativo' : 'Inativo' }}
                            <span>{{ $brand->status == 1 ? '▴' : '▾' }}</span>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="footer-actions">
        <button type="submit" class="btn-save-all">
            <i class="fa-solid fa-cloud-arrow-up"></i> SALVAR TODAS AS ALTERAÇÕES
        </button>
    </div>
</form>

<script>
/**
 * Alterna a interface visual e o valor real do input hidden
 * @param {HTMLElement} btn - O botão clicado
 */
function toggleUI(btn) {
    const input = btn.previousElementSibling; // O input hidden está logo antes do botão
    const isCurrentlyActive = input.value == "1"; 
    
    if (isCurrentlyActive) {
        // Se estava ativo, desativa
        input.value = "2";
        btn.className = "status-badge status-inactive";
        btn.innerHTML = 'Inativo <span>▾</span>';
    } else {
        // Se estava inativo, ativa
        input.value = "1";
        btn.className = "status-badge status-active";
        btn.innerHTML = 'Ativo <span>▴</span>';
    }
}
</script>
@endsection