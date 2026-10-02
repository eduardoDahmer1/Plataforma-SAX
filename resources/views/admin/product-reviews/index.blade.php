@extends('layout.admin')

@section('content')
@php
    $activeFilterCount = collect(['search', 'product_id', 'rating', 'status', 'verified', 'comment', 'date_from', 'date_to', 'sort'])
        ->filter(fn ($name) => request()->filled($name))->count();
@endphp
<div class="admin-reviews-page">
    <x-admin.card>
        <x-admin.page-header
            title="Avaliações de produtos"
            description="Visão completa das notas, comentários, produtos avaliados e moderação da loja.">
            <x-slot:actions>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-dark btn-sax-lg px-4 text-uppercase fw-bold letter-spacing-1">
                    <i class="fa-solid fa-arrow-left me-2"></i> Voltar aos produtos
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <div class="admin-review-stats">
            <article><span>Total</span><strong>{{ number_format($stats['total'], 0, ',', '.') }}</strong><small>Avaliações recebidas</small></article>
            <article><span>Produtos avaliados</span><strong>{{ number_format($stats['products'], 0, ',', '.') }}</strong><small>Itens com opinião</small></article>
            <article><span>Nota média</span><strong>{{ $stats['average'] ? number_format($stats['average'], 1, ',', '.') : '—' }}</strong><small><x-rating-stars :rating="$stats['average']" compact /></small></article>
            <article><span>Publicadas</span><strong>{{ number_format($stats['approved'], 0, ',', '.') }}</strong><small>Visíveis na loja</small></article>
            <article><span>Com comentário</span><strong>{{ number_format($stats['comments'], 0, ',', '.') }}</strong><small>Feedbacks detalhados</small></article>
            <article><span>Compra verificada</span><strong>{{ number_format($stats['verified'], 0, ',', '.') }}</strong><small>Pedidos pagos</small></article>
            <article><span>Ocultas</span><strong>{{ number_format($stats['hidden'], 0, ',', '.') }}</strong><small>Fora da loja</small></article>
            <article><span>Últimos 30 dias</span><strong>{{ number_format($stats['recent'], 0, ',', '.') }}</strong><small>Novas avaliações</small></article>
        </div>

        <div class="admin-review-overview">
            <section class="admin-review-panel admin-review-distribution">
                <header><div><span>Qualidade percebida</span><h3>Distribuição das notas</h3></div><strong>{{ number_format($stats['approved'], 0, ',', '.') }}</strong></header>
                <div class="admin-review-bars">
                    @foreach($ratingDistribution as $row)
                        <a href="{{ route('admin.products.ratings.index', array_merge(request()->except(['page', 'rating']), ['rating' => $row->rating])) }}">
                            <span>{{ $row->rating }} <i class="fa-solid fa-star"></i></span>
                            <div><span style="width: {{ $row->percentage }}%"></span></div>
                            <strong>{{ $row->total }}</strong>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="admin-review-panel">
                <header><div><span>Engajamento</span><h3>Produtos mais avaliados</h3></div><i class="fa-regular fa-comments"></i></header>
                <div class="admin-review-product-ranking">
                    @forelse($mostReviewedProducts as $summary)
                        <a href="{{ route('admin.products.ratings.index', ['product_id' => $summary->product_id]) }}">
                            <img src="{{ $summary->product?->photo_url ?? asset('storage/uploads/noimage.webp') }}" alt="">
                            <span><strong>{{ $summary->product?->external_name ?: ($summary->product?->name ?? 'Produto removido') }}</strong><small>{{ $summary->reviews_count }} avaliações · média {{ number_format((float) $summary->reviews_average, 1, ',', '.') }}</small></span>
                            <b>{{ number_format((float) $summary->reviews_average, 1, ',', '.') }}</b>
                        </a>
                    @empty
                        <p class="admin-review-panel-empty">Ainda não há produtos avaliados.</p>
                    @endforelse
                </div>
            </section>

            <section class="admin-review-panel">
                <header><div><span>Acompanhamento</span><h3>Produtos que pedem atenção</h3></div><i class="fa-solid fa-arrow-trend-down"></i></header>
                <div class="admin-review-product-ranking is-attention">
                    @forelse($attentionProducts as $summary)
                        <a href="{{ route('admin.products.ratings.index', ['product_id' => $summary->product_id, 'sort' => 'rating_low']) }}">
                            <img src="{{ $summary->product?->photo_url ?? asset('storage/uploads/noimage.webp') }}" alt="">
                            <span><strong>{{ $summary->product?->external_name ?: ($summary->product?->name ?? 'Produto removido') }}</strong><small>{{ $summary->reviews_count }} avaliações · {{ $summary->verified_count }} verificadas</small></span>
                            <b>{{ number_format((float) $summary->reviews_average, 1, ',', '.') }}</b>
                        </a>
                    @empty
                        <p class="admin-review-panel-empty">Nenhum produto com média igual ou inferior a 3,5.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="admin-review-quick-filters" aria-label="Filtros rápidos">
            <span>Atalhos</span>
            <a href="{{ route('admin.products.ratings.index', ['status' => 'hidden']) }}">Ocultas <b>{{ $stats['hidden'] }}</b></a>
            <a href="{{ route('admin.products.ratings.index', ['verified' => 1]) }}">Compras verificadas <b>{{ $stats['verified'] }}</b></a>
            <a href="{{ route('admin.products.ratings.index', ['comment' => 'with']) }}">Com comentário <b>{{ $stats['comments'] }}</b></a>
            <a href="{{ route('admin.products.ratings.index', ['rating' => 1, 'sort' => 'rating_low']) }}">Nota 1</a>
        </div>

        <form method="GET" action="{{ route('admin.products.ratings.index') }}" class="admin-review-filters">
            <label class="admin-review-search">
                <span>Buscar</span>
                <div><i class="fa-solid fa-magnifying-glass"></i><input type="search" name="search" value="{{ request('search') }}" placeholder="Produto, SKU, cliente ou comentário"></div>
            </label>
            <label class="admin-review-product-filter"><span>Produto avaliado</span><select name="product_id"><option value="">Todos os produtos</option>@foreach($reviewedProducts as $item)<option value="{{ $item->id }}" @selected((string) request('product_id') === (string) $item->id)>{{ $item->external_name ?: $item->name }} · {{ $item->sku }}</option>@endforeach</select></label>
            <label><span>Nota</span><select name="rating"><option value="">Todas</option>@for($rating = 5; $rating >= 1; $rating--)<option value="{{ $rating }}" @selected((string) request('rating') === (string) $rating)>{{ $rating }} estrelas</option>@endfor</select></label>
            <label><span>Status</span><select name="status"><option value="">Todos</option><option value="approved" @selected(request('status') === 'approved')>Publicadas</option><option value="hidden" @selected(request('status') === 'hidden')>Ocultas</option></select></label>
            <label><span>Compra</span><select name="verified"><option value="">Todas</option><option value="1" @selected(request('verified') === '1')>Verificada</option><option value="0" @selected(request('verified') === '0')>Não verificada</option></select></label>
            <label><span>Comentário</span><select name="comment"><option value="">Todos</option><option value="with" @selected(request('comment') === 'with')>Com comentário</option><option value="without" @selected(request('comment') === 'without')>Sem comentário</option></select></label>
            <label><span>De</span><input type="date" name="date_from" value="{{ request('date_from') }}"></label>
            <label><span>Até</span><input type="date" name="date_to" value="{{ request('date_to') }}"></label>
            <label><span>Ordenar</span><select name="sort"><option value="latest">Mais recentes</option><option value="oldest" @selected(request('sort') === 'oldest')>Mais antigas</option><option value="rating_high" @selected(request('sort') === 'rating_high')>Maior nota</option><option value="rating_low" @selected(request('sort') === 'rating_low')>Menor nota</option></select></label>
            <div class="admin-review-filter-actions">
                <button type="submit"><i class="fa-solid fa-filter"></i> Filtrar @if($activeFilterCount)<b>{{ $activeFilterCount }}</b>@endif</button>
                <a href="{{ route('admin.products.ratings.index') }}">Limpar</a>
            </div>
        </form>

        <div class="admin-review-results-heading">
            <div><span>Resultados</span><strong>{{ number_format($reviews->total(), 0, ',', '.') }} avaliações encontradas</strong></div>
            <small>Página {{ $reviews->currentPage() }} de {{ $reviews->lastPage() }}</small>
        </div>

        <div class="admin-review-table-wrap">
            <table class="admin-review-table">
                <thead><tr><th>Produto</th><th>Cliente</th><th>Avaliação</th><th>Status</th><th>Ações</th></tr></thead>
                <tbody>
                    @forelse($reviews as $review)
                        <tr>
                            <td data-label="Produto">
                                <a href="{{ route('produto.show', $review->product?->slug ?: $review->product_id) }}" target="_blank" rel="noopener" class="admin-review-product">
                                    <img src="{{ $review->product?->photo_url ?? asset('storage/uploads/noimage.webp') }}" alt="">
                                    <span><strong>{{ $review->product?->external_name ?: ($review->product?->name ?? 'Produto removido') }}</strong><small>SKU {{ $review->product?->sku ?? '—' }}</small></span>
                                </a>
                            </td>
                            <td data-label="Cliente">
                                <div class="admin-review-customer"><strong>{{ $review->author_name ?: 'Cliente SAX' }}</strong><small>{{ $review->user?->email ?? 'Conta removida' }}</small>@if($review->verified_purchase)<span><i class="fa-solid fa-circle-check"></i> Compra verificada</span>@endif</div>
                            </td>
                            <td data-label="Avaliação">
                                <div class="admin-review-content">
                                    <div><x-rating-stars :rating="$review->rating" compact /><strong>{{ $review->rating }},0</strong></div>
                                    @if($review->title)<h3>{{ $review->title }}</h3>@endif
                                    @if($review->comment)<p>{{ $review->comment }}</p>@else<small>Sem comentário.</small>@endif
                                    <time>{{ $review->created_at->format('d/m/Y H:i') }}</time>
                                </div>
                            </td>
                            <td data-label="Status">
                                <span class="admin-review-status is-{{ $review->status }}">{{ $review->status === 'approved' ? 'Publicada' : 'Oculta' }}</span>
                                @if($review->moderated_at)<small class="admin-review-moderated">Moderada por {{ $review->moderator?->name ?? 'administrador' }} em {{ $review->moderated_at->format('d/m/Y H:i') }}</small>@endif
                            </td>
                            <td data-label="Ações">
                                <div class="admin-review-actions">
                                    <form method="POST" action="{{ route('admin.products.ratings.update', $review) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $review->status === 'approved' ? 'hidden' : 'approved' }}">
                                        <button type="submit" class="{{ $review->status === 'approved' ? 'is-hide' : 'is-publish' }}"><i class="fa-solid {{ $review->status === 'approved' ? 'fa-eye-slash' : 'fa-eye' }}"></i>{{ $review->status === 'approved' ? 'Ocultar' : 'Publicar' }}</button>
                                    </form>
                                    <details>
                                        <summary><i class="fa-regular fa-note-sticky"></i> Nota interna</summary>
                                        <form method="POST" action="{{ route('admin.products.ratings.update', $review) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $review->status }}">
                                            <textarea name="admin_note" rows="3" maxlength="1000" placeholder="Observação apenas para a equipe">{{ $review->admin_note }}</textarea>
                                            <button type="submit">Salvar nota</button>
                                        </form>
                                    </details>
                                    <form method="POST" action="{{ route('admin.products.ratings.destroy', $review) }}" onsubmit="return confirm('Excluir esta avaliação?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="is-delete"><i class="fa-regular fa-trash-can"></i> Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="admin-review-empty"><i class="fa-regular fa-star"></i><strong>Nenhuma avaliação encontrada</strong><span>Ajuste os filtros ou aguarde novas avaliações dos clientes.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reviews->hasPages())<div class="admin-review-pagination">{{ $reviews->onEachSide(1)->links() }}</div>@endif
    </x-admin.card>
</div>
@endsection
