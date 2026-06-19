@props([
    'label' => 'CEET',
])

<span {{ $attributes->merge(['class' => 'ceet-application-logo']) }} style="width:58px;height:58px;min-width:58px;max-width:58px;min-height:58px;max-height:58px;display:inline-flex;align-items:center;justify-content:center;overflow:hidden;border:1px solid #d9dde3;border-radius:12px;background:#ffffff;line-height:1;">
    <img src="{{ asset('images/logo-ceet.png') }}" alt="{{ $label }}" loading="lazy" style="width:48px;height:48px;max-width:48px;max-height:48px;object-fit:contain;display:block;flex:0 0 auto;">
</span>
