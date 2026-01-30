<div class="sax-wrapper">
    <section class="help-section">
        <div class="help-grid">
            <div class="help-card">
                <div class="icon">👕</div>
                <h3>CÓMO REALIZAR UNA COMPRA</h3>
                <p>Tu guía para hacer pedidos</p>
            </div>
            <div class="help-card">
                <div class="icon red-icon">?</div>
                <h3>PREGUNTAS FRECUENTES</h3>
                <p>¡Respondemos tus preguntas!</p>
            </div>
            <div class="help-card">
                <div class="icon">ⓘ</div>
                <h3>¿NECESITAS AYUDA?</h3>
                <p>Contacta a nuestro equipo de Atención al Cliente</p>
            </div>
        </div>
    </section>

    <section class="newsletter-section">
        <div class="newsletter-container">
            <h2>No te pierdas de nada</h2>
            <p class="subtitle">Regístrate para recibir promociones, novedades personalizadas, actualizaciones de inventario y mucho más, directamente en su correo.</p>
            
            <div class="form-wrapper">
                <form class="newsletter-form">
                    <input type="email" placeholder="Tu correo electrónico" required>
                    <button type="submit">SUBSCRÍBETE</button>
                </form>
                <p class="legal-text">
                    Al registrarte, aceptas recibir comunicaciones de marketing por email y reconoces que leíste nuestra Política de Privacidad. Puedes darte de baja en cualquier momento en la parte inferior de nuestros emails.
                </p>
            </div>
        </div>
    </section>
</div>

<style>
/* Reset e Base */
.sax-wrapper {
    font-family: 'Helvetica', Arial, sans-serif;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    background-color: #fff;
    width: 100%;
}

.sax-wrapper *, .sax-wrapper *::before, .sax-wrapper *::after {
    box-sizing: inherit;
}

/* --- SEÇÃO DE CARDS --- */
.help-section {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.help-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.help-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 30px;
    text-align: left;
}

.help-card .icon {
    font-size: 24px;
    margin-bottom: 15px;
}

.help-card .red-icon {
    color: #d9534f;
    font-weight: bold;
}

.help-card h3 {
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 8px;
    color: #000;
}

.help-card p {
    font-size: 13px;
    color: #666;
    margin: 0;
}

/* --- SEÇÃO NEWSLETTER --- */
.newsletter-section {
    background-color: #939393;
    padding: 80px 20px;
    color: white;
    display: flex;
    justify-content: center;
}

.newsletter-container {
    max-width: 800px; /* Reduzi para focar o conteúdo */
    width: 100%;
}

.newsletter-container h2 {
    font-size: 32px;
    margin-bottom: 15px;
    font-weight: 400;
}

.newsletter-container .subtitle {
    font-size: 15px;
    margin: 0 auto 30px auto;
    line-height: 1.5;
    opacity: 0.9;
}

/* Wrapper centralizado, mas com conteúdo alinhado à esquerda */
.form-wrapper {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: flex-start; /* Alinha o texto legal com o início do input */
}

.newsletter-form {
    display: flex;
    gap: 10px;
    width: 100%;
    margin-bottom: 15px;
}

.newsletter-form input {
    flex: 1;
    padding: 15px 20px;
    border: none;
    border-radius: 4px;
    font-size: 14px;
}

.newsletter-form button {
    background-color: #1a1a1a;
    color: #fff;
    border: none;
    padding: 0 35px;
    border-radius: 4px;
    font-weight: bold;
    font-size: 14px;
    cursor: pointer;
    text-transform: uppercase;
}

.legal-text {
    font-size: 11px;
    line-height: 1.4;
    text-align: left; /* Alinhado com a borda do input */
    margin: 0;
    opacity: 0.8;
}

/* --- RESPONSIVIDADE --- */
@media (max-width: 992px) {
    .help-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {
    .help-grid {
        grid-template-columns: 1fr;
    }

    .newsletter-form {
        flex-direction: column;
    }

    .newsletter-form button {
        padding: 15px;
    }

    .newsletter-container h2 {
        font-size: 26px;
    }

    .form-wrapper {
        align-items: center; /* Centraliza no mobile para melhor estética */
    }

    .legal-text {
    }
}
</style>