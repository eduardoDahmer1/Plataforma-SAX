@props([
    'eyebrow' => 'SAX Selection',
    'title',
    'description' => null,
])

<section class="sax-directory-intro" aria-labelledby="directory-page-title">
    <div class="sax-directory-intro__glow" aria-hidden="true"></div>
    <div class="sax-directory-intro__content">
        @if (filled($eyebrow))
            <span class="sax-directory-intro__eyebrow"><i></i>{{ $eyebrow }}<i></i></span>
        @endif
        <h1 id="directory-page-title">{{ $title }}</h1>
        @if (filled($description))
            <p>{{ $description }}</p>
        @endif
    </div>
</section>

@once
    @push('styles')
        <link href="{{ asset('css/directory-pages.css') }}?v={{ filemtime(public_path('css/directory-pages.css')) }}" rel="stylesheet">
    @endpush
@endonce
