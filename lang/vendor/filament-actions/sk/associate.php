<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Pripojiť',

        'modal' => [

            'heading' => 'Pripojiť :label',

            'fields' => [

                'record_id' => [
                    'label' => 'Záznam',
                ],

            ],

            'actions' => [

                'associate' => [
                    'label' => 'Pripojiť',
                ],

                'associate_another' => [
                    'label' => 'Pripojiť & pripojiť ďalšie',
                ],

            ],

        ],

        'notifications' => [

            'associated' => [
                'title' => 'Pripojené',
            ],

        ],

    ],

];
