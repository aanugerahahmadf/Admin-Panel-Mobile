<x-filament-panels::page
    @if (method_exists($this, 'getHeaderWidgets'))
        :header-widgets="$this->getHeaderWidgets()"
    @endif
>
    @livewire('complete-profile-component')
</x-filament-panels::page>
