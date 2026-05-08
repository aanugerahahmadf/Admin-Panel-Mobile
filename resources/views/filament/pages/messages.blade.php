<x-filament-panels::page>
    @php
        $panelId = filament()->getCurrentPanel()->getId();
    @endphp

    <style>
        .messages-container {
            height: calc(100vh - 12rem);
            height: calc(100dvh - 12rem);
        }

        @media (max-width: 1024px) {
            .messages-container {
                height: calc(100vh - 9rem);
                height: calc(100dvh - 9rem);
            }
        }

        @media (max-width: 640px) {
            .messages-container {
                height: calc(100vh - 7rem);
                height: calc(100dvh - 7rem);
            }
        }
    </style>

    <div id="messages-container" class="messages-container flex flex-col lg:flex-row gap-6 w-full overflow-hidden">
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

    <script>
        (function () {
            function fitMessagesContainer() {
                var container = document.getElementById('messages-container');
                if (!container) return;

                var rect = container.getBoundingClientRect();

                // visualViewport gives accurate height on mobile (handles keyboard, browser chrome)
                var viewportHeight = window.visualViewport
                    ? window.visualViewport.height
                    : window.innerHeight;

                var height = viewportHeight - rect.top - 16;
                container.style.height = Math.max(height, 300) + 'px';
            }

            document.addEventListener('DOMContentLoaded', fitMessagesContainer);
            window.addEventListener('resize', fitMessagesContainer);

            if (window.visualViewport) {
                window.visualViewport.addEventListener('resize', fitMessagesContainer);
                window.visualViewport.addEventListener('scroll', fitMessagesContainer);
            }

            document.addEventListener('livewire:navigated', function () {
                setTimeout(fitMessagesContainer, 50);
            });

            setTimeout(fitMessagesContainer, 100);
            setTimeout(fitMessagesContainer, 500);
        })();
    </script>
</x-filament-panels::page>
