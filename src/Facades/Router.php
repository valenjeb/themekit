<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Facades;

use Closure;
use Devly\WP\Routing\Contracts\IRoute;
use Devly\WP\Routing\Routes\Ajax;
use Devly\WP\Routing\Routes\Query;
use Devly\WP\Routing\Routes\Route;

/**
 * @method static Route addRoute(string $pattern, $callback = null)
 * @method static Route redirect(string $path, string $target, int $status = 302)
 * @method static Route permanentRedirect(string $path, string $target)
 * @method static Route web(string $action, $callback = null)
 * @method static Ajax ajax(string $action, $callback = null)
 * @method static Query query(array $args = [], $callback = null)
 * @method static IRoute getRoute(string $name)
 * @method static void removeRoute(string $name)
 * @method static void editRoute(string $name, Closure $editCallback)
 * @method static bool hasRoute(string $name)
 * @method static Route getWebRoute(string $name)
 * @method static Ajax getAjaxRoute(string $name)
 */
class Router extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Devly\WP\Routing\Router::class;
    }
}
