<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Assets;

use Devly\DI\Contracts\IBootableServiceProvider;
use Devly\ThemeKit\Application;
use Devly\ThemeKit\ServiceProvider;
use Devly\WP\Assets\Bundle;
use Devly\WP\Assets\Manager;
use Devly\WP\Assets\Manifest;
use Devly\WP\Assets\Resolvers\EmptyResolver;
use Devly\WP\Assets\Resolvers\Resolver;
use Devly\WP\Assets\Resolvers\UrlResolver;
use Devly\WP\Assets\Version\JsonManifestVersionStrategy;
use Devly\WP\Assets\Version\StaticVersionStrategy;
use RuntimeException;
use Throwable;

use function file_exists;
use function sprintf;

class AssetsServiceProvider extends ServiceProvider implements IBootableServiceProvider
{
    /** @var array|string[] */
    protected array $providers = [
        Manager::class,
        UrlResolverFactory::class,
    ];

    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function register(): void
    {
        $this->app->defineShared(Manager::class);
        $this->app->defineShared(UrlResolverFactory::class);
    }

    public function boot(): void
    {
        $bundles = $this->app->config('assets.bundles', []);
        foreach ($bundles as $name => $options) {
            $this->registerBundle($name, $options);
        }
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws RuntimeException
     */
    private function registerBundle(string $name, array $options): void
    {
        try {
            $options  = $this->ensurePathAndUri($options);
            $resolver = $this->createResolver($options);
        } catch (Throwable $e) {
            throw new RuntimeException(sprintf('Failed create bundle "%s"', $name), 0, $e);
        }

        $manager = $this->app->get(Manager::class);

        $manager->add($name, new Bundle($name, $resolver, $options['bundle'] ?? []));
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws RuntimeException
     */
    protected function createManifestResolver(array $options): Resolver
    {
        $options = $this->ensureManifestPath($options);

        $manifest = new Manifest($options['manifest']);

        $options['versionStrategy'] = new JsonManifestVersionStrategy($manifest);

        $factory = $this->app->get(UrlResolverFactory::class);

        return $this->app->call([$factory, 'create'], $options);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws RuntimeException
     */
    protected function createStaticResolver(array $options): Resolver
    {
        $version = $options['version'] ?? null;
        $format  = $options['format'] ?? null;

        if (! $version) {
            throw new RuntimeException(
                'Bundle versioning strategy set to static but no static version.',
            );
        }

        $options['versionStrategy'] = new StaticVersionStrategy($version, $format);

        $factory = $this->app->get(UrlResolverFactory::class);

        return $this->app->call([$factory, 'create'], $options);
    }

    /** @param array<string, mixed> $options */
    protected function createEmptyUrlResolver(array $options): UrlResolver
    {
        $factory = $this->app->get(UrlResolverFactory::class);

        return $this->app->call([$factory, 'create'], $options);
    }

    /** @param array<string, mixed> $options */
    private function createMixResolver(array $options): MixResolver
    {
        $options = $this->ensureManifestPath($options);

        return $this->app->makeWith(MixResolver::class, $options);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     *
     * @throws RuntimeException
     */
    protected function ensureManifestPath(array $options): array
    {
        $manifestPath = $options['manifest'] ?? $options['manifestPath'] ?? $this->app->config('assets.manifest');

        if (! $manifestPath) {
            $manifestPath = $this->app->config('assets.path') . '/mix-manifest.json';

            if (! file_exists($manifestPath)) {
                throw new RuntimeException(sprintf(
                    'no manifest-path specified and the manifest file could not be located at the default path "%s".',
                    $manifestPath
                ));
            }
        }

        $options['manifestPath'] = $manifestPath;
        $options['manifest']     = $manifestPath;

        return $options;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws RuntimeException
     */
    protected function createResolver(array $options): Resolver
    {
        $versionStrategy = $options['strategy'] ?? null;

        if ($versionStrategy === 'mix') {
            return $this->createMixResolver($options);
        }

        if ($versionStrategy === 'manifest') {
            return $this->createManifestResolver($options);
        }

        if ($versionStrategy === 'static') {
            return $this->createStaticResolver($options);
        }

        if (isset($options['manifest'])) {
            return $this->createManifestResolver($options);
        }

        $path = $options['path'] ?? null;
        $uri  = $options['uri'] ?? null;
        if ($path === 'remote' || $uri === 'remote') {
            return new EmptyResolver();
        }

        return $this->createEmptyUrlResolver($options);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     *
     * @throws RuntimeException
     */
    protected function ensurePathAndUri(array $options): array
    {
        if (! isset($options['path']) && ! isset($options['uri'])) {
            $options['path'] = $this->app->config('assets.path');
            $options['uri']  = $this->app->config('assets.uri');
        }

        if (! $options['path']) {
            throw new RuntimeException('Bundle path is missing');
        }

        if (! $options['uri']) {
            throw new RuntimeException('Bundle uri is missing');
        }

        return $options;
    }
}
