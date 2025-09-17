@extends('layout.dashboard')

@section('content')
    <div class="container py-4">
        <h3>Meus Cupons</h3>
        <div class="list-group mt-3">
            @forelse($cupons as $userCupon)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $userCupon->cupon->codigo }}</strong> -
                        @if ($userCupon->cupon->tipo === 'percentual')
                            {{ $userCupon->cupon->montante }}% de desconto
                        @else
                            {{ currency($userCupon->cupon->montante) }} de desconto
                        @endif
                        <br>
                        Válido até {{ date('d/m/Y', strtotime($userCupon->cupon->data_final)) }}
                    </div>
                    <form action="{{ route('user.applyCupon') }}" method="POST">
                        @csrf
                        <input type="hidden" name="codigo" value="{{ $userCupon->cupon->codigo }}">
                        <button type="submit" class="btn btn-sm btn-primary">Aplicar</button>
                    </form>
                </div>
            @empty
                <div class="list-group-item text-muted">Nenhum cupom disponível</div>
            @endforelse
        </div>
    </div>
@endsection
