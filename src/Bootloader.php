<?php

declare(strict_types=1);

namespace Devly\ThemeKit;

use Devly\ConfigLoader\Loader;
use Devly\ThemeKit\Bridges\ACF\ACFServiceProvider;
use Devly\ThemeKit\Bridges\Assets\AssetsServiceProvider;
use Devly\ThemeKit\Bridges\Assets\MixServiceProvider;
use Devly\ThemeKit\Bridges\Latte\LatteServiceProvider;
use Devly\ThemeKit\Bridges\Routing\RoutingServiceProvider;
use Devly\ThemeKit\Facades\Facade;
use RuntimeException;
use Throwable;

use function dirname;
use function func_get_args;
use function in_array;
use function is_array;
use function sprintf;

class Bootloader
{
    /** @var string[] */
    protected array $configPaths  = [];
    protected string $environment = Environment::PRODUCTION;
    protected bool $debug         = WP_DEBUG;
    protected ?string $logDir     = null;
    /** @var string[] */
    protected array $providers = [];

    public function loadGlobalFunctions(): self
    {
        require_once dirname(__FILE__) . '/globals.php';

        return $this;
    }

    public function addConfigPath(string $path): self
    {
        $this->configPaths[] = $path;

        return $this;
    }

    public function setDebug(bool $on = true): self
    {
        $this->debug = $on;

        return $this;
    }

    public function setEnvironment(string $type): self
    {
        $this->environment = $type;

        return $this;
    }

    public function setLogDirectory(string $path): self
    {
        $this->logDir = $path;

        return $this;
    }

    /** @param string|string[] $service */
    public function addServiceProvider($service): self
    {
        $service = is_array($service) ? $service : func_get_args();

        foreach ($service as $_s) {
            if (in_array($_s, $this->providers)) {
                continue;
            }

            $this->providers[] = $_s;
        }

        return $this;
    }

    public function run(): void
    {
        do_action(Hooks::ACTION_BEFORE_INIT, $this);

        $app = new Application($this->environment, $this->debug);
        $app->alias('app', Application::class);

        Facade::setFacadeApplication($app);

        try {
            $storage = $app->isProduction() ? $app->getCacheDir('themekit') : null;
            $loader  = new Loader(false, $storage);
            $app->config()->merge($loader->load($this->configPaths, true));
        } catch (Throwable $e) {
            throw new RuntimeException('ThemeKit failed loading config files.', 0, $e);
        }

        $aliases = apply_filters(Hooks::FILTER_REGISTERED_ALIASES, $app->config('app.aliases', []));

        foreach ($aliases as $alias => $target) {
            $app->alias($alias, $target);
        }

        $this->registerSupports($app->config('app.supports', []));
        $this->addServiceProvider($app->config('app.providers', []));

        $providers = apply_filters(Hooks::FILTER_REGISTERED_SERVICE_PROVIDERS, $this->providers);

        foreach ($providers as $provider) {
            try {
                $app->registerServiceProvider($app->call($provider));
            } catch (Throwable $e) {
                $message = sprintf('ThemeKit failed registering service provider "%s"', $provider);

                throw new RuntimeException($message, 0, $e);
            }
        }

        $app->bootServices();
    }

    /** @param array<string, string, array<int, string>> $supports */
    protected function registerSupports(array $supports): void
    {
        $mappings = [
            'routing' => RoutingServiceProvider::class,
            'assets'  => AssetsServiceProvider::class,
            'mix'     => MixServiceProvider::class,
            'latte'   => LatteServiceProvider::class,
            'acf'     => ACFServiceProvider::class,
        ];

        foreach ($supports as $feature) {
            $provider = $mappings[$feature] ?? null;

            if (empty($provider) || in_array($provider, $this->providers)) {
                continue;
            }

            $this->providers[] = $provider;
        }
    }
}
