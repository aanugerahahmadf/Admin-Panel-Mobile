<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Restaurar',

        'modal' => [

            'heading' => 'Restaurar :label',

            'actions' => [

                'restore' => [
                    'label' => 'Restaurar',
                ],

            ],

        ],

        'notifications' => [

            'restored' => [
                'title' => 'Restaurado',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Restaurar selecionado',

        'modal' => [

            'heading' => 'Restaurar :label selecionado',

            'actions' => [

                'restore' => [
                    'label' => 'Restaurar',
                ],

            ],

        ],

        'notifications' => [

            'restored' => [
                'title' => 'Restaurado',
            ],

        ],

    ],

];
