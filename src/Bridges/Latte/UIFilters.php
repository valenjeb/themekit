<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Latte;

use Latte\Runtime\Html;

class UIFilters
{
    protected static UIFilters $instance;

    private function __construct(LatteEngine $engine)
    {
        /**
         * Filters text content and strips out disallowed HTML.
         *
         * This function makes sure that only the allowed HTML element names,
         * attribute names, attribute values, and HTML entities will occur in
         * the given text string.
         */
        $engine->addFilter('wpkses', static fn ($s, $context = 'post') => new Html(wp_kses($s, $context)));

        /**
         * Replaces double line breaks with paragraph elements.
         *
         * A group of regex replaces used to identify text formatted with newlines
         * and replace double line breaks with HTML paragraph tags. The remaining
         * line breaks after conversion become <br /> tags, unless $br is set to
         * '0' or 'false'
         */
        $engine->addFilter('wpautop', static fn ($s, bool $br = true) => new Html(wpautop($s, $br)));

        /**
         * Searches content for shortcodes and filter shortcodes through their hooks.
         *
         * If there are no shortcode tags defined, then the content will be returned
         * without any filtering. This might cause issues when plugins are disabled
         * but the shortcode will still show up in the post or content
         */
        $engine->addFilter('doShortcode', static fn ($content, bool $ignoreHtml = false) => new Html(do_shortcode($content, $ignoreHtml))); // phpcs:ignore

        /**
         * Sanitizes a filename, replacing whitespace with dashes.
         *
         * Removes special characters that are illegal in filenames on certain
         * operating systems and special characters requiring special escaping
         * to manipulate at the command line. Replaces spaces and consecutive
         * dashes with a single dash. Trims period, dash and underscore from
         * beginning and end of filename. It is not guaranteed that this
         * function will return a filename that is allowed to be uploaded.
         */
        $engine->addFilter('filename', static fn (string $filename) => sanitize_file_name($filename));

        /**
         * Escapes single quotes, ", <, >, &, and fixes line endings.
         *
         * Escapes text strings for echoing in JS. It is intended to be used
         * for inline JS (in a tag attribute, for example onclick="...").
         *
         * Note that the strings have to be in single quotes. The 'js_escape'
         * filter is also applied here.
         */
        $engine->addFilter('escjs', static fn ($text) => esc_js($text));
    }

    public static function install(LatteEngine $engine): void
    {
        if (isset(self::$instance)) {
            return;
        }

        self::$instance = new self($engine);
    }
}
