<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'ลบ',

        'modal' => [

            'heading' => 'ลบ:label',

            'actions' => [

                'delete' => [
                    'label' => 'ลบ',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'ลบข้อมูลเรียบร้อย',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'ลบที่เลือก',

        'modal' => [

            'heading' => 'ลบ:labelที่เลือก',

            'actions' => [

                'delete' => [
                    'label' => 'ลบ',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'ลบข้อมูลเรียบร้อย',
            ],

        ],

    ],

];
