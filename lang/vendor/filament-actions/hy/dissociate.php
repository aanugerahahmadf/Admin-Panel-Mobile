<?php

/** @return array<string, mixed> */

return [

    'single' => [

        'label' => 'Տարանջատել',

        'modal' => [

            'heading' => 'Տարանջատել :label',

            'actions' => [

                'dissociate' => [
                    'label' => 'Տարանջատել',
                ],

            ],

        ],

        'notifications' => [

            'dissociated' => [
                'title' => 'Տարանջատվել է',
            ],

        ],

    ],

    'multiple' => [

        'label' => 'Տարանջատել ընտրվածը',

        'modal' => [

            'heading' => 'Տարանջատել ընտրված :labelը',

            'actions' => [

                'dissociate' => [
                    'label' => 'Տարանջատել ընտրվածը',
                ],

            ],

        ],

        'notifications' => [

            'dissociated' => [
                'title' => 'Տարանջատվել է',
            ],

        ],

    ],

];
