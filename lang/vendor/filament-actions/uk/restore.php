<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Відновити',

        'modal' => [

            'heading' => 'Відновити :label',

            'actions' => [

                'restore' => [
                    'label' => 'Відновити',
                ],

            ],

        ],

        'notifications' => [

            'restored' => [
                'title' => 'Запис відновлено',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Відновити вибране',

        'modal' => [

            'heading' => 'Відновити вибране :label',

            'actions' => [

                'restore' => [
                    'label' => 'Відновити',
                ],

            ],

        ],

        'notifications' => [

            'restored' => [
                'title' => 'Записи відновлені',
            ],

        ],

    ],

];
