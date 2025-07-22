<footer class="bg-light text-center p-3 d-grid justify-content-center align-items-center">
    <p class="mb-0">&copy; {{ date('Y') }} Todos os direitos reservados.</p>
    @if ($webpImage)
    <div>
        <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="Imagem Header"
            style="max-height: 150px; display: block; margin-bottom: 10px;">
    </div>
    @endif
</footer>