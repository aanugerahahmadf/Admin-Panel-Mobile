<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Delete',

        'modal' => [

            'heading' => 'Delete :label',

            'actions' => [

                'delete' => [
                    'label' => 'Delete',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Deleted',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Delete selected',

        'modal' => [

            'heading' => 'Delete selected :label',

            'actions' => [

                'delete' => [
                    'label' => 'Delete',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Deleted',
            ],

        ],

    ],

];
