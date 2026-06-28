<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Biriktirish',

        'modal' => [

            'heading' => ':labelni biriktirish',

            'fields' => [

                'record_id' => [
                    'label' => 'Yozib olish',
                ],

            ],

            'actions' => [

                'attach' => [
                    'label' => 'Biriktirish',
                ],

                'attach_another' => [
                    'label' => 'Biriktirish va yana boshqa biriktirish',
                ],

            ],

        ],

        'notifications' => [

            'attached' => [
                'title' => 'Biriktirilgan',
            ],

        ],

    ],

];
