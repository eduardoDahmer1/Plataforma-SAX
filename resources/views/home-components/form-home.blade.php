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

    <section class="newsletter-section" 
         style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('{{ asset('storage/uploads/' . $banner1) }}');">
        <div class="newsletter-container">
            <h2>No te pierdas de nada</h2>
            <p class="subtitle">Regístrate para recibir promociones, novedades personalizadas, actualizaciones de inventario y mucho más, directamente en su correo.</p>
            
            <div class="form-wrapper">
                <form class="newsletter-form">
                    <input type="email" placeholder="Tu correo electrónico" required>
                    <button type="submit">SUBSCRÍBETE</button>
                </form>
                <p class="legal-text">
                    Al registrarte, aceptas recibir comunicaciones de marketing por email y reconoces que leíste nuestra Política de Privacidad. Puedes darte de baja en cualquier momento en la parte inferior de nossos emails.
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
    background: #fff;
    transition: transform 0.3s ease;
}

.help-card:hover {
    transform: translateY(-5px);
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
    letter-spacing: 0.5px;
}

.help-card p {
    font-size: 13px;
    color: #666;
    margin: 0;
}

/* --- SEÇÃO NEWSLETTER (ESTILIZADA COM BANNER) --- */
.newsletter-section {
    min-height: 450px; /* Aumentado para dar mais destaque ao banner */
    background-size: cover !important;
    background-position: center center !important;
    background-repeat: no-repeat !important;
    padding: 100px 20px;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    /* Cor de fundo caso o banner falhe */
    background-color: #333; 
}

.newsletter-container {
    max-width: 800px;
    width: 100%;
    text-align: left;
}

.newsletter-container h2 {
    font-size: 36px;
    margin-bottom: 15px;
    font-weight: 400;
    letter-spacing: 1px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.newsletter-container .subtitle {
    font-size: 16px;
    margin-bottom: 35px;
    line-height: 1.6;
    opacity: 0.95;
}

.form-wrapper {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.newsletter-form {
    display: flex;
    gap: 12px;
    width: 100%;
    margin-bottom: 20px;
}

.newsletter-form input {
    flex: 1;
    padding: 16px 20px;
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 4px;
    font-size: 14px;
    background: rgba(255, 255, 255, 0.95);
    color: #333;
    outline: none;
}

.newsletter-form button {
    background-color: #000;
    color: #fff;
    border: 1px solid #000;
    padding: 0 45px;
    border-radius: 4px;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
}

.newsletter-form button:hover {
    background-color: #333;
    border-color: #333;
}

.legal-text {
    font-size: 12px;
    line-height: 1.5;
    text-align: left;
    margin: 0;
    opacity: 0.8;
    max-width: 90%;
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

    .newsletter-section {
        padding: 60px 20px;
        min-height: auto;
    }

    .newsletter-form {
        flex-direction: column;
    }

    .newsletter-form button {
        padding: 18px;
    }

    .newsletter-container h2 {
        font-size: 28px;
    }

    .form-wrapper {
        align-items: center;
        text-align: center;
    }
    
    .legal-text {
        text-align: center;
    }
}
</style>