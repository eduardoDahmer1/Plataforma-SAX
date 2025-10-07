@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h1>Seções da Home</h1>
    <form action="{{ route('admin.sections_home.update') }}" method="POST">
        @csrf
        @method('PATCH')

        @php
            $sections = [
                'destaque' => 'Destaque',
                'mais_vendidos' => 'Mais Vendidos',
                'melhores_avaliacoes' => 'Melhores Avaliações',
                'super_desconto' => 'Super Desconto',
                'famosos' => 'Famosos',
                'lancamentos' => 'Lançamentos',
                'tendencias' => 'Tendências',
                'promocoes' => 'Promoções',
                'ofertas_relampago' => 'Ofertas Relâmpago',
                'navbar' => 'Navbar',
            ];
        @endphp

        @foreach($sections as $key => $label)
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="{{ $key }}" id="{{ $key }}"
                {{ $settings->{'show_highlight_'.$key} ? 'checked' : '' }}>
            <label class="form-check-label" for="{{ $key }}">
                {{ $label }}
            </label>
        </div>
        @endforeach

        <button type="submit" class="btn btn-primary mt-3">Salvar alterações</button>
    </form>
</div>
@endsection
