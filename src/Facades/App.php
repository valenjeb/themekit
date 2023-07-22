<?php

declare(strict_types=1);

// phpcs:disable Generic.Files.LineLength.TooLong

namespace Devly\ThemeKit\Facades;

use Devly\DI\Contracts\IBootableProvider;
use Devly\DI\Contracts\IContainer;
use Devly\DI\Contracts\IResolver;
use Devly\DI\Contracts\IServiceProvider;
use Devly\DI\Definition;
use Devly\Repository;
use Devly\ThemeKit\Application;

/**
 * @method static Application sharedByDefault(bool $shared = true) Set the container services to be shared by default
 * @method static Application autowire(bool $enable = true) Enable autowiring
 * @method static bool has(string $key) Checks wetter an item exists in the container.
 * @method static void instance(string $key, $value) Store an instance of a service in the container.
 * @method static Definition define(string $key, Definition|callable|string|null $value) Define an object or a value in the container.
 * @method static Definition defineShared(string $key, Definition|callable|string|null $value) Add a shared factory definition.
 * @method static Definition override(string $key, Definition|callable|string|null $value) Define or override an object or a value in the container.
 * @method static Definition overrideShared(string $key, Definition|callable|string|null $value) Define or override an object or a value in the container.
 * @method static Definition extend(string $key) Extend existing factory definition.
 * @method static void alias(string $name, string $target) Add a named alias to a service in the container.
 * @method static mixed get(string $key) Retrieve an object or a value from the container.
 * @method static mixed getSafe(string $key, mixed $default = null) Retrieve an object or a value from the container or return default value if not found.
 * @method static mixed make(string $key) Retrieve a new instance of an object from the container.
 * @method static mixed makeWith(string $key, array $args) Retrieve a new instance of an object from the container with a list of args.
 * @method static mixed call($callbackOrClassName, array $args = []) Resolve a callable or an object using the container.
 * @method static void forget(string $name) Drop a service definition and its instance from the container.
 * @method static void beforeResolving(string $key, callable $callback) Register a callback that will run before a service is resolved
 * @method static void afterResolving(string $key, callable $callback) Register a callback that will run after a service is resolved.
 * @method static bool resolved(string $key) Checks whether a resolved instance of a provided key name exists in the container.
 * @method static bool hasDefinition(string $key) Checks whether a definition of a provided key name exists in the container.
 * @method static IResolver getResolver()
 * @method static void register(IServiceProvider|IBootableProvider|object|string $provider)
 * @method static Application bindContainer(IContainer $container)
 * @method static string getEnvironment()
 * @method static bool isProduction()
 * @method static bool isDevelopment()
 * @method static bool isStaging()
 * @method static Application getInstance()
 * @method static mixed|Repository config(?string $key = null, mixed $default = null)
 */
class App extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'app';
    }
}
