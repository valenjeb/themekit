<?php

declare(strict_types=1);

namespace Devly\ThemeKit\UI\Contracts;

use RuntimeException;

interface ITemplate
{
    /**
     * Renders template to output.
     *
     * @param array<string, mixed> $args
     *
     * @throws RuntimeException If a template file has not been set.
     */
    public function render(?string $template = null, array $args = []): void;

    /**
     * Renders template to string.
     *
     * @param array<string, mixed> $args
     *
     * @throws RuntimeException If a template file has not been set.
     */
    public function renderToString(?string $template = null, array $args = []): string;

    /**
     * Sets the path to the template file.
     *
     * @return static
     */
    public function setFile(string $file): self;

    /**
     * Returns the path to the template file.
     */
    public function getFile(): ?string;

    /**
     * Sets all parameters.
     *
     * @param array<string, mixed> $params
     */
    public function setParameters(array $params): self;

    /**
     * Returns array of all parameters.
     *
     * @return array<string, mixed>
     */
    public function getParameters(): array;

    /**
     * Returns instance of the template engine attached to this template.
     */
    public function getEngine(): IEngine;

    public function setEngine(IEngine $engine): void;

    public function __toString(): string;
}
