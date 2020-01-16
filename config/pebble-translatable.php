<?php

return [
    'models' => [
        'translation' => Pebble\Translatable\Models\Translation::class,
    ],

    'table_names' => [
        'translatables' => 'pebble_translatables',
        'translations' => 'pebble_translations',
    ],

    'column_names' => [
        'model_morph_key' => 'model_id',
    ],
];
