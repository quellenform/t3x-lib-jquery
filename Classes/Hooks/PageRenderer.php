<?php

namespace Sonority\LibJquery\Hooks;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Add jQuery on top of javascript-stack
 *
 * @author Stephan Kellermayr <stephan.kellermayr@gmail.com>
 * @package TYPO3
 * @subpackage tx_libquery
 */
class PageRenderer
{

    /**
     * Array of jQuery version numbers shipped with this extension
     *
     * @var array
     */
    protected $availableLocalJqueryVersions = [
        1010000, 1010001, 1010002,
        1011000, 1011001, 1011002, 1011003,
        1012000,
        2000000, 2000001, 2000002, 2000003,
        2001000, 2001001, 2001003, 2001004,
        2002000, 2002001, 2002003, 2002004,
        3000000
    ];

    /**
     * Array of Content-Delivery-Networks (CDN) with placeholders
     *
     * @var array
     */
    protected $jQueryCdnUrls = [
        'local' => '',
        'google' => '//ajax.googleapis.com/ajax/libs/jquery/%1$s/jquery%2$s.js',
        'msn' => '//ajax.aspnetcdn.com/ajax/jQuery/jquery-%1$s%2$s.js',
        'jquery' => '//code.jquery.com/jquery-%1$s%2$s.js',
        'cloudflare' => '//cdnjs.cloudflare.com/ajax/libs/jquery/%1$s/jquery%2$s.js',
        'jsdelivr' => '//cdn.jsdelivr.net/jquery/%1$s/jquery%2$s.js'
    ];

    /**
     * Insert javascript-tags for jQuery
     *
     * @param array $params
     * @param \TYPO3\CMS\Core\Page\PageRenderer $pObj
     * @return void
     */
    public function renderPreProcess($params, $pObj)
    {
        // Get plugin-configuration
        $conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_libjquery.']['settings.'];
        // Generate script-tag for jquery if CDN is set
        if (!empty($conf['cdn']) && array_key_exists($conf['cdn'], $this->jQueryCdnUrls)) {
            // Set version-number for CDN
            if (!(int) $conf['version'] || $conf['version'] === 'latest') {
                $versionCdn = end($this->availableLocalJqueryVersions);
            } else {
                $versionCdn = VersionNumberUtility::convertVersionNumberToInteger($conf['version']);
            }
            // Set correct version-number for local version
            if (!in_array($versionCdn, $this->availableLocalJqueryVersions)) {
                $versionLocal = $this->getNearestVersion($versionCdn);
            } else {
                $versionLocal = $versionCdn;
            }
            $fallbackTag = '';
            // Choose minified version if debug is disabled
            $minPart = (int) $conf['debug'] ? '' : '.min';
            // Deliver gzipped-version if compression is activated and client supports gzip (compression done with "gzip --best -k -S .gzip")
            $gzipPart = (int) $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['compressionLevel'] ? '.gzip' : '';
            // Set path and placeholders for local file
            $this->jQueryCdnUrls['local'] = $conf['localPath'] . 'jquery-%1$s%2$s.js';
            // Generate tags for local or CDN (and fallback)
            if ($conf['cdn'] === 'local') {
                // Get local version and replace placeholders
                $file = sprintf($this->jQueryCdnUrls['local'],
                        VersionNumberUtility::convertIntegerToVersionNumber($versionLocal), $minPart) . $gzipPart;
                $file = str_replace(PATH_site, '', GeneralUtility::getFileAbsFileName($file));
            } else {
                // Get CDN and replace placeholders
                $file = sprintf($this->jQueryCdnUrls[$conf['cdn']],
                    VersionNumberUtility::convertIntegerToVersionNumber($versionCdn), $minPart);
                // Generate fallback if required
                if ((int) $conf['localFallback']) {
                    // Get local fallback version and replace placeholders
                    $fileFallback = sprintf($this->jQueryCdnUrls['local'],
                            VersionNumberUtility::convertIntegerToVersionNumber($versionLocal), $minPart) . $gzipPart;
                    // Get absolute path to the fallback-file
                    $fileFallback = str_replace(PATH_site, '', GeneralUtility::getFileAbsFileName($fileFallback));
                    // Wrap it in some javascript code which will enable the fallback
                    $fallbackTag = '<script>window.jQuery || document.write(\'<script src="' .
                        htmlspecialchars($fileFallback) .
                        '" type="text/javascript"><\/script>\')</script>' . LF;
                }
            }
            $pObj->addJsLibrary('lib_jquery', $file, 'text/javascript', FALSE, TRUE, '|' . LF . $fallbackTag . '', TRUE);
        }
    }

    /**
     * Return nearest available version number
     *
     * @param int $version string representation of the selected version number
     * @return int
     */
    protected function getNearestVersion($version)
    {
        // Get first available version provided by this extension
        $selectedVersion = reset($this->availableLocalJqueryVersions);
        foreach ($this->availableLocalJqueryVersions as $v) {
            if ($v < $version) {
                $selectedVersion = $v;
            } else if ($v == $version) {
                $selectedVersion = $version;
                break;
            } else if ($v > $version) {
                break;
            }
        }
        return $selectedVersion;
    }

}
