<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $receipt->receipt_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 0.75rem;
            color: #1a1a1a;
            background: #fff;
            padding: 0 2rem;
        }

        /* ── HEADER BAND ── */
        .header-band {
            background: #fff;
            padding: 1.4rem 0 1.2rem;
            width: 100%;
            border-bottom: 2px solid #111;
        }

        .header-inner {
            width: 100%;
        }

        .header-inner td {
            vertical-align: middle;
        }

        .logo-cell {
            width: 50%;
        }

        .logo-img {
            max-height: 2.8rem;
            width: auto;
            display: block;
        }

        .logo-text {
            font-size: 2.2rem;
            font-weight: 900;
            letter-spacing: 0.15em;
            color: #111;
        }

        .meta-cell {
            text-align: right;
        }

        .receipt-number {
            font-size: 1.1rem;
            font-weight: 800;
            color: #111;
            letter-spacing: 0.06em;
        }

        .receipt-date {
            font-size: 0.65rem;
            color: #6b7280;
            margin-top: 0.2rem;
        }

        .badge-paid {
            display: inline-block;
            margin-top: 0.45rem;
            padding: 0.2rem 0.7rem;
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
            border-radius: 50px;
            font-size: 0.55rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        /* ── CONTENT ── */
        .content {
            padding: 1.5rem 0;
        }

        /* ── SECTION LABEL ── */
        .section-label {
            font-size: 0.55rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #6b7280;
            padding-bottom: 0.35rem;
            border-bottom: 2px solid #111;
            margin-bottom: 0.65rem;
        }

        /* ── INFO GRID ── */
        .info-grid {
            width: 100%;
            margin-bottom: 1.5rem;
        }

        .info-grid td {
            width: 50%;
            vertical-align: top;
        }

        .info-grid td:first-child {
            padding-right: 0.75rem;
        }

        .info-grid td:last-child {
            padding-left: 0.75rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            padding: 0.28rem 0;
            border-bottom: 1px dashed #e5e7eb;
            font-size: 0.7rem;
            gap: 0.5rem;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #6b7280;
            white-space: nowrap;
        }

        .info-value {
            font-weight: 600;
            text-align: right;
        }

        /* ── PRODUCTS TABLE ── */
        table.products {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.5rem;
            font-size: 0.7rem;
        }

        table.products thead tr {
            background: #1a1a1a;
        }

        table.products thead th {
            padding: 0.5rem 0.6rem;
            color: #fff;
            font-size: 0.55rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 700;
            text-align: left;
        }

        table.products tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }

        table.products tbody tr:last-child {
            border-bottom: none;
        }

        table.products tbody tr:nth-child(even) {
            background: #fafafa;
        }

        table.products tbody td {
            padding: 0.6rem 0.6rem;
            vertical-align: middle;
        }

        .product-img {
            width: 2.4rem;
            height: 2.4rem;
            object-fit: cover;
            border-radius: 0.25rem;
            border: 1px solid #e5e7eb;
            display: block;
        }

        .product-img-placeholder {
            width: 2.4rem;
            height: 2.4rem;
            background: #f3f4f6;
            border-radius: 0.25rem;
            border: 1px solid #e5e7eb;
            display: block;
        }

        .product-name {
            font-weight: 700;
            color: #111;
        }

        .product-sku {
            font-size: 0.6rem;
            color: #9ca3af;
            margin-top: 0.1rem;
        }

        .text-right {
            text-align: right;
        }

        /* ── TOTALS ── */
        .totals-wrap {
            border-top: 1px solid #e5e7eb;
            padding-top: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .totals-table {
            width: 100%;
            font-size: 0.72rem;
        }

        .totals-table td {
            padding: 0.2rem 0.5rem;
        }

        .totals-spacer {
            width: 100%;
        }

        .totals-label {
            color: #6b7280;
            text-align: right;
            padding-right: 2rem;
            white-space: nowrap;
        }

        .totals-value {
            text-align: right;
            white-space: nowrap;
            min-width: 7rem;
        }

        .grand-row td {
            background: #111;
            color: #fff;
            font-size: 0.9rem;
            font-weight: 800;
            padding: 0.55rem 0.75rem;
            margin-top: 0.3rem;
        }

        /* ── FOOTER ── */
        .footer {
            text-align: center;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
            font-size: 0.6rem;
            color: #9ca3af;
            line-height: 1.6;
        }
    </style>
</head>
<body>

    {{-- ── Header ── --}}
    <div class="header-band">
        <table class="header-inner" cellpadding="0" cellspacing="0">
            <tr>
                <td class="logo-cell">
                    @if ($logoPath && file_exists($logoPath))
                        <img src="{{ $logoPath }}" class="logo-img" alt="SAX">
                    @else
                        <span class="logo-text">SAX</span>
                    @endif
                </td>
                <td class="meta-cell">
                    <div class="receipt-number">{{ $receipt->receipt_number }}</div>
                    <div class="receipt-date">Emitido el {{ $receipt->issued_at->format('d/m/Y \a\l\a\s H:i') }}</div>
                    <span class="badge-paid">Pago confirmado</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="content">

        {{-- ── Info: pedido + cliente ── --}}
        <table class="info-grid" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    <div class="section-label">Datos del pedido</div>
                    <div class="info-row">
                        <span class="info-label">N° de pedido</span>
                        <span class="info-value">{{ $order->order_number ?? '#' . $order->id }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Fecha</span>
                        <span class="info-value">{{ $order->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Método de pago</span>
                        <span class="info-value">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                    </div>
                    @if ($order->shop_process_id)
                    <div class="info-row">
                        <span class="info-label">Referencia</span>
                        <span class="info-value">{{ $order->shop_process_id }}</span>
                    </div>
                    @endif
                    @if ($order->shipping == 3)
                    <div class="info-row">
                        <span class="info-label">Entrega</span>
                        <span class="info-value">Retiro en tienda</span>
                    </div>
                    @elseif ($order->street)
                    <div class="info-row">
                        <span class="info-label">Dirección</span>
                        <span class="info-value">{{ $order->street }}{{ $order->number ? ', ' . $order->number : '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Ciudad</span>
                        <span class="info-value">{{ $order->city }}{{ $order->country ? ', ' . $order->country : '' }}</span>
                    </div>
                    @endif
                </td>
                <td>
                    <div class="section-label">Datos del cliente</div>
                    <div class="info-row">
                        <span class="info-label">Nombre</span>
                        <span class="info-value">{{ $order->name }}</span>
                    </div>
                    @if ($order->document)
                    <div class="info-row">
                        <span class="info-label">Documento</span>
                        <span class="info-value">{{ $order->document }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value">{{ $order->email }}</span>
                    </div>
                    @if ($order->phone)
                    <div class="info-row">
                        <span class="info-label">Teléfono</span>
                        <span class="info-value">{{ $order->phone }}</span>
                    </div>
                    @endif
                </td>
            </tr>
        </table>

        {{-- ── Productos ── --}}
        <div class="section-label">Productos</div>
        <table class="products" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th style="width:3rem"></th>
                    <th>Producto</th>
                    <th>SKU</th>
                    <th class="text-right">Precio unit.</th>
                    <th class="text-right">Cant.</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                <tr>
                    <td>
                        @if ($item->product && $item->product->photo)
                            <img src="{{ storage_path('app/public/' . $item->product->photo) }}" class="product-img" alt="">
                        @else
                            <span class="product-img-placeholder"></span>
                        @endif
                    </td>
                    <td>
                        <div class="product-name">{{ $item->external_name ?? $item->name }}</div>
                    </td>
                    <td><span class="product-sku">{{ $item->sku ?? '-' }}</span></td>
                    <td class="text-right">{{ currency_format($item->price) }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right" style="font-weight:700;">{{ currency_format($item->price * $item->quantity) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ── Totales ── --}}
        <div class="totals-wrap">
            <table class="totals-table" cellpadding="0" cellspacing="0">
                @if ($order->discount > 0)
                <tr>
                    <td class="totals-spacer"></td>
                    <td class="totals-label">Descuento</td>
                    <td class="totals-value">- {{ currency_format($order->discount) }}</td>
                </tr>
                @endif
                @if ($order->shipping_cost > 0)
                <tr>
                    <td class="totals-spacer"></td>
                    <td class="totals-label">Envío</td>
                    <td class="totals-value">{{ currency_format($order->shipping_cost) }}</td>
                </tr>
                @endif
                <tr class="grand-row">
                    <td class="totals-spacer"></td>
                    <td style="text-align:right; padding-right:2rem; font-weight:800;">Total</td>
                    <td style="text-align:right; min-width:7rem;">{{ currency_format($order->total) }}</td>
                </tr>
            </table>
        </div>

        {{-- ── Footer ── --}}
        <div class="footer">
            Este documento es un comprobante oficial de compra emitido por SAX.<br>
            {{ $receipt->receipt_number }} &mdash; {{ $receipt->issued_at->format('d/m/Y H:i') }}
        </div>

    </div>
</body>
</html>
