<?php

declare(strict_types=1);

namespace Devly\ThemeKit;

use Devly\DI\Container;
use Nette\Utils\FileSystem;

use function in_array;
use function ltrim;
use function rtrim;

class Application extends Container
{
    protected string $env;
    protected bool $debug;
    protected string $cachePath;

    public function __construct(
        string $env = Environment::PRODUCTION,
        bool $debug = false,
        bool $autowire = true,
        bool $shared = true,
        ?string $cachePath = null
    ) {
        if (
            ! in_array(
                $env,
                [Environment::PRODUCTION, Environment::STAGING, Environment::DEVELOPMENT, Environment::LOCAL]
            )
        ) {
            $this->env = Environment::PRODUCTION;
        } else {
            $this->env = $env;
        }

        $this->debug = $debug;

        $this->cachePath = apply_filters('themekit/cache_directory_path', $cachePath ?? WP_CONTENT_DIR . '/cache');
        FileSystem::createDir($this->cachePath);

        parent::__construct([], $autowire, $shared);
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Get the current environment type.
     *
     * @return string Possible values are ‘local’, ‘development’, ‘staging’, and ‘production’.
     */
    public function getEnvironment(): string
    {
        return $this->env;
    }

    public function isProduction(): bool
    {
        return $this->getEnvironment() === Environment::PRODUCTION;
    }

    public function isDevelopment(): bool
    {
        return $this->getEnvironment() === Environment::DEVELOPMENT;
    }

    public function isStaging(): bool
    {
        return $this->getEnvironment() === Environment::STAGING;
    }

    public function isLocal(): bool
    {
        return $this->getEnvironment() === Environment::LOCAL;
    }

    public function getCacheDir(?string $path = null): string
    {
        return rtrim($this->cachePath, '/') . (! empty($path) ? '/' . ltrim($path, '/') : '');
    }
}
