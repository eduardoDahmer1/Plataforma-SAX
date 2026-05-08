<div id="mobileSearchOverlay" class="mobile-search-overlay">
    <div class="p-4 text-end">
        <button class="btn-close-search" id="closeSearch">&times;</button>
    </div>
    <div class="container">
        <form action="{{ route('search') }}" method="GET" class="position-relative">
            <div class="sax-search-container bg-white border">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0"><i class="fa fa-search"></i></span>
                    <input type="text" name="search" id="mobileSearchInput" class="form-control sax-search-input search-autocomplete-input"
                        placeholder="{{ __('messages.pesquisar') }}" autocomplete="off">
                </div>
            </div>
            <div class="autocomplete-results d-none"></div>
        </form>
    </div>
</div>