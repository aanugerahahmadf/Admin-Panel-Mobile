<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Detach',

        'modal' => [

            'heading' => 'Detach :label',

            'actions' => [

                'detach' => [
                    'label' => 'Detach',
                ],

            ],

        ],

        'notifications' => [

            'detached' => [
                'title' => 'Detached',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Detach selected',

        'modal' => [

            'heading' => 'Detach selected :label',

            'actions' => [

                'detach' => [
                    'label' => 'Detach',
                ],

            ],

        ],

        'notifications' => [

            'detached' => [
                'title' => 'Detached',
            ],

        ],

    ],

];
