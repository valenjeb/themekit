<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Facades;

use Closure;
use Devly\DI\Contracts\IContainer;
use RuntimeException;

abstract class Facade
{
    /**
     * The application instance being facaded.
     */
    protected static IContainer $app;

    /**
     * The resolved object instances.
     *
     * @var object[]
     */
    protected static array $resolvedInstance;

    /**
     * Indicates if the resolved instance should be cached.
     */
    protected static bool $cached = true;

    /** @return mixed|object|null */
    public static function getInstance()
    {
        return static::getFacadeRoot();
    }

    /**
     * Run a Closure when the facade has been resolved.
     */
    public static function resolved(Closure $callback): void
    {
        $accessor = static::getFacadeAccessor();

        if (static::$app->resolved($accessor) === true) {
            $callback(static::getFacadeRoot());
        }

        static::$app->afterResolving($accessor, static function ($service) use ($callback): void {
            $callback($service);
        });
    }

    /**
     * Hotswap the underlying instance behind the facade.
     *
     * @param  mixed $instance
     */
    public static function swap($instance): void
    {
        static::$resolvedInstance[static::getFacadeAccessor()] = $instance;

        if (! isset(static::$app)) {
            return;
        }

        static::$app->instance(static::getFacadeAccessor(), $instance);
    }

    /**
     * Get the root object behind the facade.
     *
     * @return mixed
     */
    public static function getFacadeRoot()
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }

    /**
     * Get the registered name of the component.
     *
     * @throws RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        throw new RuntimeException('Facade does not implement getFacadeAccessor method.');
    }

    /**
     * Resolve the facade root instance from the container.
     *
     * @return mixed
     */
    protected static function resolveFacadeInstance(string $name)
    {
        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }

        if (! static::$app) {
            return null;
        }

        if (static::$cached) {
            return static::$resolvedInstance[$name] = static::$app[$name];
        }

        return static::$app[$name];
    }

    /**
     * Clear a resolved facade instance.
     */
    public static function clearResolvedInstance(string $name): void
    {
        unset(static::$resolvedInstance[$name]);
    }

    /**
     * Clear all the resolved instances.
     */
    public static function clearResolvedInstances(): void
    {
        static::$resolvedInstance = [];
    }

    /**
     * Get the application instance behind the facade.
     */
    public static function getFacadeApplication(): IContainer
    {
        return static::$app;
    }

    /**
     * Set the application instance.
     */
    public static function setFacadeApplication(IContainer $app): void
    {
        static::$app = $app;
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param list<mixed> $args
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public static function __callStatic(string $method, array $args)
    {
        $instance = static::getFacadeRoot();

        if (! $instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        return $instance->$method(...$args);
    }

    /**
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function __get(string $name)
    {
        $instance = static::getFacadeRoot();

        if (! $instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        return $instance->$name;
    }
}
