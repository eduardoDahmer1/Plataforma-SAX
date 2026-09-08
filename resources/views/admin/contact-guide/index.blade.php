@extends('layout.admin')

@section('content')
<x-admin.card>
    <div class="guide-admin">
        <header class="guide-admin-hero">
            <div>
                <span>{{ __('messages.admin_guide_public_content') }}</span>
                <h1>{{ __('messages.admin_guide_title') }}</h1>
                <p>{{ __('messages.admin_guide_description') }}</p>
            </div>
            <a href="{{ route('contact.guide') }}" target="_blank" rel="noopener" class="guide-admin-preview">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> {{ __('messages.admin_guide_view_site') }}
            </a>
        </header>

        @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

        <section class="guide-admin-create">
            <div class="guide-admin-section-title">
                <div><span>{{ __('messages.admin_guide_new_content') }}</span><h2>{{ __('messages.admin_guide_add_sector') }}</h2></div>
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <form action="{{ route('admin.contact-guide.entries.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-4"><label>{{ __('messages.admin_guide_location') }}</label><select name="location_id" class="form-select" required>@foreach($locations as $location)<option value="{{ $location->id }}">{{ $location->city }} · {{ $location->name }}</option>@endforeach</select></div>
                <div class="col-6 col-md-2"><label>{{ __('messages.admin_guide_floor') }}</label><input name="floor" class="form-control" placeholder="Piso 4" required></div>
                <div class="col-6 col-md-4"><label>{{ __('messages.admin_guide_sector') }}</label><input name="sector" class="form-control" placeholder="Lentes e óculos" required></div>
                <div class="col-6 col-md-2"><label>{{ __('messages.admin_guide_order') }}</label><input name="sort_order" type="number" min="0" class="form-control" value="0"></div>
                <div class="col-md-4"><label>{{ __('messages.admin_guide_phone') }}</label><input name="phone" class="form-control" placeholder="+595 000 000000"></div>
                <div class="col-md-8"><label>{{ __('messages.admin_guide_custom_link') }} <small>{{ __('messages.admin_guide_optional') }}</small></label><input name="whatsapp_url" type="url" class="form-control" placeholder="https://wa.link/..."></div>
                <div class="col-md-5"><label>{{ __('messages.admin_guide_short_description') }}</label><textarea name="description" class="form-control" rows="3" maxlength="500"></textarea></div>
                <div class="col-md-7"><label>{{ __('messages.marcas') }} <small>({{ __('messages.admin_guide_brands_hint') }})</small></label><textarea name="brands_text" class="form-control" rows="3"></textarea></div>
                <details class="col-12 guide-admin-translations"><summary><i class="fa-solid fa-language"></i> English / Español</summary><div class="row g-2 pt-2"><div class="col-md-2"><label>Floor (EN)</label><input name="floor_en" class="form-control"></div><div class="col-md-4"><label>Department (EN)</label><input name="sector_en" class="form-control"></div><div class="col-md-6"><label>Description (EN)</label><input name="description_en" class="form-control"></div><div class="col-md-2"><label>Piso (ES)</label><input name="floor_es" class="form-control"></div><div class="col-md-4"><label>Sector (ES)</label><input name="sector_es" class="form-control"></div><div class="col-md-6"><label>Descripción (ES)</label><input name="description_es" class="form-control"></div></div></details>
                <div class="col-6 col-md-3"><label class="guide-admin-check"><input type="hidden" name="active" value="0"><input type="checkbox" name="active" value="1" checked> {{ __('messages.ativo') }}</label></div>
                <div class="col-6 col-md-4"><label class="guide-admin-check"><input type="hidden" name="is_optical" value="0"><input type="checkbox" name="is_optical" value="1"> {{ __('messages.admin_guide_show_optical') }}</label></div>
                <div class="col-md-5 text-md-end"><button class="btn btn-dark px-4"><i class="fa-solid fa-plus me-2"></i>{{ __('messages.admin_guide_add_sector') }}</button></div>
            </form>
        </section>

        <div class="guide-admin-list-head">
            <div><span>{{ __('messages.admin_guide_registered_structure') }}</span><h2>{{ __('messages.admin_guide_count_summary', ['locations' => $locations->count(), 'sectors' => $locations->sum(fn($location) => $location->entries->count())]) }}</h2></div>
            <details class="guide-admin-new-location">
                <summary><i class="fa-solid fa-plus"></i> {{ __('messages.admin_guide_new_location') }}</summary>
                <form action="{{ route('admin.contact-guide.locations.store') }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-md-6"><label>{{ __('messages.admin_guide_city') }}</label><input name="city" class="form-control" required></div>
                    <div class="col-md-6"><label>{{ __('messages.admin_guide_location_name') }}</label><input name="name" class="form-control" required></div>
                    <div class="col-md-8"><label>{{ __('messages.admin_guide_complement') }}</label><input name="subtitle" class="form-control"></div>
                    <div class="col-md-4"><label>{{ __('messages.admin_guide_order') }}</label><input name="sort_order" type="number" min="0" class="form-control" value="0"></div>
                    <div class="col-12"><label>{{ __('messages.admin_guide_hours') }} <small>({{ __('messages.admin_guide_hours_hint') }})</small></label><textarea name="service_hours" class="form-control" rows="2"></textarea></div>
                    <details class="col-12 guide-admin-translations"><summary><i class="fa-solid fa-language"></i> English / Español</summary><div class="row g-2 pt-2">@foreach(['en' => 'English', 'es' => 'Español'] as $code => $language)<div class="col-12"><strong>{{ $language }}</strong></div><div class="col-md-4"><input name="city_{{ $code }}" class="form-control" placeholder="City"></div><div class="col-md-4"><input name="name_{{ $code }}" class="form-control" placeholder="Location"></div><div class="col-md-4"><input name="subtitle_{{ $code }}" class="form-control" placeholder="Subtitle"></div><div class="col-12"><textarea name="service_hours_{{ $code }}" class="form-control" rows="2" placeholder="Opening hours"></textarea></div>@endforeach</div></details>
                    <div class="col-5"><label class="guide-admin-check"><input type="hidden" name="active" value="0"><input type="checkbox" name="active" value="1" checked> {{ __('messages.ativo') }}</label></div>
                    <div class="col-7 text-end"><button class="btn btn-dark">{{ __('messages.admin_guide_create_location') }}</button></div>
                </form>
            </details>
        </div>

        <div class="guide-admin-locations">
            @foreach($locations as $location)
                <article class="guide-admin-location">
                    <details {{ $loop->first ? 'open' : '' }}>
                        <summary>
                            <span class="guide-admin-location-icon"><i class="fa-solid fa-location-dot"></i></span>
                            <span><small>{{ $location->city }}</small><strong>{{ $location->name }}</strong></span>
                            <span class="guide-admin-location-count">{{ $location->entries->count() }} {{ __('messages.contact_guide_sectors') }}</span>
                            <i class="fa-solid fa-chevron-down guide-admin-chevron"></i>
                        </summary>
                        <div class="guide-admin-location-body">
                            <form action="{{ route('admin.contact-guide.locations.update', $location) }}" method="POST" class="row g-2 guide-admin-location-form">
                                @csrf @method('PUT')
                                <div class="col-md-3"><label>{{ __('messages.admin_guide_city') }}</label><input name="city" class="form-control" value="{{ $location->city }}" required></div>
                                <div class="col-md-4"><label>{{ __('messages.admin_guide_location') }}</label><input name="name" class="form-control" value="{{ $location->name }}" required></div>
                                <div class="col-md-3"><label>{{ __('messages.admin_guide_complement') }}</label><input name="subtitle" class="form-control" value="{{ $location->subtitle }}"></div>
                                <div class="col-md-2"><label>{{ __('messages.admin_guide_order') }}</label><input name="sort_order" type="number" class="form-control" min="0" value="{{ $location->sort_order }}"></div>
                                <div class="col-md-8"><label>{{ __('messages.admin_guide_hours') }}</label><textarea name="service_hours" class="form-control" rows="2">{{ $location->service_hours }}</textarea></div>
                                <details class="col-12 guide-admin-translations"><summary><i class="fa-solid fa-language"></i> English / Español</summary><div class="row g-2 pt-2">@foreach(['en' => 'English', 'es' => 'Español'] as $code => $language)<div class="col-12"><strong>{{ $language }}</strong></div><div class="col-md-4"><input name="city_{{ $code }}" class="form-control" value="{{ $location->{'city_'.$code} }}" placeholder="City"></div><div class="col-md-4"><input name="name_{{ $code }}" class="form-control" value="{{ $location->{'name_'.$code} }}" placeholder="Location"></div><div class="col-md-4"><input name="subtitle_{{ $code }}" class="form-control" value="{{ $location->{'subtitle_'.$code} }}" placeholder="Subtitle"></div><div class="col-12"><textarea name="service_hours_{{ $code }}" class="form-control" rows="2" placeholder="Opening hours">{{ $location->{'service_hours_'.$code} }}</textarea></div>@endforeach</div></details>
                                <div class="col-5 col-md-2"><label class="guide-admin-check"><input type="hidden" name="active" value="0"><input type="checkbox" name="active" value="1" @checked($location->active)> {{ __('messages.ativo') }}</label></div>
                                <div class="col-7 col-md-2 text-end"><button class="btn btn-outline-dark">{{ __('messages.admin_guide_save_location') }}</button></div>
                            </form>

                            <div class="guide-admin-entries">
                                @foreach($location->entries as $entry)
                                    <details class="guide-admin-entry">
                                        <summary>
                                            <span class="guide-admin-floor">{{ $entry->floor }}</span>
                                            <strong>{{ $entry->sector }}</strong>
                                            @if($entry->is_optical)<span class="guide-admin-optical"><i class="fa-solid fa-glasses"></i> {{ __('messages.contact_guide_optical_badge') }}</span>@endif
                                            @unless($entry->active)<span class="guide-admin-inactive">{{ __('messages.inativo') }}</span>@endunless
                                            <span class="guide-admin-phone">{{ $entry->phone ?: __('messages.admin_guide_no_phone') }}</span>
                                            <i class="fa-solid fa-pen"></i>
                                        </summary>
                                        <form action="{{ route('admin.contact-guide.entries.update', $entry) }}" method="POST" class="row g-2 guide-admin-entry-form">
                                            @csrf @method('PUT')
                                            <div class="col-md-4"><label>{{ __('messages.admin_guide_location') }}</label><select name="location_id" class="form-select">@foreach($locations as $option)<option value="{{ $option->id }}" @selected($option->id === $entry->location_id)>{{ $option->city }} · {{ $option->name }}</option>@endforeach</select></div>
                                            <div class="col-6 col-md-2"><label>{{ __('messages.admin_guide_floor') }}</label><input name="floor" class="form-control" value="{{ $entry->floor }}" required></div>
                                            <div class="col-6 col-md-4"><label>{{ __('messages.admin_guide_sector') }}</label><input name="sector" class="form-control" value="{{ $entry->sector }}" required></div>
                                            <div class="col-md-2"><label>{{ __('messages.admin_guide_order') }}</label><input name="sort_order" type="number" min="0" class="form-control" value="{{ $entry->sort_order }}"></div>
                                            <div class="col-md-4"><label>{{ __('messages.telefone') }}</label><input name="phone" class="form-control" value="{{ $entry->phone }}"></div>
                                            <div class="col-md-8"><label>{{ __('messages.admin_guide_custom_link_short') }}</label><input name="whatsapp_url" type="url" class="form-control" value="{{ $entry->whatsapp_url }}"></div>
                                            <div class="col-md-5"><label>{{ __('messages.admin_guide_description_field') }}</label><textarea name="description" class="form-control" rows="4">{{ $entry->description }}</textarea></div>
                                            <div class="col-md-7"><label>{{ __('messages.marcas') }}</label><textarea name="brands_text" class="form-control" rows="4">{{ implode("\n", $entry->brands ?? []) }}</textarea></div>
                                            <details class="col-12 guide-admin-translations"><summary><i class="fa-solid fa-language"></i> English / Español</summary><div class="row g-2 pt-2"><div class="col-md-2"><label>Floor (EN)</label><input name="floor_en" class="form-control" value="{{ $entry->floor_en }}"></div><div class="col-md-4"><label>Department (EN)</label><input name="sector_en" class="form-control" value="{{ $entry->sector_en }}"></div><div class="col-md-6"><label>Description (EN)</label><input name="description_en" class="form-control" value="{{ $entry->description_en }}"></div><div class="col-md-2"><label>Piso (ES)</label><input name="floor_es" class="form-control" value="{{ $entry->floor_es }}"></div><div class="col-md-4"><label>Sector (ES)</label><input name="sector_es" class="form-control" value="{{ $entry->sector_es }}"></div><div class="col-md-6"><label>Descripción (ES)</label><input name="description_es" class="form-control" value="{{ $entry->description_es }}"></div></div></details>
                                            <div class="col-4"><label class="guide-admin-check"><input type="hidden" name="active" value="0"><input type="checkbox" name="active" value="1" @checked($entry->active)> {{ __('messages.ativo') }}</label></div>
                                            <div class="col-4"><label class="guide-admin-check"><input type="hidden" name="is_optical" value="0"><input type="checkbox" name="is_optical" value="1" @checked($entry->is_optical)> {{ __('messages.contact_guide_optical_badge') }}</label></div>
                                            <div class="col-4 text-end"><button class="btn btn-dark">{{ __('messages.admin_guide_save_sector') }}</button></div>
                                        </form>
                                        <form action="{{ route('admin.contact-guide.entries.destroy', $entry) }}" method="POST" class="guide-admin-delete" onsubmit="return confirm(@js(__('messages.admin_guide_confirm_delete_sector')))">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i> {{ __('messages.admin_guide_delete_sector') }}</button></form>
                                    </details>
                                @endforeach
                            </div>

                            <form action="{{ route('admin.contact-guide.locations.destroy', $location) }}" method="POST" class="guide-admin-delete-location" onsubmit="return confirm(@js(__('messages.admin_guide_confirm_delete_location')))">@csrf @method('DELETE')<button class="btn btn-sm btn-link text-danger"><i class="fa-regular fa-trash-can"></i> {{ __('messages.admin_guide_delete_location') }}</button></form>
                        </div>
                    </details>
                </article>
            @endforeach
        </div>
    </div>
</x-admin.card>
@endsection

@push('styles')
<style>
.guide-admin-translations{padding:.55rem .7rem;border:1px solid #dfe5ec;border-radius:9px;background:#fff}.guide-admin-translations>summary{color:#536176;font-size:.67rem;font-weight:800;cursor:pointer;list-style:none}.guide-admin-translations>summary i{margin-right:.35rem;color:#8469a7}
.guide-admin{color:#20242b}.guide-admin label{display:block;margin-bottom:.32rem;color:#596274;font-size:.68rem;font-weight:700}.guide-admin label small{font-weight:500}.guide-admin-hero{display:flex;margin-bottom:1.2rem;padding:1.4rem;align-items:center;justify-content:space-between;gap:1rem;border-radius:16px;background:linear-gradient(125deg,#171a20,#2d313a);color:#fff}.guide-admin-hero span,.guide-admin-section-title span,.guide-admin-list-head>div>span{display:block;color:#d8b477;font-size:.62rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.guide-admin-hero h1{margin:.3rem 0;font-size:1.45rem}.guide-admin-hero p{max-width:720px;margin:0;color:#c4c8d0;font-size:.75rem}.guide-admin-preview{padding:.7rem .9rem;border-radius:9px;background:#fff;color:#1c2027;font-size:.7rem;font-weight:800;text-decoration:none;white-space:nowrap}.guide-admin-create{padding:1rem;border:1px solid #e2e7ed;border-radius:14px;background:#f8fafc}.guide-admin-section-title{display:flex;margin-bottom:.8rem;align-items:center;justify-content:space-between}.guide-admin-section-title h2,.guide-admin-list-head h2{margin:.15rem 0 0;font-size:1rem}.guide-admin-check{display:flex!important;min-height:38px;margin:0!important;align-items:center;gap:.45rem}.guide-admin-list-head{display:flex;margin:1.4rem 0 .7rem;align-items:center;justify-content:space-between;gap:1rem}.guide-admin-new-location{position:relative}.guide-admin-new-location>summary{padding:.55rem .75rem;border:1px solid #d8dee6;border-radius:9px;cursor:pointer;font-size:.68rem;font-weight:750;list-style:none}.guide-admin-new-location[open]>form{position:absolute;right:0;z-index:5;width:min(680px,90vw);margin-top:.5rem;padding:1rem;border:1px solid #dfe4ea;border-radius:12px;background:#fff;box-shadow:0 16px 40px rgba(20,28,40,.16)}.guide-admin-locations{display:grid;gap:.65rem}.guide-admin-location{border:1px solid #e1e6ec;border-radius:13px;background:#fff;overflow:hidden}.guide-admin-location>details>summary{display:grid;padding:.8rem 1rem;grid-template-columns:36px minmax(0,1fr) auto 16px;align-items:center;gap:.7rem;cursor:pointer;list-style:none}.guide-admin-location-icon{display:grid;width:36px;height:36px;place-items:center;border-radius:9px;background:#edf2f7;color:#506176}.guide-admin-location summary small,.guide-admin-location summary strong{display:block}.guide-admin-location summary small{color:#8b6c3f;font-size:.55rem;text-transform:uppercase}.guide-admin-location summary strong{font-size:.78rem}.guide-admin-location-count{font-size:.62rem;color:#737d8d}.guide-admin-chevron{font-size:.58rem;transition:transform .2s}.guide-admin-location>details[open]>summary .guide-admin-chevron{transform:rotate(180deg)}.guide-admin-location-body{padding:1rem;border-top:1px solid #e8ecf1;background:#fafbfc}.guide-admin-location-form{padding-bottom:1rem;border-bottom:1px solid #e2e7ed}.guide-admin-entries{display:grid;margin-top:1rem;gap:.45rem}.guide-admin-entry{border:1px solid #e1e6ec;border-radius:10px;background:#fff}.guide-admin-entry>summary{display:flex;padding:.65rem .75rem;align-items:center;gap:.55rem;cursor:pointer;list-style:none}.guide-admin-entry>summary strong{font-size:.7rem}.guide-admin-floor{padding:.25rem .4rem;border-radius:5px;background:#f1ece4;color:#876b40;font-size:.52rem;font-weight:800}.guide-admin-optical,.guide-admin-inactive{padding:.22rem .38rem;border-radius:999px;background:#eee8f5;color:#725695;font-size:.5rem;font-weight:800}.guide-admin-inactive{background:#f7e8e8;color:#a34b4b}.guide-admin-phone{margin-left:auto;color:#27814a;font-size:.58rem}.guide-admin-entry>summary>i{color:#9ba3ae;font-size:.55rem}.guide-admin-entry-form{padding:.9rem;border-top:1px solid #edf0f3}.guide-admin-delete{padding:0 .9rem .9rem;text-align:right}.guide-admin-delete-location{margin-top:.8rem;text-align:right}.guide-admin .form-control,.guide-admin .form-select{font-size:.72rem}.guide-admin .btn{font-size:.68rem}
@media(max-width:767px){.guide-admin-hero{padding:1rem;align-items:flex-start;flex-direction:column}.guide-admin-hero h1{font-size:1.05rem}.guide-admin-hero p{font-size:.65rem}.guide-admin-preview{width:100%;text-align:center}.guide-admin-list-head{align-items:flex-start;flex-direction:column}.guide-admin-new-location{width:100%}.guide-admin-new-location>summary{text-align:center}.guide-admin-new-location[open]>form{position:static;width:100%}.guide-admin-location>details>summary{padding:.65rem;grid-template-columns:32px minmax(0,1fr) 14px}.guide-admin-location-icon{width:32px;height:32px}.guide-admin-location-count{display:none}.guide-admin-location-body{padding:.65rem}.guide-admin-entry>summary{display:grid;grid-template-columns:auto minmax(0,1fr) auto}.guide-admin-phone{grid-column:2;margin:0}.guide-admin-entry>summary>i{grid-column:3;grid-row:1/3}.guide-admin-optical,.guide-admin-inactive{display:none}}
</style>
@endpush
