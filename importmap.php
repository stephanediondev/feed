<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'bootstrap-icons/font/bootstrap-icons.min.css' => [
        'version' => '1.11.3',
        'type' => 'css',
    ],
    'bootstrap' => [
        'version' => '5.3.3',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.3',
        'type' => 'css',
    ],
    'moment-timezone' => [
        'version' => '0.5.47',
    ],
    'moment' => [
        'version' => '2.30.1',
    ],
    'jquery.scrollto' => [
        'version' => '2.1.3',
    ],
    'jquery' => [
        'version' => '3.7.1',
    ],
    'handlebars' => [
        'version' => '4.7.8',
    ],
    'i18next' => [
        'version' => '24.2.2',
    ],
    'file-saver' => [
        'version' => '2.0.5',
    ],
];
