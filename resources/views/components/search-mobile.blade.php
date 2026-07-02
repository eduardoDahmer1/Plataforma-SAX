<div id="mobileSearchOverlay" class="mobile-search-overlay">
    <div class="mobile-search-dialog container">
        <div class="mobile-search-header d-flex align-items-center justify-content-between mb-3">
            <div>
                <h6 class="mobile-search-title mb-1">Pesquisar</h6>
                <p class="mobile-search-subtitle mb-0">Encontre produtos, marcas e categorias</p>
            </div>
            <button class="btn-close-search" id="closeSearch" type="button" aria-label="Fechar busca">&times;</button>
        </div>

        <form action="{{ route('search') }}" method="GET" class="position-relative mobile-search-form" role="search">
            <div class="sax-search-container bg-white border-0">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0 sax-search-icon-wrap"><i class="fa fa-search"></i></span>
                    <input type="text" name="search" id="mobileSearchInput" class="form-control sax-search-input search-autocomplete-input"
                        placeholder="{{ __('messages.pesquisar') }}" autocomplete="off" autofocus>
                </div>
            </div>
            <div class="autocomplete-results d-none"></div>
        </form>
    </div>
</div>