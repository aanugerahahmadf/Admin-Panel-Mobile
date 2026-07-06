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
        'ms' => ['flag' => 'my', 'label' => 'Bahasa Melayu'],
        'en' => ['flag' => 'gb', 'label' => 'English (UK)'],
        'en_US' => ['flag' => 'us', 'label' => 'English (US)'],
        'zh' => ['flag' => 'cn', 'label' => '中文'],
        'zh_CN' => ['flag' => 'cn', 'label' => '中文 (简体)'],
        'zh_TW' => ['flag' => 'tw', 'label' => '中文 (繁體)'],
        'ar' => ['flag' => 'sa', 'label' => 'العربية'],
        'ja' => ['flag' => 'jp', 'label' => '日本語'],
        'ko' => ['flag' => 'kr', 'label' => '한국어'],
        'th' => ['flag' => 'th', 'label' => 'ไทย'],
        'vi' => ['flag' => 'vn', 'label' => 'Tiếng Việt'],
        'hi' => ['flag' => 'in', 'label' => 'हिन्दी'],
        'bn' => ['flag' => 'bd', 'label' => 'বাংলা'],
        'ur' => ['flag' => 'pk', 'label' => 'اردو'],
        'fa' => ['flag' => 'ir', 'label' => 'فارسی'],
        'pt' => ['flag' => 'pt', 'label' => 'Português'],
        'pt_BR' => ['flag' => 'br', 'label' => 'Português (Brasil)'],
        'pt_PT' => ['flag' => 'pt', 'label' => 'Português (Portugal)'],
        'es' => ['flag' => 'es', 'label' => 'Español'],
        'fr' => ['flag' => 'fr', 'label' => 'Français'],
        'de' => ['flag' => 'de', 'label' => 'Deutsch'],
        'it' => ['flag' => 'it', 'label' => 'Italiano'],
        'nl' => ['flag' => 'nl', 'label' => 'Nederlands'],
        'ru' => ['flag' => 'ru', 'label' => 'Русский'],
        'tr' => ['flag' => 'tr', 'label' => 'Türkçe'],
        'pl' => ['flag' => 'pl', 'label' => 'Polski'],
        'uk' => ['flag' => 'ua', 'label' => 'Українська'],
        'ro' => ['flag' => 'ro', 'label' => 'Română'],
        'cs' => ['flag' => 'cz', 'label' => 'Čeština'],
        'hu' => ['flag' => 'hu', 'label' => 'Magyar'],
        'el' => ['flag' => 'gr', 'label' => 'Ελληνικά'],
        'sv' => ['flag' => 'se', 'label' => 'Svenska'],
        'da' => ['flag' => 'dk', 'label' => 'Dansk'],
        'fi' => ['flag' => 'fi', 'label' => 'Suomi'],
        'no' => ['flag' => 'no', 'label' => 'Norsk'],
        'fil' => ['flag' => 'ph', 'label' => 'Filipino'],
        'my' => ['flag' => 'mm', 'label' => 'မြန်မာဘာသာ'],
        'km' => ['flag' => 'kh', 'label' => 'ភាសាខ្មែរ'],
        'he' => ['flag' => 'il', 'label' => 'עברית'],
        'sr' => ['flag' => 'rs', 'label' => 'Српски'],
        'hr' => ['flag' => 'hr', 'label' => 'Hrvatski'],
        'sk' => ['flag' => 'sk', 'label' => 'Slovenčina'],
        'bg' => ['flag' => 'bg', 'label' => 'Български'],
        'lt' => ['flag' => 'lt', 'label' => 'Lietuvių'],
        'lv' => ['flag' => 'lv', 'label' => 'Latviešu'],
        'et' => ['flag' => 'ee', 'label' => 'Eesti'],
        'sl' => ['flag' => 'si', 'label' => 'Slovenščina'],
        'sq' => ['flag' => 'al', 'label' => 'Shqip'],
        'bs' => ['flag' => 'ba', 'label' => 'Bosanski'],
        'hy' => ['flag' => 'am', 'label' => 'Հայերեն'],
        'ka' => ['flag' => 'ge', 'label' => 'ქართული'],
        'az' => ['flag' => 'az', 'label' => 'Azərbaycan'],
        'kk' => ['flag' => 'kz', 'label' => 'Қазақ'],
        'mn' => ['flag' => 'mn', 'label' => 'Монгол'],
        'ne' => ['flag' => 'np', 'label' => 'नेपाली'],
        'tl' => ['flag' => 'ph', 'label' => 'Tagalog'],
        'sw' => ['flag' => 'tz', 'label' => 'Kiswahili'],
        'am' => ['flag' => 'et', 'label' => 'አማርኛ'],
        'ca' => ['flag' => 'es_ct', 'label' => 'Català'],
        'eu' => ['flag' => 'es_pv', 'label' => 'Euskara'],
        'cy' => ['flag' => 'gb_wls', 'label' => 'Cymraeg'],
        'uz' => ['flag' => 'uz', 'label' => "O'zbek"],
        'ku' => ['flag' => 'iq', 'label' => 'Kurdî'],
        'ckb' => ['flag' => 'iq', 'label' => 'کوردی'],
        'zu' => ['flag' => 'za', 'label' => 'isiZulu'],
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
