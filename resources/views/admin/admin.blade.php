@extends('layout.admin')

@section('content')
<x-admin.card>
    <div id="banner-admin-config" data-confirm-delete="{{ __('messages.confirmar_exclusao_imagem') }}" hidden></div>
    <x-admin.page-header title="Central de mídia e banners"
        description="Organize campanhas por posição, confira as medidas do front e atualize imagens e links sem recarregar a página." />
    <x-admin.alert />

    @php
        $groups = [
            'home-slider' => ['label' => 'Slider principal', 'icon' => 'fa-panorama', 'description' => 'Campanhas de abertura da home, exibidas em um carrossel editorial de luxo.'],
            'home-editorial' => ['label' => 'Destaques da home', 'icon' => 'fa-layer-group', 'description' => 'Segundo carrossel para coleções, novidades e campanhas sazonais.'],
            'catalog' => ['label' => 'Catálogo e páginas internas', 'icon' => 'fa-table-cells-large', 'description' => 'Imagens padrão usadas quando categorias, subcategorias, categorias-filhas ou marcas não possuem banner próprio.'],
            'identity' => ['label' => 'Logotipos', 'icon' => 'fa-signature', 'description' => 'Arquivos de identidade das experiências SAX.'],
            'system' => ['label' => 'Sistema', 'icon' => 'fa-sliders', 'description' => 'Imagens auxiliares e estados padrão da plataforma.'],
        ];

        $images = [
            ['field'=>'banner9','group'=>'home-editorial','title'=>'Coleção exclusiva','position'=>'Editorial fixo','description'=>'Imagem ao lado do texto de curadoria da home.','dimensions'=>'1400 × 1000 px','ratioLabel'=>'Proporção 7:5','file'=>$banners['banner9']??null,'link'=>$bannerLinks['banner9_link']??null,'linkField'=>'banner9_link','routeUpload'=>'admin.banner9.upload','routeDelete'=>'admin.banner9.delete'],

            ['field'=>'banner10','group'=>'catalog','title'=>'Banner de topo padrão','position'=>'Páginas internas','description'=>'Fallback dos heróis de catálogo e marca.','dimensions'=>'1920 × 560 px','ratioLabel'=>'Área segura central','file'=>$banners['banner10']??null,'link'=>$bannerLinks['banner10_link']??null,'linkField'=>'banner10_link','routeUpload'=>'admin.banner10.upload','routeDelete'=>'admin.banner10.delete'],
            ['field'=>'banner_horizontal','group'=>'catalog','title'=>'Banner lateral padrão','position'=>'Filtros e listagens','description'=>'Campanha completa ao lado dos produtos no desktop e inserida na grade no mobile.','dimensions'=>'1200 × 675 px','ratioLabel'=>'Proporção 16:9','file'=>$bannerHorizontal??null,'link'=>$bannerLinks['banner_horizontal_link']??null,'linkField'=>'banner_horizontal_link','routeUpload'=>'admin.bannerhorizontal.upload','routeDelete'=>'admin.bannerhorizontal.delete'],

            ['field'=>'header_image','group'=>'identity','title'=>'Logo Header','dimensions'=>'800 × 320 px','ratioLabel'=>'Fundo transparente','file'=>$webpImage??null,'routeUpload'=>'admin.header.upload','routeDelete'=>'admin.header.delete'],
            ['field'=>'logo_palace','group'=>'identity','title'=>'Logo SAX Palace','dimensions'=>'800 × 320 px','ratioLabel'=>'Fundo transparente','file'=>$logoPalace??null,'routeUpload'=>'admin.logopalace.upload','routeDelete'=>'admin.logopalace.delete'],
            ['field'=>'logo_bridal','group'=>'identity','title'=>'Logo SAX Bridal','dimensions'=>'800 × 320 px','ratioLabel'=>'Fundo transparente','file'=>$logoBridal??null,'routeUpload'=>'admin.logobridal.upload','routeDelete'=>'admin.logobridal.delete'],
            ['field'=>'logo_cafe_bistro','group'=>'identity','title'=>'Logo Café & Bistrô PJC','dimensions'=>'800 × 320 px','ratioLabel'=>'Fundo transparente','file'=>$logoCafeBistro??null,'routeUpload'=>'admin.logocafebistro.upload','routeDelete'=>'admin.logocafebistro.delete'],
            ['field'=>'logo_cafe_bistro_asuncion','group'=>'identity','title'=>'Logo Café & Bistrô Assunção','dimensions'=>'800 × 320 px','ratioLabel'=>'Fundo transparente','file'=>$logoCafeBistroAsuncion??null,'routeUpload'=>'admin.logocafebistroasuncion.upload','routeDelete'=>'admin.logocafebistroasuncion.delete'],

            ['field'=>'icon_info','group'=>'system','title'=>'Ícone Info/Relógio','dimensions'=>'256 × 256 px','ratioLabel'=>'Quadrado','file'=>$attribute->icon_info??null,'routeUpload'=>'admin.icon_info.upload','routeDelete'=>'admin.icon_info.delete'],
            ['field'=>'icon_cabide','group'=>'system','title'=>'Ícone Cabide','dimensions'=>'256 × 256 px','ratioLabel'=>'Quadrado','file'=>$attribute->icon_cabide??null,'routeUpload'=>'admin.icon_cabide.upload','routeDelete'=>'admin.icon_cabide.delete'],
            ['field'=>'icon_help','group'=>'system','title'=>'Ícone Ajuda','dimensions'=>'256 × 256 px','ratioLabel'=>'Quadrado','file'=>$attribute->icon_help??null,'routeUpload'=>'admin.icon_help.upload','routeDelete'=>'admin.icon_help.delete'],
            ['field'=>'noimage','group'=>'system','title'=>'Imagem indisponível','dimensions'=>'1000 × 1250 px','ratioLabel'=>'Proporção 4:5','file'=>$noimage??null,'routeUpload'=>'admin.noimage.upload','routeDelete'=>'admin.noimage.delete'],
            ['field'=>'whatsapp_banner','group'=>'system','title'=>'Ícone WhatsApp','dimensions'=>'256 × 256 px','ratioLabel'=>'Quadrado','file'=>$banners['whatsapp_banner']??null,'routeUpload'=>'admin.whatsapp_banner.upload','routeDelete'=>'admin.whatsapp_banner.delete'],
        ];

        $catalogLinks = [
            ['label'=>'Banners de categorias','route'=>'admin.categories.index','icon'=>'fa-folder'],
            ['label'=>'Banners de subcategorias','route'=>'admin.subcategories.index','icon'=>'fa-folder-tree'],
            ['label'=>'Banners de categorias-filhas','route'=>'admin.categorias-filhas.index','icon'=>'fa-sitemap'],
            ['label'=>'Banners de marcas','route'=>'admin.brands.index','icon'=>'fa-tags'],
        ];
    @endphp

    <nav class="banner-section-nav" aria-label="Seções da central de mídia">
        @foreach($groups as $key => $group)
            <a href="#banner-group-{{ $key }}"><i class="fa-solid {{ $group['icon'] }}"></i>{{ $group['label'] }}</a>
        @endforeach
    </nav>

    @foreach($groups as $key => $group)
        <section class="banner-admin-section" id="banner-group-{{ $key }}">
            <div class="banner-admin-section__heading">
                <div class="banner-admin-section__icon"><i class="fa-solid {{ $group['icon'] }}"></i></div>
                <div><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h2>{{ $group['label'] }}</h2><p>{{ $group['description'] }}</p></div>
            </div>

            @if($key === 'catalog')
                <div class="banner-catalog-links">
                    @foreach($catalogLinks as $item)
                        <a href="{{ route($item['route']) }}"><i class="fa-solid {{ $item['icon'] }}"></i><span>{{ $item['label'] }}</span><i class="fa-solid fa-arrow-right"></i></a>
                    @endforeach
                </div>
            @endif

            @if($key === 'home-slider')
                @include('admin.partials.home_banner_manager', [
                    'managerGroup' => 'main',
                    'managerItems' => $homeBannerGroups->get('main', collect()),
                    'managerTitle' => 'Galeria do slider principal',
                    'managerDescription' => 'Selecione várias campanhas de uma vez. Depois, defina o link de cada slide e arraste os cartões para mudar a ordem.',
                    'managerDimensions' => '1920 × 720 px',
                    'managerRatio' => '8:3',
                ])
            @elseif($key === 'home-editorial')
                @include('admin.partials.home_banner_manager', [
                    'managerGroup' => 'editorial',
                    'managerItems' => $homeBannerGroups->get('editorial', collect()),
                    'managerTitle' => 'Galeria de destaques da home',
                    'managerDescription' => 'Envie vários destaques juntos, associe um destino a cada imagem e organize a sequência do segundo carrossel.',
                    'managerDimensions' => '1600 × 760 px',
                    'managerRatio' => '2.1:1',
                ])
            @endif

            <div class="row g-4">
                @foreach($images as $img)
                    @if($img['group'] === $key)
                        @include('admin.partials.banner_card', ['img' => $img])
                    @endif
                @endforeach
            </div>
        </section>
    @endforeach
</x-admin.card>
@endsection

@push('scripts')
    <script src="{{ asset('js/home-banner-admin.js') }}?v={{ filemtime(public_path('js/home-banner-admin.js')) }}"></script>
@endpush
