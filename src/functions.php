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

use function assert;
use function file_get_contents;
use function is_file;
use function is_string;

/**
 * Retrieves an instance of current application object or a service from the application container.
 *
 * @return Application|mixed
 */
function app(?string $key = null)
{
    if ($key) {
        return App::get($key);
    }

    return App::getInstance();
}

/**
 * Get an asset from the assets manifest as an Asset object
 */
function mix(string $path): Asset
{
    return Mix::get($path);
}

/**
 * Get an asset from the assets manifest as an Asset object
 */
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
 * Renders view to output
 *
 * @param ITemplate|string            $template Template file path or an instance of object which implements ITemplate.
 * @param array<string, mixed>|object $params   List of params to pass to the template.
 */
function view($template, $params = []): void
{
    if ($template instanceof ITemplate) {
        $template->render(null, (array) $params);

        return;
    }

    Engine::render($template, $params);
}

/**
 * Renders SVG using provided path or Asset object
 *
 * @param string|Asset $path
 */
function svg($path): string
{
    if (is_string($path)) {
        if (is_file($path)) {
            return file_get_contents($path);
        }

        $path = asset($path);
    }

    assert($path instanceof Asset);

    return file_get_contents($path->path());
}
