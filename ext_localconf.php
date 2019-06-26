<?php

defined('TYPO3_MODE') or die();

// Define TypoScript as content rendering template
$GLOBALS['TYPO3_CONF_VARS']['FE']['contentRenderingTemplates'][] = 'lib_jquery/Configuration/TypoScript/';

if (TYPO3_MODE === 'FE') {
    // Register hook for PageRenderer
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][] =
        \Sonority\LibJquery\Hooks\PageRenderer::class . '->renderPreProcess';
}
