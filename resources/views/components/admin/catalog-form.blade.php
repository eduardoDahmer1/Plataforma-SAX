@props([
    'title',
    'subtitle' => null,
    'action',
    'method' => 'POST',   // POST (criar) ou PUT (editar)
    'backUrl',
    'submitLabel' => null,
])

{{-- Casca comum dos formulários do catálogo: cabeçalho, erros, campos (slot) e ações --}}
<div class="sax-catf">
    <div class="sax-catf__head">
        <div>
            <h1 class="sax-cat__title">{{ $title }}</h1>
            @if ($subtitle)
                <span class="sax-cat__sub">{{ $subtitle }}</span>
            @endif
        </div>

        <a href="{{ $backUrl }}" class="sax-catf__back">
            <i class="fa fa-chevron-left"></i> {{ __('messages.voltar') }}
        </a>
    </div>

    @if ($errors->any())
        <div class="sax-catf__errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
        @csrf
        @if (strtoupper($method) !== 'POST')
            @method($method)
        @endif

        {{ $slot }}

        <div class="sax-catf__actions">
            <button type="submit" class="sax-catf__save">
                {{ $submitLabel ?? __('messages.salvar') }}
            </button>
            <a href="{{ $backUrl }}" class="sax-catf__cancel">{{ __('messages.cancelar_btn') }}</a>
        </div>
    </form>
</div>
