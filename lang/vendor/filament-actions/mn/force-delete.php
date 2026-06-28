<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Устгах үйлдэл (force)',

        'modal' => [

            'heading' => 'Устгах :label',

            'actions' => [

                'delete' => [
                    'label' => 'Устгах',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Устгасан',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Сонгосон устгах',

        'modal' => [

            'heading' => 'Сонгосон устгах :label',

            'actions' => [

                'delete' => [
                    'label' => 'Устгах',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Устгасан',
            ],

        ],

    ],

];
