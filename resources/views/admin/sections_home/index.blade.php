@extends('layout.admin')

@section('content')
@push('styles')
    <link href="{{ asset('css/home-sections-admin.css') }}?v={{ filemtime(public_path('css/home-sections-admin.css')) }}" rel="stylesheet">
@endpush

<x-admin.card>
    <x-admin.page-header
        title="Seções da Home"
        description="Ative, desative e organize todos os blocos exibidos na página inicial.">
        <x-slot:actions>
            <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-outline-dark px-3 text-uppercase fw-bold x-small tracking-wider">
                <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>Ver Home
            </a>
            <button type="submit" form="sectionsForm" class="btn btn-dark px-4 text-uppercase fw-bold x-small tracking-wider">
                <i class="fa-solid fa-floppy-disk me-2"></i>{{ __('messages.guardar_configuracao_btn') }}
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <div class="home-sections-layout">
        <form action="{{ route('admin.sections_home.update') }}" method="POST" id="sectionsForm">
            @csrf
            @method('PATCH')

            <section class="storefront-layout-picker" aria-labelledby="storefront-layout-title">
                <div class="storefront-layout-picker__heading">
                    <div><strong id="storefront-layout-title">Layout do site</strong><span>Define o cabeçalho, a Home, o rodapé e a linguagem visual do front.</span></div>
                    <span class="home-sections-count">Por instalação</span>
                </div>
                <div class="storefront-layout-options">
                    @foreach($layouts as $layoutKey => $layout)
                        <label class="storefront-layout-option">
                            <input type="radio" name="storefront_layout" value="{{ $layoutKey }}"
                                   @checked((count($layouts) === 1 ? $selectedLayout : old('storefront_layout', $selectedLayout)) === $layoutKey)>
                            <span class="storefront-layout-option__visual storefront-layout-option__visual--{{ $layoutKey }}"><i class="fa-solid {{ $layout['icon'] }}"></i><b>{{ $layout['label'] }}</b></span>
                            <span class="storefront-layout-option__copy"><strong>{{ $layout['label'] }}</strong><small>{{ $layout['description'] }}</small></span>
                            <i class="fa-solid fa-circle-check storefront-layout-option__check"></i>
                        </label>
                    @endforeach
                </div>
            </section>

            <div class="home-sections-toolbar">
                <div>
                    <strong>Estrutura da página inicial</strong>
                    <span>Arraste os itens ou use as setas para alterar a posição.</span>
                </div>
                <span class="home-sections-count">{{ count($sections) }} seções</span>
            </div>

            <div class="home-sections-list" data-home-sections-list>
                @foreach($sections as $key => $section)
                    <article class="home-section-item" draggable="true" data-home-section data-section-key="{{ $key }}">
                        <input type="hidden" name="sections[{{ $key }}][key]" value="{{ $key }}">
                        <input type="hidden" name="sections[{{ $key }}][position]" value="{{ $section['position'] }}" data-section-position>
                        <input type="hidden" name="sections[{{ $key }}][enabled]" value="0">

                        <button type="button" class="home-section-drag" aria-label="Arrastar {{ $section['label'] }}" title="Arraste para reorganizar">
                            <i class="fa-solid fa-grip-vertical"></i>
                        </button>
                        <span class="home-section-position" data-section-number>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="home-section-icon"><i class="fa-solid {{ $section['icon'] }}"></i></span>

                        <div class="home-section-copy">
                            <label for="section-{{ $key }}">{{ $section['label'] }}</label>
                            <span>{{ $section['description'] }}</span>
                        </div>

                        <div class="home-section-actions">
                            <button type="button" class="home-section-move" data-move="up" aria-label="Mover {{ $section['label'] }} para cima"><i class="fa-solid fa-arrow-up"></i></button>
                            <button type="button" class="home-section-move" data-move="down" aria-label="Mover {{ $section['label'] }} para baixo"><i class="fa-solid fa-arrow-down"></i></button>
                            <label class="home-section-switch" for="section-{{ $key }}">
                                <span data-switch-label>{{ $section['enabled'] ? 'Ativa' : 'Inativa' }}</span>
                                <input class="form-check-input" type="checkbox"
                                       name="sections[{{ $key }}][enabled]" id="section-{{ $key }}" value="1"
                                       @checked($section['enabled']) data-section-toggle>
                            </label>
                        </div>

                        @if($key === 'main_slider')
                            @foreach(['pt', 'en', 'es'] as $language)
                                <input type="hidden" name="sections[{{ $key }}][content][{{ $language }}][title]" value="{{ $section['content'][$language]['title'] }}">
                                <input type="hidden" name="sections[{{ $key }}][content][{{ $language }}][description]" value="{{ $section['content'][$language]['description'] }}">
                            @endforeach
                            <div class="home-section-content home-section-content--notice">
                                <span><i class="fa-solid fa-circle-info me-2"></i>Os textos agora são configurados individualmente em cada imagem.</span>
                                <a href="{{ route('admin.banners.index') }}">Editar slides <i class="fa-solid fa-arrow-right ms-1"></i></a>
                            </div>
                        @else
                        <details class="home-section-content">
                            <summary>
                                <span><i class="fa-solid fa-language me-2"></i>Editar títulos e descrições</span>
                                <i class="fa-solid fa-chevron-down home-section-content__chevron"></i>
                            </summary>

                            @if($key === 'categories')
                                <div class="home-section-option-row">
                                    <div><strong>Quantidade automática</strong><span>Usada somente quando nenhuma categoria específica for selecionada abaixo.</span></div>
                                    <select class="form-select" name="sections[{{ $key }}][category_limit]" aria-label="Quantidade de categorias">
                                        @foreach(['1' => '1 categoria', '2' => '2 categorias', '3' => '3 categorias', '4' => '4 categorias', 'all' => 'Todas as categorias'] as $value => $label)
                                            <option value="{{ $value }}" @selected((string) old("sections.{$key}.category_limit", $section['category_limit'] ?? 'all') === (string) $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                @include('admin.sections_home.optical-item-picker', [
                                    'title' => 'Categorias do bloco superior',
                                    'description' => 'Marque exatamente quais cards devem aparecer no primeiro bloco de categorias da Home.',
                                ])
                            @endif

                            @if($key === 'exclusive_collection')
                                @include('admin.sections_home.optical-item-picker', [
                                    'title' => 'Categorias dos cards grandes',
                                    'description' => 'Escolha os cards com imagem que antes apareciam automaticamente como mulher, homem e crianças.',
                                ])
                            @endif

                            <div class="home-section-languages">
                                @foreach([
                                    'pt' => ['label' => 'Português', 'flag' => '🇧🇷'],
                                    'en' => ['label' => 'English', 'flag' => '🇺🇸'],
                                    'es' => ['label' => 'Español', 'flag' => '🇪🇸'],
                                ] as $language => $languageData)
                                    <fieldset class="home-section-language">
                                        <legend><span>{{ $languageData['flag'] }}</span>{{ $languageData['label'] }}</legend>
                                        <label for="section-{{ $key }}-{{ $language }}-title">Título</label>
                                        <input id="section-{{ $key }}-{{ $language }}-title" type="text" class="form-control"
                                               name="sections[{{ $key }}][content][{{ $language }}][title]"
                                               value="{{ old("sections.{$key}.content.{$language}.title", $section['content'][$language]['title']) }}"
                                               maxlength="160">

                                        <label for="section-{{ $key }}-{{ $language }}-description">Descrição</label>
                                        <textarea id="section-{{ $key }}-{{ $language }}-description" class="form-control"
                                                  name="sections[{{ $key }}][content][{{ $language }}][description]"
                                                  rows="3" maxlength="600">{{ old("sections.{$key}.content.{$language}.description", $section['content'][$language]['description']) }}</textarea>
                                    </fieldset>
                                @endforeach
                            </div>

                            @if($key === 'help')
                                <div class="home-benefit-editor">
                                    <div class="home-benefit-editor__heading"><strong>Textos ao lado dos três ícones</strong><span>Os ícones continuam sendo alterados em Banners e Identidade Visual.</span></div>
                                    @foreach($section['items'] as $itemIndex => $item)
                                        <fieldset class="home-benefit-item">
                                            <legend>Ícone {{ $itemIndex + 1 }}</legend>
                                            <div>
                                                @foreach(['pt' => 'Português', 'en' => 'English', 'es' => 'Español'] as $language => $languageLabel)
                                                    <label for="help-item-{{ $itemIndex }}-{{ $language }}">{{ $languageLabel }}</label>
                                                    <input id="help-item-{{ $itemIndex }}-{{ $language }}" class="form-control" type="text"
                                                        name="sections[{{ $key }}][items][{{ $itemIndex }}][{{ $language }}]"
                                                        value="{{ old("sections.{$key}.items.{$itemIndex}.{$language}", $item[$language]) }}" maxlength="100">
                                                @endforeach
                                            </div>
                                        </fieldset>
                                    @endforeach
                                </div>
                            @endif
                        </details>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="home-sections-footer">
                <span><i class="fa-solid fa-circle-info me-2"></i>As alterações aparecem na Home após salvar.</span>
                <button type="submit" class="btn btn-dark px-4"><i class="fa-solid fa-floppy-disk me-2"></i>Salvar seções</button>
            </div>
        </form>

        @php
            $homePreviewData = [
                'saxCategories' => $saxCategories,
                'opticalItems' => $opticalItems->map(fn (array $item): array => [
                    'key' => $item['type'].':'.$item['id'],
                    'type' => $item['type'],
                    'label' => $item['label'],
                    'photo' => $item['photo'],
                    'banner' => $item['banner'],
                ])->values(),
            ];
        @endphp
        <aside class="home-live-preview" id="homeLivePreview" data-locale="{{ app()->getLocale() }}">
            <script type="application/json" id="homePreviewData">@json($homePreviewData)</script>

            <div class="home-live-preview__bar">
                <span class="home-live-preview__dots"><i></i><i></i><i></i></span>
                <strong>Prévia instantânea</strong>
                <span class="home-preview-state" data-preview-state><i class="fa-solid fa-circle-check"></i> Sincronizada</span>
            </div>

            <div class="home-preview-toolbar">
                <div class="home-preview-devices" role="group" aria-label="Tamanho da prévia">
                    <button type="button" class="is-active" data-preview-device="desktop" aria-label="Prévia desktop" title="Desktop"><i class="fa-solid fa-desktop"></i></button>
                    <button type="button" data-preview-device="mobile" aria-label="Prévia celular" title="Celular"><i class="fa-solid fa-mobile-screen-button"></i></button>
                </div>
                <label>
                    <span>Idioma</span>
                    <select data-preview-language aria-label="Idioma da prévia">
                        <option value="pt">PT</option>
                        <option value="es">ES</option>
                        <option value="en">EN</option>
                    </select>
                </label>
                <a href="{{ url('/') }}" target="_blank" rel="noopener" title="Abrir Home completa"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
            </div>

            <div class="home-preview-stage">
                <div class="home-preview-page" data-preview-page>
                    <header class="home-preview-header">
                        <strong data-preview-brand>SAX</strong>
                        <span></span><span></span><span></span>
                    </header>
                    <div class="home-preview-sections" data-preview-sections></div>
                    <footer class="home-preview-footer"><strong>SAX</strong><span>Atendimento · Contato · Redes sociais</span></footer>
                </div>
            </div>

            <div class="home-preview-summary">
                <span><strong data-preview-active-count>0</strong> seções ativas</span>
                <span><strong data-preview-category-count>0</strong> categorias no topo</span>
            </div>
            <div class="home-preview-tip" data-preview-tip>
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <p><strong>Dica de composição</strong><span>A prévia será atualizada conforme você editar.</span></p>
            </div>
            <p class="home-live-preview__note"><i class="fa-solid fa-hand-pointer"></i> Clique em um bloco da prévia para abrir sua configuração.</p>
        </aside>
    </div>
</x-admin.card>

<style>
    .storefront-layout-picker{margin-bottom:1.4rem;padding:1rem;border:1px solid #dfe5ed;border-radius:14px;background:#fff}.storefront-layout-picker__heading{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:.85rem}.storefront-layout-picker__heading strong,.storefront-layout-picker__heading span{display:block}.storefront-layout-picker__heading strong{color:#172033;font-size:.82rem}.storefront-layout-picker__heading div>span{margin-top:.2rem;color:#667085;font-size:.72rem}.storefront-layout-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}.storefront-layout-option{position:relative;display:grid;grid-template-columns:92px 1fr auto;align-items:center;gap:.8rem;padding:.7rem;border:1px solid #dfe5ed;border-radius:11px;cursor:pointer;transition:.2s}.storefront-layout-option:hover{border-color:#98a2b3}.storefront-layout-option:has(input:checked){border-color:#172033;box-shadow:0 0 0 1px #172033}.storefront-layout-option>input{position:absolute;opacity:0;pointer-events:none}.storefront-layout-option__visual{display:grid;height:56px;place-items:center;grid-template-columns:auto auto;gap:.35rem;border-radius:7px;background:#171717;color:#fff;font-size:.7rem;letter-spacing:.04em}.storefront-layout-option__visual--vista{background:#f2f2f2;color:#111}.storefront-layout-option__copy strong,.storefront-layout-option__copy small{display:block}.storefront-layout-option__copy strong{font-size:.76rem;color:#172033}.storefront-layout-option__copy small{margin-top:.2rem;color:#667085;font-size:.68rem;line-height:1.35}.storefront-layout-option__check{color:#172033;opacity:0}.storefront-layout-option:has(input:checked) .storefront-layout-option__check{opacity:1}
    .home-sections-layout { display:grid; grid-template-columns:minmax(0,1fr) minmax(300px,360px); gap:1rem; align-items:start; }
    .home-sections-toolbar { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1rem; padding:.9rem 1rem; border:1px solid #e2e7ef; border-radius:12px; background:#f8fafc; }
    .home-sections-toolbar strong,.home-sections-toolbar span { display:block; }
    .home-sections-toolbar strong { color:#172033; font-size:.82rem; }
    .home-sections-toolbar div > span { margin-top:.2rem; color:#667085; font-size:.72rem; }
    .home-sections-count { flex:0 0 auto; padding:.35rem .65rem; border-radius:999px; background:#e9eef5; color:#475467; font-size:.68rem; font-weight:800; text-transform:uppercase; }
    .home-sections-list { display:grid; gap:.65rem; }
    .home-section-item { display:grid; grid-template-columns:24px 34px 44px minmax(0,1fr) auto; min-height:82px; padding:.85rem 1rem; align-items:center; gap:.8rem; border:1px solid #dfe5ed; border-radius:12px; background:#fff; transition:border-color .2s,box-shadow .2s,opacity .2s,transform .2s; }
    .home-section-item:hover { border-color:#bac5d3; box-shadow:0 8px 24px rgba(16,24,40,.06); }
    .home-section-item.is-dragging { opacity:.45; transform:scale(.99); }
    .home-section-item.is-disabled { background:#f8fafc; opacity:.72; }
    .home-section-drag,.home-section-move { display:inline-grid; padding:0; place-items:center; border:0; background:transparent; color:#98a2b3; }
    .home-section-drag { height:34px; cursor:grab; }
    .home-section-drag:active { cursor:grabbing; }
    .home-section-position { color:#98a2b3; font-size:.68rem; font-weight:800; letter-spacing:.08em; }
    .home-section-icon { display:inline-grid; width:44px; height:44px; place-items:center; border-radius:10px; background:#f1f5f9; color:#344054; }
    .home-section-copy { min-width:0; }
    .home-section-copy label,.home-section-copy span { display:block; }
    .home-section-copy label { margin-bottom:.25rem; color:#172033; font-size:.76rem; font-weight:800; text-transform:uppercase; cursor:pointer; }
    .home-section-copy span { color:#667085; font-size:.7rem; line-height:1.4; }
    .home-section-actions { display:flex; align-items:center; gap:.45rem; }
    .home-section-move { width:32px; height:32px; border:1px solid #e1e6ee; border-radius:8px; cursor:pointer; }
    .home-section-move:hover:not(:disabled) { border-color:#172033; color:#172033; }
    .home-section-move:disabled { opacity:.3; cursor:not-allowed; }
    .home-section-switch { display:flex; min-width:105px; margin-left:.35rem; padding:.55rem .7rem; align-items:center; justify-content:space-between; gap:.65rem; border:1px solid #e1e6ee; border-radius:9px; background:#f8fafc; cursor:pointer; }
    .home-section-switch span { color:#475467; font-size:.65rem; font-weight:800; text-transform:uppercase; }
    .home-section-content { grid-column:1/-1; overflow:hidden; border:1px solid #e1e6ee; border-radius:10px; background:#f8fafc; }
    .home-section-content summary { display:flex; padding:.75rem .9rem; align-items:center; justify-content:space-between; color:#344054; font-size:.7rem; font-weight:800; text-transform:uppercase; cursor:pointer; list-style:none; }
    .home-section-content summary::-webkit-details-marker { display:none; }
    .home-section-content__chevron { transition:transform .2s ease; }
    .home-section-content[open] .home-section-content__chevron { transform:rotate(180deg); }
    .home-section-option-row { display:flex; margin:0 .8rem .8rem; padding:.8rem; align-items:center; justify-content:space-between; gap:1rem; border:1px solid #e3e8ef; border-radius:9px; background:#fff; }
    .home-section-option-row strong,.home-section-option-row span { display:block; }
    .home-section-option-row strong { color:#344054; font-size:.72rem; }
    .home-section-option-row span { margin-top:.2rem; color:#667085; font-size:.67rem; }
    .home-section-option-row .form-select { width:210px; font-size:.74rem; }
    .home-optical-picker { margin:0 .8rem .8rem; padding:.9rem; border:1px solid #dfe5ed; border-radius:10px; background:#fff; }
    .home-optical-picker__heading { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; }
    .home-optical-picker__heading strong,.home-optical-picker__heading small { display:block; }
    .home-optical-picker__heading strong { margin-top:.35rem; color:#172033; font-size:.78rem; }
    .home-optical-picker__heading small { margin-top:.2rem; color:#667085; font-size:.68rem; line-height:1.45; }
    .home-optical-picker__badge { display:inline-flex; align-items:center; gap:.35rem; padding:.25rem .5rem; border-radius:999px; background:#edf2f7; color:#344054; font-size:.58rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
    .home-optical-picker__counter { flex:0 0 auto; padding:.3rem .55rem; border-radius:999px; background:#172033; color:#fff; font-size:.62rem; font-weight:800; }
    .home-optical-picker__search { position:relative; display:block; margin-top:.8rem; }
    .home-optical-picker__search i { position:absolute; top:50%; left:.8rem; z-index:1; color:#98a2b3; transform:translateY(-50%); }
    .home-optical-picker__search input { padding-left:2.25rem; font-size:.72rem; }
    .home-optical-picker__items { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.5rem; max-height:290px; margin-top:.7rem; padding:.15rem; overflow:auto; }
    .home-optical-option { display:grid; grid-template-columns:24px minmax(0,1fr); align-items:center; gap:.55rem; min-width:0; padding:.65rem; border:1px solid #e3e8ef; border-radius:8px; cursor:pointer; transition:.15s; }
    .home-optical-option:hover { border-color:#98a2b3; }
    .home-optical-option:has(input:checked) { border-color:#172033; background:#f8fafc; box-shadow:0 0 0 1px #172033; }
    .home-optical-option[hidden] { display:none; }
    .home-optical-option input { position:absolute; opacity:0; pointer-events:none; }
    .home-optical-option__check { display:grid; width:22px; height:22px; place-items:center; border:1px solid #cfd6df; border-radius:6px; color:transparent; font-size:.65rem; }
    .home-optical-option:has(input:checked) .home-optical-option__check { border-color:#172033; background:#172033; color:#fff; }
    .home-optical-option__copy { min-width:0; }
    .home-optical-option__copy strong,.home-optical-option__copy small { display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .home-optical-option__copy strong { color:#344054; font-size:.7rem; }
    .home-optical-option__copy small { margin-top:.1rem; color:#98a2b3; font-size:.58rem; text-transform:uppercase; }
    .home-optical-picker__footer { display:flex; margin-top:.7rem; align-items:center; justify-content:space-between; gap:1rem; color:#667085; font-size:.63rem; }
    .home-optical-picker__footer button { padding:0; border:0; background:transparent; color:#344054; font-size:.63rem; font-weight:800; text-decoration:underline; }
    .home-section-content--notice { display:flex; padding:.75rem .9rem; align-items:center; justify-content:space-between; gap:1rem; color:#526078; font-size:.7rem; }
    .home-section-content--notice a { flex:0 0 auto; color:#172033; font-weight:800; text-decoration:none; text-transform:uppercase; }
    .home-section-languages { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.8rem; padding:0 .8rem .8rem; }
    .home-section-language { min-width:0; margin:0; padding:.85rem; border:1px solid #e3e8ef; border-radius:9px; background:#fff; }
    .home-section-language legend { float:none; width:auto; margin:0 0 .7rem; padding:0; color:#172033; font-size:.72rem; font-weight:800; }
    .home-section-language legend span { margin-right:.4rem; }
    .home-section-language label { display:block; margin:.65rem 0 .35rem; color:#526078; font-size:.62rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
    .home-section-language label:first-of-type { margin-top:0; }
    .home-section-language .form-control { font-size:.76rem; }
    .home-section-language textarea.form-control { min-height:82px; resize:vertical; }
    .home-benefit-editor { margin:0 .8rem .8rem; padding:.9rem; border:1px solid #e3e8ef; border-radius:9px; background:#fff; }
    .home-benefit-editor__heading strong,.home-benefit-editor__heading span { display:block; }
    .home-benefit-editor__heading strong { color:#344054; font-size:.74rem; }
    .home-benefit-editor__heading span { margin-top:.2rem; color:#667085; font-size:.68rem; }
    .home-benefit-item { margin:1rem 0 0; padding-top:.8rem; border:0; border-top:1px solid #e8ecf1; }
    .home-benefit-item legend { float:none; width:auto; margin:0 0 .55rem; color:#172033; font-size:.68rem; font-weight:800; text-transform:uppercase; }
    .home-benefit-item > div { display:grid; grid-template-columns:auto minmax(0,1fr) auto minmax(0,1fr) auto minmax(0,1fr); align-items:center; gap:.55rem; }
    .home-benefit-item label { margin:0; color:#667085; font-size:.62rem; font-weight:700; }
    .home-benefit-item .form-control { font-size:.72rem; }
    .home-sections-footer { display:flex; margin-top:1rem; padding-top:1rem; justify-content:space-between; align-items:center; gap:1rem; border-top:1px solid #e9edf2; }
    .home-sections-footer > span { color:#667085; font-size:.72rem; }
    .home-sections-kicker { color:#667085; font-size:.65rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
    .home-sections-guide { position:sticky; top:1rem; padding:1.35rem; border:1px solid #e1e6ee; border-radius:14px; background:#f8fafc; }
    .home-sections-guide h2 { margin:.25rem 0 .65rem; color:#172033; font-size:1rem; font-weight:800; }
    .home-sections-guide > p { margin:0 0 1rem; color:#667085; font-size:.75rem; line-height:1.55; }
    .home-sections-guide__item { display:flex; gap:.75rem; padding:.85rem 0; border-top:1px solid #e1e6ee; }
    .home-sections-guide__item > i { width:22px; margin-top:.15rem; color:#344054; text-align:center; }
    .home-sections-guide__item strong,.home-sections-guide__item span { display:block; }
    .home-sections-guide__item strong { color:#344054; font-size:.74rem; }
    .home-sections-guide__item span { margin-top:.15rem; color:#667085; font-size:.69rem; line-height:1.45; }
    .home-sections-guide__note { margin-top:.5rem; padding:.85rem; border-left:3px solid #172033; background:#fff; color:#667085; font-size:.69rem; line-height:1.5; }
    @media(max-width:1100px){.home-sections-layout{grid-template-columns:1fr}.home-sections-guide{position:static}.home-section-item{grid-template-columns:20px 30px 40px minmax(0,1fr) auto}.home-section-languages{grid-template-columns:1fr}}
    @media(max-width:700px){.storefront-layout-options{grid-template-columns:1fr}.home-section-item{grid-template-columns:20px 30px 40px minmax(0,1fr);padding:.8rem;gap:.6rem}.home-section-actions{grid-column:1/-1;justify-content:flex-end;padding-top:.7rem;border-top:1px solid #edf0f4}.home-section-switch{margin-left:auto}.home-section-option-row{align-items:stretch;flex-direction:column}.home-section-option-row .form-select{width:100%}.home-optical-picker__heading,.home-optical-picker__footer{align-items:stretch;flex-direction:column}.home-optical-picker__counter{align-self:flex-start}.home-optical-picker__items{grid-template-columns:1fr;max-height:360px}.home-benefit-item>div{grid-template-columns:1fr}.home-sections-footer{align-items:stretch;flex-direction:column}.home-sections-footer .btn{width:100%}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const list = document.querySelector('[data-home-sections-list]');
    if (!list) return;

    let dragged = null;
    const rows = () => Array.from(list.querySelectorAll('[data-home-section]'));
    const sync = () => {
        rows().forEach((row, index, allRows) => {
            row.querySelector('[data-section-position]').value = index + 1;
            row.querySelector('[data-section-number]').textContent = String(index + 1).padStart(2, '0');
            row.querySelector('[data-move="up"]').disabled = index === 0;
            row.querySelector('[data-move="down"]').disabled = index === allRows.length - 1;
            const toggle = row.querySelector('[data-section-toggle]');
            row.classList.toggle('is-disabled', !toggle.checked);
            row.querySelector('[data-switch-label]').textContent = toggle.checked ? 'Ativa' : 'Inativa';
        });
    };

    rows().forEach(row => {
        row.addEventListener('dragstart', event => {
            if (!event.target.closest('.home-section-drag')) {
                event.preventDefault();
                return;
            }
            dragged = row;
            row.classList.add('is-dragging');
            event.dataTransfer.effectAllowed = 'move';
        });
        row.addEventListener('dragend', () => {
            row.classList.remove('is-dragging');
            dragged = null;
            sync();
        });
        row.querySelectorAll('[data-move]').forEach(button => button.addEventListener('click', () => {
            const sibling = button.dataset.move === 'up' ? row.previousElementSibling : row.nextElementSibling;
            if (!sibling) return;
            if (button.dataset.move === 'up') list.insertBefore(row, sibling);
            else list.insertBefore(sibling, row);
            sync();
        }));
        row.querySelector('[data-section-toggle]').addEventListener('change', sync);
    });

    document.querySelectorAll('[data-optical-picker]').forEach(picker => {
        const checkboxes = Array.from(picker.querySelectorAll('input[type="checkbox"]'));
        const counter = picker.querySelector('[data-optical-count]');
        const search = picker.querySelector('[data-optical-search]');
        const options = Array.from(picker.querySelectorAll('[data-optical-option]'));
        const updateCount = () => {
            const count = checkboxes.filter(checkbox => checkbox.checked).length;
            counter.textContent = `${count} ${count === 1 ? 'selecionada' : 'selecionadas'}`;
        };
        checkboxes.forEach(checkbox => checkbox.addEventListener('change', updateCount));
        search.addEventListener('input', () => {
            const term = search.value.trim().toLocaleLowerCase();
            options.forEach(option => option.hidden = term !== '' && !option.dataset.searchText.includes(term));
        });
        picker.querySelector('[data-optical-clear]').addEventListener('click', () => {
            checkboxes.forEach(checkbox => checkbox.checked = false);
            updateCount();
        });
        updateCount();
    });

    list.addEventListener('dragover', event => {
        event.preventDefault();
        if (!dragged) return;
        const next = rows().filter(row => row !== dragged).find(row => {
            const rect = row.getBoundingClientRect();
            return event.clientY < rect.top + rect.height / 2;
        });
        list.insertBefore(dragged, next || null);
    });

    sync();
});
</script>

@push('scripts')
    <script src="{{ asset('js/home-sections-admin.js') }}?v={{ filemtime(public_path('js/home-sections-admin.js')) }}"></script>
@endpush
@endsection
