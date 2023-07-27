<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'JS Library: jQuery',
    'description' => 'Integrates the jQuery-library from CDN with a local fallback if requested library is not available. All relevant versions of jQuery (including minified and gzipped-versions) are shipped with this extension.',
    'category' => 'fe',
    'state' => 'stable',
    'clearcacheonload' => true,
    'author' => 'Stephan Kellermayr',
    'author_email' => 'typo3@quellenform.at',
    'author_company' => 'Kellermayr KG',
    'version' => '4.0.1',
    'constraints' => [
        'depends' => [
            'php' => '7.2.0-8.4.99',
            'typo3' => '10.4.0-12.5.99',
        ],
        'conflicts' => [],
        'suggests' => []
    ],
    'autoload' => [
        'psr-4' => ['Quellenform\\LibJquery\\' => 'Classes']
    ],
];
