@extends('layout.layout')

@section('content')
<div class="guide-two" id="guideTwo">
    <div class="guide-two__container">
        <nav class="guide-two__breadcrumb" aria-label="Breadcrumb">
            <a href="{{ url('/') }}">{{ __('messages.contact_guide_v2_home') }}</a><span aria-hidden="true">/</span><span>{{ __('messages.contact_guide_v2_title') }}</span>
        </nav>
        <header class="guide-two__hero" id="guideTwoHero" aria-labelledby="guideTwoHeroTitle">
            <div class="guide-two__hero-copy">
                <h1 id="guideTwoHeroTitle">{{ __('messages.contact_guide_v2_title') }}</h1>
                <p>{{ __('messages.contact_guide_v2_hero_subtitle') }}</p>
            </div>
        </header>
        @if(empty($directory))
            <p class="guide-two__empty">{{ __('messages.contact_guide_v2_unavailable') }}</p>
        @else
            <div class="guide-two__controls">
                <div>
                    <label for="guideTwoSearch">{{ __('messages.contact_guide_v2_search') }}</label>
                    <div class="guide-two__search">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <input id="guideTwoSearch" type="search" placeholder="{{ __('messages.contact_guide_v2_search') }}" autocomplete="off" aria-controls="guideTwoResults">
                        <button id="guideTwoClear" type="button" aria-label="{{ __('messages.contact_guide_v2_clear') }}" hidden><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                    </div>
                </div>
                <div>
                    <label for="guideTwoLocation">{{ __('messages.contact_guide_v2_location') }}</label>
                    <select id="guideTwoLocation">
                        @foreach($directory as $location)
                            <option value="{{ $location['id'] }}">{{ $location['name'] }} — {{ $location['city'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <section id="guideTwoResults" class="guide-two__results" aria-label="{{ __('messages.contact_guide_v2_results') }}" hidden>
                <p id="guideTwoResultStatus" role="status"></p>
                <ul id="guideTwoResultList"></ul>
            </section>
            <p id="guideTwoHours" class="guide-two__hours"></p>
            <div class="guide-two__layout">
                <aside class="guide-two__sidebar">
                    <h2>{{ __('messages.contact_guide_v2_floors') }}</h2>
                    <nav id="guideTwoFloors" aria-label="{{ __('messages.contact_guide_v2_floors') }}"></nav>
                </aside>
                <div class="guide-two__mobile-floor">
                    <label for="guideTwoFloor">{{ __('messages.contact_guide_v2_floor') }}</label>
                    <select id="guideTwoFloor"></select>
                </div>
                <div id="guideTwoContent"></div>
            </div>
            <noscript><p>{{ __('messages.contact_guide_v2_javascript') }} <a href="{{ route('contact.guide') }}">{{ __('messages.contact_guide_v2_title') }}</a></p></noscript>
        @endif
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/contact-guide-alternative.css') }}?v={{ filemtime(public_path('css/contact-guide-alternative.css')) }}">
@endpush

@push('scripts')
<script>
    window.saxGuideDirectory = {{ Illuminate\Support\Js::from($directory) }};
    window.saxGuideLabels = {{ Illuminate\Support\Js::from([
        'contact' => __('messages.contact_guide_v2_contact'),
        'contacts' => __('messages.contact_guide_v2_contacts'),
        'see_more' => __('messages.contact_guide_v2_see_more'),
        'see_less' => __('messages.contact_guide_v2_see_less'),
        'results_count' => __('messages.contact_guide_v2_results_count'),
        'empty' => __('messages.contact_guide_v2_empty'),
    ]) }};
</script>
<script src="{{ asset('js/contact-guide-alternative.js') }}?v={{ filemtime(public_path('js/contact-guide-alternative.js')) }}" defer></script>
@endpush
