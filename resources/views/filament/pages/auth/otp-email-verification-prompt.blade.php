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
             resendInText: @js(__('Resend in')),
             resendCodeText: @js(__('Resend code')),
             
             startTimer() {
                 clearInterval(this.interval);
                 
                 let updateText = () => {
                     let btn = this.$el.querySelector('button');
                     let labelEl = this.$el.querySelector('.fi-btn-label');
                     
                     if (labelEl) {
                         if (this.timeLeft > 0) {
                             let m = Math.floor(this.timeLeft / 60);
                             let s = this.timeLeft % 60;
                             let timeStr = String(m).padStart(2, '0') + '.' + String(s).padStart(2, '0');
                             labelEl.innerText = this.resendInText + ' ' + timeStr;
                         } else {
                             labelEl.innerText = this.resendCodeText;
                         }
                     }
                     
                     if (btn) {
                         if (this.timeLeft > 0) {
                             btn.classList.add('pointer-events-none', 'opacity-50');
                             btn.disabled = true;
                         } else {
                             btn.classList.remove('pointer-events-none', 'opacity-50');
                             btn.disabled = false;
                         }
                     }
                 };
                 
                 updateText();
                 if (this.timeLeft > 0) {
                     this.interval = setInterval(() => {
                         if (this.timeLeft > 0) {
                             this.timeLeft--;
                             updateText();
                         } else {
                             clearInterval(this.interval);
                             updateText();
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
        {{ __('Tidak menerima email?') }} <br><br>
        {{ $this->resendNotificationAction }}
    </div>
</x-filament-panels::page.simple>
