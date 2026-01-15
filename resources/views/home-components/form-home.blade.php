    <div class="container help-cards-container mb-3">
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="help-card shadow-sm">
                    <i class="fas fa-tshirt card-icon"></i>
                    <h6>CÓMO REALIZAR UNA COMPRA</h6>
                    <p>Tu guia para hacer pedidos</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="help-card shadow-sm">
                    <i class="far fa-question-circle card-icon"></i>
                    <h6>PREGUNTAS FRECUENTES</h6>
                    <p>¡Respondemos tus preguntas!</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="help-card shadow-sm">
                    <i class="fas fa-info-circle card-icon"></i>
                    <h6>¿NECESITAS AYUDA?</h6>
                    <p>Contacta a nuestro equipo de Atención al Cliente</p>
                </div>
            </div>
        </div>
    </div>
    <section class="sax-help-newsletter">
        {{-- Parte Inferior: Newsletter com Background --}}
        <div class="newsletter-bg-section" style="background-image: url('{{ asset('images/sax-store-bg.jpg') }}');">
            <div class="overlay"></div>
            <div class="container newsletter-content">
                <div class="row justify-content-center">
                    <div class="col-lg-8 text-center text-white">
                        <h2 class="display-6 fw-bold mb-3">No te pierdas de nada</h2>
                        <p class="mb-4">Regístrate para recibir promociones, novedades personalizadas, actualizaciones
                            de inventario y mucho más, directamente en su correo.</p>

                        <form action="#" method="POST"
                            class="newsletter-form d-flex gap-2 justify-content-center flex-wrap">
                            @csrf
                            <input type="email" name="email" class="form-control sax-input-light"
                                placeholder="Tu correo electrónico" required>
                            <button type="submit" class="btn btn-sax-dark">SUBSCRÍBETE</button>
                        </form>

                        <p class="x-small mt-4 opacity-75">
                            Al registrarte, aceptas recibir comunicaciones de marketing por email y reconoces que leíste
                            nuestra Política de Privacidad. Puedes darte de baja en cualquier momento.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <style>
        /* Container Geral */
        .sax-help-newsletter {
            position: relative;
            padding-top: 50px;
        }

        /* Cards de Ajuda */
        .help-cards-container {
            position: relative;
            z-index: 10;
            margin-bottom: -60px;
            /* Faz os cards sobreporem a imagem */
        }

        .help-card {
            background: #fff;
            border-radius: 12px;
            padding: 30px 20px;
            text-align: center;
            border: 1px solid #eee;
            height: 100%;
            transition: transform 0.3s ease;
        }

        .help-card:hover {
            transform: translateY(-5px);
        }

        .card-icon {
            font-size: 2rem;
            margin-bottom: 15px;
            color: #333;
        }

        .help-card h6 {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .help-card p {
            font-size: 0.8rem;
            color: #666;
            margin: 0;
        }

        /* Seção Newsletter */
        .newsletter-bg-section {
            position: relative;
            background-size: cover;
            background-position: center;
            padding: 120px 0 80px 0;
            /* Espaço para compensar a sobreposição */
            min-height: 450px;
            display: flex;
            align-items: center;
        }

        .newsletter-bg-section .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            /* Escurece a imagem para o texto ler bem */
        }

        .newsletter-content {
            position: relative;
            z-index: 5;
        }

        /* Formulário Newsletter */
        .sax-input-light {
            max-width: 400px;
            height: 55px;
            border-radius: 4px;
            border: none;
            padding: 0 20px;
            font-size: 0.9rem;
        }

        .btn-sax-dark {
            background: #1a1a1a;
            color: #fff;
            border: 1px solid #333;
            padding: 0 30px;
            height: 55px;
            border-radius: 4px;
            font-weight: bold;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .btn-sax-dark:hover {
            background: #000;
            color: #fff;
        }

        .x-small {
            font-size: 0.7rem;
            line-height: 1.4;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .help-cards-container {
                margin-bottom: 20px;
            }

            .newsletter-bg-section {
                padding: 60px 0;
            }

            .sax-input-light {
                width: 100%;
            }
        }
    </style>
