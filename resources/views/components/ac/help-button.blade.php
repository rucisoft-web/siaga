@php $key = \App\Support\Help::current(); @endphp
@if ($key)
    @php $h = \App\Support\Help::get($key); @endphp
    <div x-data="{ open: false }" x-on:keydown.escape.window="open = false" class="ac-help">
        <button type="button" class="ac-help-btn" x-on:click="open = true" title="Panduan layar ini" aria-label="Panduan layar ini">
            <span class="q">?</span><span class="t">Panduan</span>
        </button>
        <template x-teleport="body">
            <div class="ac-help-layer" x-show="open" x-cloak x-transition.opacity>
                <div class="ac-help-backdrop" x-on:click="open = false"></div>
                <aside class="ac-help-panel" role="dialog" aria-modal="true" aria-label="Panduan: {{ $h['title'] }}" x-show="open" x-transition:enter-start="off" x-transition:leave-end="off">
                    <header>
                        <div><small>Panduan</small><b>{{ $h['title'] }}</b></div>
                        <button type="button" x-on:click="open = false" aria-label="Tutup">✕</button>
                    </header>
                    <div class="ac-help-body">{!! $h['html'] !!}</div>
                    <footer>
                        <a href="{{ \App\Filament\Pages\Panduan::getUrl() }}#{{ $key }}">Semua panduan</a>
                        <a href="{{ \App\Filament\Pages\Panduan::getUrl(['baca' => $key]) }}" target="_blank">Buka halaman penuh</a>
                    </footer>
                </aside>
            </div>
        </template>
    </div>
@endif
