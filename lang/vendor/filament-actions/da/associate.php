<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Associer',

        'modal' => [

            'heading' => 'Associer :label',

            'fields' => [

                'record_id' => [
                    'label' => 'Registrering',
                ],

            ],

            'actions' => [

                'associate' => [
                    'label' => 'Associer',
                ],

                'associate_another' => [
                    'label' => 'Associer & Associer en mere',
                ],

            ],

        ],

        'notifications' => [

            'associated' => [
                'title' => 'Associeret',
            ],

        ],

    ],

];
