<?php

declare(strict_types=1);

namespace Devly\ThemeKit;

use Devly\DI\Contracts\IServiceProvider;

use function in_array;

abstract class ServiceProvider implements IServiceProvider
{
    /** @var string[] List of services provided by the service provider. */
    protected array $providers = [];

    public function provides(string $key): bool
    {
        return in_array($key, $this->providers, true);
    }
}
