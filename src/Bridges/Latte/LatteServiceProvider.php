<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Latte;

use Devly\DI\DI;
use Devly\DI\ServiceProvider;
use Devly\ThemeKit\Application;
use Devly\ThemeKit\UI\Contracts\ITemplateFactory;
use Devly\ThemeKit\UI\DefaultPresenter;
use Devly\ThemeKit\UI\Finder;
use Devly\ThemeKit\UI\Presenter;
use Devly\WP\Routing\Hooks;

class LatteServiceProvider extends ServiceProvider
{
    /** @var array|string[] */
    public array $provides = [
        LatteEngine::class,
        Finder::class,
    ];

    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;

        $this->init();
    }

    public function register(): void
    {
        $this->app->defineShared(Finder::class)
            ->setParam('paths', $this->app->config('view.paths', $this->app->config('view.dirname', 'views')));

        $this->app->defineShared(LatteEngine::class, LatteFactory::class)
            ->setParams([
                'finder' => DI::get(Finder::class),
                'cachePath' => $this->app->config('view.cache', $this->app->getCacheDir('views')),
                'autorefresh' => ! $this->app->isProduction() || $this->app->isProduction() && $this->app->isDebug(),
            ]);
    }

    public function boot(): void
    {
        add_filter(Hooks::FILTER_NAMESPACE, [$this, 'filterPresenterNamespace']);
        add_filter(Hooks::FILTER_CONTROLLER_SUFFIX, [$this, 'filterPresenterSuffix']);
        add_filter(Hooks::FILTER_DEFAULT_CONTROLLER, [$this, 'filterDefaultPresenterName']);

        Presenter::$printMode = $this->app->config('view.mode', Presenter::MODE_NO_PRINT);
    }

    public function filterPresenterNamespace(string $namespace): string
    {
        $namespace = $this->app->config('view.namespace.presenter');

        if ($namespace) {
            return $namespace;
        }

        return $this->app->config('app.namespace', 'App') . '\\UI\\Presenters';
    }

    public function filterPresenterSuffix(): string
    {
        return 'Presenter';
    }

    public function filterDefaultPresenterName(): string
    {
        return DefaultPresenter::class;
    }

    private function init(): void
    {
        $this->app->alias(ITemplateFactory::class, TemplateFactory::class);
    }
}
