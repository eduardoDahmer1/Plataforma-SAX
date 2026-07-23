@extends('layout.admin')

@section('content')
<x-admin.card>
    <div class="sax-activate" id="activate-app"
         data-toggle-url="{{ route('admin.activate.toggle', ['type' => '__TYPE__', 'id' => '__ID__']) }}"
         data-error-message="{{ __('messages.activate_erro') }}">

        <div class="sax-activate__bar">
            <div class="sax-activate__search">
                <i class="fa fa-search"></i>
                <input type="text" id="activate-search" autocomplete="off"
                       placeholder="{{ __('messages.activate_buscar_placeholder') }}">
            </div>

            <div class="sax-activate__filters" role="group">
                <button type="button" class="sax-chip is-on" data-filter="all">{{ __('messages.cupon_situacao_todas') }}</button>
                <button type="button" class="sax-chip" data-filter="1">{{ __('messages.ativo') }}</button>
                <button type="button" class="sax-chip" data-filter="2">{{ __('messages.inativo') }}</button>
            </div>

            <span class="sax-activate__hint" id="activate-feedback" role="status"></span>
        </div>

        @foreach ([
            ['tipo' => 'category', 'titulo' => __('messages.categorias'), 'icone' => 'fa-layer-group', 'itens' => $categories],
            ['tipo' => 'brand', 'titulo' => __('messages.marcas'), 'icone' => 'fa-tag', 'itens' => $brands],
        ] as $grupo)
            @php $ativos = $grupo['itens']->where('status', 1)->count(); @endphp

            <section class="sax-activate__section" data-section="{{ $grupo['tipo'] }}">
                <header class="sax-activate__head">
                    <h2><i class="fa-solid {{ $grupo['icone'] }}"></i> {{ $grupo['titulo'] }}</h2>
                    <span class="sax-activate__count">
                        <strong data-count-active>{{ $ativos }}</strong> / {{ $grupo['itens']->count() }} {{ __('messages.ativo') }}
                    </span>
                </header>

                <div class="sax-activate__grid">
                    @foreach ($grupo['itens'] as $item)
                        @php
                            $rotulo = trim((string) $item->name) !== ''
                                ? $item->name
                                : ($item->slug ?: '#' . $item->id);
                        @endphp

                        <div class="ai" data-s="{{ $item->status }}">
                            <span class="ai-n">{{ $rotulo }}</span>
                            <button type="button" class="sax-toggle{{ $item->status == 1 ? ' is-on' : '' }}" data-id="{{ $item->id }}"><i></i></button>
                        </div>
                    @endforeach
                </div>

                <p class="sax-activate__empty" data-empty hidden>{{ __('messages.activate_sem_resultado') }}</p>
            </section>
        @endforeach
    </div>
</x-admin.card>
@endsection
