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

use TYPO3\CMS\Core\Utility\PathUtility;
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
        3000000,
        3001000, 3001001,
        3002000, 3002001,
        3003000, 3003001,
        3004000, 3004001
    ];

    /**
     * Array of Content-Delivery-Networks (CDN) with placeholders
     *
     * @var array
     */
    protected $jQueryCdnUrls = [
        'local' => '',
        'google' => ['url' => '//ajax.googleapis.com/ajax/libs/jquery/%1$s/jquery%2$s.js'],
        'msn' => ['url' => '//ajax.aspnetcdn.com/ajax/jQuery/jquery-%1$s%2$s.js'],
        'jquery' => ['url' => '//code.jquery.com/jquery-%1$s%2$s.js'],
        'cloudflare' => ['url' => '//cdnjs.cloudflare.com/ajax/libs/jquery/%1$s/jquery%2$s.js'],
        'jsdelivr' => ['url' => '//cdn.jsdelivr.net/npm/jquery@%1$s/dist/jquery%2$s.js']
    ];

    /**
     * Array of integrity hashes
     * $cat jquery.js | openssl dgst -sha384 -binary | openssl base64 -A
     *
     * @var array
     */
    protected $integrity = [
        1010000 => [
            0 => 'sha384-SDfd5ZIemaAmf6Ljbm07lqw2szoA1xgwjqCzgYX4TKGTvO081yWl27tPRl1jzjaT',
            1 => 'sha384-5zg4jSLVIH70npW3C3dzc3zKoHias+JjURN/RKFpU0oWq9Nbf17miq2PC4AXePsx'
        ],
        1010001 => [
            0 => 'sha384-M7ti4rBhW6K9vKaD69Y/xNT8YW/SFBQBKbSZtEdjveDyXUpsF3gmBWZRjtr0ZbCD',
            1 => 'sha384-gwskTDRpKgp0b8KeTMEfiPCRDmNqpY2TGo7y+gyJhUGHlY1VfeXQTE4Ym3R+uXOi'
        ],
        1010002 => [
            0 => 'sha384-hK8q2gkBjirpIGHAH+sgqYMv6i6mfx2JVZWJ50jyYhkuEHASU6AS1UTWSo32wuGL',
            1 => 'sha384-r0tJvB87edk25TJle8mfwmdYBwaGtkX3r4CYHXS+2yZ7VPdI8xd2rHl6KTQ6oij4'
        ],
        1011000 => [
            0 => 'sha384-/Gm+ur33q/W+9ANGYwB2Q4V0ZWApToOzRuA8md/1p9xMMxpqnlguMvk8QuEFWA1B',
            1 => 'sha384-wXaUK2xxr5SLxv/mtBosW5jnp5oXJG8tOkdKVyfQa4szUSJfvRYiFgPdKr/BoGt+'
        ],
        1011001 => [
            0 => 'sha384-UM1JrZIpBwVf5jj9dTKVvGiiZPZTLVoq4sfdvIe9SBumsvCuv6AHDNtEiIb5h1kU',
            1 => 'sha384-wciR07FV6RBcI+YEVsZy/bInxpyn0uefUp6Yi9R5r46Qv/yk/osR5nzY31koh9Uq'
        ],
        1011002 => [
            0 => 'sha384-Pn+PczAsODRZ2PiGg0IheRROpP7lXO1NTIjiPo6cca8TliBvaeil42fobhzvZd74',
            1 => 'sha384-ACws9ykGE3FuLkDpto3htd4WDroMJsyYg0Rv3bO+B9Y37HpERfGFpgwMwj+AJG5q'
        ],
        1011003 => [
            0 => 'sha384-+54fLHoW8AHu3nHtUxs9fW2XKOZ2ZwKHB5olRtKSDTKJIb1Na1EceFZMS8E72mzW',
            1 => 'sha384-EEeHkUH6Bi7gEpctGhqp/WxHMDWLj+lN/cssQT5AXRasoLB8/4UbV786cHcBlhZS'
        ],
        1012000 => [
            0 => 'sha384-XxcvoeNF5V0ZfksTnV+bejnCsJjOOIzN6UVwF85WBsAnU3zeYh5bloN+L4WLgeNE',
            1 => 'sha384-XW9ir2wQxI+Fdr/3lkkghOtgu8RBgJcwjpSduK5VzTvKm1jLqbS2zqw6fnHn9LTC'
        ],
        2000000 => [
            0 => 'sha384-RSXe9j39thYJayrqYBX6YfS7IV4bsKWZ9eEMYN+CLnB5pUyGAlXbUG/zWLqB62Na',
            1 => 'sha384-CdwPx9ubjNqFc8hhe3VnpER1IQoQUrFGYLzCtX/1RrEVZnGWmhkVMkjSIOhK2dfv'
        ],
        2000001 => [
            0 => 'sha384-XbeMbOioEXkmLLwBnrkuzXrlPiGXHcrhy8cemVjBT8AXJhCKL7vWP65VBL6Psufx',
            1 => 'sha384-HvDSThxrD4rFcfMHrsJq83qb5TMVOOB6bBIZIag2KmOYv4PeyGYnXJZTWIR+W7of'
        ],
        2000002 => [
            0 => 'sha384-1UZ2WGivCCxSFpBAaNkUfGnXmvLO/eXAvLFtIfO8t54OSIpc1dwSF7LKdARVIy/7',
            1 => 'sha384-Oa7q0oEZI/gi1C3x6wJD+gdeCSGVQsMhZEdPj0Ce3i/50kKa1ns0RhLxXyRqx2Xt'
        ],
        2000003 => [
            0 => 'sha384-5BJxsRUsHmz1Kk/hmquvrNXoqth2fWO8TW4G5MbuwrD7g7eyanb4SJ9KV+VGChAT',
            1 => 'sha384-CQyOkvfvCvhwQOdH5W6oGDYPTE7Q+FaLVAqTfeM2O2wECkW/92wC2/QIuQFRLNtj'
        ],
        2001000 => [
            0 => 'sha384-85/BFduEdDxQ86xztyNu4BBkVZmlvu+iB7zhBu0VoYdq+ODs3PKpU6iVE3ZqPMut',
            1 => 'sha384-Zs6hQFvI8SnJnsfOJN79J1olsSzbRiiTRFI3MKnUr3Mr/SYHHA7O39r6HPJYLrzS'
        ],
        2001001 => [
            0 => 'sha384-fj9YEHKNa/e0CNquG4NcocjoyMATYo1k2Ff5wGB42C/9AwOlJjDoySPtNJalccfI',
            1 => 'sha384-OhORL1bfiBqoyzpjW+0vUZ33mZ0eAsE3yqUM/7s7BFH8C1n6BsioFATPtW2HJX3R'
        ],
        2001003 => [
            0 => 'sha384-E7gp+UYBLS2XewcxoJbfi0UpGMHSvt9XyI9bH4YIw5GDGW8AlC+2J7bVBBlMFC6p',
            1 => 'sha384-qeGYpo1beiY4wzqymFFtQd2+y7Y1MIcqZ0k6kfWaM3AZlt2MF/+iaqFhoSd4f7zA'
        ],
        2001004 => [
            0 => 'sha384-R4/ztc4ZlRqWjqIuvf6RX5yb/v90qNGx6fS48N0tRxiGkqveZETq72KgDVJCp2TC',
            1 => 'sha384-1qy6pxCPVEhkjPJM8mBaaRNIDGE20UzrPyndMEoCaeK390vhZ3jt3SQtS6aZDqRA'
        ],
        2002000 => [
            0 => 'sha384-K+ctZQ+LL8q6tP7I94W+qzQsfRV2a+AfHIi9k8z8l9ggpc8X+Ytst4yBo/hH+8Fk',
            1 => 'sha384-YLaE+8cMSCcSIhxjU/oq+EdgFwCRhLWjvna+rClG1pfJ+Fi5929axqLBLB1roPrJ'
        ],
        2002001 => [
            0 => 'sha384-8C+3bW/ArbXinsJduAjm9O7WNnuOcO+Bok/VScRYikawtvz4ZPrpXtGfKIewM9dK',
            1 => 'sha384-0fYOtFLBCRNBtt6roCCNUh4ZV0Zr+ag0wIzFqgiMF7AkGHJDrAeNNiLwxhjv93an'
        ],
        2002002 => [
            0 => 'sha384-mXQoED/lFIuocc//nss8aJOIrz7X7XruhR6bO+sGceiSyMELoVdZkN7F0oYwcFH+',
            1 => 'sha384-wXzUADPF9Bz2799p4Pdz47lL4QjBMFbtTd1W0IuSlgSsY0YgldTUrinwB2mFzmVK'
        ],
        2002003 => [
            0 => 'sha384-I6F5OKECLVtK/BL+8iSLDEHowSAfUo76ZL9+kGAgTRdiByINKJaqTPH/QVNS1VDb',
            1 => 'sha384-Sslvlvc3erwg2M8wKR5WKJgcxp5BextrY7gzTAmVZ6xFvWgWbe6KQSOHxvdNdiYv'
        ],
        2002004 => [
            0 => 'sha384-rY/jv8mMhqDabXSo+UCggqKtdmBfd3qC2/KvyTDNQ6PcUJXaxK1tMepoQda4g5vB',
            1 => 'sha384-TlQc6091kl7Au04dPgLW7WK3iey+qO8dAi/LdwxaGBbszLxnizZ4xjPyNrEf+aQt'
        ],
        3000000 => [
            0 => 'sha384-THPy051/pYDQGanwU6poAc/hOdQxjnOEXzbT+OuUAFqNqFjL+4IGLBgCJC3ZOShY',
            1 => 'sha384-4s7h2GHjGL3pidmgZNMUflDgyCMrPn1cnW/2NsD1U7SCZcCU5lBzlNhd6QENeuSK'
        ],
        3001000 => [
            0 => 'sha384-nrOSfDHtoPMzJHjVTdCopGqIqeYETSXhZDFyniQ8ZHcVy08QesyHcnOUpMpqnmWq',
            1 => 'sha384-Te3ltuYHeiA5s5F5AZJT+ypl5KHViBntHsSyFm38sNPyiMAOYCIMiegWF4UT+vxK'
        ],
        3001001 => [
            0 => 'sha384-3ceskX3iaEnIogmQchP8opvBy3Mi7Ce34nWjpBIwVTHfGYWQS9jwHDVRnpKKHJg7',
            1 => 'sha384-VC7EHu0lDzZyFfmjTPJq+DFyIn8TUGAJbEtpXquazFVr00Q/OOx//RjiZ9yU9+9m'
        ],
        3002000 => [
            0 => 'sha384-o9KO9jVK1Q4ybtHgJCCHfgQrTRNlkT6SL3j/qMuBMlDw3MmFrgrOHCOaIMJWGgK5',
            1 => 'sha384-Z7BISSdO0r7B6SzrJbAMx7YC8qHx9BpV4iJow8J2pCY4jWj2LYgFfCMekrfmST4m'
        ],
        3002001 => [
            0 => 'sha384-xBuQ/xzmlsLoJpyjoggmTEz8OWUFM0/RC5BsqQBDX2v5cMvDHcMakNTNrHIW2I5f',
            1 => 'sha384-p7RDedFtQzvcp0/3247fDud39nqze/MUmahi6MOWjyr3WKWaMOyqhXuCT1sM9Q+l'
        ],
        3003000 => [
            0 => 'sha384-T71zTKG4DxXKaUTgGmqdaNdSf9Y4FQsJDVhzYmnC9z5+3PkzQoBZtfc69ySrMK/J',
            1 => 'sha384-OhEg3Gh/4fPZov0xAAH7gRe8gGgHFsLLIJt0gEKvTqAnpjxedEW1CXSwt25y+rDd'
        ],
        3003001 => [
            0 => 'sha384-tsQFqpEReu7ZLhBV2VZlAu7zcOV+rXbYlF2cqB8txI/8aZajjp4Bqd+V6D5IgvKT',
            1 => 'sha384-fJU6sGmyn07b+uD1nMk7/iSb4yvaowcueiQhfVgQuD98rfva8mcr1eSvjchfpMrH'
        ],
        3004000 => [
            0 => 'sha384-JUMjoW8OzDJw4oFpWIB2Bu/c6768ObEthBMVSiIx4ruBIEdyNSUQAjJNFqT5pnJ6',
            1 => 'sha384-WGhEWG1n4j4SSTvTWxHLVbwDs5irzinCJT89aUzyS2H/wY2d2eZrUWSsNyCucTYy'
        ],
        3004001 => [
            0 => 'sha384-vk5WoKIaW/vJyUAd9n/wmopsmNhiy+L2Z+SBxGYnUkunIxVxAv/UtMOhba/xskxh',
            1 => 'sha384-mlceH9HlqLp7GMKHrj5Ara1+LvdTZVMx4S1U43/NxCvAkzIo8WJ0FE7duLel3wVo'
        ]
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
        if (!empty($conf['source']) && array_key_exists($conf['source'], $this->jQueryCdnUrls)) {
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
            $integrity = '';
            $fallbackTag = '';
            // Enable anonymous crossorigin-request
            $crossorigin = boolval($conf['anonymousCrossorigin']) ? 'anonymous' : '';
            // Check if file is local
            $isLocal = ($conf['source'] === 'local') ? true : false;
            // Check if the file should be concatenated
            $excludeFromConcatenation = (boolval($conf['excludeFromConcatenation']) || !$isLocal) ? true : false;
            // Choose minified version if debug is disabled
            $minPart = (int) $conf['debug'] ? '' : '.min';
            // Deliver gzipped-version if compression is activated and client supports gzip (compression done with "gzip --best -k -S .gzip")
            $gzipPart = (intval($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['compressionLevel']) && $excludeFromConcatenation) ? '.gzip' : '';
            // Set path and placeholders for local file
            $this->jQueryCdnUrls['local'] = $conf['localPath'] . 'jquery-%1$s%2$s.js';
            // Generate tags for local or CDN (and fallback)
            if ($isLocal) {
                // Get local version and replace placeholders
                $file = sprintf($this->jQueryCdnUrls['local'],
                        VersionNumberUtility::convertIntegerToVersionNumber($versionLocal), $minPart) . $gzipPart;
                $file = PathUtility::stripPathSitePrefix(GeneralUtility::getFileAbsFileName($file));
            } else {
                // Get CDN and replace placeholders
                $file = sprintf($this->jQueryCdnUrls[$conf['source']]['url'],
                    VersionNumberUtility::convertIntegerToVersionNumber($versionCdn), $minPart);
                // Get file integrity
                $integrity = $this->integrity[$versionCdn][intval($conf['debug'])];
                // Generate fallback if required
                if ((int) $conf['localFallback']) {
                    // Get local fallback version and replace placeholders
                    $fileFallback = sprintf($this->jQueryCdnUrls['local'],
                            VersionNumberUtility::convertIntegerToVersionNumber($versionLocal), $minPart) . $gzipPart;
                    // Get absolute path to the fallback-file
                    $fileFallback = PathUtility::stripPathSitePrefix(GeneralUtility::getFileAbsFileName($fileFallback));
                    // Wrap it in some javascript code which will enable the fallback
                    $fallbackTag = '<script>window.jQuery || document.write(\'<script src="' .
                        htmlspecialchars($fileFallback) .
                        '" type="text/javascript"><\/script>\')</script>' . LF;
                }
            }
            $pObj->addJsLibrary('lib_jquery', $file, 'text/javascript', FALSE, TRUE, '|' . LF . $fallbackTag . '', $excludeFromConcatenation, '|', false, $integrity, false, $crossorigin);
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
