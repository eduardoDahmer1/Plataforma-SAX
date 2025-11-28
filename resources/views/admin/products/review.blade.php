@extends('layout.admin')

@section('content')
<div class="container-fluid py-4">

    <h2 class="fw-bold mb-4">📊 Produtos Editados por Dia</h2>

    <div class="row g-3">
        @foreach ($edicoesPorDia as $linha)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <div class="fw-bold text-primary fs-6">
                            {{ \Carbon\Carbon::parse($linha->dia)->format('d/m/Y') }}
                        </div>
                        <div class="fs-2 fw-bold text-dark">
                            {{ $linha->total }}
                        </div>
                        <small class="text-muted">produtos editados</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
