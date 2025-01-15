<?php

return [

    'label' => '分頁導航',

    'overview' => '正在顯示第 :first 至 :last 項結果，共 :total 項',

    'fields' => [

        'records_per_page' => [
            'label' => '每頁顯示',
            'options' => [
                10 => '10 筆',
                20 => '20 筆',
                50 => '50 筆',
                100 => '100 筆',
                'all' => '全部',
            ],
        ],

    ],
    'table' => [
        'filters' => [
            'heading' => '每頁顯示',
        ],
    ],

    'actions' => [

        'go_to_page' => [
            'label' => '前往第 :page 頁',
        ],

        'next' => [
            'label' => '下一頁',
        ],

        'previous' => [
            'label' => '上一頁',
        ],

    ],
];
