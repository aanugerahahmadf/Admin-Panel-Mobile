<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Poista',

        'modal' => [

            'heading' => 'Poista :label',

            'actions' => [

                'delete' => [
                    'label' => 'Poista',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Poistettu',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Poista valitut',

        'modal' => [

            'heading' => 'Poista valitut :label',

            'actions' => [

                'delete' => [
                    'label' => 'Poista',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Poistettu',
            ],

        ],

    ],

];
