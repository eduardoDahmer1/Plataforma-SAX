@extends('layout.email')

@section('title', 'Novo currículo recebido')

@section('content')
    <p style="margin:0 0 6px;color:#74787d;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">Recrutamento</p>
    <h1 class="sax-email-title" style="margin:0 0 20px;color:#25282c;font-size:24px;font-weight:800;line-height:1.2;">Novo currículo recebido</h1>
    <p class="sax-email-copy" style="margin:0 0 18px;color:#55595e;font-size:14px;line-height:1.6;">Uma nova candidatura foi enviada pelo site SAX.</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 18px;background:#f5f5f4;border:1px solid #d7d8da;border-radius:3px;">
        <tr>
            <td class="sax-email-card-cell" style="padding:16px 18px;color:#383b40;font-size:13px;line-height:1.75;">
                <strong style="color:#25282c;">Nome:</strong> {{ $contact->name }}<br>
                <strong style="color:#25282c;">E-mail:</strong> {{ $contact->email }}<br>
                <strong style="color:#25282c;">Telefone:</strong> {{ $contact->phone ?: 'Não informado' }}<br>
                <strong style="color:#25282c;">Loja desejada:</strong> {{ $contact->store_name }}
            </td>
        </tr>
    </table>

    <p style="margin:0 0 6px;color:#74787d;font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;">Mensagem</p>
    <div style="margin:0 0 18px;padding:14px 16px;background:#ffffff;border:1px solid #d7d8da;color:#383b40;font-size:13px;line-height:1.65;">{!! nl2br(e($contact->message)) !!}</div>

    <p style="margin:0;color:#74787d;font-size:11px;line-height:1.6;">Currículo em anexo. Registro #{{ $contact->id }} no painel de contatos.</p>
@endsection
