<?php

declare(strict_types=1);

namespace Devly\ThemeKit;

use Devly\ConfigLoader\Loader;
use Devly\ThemeKit\Bridges\Tracy\TracyExtension;
use Devly\ThemeKit\Facades\Facade;
use Nette\Utils\FileSystem;
use RuntimeException;
use Throwable;
use Tracy\Debugger;

use function array_merge;
use function func_get_args;
use function is_array;
use function sprintf;

class Bootloader
{
    /** @var string[] */
    protected array $configPaths  = [];
    protected string $environment = 'production';
    protected bool $debug         = false;
    protected ?string $logDir     = null;
    protected array $providers    = [];

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

    /**
     * @param string|string[] $service
     */
    public function addServiceProvider($service): self
    {
        $service = is_array($service) ? $service : func_get_args();

        $this->providers = array_merge($this->providers, $service);

        return $this;
    }

    public function run(): void
    {
        $app = new Application($this->environment, $this->debug);
        $app->alias('app', Application::class);

        Facade::setFacadeApplication($app);

        try {
            $loader = new Loader(false, $app->isProduction() && ! $app->isDebug() ? $app->getCacheDir() : null);
            $app->config()->merge($loader->load($this->configPaths, true));
        } catch (Throwable $e) {
            throw new RuntimeException('ThemeKit failed loading config files.', 0, $e);
        }

        foreach ($app->config('app.aliases', []) as $alias => $target) {
            $app->alias($alias, $target);
        }

        $providers = array_merge($app->config('app.providers', []), $this->providers);

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
}
