<div>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" wire:loading.remove wire:target="save" class="w-full">
                {{ __('Simpan & Lanjutkan') }}
            </x-filament::button>
            <div wire:loading wire:target="save" class="w-full">
                <x-filament::button type="button" disabled class="w-full">
                    {{ __('Menyimpan...') }}
                </x-filament::button>
            </div>
        </div>
    </form>
</div>
