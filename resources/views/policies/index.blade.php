@extends('layout.layout')

@section('content')
<section class="py-5 bg-light">
    <div class="container py-lg-4">
        <div class="text-center mb-5">
            <span class="text-uppercase text-muted small fw-bold tracking-wider">SAX Department</span>
            <h1 class="display-6 fw-bold mt-2">Políticas e Termos</h1>
            <p class="text-muted mb-0">Consulte as condições aplicáveis ao uso do site e às suas compras.</p>
        </div>

        @if($policies->isEmpty())
            <div class="alert alert-light border text-center">Nenhuma política está disponível no momento.</div>
        @else
            <div class="row g-4">
                <aside class="col-lg-3">
                    <nav class="list-group position-sticky" style="top: 110px" aria-label="Políticas">
                        @foreach($policies as $policy)
                            <a class="list-group-item list-group-item-action py-3" href="#{{ $policy->slug }}">{{ $policy->title }}</a>
                        @endforeach
                    </nav>
                </aside>
                <div class="col-lg-9">
                    @foreach($policies as $policy)
                        <article id="{{ $policy->slug }}" class="bg-white border rounded-3 p-4 p-lg-5 mb-4 shadow-sm policy-content">
                            <h2 class="h3 fw-bold border-bottom pb-3 mb-4">{{ $policy->title }}</h2>
                            {!! $policy->content !!}
                            <p class="small text-muted border-top pt-3 mt-4 mb-0">Última atualização: {{ $policy->updated_at->format('d/m/Y') }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
