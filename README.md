[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg?style=for-the-badge)](https://www.paypal.me/quellenform)
[![Latest Stable Version](https://img.shields.io/packagist/v/quellenform/t3x-lib-jquery?style=for-the-badge)](https://packagist.org/packages/quellenform/t3x-lib-jquery)
[![TYPO3](https://img.shields.io/badge/TYPO3-10|11|12|13-%23f49700.svg?style=for-the-badge)](https://get.typo3.org/)
[![License](https://img.shields.io/packagist/l/quellenform/t3x-lib-jquery?style=for-the-badge)](https://packagist.org/packages/quellenform/t3x-lib-jquery)

# JS Library: jQuery

TYPO3 CMS Extension "lib_jquery"

## What does it do?

This extension integrates the jQuery-library from CDN with a local fallback if requested library is not available. All relevant versions of jQuery (including minified and gzipped-versions) are shipped with this extension.
The script is automatically added on top of your included javascript and loads the latest jquery-library per default.

## Installation/Configuration

1. Install extension with composer or from TER/git
2. Include the provided static template into your template-setup
3. Optional: modify the values in constant editor or by manually editing the typoscript
