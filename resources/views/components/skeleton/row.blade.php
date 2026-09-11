@props([
    'columns' => 5,
    'avatar' => true,
])

<div {{ $attributes->merge(['class' => 'flex items-center gap-4 px-5 py-3.5 border-b border-slate-100 last:border-b-0']) }} aria-hidden="true">
    @if($avatar)
        <x-skeleton.circle size="w-8 h-8" />
    @endif
    <div class="flex-1 grid items-center gap-4" style="grid-template-columns: repeat({{ max(1, (int) $columns - ($avatar ? 1 : 0)) }}, minmax(0, 1fr));">
        @for($c = 0; $c < max(1, (int) $columns - ($avatar ? 1 : 0)); $c++)
            <div class="space-y-1.5">
                <x-skeleton.text width="{{ $c === 0 ? 'w-3/4' : ($c === (int) $columns - ($avatar ? 2 : 1) ? 'w-16 ml-auto' : 'w-2/3') }}" height="h-3" />
                @if($c === 0)
                    <x-skeleton.text width="w-1/2" height="h-2" />
                @endif
            </div>
        @endfor
    </div>
</div>
