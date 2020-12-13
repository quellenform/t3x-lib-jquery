<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'JS Library: jQuery',
    'description' => 'Integrates the jQuery-library from CDN with a local fallback if requested library is not available. All relevant versions of jQuery (including minified and gzipped-versions) are shipped with this extension.',
    'category' => 'fe',
    'state' => 'stable',
    'clearcacheonload' => true,
    'author' => 'Stephan Kellermayr',
    'author_email' => 'stephan.kellermayr@gmail.com',
    'author_company' => 'sonority.at - MULTIMEDIA ART DESIGN',
    'version' => '3.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.23-10.9.99'
        ],
        'conflicts' => [],
        'suggests' => [
            'fluid_styled_content' => '',
            'lib_bootstrap' => ''
        ]
    ]
];
