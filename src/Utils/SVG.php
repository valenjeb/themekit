<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Utils;

use function file_get_contents;

class SVG
{
    /** @var array<string, string> */
    private static array $cache = [];

    public static function get(string $path): string
    {
        if (! isset(self::$cache[$path])) {
            self::$cache[$path] = file_get_contents($path);
        }

        return self::$cache[$path];
    }
}
