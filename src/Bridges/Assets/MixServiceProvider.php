<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Assets;

use Devly\DI\ServiceProvider;

class MixServiceProvider extends ServiceProvider
{
    /** @var array|string[] */
    public array $provides = [
        MixResolver::class,
    ];

    public function init(): void
    {
        $this->container->alias('mix', MixResolver::class);
    }

    public function register(): void
    {
        $this->container->define(MixResolver::class, MixResolverFactory::class)
            ->setParams([
                'manifestPath' => $this->container->config('assets.manifest'),
                'path'         => $this->container->config('assets.path'),
                'uri'          => $this->container->config('assets.uri'),
            ]);
    }
}
