@props([
    'lines' => 3,
    'avatar' => false,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-200/80 p-5 space-y-3']) }} aria-hidden="true">
    @if($avatar)
        <div class="flex items-center gap-3">
            <x-skeleton.circle size="w-10 h-10" />
            <div class="flex-1 space-y-2">
                <x-skeleton.text width="w-1/2" />
                <x-skeleton.text width="w-1/3" height="h-2.5" />
            </div>
        </div>
    @endif
    @for($i = 0; $i < (int) $lines; $i++)
        <x-skeleton.text width="{{ $i === (int) $lines - 1 ? 'w-2/3' : 'w-full' }}" />
    @endfor
</div>
