@props([
    'variant' => 'light', // 'light' | 'dark'
])

@if($variant === 'dark')
    {{-- Dark hero card variant (matches admin dashboard hero) --}}
    <div {{ $attributes->merge(['class' => 'bg-gradient-to-br from-maarif-800 to-maarif-900 rounded-2xl p-6 space-y-4']) }} aria-hidden="true">
        <x-skeleton.text width="w-1/3" height="h-3" :dark="true" />
        <div class="flex items-baseline gap-3">
            <x-skeleton.text width="w-24" height="h-10" :dark="true" />
            <x-skeleton.text width="w-20" height="h-4" :dark="true" />
        </div>
        <div class="space-y-2">
            <div class="flex justify-between">
                <x-skeleton.text width="w-24" height="h-2.5" :dark="true" />
                <x-skeleton.text width="w-10" height="h-2.5" :dark="true" />
            </div>
            <x-skeleton.text width="w-full" height="h-2" :dark="true" rounded="rounded-full" />
        </div>
    </div>
@else
    {{-- Light mini card variant --}}
    <div {{ $attributes->merge(['class' => 'bg-white rounded-2xl p-5 border border-slate-200/80 space-y-3']) }} aria-hidden="true">
        <div class="flex items-center justify-between">
            <x-skeleton.text width="w-20" height="h-2.5" />
            <x-skeleton.rect height="h-8" rounded="rounded-xl" />
        </div>
        <x-skeleton.text width="w-1/2" height="h-7" />
        <x-skeleton.text width="w-2/3" height="h-2.5" />
    </div>
@endif
