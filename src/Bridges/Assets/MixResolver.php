<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Assets;

use Devly\WP\Assets\Configurator;
use Devly\WP\Assets\Resolvers\Resolver;
use Devly\WP\Assets\Version\VersionStrategy;
use function file_exists;
use function file_get_contents;
use function strpos;
use function trim;
use const DIRECTORY_SEPARATOR;

class MixResolver implements Resolver
{
    protected Configurator $config;
    protected VersionStrategy $version;
    private ?string $hot;

    public function __construct(Configurator $config, VersionStrategy $version)
    {
        $this->config  = $config;
        $this->version = $version;
        $isHot         = file_exists($this->config->getPath('hot'));
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        $this->hot = $isHot ? trim(file_get_contents($this->config->getPath('hot'))) : null;
    }

    public function getUrl(string $path): string
    {
        if (strpos($path, DIRECTORY_SEPARATOR) !== 0) {
            $path = DIRECTORY_SEPARATOR . $path;
        }

        if ($this->config->isDebug() && $this->hot) {
            return $this->hot . $this->version->applyVersion($path);
        }

        return $this->config->getUri($this->version->applyVersion($path));
    }

    public function getPath(string $path): string
    {
        return $this->config->getPath($path);
    }
}
