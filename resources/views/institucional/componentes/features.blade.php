<section class="features-elegant py-5">
    <div class="container py-5">
        <div class="row g-4 justify-content-center">
            {{-- Feature 01 --}}
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card-feature">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-gem"></i>
                    </div>
                    <div class="feature-divider"></div>
                    <h3 class="feature-title">{{ $institucional->text_section_one_title }}</h3>
                    <p class="feature-body">{{ $institucional->text_section_one_body }}</p>
                </div>
            </div>

            {{-- Feature 02 (Destaque Central) --}}
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card-feature active">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-star"></i>
                    </div>
                    <div class="feature-divider"></div>
                    <h3 class="feature-title">{{ $institucional->text_section_two_title }}</h3>
                    <p class="feature-body">{{ $institucional->text_section_two_body }}</p>
                </div>
            </div>

            {{-- Feature 03 --}}
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card-feature">
                    <div class="feature-icon-wrapper">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="feature-divider"></div>
                    <h3 class="feature-title">{{ $institucional->text_section_three_title }}</h3>
                    <p class="feature-body">{{ $institucional->text_section_three_body }}</p>
                </div>
            </div>
        </div>
    </div>
</section>
