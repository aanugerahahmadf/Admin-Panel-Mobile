<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Pridať',

        'modal' => [

            'heading' => 'Pridať :label',

            'fields' => [

                'record_id' => [
                    'label' => 'Záznam',
                ],

            ],

            'actions' => [

                'attach' => [
                    'label' => 'Pridať',
                ],

                'attach_another' => [
                    'label' => 'Pridať & pridať ďalšie',
                ],

            ],

        ],

        'notifications' => [

            'attached' => [
                'title' => 'Pridané',
            ],

        ],

    ],

];
