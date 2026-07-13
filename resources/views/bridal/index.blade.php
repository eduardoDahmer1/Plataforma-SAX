@extends('layout.internas')

@section('title', $bridal->title ?? 'SAX Bridal')

@php
    // Traducción activa: cada texto y lista cae al idioma actual, con fallback
    // a la tabla principal si ese idioma todavía no se guardó.
    $t = $bridal->translations->firstWhere('locale', translation_locale());

    $rawServices     = $t?->bridal_services     ?? $bridal->services;
    $rawPromos       = $t?->bridal_promos       ?? $bridal->promos;
    $rawTestimonials = $t?->bridal_testimonials ?? $bridal->testimonials;
    $rawLocations    = $t?->bridal_locations    ?? $bridal->locations;

    $services     = is_array($rawServices)     ? $rawServices     : (json_decode($rawServices, true)     ?? []);
    $promos       = is_array($rawPromos)       ? $rawPromos       : (json_decode($rawPromos, true)       ?? []);
    $testimonials = is_array($rawTestimonials) ? $rawTestimonials : (json_decode($rawTestimonials, true) ?? []);
    $locations    = is_array($rawLocations)    ? $rawLocations    : (json_decode($rawLocations, true)    ?? []);
@endphp

@section('content')

    {{-- Hero --}}
    @include('bridal.componentes.hero', [
      'backgroundImage' => asset('storage/' . $bridal->hero_image),
      'subtitle'        => $t?->bridal_hero_subtitle    ?? $bridal->hero_subtitle,
      'title'           => $t?->bridal_hero_title       ?? $bridal->hero_title,
      'description'     => $t?->bridal_hero_description ?? $bridal->hero_description,
      'primaryLink'     => '#contact',
      'primaryText'     => 'Conoce más',
      'secondaryLink'   => route('contact.form'),
      'secondaryText'   => 'Contáctanos',
    ])

    {{-- Brand Ticker --}}
    @include('bridal.componentes.brand-ticker', [
        'brands' => $brands
    ])

    {{-- Products Carousel --}}
    @include('bridal.componentes.products-carousel', [
        'sectionLabel' => 'Colección',
        'sectionTitle' => 'Nuestros Productos',
        'products'     => $bridalProducts,
    ])


    {{-- Promo Carousel --}}
    @include('bridal.componentes.promo-carousel', [
        'promos' => $promos
    ])

    {{-- Services --}}
    @include('bridal.componentes.services', [
        'sectionLabel' => $t?->bridal_services_label    ?? $bridal->services_label,
        'sectionTitle' => $t?->bridal_services_title    ?? $bridal->services_title,
        'services'     => $services,
        'ctaLink'      => $bridal->services_cta_link,
        'ctaText'      => $t?->bridal_services_cta_text ?? $bridal->services_cta_text,
    ])


    {{-- Testimonials --}}
    @include('bridal.componentes.testimonials', [
        'sectionLabel' => $t?->bridal_testimonials_label ?? $bridal->testimonials_label,
        'sectionTitle' => $t?->bridal_testimonials_title ?? $bridal->testimonials_title,
        'testimonials' => $testimonials
    ])

    {{-- Instagram CTA --}}
    @include('bridal.componentes.instagram-cta', [
        'instagramUrl' => $bridal->social_instagram ? 'https://instagram.com/' . ltrim($bridal->social_instagram, '@') : '#',
    ])


    {{-- Contacto / Sucursales --}}
    @include('bridal.componentes.contato', [
    'locations' => $locations,
    ])


@endsection

@section('footer')
    @include('bridal.componentes.footer')
@endsection

