<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Tvångsradera',

        'modal' => [

            'heading' => 'Tvångsradera :label',

            'actions' => [

                'delete' => [
                    'label' => 'Radera',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Raderades',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Tvångsradera valda',

        'modal' => [

            'heading' => 'Tvångsradera valda :label',

            'actions' => [

                'delete' => [
                    'label' => 'Radera',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Raderades',
            ],

        ],

    ],

];
