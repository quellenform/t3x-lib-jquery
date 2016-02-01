<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "lib_jquery".
 *
 * Auto generated 19-08-2014 16:34
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
	'title' => 'JS Library: jQuery',
	'description' => 'Integrates the jQuery-library from CDN with a local fallback if requested library is not available. All major versions of jQuery (including minified and gzipped-versions) are shipped with this extension.',
	'category' => 'misc',
	'version' => '0.0.1',
	'state' => 'alpha',
	'uploadfolder' => false,
	'createDirs' => '',
	'clearcacheonload' => true,
	'author' => 'Stephan Kellermayr',
	'author_email' => 'stephan.kellermayr@gmail.com',
	'author_company' => 'sonority.at - MULTIMEDIA ART DESIGN',
    'constraints' => [
        'depends' => [
            'typo3' => '6.2.0-7.6.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];

