<?php

declare(strict_types=1);

namespace Devly\ThemeKit;

use Devly\DI\Container;

use function in_array;
use function ltrim;
use function rtrim;

class Application extends Container
{
    protected string $env;
    protected bool $debug;

    public function __construct(string $env = Environment::PRODUCTION, bool $debug = false, bool $autowire = true, bool $shared = true)
    {
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
        $dirPath = apply_filters('themekit/cache_directory_path', WP_CONTENT_DIR . '/cache/themekit');

        return rtrim($dirPath, '/') . (! empty($path) ? '/' . ltrim($path, '/') : '');
    }
}
