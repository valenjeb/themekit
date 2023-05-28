<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Tracy;

use Devly\Exceptions\FileNotFoundException;
use Devly\Utils\Str;
use Nette\Utils\FileSystem;
use Tracy\Debugger;

use function file_exists;
use function is_string;
use function property_exists;
use function sprintf;

class TracyExtension
{
    /** @var array<string, mixed> */
    protected static array $config = [];

    /** @param string|array<string, mixed> $config A full config file path or a key value pair array. */
    public static function setConfig($config): void
    {
        if (is_string($config)) {
            if (! file_exists($config)) {
                throw new FileNotFoundException(sprintf('Provided string \'%s\' is not a valid file path.', $config));
            }

            $config = require $config;
        }

        self::$config = $config;
    }

    public static function enable(?bool $productionMode = null, ?string $logDirectory = null): void
    {
        $logDirectory ??= self::$config['log_directory'] ?? self::$config['logDirectory'] ?? null;
        unset(self::$config['log_directory'], self::$config['logDirectory']);

        if ($logDirectory) {
            FileSystem::createDir($logDirectory);
        }

        Debugger::enable($productionMode, $logDirectory);

        foreach (self::$config as $key => $value) {
            $key = Str::camelize($key);
            if (! property_exists(Debugger::class, $key)) {
                continue;
            }

            Debugger::${$key} = $value;
        }
    }
}
