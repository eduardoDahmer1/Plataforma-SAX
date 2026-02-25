@extends('layout.bridal')

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
        'brands' => $bridal->brands ?? []
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
        'instagramUrl' => $bridal->social_instagram ?? '#',
    ])

    
    {{-- Contacto / Sucursales --}}
    @include('bridal.componentes.contato', [
        'sectionLabel'     => 'ENCUÉNTRANOS',
        'sectionTitle'     => 'Nuestras Sucursales',
        'asuncion_name'    => $bridal->branch_asuncion_name    ?? 'Asunción',
        'asuncion_address' => $bridal->branch_asuncion_address ?? null,
        'asuncion_phone'   => $bridal->branch_asuncion_phone   ?? null,
        'asuncion_image'   => $bridal->branch_asuncion_image   ?? null,
        'cde_name'         => $bridal->branch_cde_name         ?? 'Ciudad del Este',
        'cde_address'      => $bridal->branch_cde_address      ?? null,
        'cde_phone'        => $bridal->branch_cde_phone        ?? null,
        'cde_image'        => $bridal->branch_cde_image        ?? null,
    ])

@endsection

