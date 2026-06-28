<?php

namespace App\Support\Csp;

use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

class MidtransPreset implements Preset
{
    public function configure(Policy $policy): void
    {
        $policy
            ->add(Directive::BASE, Keyword::SELF)
            ->add(Directive::CONNECT, Keyword::SELF)
            ->add(Directive::DEFAULT, Keyword::SELF)
            ->add(Directive::FONT, Keyword::SELF)
            ->add(Directive::FORM_ACTION, Keyword::SELF)
            ->add(Directive::FRAME, Keyword::SELF)
            ->add(Directive::IMG, Keyword::SELF)
            ->add(Directive::MEDIA, Keyword::SELF)
            ->add(Directive::OBJECT, Keyword::NONE)
            ->add(Directive::SCRIPT, Keyword::SELF)
            ->add(Directive::STYLE, Keyword::SELF)
            // Add Midtrans specific directives
            ->add(Directive::SCRIPT, Keyword::UNSAFE_EVAL)
            ->add(Directive::SCRIPT, Keyword::UNSAFE_INLINE)
            ->add(Directive::SCRIPT, 'https://app.midtrans.com')
            ->add(Directive::SCRIPT, 'https://app.sandbox.midtrans.com')
            ->add(Directive::SCRIPT, 'https://snap-popup-app.sandbox.midtrans.com')
            ->add(Directive::SCRIPT, 'https://snap-popup-app.midtrans.com')
            ->add(Directive::SCRIPT, '*.midtrans.com')
            ->add(Directive::SCRIPT, '*.google.com')
            ->add(Directive::SCRIPT, '*.googleapis.com')
            ->add(Directive::FRAME, 'https://app.midtrans.com')
            ->add(Directive::FRAME, 'https://app.sandbox.midtrans.com')
            ->add(Directive::FRAME, 'https://snap-popup-app.sandbox.midtrans.com')
            ->add(Directive::FRAME, 'https://snap-popup-app.midtrans.com')
            ->add(Directive::FRAME, '*.midtrans.com')
            ->add(Directive::CONNECT, 'https://app.midtrans.com')
            ->add(Directive::CONNECT, 'https://app.sandbox.midtrans.com')
            ->add(Directive::CONNECT, '*.midtrans.com')
            ->add(Directive::IMG, '*.midtrans.com')
            ->add(Directive::IMG, 'data:')
            ->add(Directive::IMG, 'blob:');
    }
}
