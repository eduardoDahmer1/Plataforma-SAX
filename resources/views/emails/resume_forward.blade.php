<p>Foi recebido um novo currículo pelo site SAX.</p>
<p><strong>Nome:</strong> {{ $contact->name }}<br>
<strong>E-mail:</strong> {{ $contact->email }}<br>
<strong>Telefone:</strong> {{ $contact->phone ?: 'Não informado' }}<br>
<strong>Loja desejada:</strong> {{ $contact->store_name }}</p>
<p><strong>Mensagem:</strong><br>{!! nl2br(e($contact->message)) !!}</p>
<p>Currículo em anexo. Registro #{{ $contact->id }} no painel de contatos.</p>
