@php
    $sectionContent = is_array($sectionContent ?? null)
        ? array_merge(['title' => '', 'description' => ''], $sectionContent)
        : ['title' => '', 'description' => ''];
@endphp

<div class="sax-wrapper">
<section class="newsletter-section"
    style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('{{ asset('storage/uploads/' . ($banner1 ?? 'banner1.webp')) }}');">
    <div class="newsletter-container">
        @if(filled($sectionContent['title']))
            <h2>{{ $sectionContent['title'] }}</h2>
        @endif
        @if(filled($sectionContent['description']))
            <p class="subtitle">{{ $sectionContent['description'] }}</p>
        @endif
        <div class="form-wrapper">
            <form class="newsletter-form" action="{{ route('newsletter.store') }}" method="POST">
                @csrf
                <input type="hidden" name="contact_type" value="3">
                <input type="hidden" name="name" value="Newsletter Subscriber">
                <input type="email" name="email" placeholder="{{ __('messages.seu_email') }}" required>
                <button type="submit">{{ __('messages.inscrever_se') }}</button>
            </form>
            <p class="legal-text">{{ __('messages.prazos_entrega_texto') }}</p>
        </div>
    </div>
</section>
</div>
