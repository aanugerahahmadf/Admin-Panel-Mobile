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
                'title' => 'Registro restaurado',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Restaurar seleccionados',

        'modal' => [

            'heading' => 'Restaurar los :label seleccionados',

            'actions' => [

                'restore' => [
                    'label' => 'Restaurar',
                ],

            ],

        ],

        'notifications' => [

            'restored' => [
                'title' => 'Registros restaurados',
            ],

        ],

    ],

];
