@php
    use App\Providers\NativeServiceProvider;
    // Pakai path relatif untuk semua platform — WebView dan browser sama-sama handle ini.
    // normalizeUrl() tidak dipakai untuk navigasi halaman agar tidak buka Chrome di mobile.
    $googleRedirectUrl = '/auth/google/redirect';
    $registerUrl       = '/user/register';
    $loginUrl          = '/user/login';
@endphp
<div x-data="{ 
    agreed: $wire.entangle('data.agreement'), 
    remembered: $wire.entangle('data.remember'), 
    loading: false,
    googleUrl: '{{ $googleRedirectUrl }}'
}" x-effect="
        $nextTick(function() {
            let form = $el.closest('form');
            if (form && !form.dataset.validationBound) {
                form.dataset.validationBound = 'true';
                form.addEventListener('submit', function(e) {
                    if (!(agreed && remembered)) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        
                        new FilamentNotification()
                            .title('{{ __('Perhatian') }}')
                            .body('{{ __('Silakan centang opsi Ingat Saya dan Setujui Syarat & Ketentuan untuk melanjutkan.') }}')
                            .warning()
                            .send();
                    }
                });
            }
        })
    " class="flex flex-col items-center justify-center gap-0 py-0 social-login-container w-full">
    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .fi-page-subheading,
        [class*="fi-page-subheading"],
        .fi-simple-page header+div {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
    </style>

    <div class="flex flex-wrap items-center justify-center w-full gap-0 mt-2">
        <button type="button"
            x-bind:class="!(agreed && remembered) ? 'opacity-40 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700 active:scale-[0.98]'"
            x-on:click="
                if (!(agreed && remembered)) return;
                loading = true;
                window.location.href = googleUrl;
            "
            x-bind:disabled="!(agreed && remembered) || loading"
            class="flex items-center justify-center gap-3 w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 text-sm font-semibold shadow-sm transition-all duration-100">
            {{-- x-show dipakai bukan x-if/template — template tag tidak render di Android WebView lama --}}
            <svg x-show="loading" class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display:none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <svg x-show="!loading" class="h-5 w-5" viewBox="0 0 48 48">
                <path fill="#EA4335"
                    d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
                <path fill="#4285F4"
                    d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
                <path fill="#FBBC05"
                    d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24s.92 7.54 2.56 10.78l7.97-6.19z" />
                <path fill="#34A853"
                    d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
            </svg>
            <span x-text="loading ? '{{ __('Menghubungkan...') }}' : '{{ __('Masuk Dengan Google') }}'">{{ __('Masuk Dengan Google') }}</span>
        </button>
    </div>

    <div class="w-full flex flex-col gap-3 mt-4 px-1">
        <div class="flex items-center gap-3 group">
            <input type="checkbox" id="remember-me-checkbox" name="remember" x-model="remembered"
                class="fi-checkbox rounded border-gray-300 text-primary-500 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:checked:bg-primary-500 transition duration-75 cursor-pointer" />
            <label for="remember-me-checkbox"
                class="text-xs font-semibold text-gray-500 dark:text-gray-400 leading-relaxed cursor-pointer select-none">
                {{ __('Ingat Saya') }}
            </label>
        </div>

        <div class="flex items-start gap-3 group">
            <div class="pt-0.5">
                <input type="checkbox" id="agreement-checkbox" name="agreement" x-model="agreed"
                    x-on:change="if(agreed) $dispatch('open-agreement', { mode: 'wizard', step: 1 })"
                    class="fi-checkbox rounded border-gray-300 text-primary-500 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:checked:bg-primary-500 transition duration-75 cursor-pointer" />
            </div>
            <div class="text-xs font-semibold text-justify leading-relaxed text-gray-500 dark:text-gray-400 select-none"
                style="text-justify: inter-word; -webkit-hyphens: auto; hyphens: auto;">
                <label for="agreement-checkbox"
                    class="cursor-pointer">{{ __('Dengan mencentang Setuju & Bergabung atau Lanjutkan, Anda menyetujui') }}</label>
                <x-filament::link color="primary" tag="button" type="button"
                    class="text-xs font-bold hover:underline focus:underline active:underline"
                    @click.stop="$dispatch('open-agreement', { mode: 'terms' })">{{ __('Perjanjian Pengguna') }}</x-filament::link>,
                <x-filament::link color="primary" tag="button" type="button"
                    class="text-xs font-bold hover:underline focus:underline active:underline"
                    @click.stop="$dispatch('open-agreement', { mode: 'privacy' })">{{ __('Kebijakan Privasi') }}</x-filament::link>
                <span>{{ __('dan Kebijakan Wedding Flowers Decorasi.') }}</span>
            </div>
        </div>
    </div>

    <div class="w-full text-center mt-4">
        @if (request()->routeIs('filament.user.auth.login'))
            <p class="text-[13px] text-gray-500 font-medium">
                {{ __('Belum punya akun?') }}
                <x-filament::link :href="$registerUrl" color="primary"
                    class="text-[13px] font-bold hover:underline focus:underline active:underline transition-all">
                    {{ __('Daftar') }}
                </x-filament::link>
            </p>
        @elseif (request()->routeIs('filament.user.auth.register'))
            <p class="text-[13px] text-gray-500 font-medium">
                {{ __('Sudah punya akun?') }}
                <x-filament::link :href="$loginUrl" color="primary"
                    class="text-[13px] font-bold hover:underline focus:underline active:underline transition-all">
                    {{ __('Masuk') }}
                </x-filament::link>
            </p>
        @endif
    </div>

    @php
        try {
            $termsRecord = \App\Models\TermsOfService::first();
            $privacyRecord = \App\Models\PrivacyPolicy::first();
        } catch (\Throwable $e) {
            $termsRecord = null;
            $privacyRecord = null;
        }
    @endphp

    <x-filament::modal id="agreement-modal" width="3xl" :close-by-clicking-away="false" :close-button="false">
        <div x-data="{ step: 1, mode: 'wizard' }"
            x-on:open-agreement.window="mode = $event.detail.mode || 'wizard'; step = $event.detail.step || 1; $dispatch('open-modal', { id: 'agreement-modal' })"
            class="relative overflow-hidden min-h-[450px]">

            {{-- Terms of Service Content --}}
            <div x-show="mode === 'terms' || (mode === 'wizard' && step === 1)"
                x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0 translate-x-full" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-500 absolute w-full"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 -translate-x-full" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-primary-500">
                        {{ __($termsRecord?->title ?? 'Perjanjian Pengguna') }}</h2>
                    <template x-if="mode === 'wizard'">
                        <span class="text-xs font-bold text-gray-400">{{ __('Langkah :step dari :total', ['step' => 1, 'total' => 2]) }}</span>
                    </template>
                </div>

                <div class="space-y-6 text-left py-2 max-h-[60vh] overflow-y-auto no-scrollbar">
                    @forelse ($termsRecord?->content ?? [] as $i => $item)
                        <article class="space-y-2">
                            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-900 dark:text-gray-100">
                                {{ $i + 1 }}. {{ __($item['heading']) }}</h3>
                            <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400 text-justify">
                                {!! nl2br(e(__($item['body']))) !!}</p>
                        </article>
                    @empty
                        <p class="text-sm text-gray-400 italic">{{ __('Konten belum tersedia.') }}</p>
                    @endforelse
                </div>

                <div class="flex justify-end pt-2 gap-3">
                    <template x-if="mode === 'wizard'">
                        <x-filament::button color="primary" @click="step = 2">
                            {{ __('Lanjutkan') }}
                        </x-filament::button>
                    </template>
                    <template x-if="mode === 'terms'">
                        <x-filament::button color="gray" outlined
                            @click="$dispatch('close-modal', { id: 'agreement-modal' })">
                            {{ __('Tutup') }}
                        </x-filament::button>
                    </template>
                </div>
            </div>

            {{-- Privacy Policy Content --}}
            <div x-show="mode === 'privacy' || (mode === 'wizard' && step === 2)"
                x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0 translate-x-full" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-500 absolute w-full"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 -translate-x-full" class="space-y-4" style="display: none;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <template x-if="mode === 'wizard'">
                            <button @click="step = 1" class="text-gray-400 hover:text-primary-500 transition-colors">
                                <x-heroicon-m-arrow-left class="w-6 h-6" />
                            </button>
                        </template>
                        <h2 class="text-xl font-bold text-primary-500">
                            {{ __($privacyRecord?->title ?? 'Kebijakan Privasi') }}</h2>
                    </div>
                    <template x-if="mode === 'wizard'">
                        <span class="text-xs font-bold text-gray-400">{{ __('Langkah :step dari :total', ['step' => 2, 'total' => 2]) }}</span>
                    </template>
                </div>

                <div class="space-y-6 text-left py-2 max-h-[60vh] overflow-y-auto no-scrollbar">
                    @forelse ($privacyRecord?->content ?? [] as $i => $item)
                        <article class="space-y-2">
                            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-900 dark:text-gray-100">
                                {{ $i + 1 }}. {{ __($item['heading']) }}</h3>
                            <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400 text-justify">
                                {!! nl2br(e(__($item['body']))) !!}</p>
                        </article>
                    @empty
                        <p class="text-sm text-gray-400 italic">{{ __('Konten belum tersedia.') }}</p>
                    @endforelse
                </div>

                <div class="flex justify-end pt-2 gap-3">
                    <template x-if="mode === 'wizard'">
                        <x-filament::button color="primary"
                            @click="agreed = true; $dispatch('close-modal', { id: 'agreement-modal' })">
                            {{ __('Saya Mengerti & Setuju') }}
                        </x-filament::button>
                    </template>
                    <template x-if="mode === 'privacy'">
                        <x-filament::button color="gray" outlined
                            @click="$dispatch('close-modal', { id: 'agreement-modal' })">
                            {{ __('Tutup') }}
                        </x-filament::button>
                    </template>
                </div>
            </div>
        </div>
    </x-filament::modal>
</div>