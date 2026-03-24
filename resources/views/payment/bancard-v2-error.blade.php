@extends('layout.layout')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-4 p-lg-5">
                    <h2 class="h4 fw-bold mb-2">Pagamento Bancard V2</h2>
                    <p class="text-muted mb-4">Nao foi possivel concluir o pagamento.</p>

                    @if(session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <tbody>
                                <tr>
                                    <th scope="row" class="text-muted" style="width: 40%;">Data e Hora da transacao</th>
                                    <td>{{ $transactionDateTime }}</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="text-muted">Numero do pedido</th>
                                    <td>{{ $shopProcessId }}</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="text-muted">Importe</th>
                                    <td>{{ $amount }} PYG</td>
                                </tr>
                                <tr>
                                    <th scope="row" class="text-muted">Descricao da resposta</th>
                                    <td>{{ $responseDescription }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a href="{{ route('user.orders') }}" class="btn btn-dark">Ver meus pedidos</a>
                        <a href="{{ route('checkout.index') }}" class="btn btn-outline-secondary">Tentar novamente</a>
                    </div>

                    <small class="d-block text-muted mt-3">
                        Esta tela nao exibe codigo de autorizacao, codigo de resposta ou dados sensiveis de seguranca.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
