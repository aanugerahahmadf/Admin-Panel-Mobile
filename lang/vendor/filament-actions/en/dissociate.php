<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Dissociate',

        'modal' => [

            'heading' => 'Dissociate :label',

            'actions' => [

                'dissociate' => [
                    'label' => 'Dissociate',
                ],

            ],

        ],

        'notifications' => [

            'dissociated' => [
                'title' => 'Dissociated',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Dissociate selected',

        'modal' => [

            'heading' => 'Dissociate selected :label',

            'actions' => [

                'dissociate' => [
                    'label' => 'Dissociate',
                ],

            ],

        ],

        'notifications' => [

            'dissociated' => [
                'title' => 'Dissociated',
            ],

        ],

    ],

];
