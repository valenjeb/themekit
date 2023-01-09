<?php

declare(strict_types=1);

namespace Devly\ThemeKit\UI\Contracts;

/**
 * Component with ability to receive signal.
 */
interface ISignalReceiver
{
    public function signalReceived(string $signal): void; // handleSignal
}

