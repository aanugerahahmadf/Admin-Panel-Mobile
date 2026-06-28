<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Připojit',

        'modal' => [

            'heading' => 'Připojit :label',

            'fields' => [

                'record_id' => [
                    'label' => 'Záznam',
                ],

            ],

            'actions' => [

                'associate' => [
                    'label' => 'Připojit',
                ],

                'associate_another' => [
                    'label' => 'Připojit & připojit další',
                ],

            ],

        ],

        'notifications' => [

            'associated' => [
                'title' => 'Připojeno',
            ],

        ],

    ],

];
