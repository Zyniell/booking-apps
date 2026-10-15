<x-filament-panels::page>
    <form wire:submit.prevent="save">
        {{ $this->form }}
        <div style="margin-top: 1rem">
            <x-filament::button type="submit">
                Simpan
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
