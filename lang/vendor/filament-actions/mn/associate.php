<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Харилцан холбоос',

        'modal' => [

            'heading' => 'Холбох :label',

            'fields' => [

                'record_id' => [
                    'label' => 'Бичлэг',
                ],

            ],

            'actions' => [

                'associate' => [
                    'label' => 'Холбох',
                ],

                'associate_another' => [
                    'label' => 'Хадгалаад & ахиад шинийг үүсгэх',
                ],

            ],

        ],

        'notifications' => [

            'associated' => [
                'title' => 'Холбоос үүсэв',
            ],

        ],

    ],

];
