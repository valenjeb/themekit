<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Facades;

use Devly\WP\Rest\Route;
use Devly\WP\Rest\Router;

/**
 * @method static Router group(string $pattern, callable $routes) Create a route group with shared attributes.
 * @method static Route match(string|string[] $methods, string $pattern, callable|string|array $callback) Add a new route with the given HTTP methods
 * @method static Route any(string $pattern, callable|string|array $callback) Add a route responding to all HTTP methods.
 * @method static Route get(string $pattern, callable|string|array $callback) Add a GET route.
 * @method static Route post(string $pattern, callable|string|array $callback) Add a POST route.
 * @method static Route put(string $pattern, callable|string|array $callback) Add a PUT route.
 * @method static Route patch(string $pattern, callable|string|array $callback) Add a PATCH route.
 * @method static Route delete(string $pattern, callable|string|array $callback) Add a DELETE route.
 * @method static Route addRoute(string $method, string $pattern, callable|string|array $callback)
 * @method static bool hasRoute(string $name)
 * @method static Route getRoute(string $name)
 */
class RestRouter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Router::class;
    }
}
