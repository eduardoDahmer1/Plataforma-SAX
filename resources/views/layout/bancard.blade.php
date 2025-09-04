@extends('layout.checkout') {{-- ou seu layout principal --}}

@section('content')
<div class="container mt-5 text-center">
    <h3>Redirecionando para pagamento...</h3>
    <div id="bancardContainer"></div>
</div>

<script src="https://vpos.infonet.com.py/bancard-checkout-5.0.1.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const processId = "{{ $process_id }}";
        const options = {
            process_id: processId,
            container: "#bancardContainer",
            callback: "{{ route('bancard.callback') }}",
        };
        BancardCheckout.initialize(options);
    });
</script>
@endsection
