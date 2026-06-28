<x-filament-panels::page.simple>
    <x-filament-panels::form wire:submit="verify">
        {{ $this->form }}

        <div class="flex justify-center mt-2">
            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </div>
    </x-filament-panels::form>

    <div class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400"
         x-data="{
             timeLeft: @js($resendCooldown),
             interval: null,
             
             formatTime(seconds) {
                 let m = Math.floor(seconds / 60);
                 let s = seconds % 60;
                 return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
             },
             startTimer() {
                 clearInterval(this.interval);
                 if (this.timeLeft > 0) {
                     this.interval = setInterval(() => {
                         if (this.timeLeft > 0) {
                             this.timeLeft--;
                         } else {
                             clearInterval(this.interval);
                         }
                     }, 1000);
                 }
             },
             init() {
                 this.startTimer();
             }
         }"
         x-on:otp-resent.window="timeLeft = 300; startTimer();"
    >
        <p class="mb-4">{{ __('Tidak menerima email?') }}</p>

        <template x-if="timeLeft > 0">
            <div class="flex items-center justify-center gap-2 text-primary-600 font-medium">
                <x-filament::loading-indicator class="h-4 w-4" />
                <span>{{ __('Kirim ulang tersedia dalam') }} <span x-text="formatTime(timeLeft)"></span></span>
            </div>
        </template>

        <div x-show="timeLeft <= 0">
            {{ $this->resendNotificationAction }}
        </div>
    </div>
</x-filament-panels::page.simple>
