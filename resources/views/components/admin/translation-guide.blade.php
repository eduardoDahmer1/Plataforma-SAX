@props(['shared' => 'Imagens, arquivos, links, contatos e configurações são compartilhados entre os idiomas.'])

<aside class="translation-guide" aria-label="Como editar os idiomas">
    <div class="translation-guide__icon"><i class="fas fa-language" aria-hidden="true"></i></div>
    <div class="translation-guide__copy">
        <strong>Edite PT, ES e EN no mesmo formulário</strong>
        <span>Use os botões de idioma dentro de cada campo. O ponto colorido indica que aquele idioma possui conteúdo.</span>
        <small><i class="fas fa-link" aria-hidden="true"></i>{{ $shared }}</small>
    </div>
    <div class="translation-guide__legend" aria-label="Idiomas disponíveis">
        <span>PT</span><span>ES</span><span>EN</span>
    </div>
</aside>
