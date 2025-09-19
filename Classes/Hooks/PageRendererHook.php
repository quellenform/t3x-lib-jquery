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
 * Add jQuery library on top of javascript stack.
 */
final class PageRendererHook
{
    /**
     * @var int
     */
    private $version = 0;

    /**
     * @var bool
     */
    private $useCdn = true;

    /**
     * @var bool
     */
    private $localFallback = true;

    /**
     * @var bool
     */
    private $slim = false;

    /**
     * @var bool
     */
    private $minify = true;

    /**
     * @var bool
     */
    private $addJsFooterLibrary = false;

    /**
     * @var string
     */
    private $file = '';

    /**
     * @var bool
     */
    private $forceOnTop = true;

    /**
     * @var string
     */
    private $allWrap = '';

    /**
     * @var bool
     */
    private $excludeFromConcatenation = false;

    /**
     * @var string
     */
    private $integrity = '';

    /**
     * @var bool
     */
    private $anonymousCrossorigin = true;

    /**
     * Array of jQuery version numbers shipped with this extension.
     *
     * @var array
     */
    private $availableLocalJqueryVersions = [
        3005000,
        3005001,
        3006000,
        3006001,
        3006002,
        3006003,
        3006004,
        3007000,
        3007001
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
     * $cat jquery.js | openssl dgst -sha512 -binary | openssl base64 -A
     *
     * [$version] =>
     *   [0] => default
     *   [1] => minified
     *   [2] => slim
     *   [3] => slim-minified
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
        ],
        3007001 => [
            'sha512-+k1pnlgt4F1H8L7t3z95o3/KO+o78INEcXTbnoJQ/F2VqDVhWoaiVml/OEHv9HsVgxUaVW+IbiZPUJQfF/YxZw==',
            'sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==',
            'sha512-docBEeq28CCaXCXN7cINkyQs0pRszdQsVBFWUd+pLNlEk3LDlSDDtN7i1H+nTB8tshJPQHS0yu0GW9YGFd/CRg==',
            'sha512-sNylduh9fqpYUK5OYXWcBleGzbZInWj8yCJAU57r1dpSK9tP2ghf/SRYCMj+KsslFkCOt3TvJrX2AV/Gc3wOqA=='
        ]
    ];

    /**
     * Execute pre-render hook.
     *
     * @param array $params Parameters
     * @param PageRenderer $pageRenderer PageRenderer
     *
     * @return void
     */
    public function renderPreProcess(array $params, PageRenderer $pageRenderer): void
    {
        if (
            ($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface
            && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()
        ) {
            $this->process($pageRenderer);
        }
    }

    /**
     * Insert JavaScript tags for jQuery.
     *
     * @param PageRenderer $pageRenderer
     *
     * @return void
     */
    private function process(PageRenderer $pageRenderer): void
    {
        $settings = GeneralUtility::makeInstance(ConfigurationManager::class)
            ->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
            )['plugin.']['tx_libjquery.'] ?? [];

        if ($this->isEnabled($settings)) {
            $this->initialize($settings);
            if ($this->useCdn) {
                $this->excludeFromConcatenation = true;
            } else {
                $this->anonymousCrossorigin = false;
            }
            $this->file = $this->replaceFilePlaceholders(
                $this->jQueryCdnUrls[$settings['source']]['url']
            );
            $this->setIntegrity();
            $this->setAllwrap();
            $this->addJsLibrary($pageRenderer);
        }
    }

    /**
     * Check if jQuery should be integrated.
     *
     * @param array $settings
     *
     * @return bool
     */
    private function isEnabled(array $settings): bool
    {
        if (
            (bool) ($settings['enable'] ?? false)
            && array_key_exists(
                (string) ($settings['source'] ?? ''),
                $this->jQueryCdnUrls
            )
        ) {
            return true;
        }
        return false;
    }

    /**
     * Initialize.
     *
     * @param array $settings
     *
     * @return void
     */
    private function initialize(array $settings): void
    {
        $this->setVersions($settings['version'] ?? '');
        $this->useCdn = $settings['source'] === 'local' ? false : true;
        $this->localFallback = (bool) ($settings['localFallback'] ?? true);
        $this->excludeFromConcatenation = (bool) ($settings['excludeFromConcatenation'] ?? false);
        $this->anonymousCrossorigin = (bool) ($settings['anonymousCrossorigin'] ?? true);
        $this->slim = (bool) ($settings['slim'] ?? false);
        $this->minify = (bool) ($settings['minify'] ?? true);
        $this->forceOnTop = (bool) ($settings['forceOnTop'] ?? true);
        $this->addJsFooterLibrary = (bool) ($settings['addJsFooterLibrary'] ?? false);
    }

    /**
     * Set versions to be used.
     *
     * @param string $versionString
     *
     * @return void
     */
    private function setVersions(string $versionString): void
    {
        if (
            $versionString === 'latest'
            || empty($versionString)
        ) {
            // Get latest available version provided by this extension
            $versionNumber = end($this->availableLocalJqueryVersions);
        } else {
            // Get specific version
            $versionNumber = VersionNumberUtility::convertVersionNumberToInteger($versionString);
            if (!in_array($versionNumber, $this->availableLocalJqueryVersions)) {
                $versionNumber = $this->getNearestVersion($versionNumber);
            }
        }
        $this->version = $versionNumber;
    }

    /**
     * Get file parts for the final file name.
     *
     * @return string
     */
    private function getFileParts(): string
    {
        return (
            $this->slim ? '.slim' : ''
        ) . (
            $this->minify ? '.min' : ''
        );
    }

    /**
     * Set SRI hash for the CDN.
     *
     * @return void
     */
    private function setIntegrity(): void
    {
        if (
            $this->useCdn
            && $this->anonymousCrossorigin
            && in_array($this->version, array_keys($this->sriHashes))
        ) {
            $hashIndex = (int) $this->minify;
            if ($this->slim) {
                $hashIndex += 2;
            }
            $this->integrity = $this->sriHashes[$this->version][$hashIndex];
        }
    }

    /**
     * Set wrap around the locally integrated fallback JavaScript library.
     *
     * @return void
     */
    private function setAllwrap(): void
    {
        if (
            $this->useCdn
            && $this->localFallback
        ) {
            $this->allWrap = $this->wrapFallback(
                $this->replaceFilePlaceholders(
                    $this->jQueryCdnUrls['local']['url']
                )
            );
        }
    }

    /**
     * Wrap the local fallback file in some javascript code which will enable the fallback.
     *
     * @param string $file
     *
     * @return string
     */
    private function wrapFallback(string $file): string
    {
        return
            '|' . LF . '<script>window.jQuery || document.write(\'<script src="'
            . htmlspecialchars($file)
            . '" type="text/javascript"><\/script>\')</script>' . LF;
    }

    /**
     * Replace placeholders in file string.
     *
     * @param string $url
     *
     * @return string
     */
    private function replaceFilePlaceholders(string $url): string
    {
        $file = sprintf(
            $url,
            $this->convertIntegerToVersionNumber($this->version),
            $this->getFileParts()
        );
        // Get relative path if the given file is a local path
        if (substr($file, 0, 2) !== '//') {
            $file .= ($this->useCompression() ? '.gz' : '');
            $file = PathUtility::getPublicResourceWebPath($file);
        }
        return $file;
    }

    /**
     * Use gzipped version of the provided JavaScript files?
     *
     * @return bool
     */
    private function useCompression(): bool
    {
        if (
            $this->excludeFromConcatenation
            && (int) ($GLOBALS['TYPO3_CONF_VARS']['FE']['compressionLevel'] ?? 0) > 0
        ) {
            return true;
        }
        return false;
    }

    /**
     * Add the library to the PageRenderer.
     *
     * @param PageRenderer $pageRenderer
     *
     * @return void
     */
    private function addJsLibrary(PageRenderer $pageRenderer): void
    {
        $params = [
            'lib_jquery',
            $this->file,
            'text/javascript',
            false,
            $this->forceOnTop,
            $this->allWrap,
            $this->excludeFromConcatenation,
            '|',
            false,
            $this->integrity,
            false,
            $this->anonymousCrossorigin ? 'anonymous' : ''
        ];
        if ($this->addJsFooterLibrary) {
            $pageRenderer->addJsFooterLibrary(...$params);
        } else {
            $pageRenderer->addJsLibrary(...$params);
        }
    }

    /**
     * Return nearest available version number.
     *
     * @param int $version String representation of the selected version number
     *
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
     *
     * @return string Version number as format x.x.x
     */
    private function convertIntegerToVersionNumber(int $versionInteger = 0): string
    {
        $versionString = str_pad((string) $versionInteger, 9, '0', STR_PAD_LEFT);
        $parts = str_split($versionString, 3);
        return (int) $parts[0] . '.' . (int) $parts[1] . '.' . (int) $parts[2];
    }
}
