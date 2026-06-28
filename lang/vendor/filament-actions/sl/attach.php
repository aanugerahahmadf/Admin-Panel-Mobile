<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Poveži',

        'modal' => [

            'heading' => 'Poveži :label',

            'fields' => [

                'record_id' => [
                    'label' => 'Zapis',
                ],

            ],

            'actions' => [

                'attach' => [
                    'label' => 'Poveži',
                ],

                'attach_another' => [
                    'label' => 'Poveži in poveži drugega',
                ],

            ],

        ],

        'notifications' => [

            'attached' => [
                'title' => 'Povezano',
            ],

        ],

    ],

];
