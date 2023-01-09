<?php

declare(strict_types=1);

namespace Devly\ThemeKit\UI\Contracts;

interface IRenderable
{
    /**
     * Forces control to repaint.
     */
    public function redrawControl(): void;

    /**
     * Is required to repaint the control?
     */
    public function isControlInvalid(): bool;
}
