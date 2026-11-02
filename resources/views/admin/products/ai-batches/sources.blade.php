@if(!empty($sources))
    <strong class="d-block mt-2">Fuentes consultadas</strong>
    <ul class="mb-0 ps-3">
        @foreach($sources as $source)
            @if(is_string($source['url'] ?? null) && preg_match('~^https?://~i', $source['url']))
                <li><a href="{{ $source['url'] }}" target="_blank" rel="noopener noreferrer" class="text-break">{{ ($source['title'] ?? '') ?: $source['url'] }}</a></li>
            @endif
        @endforeach
    </ul>
@endif
