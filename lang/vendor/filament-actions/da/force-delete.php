<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Fremtving sletning',

        'modal' => [

            'heading' => 'Fremtving sletning af :label',

            'actions' => [

                'delete' => [
                    'label' => 'Slet',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Slettet',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Fremtving sletning af valgte',

        'modal' => [

            'heading' => 'Fremtving sletning af valgte :label',

            'actions' => [

                'delete' => [
                    'label' => 'Slet',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Slettet',
            ],

        ],

    ],

];
