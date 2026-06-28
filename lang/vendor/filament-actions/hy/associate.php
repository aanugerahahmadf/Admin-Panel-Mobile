<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Ասոցացնել',

        'modal' => [

            'heading' => 'Ասոցացնել :label',

            'fields' => [

                'record_id' => [
                    'label' => 'Գրառումներ',
                ],

            ],

            'actions' => [

                'associate' => [
                    'label' => 'Ասոցացնել',
                ],

                'associate_another' => [
                    'label' => 'Ասոցացնել և ասոցացնել մեկ այլ',
                ],

            ],

        ],

        'notifications' => [

            'associated' => [
                'title' => 'Ասոցացվել է',
            ],

        ],

    ],

];
