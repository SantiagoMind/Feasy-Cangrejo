<?php

if (!class_exists('Feasy_Library_Updater')) return;

Feasy_Library_Updater::register([
    'sortable' => [
        'repo'     => 'SortableJS/Sortable',
        'filename' => 'sortable.min.js',
        'url'      => 'https://raw.githubusercontent.com/SortableJS/Sortable/%version%/Sortable.min.js',
    ],
    // Aquí puedes registrar más librerías en el futuro
]);