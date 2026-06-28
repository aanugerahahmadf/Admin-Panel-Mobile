<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'מחיקה לתמיד',

        'modal' => [

            'heading' => 'מחק לתמיד את :label',

            'actions' => [

                'delete' => [
                    'label' => 'מחק',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'נמחק',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'נבחרו למחיקה לתמיד',

        'modal' => [

            'heading' => 'נבחרו עבור מחיקה לתיד :label',

            'actions' => [

                'delete' => [
                    'label' => 'מחק',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'נמחק',
            ],

        ],

    ],

];
