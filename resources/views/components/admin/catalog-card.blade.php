@props([
    'id',
    'title',
    'image' => null,     // caminho da foto/logo já resolvido para URL
    'parent' => null,    // categoria/subcategoria a que pertence
    'meta' => null,      // texto auxiliar (slug, etc.)
    'publicUrl' => null, // página na loja
    'editUrl' => null,
    'deleteUrl' => null,
    'deleteConfirm' => null,
])

<div class="sax-cat-card">
    <div class="sax-cat-card__media">
        @if ($image)
            <img src="{{ $image }}" alt="{{ $title }}" loading="lazy">
        @else
            <i class="fa fa-image"></i>
        @endif
    </div>

    <div class="sax-cat-card__body">
        <span class="sax-cat-card__name" title="{{ $title }}">{{ $title }}</span>
        <span class="sax-cat-card__meta">#{{ $id }}{{ $meta ? ' · ' . $meta : '' }}</span>

        @if ($parent)
            <span class="sax-cat-card__parent" title="{{ $parent }}">{{ $parent }}</span>
        @endif
    </div>

    <div class="sax-cat-card__actions">
        @if ($publicUrl)
            <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="sax-cat-act" title="{{ __('messages.ver_na_loja') }}">
                <i class="fa fa-external-link-alt"></i>
            </a>
        @endif

        @if ($editUrl)
            <a href="{{ $editUrl }}" class="sax-cat-act">
                <i class="fa fa-pen"></i> {{ __('messages.editar') }}
            </a>
        @endif

        @if ($deleteUrl)
            <form method="POST" action="{{ $deleteUrl }}"
                  onsubmit="return confirm('{{ $deleteConfirm ?? __('messages.confirmar_exclusao') }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="sax-cat-act sax-cat-act--danger" title="{{ __('messages.eliminar') }}">
                    <i class="fa fa-trash"></i>
                </button>
            </form>
        @endif
    </div>
</div>
