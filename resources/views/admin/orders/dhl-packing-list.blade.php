<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Packing List {{ $order->order_number }}</title>
    <style>
        @page { margin: 28px; }
        body { color: #222; font: 12px Arial, sans-serif; }
        h1 { margin: 0; font-size: 22px; }
        h2 { margin: 24px 0 8px; font-size: 14px; text-transform: uppercase; }
        .meta { margin: 5px 0 20px; color: #555; }
        .box { border: 1px solid #bbb; padding: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 7px; text-align: left; vertical-align: top; }
        th { background: #f1f1f1; font-size: 10px; text-transform: uppercase; }
        .right { text-align: right; }
        .muted { color: #666; }
    </style>
</head>
<body>
    <h1>SAX Department Store — Packing List</h1>
    <div class="meta">
        Order: <strong>{{ $order->order_number ?: $order->id }}</strong>
        @if($order->dhl_tracking_number) · AWB: <strong>{{ $order->dhl_tracking_number }}</strong>@endif
        · Date: {{ now()->format('Y-m-d') }}
    </div>

    <table>
        <tr>
            <th>Shipper</th>
            <th>Consignee</th>
        </tr>
        <tr>
            <td>
                {{ $origin['company_name'] ?? 'SAX Department Store' }}<br>
                {{ $origin['address_line_1'] ?? '' }}@if($origin['address_line_2'] ?? null), {{ $origin['address_line_2'] }}@endif<br>
                <span class="muted">{{ $origin['city_name'] ?? '' }}@if($origin['province_name'] ?? null), {{ $origin['province_name'] }}@endif · {{ $origin['country_code'] ?? 'PY' }}</span>
            </td>
            <td>
                {{ $order->name }}<br>
                {{ trim($order->street.' '.$order->number) }}@if($order->complement), {{ $order->complement }}@endif<br>
                @if($order->district){{ $order->district }}, @endif{{ $order->city }}@if($order->state), {{ $order->state }}@endif<br>
                {{ $order->cep }} · {{ $order->country }}
            </td>
        </tr>
    </table>

    <h2>Contents</h2>
    <table>
        <thead>
            <tr><th>#</th><th>SKU / description</th><th>HS code</th><th>Origin</th><th class="right">Qty</th><th class="right">Weight</th></tr>
        </thead>
        <tbody>
        @foreach($order->items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->sku }}<br>{{ $item->shipping_customs_description ?: $item->name }}</td>
                <td>{{ $item->shipping_hs_code ?: $item->product?->shipping_hs_code }}</td>
                <td>{{ $item->shipping_country_of_origin ?: $item->product?->shipping_country_of_origin ?: 'PY' }}</td>
                <td class="right">{{ $item->quantity }}</td>
                <td class="right">{{ number_format((float) $item->shipping_weight_kg * (int) $item->quantity, 3, '.', '') }} kg</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h2>Packages</h2>
    <table>
        <thead><tr><th>#</th><th>Package</th><th>Dimensions (cm)</th><th class="right">Actual weight</th><th class="right">Billable weight</th></tr></thead>
        <tbody>
        @foreach((array) $order->shipping_packages as $package)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $package['name'] ?? 'DHL package' }}</td>
                <td>{{ $package['length'] ?? '—' }} × {{ $package['width'] ?? '—' }} × {{ $package['height'] ?? '—' }}</td>
                <td class="right">{{ number_format((float) ($package['weight'] ?? 0), 3, '.', '') }} kg</td>
                <td class="right">{{ number_format((float) ($package['billable_weight'] ?? 0), 3, '.', '') }} kg</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <p class="muted">Commercial retail merchandise. Generated from the order and package snapshots recorded at checkout.</p>
</body>
</html>
