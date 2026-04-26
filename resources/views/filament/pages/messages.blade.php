<x-filament-panels::page>
    @php
        $panelId = filament()->getCurrentPanel()->getId();
    @endphp
    <div class="flex flex-col lg:flex-row gap-6 w-full overflow-hidden" style="height: calc(100vh - 12rem);">
        @if($panelId === 'admin')
            {{-- Inbox List --}}
            <div @class([
                'w-full h-full',
                'hidden' => $selectedConversation,
                'block' => !$selectedConversation,
            ])>
                <livewire:fm-inbox :selectedConversation="$selectedConversation" />
            </div>

            {{-- Message Content --}}
            <div @class([
                'flex-1 min-w-0 h-full',
                'block' => $selectedConversation,
                'hidden' => !$selectedConversation,
            ])>
                <livewire:fm-messages :selectedConversation="$selectedConversation" />
            </div>

        @else
            <div class="flex-1 min-w-0 h-full">
                <livewire:fm-messages :selectedConversation="$selectedConversation" />
            </div>
        @endif
    </div>
</x-filament-panels::page>


