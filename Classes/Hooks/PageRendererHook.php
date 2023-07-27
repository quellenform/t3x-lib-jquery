<?php

declare(strict_types=1);

namespace Quellenform\LibJquery\Hooks;

/*
 * This file is part of the "lib_jquery" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Add jQuery on top of javascript-stack
 */
class PageRendererHook
{
    /**
     * TypoScript configuration array.
     *
     * @var array
     */
    private $settings = null;

    /**
     * The jsLibrary array which holds the final values.
     *
     * @var array
     */
    private $jsLibrary = [
        'file' => '',
        'forceOnTop' => true,
        'allWrap' => '',
        'excludeFromConcatenation' => false,
        'integrity' => '',
        'crossorigin' => ''
    ];

    /**
     * Array of jQuery version numbers shipped with this extension.
     *
     * @var array
     */
    private $availableLocalJqueryVersions = [
        3005000, 3005001,
        3006000, 3006001, 3006002, 3006003, 3006004,
        3007000
    ];

    /**
     * Array of Content-Delivery-Networks (CDN) with placeholders.
     *
     * @var array
     */
    private $jQueryCdnUrls = [
        'local' => ['url' => 'EXT:lib_jquery/Resources/Public/Vendor/jQuery/jquery-%1$s%2$s.js'],
        'google' => ['url' => '//ajax.googleapis.com/ajax/libs/jquery/%1$s/jquery%2$s.js'],
        'msn' => ['url' => '//ajax.aspnetcdn.com/ajax/jQuery/jquery-%1$s%2$s.js'],
        'jquery' => ['url' => '//code.jquery.com/jquery-%1$s%2$s.js'],
        'cloudflare' => ['url' => '//cdnjs.cloudflare.com/ajax/libs/jquery/%1$s/jquery%2$s.js'],
        'jsdelivr' => ['url' => '//cdn.jsdelivr.net/npm/jquery@%1$s/dist/jquery%2$s.js']
    ];

    /**
     * Array of SRI hashes.
     * $cat jquery.js | openssl dgst -sha384 -binary | openssl base64 -A
     *
     * @var array
     */
    private $sriHashes = [
        3005000 => [
            'sha512-cEgdeh0IWe1pUYypx4mYPjDxGB/tyIORwjxzKrnoxcif2ZxI7fw81pZWV0lGnPWLrfIHGA7qc964MnRjyCYmEQ==',
            'sha512-k2WPPrSgRFI6cTaHHhJdc8kAXaRM4JBFEDo1pPGGlYiOyv4vnA0Pp0G5XMYYxgAPmtmv/IIaQA6n5fLAyJaFMA==',
            'sha512-yE2jpwVUl+o8LXPtfSETDOBUyQlMyyekY2u2Ds8d9OGVhQrCJMRl1f32gAZdg8ZE6Rcqf1Di8mu3L9vZtRIw5w==',
            'sha512-L/VGU4zvy5QAszSof84v07PyoLWojkWLGLlUMiFcIvgAP2jhPPdFmoT4MTt+rGMT9aAM6HQzIpL6sKsc7LdQtg=='
        ],
        3005001 => [
            'sha512-WNLxfP/8cVYL9sj8Jnp6et0BkubLP31jhTG9vhL/F5uEZmg5wEzKoXp1kJslzPQWwPT1eyMiSxlKCgzHLOTOTQ==',
            'sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==',
            'sha512-1lagjLfnC1I0iqH9plHYIUq3vDMfjhZsLy9elfK89RBcpcRcx4l+kRJBSnHh2Mh6kLxRHoObD1M5UTUbgFy6nA==',
            'sha512-/DXTXr6nQodMUiq+IUJYCt2PPOUjrHJ9wFrqpJ3XkgPNOZVfMok7cRw6CSxyCQxXn6ozlESsSh1/sMCTF1rL/g=='
        ],
        3006000 => [
            'sha512-n/4gHW3atM3QqRcbCn6ewmpxcLAHGaDjpEBu4xZd47N0W2oQ+6q7oc3PXstrJYXcbNU1OHdQ1T7pAP+gi5Yu8g==',
            'sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==',
            'sha512-HNbo1d4BaJjXh+/e6q4enTyezg5wiXvY3p/9Vzb20NIvkJghZxhzaXeffbdJuuZSxFhJP87ORPadwmU9aN3wSA==',
            'sha512-6ORWJX/LrnSjBzwefdNUyLCMTIsGoNP6NftMy2UAm1JBm6PRZCO1d7OHBStWpVFZLO+RerTvqX/Z9mBFfCJZ4A=='
        ],
        3006001 => [
            'sha512-CX7sDOp7UTAq+i1FYIlf9Uo27x4os+kGeoT7rgwvY+4dmjqV0IuE/Bl5hVsjnQPQiTOhAX1O2r2j5bjsFBvv/A==',
            'sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==',
            'sha512-1cF8XUz5U3BlnRVqNFn+aPNwwSr/FPtrmKvM1g4dJJ9tg8kmqRUzqbSOvRRAMScDnTkOcOnnfwF3+jRA/nE2Ow==',
            'sha512-yBpuflZmP5lwMzZ03hiCLzA94N0K2vgBtJgqQ2E1meJzmIBfjbb7k4Y23k2i2c/rIeSUGc7jojyIY5waK3ZxCQ=='
        ],
        3006002 => [
            'sha512-NMtENEqUQ8zHZWjwLg6/1FmcTWwRS2T5f487CCbQB3pQwouZfbrQfylryimT3XvQnpE7ctEKoZgQOAkWkCW/vg==',
            'sha512-tWHlutFnuG0C6nQRlpvrEhE4QpkG1nn2MOUMWmUeRePl4e3Aki0VB6W1v3oLjFtd0hVOtRQ9PHpSfN6u6/QXkQ==',
            'sha512-UgtCdy5Rd9HYhTf2bbpDOErKyhUevrYlfnMYm/nV5pb48ZN4i0cCw0vt0Gt2DBUWf7OBgcbLOzB7uhjuH3601w==',
            'sha512-1e7eG2wdX0SfkBsRkR+ETYdfg0UfcdMpYeH0FXKFCceSJkB9jzetxVpUvNAgTuUfJDhbRQdkuLvylB7U2N2uhg=='
        ],
        3006003 => [
            'sha512-nO7wgHUoWPYGCNriyGzcFwPSF+bPDOR+NvtOYy2wMcWkrnCNPKBcFEkU80XIN14UVja0Gdnff9EmydyLlOL7mQ==',
            'sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ==',
            'sha512-M3zrhxXOYQaeBJYLBv7DsKg2BWwSubf6htVyjSkjc9kPqx7Se98+q1oYyBJn2JZXzMaZvUkB8QzKAmeVfzj9ug==',
            'sha512-jxwTCbLJmXPnV277CvAjAcWAjURzpephk0f0nO2lwsvcoDMqBdy1rh1jEwWWTabX1+Grdmj9GFAgtN22zrV0KQ=='
        ],
        3006004 => [
            'sha512-6DC1eE3AWg1bgitkoaRM1lhY98PxbMIbhgYCGV107aZlyzzvaWCW1nJW2vDuYQm06hXrW0As6OGKcIaAVWnHJw==',
            'sha512-pumBsjNRGGqkPzKHndZMaAG+bir374sORyzM3uulLV14lN5LyykqNk8eEeUlUkB3U0M4FApyaHraT65ihJhDpQ==',
            'sha512-G1QAKkF7DrLYdKiz55LTK3Tlo8Vet2JnjQHuJh+LnU0zimJkMZ7yKZ/+lQ/0m94NC1EisSVS1b35jugu3wLdQg==',
            'sha512-fYjSocDD6ctuQ1QGIo9+Nn9Oc4mfau2IiE8Ki1FyMV4OcESUt81FMqmhsZe9zWZ6g6NdczrEMAos1GlLLAipWg=='
        ],
        3007000 => [
            'sha512-8Z5++K1rB3U+USaLKG6oO8uWWBhdYsM3hmdirnOEWp8h2B1aOikj5zBzlXs8QOrvY9OxEnD2QDkbSKKpfqcIWw==',
            'sha512-3gJwYpMe3QewGELv8k/BX9vcqhryRdzRMxVfq6ngyWXwo03GFEzjsUm8Q7RZcHPHksttq7/GFoxjCVUjkjvPdw==',
            'sha512-JC/KiiKXoc40I1lqZUnoRQr96y5/q4Wxrq5w+WKqbg/6Aq0ivpS2oZ24x/aEtTRwxahZ/KOApxy8BSZOeLXMiA==',
            'sha512-5NqgLBAYtvRsyAzAvEBWhaW+NoB+vARl6QiA02AFMhCWvPpi7RWResDcTGYvQtzsHVCfiUhwvsijP+3ixUk1xw=='
        ]
    ];

    /**
     * Insert javascript-tags for jQuery
     *
     * @param array $params Parameters
     * @param PageRenderer $pageRenderer PageRenderer
     * @return void
     */
    public function renderPreProcess(array $params, PageRenderer $pageRenderer): void
    {
        if (
            ($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface
            && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()
        ) {
            if ($this->initilialize()) {
                $this->prepareSettings();
                $this->setJsLibrary();
                $this->addJsLibrary($pageRenderer);
            }
        }
    }

    /**
     * Initialize the settings array and decide what to do next.
     *
     * @return bool
     */
    private function initilialize(): bool
    {
        // Get the plugin configuration
        $this->settings = GeneralUtility::makeInstance(ConfigurationManager::class)
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
            )['plugin.']['tx_libjquery.']['settings.'] ?? [];
        if (
            isset($this->settings['enable']) &&
            (bool) ($this->settings['enable']) &&
            isset($this->settings['source']) &&
            array_key_exists(
                $this->settings['source'],
                $this->jQueryCdnUrls
            )
        ) {
            return true;
        }
        return false;
    }

    /**
     * Fill the settings array with meaningful values.
     *
     * @return void
     */
    private function prepareSettings(): void
    {
        $source = (string) $this->settings['source'];
        $version = $this->settings['version'] ?? 'latest';
        $localFallback = $this->settings['localFallback'] ?? true;
        $excludeFromConcatenation = $this->settings['excludeFromConcatenation'] ?? false;
        $anonymousCrossorigin = $this->settings['anonymousCrossorigin'] ?? true;
        $slim = $this->settings['slim'] ?? false;
        $minify = $this->settings['minify'] ?? true;
        $forceOnTop = $this->settings['forceOnTop'] ?? true;
        $forceVersion = $this->settings['forceVersion'] ?? false;
        $localPath = $this->settings['localPath'] ?? '';
        $sriHash = $this->settings['sriHash'] ?? '';
        $addJsFooterLibrary = $this->settings['addJsFooterLibrary'] ?? false;

        $versionCdn = 0;
        if (!(empty($version) || $version === 'latest') && (bool) $forceVersion) {
            $versionCdn = VersionNumberUtility::convertVersionNumberToInteger($version);
        }
        if ($versionCdn === 0) {
            $versionCdn = end($this->availableLocalJqueryVersions);
        }

        // Set correct version-number for local version
        $versionLocal = $versionCdn;
        if (!in_array($versionCdn, $this->availableLocalJqueryVersions)) {
            $versionLocal = $this->getNearestVersion($versionCdn);
        }

        // Set path to the local fallback file
        if (empty($this->settings['localPath'])) {
            $localPath = $this->jQueryCdnUrls['local']['url'];
        }

        // Enable the use of an external CDN
        $useCdn = false;
        if ($source !== 'local') {
            $useCdn = true;
            $excludeFromConcatenation = true;
        }

        // Enable gzip-version if compression is enabled and concatenation is disabled
        $gzip = false;
        if ((int) ($GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel']) > 0 && (bool) $excludeFromConcatenation) {
            $gzip = true;
        }

        // Overwrite settings array
        $this->settings = [
            'source' => $source,
            'versionCdn' => $versionCdn,
            'versionLocal' => $versionLocal,
            'localFallback' => (bool) $localFallback,
            'excludeFromConcatenation' => (bool) $excludeFromConcatenation,
            'anonymousCrossorigin' => (bool) $anonymousCrossorigin,
            'slim' => (bool) $slim,
            'minify' => (bool) $minify,
            'forceOnTop' => (bool) $forceOnTop,
            'localPath' => (string) $localPath,
            'sriHash' => (string) $sriHash,
            'useCdn' => $useCdn,
            'gzip' => $gzip,
            'addJsFooterLibrary' => (bool) $addJsFooterLibrary
        ];
    }

    /**
     * Fill the JSLibrary array with useful values.
     *
     * @return void
     */
    private function setJsLibrary(): void
    {
        $filePart = ($this->settings['slim'] ? '.slim' : '') . ($this->settings['minify'] ? '.min' : '');
        $local = true;

        if ($this->settings['useCdn']) {
            $url = $this->jQueryCdnUrls[$this->settings['source']]['url'];
            $version = $this->settings['versionCdn'];
            $local = false;
            if ($this->settings['anonymousCrossorigin']) {
                if (!empty($this->settings['sriHash'])) {
                    // Override SRI Hash value
                    $this->jsLibrary['integrity'] = $this->settings['sriHash'];
                } else {
                    if (in_array($this->settings['versionCdn'], array_keys($this->sriHashes))) {
                        // Get hash index
                        $hashIndex = (int) ($this->settings['minify']);
                        if ($this->settings['slim']) {
                            $hashIndex += 2;
                        }
                        $this->jsLibrary['integrity'] = $this->sriHashes[$this->settings['versionCdn']][$hashIndex];
                    }
                }
            }
            if ($this->settings['localFallback']) {
                $this->jsLibrary['allWrap'] = $this->wrapFallback(
                    $this->replaceFilePlaceholders(
                        $this->settings['localPath'],
                        $this->convertIntegerToVersionNumber($this->settings['versionLocal']),
                        $filePart,
                        true
                    )
                );
            }
            $this->jsLibrary['crossorigin'] = $this->settings['anonymousCrossorigin'] ? 'anonymous' : '';
        } else {
            $url = $this->settings['localPath'];
            $version = $this->settings['versionLocal'];
        }
        $this->jsLibrary['excludeFromConcatenation'] = $this->settings['excludeFromConcatenation'];
        $this->jsLibrary['file'] = $this->replaceFilePlaceholders(
            $url,
            $this->convertIntegerToVersionNumber($version),
            $filePart,
            $local
        );
    }

     /**
      * Wrap the local fallback file in some javascript code which will enable the fallback.
      *
      * @return string
      */
    private function wrapFallback(string $file): string
    {
        return '|' . LF . '<script>window.jQuery || document.write(\'<script src="' .
            htmlspecialchars($file) .
            '" type="text/javascript"><\/script>\')</script>' . LF;
    }

    /**
     * Replace placeholders in file string.
     *
     * @param string $url
     * @param string $version
     * @param boolean $external
     *
     * @return string
     */
    private function replaceFilePlaceholders(
        string $url,
        string $version,
        string $filePart,
        bool $local = false
    ): string {
        $file = sprintf(
            $url,
            $version,
            $filePart
        ) . (($this->settings['gzip'] && $local) ? '.gzip' : '');
        // Get relative path if the given file is local
        if (substr($file, 0, 2) !== '//') {
            $file = PathUtility::stripPathSitePrefix(
                GeneralUtility::getFileAbsFileName($file)
            );
        }
        return $file;
    }

    /**
     * Add the library to the PageRenderer.
     *
     * @param \TYPO3\CMS\Core\Page\PageRenderer $pageRenderer
     *
     * @return void
     */
    private function addJsLibrary(PageRenderer $pageRenderer): void
    {
        if ($this->settings['addJsFooterLibrary']) {
            $pageRenderer->addJsFooterLibrary(
                'lib_jquery',
                $this->jsLibrary['file'],
                'text/javascript',
                false,
                $this->jsLibrary['forceOnTop'],
                $this->jsLibrary['allWrap'],
                $this->jsLibrary['excludeFromConcatenation'],
                '|',
                false,
                $this->jsLibrary['integrity'],
                false,
                $this->jsLibrary['crossorigin']
            );
        } else {
            $pageRenderer->addJsLibrary(
                'lib_jquery',
                $this->jsLibrary['file'],
                'text/javascript',
                false,
                $this->jsLibrary['forceOnTop'],
                $this->jsLibrary['allWrap'],
                $this->jsLibrary['excludeFromConcatenation'],
                '|',
                false,
                $this->jsLibrary['integrity'],
                false,
                $this->jsLibrary['crossorigin']
            );
        }
    }

    /**
     * Return nearest available version number.
     *
     * @param int $version String representation of the selected version number
     * @return int
     */
    private function getNearestVersion(int $version): int
    {
        // Get first available version provided by this extension
        $selectedVersion = reset($this->availableLocalJqueryVersions);
        foreach ($this->availableLocalJqueryVersions as $v) {
            if ($v < $version) {
                $selectedVersion = $v;
            } elseif ($v == $version) {
                $selectedVersion = $version;
                break;
            } elseif ($v > $version) {
                break;
            }
        }
        return $selectedVersion;
    }

    /**
     * Returns the three part version number (string) from an integer, eg 3015001 -> '3.15.1'.
     *
     * @param int $versionInteger Integer representation of version number
     * @return string Version number as format x.x.x
     */
    private function convertIntegerToVersionNumber(int $versionInteger = 0): string
    {
        $versionString = str_pad((string) $versionInteger, 9, '0', STR_PAD_LEFT);
        $parts = str_split($versionString, 3);
        return (int) $parts[0] . '.' . (int) $parts[1] . '.' . (int) $parts[2];
    }
}
