<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f9f9f9; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border: 1px solid #eeeeee; }
        .header { padding: 40px; text-align: center; background-color: #000000; }
        .content { padding: 40px; color: #333333; line-height: 1.6; }
        .order-info { background-color: #fcfcfc; border: 1px solid #f0f0f0; padding: 20px; margin: 20px 0; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #999999; }
        .btn { display: inline-block; padding: 12px 25px; background-color: #000000; color: #ffffff !important; text-decoration: none; font-weight: bold; text-transform: uppercase; font-size: 13px; margin-top: 20px; }
        h1 { font-size: 20px; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px; }
        .price { font-weight: bold; color: #000; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            {{-- Substitua pelo link da sua logo --}}
            <h2 style="color: #fff; margin: 0; letter-spacing: 5px;">SAX</h2>
        </div>
        
        <div class="content">
            <h1>Olá, {{ $order->name }}</h1>
            <p>{{ $messageCustom }}</p>
            
            <div class="order-info">
                <p style="margin: 0;"><strong>Pedido:</strong> #{{ $order->order_number ?? $order->id }}</p>
                <p style="margin: 0;"><strong>Data:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                <p style="margin: 0;"><strong>Método:</strong> {{ ucfirst($order->payment_method) }}</p>
                <p style="margin: 10px 0 0 0; font-size: 18px;"><strong>Total:</strong> <span class="price">{{ $order->currency_sign }} {{ number_format($order->total, 2, ',', '.') }}</span></p>
            </div>

            <p>Você pode acompanhar o status detalhado clicando no botão abaixo:</p>
            
            <a href="{{ route('user.orders.show', $order->id) }}" class="btn">Ver meu pedido</a>
        </div>

        <div class="footer">
            <p>&copy; {{ date('year') }} SAX Department Store. Todos os direitos reservados.</p>
            <p>Ciudad del Este, Paraguai | Foz do Iguaçu, Brasil</p>
        </div>
    </div>
</body>
</html>