<?php

declare(strict_types=1);

namespace Devly\ThemeKit\UI\Contracts;

interface IEngine
{
    /**
     * Renders template to output.
     *
     * @param object|array<string, mixed> $params
     */
    public function render(string $name, $params = []): void;

    /**
     * Renders template to string.
     *
     * @param object|array<string, mixed> $params
     */
    public function renderToString(string $name, $params = []): string;
}
