<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Ջնջել',

        'modal' => [

            'heading' => 'Ջնջել :labelը',

            'actions' => [

                'delete' => [
                    'label' => 'Ջնջել',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Ջնջվել է',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Ջնջել ընտրվածը',

        'modal' => [

            'heading' => 'Ջնջել ընտրված :labelը',

            'actions' => [

                'delete' => [
                    'label' => 'Ջնջել',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Ջնջվել է',
            ],

        ],

    ],

];
