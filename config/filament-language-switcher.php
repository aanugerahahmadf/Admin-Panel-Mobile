<?php

/** @return array<string, mixed> */

return [
    /*
     |--------------------------------------------------------------------------
     | Locals
     |--------------------------------------------------------------------------
     |
     | add the locals that will be show on the languages selector
     |
     */
    'locals' => [
        'id' => ['flag' => 'id', 'label' => 'Bahasa Indonesia'],
        'en' => ['flag' => 'gb', 'label' => 'English (UK)'],
        'en_US' => ['flag' => 'us', 'label' => 'English (US)'],
        'ar' => ['flag' => 'sa', 'label' => 'العربية'],
        'de' => ['flag' => 'de', 'label' => 'Deutsch'],
        'es' => ['flag' => 'es', 'label' => 'Español'],
        'fr' => ['flag' => 'fr', 'label' => 'Français'],
        'it' => ['flag' => 'it', 'label' => 'Italiano'],
        'ja' => ['flag' => 'jp', 'label' => '日本語'],
        'ko' => ['flag' => 'kr', 'label' => '한국어'],
        'zh' => ['flag' => 'cn', 'label' => '中文'],
        'ru' => ['flag' => 'ru', 'label' => 'Русский'],
    ],

    /*
     |--------------------------------------------------------------------------
     | Show Flags
     |--------------------------------------------------------------------------
     |
     | Show flags on the language selector
     |
     */
    'show_flags' => true,

    /*
    |--------------------------------------------------------------------------
    |
    | Determines the render hook for the language switcher.
    | Available render hooks: https://filamentphp.com/docs/3.x/support/render-hooks#available-render-hooks
    |
    */

    'language_switcher_render_hook' => 'panels::user-menu.before',

    /*
     |--------------------------------------------------------------------------
     |
     | Language Switch Middlewares
     |
     */
    'language_switcher_middlewares' => [
        'web', 'mobile',
    ],

    /*
    |--------------------------------------------------------------------------
    | Redirect
    |--------------------------------------------------------------------------
    |
    | set the redirect path when change the language between selected path or next request
    |
    */
    'redirect' => 'next',

    /*
    |--------------------------------------------------------------------------
    | User Language Table
    |--------------------------------------------------------------------------
    |
    | set the user language table to store the user language, if your model don't have lang field
    |
    */
    'allow_user_lang_table' => true,
];
