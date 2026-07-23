@php $settings = \App\Models\MarketingSetting::current(); @endphp
@if(!Route::is('admin.*') && $settings->enabled)
    @if($settings->google_tag_manager_id)
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ urlencode($settings->google_tag_manager_id) }}" height="0" width="0" class="d-none invisible"></iframe></noscript>
    @endif
    @if($settings->meta_pixel_id)
        <noscript><img height="1" width="1" class="d-none" src="https://www.facebook.com/tr?id={{ urlencode($settings->meta_pixel_id) }}&ev=PageView&noscript=1" alt=""></noscript>
    @endif
    {!! $settings->custom_body_start_scripts !!}
@endif
