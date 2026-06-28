<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Przywróć',

        'modal' => [

            'heading' => 'Przywróć :label',

            'actions' => [

                'restore' => [
                    'label' => 'Przywróć',
                ],

            ],

        ],

        'notifications' => [

            'restored' => [
                'title' => 'Przywrócono',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Przywróć zaznaczone',

        'modal' => [

            'heading' => 'Przywróć zaznaczone :label',

            'actions' => [

                'restore' => [
                    'label' => 'Przywróć',
                ],

            ],

        ],

        'notifications' => [

            'restored' => [
                'title' => 'Przywrócono',
            ],

        ],

    ],

];
