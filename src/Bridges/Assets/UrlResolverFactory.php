<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Assets;

use Devly\ThemeKit\Application;
use Devly\WP\Assets\Configurator;
use Devly\WP\Assets\Resolvers\UrlResolver;
use Devly\WP\Assets\Version\EmptyVersionStrategy;
use Devly\WP\Assets\Version\VersionStrategy;

class UrlResolverFactory
{
    protected Application $app;

    public function __construct(Application $container)
    {
        $this->app = $container;
    }

    public function create(string $path, string $uri, ?VersionStrategy $versionStrategy): UrlResolver
    {
        $config   = new Configurator($path, $uri, $this->app->isDebug());
        $strategy = $versionStrategy ?? new EmptyVersionStrategy();

        return new UrlResolver($config, $strategy);
    }
}
