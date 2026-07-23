@php $settings = \App\Models\MarketingSetting::current(); @endphp
@if(!Route::is('admin.*') && $settings->enabled)
    {!! $settings->custom_body_end_scripts !!}
@endif
