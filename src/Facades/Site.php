<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Facades;

/**
 * @method static int getID() Get blog ID
 * @method static string getName()
 * @method static string getDescription()
 * @method static string getWpurl()
 * @method static string getUrl()
 * @method static string getAdminUrl()
 * @method static string getAdminEmail()
 * @method static string getCharset()
 * @method static string getVersion()
 * @method static string getTextDirection()
 * @method static string getLanguage()
 * @method static bool isMultisite()
 * @method static mixed getOption(string $key, $default = null)
 * @method static string|null info(string $show = '', string $filter = 'raw') Retrieves information about the current site.
 */
class Site extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Devly\WP\Models\Site::class;
    }
}
