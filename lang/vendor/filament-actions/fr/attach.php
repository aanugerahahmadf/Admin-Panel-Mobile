<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Attacher',

        'modal' => [

            'heading' => 'Attacher :label',

            'fields' => [

                'record_id' => [
                    'label' => 'Enregistrement',
                ],

            ],

            'actions' => [

                'attach' => [
                    'label' => 'Attacher',
                ],

                'attach_another' => [
                    'label' => 'Attacher & Attacher un autre',
                ],

            ],

        ],

        'notifications' => [

            'attached' => [
                'title' => 'Attaché(e)s',
            ],

        ],

    ],

];
