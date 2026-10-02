@extends('layout.email')

@section('title', $title)

@section('content')
    @php
        $isRecovery = $type === 'integration_recovered';
        $accent = '#55595e';
        $label = $isRecovery ? 'Recuperação da integração' : 'Alerta de integração';
    @endphp

    <p style="margin:0 0 0.6rem 0;font-size:0.76rem;letter-spacing:0.2rem;text-transform:uppercase;color:#74787d;">Monitoramento automático</p>
    <h1 class="sax-email-title" style="margin:0 0 1.8rem 0;font-size:24px;font-weight:800;letter-spacing:0.02rem;color:#25282c;line-height:1.15;">{{ $title }}</h1>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 1.5rem 0;">
        <tr>
            <td style="background:#f3f3f2;border-left:4px solid {{ $accent }};padding:0.9rem 1rem;">
                <span style="font-size:0.82rem;font-weight:700;color:#222222;text-transform:uppercase;letter-spacing:0.08rem;">{{ $label }}</span>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 1.6rem 0;font-size:1rem;color:#34373b;line-height:1.75;">
        {{ $alertMessage }}
    </p>

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f5f4;margin-bottom:1.8rem;border:1px solid #d7d8da;">
        <tr>
            <td style="padding:1.2rem 1.4rem;">
                <p style="margin:0 0 0.75rem 0;font-size:0.7rem;text-transform:uppercase;letter-spacing:0.15rem;color:#74787d;">Detalhes</p>
                <p style="margin:0 0 0.45rem 0;font-size:0.92rem;color:#383b40;"><strong>Origem:</strong> {{ $details['source'] ?? 'catalog' }}</p>
                <p style="margin:0 0 0.45rem 0;font-size:0.92rem;color:#383b40;"><strong>Status:</strong> {{ $details['status'] ?? ($isRecovery ? 'healthy' : 'error') }}</p>
                @if(!empty($details['error_code']))
                    <p style="margin:0;font-size:0.92rem;color:#383b40;"><strong>Código:</strong> {{ $details['error_code'] }}</p>
                @endif
            </td>
        </tr>
    </table>

    <x-email-button :url="url($actionUrl ?: '/admin')">Abrir monitor no painel</x-email-button>

    <p style="margin:1.7rem 0 0 0;font-size:0.85rem;color:#74787d;line-height:1.7;">
        Este aviso foi gerado automaticamente pela SAX.
    </p>
@endsection
