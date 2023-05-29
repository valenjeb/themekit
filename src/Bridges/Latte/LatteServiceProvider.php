<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Latte;

use Devly\DI\Contracts\IContainer;
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

    protected IContainer $app;

    public function init(IContainer $di): void
    {
        $di->alias(ITemplateFactory::class, TemplateFactory::class);
    }

    /** @var Application $di */
    public function register(IContainer $di): void
    {
        $di->defineShared(Finder::class)
            ->setParam(
                'paths',
                $di->config('view.paths', $di->config('view.dirname', 'views'))
            );

        $di->defineShared(LatteEngine::class, LatteFactory::class)
            ->setParams([
                'finder'      => DI::get(Finder::class),
                'cachePath'   => $di->config('view.cache', $di->getCacheDir('views')),
                'autorefresh' => ! $di->isProduction() || $di->isProduction() && $di->isDebug(),
            ]);
    }

    public function boot(IContainer $di): void
    {
        $this->app = $di;

        add_filter(Hooks::FILTER_NAMESPACE, [$this, 'filterPresenterNamespace']);
        add_filter(Hooks::FILTER_CONTROLLER_SUFFIX, [$this, 'filterPresenterSuffix']);
        add_filter(Hooks::FILTER_DEFAULT_CONTROLLER, [$this, 'filterDefaultPresenterName']);

        Presenter::$printMode = $di->config('view.mode', Presenter::MODE_NO_PRINT);
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
}
