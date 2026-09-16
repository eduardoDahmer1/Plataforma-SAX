@props(['product' => null, 'newTab' => false])

@if ($product)
    <a href="{{ route('produto.show', $product->slug ?: $product->id) }}"
       {{ $attributes->merge(['class' => 'text-decoration-none', 'style' => 'color: inherit;']) }}
       @if ($newTab) target="_blank" rel="noopener noreferrer" @endif>{{ $slot }}</a>
@else
    <span {{ $attributes }}>{{ $slot }}</span>
@endif
