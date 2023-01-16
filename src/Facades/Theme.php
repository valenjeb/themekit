<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Facades;

use WP_Theme;

/**
 * @method static mixed getOption(string $key, mixed $default = null)
 * @method static bool setOption(string $name, mixed $value)
 * @method static bool deleteOption(string $name)
 * @method static array getOptions
 * @method static bool deleteOptions
 * @method static bool importOptions(string|\Devly\WP\Models\Theme $theme)
 * @method static array<string, mixed>|false|string display(string $header, bool $markup = true, bool $translate = true)
 * @method static array<string, mixed>|false|string get(string $header) Gets a raw, un-formatted theme header.
 * @method static string getName(bool $formatted = false) Retrieves the theme name
 * @method static string getDescription(bool $formatted = false) Retrieves the theme description
 * @method static string getVersion Retrieves the theme version
 * @method static string getTextDomain Retrieves the theme text domain
 * @method static string getSlug Returns the directory name of the theme's "stylesheet" files, inside the theme root.
 * @method static string getUrl Returns the URL to the directory of a theme's “stylesheet” files.
 * @method static string getPath Returns the absolute path to the directory of the theme's “stylesheet” files.
 * @method static string getTemplate
 * @method static string|null getScreenshot(bool $absolute = true)
 * @method static bool isActive
 * @method static bool hasParent
 * @method static \Devly\WP\Models\Theme getParent
 * @method static WP_Theme getCoreObject
 */
class Theme extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Devly\WP\Models\Theme::class;
    }
}
