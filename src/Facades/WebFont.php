<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Facades;

use Devly\WP\Assets\WebFont\Family;
use Devly\WP\Assets\WebFont\WebFontsLoader;

/**
 * @method static void enqueue() Register web fonts.
 * @method static Family addFontFamily(string $name)
 * @method static Family addGoogleFont(string $name)
 */
class WebFont extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return WebFontsLoader::class;
    }
}
