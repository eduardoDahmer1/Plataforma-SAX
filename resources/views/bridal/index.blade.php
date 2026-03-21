@extends('layout.internas')

@section('title', $bridal->title ?? 'SAX Bridal')

@section('content')

    {{-- Hero --}}
    @include('bridal.componentes.hero', [
      'backgroundImage' => asset('storage/' . $bridal->hero_image),
      'subtitle'        => $bridal->hero_subtitle,
      'title'           => $bridal->hero_title,
      'description'     => $bridal->hero_description,
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
        'promos' => $bridal->promos ?? []
    ])

    {{-- Services --}}
    @include('bridal.componentes.services', [
        'sectionLabel' => $bridal->services_label,
        'sectionTitle' => $bridal->services_title,
        'services'     => $bridal->services ?? [],
        'ctaLink'      => $bridal->services_cta_link,
        'ctaText'      => $bridal->services_cta_text,
    ])


    {{-- Testimonials --}}
    @include('bridal.componentes.testimonials', [
        'sectionLabel' => $bridal->testimonials_label,
        'sectionTitle' => $bridal->testimonials_title,
        'testimonials' => $bridal->testimonials ?? []
    ])

    {{-- Instagram CTA --}}
    @include('bridal.componentes.instagram-cta', [
        'instagramUrl' => $bridal->social_instagram ? 'https://instagram.com/' . ltrim($bridal->social_instagram, '@') : '#',
    ])


    {{-- Contacto / Sucursales --}}
    @include('bridal.componentes.contato', [
    'locations' => $bridal->locations ?? [],
    ])


@endsection

@section('footer')
    @include('bridal.componentes.footer')
@endsection

