@once
@if (! Route::is('admin.*') && ! Request::is('admin/*'))
    @php
        $whatsappWidget = app(\App\Services\WhatsappWidgetService::class)->configuration(request());
        $whatsappContacts = $whatsappWidget['contacts'];
        $widgetBanner = $whatsapp_banner ?? $attributes?->whatsapp_banner ?? null;
    @endphp

    @if (($whatsappWidget['enabled'] ?? false) && $whatsappContacts->isNotEmpty())
        <div class="whatsapp-container" id="whatsappContainer" data-page-context="{{ $whatsappWidget['context'] }}">
            <div class="whatsapp-menu" id="whatsappMenu">
                <div class="whatsapp-menu-header">
                    <strong>{{ $whatsappWidget['title'] }}</strong>
                    <span>{{ $whatsappWidget['subtitle'] }}</span>
                </div>
                <div class="whatsapp-menu-body">
                    @foreach ($whatsappContacts as $whatsappContact)
                        <a href="{{ $whatsappContact->whatsappUrl() }}" target="_blank" rel="noopener noreferrer"
                           class="whatsapp-menu-item" aria-label="{{ __('messages.whatsapp_speak_with', ['name' => $whatsappContact->translated('title')]) }}">
                            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
                            <span>
                                <strong>{{ $whatsappContact->translated('title') }}</strong>
                                <small>{{ $whatsappContact->phone }}</small>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            <button type="button" class="whatsapp-floating-banner" id="whatsappToggle"
                    aria-expanded="false" aria-controls="whatsappMenu" aria-label="{{ __('messages.whatsapp_open_service') }}">
                @if ($widgetBanner)
                    <img src="{{ asset('storage/uploads/'.$widgetBanner) }}" alt="WhatsApp">
                @else
                    <span><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></span>
                @endif
            </button>
        </div>

        <style>
            .whatsapp-container{position:fixed;right:30px;bottom:30px;z-index:2000}
            .whatsapp-floating-banner{display:grid;width:54px;height:54px;padding:0;place-items:center;border:0;border-radius:999px;background:transparent;cursor:pointer;filter:drop-shadow(0 6px 14px rgba(0,0,0,.2));transition:transform .24s ease,filter .24s ease}
            .whatsapp-floating-banner img{display:block;width:100%;height:100%;border-radius:999px;object-fit:contain}
            .whatsapp-floating-banner>span{display:grid;width:54px;height:54px;place-items:center;border:2px solid #fff;border-radius:999px;background:#25d366;color:#fff;font-size:1.8rem}
            .whatsapp-floating-banner:hover{transform:translateY(-2px) scale(1.03);filter:drop-shadow(0 10px 20px rgba(0,0,0,.28))}
            .whatsapp-menu{position:absolute;right:0;bottom:72px;display:none;width:330px;overflow:hidden;border:1px solid #e8e8e8;border-radius:16px;background:#fff;box-shadow:0 16px 42px rgba(0,0,0,.2);opacity:0;transform:translateY(12px) scale(.98);transition:opacity .2s ease,transform .2s ease}
            .whatsapp-container.show-menu .whatsapp-menu{display:block;opacity:1;transform:translateY(0) scale(1)}
            .whatsapp-menu-header{display:flex;padding:13px 15px;flex-direction:column;background:linear-gradient(135deg,#111 0%,#2a2a2a 100%);color:#fff}
            .whatsapp-menu-header strong{font-family:'Montserrat',sans-serif;font-size:14px;letter-spacing:.5px;line-height:1.2}
            .whatsapp-menu-header span{margin-top:5px;color:#d4d4d4;font-size:11px;line-height:1.3}
            .whatsapp-menu-body{max-height:min(52vh,380px);padding:6px 0;overflow-y:auto;background:#fff}
            .whatsapp-menu-item{display:flex;padding:10px 15px;align-items:center;gap:10px;color:#333!important;font-size:12px;line-height:1.35;text-decoration:none!important;transition:background-color .16s ease}
            .whatsapp-menu-item>i{flex:0 0 auto;color:#25d366;font-size:17px}
            .whatsapp-menu-item>span{display:block;min-width:0}
            .whatsapp-menu-item strong,.whatsapp-menu-item small{display:block}
            .whatsapp-menu-item strong{font-size:12px;font-weight:600}
            .whatsapp-menu-item small{margin-top:2px;color:#7a7a7a;font-size:10px}
            .whatsapp-menu-item:hover{background:#f4f4f4;color:#191919!important}
            @media(max-width:768px){.whatsapp-container{right:14px;bottom:calc(86px + env(safe-area-inset-bottom,0px))}.whatsapp-floating-banner,.whatsapp-floating-banner>span{width:48px;height:48px}.whatsapp-menu{bottom:62px;width:min(330px,calc(100vw - 24px))}}
        </style>

        <script>
            (function () {
                var init = function () {
                    var container = document.getElementById('whatsappContainer');
                    var toggle = document.getElementById('whatsappToggle');
                    if (!container || !toggle || container.dataset.bound === '1') return;
                    container.dataset.bound = '1';
                    var timer = null;
                    var desktop = window.matchMedia('(min-width:769px)');
                    var setOpen = function (open) {
                        container.classList.toggle('show-menu', open);
                        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                    };
                    var clearTimer = function () { if (timer) { window.clearTimeout(timer); timer = null; } };
                    container.addEventListener('mouseenter', function () { if (desktop.matches) { clearTimer(); setOpen(true); } });
                    container.addEventListener('mouseleave', function () { if (desktop.matches) { clearTimer(); timer = window.setTimeout(function () { setOpen(false); }, 1800); } });
                    toggle.addEventListener('click', function () { clearTimer(); setOpen(!container.classList.contains('show-menu')); });
                    document.addEventListener('pointerdown', function (event) { if (!container.contains(event.target)) setOpen(false); }, {passive:true});
                    document.addEventListener('keydown', function (event) { if (event.key === 'Escape') setOpen(false); });
                };
                if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, {once:true});
                else init();
            })();
        </script>
    @endif
@endif
@endonce
