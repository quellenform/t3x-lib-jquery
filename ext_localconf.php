<?php

defined('TYPO3') || die();

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][]
    = \Quellenform\LibJquery\Hooks\PageRendererHook::class . '->renderPreProcess';
