<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Routing;

use Devly\DI\ServiceProvider;
use Devly\ThemeKit\Application;
use Devly\ThemeKit\UI\DefaultPresenter;
use Devly\WP\Routing\Hooks;
use Devly\WP\Routing\Router;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Http\Request as HttpRequest;
use Nette\Http\RequestFactory;
use Nette\Http\Response as HttpResponse;

use function file_exists;

class RoutingServiceProvider extends ServiceProvider
{
    /** @var string[] */
    public array $provides = [
        Router::class,
        HttpRequest::class,
    ];

    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->app->alias(IResponse::class, HttpResponse::class);
        $this->app->alias(IRequest::class, HttpRequest::class);
    }

    public function register(): void
    {
        $this->app->defineShared(HttpRequest::class, RequestFactory::class)->return('@fromGlobals');
        $this->app->defineShared(Router::class);
    }

    public function boot(): void
    {
        add_filter(Hooks::FILTER_CONTROLLER_SUFFIX, static fn () => 'Presenter');
        add_filter(Hooks::FILTER_DEFAULT_CONTROLLER, static fn () => DefaultPresenter::class);
        add_filter(Hooks::FILTER_NAMESPACE, fn () => $this->app->config(
            'view.namespace.presenter',
            $this->app->config('app.namespace', 'App\\') . 'UI\\Presenters'
        ));

        $router = $this->app->get(Router::class);
        if ($this->app->config('view.handle') === 'all') {
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
