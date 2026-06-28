<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Force delete',

        'modal' => [

            'heading' => 'Force delete :label',

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

        'label' => 'Force delete selected',

        'modal' => [

            'heading' => 'Force delete selected :label',

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
