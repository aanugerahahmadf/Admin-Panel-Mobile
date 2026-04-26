<div class="flex flex-col items-center justify-center gap-4 py-4 social-login-container w-full mt-2">
    <div class="flex items-center w-full gap-2 text-sm text-gray-500 divider opacity-60">
        <div class="grow border-t border-gray-300 dark:border-gray-700"></div>
        <span class="px-2 font-medium tracking-widest uppercase">{{ __('ATAU') }}</span>
        <div class="grow border-t border-gray-300 dark:border-gray-700"></div>
    </div>

    <div class="flex flex-wrap items-center justify-center w-full gap-4">
        <x-filament::button 
            tag="button"
            type="button"
            color="gray" 
            outlined
            class="flex-1 w-full transition-all duration-100 transform active:scale-[0.98] shadow-sm hover:shadow-md"
            x-data="{ 
                loading: false,
                login() {
                    this.loading = true;
                    window.location.href = '{{ route('auth.redirect', 'google') }}';
                }
            }"
            x-on:mousedown="login()"
            x-on:keydown.enter="login()"
            x-bind:class="{ 'opacity-50 pointer-events-none': loading }"
        >
            <div class="flex items-center justify-center gap-3">
                <template x-if="loading">
                    <x-filament::loading-indicator class="h-5 w-5" />
                </template>
                <template x-if="!loading">
                    <svg class="h-5 w-5" viewBox="0 0 48 48">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24s.92 7.54 2.56 10.78l7.97-6.19z" />
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.31-8.16 2.31-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
                    </svg>
                </template>
                <span x-text="loading ? '{{ __('Menghubungkan...') }}' : '{{ __('Masuk Dengan Google') }}'">
                    {{ __('Masuk Dengan Google') }}
                </span>
            </div>
        </x-filament::button>
    </div>

    @php
        $termsRecord = \App\Models\TermsOfService::first();
        $privacyRecord = \App\Models\PrivacyPolicy::first();
    @endphp

    <div class="mt-4 text-center">
        {{-- Modal Syarat & Ketentuan --}}
        <x-filament::modal id="terms-modal" width="3xl" slide-over>
            <x-slot name="trigger">
                <x-filament::link size="xs" color="warning" class="cursor-pointer">
                    {{ __('Syarat & Ketentuan') }}
                </x-filament::link>
            </x-slot>

            <x-slot name="heading">
                <span class="text-xl font-bold text-primary-600 dark:text-primary-500">
                    {{ __($termsRecord?->title ?? 'Syarat & Ketentuan') }}
                </span>
            </x-slot>

            <div class="space-y-6 text-left py-2">
                @forelse ($termsRecord?->content ?? [] as $i => $item)
                    <article class="space-y-2">
                        <h3 class="text-sm font-bold uppercase tracking-wide text-gray-900 dark:text-gray-100">
                            {{ $i + 1 }}. {{ __($item['heading']) }}
                        </h3>
                        <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400 text-justify">
                            {!! nl2br(e(__($item['body']))) !!}
                        </p>
                    </article>
                @empty
                    <p class="text-sm text-gray-400 italic">{{ __('Konten belum tersedia.') }}</p>
                @endforelse
            </div>
        </x-filament::modal>

        <span class="text-xs text-gray-500 mx-1">{{ __('&') }}</span>

        {{-- Modal Kebijakan Privasi --}}
        <x-filament::modal id="privacy-modal" width="3xl" slide-over>
            <x-slot name="trigger">
                <x-filament::link size="xs" color="warning" class="cursor-pointer">
                    {{ __('Kebijakan Privasi') }}
                </x-filament::link>
            </x-slot>

            <x-slot name="heading">
                <span class="text-xl font-bold text-primary-600 dark:text-primary-500">
                    {{ __($privacyRecord?->title ?? 'Kebijakan Privasi') }}
                </span>
            </x-slot>

            <div class="space-y-6 text-left py-2">
                @forelse ($privacyRecord?->content ?? [] as $i => $item)
                    <article class="space-y-2">
                        <h3 class="text-sm font-bold uppercase tracking-wide text-gray-900 dark:text-gray-100">
                            {{ $i + 1 }}. {{ __($item['heading']) }}
                        </h3>
                        <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400 text-justify">
                            {!! nl2br(e(__($item['body']))) !!}
                        </p>
                    </article>
                @empty
                    <p class="text-sm text-gray-400 italic">{{ __('Konten belum tersedia.') }}</p>
                @endforelse
            </div>
        </x-filament::modal>

        <div class="mt-1 text-xs text-gray-500">
            {{ __('Dengan login, kamu menyetujui kebijakan penyelenggara.') }}
        </div>
    </div>
</div>