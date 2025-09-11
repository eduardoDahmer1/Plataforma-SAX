@props(['request'])

<form id="sortForm" method="GET" class="d-flex justify-content-between align-items-center mb-3">
    {{-- Mantém todos os filtros do sidebar --}}
    <input type="hidden" name="search" value="{{ $request->search }}">
    <input type="hidden" name="brand" value="{{ $request->brand }}">
    <input type="hidden" name="category" value="{{ $request->category }}">
    <input type="hidden" name="subcategory" value="{{ $request->subcategory }}">
    <input type="hidden" name="childcategory" value="{{ $request->childcategory }}">
    <input type="hidden" name="min_price" value="{{ $request->min_price }}">
    <input type="hidden" name="max_price" value="{{ $request->max_price }}">

    <div>
        <label for="sort_by" class="me-2 fw-bold">Ordenar por:</label>
        <select name="sort_by" id="sort_by" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
            <option value="">Padrão</option>
            <option value="latest" @selected($request->sort_by == 'latest')>Último produto</option>
            <option value="oldest" @selected($request->sort_by == 'oldest')>Produto mais antigo</option>
            <option value="name_az" @selected($request->sort_by == 'name_az')>Nome (A-Z)</option>
            <option value="name_za" @selected($request->sort_by == 'name_za')>Nome (Z-A)</option>
            <option value="price_low" @selected($request->sort_by == 'price_low')>Menor preço</option>
            <option value="price_high" @selected($request->sort_by == 'price_high')>Maior preço</option>
            <option value="in_stock" @selected($request->sort_by == 'in_stock')>Disponibilidade</option>
        </select>

        <label for="per_page" class="ms-3 me-2 fw-bold">Mostrar:</label>
        <select name="per_page" id="per_page" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
            <option value="25" @selected($request->per_page == 25)>25</option>
            <option value="35" @selected($request->per_page == 35)>35</option>
            <option value="45" @selected($request->per_page == 45)>45</option>
            <option value="55" @selected($request->per_page == 55)>55</option>
        </select>
    </div>
</form>
