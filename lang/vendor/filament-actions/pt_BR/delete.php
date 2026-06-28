<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Excluir',

        'modal' => [

            'heading' => 'Excluir :label',

            'actions' => [

                'delete' => [
                    'label' => 'Excluir',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Excluído',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Excluir selecionado',

        'modal' => [

            'heading' => 'Excluir :label selecionado',

            'actions' => [

                'delete' => [
                    'label' => 'Excluir',
                ],

            ],

        ],

        'notifications' => [

            'deleted' => [
                'title' => 'Excluído',
            ],

        ],

    ],

];
