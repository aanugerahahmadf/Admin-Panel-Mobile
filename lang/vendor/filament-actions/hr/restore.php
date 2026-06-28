<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Vrati',

        'modal' => [

            'heading' => 'Vrati :label',

            'actions' => [

                'restore' => [
                    'label' => 'Vrati',
                ],

            ],

        ],

        'notifications' => [

            'restored' => [
                'title' => 'Vraćeno',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Vrati odabrano',

        'modal' => [

            'heading' => 'Vrati odabrano :label',

            'actions' => [

                'restore' => [
                    'label' => 'Vrati',
                ],

            ],

        ],

        'notifications' => [

            'restored' => [
                'title' => 'Vraćeno',
            ],

        ],

    ],

];
