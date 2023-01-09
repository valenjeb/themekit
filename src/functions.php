<?php

/** phpcs:disable Squiz.Functions.GlobalFunction.Found */

declare(strict_types=1);

namespace Devly\ThemeKit;

use Devly\ThemeKit\Facades\App;
use Devly\ThemeKit\Facades\Bundle;
use Devly\ThemeKit\Facades\Engine;
use Devly\ThemeKit\Facades\Mix;
use Devly\ThemeKit\UI\Contracts\ITemplate;
use Devly\WP\Assets\Asset;
use Devly\WP\Assets\Manager;

/**
 * @return Application|mixed
 */
function app(string $key = null)
{
    if ($key) {
        return App::get($key);
    }

    return App::getInstance();
}

function mix(string $path): Asset
{
    return Mix::get($path);
}

function asset(string $path): Asset
{
    return Mix::get($path);
}

/**
 * Retrieve an assets bundle by name
 *
 * @return \Devly\WP\Assets\Bundle|Manager
 */
function bundle(string $name, ?\Devly\WP\Assets\Bundle $bundle = null)
{
    if ($bundle) {
        return Bundle::add($name, $bundle);
    }

    return Bundle::get($name);
}

/**
 * @param ITemplate|string            $template
 * @param array<string, mixed>|object $params
 */
function view($template, $params = []): void
{
    if ($template instanceof ITemplate) {
        $template->render(null, (array) $params);

        return;
    }

    Engine::render($template, $params);
}
