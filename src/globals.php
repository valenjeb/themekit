<?php

/** phpcs:disable Squiz.Functions.GlobalFunction.Found */

declare(strict_types=1);

use Devly\ThemeKit\Application;
use Devly\ThemeKit\Facades\App;
use Devly\ThemeKit\Facades\Bundle;
use Devly\ThemeKit\Facades\Engine;
use Devly\ThemeKit\Facades\Mix;
use Devly\ThemeKit\UI\Contracts\ITemplate;
use Devly\WP\Assets\Asset;
use Devly\WP\Assets\Manager;

if (! function_exists('app')) {
    /** @return Application|mixed */
    function app(?string $key = null)
    {
        if ($key) {
            return App::get($key);
        }

        return App::getInstance();
    }
}

if (! function_exists('mix')) {
    function mix(string $path): Asset
    {
        return Mix::get($path);
    }
}

if (! function_exists('asset')) {
    function asset(string $path): Asset
    {
        return Mix::get($path);
    }
}

if (! function_exists('bundle')) {
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
}

if (! function_exists('view')) {
    /**
     * @param ITemplate|string            $template A template file path or a Template object
     * @param array<string, mixed>|object $params   A list of parameters to pass to the template
     */
    function view($template, $params = []): void
    {
        if ($template instanceof ITemplate) {
            $template->render(null, (array) $params);

            return;
        }

        Engine::render($template, $params);
    }
}
