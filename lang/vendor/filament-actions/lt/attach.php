<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Pridėti',

        'modal' => [

            'heading' => 'Pridėti :label',

            'fields' => [

                'record_id' => [
                    'label' => 'Įrašas',
                ],

            ],

            'actions' => [

                'attach' => [
                    'label' => 'Pridėti',
                ],

                'attach_another' => [
                    'label' => 'Pridėti ir pridėti kitą',
                ],

            ],

        ],

        'notifications' => [

            'attached' => [
                'title' => 'Pridėta',
            ],

        ],

    ],

];
