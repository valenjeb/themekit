<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Routing;

use Devly\DI\Contracts\IBootableServiceProvider;
use Devly\DI\Contracts\IContainer;
use Devly\ThemeKit\ServiceProvider;
use Devly\ThemeKit\UI\DefaultPresenter;
use Devly\WP\Routing\Hooks;
use Devly\WP\Routing\Router;

use function file_exists;

class RoutingServiceProvider extends ServiceProvider implements IBootableServiceProvider
{
    public function register(IContainer $di): void
    {
        $di->defineShared(Router::class);
    }

    public function boot(IContainer $di): void
    {
        add_filter(Hooks::FILTER_CONTROLLER_SUFFIX, static fn () => 'Presenter');
        add_filter(Hooks::FILTER_DEFAULT_CONTROLLER, static fn () => DefaultPresenter::class);
        add_filter(Hooks::FILTER_NAMESPACE, static fn () => $di->config(
            'view.namspace',
            $di->config('app.namespace', 'App\\') . 'UI\\Presenters'
        ));

        $router = $di->get(Router::class);
        if ($di->config('view.handle') === 'all') {
            $router->handleAllRequests(true);
        }

        $webRoutes = get_template_directory() . '/routes/web.php';

        if (file_exists($webRoutes)) {
            require_once $webRoutes;
        }

        $ajaxRoutes = get_template_directory() . '/routes/ajax.php';

        if (! file_exists($ajaxRoutes)) {
            return;
        }

        require_once $ajaxRoutes;
    }
}
