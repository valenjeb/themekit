<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Facades;

use Devly\WP\Assets\Bundle as BundleObject;
use Devly\WP\Assets\Manager;

/**
 * @method static Manager add($name, ?BundleObject $bundle = null)
 * @method static BundleObject get(string $name)
 */
class Bundle extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Manager::class;
    }
}
