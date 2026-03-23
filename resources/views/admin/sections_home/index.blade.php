@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-md-5">
    
    {{-- Header Minimalista --}}
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h1 class="h4 fw-light text-uppercase tracking-wider mb-1">Estrutura inicial</h1>
            <p class="small text-secondary mb-0">Gestione a visibilidade das seções principais da sua loja</p>
        </div>
        <button type="submit" form="sectionsForm" class="btn btn-dark btn-sm rounded-0 px-4 text-uppercase fw-bold x-small tracking-wider">
            Guardar Configuração
        </button>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <form action="{{ route('admin.sections_home.update') }}" method="POST" id="sectionsForm">
                @csrf
                @method('PATCH')

                @php
                    // Ajustado para exibir APENAS as seções que você está usando agora
                    $sections = [
                        'lancamentos' => ['label' => 'Novos Lançamentos', 'icon' => 'fa-calendar-plus'],
                        'destaque' => ['label' => 'Produtos Destacados', 'icon' => 'fa-star'],
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
                                <span class="x-small text-muted italic">Seção: {{ $key }}</span>
                            </div>
                        </div>
                        
                        <div class="form-check form-switch">
                            {{-- O name deve ser o mesmo da chave do array --}}
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
                <h6 class="x-small fw-bold text-uppercase tracking-wider mb-3">Guía de Visualização</h6>
                <p class="x-small text-secondary lh-base italic mb-4">
                    Estas são as duas seções principais ativas em sua nova interface de luxo. 
                    Certifique-se de que os produtos tenham marcada a opção correspondente em seu painel individual para que apareçam na Home.
                </p>
                <div class="p-3 bg-light border-dashed">
                    <span class="x-small fw-bold text-dark d-block mb-1 text-uppercase">Nota de Estilo:</span>
                    <span class="x-small text-muted italic">A Home agora segue um design editorial mais limpo, enfocándose exclusivamente no que é tendência e em produtos estrela.</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection