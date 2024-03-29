<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Latte;

use Devly\Exceptions\FileNotFoundException;
use Devly\ThemeKit\UI\Contracts\IEngine;
use Devly\ThemeKit\UI\Finder;
use Latte\Engine;

class LatteEngine extends Engine implements IEngine
{
    protected Finder $finder;

    public function __construct(Finder $finder)
    {
        parent::__construct();

        $this->finder = $finder;
    }

    /**
     * Renders template to output.
     *
     * @param object|array<string, mixed> $params
     *
     * @throws FileNotFoundException if template file not found.
     */
    public function render(string $name, object|array $params = [], ?string $block = null): void
    {
        $path = $this->finder->find($name);

        parent::render($path, $params, $block); // TODO: Change the autogenerated stub
    }

    /**
     * Renders template to output.
     *
     * @param object|array<string, mixed> $params
     *
     * @throws FileNotFoundException if template file not found.
     */
    public function renderToString(string $name, object|array $params = [], ?string $block = null): string
    {
        $path = $this->finder->find($name);

        return parent::renderToString($path, $params, $block); // TODO: Change the autogenerated stub
    }

    /**
     * Return an instance template file finder.
     */
    public function getFinder(): Finder
    {
        return $this->finder;
    }
}
