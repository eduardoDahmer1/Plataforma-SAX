<section class="vista-newsletter">
    <div>
        <h2>{{ $sectionContent['title'] ?: 'No te pierdas de nada' }}</h2>
        <p>{{ $sectionContent['description'] ?: 'Regístrate para recibir promociones, novedades personalizadas y actualizaciones de inventario.' }}</p>
        <form action="{{ route('newsletter.store') }}" method="POST">
            @csrf
            <input type="hidden" name="contact_type" value="3"><input type="hidden" name="name" value="Newsletter Subscriber">
            <input type="email" name="email" placeholder="Tu correo electrónico" required>
            <button type="submit">Suscríbete</button>
        </form>
        <small>Al registrarte, aceptas recibir comunicaciones de marketing y reconoces que leíste nuestra Política de Privacidad.</small>
    </div>
</section>
