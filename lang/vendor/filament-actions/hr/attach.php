<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Priloži',

        'modal' => [

            'heading' => 'Priloži :label',

            'fields' => [

                'record_id' => [
                    'label' => 'Zapis',
                ],

            ],

            'actions' => [

                'attach' => [
                    'label' => 'Priloži',
                ],

                'attach_another' => [
                    'label' => 'Priloži i dodaj još jedan',
                ],

            ],

        ],

        'notifications' => [

            'attached' => [
                'title' => 'Priloženo',
            ],

        ],

    ],

];
