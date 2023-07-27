<?php

defined('TYPO3') || die();

// Add static typoscript configuration
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'lib_jquery',
    'Configuration/TypoScript/',
    'JS Library: jQuery'
);
