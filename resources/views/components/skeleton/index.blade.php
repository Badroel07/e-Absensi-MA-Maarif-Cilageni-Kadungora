@props([
    'count' => 5,
    'columns' => 5,
    'showHeader' => true,
    'bordered' => false,
    'avatar' => true,
])

<div {{ $attributes->class(['bg-white overflow-hidden', 'rounded-2xl border border-slate-200/80' => $bordered]) }} aria-hidden="true">
    @if($showHeader)
        <div class="px-5 py-3.5 border-b border-slate-200/80 bg-slate-50/60">
            <div class="grid items-center gap-4" style="grid-template-columns: repeat({{ (int) $columns }}, minmax(0, 1fr));">
                @for($i = 0; $i < (int) $columns; $i++)
                    <x-skeleton.text width="{{ $i === (int) $columns - 1 ? 'w-16 ml-auto' : 'w-24' }}" height="h-2.5" />
                @endfor
            </div>
        </div>
    @endif
    @for($i = 0; $i < (int) $count; $i++)
        <x-skeleton.row :columns="$columns" :avatar="$avatar" />
    @endfor
</div>
