<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Facades;

use Devly\ThemeKit\Bridges\Latte\LatteEngine;
use Devly\ThemeKit\UI\Finder;
use Latte\Macro;

/**
 * @method static void render(string $name, $params = [], ?string $block = null) Renders template to output.
 * @method static string renderToString(string $name, $params = [], ?string $block = null) Renders template to output.
 * @method static Finder getFinder Return an instance template file finder.
 * @method static LatteEngine addFilter(?string $name, callable $callback) Registers run-time filter.
 * @method static LatteEngine addFilterLoader(callable $callback) Registers filter loader.
 * @method static LatteEngine addFunction(string $name, callable $callback) Registers run-time function.
 * @method static LatteEngine addMacro(string $name, Macro $macro) Adds new macro.
 * @method static LatteEngine getInstance
 */
class Engine extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LatteEngine::class;
    }
}
