<?php

/** phpcs:disable Squiz.Functions.GlobalFunction.Found */

declare(strict_types=1);

use Devly\ThemeKit\Application;
use Devly\ThemeKit\UI\Contracts\ITemplate;
use Devly\WP\Assets\Asset;
use Devly\WP\Assets\Bundle;
use Devly\WP\Assets\Manager;

if (! function_exists('app')) {
    /**
     * Retrieves an instance of current application object
     * or a service from the application container.
     *
     * @return Application|mixed
     */
    function app(?string $key = null)
    {
        return \Devly\ThemeKit\app($key);
    }
}

if (! function_exists('mix')) {
    /**
     * Get an asset from the assets manifest as an Asset object
     */
    function mix(string $path): Asset
    {
        return \Devly\ThemeKit\mix($path);
    }
}

if (! function_exists('asset')) {
    /**
     * Get an asset from the assets manifest as an Asset object
     */
    function asset(string $path): Asset
    {
        return \Devly\ThemeKit\asset($path);
    }
}

if (! function_exists('bundle')) {
    /**
     * Retrieve an assets bundle by name
     *
     * @return Bundle|Manager
     */
    function bundle(string $name, ?Bundle $bundle = null)
    {
        return \Devly\ThemeKit\bundle($name, $bundle);
    }
}

if (! function_exists('view')) {
    /**
     * Renders view to output
     *
     * @param ITemplate|string            $template A template file path or a Template object
     * @param array<string, mixed>|object $params   A list of parameters to pass to the template
     */
    function view($template, $params = []): void
    {
        \Devly\ThemeKit\view($template, $params);
    }
}

if (! function_exists('svg')) {
    /**
     * Renders SVG using provided path or Asset object
     *
     * @param string|Asset $path
     */
    function svg($path): string
    {
        return \Devly\ThemeKit\svg($path);
    }
}
