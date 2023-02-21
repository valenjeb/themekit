<?php

/** phpcs:disable Squiz.Functions.GlobalFunction.Found */

declare(strict_types=1);

namespace Devly\ThemeKit;

use Devly\Exceptions\FileNotFoundException;
use Devly\ThemeKit\Facades\App;
use Devly\ThemeKit\Facades\Bundle;
use Devly\ThemeKit\Facades\Engine;
use Devly\ThemeKit\Facades\Mix;
use Devly\ThemeKit\UI\Contracts\ITemplate;
use Devly\ThemeKit\Utils\SVG;
use Devly\WP\Assets\Asset;
use Devly\WP\Assets\Manager;

use function assert;
use function is_file;
use function is_string;

use const DIRECTORY_SEPARATOR;

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
 * Get an asset as an Asset object
 *
 * @param string $path A file path relative to the theme directory.
 *
 * @throws FileNotFoundException
 */
function asset(string $path): Asset
{
    $filePath = get_template_directory() . DIRECTORY_SEPARATOR . $path;
    $fileUrl  = get_template_directory_uri() . '/' . $path;

    $asset = new Asset($filePath, $fileUrl);

    if (! $asset->exists()) {
        throw new FileNotFoundException('No file found for path "' . $filePath . '".');
    }

    return $asset;
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
            return SVG::get($path);
        }

        $path = asset($path);
    }

    assert($path instanceof Asset);

    return SVG::get($path->path());
}
