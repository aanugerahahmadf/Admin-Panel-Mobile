<x-filament-panels::page>
    @php
        $panelId = filament()->getCurrentPanel()->getId();
    @endphp

    <style>
        /*
         * Initial CSS height — overridden immediately by JS after paint.
         * These are just fallback values to prevent layout flash.
         * Desktop: topbar(fixed,~56px) + page-heading(~48px) + footer(~40px) + gaps
         * Mobile : topbar + bottom-nav(64px) + safe-area
         */
        .messages-container {
            height: calc(100dvh - 13rem);
            min-height: 300px;
        }
        @media (max-width: 1023px) {
            .messages-container { height: calc(100dvh - 10rem); }
        }
        @media (max-width: 639px) {
            .messages-container { height: calc(100dvh - 8rem); }
        }

        /*
         * On mobile the messages page must NOT have the generic .fi-main
         * padding-bottom that other pages use (that padding is for normal
         * scrollable pages). The messages container has its own height
         * calculation, and double-padding causes the input bar to be buried.
         * We remove it here so the JS calculation is the single source of truth.
         */
        @media (max-width: 1023px) {
            .fi-panel-user .fi-main:has(#messages-container) {
                padding-bottom: 0 !important;
            }
            /* Also remove the fi-main-ctn padding that the bottom-nav style injects */
            .fi-main-ctn:has(#messages-container) {
                padding-bottom: 0 !important;
            }
        }
    </style>

    <div id="messages-container" class="messages-container flex flex-col lg:flex-row gap-6 w-full overflow-hidden">
        @if($panelId === 'admin')
            {{-- Inbox List --}}
            <div @class([
                'w-full h-full min-h-0',
                'hidden' => $selectedConversation,
                'block'  => !$selectedConversation,
            ])>
                <livewire:fm-inbox :selectedConversation="$selectedConversation" />
            </div>

            {{-- Message Content --}}
            <div @class([
                'flex-1 min-w-0 h-full min-h-0',
                'block'  => $selectedConversation,
                'hidden' => !$selectedConversation,
            ])>
                <livewire:fm-messages :selectedConversation="$selectedConversation" />
            </div>

        @else
            <div class="flex-1 min-w-0 h-full min-h-0">
                <livewire:fm-messages :selectedConversation="$selectedConversation" />
            </div>
        @endif
    </div>

    <script>
        (function () {
            function fitMessagesContainer() {
                var container = document.getElementById('messages-container');
                if (!container) return;

                // visualViewport is the only reliable height on mobile — it
                // accounts for the on-screen keyboard, browser chrome, and
                // safe-area insets shrinking the visible area.
                var viewportHeight = window.visualViewport
                    ? window.visualViewport.height
                    : window.innerHeight;

                // rect.top = distance from the TOP of the visible viewport to
                // the top of the container — already includes topbar height,
                // page-heading height, and any Filament wrapper padding.
                // Works correctly even with position:fixed topbar because the
                // fixed topbar is NOT in the document flow and the Filament
                // layout adds padding-top equal to its own height.
                var rect = container.getBoundingClientRect();

                var isDesktop = window.innerWidth >= 1024;

                var bottomClearance;
                if (isDesktop) {
                    // Footer height (~40px) + a little breathing room
                    var footer = document.querySelector('.fi-footer') || document.querySelector('footer');
                    var footerH = footer ? footer.getBoundingClientRect().height : 0;
                    bottomClearance = footerH + 16;
                } else {
                    // Bottom-nav (64px) + CSS safe-area-inset-bottom
                    // Read the actual safe-area value from CSS env() via a dummy element
                    var safeBottom = 0;
                    try {
                        var probe = document.getElementById('_sai_probe');
                        if (!probe) {
                            probe = document.createElement('div');
                            probe.id = '_sai_probe';
                            probe.style.cssText = 'position:fixed;bottom:0;height:env(safe-area-inset-bottom,0px);width:0;pointer-events:none;visibility:hidden;';
                            document.body.appendChild(probe);
                        }
                        safeBottom = probe.getBoundingClientRect().height || 0;
                    } catch(e) {}
                    bottomClearance = 64 + safeBottom + 8; // bottom-nav + safe-area + gap
                }

                var height = viewportHeight - rect.top - bottomClearance;
                container.style.height = Math.max(height, 280) + 'px';
            }

            function schedulefit() {
                requestAnimationFrame(function () {
                    requestAnimationFrame(fitMessagesContainer);
                });
            }

            document.addEventListener('DOMContentLoaded', schedulefit);
            window.addEventListener('resize', fitMessagesContainer);

            if (window.visualViewport) {
                window.visualViewport.addEventListener('resize', fitMessagesContainer);
                window.visualViewport.addEventListener('scroll', fitMessagesContainer);
            }

            document.addEventListener('livewire:navigated', schedulefit);

            // Fallback for slow hydration
            setTimeout(fitMessagesContainer, 200);
            setTimeout(fitMessagesContainer, 600);
        })();
    </script>
</x-filament-panels::page>
