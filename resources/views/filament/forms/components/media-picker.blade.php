<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @php
        $imgBorder = '1px solid rgba(120,120,120,0.35)';
    @endphp

    @if ($field->isMultiple())
        {{-- Multiple images: entangled grid with remove + reorder --}}
        <div
            x-data="{
                items: $wire.$entangle('{{ $getStatePath() }}'),
                src(u) { return (u && (u.startsWith('http') || u.startsWith('/'))) ? u : '/' + u; },
                remove(i) { this.items.splice(i, 1); },
                move(i, d) { const j = i + d; if (j < 0 || j >= this.items.length) return; [this.items[i], this.items[j]] = [this.items[j], this.items[i]]; },
            }"
        >
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:0.75rem;margin-bottom:0.75rem">
                <template x-for="(url, i) in items" :key="i">
                    <div style="position:relative;overflow:hidden;border-radius:0.5rem;border:{{ $imgBorder }}">
                        <img :src="src(url)" style="height:6rem;width:100%;object-fit:cover;display:block" />
                        <button type="button" @click="remove(i)" title="Remove"
                                style="position:absolute;top:0.25rem;right:0.25rem;width:1.5rem;height:1.5rem;border-radius:9999px;background:rgba(0,0,0,0.6);color:#fff;display:inline-flex;align-items:center;justify-content:center;line-height:1">&times;</button>
                        <div style="position:absolute;bottom:0.25rem;left:0.25rem;display:flex;gap:0.25rem">
                            <button type="button" @click="move(i,-1)" x-show="i > 0" style="width:1.5rem;height:1.5rem;border-radius:0.25rem;background:rgba(0,0,0,0.6);color:#fff;line-height:1">&uarr;</button>
                            <button type="button" @click="move(i,1)" x-show="i < items.length - 1" style="width:1.5rem;height:1.5rem;border-radius:0.25rem;background:rgba(0,0,0,0.6);color:#fff;line-height:1">&darr;</button>
                        </div>
                    </div>
                </template>
            </div>
            <template x-if="!items || !items.length">
                <div style="display:flex;height:5rem;align-items:center;justify-content:center;border:1px dashed rgba(120,120,120,0.4);border-radius:0.5rem;font-size:0.875rem;color:#9ca3af;margin-bottom:0.75rem">
                    No images selected
                </div>
            </template>

            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center">
                {{ $getAction('browse') }}
                {{ $getAction('upload') }}
                {{ $getAction('clear') }}
            </div>
        </div>
    @else
        {{-- Single image --}}
        @php $state = $getState(); @endphp
        <div style="display:flex;align-items:flex-start;gap:1rem">
            <div style="flex-shrink:0">
                @if ($state)
                    <img src="{{ media_url($state) }}" alt="" style="height:7rem;width:7rem;object-fit:cover;border-radius:0.5rem;border:{{ $imgBorder }};display:block" />
                @else
                    <div style="display:flex;height:7rem;width:7rem;align-items:center;justify-content:center;text-align:center;border:1px dashed rgba(120,120,120,0.4);border-radius:0.5rem;font-size:0.75rem;color:#9ca3af">
                        No image<br>selected
                    </div>
                @endif
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;align-items:center;padding-top:0.25rem">
                {{ $getAction('browse') }}
                {{ $getAction('upload') }}
                {{ $getAction('clear') }}
            </div>
        </div>
    @endif
</x-dynamic-component>
