<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Facades;

/**
 * @method static mixed getOption(string $key, $default = null)
 */
class Theme extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Devly\WP\Models\Theme::class;
    }
}
