<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Assets;

use Devly\DI\Contracts\IContainer;
use Devly\ThemeKit\Application;
use Devly\ThemeKit\ServiceProvider;

class MixServiceProvider extends ServiceProvider
{
    /** @var array|string[]  */
    protected array $providers = [
        'mix',
        MixResolver::class,
    ];

    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
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
