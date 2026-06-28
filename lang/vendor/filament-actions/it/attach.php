<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Collega',

        'modal' => [

            'heading' => 'Collega :label',

            'fields' => [

                'record_id' => [
                    'label' => 'Record',
                ],

            ],

            'actions' => [

                'attach' => [
                    'label' => 'Collega',
                ],

                'attach_another' => [
                    'label' => 'Collega & collega un altro',
                ],

            ],

        ],

        'notifications' => [

            'attached' => [
                'title' => 'Collegato',
            ],

        ],

    ],

];
