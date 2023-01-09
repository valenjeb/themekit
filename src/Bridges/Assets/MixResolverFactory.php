<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Assets;

use Devly\DI\Contracts\Factory;
use Devly\ThemeKit\Application;
use Devly\WP\Assets\Configurator;
use Devly\WP\Assets\Manifest;
use Devly\WP\Assets\Version\JsonManifestVersionStrategy;

class MixResolverFactory implements Factory
{
    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function create(string $manifestPath, string $path, string $uri): MixResolver
    {
        $config   = new Configurator($path, $uri, $this->app->isDebug());
        $manifest = new Manifest($manifestPath);

        $strategy = new JsonManifestVersionStrategy($manifest);

        return new MixResolver($config, $strategy);
    }
}
