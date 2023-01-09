<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Facades;

use Devly\WP\Assets\Asset;

/**
 * @method static Asset get(string $path)
 */
class Mix extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Devly\ThemeKit\Bridges\Assets\Mix::class;
    }
}
