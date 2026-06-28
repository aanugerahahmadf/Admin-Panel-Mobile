<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Восстановить',

        'modal' => [

            'heading' => 'Восстановить :label',

            'actions' => [

                'restore' => [
                    'label' => 'Восстановить',
                ],

            ],

        ],

        'notifications' => [

            'restored' => [
                'title' => 'Запись восстановлена',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Восстановить выбранное',

        'modal' => [

            'heading' => 'Восстановить выбранное :label',

            'actions' => [

                'restore' => [
                    'label' => 'Восстановить',
                ],

            ],

        ],

        'notifications' => [

            'restored' => [
                'title' => 'Записи восстановлены',
            ],

        ],

    ],

];
