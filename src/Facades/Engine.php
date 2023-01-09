<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Facades;

use Devly\ThemeKit\Bridges\Latte\LatteEngine;

/**
 * @method static void render(string $name, $params = [], ?string $block = null)
 * @method static string renderToString(string $name, $params = [], ?string $block = null)
 */
class Engine extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LatteEngine::class;
    }
}
