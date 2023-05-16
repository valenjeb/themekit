<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Assets;

use Devly\DI\ServiceProvider;
use Devly\ThemeKit\Application;

class MixServiceProvider extends ServiceProvider
{
    /** @var array|string[]  */
    public array $providers = [
        'mix',
        MixResolver::class,
    ];

    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function register(): void
    {
        $this->app->define(MixResolver::class, MixResolverFactory::class)
            ->setParams([
                'manifestPath' => $this->app->config('assets.manifest'),
                'path'         => $this->app->config('assets.path'),
                'uri'          => $this->app->config('assets.uri'),
            ]);
    }
}
