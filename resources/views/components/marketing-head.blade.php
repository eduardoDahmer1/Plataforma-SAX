@if(!Route::is('admin.*') && $settings->enabled)
    @if($settings->google_tag_manager_id)
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer',@json($settings->google_tag_manager_id));</script>
    @endif

    @php $googleTagId = $settings->google_analytics_id ?: $settings->google_ads_id; @endphp
    @if($googleTagId)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($googleTagId) }}"></script>
        <script>
            window.dataLayer=window.dataLayer||[];
            function gtag(){dataLayer.push(arguments);}
            gtag('js',new Date());
            @if($settings->google_analytics_id) gtag('config',@json($settings->google_analytics_id)); @endif
            @if($settings->google_ads_id) gtag('config',@json($settings->google_ads_id)); @endif
        </script>
    @endif

    @if($settings->meta_pixel_id)
        <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init',@json($settings->meta_pixel_id));fbq('track','PageView');</script>
    @endif

    @if($settings->tiktok_pixel_id)
        <script>!function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=['page','track','identify','instances','debug','on','off','once','ready','alias','group','enableCookie','disableCookie','holdConsent','revokeConsent','grantConsent'];ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.load=function(e){var n='https://analytics.tiktok.com/i18n/pixel/events.js',a=d.createElement('script');a.type='text/javascript';a.async=!0;a.src=n+'?sdkid='+e+'&lib='+t;var s=d.getElementsByTagName('script')[0];s.parentNode.insertBefore(a,s)};ttq.load(@json($settings->tiktok_pixel_id));ttq.page()}(window,document,'ttq');</script>
    @endif

    @if($settings->pinterest_tag_id)
        <script>!function(e){if(!window.pintrk){window.pintrk=function(){window.pintrk.queue.push(Array.prototype.slice.call(arguments))};var n=window.pintrk;n.queue=[];n.version='3.0';var t=document.createElement('script');t.async=!0;t.src=e;var r=document.getElementsByTagName('script')[0];r.parentNode.insertBefore(t,r)}}('https://s.pinimg.com/ct/core.js');pintrk('load',@json($settings->pinterest_tag_id));pintrk('page');</script>
    @endif

    @if($settings->linkedin_partner_id)
        <script>window._linkedin_partner_id=@json($settings->linkedin_partner_id);window._linkedin_data_partner_ids=window._linkedin_data_partner_ids||[];window._linkedin_data_partner_ids.push(window._linkedin_partner_id);(function(l){if(!l){window.lintrk=function(a,b){window.lintrk.q.push([a,b])};window.lintrk.q=[]}var s=document.getElementsByTagName('script')[0],b=document.createElement('script');b.type='text/javascript';b.async=true;b.src='https://snap.licdn.com/li.lms-analytics/insight.min.js';s.parentNode.insertBefore(b,s)})(window.lintrk);</script>
    @endif

    @if($settings->microsoft_clarity_id)
        <script>(function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src='https://www.clarity.ms/tag/'+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y)})(window,document,'clarity','script',@json($settings->microsoft_clarity_id));</script>
    @endif

    @if($settings->organization_name)
        @php
            $schema = array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => $settings->organization_name,
                'url' => $settings->organization_url,
                'logo' => $settings->organization_logo_url,
                'sameAs' => collect(preg_split('/\R/', (string) $settings->organization_social_urls))->map(fn($url) => trim($url))->filter()->values()->all(),
            ], fn($value) => $value !== null && $value !== '' && $value !== []);
        @endphp
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP) !!}</script>
    @endif

    {!! $settings->custom_head_scripts !!}
@endif
