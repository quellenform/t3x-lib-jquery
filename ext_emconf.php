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
    'version' => '5.0.1',
    'constraints' => [
        'depends' => [
            'php' => '8.1.0-8.4.99',
            'typo3' => '12.0.0-14.9.99',
        ],
        'conflicts' => [],
        'suggests' => []
    ]
];
