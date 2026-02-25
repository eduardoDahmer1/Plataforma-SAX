<section class="sv-editorial section-padding">
    <div class="container">

        <div class="text-center mb-5" data-reveal="up">
            <span class="title-gold">{!! $sectionLabel !!}</span>
            <h2 class="section-title">{!! $sectionTitle !!}</h2>
        </div>

        @php
            $stockServices = [
                'https://images.unsplash.com/photo-1594552072238-b8a33785b6cd?w=800&q=80&fit=crop',
                'https://images.unsplash.com/photo-1519741497674-4c45ba9ff7a2?w=800&q=80&fit=crop',
                'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=800&q=80&fit=crop',
                'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?w=800&q=80&fit=crop',
            ];
        @endphp

        @foreach($services as $i => $service)
            <div class="sv-block {{ $i % 2 !== 0 ? 'sv-block--reverse' : '' }}" data-reveal="up" style="transition-delay: {{ $i * 0.08 }}s">

                {{-- Imagen --}}
                <div class="sv-img-col">
                    @if(!empty($service['image']))
                        <img src="{{ asset('storage/' . $service['image']) }}" alt="{{ $service['title'] }}" class="sv-img">
                    @else
                        <img src="{{ $stockServices[$i % count($stockServices)] }}" alt="{{ $service['title'] }}" class="sv-img">
                    @endif
                </div>

                {{-- Texto --}}
                <div class="sv-text-col">
                    <span class="sv-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}.</span>
                    <h3 class="sv-title">{{ $service['title'] }}</h3>
                    <p class="sv-desc">{{ $service['description'] }}</p>
                </div>

            </div>
        @endforeach

        @if(!empty($ctaLink) && !empty($ctaText))
            <div class="text-center mt-5" data-reveal="up">
                <a href="{{ $ctaLink }}" class="btn-sax">{{ $ctaText }}</a>
            </div>
        @endif

    </div>
</section>

@push('styles')
<style>
    .sv-editorial {
        background: var(--bridal-white);
        padding: 60px 0;
    }

    .sv-block {
        display: grid;
        grid-template-columns: 2fr 3fr;
        gap: 56px;
        align-items: center;
        margin-bottom: 64px;
    }

    .sv-block:last-of-type {
        margin-bottom: 0;
    }

    .sv-block--reverse {
        grid-template-columns: 3fr 2fr;
    }

    .sv-block--reverse .sv-img-col {
        order: 2;
    }

    .sv-block--reverse .sv-text-col {
        order: 1;
    }

    .sv-img-col {
        position: relative;
    }

    .sv-img {
        width: 100%;
        aspect-ratio: 4 / 5;
        height: auto;
        max-height: 500px;
        object-fit: cover;
        display: block;
        border-radius: 4px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18), 0 6px 16px rgba(0, 0, 0, 0.1);
        filter: brightness(1.02) saturate(1.05);
        transition: transform 0.6s ease, box-shadow 0.6s ease;
    }

    .sv-img:hover {
        transform: scale(1.02);
        box-shadow: 0 28px 60px rgba(0, 0, 0, 0.22), 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .sv-text-col {
        padding: 12px 0;
    }

    .sv-num {
        font-family: var(--font-serif);
        font-size: 0.8rem;
        color: var(--bridal-gold);
        letter-spacing: 2px;
        display: block;
        margin-bottom: 4px;
        opacity: 0.7;
    }

    .sv-title {
        font-family: var(--font-serif);
        font-size: clamp(1.2rem, 2vw, 1.65rem);
        font-weight: 400;
        text-transform: uppercase;
        letter-spacing: 3px;
        color: var(--bridal-dark);
        line-height: 1.3;
        margin-bottom: 18px;
    }

    .sv-desc {
        font-size: 0.88rem;
        color: var(--bridal-dark);
        opacity: 0.5;
        line-height: 1.7;
        max-width: 100%;
        margin-bottom: 0;
    }

    .sv-block--reverse .sv-desc {
        margin-left: auto;
    }

    @media (max-width: 767px) {
        .sv-editorial {
            padding: 48px 0;
        }

        .sv-block,
        .sv-block--reverse {
            grid-template-columns: 1fr;
            gap: 24px;
            margin-bottom: 48px;
        }

        .sv-block--reverse .sv-img-col,
        .sv-block--reverse .sv-text-col {
            order: unset;
        }

        .sv-img {
            aspect-ratio: 4 / 3;
            object-position: top center;
        }

        .sv-text-col {
            text-align: center;
            padding: 0;
        }

        .sv-desc {
            max-width: 100%;
            margin: 0 auto;
        }

        .sv-block--reverse .sv-desc {
            margin-left: auto;
            margin-right: auto;
        }
    }
</style>
@endpush
