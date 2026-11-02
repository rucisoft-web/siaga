<x-filament-panels::page>
    <form wire:submit="save" class="arah" style="display:flex; flex-direction:column; gap:1rem">
        {{ $this->form }}

        <div class="arah-card" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:1rem">
            <div>
                <div class="arah-muted" style="text-transform:uppercase; letter-spacing:.12em; font-size:.66rem; font-weight:700">Pratinjau logo teks</div>
                <div style="margin-top:.5rem">{!! \App\Support\Branding::textLogo() !!}</div>
            </div>
            <x-filament::button type="submit" icon="heroicon-o-check">Simpan profil</x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
