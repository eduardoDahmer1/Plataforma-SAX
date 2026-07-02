<form action="{{ route('search') }}" method="GET" class="sax-search-container sax-search-container--desktop position-relative" role="search">
    <div class="input-group">
        <span class="input-group-text bg-transparent border-0 sax-search-icon-wrap">
            <i class="fa fa-search"></i>
        </span>
        <input type="text" name="search" class="form-control sax-search-input search-autocomplete-input"
            placeholder="{{ __('messages.pesquisar') }}" value="{{ request('search') }}" autocomplete="off">
    </div>
    <div class="autocomplete-results d-none"></div>
</form>