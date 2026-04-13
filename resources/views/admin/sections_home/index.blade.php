@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.estrutura_inicial_titulo') }}"
        description="{{ __('messages.estrutura_inicial_desc') }}">
        <x-slot:actions>
            <button type="submit" form="sectionsForm" class="btn btn-dark btn-sm rounded-0 px-4 text-uppercase fw-bold x-small tracking-wider">
                {{ __('messages.guardar_configuracao_btn') }}
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="row">
        <div class="col-lg-6">
            <form action="{{ route('admin.sections_home.update') }}" method="POST" id="sectionsForm">
                @csrf
                @method('PATCH')

                @php
                    $sections = [
                        'lancamentos' => ['label' => __('messages.novos_lancamentos_label'), 'icon' => 'fa-calendar-plus'],
                        'destaque' => ['label' => __('messages.produtos_destacados_label'), 'icon' => 'fa-star'],
                    ];
                @endphp

                <div class="sax-settings-box border">
                    @foreach($sections as $key => $data)
                    <div class="sax-setting-item d-flex align-items-center justify-content-between p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex align-items-center">
                            <div class="sax-icon-wrapper me-3">
                                <i class="fas {{ $data['icon'] }} text-muted small"></i>
                            </div>
                            <div>
                                <label class="d-block fw-bold text-dark text-uppercase x-small tracking-tighter mb-0 cursor-pointer" for="{{ $key }}">
                                    {{ $data['label'] }}
                                </label>
                                <span class="x-small text-muted italic">{{ __('messages.secao_label_hint') }} {{ $key }}</span>
                            </div>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input sax-switch" type="checkbox" name="{{ $key }}" id="{{ $key }}"
                                {{ $settings->{'show_highlight_'.$key} ? 'checked' : '' }}>
                        </div>
                    </div>
                    @endforeach
                </div>
            </form>
        </div>

        {{-- Coluna de Ajuda --}}
        <div class="col-lg-4 offset-lg-1 mt-5 mt-lg-0">
            <div class="border-start ps-4 h-100">
                <h6 class="x-small fw-bold text-uppercase tracking-wider mb-3">{{ __('messages.guia_visualizacao_titulo') }}</h6>
                <p class="x-small text-secondary lh-base italic mb-4">
                    {{ __('messages.guia_visualizacao_texto') }}
                </p>
                <div class="p-3 bg-light border-dashed">
                    <span class="x-small fw-bold text-dark d-block mb-1 text-uppercase">{{ __('messages.nota_estilo_label') }}</span>
                    <span class="x-small text-muted italic">{{ __('messages.nota_estilo_texto') }}</span>
                </div>
            </div>
        </div>
    </div>
</x-admin.card>
@endsection