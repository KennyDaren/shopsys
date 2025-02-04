<?php

declare(strict_types=1);

namespace App\Component\GrapesJs;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Reference\W3CReference;

class GrapesJsParser
{
    private const GJS_PRODUCTS_REGEX = '/<div[^<>]*class="gjs-products"[^<>]*data-products="[\d\w,.][^"]*"[^<>]*><\/div>|<div[^<>]*data-products="[\d\w,.]*[^"]"[^<>]*class="gjs-products"[^<>]*><\/div>/';
    private const GJS_PRODUCT_REGEX = '/<section class=".+?">|<\/section>| id=".*?"|<div[^<>]*class="gjs-product"[^<>]*data-product="[\d\w,.]*[^"]"[^<>]*><\/div>|<div[^<>]*data-product="[\d\w,.]*[^"]"[^<>]*class="gjs-product"[^<>]*><\/div>/';
    private const GJS_PRODUCTS_SEPARATOR = '|||';

    /**
     * @param string|null $text
     * @return string|null
     */
    public function parse(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $newText = preg_replace_callback(self::GJS_PRODUCTS_REGEX, static function (array $matches): string {
            preg_match('/data-products="(.+?)"/', $matches[0], $productMatches);

            return sprintf('%s[gjc-comp-ProductList=%s]%s', self::GJS_PRODUCTS_SEPARATOR, $productMatches[1], self::GJS_PRODUCTS_SEPARATOR);
        }, preg_replace(self::GJS_PRODUCT_REGEX, '', $text));

        $sanitizer = $this->getConfiguredSanitizer();

        return $sanitizer->sanitize($newText);
    }

    /**
     * @return \Symfony\Component\HtmlSanitizer\HtmlSanitizer
     */
    private function getConfiguredSanitizer(): HtmlSanitizer
    {
        $config = (new HtmlSanitizerConfig())
            ->allowSafeElements()
            ->allowStaticElements()
            ->allowRelativeLinks()
            ->dropAttribute('style', 'div')
            ->allowElement('iframe', '*')
            ->withMaxInputLength(25000)
        ;

        foreach (array_keys(W3CReference::HEAD_ELEMENTS) as $element) {
            $config->allowElement($element, '*');
        }

        foreach (array_keys(W3CReference::BODY_ELEMENTS) as $element) {
            $config->allowElement($element, '*');
        }

        return new HtmlSanitizer($config);
    }
}
