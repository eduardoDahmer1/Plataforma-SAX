@extends('layout.layout')

@section('content')
    <main class="sax-guide">
        <section class="sax-guide-hero">
            <div class="container">
                <span class="sax-guide-eyebrow">{{ __($isOtica ? 'messages.contact_guide_eyebrow_optical' : 'messages.contact_guide_eyebrow_general') }}</span>
                <h1>{{ __($isOtica ? 'messages.contact_guide_title_optical' : 'messages.contact_guide_title_general') }}</h1>
                <p>
                    {{ __($isOtica ? 'messages.contact_guide_description_optical' : 'messages.contact_guide_description_general') }}
                </p>

                <div class="sax-guide-stats" aria-label="Resumo do guia">
                    <div><strong>{{ $locations->count() }}</strong><span>{{ __('messages.contact_guide_units') }}</span></div>
                    <div><strong>{{ $sectorCount }}</strong><span>{{ __('messages.contact_guide_sectors') }}</span></div>
                    @unless($isOtica)<div><strong>{{ $brandCount }}</strong><span>{{ __('messages.contact_guide_brands') }}</span></div>@endunless
                </div>
            </div>
        </section>

        <section class="container sax-guide-body">
            <div class="sax-guide-toolbar">
                <div class="sax-guide-search">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input id="guideSearch" type="search" placeholder="{{ __('messages.contact_guide_search_placeholder') }}" aria-label="{{ __('messages.contact_guide_search_placeholder') }}">
                </div>
                <div class="sax-guide-city-filters" role="group" aria-label="{{ __('messages.contact_guide_filter_cities') }}">
                    <button type="button" class="is-active" data-guide-city="all">{{ __('messages.contact_guide_all_cities') }}</button>
                    @foreach($locations->unique(fn($location) => $location->translated('city')) as $filterLocation)
                        <button type="button" data-guide-city="{{ Str::slug($filterLocation->translated('city')) }}">{{ $filterLocation->translated('city') }}</button>
                    @endforeach
                </div>
            </div>

            <div class="sax-guide-locations" id="guideLocations">
                @foreach($locations as $location)
                    <article class="sax-guide-location" data-guide-location data-city="{{ Str::slug($location->translated('city')) }}">
                        <header class="sax-guide-location-head">
                            <div class="sax-guide-location-mark"><i class="fa-solid {{ $isOtica ? 'fa-glasses' : 'fa-location-dot' }}"></i></div>
                            <div>
                                <span>{{ $location->translated('city') }}</span>
                                <h2>{{ $location->translated('name') }}</h2>
                                @if($location->translated('subtitle'))<p>{{ $location->translated('subtitle') }}</p>@endif
                            </div>
                            @if($location->service_hours)
                                <div class="sax-guide-hours">
                                    <i class="fa-regular fa-clock"></i>
                                    <span>{!! nl2br(e($location->translated('service_hours'))) !!}</span>
                                </div>
                            @endif
                        </header>

                        <div class="sax-guide-sectors">
                            @foreach($location->entries as $entry)
                                <article class="sax-guide-sector" data-guide-entry
                                         data-guide-search="{{ Str::lower($location->translated('city').' '.$location->translated('name').' '.$entry->translated('floor').' '.$entry->translated('sector').' '.$entry->translated('description').' '.implode(' ', $entry->brands ?? [])) }}">
                                    <div class="sax-guide-sector-top">
                                        <span class="sax-guide-floor">{{ $entry->translated('floor') }}</span>
                                        @if($entry->is_optical)<span class="sax-guide-optical"><i class="fa-solid fa-glasses"></i> {{ __('messages.contact_guide_optical_badge') }}</span>@endif
                                    </div>
                                    <h3>{{ $entry->translated('sector') }}</h3>
                                    @if($entry->translated('description'))<p>{{ $entry->translated('description') }}</p>@endif

                                    @if($entry->brands)
                                        <div class="sax-guide-brands" aria-label="{{ __('messages.contact_guide_brands_label') }}">
                                            @foreach($entry->brands as $brand)
                                                <span>{{ $brand }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="sax-guide-sector-bottom">
                                        @if($entry->phone)
                                            <span class="sax-guide-phone"><i class="fa-brands fa-whatsapp"></i>{{ $entry->phone }}</span>
                                        @else
                                            <span class="sax-guide-updating"><i class="fa-regular fa-clock"></i>{{ __('messages.contact_guide_contact_updating') }}</span>
                                        @endif
                                        @if($entry->whatsappUrl())
                                            <a href="{{ $entry->whatsappUrl() }}" target="_blank" rel="noopener">
                                                {{ __('messages.contact_guide_talk_now') }} <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                            </a>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="sax-guide-empty" id="guideEmpty" hidden>
                <i class="fa-solid fa-magnifying-glass"></i>
                <h2>{{ __('messages.contact_guide_empty_title') }}</h2>
                <p>{{ __('messages.contact_guide_empty_description') }}</p>
            </div>

            <aside class="sax-guide-help">
                <div>
                    <span>{{ __('messages.contact_guide_help_eyebrow') }}</span>
                    <h2>{{ __('messages.contact_guide_help_title') }}</h2>
                </div>
                <a href="{{ route('contact.form') }}">{{ __('messages.contact_guide_help_button') }} <i class="fa-solid fa-arrow-right"></i></a>
            </aside>
        </section>
    </main>
@endsection

@push('styles')
<style>
    .sax-guide{background:#f8f6f2;color:#1d1a16;min-height:70vh}.sax-guide-hero{padding:4.5rem 0 5.5rem;background:radial-gradient(circle at 85% 10%,rgba(181,147,92,.2),transparent 28%),#171512;color:#fff}.sax-guide-hero .container{max-width:1180px}.sax-guide-eyebrow{display:block;margin-bottom:.9rem;color:#d6b881;font-size:.66rem;font-weight:800;letter-spacing:.18em;text-transform:uppercase}.sax-guide-hero h1{max-width:780px;margin:0;font-size:clamp(2rem,5vw,4.2rem);font-weight:350;line-height:1.04;letter-spacing:-.045em}.sax-guide-hero p{max-width:680px;margin:1.25rem 0 0;color:#c9c4bc;font-size:.86rem;line-height:1.7}.sax-guide-stats{display:flex;margin-top:2rem;gap:2.2rem}.sax-guide-stats div{display:flex;align-items:baseline;gap:.45rem}.sax-guide-stats strong{font-size:1.35rem}.sax-guide-stats span{color:#aaa49b;font-size:.62rem;text-transform:uppercase;letter-spacing:.1em}.sax-guide-body{position:relative;max-width:1180px;margin-top:-2rem;padding-bottom:5rem}.sax-guide-toolbar{position:sticky;top:calc(var(--header-height,0px) + 10px);z-index:6;display:flex;padding:.75rem;align-items:center;justify-content:space-between;gap:.8rem;border:1px solid #e5ded3;border-radius:16px;background:rgba(255,255,255,.95);box-shadow:0 12px 35px rgba(30,24,15,.09);backdrop-filter:blur(12px)}.sax-guide-search{position:relative;flex:1 1 360px;max-width:440px}.sax-guide-search i{position:absolute;top:50%;left:1rem;color:#9d9487;font-size:.72rem;transform:translateY(-50%)}.sax-guide-search input{width:100%;min-height:43px;padding:.65rem 1rem .65rem 2.6rem;border:1px solid #e7e1d7;border-radius:11px;background:#faf9f7;font-size:.72rem;outline:0}.sax-guide-search input:focus{border-color:#a98b5c;background:#fff;box-shadow:0 0 0 3px rgba(169,139,92,.1)}.sax-guide-city-filters{display:flex;gap:.4rem;overflow-x:auto}.sax-guide-city-filters button{flex:0 0 auto;padding:.55rem .82rem;border:1px solid #e2dbd0;border-radius:999px;background:#fff;color:#625b51;font-size:.62rem;font-weight:750}.sax-guide-city-filters button.is-active,.sax-guide-city-filters button:hover{border-color:#1b1814;background:#1b1814;color:#fff}.sax-guide-locations{display:grid;margin-top:1.25rem;gap:1.1rem}.sax-guide-location{overflow:hidden;border:1px solid #e6dfd5;border-radius:18px;background:#fff;box-shadow:0 12px 30px rgba(30,24,15,.045)}.sax-guide-location[hidden],.sax-guide-sector[hidden]{display:none}.sax-guide-location-head{display:grid;padding:1.2rem;grid-template-columns:44px minmax(0,1fr) auto;align-items:center;gap:.85rem;border-bottom:1px solid #eee8df;background:linear-gradient(120deg,#fff,#fbf9f5)}.sax-guide-location-mark{display:grid;width:44px;height:44px;place-items:center;border-radius:12px;background:#1b1814;color:#d8bc88}.sax-guide-location-head span{color:#a07e49;font-size:.58rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.sax-guide-location-head h2{margin:.18rem 0 0;font-size:1rem;font-weight:780}.sax-guide-location-head p{margin:.2rem 0 0;color:#827a6f;font-size:.63rem}.sax-guide-hours{display:flex;max-width:270px;padding:.6rem .75rem;align-items:flex-start;gap:.5rem;border:1px solid #e8e1d7;border-radius:10px;background:#fff;color:#71695e}.sax-guide-hours i{margin-top:.15rem;color:#a07e49;font-size:.65rem}.sax-guide-hours span{color:inherit;font-size:.56rem;font-weight:600;line-height:1.55;letter-spacing:0;text-transform:none}.sax-guide-sectors{display:grid;padding:1rem;grid-template-columns:repeat(3,minmax(0,1fr));gap:.7rem}.sax-guide-sector{display:flex;min-width:0;min-height:180px;padding:.9rem;flex-direction:column;border:1px solid #ebe5dc;border-radius:13px;background:#fff;transition:transform .18s ease,border-color .18s ease,box-shadow .18s ease}.sax-guide-sector:hover{border-color:#ceb991;box-shadow:0 8px 20px rgba(31,25,17,.07);transform:translateY(-2px)}.sax-guide-sector-top{display:flex;align-items:center;justify-content:space-between;gap:.5rem}.sax-guide-floor{display:inline-flex;padding:.3rem .48rem;border-radius:6px;background:#f3eee6;color:#896d40;font-size:.52rem;font-weight:850;letter-spacing:.08em;text-transform:uppercase}.sax-guide-optical{color:#715595;font-size:.5rem;font-weight:800;text-transform:uppercase}.sax-guide-optical i{margin-right:.25rem}.sax-guide-sector h3{margin:.65rem 0 0;color:#211d18;font-size:.8rem;font-weight:780}.sax-guide-sector>p{margin:.3rem 0 0;color:#7f776c;font-size:.6rem;line-height:1.45}.sax-guide-brands{display:flex;margin-top:.7rem;gap:.3rem;flex-wrap:wrap;max-height:68px;overflow:auto;scrollbar-width:thin}.sax-guide-brands span{padding:.25rem .4rem;border:1px solid #ece6dd;border-radius:999px;background:#faf9f7;color:#696157;font-size:.49rem}.sax-guide-sector-bottom{display:flex;margin-top:auto;padding-top:.8rem;align-items:center;justify-content:space-between;gap:.5rem}.sax-guide-phone,.sax-guide-updating{color:#27844a;font-size:.55rem;font-weight:750}.sax-guide-phone i{margin-right:.3rem}.sax-guide-updating{color:#8c8377}.sax-guide-sector-bottom a{color:#25211c;font-size:.55rem;font-weight:800;text-decoration:none}.sax-guide-sector-bottom a i{margin-left:.25rem;font-size:.46rem}.sax-guide-empty{padding:4rem 1rem;color:#81796e;text-align:center}.sax-guide-empty>i{font-size:1.2rem}.sax-guide-empty h2{margin:.8rem 0 .3rem;font-size:1rem}.sax-guide-empty p{font-size:.68rem}.sax-guide-help{display:flex;margin-top:1.2rem;padding:1.3rem 1.5rem;align-items:center;justify-content:space-between;gap:1rem;border-radius:16px;background:#a88650;color:#fff}.sax-guide-help span{font-size:.55rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.sax-guide-help h2{margin:.25rem 0 0;font-size:1rem}.sax-guide-help a{flex:0 0 auto;padding:.7rem .9rem;border-radius:9px;background:#171512;color:#fff;font-size:.62rem;font-weight:800;text-decoration:none}.sax-guide-help a i{margin-left:.4rem}
    .sax-guide-toolbar{position:relative;top:auto}
    @media(max-width:991px){.sax-guide-sectors{grid-template-columns:repeat(2,minmax(0,1fr))}.sax-guide-toolbar{align-items:stretch;flex-direction:column}.sax-guide-search{max-width:none}.sax-guide-city-filters{padding-bottom:.1rem}.sax-guide-location-head{grid-template-columns:40px minmax(0,1fr)}.sax-guide-location-mark{width:40px;height:40px}.sax-guide-hours{grid-column:1/-1;max-width:none}}
    @media(max-width:575px){.sax-guide-hero{padding:3rem 0 4rem}.sax-guide-hero h1{font-size:2rem}.sax-guide-hero p{font-size:.7rem}.sax-guide-stats{gap:1rem}.sax-guide-stats strong{font-size:1rem}.sax-guide-stats span{font-size:.48rem}.sax-guide-body{padding-right:.75rem;padding-left:.75rem}.sax-guide-toolbar{padding:.55rem;border-radius:13px}.sax-guide-search input{min-height:38px;font-size:.64rem}.sax-guide-city-filters button{padding:.45rem .62rem;font-size:.54rem}.sax-guide-location{border-radius:14px}.sax-guide-location-head{padding:.8rem;grid-template-columns:34px minmax(0,1fr);gap:.6rem}.sax-guide-location-mark{width:34px;height:34px;border-radius:9px;font-size:.7rem}.sax-guide-location-head h2{font-size:.8rem}.sax-guide-location-head p{font-size:.55rem}.sax-guide-hours{padding:.5rem}.sax-guide-sectors{padding:.6rem;grid-template-columns:1fr;gap:.45rem}.sax-guide-sector{min-height:0;padding:.72rem}.sax-guide-sector h3{font-size:.72rem}.sax-guide-brands{max-height:54px}.sax-guide-sector-bottom{padding-top:.65rem}.sax-guide-help{padding:1rem;align-items:flex-start;flex-direction:column}.sax-guide-help h2{font-size:.78rem}.sax-guide-help a{width:100%;text-align:center}}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',()=>{const search=document.getElementById('guideSearch');const filters=[...document.querySelectorAll('[data-guide-city]')];const locations=[...document.querySelectorAll('[data-guide-location]')];const empty=document.getElementById('guideEmpty');let city='all';const norm=v=>(v||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();const apply=()=>{const term=norm(search?.value);let visibleLocations=0;locations.forEach(location=>{let visibleEntries=0;location.querySelectorAll('[data-guide-entry]').forEach(entry=>{const show=(!term||norm(entry.dataset.guideSearch).includes(term))&&(city==='all'||location.dataset.city===city);entry.hidden=!show;if(show)visibleEntries++});location.hidden=visibleEntries===0;if(visibleEntries)visibleLocations++});if(empty)empty.hidden=visibleLocations>0};filters.forEach(button=>button.addEventListener('click',()=>{city=button.dataset.guideCity;filters.forEach(item=>item.classList.toggle('is-active',item===button));apply()}));search?.addEventListener('input',apply)});
</script>
@endpush
