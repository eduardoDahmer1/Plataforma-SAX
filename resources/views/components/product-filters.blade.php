@props(['categories', 'brands', 'currentCategory' => null, 'currentSub' => null, 'currentChild' => null])

@php
    $curCatId   = is_object($currentCategory) ? $currentCategory->id : $currentCategory;
    $curSubId   = is_object($currentSub) ? $currentSub->id : $currentSub;
    $curChildId = is_object($currentChild) ? $currentChild->id : $currentChild;
@endphp

<div class="product-filters-wrapper">
    <h6 class="text-uppercase fw-bold mb-4 small" style="letter-spacing: 2px;">Filtrar Por</h6>

    {{-- Hierarquia de Categorias --}}
    <div class="filter-group mb-4">
        <label class="text-uppercase extra-small fw-bold text-muted mb-2 d-block" style="letter-spacing: 1px;">
            Categorias
        </label>
        
        <div class="accordion accordion-flush" id="accordionFilters">
            @foreach($categories as $cat)
                <div class="accordion-item border-0 bg-transparent">
                    <div class="d-flex align-items-center justify-content-between">
                        <a href="{{ route('categories.show', $cat->slug ?? $cat->id) }}" 
                           class="filter-link flex-grow-1 {{ $curCatId == $cat->id ? 'active fw-bold' : '' }}">
                            {{ $cat->name }}
                        </a>
                        
                        @if($cat->subcategories->count() > 0)
                            <button class="btn btn-link btn-sm p-0 text-muted accordion-trigger {{ ($curCatId == $cat->id || (isset($currentSub->category_id) && $currentSub->category_id == $cat->id)) ? '' : 'collapsed' }}" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapse-cat-{{ $cat->id }}">
                                <i class="fas fa-chevron-down small-icon"></i>
                            </button>
                        @endif
                    </div>

                    @if($cat->subcategories->count() > 0)
                        <div id="collapse-cat-{{ $cat->id }}" 
                             class="collapse {{ ($curCatId == $cat->id || (isset($currentSub->category_id) && $currentSub->category_id == $cat->id)) ? 'show' : '' }}" 
                             data-bs-parent="#accordionFilters">
                            
                            <ul class="list-unstyled ms-2 mt-1 border-start ps-2">
                                @foreach($cat->subcategories as $sub)
                                    <li class="mb-1">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <a href="{{ route('subcategories.show', $sub->slug ?? $sub->id) }}" 
                                               class="filter-link extra-small {{ $curSubId == $sub->id ? 'active fw-bold text-dark' : '' }}">
                                                — {{ $sub->name }}
                                            </a>
                                            
                                            @if($sub->categoriasfilhas->count() > 0)
                                                <button class="btn btn-link btn-sm p-0 text-muted accordion-trigger {{ ($curSubId == $sub->id || (isset($currentChild->subcategory_id) && $currentChild->subcategory_id == $sub->id)) ? '' : 'collapsed' }}" 
                                                        type="button" 
                                                        data-bs-toggle="collapse" 
                                                        data-bs-target="#collapse-sub-{{ $sub->id }}">
                                                    <i class="fas fa-chevron-down extra-small-icon"></i>
                                                </button>
                                            @endif
                                        </div>

                                        @if($sub->categoriasfilhas->count() > 0)
                                            <div id="collapse-sub-{{ $sub->id }}" 
                                                 class="collapse {{ ($curSubId == $sub->id || (isset($currentChild->subcategory_id) && $currentChild->subcategory_id == $sub->id)) ? 'show' : '' }}">
                                                <ul class="list-unstyled ms-3 mt-1">
                                                    @foreach($sub->categoriasfilhas as $filha)
                                                        <li>
                                                            <a href="{{ route('categorias-filhas.show', $filha->slug ?? $filha->id) }}" 
                                                               class="filter-link extra-small opacity-75 {{ $curChildId == $filha->id ? 'active fw-bold text-dark opacity-100' : '' }}" 
                                                               style="font-size: 0.6rem;">
                                                                • {{ $filha->name }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Marcas com Busca em Tempo Real --}}
    @if(isset($brands) && $brands->count() > 0)
    <div class="filter-group mb-4 border-top pt-3">
        <label class="text-uppercase extra-small fw-bold text-muted mb-2 d-block" style="letter-spacing: 1px;">
            Marcas
        </label>
        
        {{-- Campo de busca de Marcas --}}
        <div class="mb-3 position-relative">
            <input type="text" id="brandSearchInput" 
                   class="form-control form-control-sm border-0 bg-light extra-small py-2 ps-3" 
                   placeholder="Pesquisar marca..."
                   onkeyup="filterBrands()">
            <i class="fas fa-search position-absolute text-muted" style="right: 10px; top: 8px; font-size: 0.7rem;"></i>
        </div>

        <div class="brand-filter-scroll" style="max-height: 250px; overflow-y: auto;">
            <ul class="list-unstyled mb-0" id="brandList">
                @foreach($brands as $brand)
                    <li class="mb-1 brand-item">
                        <a href="{{ route('brands.show', $brand->slug) }}" 
                           class="filter-link extra-small d-block py-1 brand-name-text">
                            {{ $brand->name }}
                        </a>
                    </li>
                @endforeach
                {{-- Mensagem caso não encontre nada --}}
                <li id="noBrandsFound" class="extra-small text-muted py-2 d-none">
                    Nenhuma marca encontrada.
                </li>
            </ul>
        </div>
    </div>
    @endif
</div>

<script>
    /**
     * Filtra as marcas na lista de acordo com o que o usuário digita.
     */
    function filterBrands() {
        const input = document.getElementById('brandSearchInput');
        const filter = input.value.toUpperCase();
        const ul = document.getElementById('brandList');
        const items = ul.getElementsByClassName('brand-item');
        const noResults = document.getElementById('noBrandsFound');
        let hasResults = false;

        for (let i = 0; i < items.length; i++) {
            const link = items[i].getElementsByClassName('brand-name-text')[0];
            const txtValue = link.textContent || link.innerText;
            
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                items[i].classList.remove('d-none');
                hasResults = true;
            } else {
                items[i].classList.add('d-none');
            }
        }

        // Exibe mensagem de erro se nada for filtrado
        if (hasResults) {
            noResults.classList.add('d-none');
        } else {
            noResults.classList.remove('d-none');
        }
    }
</script>

<style>
    .filter-link { font-size: 0.72rem; text-transform: uppercase; color: #777; text-decoration: none; transition: all 0.2s ease; letter-spacing: 0.5px; }
    .filter-link:hover, .filter-link.active { color: #000; transform: translateX(2px); }
    .extra-small { font-size: 0.65rem; }
    
    /* Input Search Styles */
    #brandSearchInput:focus {
        background-color: #efefef !important;
        box-shadow: none;
        outline: none;
    }

    .border-start { border-left: 1px solid #eee !important; }
    .accordion-trigger i { transition: transform 0.3s ease; font-size: 0.7rem; }
    .accordion-trigger.collapsed i { transform: rotate(-90deg); }
    .small-icon { font-size: 0.7rem; }
    .extra-small-icon { font-size: 0.55rem; }

    .brand-filter-scroll::-webkit-scrollbar { width: 2px; }
    .brand-filter-scroll::-webkit-scrollbar-thumb { background: #ddd; }
</style>