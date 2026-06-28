<x-filament-panels::page.simple>
    {{-- Custom: Hidden the default login link area --}}
    {{-- <x-slot name="subheading">
        {{ __('filament-panels::pages/auth/register.actions.login.before') }}
        {{ $this->loginAction }}
    </x-slot> --}}

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_REGISTER_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <style>
        /* ── Wizard step headers: only show current + completed steps ── */
        .fi-fo-wizard-header li.fi-fo-wizard-header-step {
            opacity: 0 !important; pointer-events: none !important;
            width: 0 !important; overflow: hidden !important;
            padding: 0 !important; margin: 0 !important;
            flex: none !important; min-width: 0 !important;
        }
        .fi-fo-wizard-header li.fi-fo-wizard-header-step[aria-current="step"] {
            opacity: 1 !important; pointer-events: auto !important;
            width: auto !important; overflow: visible !important;
            padding: revert !important; margin: revert !important;
            flex: 1 !important; min-width: 0 !important;
        }
        .fi-fo-wizard-header:has(li[aria-current="step"])
            li.fi-fo-wizard-header-step:not([aria-current="step"]) {
            opacity: 0 !important; pointer-events: none !important;
            width: 0 !important; overflow: hidden !important;
            padding: 0 !important; margin: 0 !important; flex: none !important;
        }
        .fi-fo-wizard-header li.fi-fo-wizard-header-step:has(~ li[aria-current="step"]) {
            opacity: 1 !important; pointer-events: none !important;
            width: auto !important; overflow: visible !important;
            padding: revert !important; margin: revert !important; flex: 1 !important;
        }
        .fi-fo-wizard-header:has(li[aria-current="step"]) li.fi-fo-wizard-header-step-separator {
            opacity: 0 !important; width: 0 !important; overflow: hidden !important;
            padding: 0 !important; margin: 0 !important; flex: none !important;
        }
        .fi-fo-wizard-header li.fi-fo-wizard-header-step:has(~ li[aria-current="step"])
            + li.fi-fo-wizard-header-step-separator {
            opacity: 1 !important; width: auto !important; overflow: visible !important;
            padding: revert !important; margin: revert !important; flex: revert !important;
        }

        /* ── Profile picture label: center-align ── */
        .avatar-upload-centered > .fi-fo-field-wrp-label {
            display: flex !important; justify-content: center !important;
            text-align: center !important; width: 100% !important;
        }
        .avatar-upload-centered > .fi-fo-field-wrp-label label {
            text-align: center !important; width: 100% !important;
        }

        /* ── Avatar wrapper — intercept clicks ── */
        .avatar-upload-centered {
            position: relative;
            cursor: pointer;
        }
        /* Block FilePond's own click-to-pick so our modal handles it */
        .avatar-upload-centered .filepond--root,
        .avatar-upload-centered .filepond--drop-label {
            pointer-events: none !important;
        }
    </style>

    {{-- Attach click listener to .avatar-upload-centered after DOM is ready --}}
    <div
        x-data="{}"
        x-init="
            $nextTick(() => {
                const wrapper = document.querySelector('.avatar-upload-centered');
                if (!wrapper) return;
                wrapper.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    window.dispatchEvent(new CustomEvent('open-avatar-browse'));
                });
            });
        "
        class="hidden"
    ></div>

    {{-- Avatar browse modal (same style as cbir-browse-modal) --}}
    @include('filament.user.components.avatar-browse-modal', [
        'pondSelector' => '.avatar-upload-centered',
    ])

    <x-filament-panels::form id="form" wire:submit="register">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    @include('filament.user.social-buttons')

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_REGISTER_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>
