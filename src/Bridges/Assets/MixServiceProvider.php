<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Assets;

use Devly\DI\Contracts\IContainer;
use Devly\DI\ServiceProvider;

class MixServiceProvider extends ServiceProvider
{
    /** @var array|string[] */
    public array $provides = [
        MixResolver::class,
    ];

    public function init(IContainer $di): void
    {
        $di->alias('mix', MixResolver::class);
    }

    public function register(IContainer $di): void
    {
        $di->define(MixResolver::class, MixResolverFactory::class)
            ->setParams([
                'manifestPath' => $di->config('assets.manifest'),
                'path'         => $di->config('assets.path'),
                'uri'          => $di->config('assets.uri'),
            ]);
    }
}
