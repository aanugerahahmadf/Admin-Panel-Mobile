<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Palauta',

        'modal' => [

            'heading' => 'Palauta :label',

            'actions' => [

                'restore' => [
                    'label' => 'Palauta',
                ],

            ],

        ],

        'notifications' => [

            'restored' => [
                'title' => 'Palautettu',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Palauta valitut',

        'modal' => [

            'heading' => 'Palauta valitut :label',

            'actions' => [

                'restore' => [
                    'label' => 'Palauta',
                ],

            ],

        ],

        'notifications' => [

            'restored' => [
                'title' => 'Palautettu',
            ],

        ],

    ],

];
