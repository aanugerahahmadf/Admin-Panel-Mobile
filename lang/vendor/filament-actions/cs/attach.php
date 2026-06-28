<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Přidat',

        'modal' => [

            'heading' => 'Přidat :label',

            'fields' => [

                'record_id' => [
                    'label' => 'Záznam',
                ],

            ],

            'actions' => [

                'attach' => [
                    'label' => 'Přidat',
                ],

                'attach_another' => [
                    'label' => 'Přidat & přidat další',
                ],

            ],

        ],

        'notifications' => [

            'attached' => [
                'title' => 'Přidáno',
            ],

        ],

    ],

];
