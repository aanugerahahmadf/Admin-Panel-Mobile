<x-filament-panels::page.simple>
    <x-slot name="subheading">
        {{ $this->loginAction }}
    </x-slot>
    <x-filament-panels::form id="form" wire:submit="verify">
        {{ $this->form }}

        <div class="flex justify-center mt-2">
            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </div>
    </x-filament-panels::form>
</x-filament-panels::page.simple>
