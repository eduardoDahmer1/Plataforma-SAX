@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.gestao_banners_titulo') }}"
        description="{{ __('messages.gestao_banners_desc') }}">
    </x-admin.page-header>

    {{-- Alertas --}}
    <x-admin.alert />

    {{-- <div class="card border-0 shadow-sm mb-5">
        <div class="card-body p-4">
            <div class="d-flex align-items-center mb-3">
                <div class="icon-shape bg-soft-primary text-primary rounded me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: #eef2ff;">
                    <i class="fas fa-font"></i>
                </div>
                <h5 class="mb-0 fw-bold">{{ __('messages.texto_informativo_topo') }}</h5>
            </div>
            
            <form action="{{ route('admin.attributes.update_text') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3 align-items-end">
                    <div class="col-md-9">
                        <label for="text_topo" class="form-label small text-uppercase fw-bold text-muted">{{ __('messages.conteudo_do_texto') }}</label>
                        <input type="text" 
                               name="text_topo" 
                               id="text_topo" 
                               class="form-control form-control-lg border-2" 
                               placeholder="Ex: Frete grátis..." 
                               value="{{ $attribute->text_topo ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">
                            <i class="fas fa-save me-2"></i> {{ __('messages.atualizar') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div> --}}

    @php
        // Definimos as categorias traduzidas para os labels
        $catIdentidade = __('messages.cat_identidade');
        $catHome = __('messages.cat_home');
        $catSistema = __('messages.cat_sistema');

        $images = [
            ['field' => 'header_image', 'title' => 'Logo Header', 'category' => $catIdentidade, 'file' => $webpImage ?? null, 'routeUpload' => 'admin.header.upload', 'routeDelete' => 'admin.header.delete'],
            ['field' => 'logo_palace', 'title' => 'Logo SAX Palace', 'category' => $catIdentidade, 'file' => $logoPalace ?? null, 'routeUpload' => 'admin.logopalace.upload', 'routeDelete' => 'admin.logopalace.delete'],
            ['field' => 'logo_bridal', 'title' => 'Logo SAX Bridal', 'category' => $catIdentidade, 'file' => $logoBridal ?? null, 'routeUpload' => 'admin.logobridal.upload', 'routeDelete' => 'admin.logobridal.delete'],
                ['field' => 'logo_cafe_bistro', 'title' => 'Logo SAX Café & Bistrô', 'category' => $catIdentidade, 'file' => $logoCafeBistro ?? null, 'routeUpload' => 'admin.logocafebistro.upload', 'routeDelete' => 'admin.logocafebistro.delete'],
                ['field' => 'banner_horizontal', 'title' => 'Banner Horizontal', 'category' => $catIdentidade, 'file' => $bannerHorizontal ?? null, 'routeUpload' => 'admin.bannerhorizontal.upload', 'routeDelete' => 'admin.bannerhorizontal.delete'],

                ['field' => 'icon_info', 'title' => 'Ícone Info/Relógio', 'category' => $catSistema, 'file' => $attribute->icon_info ?? null, 'routeUpload' => 'admin.icon_info.upload', 'routeDelete' => 'admin.icon_info.delete'],
                ['field' => 'icon_cabide', 'title' => 'Ícone Cabide', 'category' => $catSistema, 'file' => $attribute->icon_cabide ?? null, 'routeUpload' => 'admin.icon_cabide.upload', 'routeDelete' => 'admin.icon_cabide.delete'],
                ['field' => 'icon_help', 'title' => 'Ícone Ajuda', 'category' => $catSistema, 'file' => $attribute->icon_help ?? null, 'routeUpload' => 'admin.icon_help.upload', 'routeDelete' => 'admin.icon_help.delete'],
                ['field' => 'noimage', 'title' => 'Noimage Default', 'category' => $catSistema, 'file' => $noimage ?? null, 'routeUpload' => 'admin.noimage.upload', 'routeDelete' => 'admin.noimage.delete'],

                ['field' => 'banner1', 'title' => 'Slider Home 01', 'category' => $catHome, 'file' => $banners['banner1'] ?? null, 'link' => $bannerLinks['banner1_link'] ?? null, 'linkField' => 'banner1_link', 'routeUpload' => 'admin.banner1.upload', 'routeDelete' => 'admin.banner1.delete'],
                ['field' => 'banner2', 'title' => 'Slider Home 02', 'category' => $catHome, 'file' => $banners['banner2'] ?? null, 'link' => $bannerLinks['banner2_link'] ?? null, 'linkField' => 'banner2_link', 'routeUpload' => 'admin.banner2.upload', 'routeDelete' => 'admin.banner2.delete'],
                ['field' => 'banner3', 'title' => 'Slider Home 03', 'category' => $catHome, 'file' => $banners['banner3'] ?? null, 'link' => $bannerLinks['banner3_link'] ?? null, 'linkField' => 'banner3_link', 'routeUpload' => 'admin.banner3.upload', 'routeDelete' => 'admin.banner3.delete'],
                ['field' => 'banner4', 'title' => 'Slider Home 04', 'category' => $catHome, 'file' => $banners['banner4'] ?? null, 'link' => $bannerLinks['banner4_link'] ?? null, 'linkField' => 'banner4_link', 'routeUpload' => 'admin.banner4.upload', 'routeDelete' => 'admin.banner4.delete'],
                ['field' => 'banner5', 'title' => 'Slider Home 05', 'category' => $catHome, 'file' => $banners['banner5'] ?? null, 'link' => $bannerLinks['banner5_link'] ?? null, 'linkField' => 'banner5_link', 'routeUpload' => 'admin.banner5.upload', 'routeDelete' => 'admin.banner5.delete'],
                ['field' => 'banner6', 'title' => 'Banner Principal 06', 'category' => $catHome, 'file' => $banners['banner6'] ?? null, 'link' => $bannerLinks['banner6_link'] ?? null, 'linkField' => 'banner6_link', 'routeUpload' => 'admin.banner6.upload', 'routeDelete' => 'admin.banner6.delete'],
                ['field' => 'banner7', 'title' => 'Banner Principal 07', 'category' => $catHome, 'file' => $banners['banner7'] ?? null, 'link' => $bannerLinks['banner7_link'] ?? null, 'linkField' => 'banner7_link', 'routeUpload' => 'admin.banner7.upload', 'routeDelete' => 'admin.banner7.delete'],
                ['field' => 'banner8', 'title' => 'Banner Principal 08', 'category' => $catHome, 'file' => $banners['banner8'] ?? null, 'link' => $bannerLinks['banner8_link'] ?? null, 'linkField' => 'banner8_link', 'routeUpload' => 'admin.banner8.upload', 'routeDelete' => 'admin.banner8.delete'],
                ['field' => 'banner9', 'title' => 'Banner Principal 09', 'category' => $catHome, 'file' => $banners['banner9'] ?? null, 'link' => $bannerLinks['banner9_link'] ?? null, 'linkField' => 'banner9_link', 'routeUpload' => 'admin.banner9.upload', 'routeDelete' => 'admin.banner9.delete'],
                ['field' => 'banner10', 'title' => 'Banners Internas', 'category' => $catHome, 'file' => $banners['banner10'] ?? null, 'link' => $bannerLinks['banner10_link'] ?? null, 'linkField' => 'banner10_link', 'routeUpload' => 'admin.banner10.upload', 'routeDelete' => 'admin.banner10.delete'],
                ['field' => 'whatsapp_banner', 'title' => 'Banner WhatsApp', 'category' => $catSistema, 'file' => $banners['whatsapp_banner'] ?? null, 'routeUpload' => 'admin.whatsapp_banner.upload', 'routeDelete' => 'admin.whatsapp_banner.delete'],
        ];
        
        $categories = [$catIdentidade, $catHome, $catSistema];
    @endphp

    <ul class="nav nav-pills-custom mb-4" id="bannerTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#all">{{ __('messages.todos') }}</button>
        </li>
        @foreach($categories as $cat)
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#cat-{{ Str::slug($cat) }}">{{ $cat }}</button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="all">
            <div class="row g-4">
                @foreach($images as $img)
                    @include('admin.partials.banner_card', ['img' => $img])
                @endforeach
            </div>
        </div>

        @foreach($categories as $cat)
            <div class="tab-pane fade" id="cat-{{ Str::slug($cat) }}">
                <div class="row g-4">
                    @foreach($images as $img)
                        @if($img['category'] == $cat)
                            @include('admin.partials.banner_card', ['img' => $img])
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-admin.card>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.upload-form').forEach(function (form) {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                const card = form.closest('.banner-admin-card');
                const btn = form.querySelector('.btn-submit');
                const img = card.querySelector('.banner-preview-img');
                const empty = card.querySelector('.empty-state');
                const delBtn = card.querySelector('.btn-delete');

                btn.disabled = true;

                const res = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form)
                });

                const data = await res.json();

                if (data.success) {
                    img.src = data.url;
                    img.style.display = 'block';
                    empty.style.display = 'none';
                    delBtn.style.display = 'block';
                }

                btn.disabled = false;
            });
        });

        document.querySelectorAll('.btn-delete').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                if (!confirm('{{ __('messages.confirmar_exclusao_imagem') }}')) {
                    return;
                }

                const form = btn.closest('.delete-form');
                const card = form.closest('.banner-admin-card');
                const img = card.querySelector('.banner-preview-img');
                const empty = card.querySelector('.empty-state');

                const res = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form)
                });

                const data = await res.json();

                if (data.success) {
                    img.style.display = 'none';
                    empty.style.display = 'block';
                    btn.style.display = 'none';
                }
            });
        });

        document.querySelectorAll('.banner-link-form').forEach(function (form) {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();

                const card = form.closest('.banner-admin-card');
                const input = form.querySelector('.banner-link-input');
                const btn = form.querySelector('.btn-link-submit');
                const activeLink = card.querySelector('.banner-active-link');
                const originalButtonHtml = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Salvando...';

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(form)
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        const firstError = data?.errors ? Object.values(data.errors)[0]?.[0] : null;
                        throw new Error(firstError || data?.message || 'Erro ao salvar o link.');
                    }

                    const currentValue = (input.value || '').trim();

                    if (activeLink) {
                        if (currentValue) {
                            activeLink.href = currentValue;
                            activeLink.classList.remove('d-none');
                        } else {
                            activeLink.href = '#';
                            activeLink.classList.add('d-none');
                        }
                    }

                    btn.innerHTML = '<i class="fas fa-check me-1"></i>Salvo';
                } catch (error) {
                    alert(error.message || 'Nao foi possivel salvar o link agora.');
                    btn.innerHTML = '<i class="fas fa-triangle-exclamation me-1"></i>Tentar novamente';
                } finally {
                    setTimeout(function () {
                        btn.disabled = false;
                        btn.innerHTML = originalButtonHtml;
                    }, 900);
                }
            });
        });
    });
</script>
@endpush