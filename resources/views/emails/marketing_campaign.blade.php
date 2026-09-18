@extends('layout.email')

@section('title', $renderedSubject)

@section('content')
    <div style="font-size:16px;line-height:1.7;color:#252525;word-break:break-word">
        {!! $renderedBody !!}
    </div>

    @if ($campaign->type === 'marketing')
        <p style="margin:32px 0 0;padding-top:20px;border-top:1px solid #eeeeee;text-align:center;font-size:11px;line-height:1.5;color:#888888">
            Você recebeu esta mensagem por ter cadastro ou contato com a SAX.
            <a href="{{ route('email.unsubscribe', $recipient->unsubscribe_token) }}" style="color:#555555">Não quero mais receber e-mails promocionais</a>.
        </p>
    @endif
@endsection
