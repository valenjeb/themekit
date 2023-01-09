<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Latte;

use Devly\DI\Contracts\Factory;
use Devly\ThemeKit\UI\Finder;

class LatteFactory implements Factory
{
    protected Finder $finder;
    private string $cachePath;
    private bool $autorefresh;

    public function __construct(Finder $finder, ?string $cachePath = null, ?bool $autorefresh = null)
    {
        $this->cachePath   = $cachePath;
        $this->autorefresh = $autorefresh;
        $this->finder      = $finder;
    }

    public function create(?Finder $finder = null, ?string $cachePath = null, bool $autorefresh = true): LatteEngine
    {
        return (new LatteEngine($finder ?? $this->finder))
            ->setAutoRefresh($autorefresh ?? $this->autorefresh)
            ->setTempDirectory($cachePath ?? $this->cachePath);
    }
}
