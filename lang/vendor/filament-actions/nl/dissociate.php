<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Ontkoppelen',

        'modal' => [

            'heading' => ':Label ontkoppelen',

            'actions' => [

                'dissociate' => [
                    'label' => 'Ontkoppelen',
                ],

            ],

        ],

        'notifications' => [

            'dissociated' => [
                'title' => 'Ontkoppeld',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Geselecteerde ontkoppelen',

        'modal' => [

            'heading' => 'Geselecteerde :label ontkoppelen',

            'actions' => [

                'dissociate' => [
                    'label' => 'Ontkoppelen',
                ],

            ],

        ],

        'notifications' => [

            'dissociated' => [
                'title' => 'Ontkoppeld',
            ],

        ],

    ],

];
