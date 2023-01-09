<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Latte;

use Devly\DI\Contracts\IBootableServiceProvider;
use Devly\DI\Contracts\IConfigProvider;
use Devly\DI\Contracts\IContainer;
use Devly\DI\DI;
use Devly\ThemeKit\Application;
use Devly\ThemeKit\ServiceProvider;
use Devly\ThemeKit\UI\Contracts\ITemplateFactory;
use Devly\ThemeKit\UI\DefaultPresenter;
use Devly\ThemeKit\UI\Finder;
use Devly\ThemeKit\UI\Presenter;
use Devly\WP\Routing\Hooks;

class LatteServiceProvider extends ServiceProvider implements IBootableServiceProvider, IConfigProvider
{
    protected array $providers = [
        LatteEngine::class,
        Finder::class,
    ];

    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function register(IContainer $di): void
    {
        $di->defineShared(Finder::class)
            ->setParam('paths', $di->config('view.paths', $di->config('view.dirname', 'views')));

        $di->defineShared(LatteEngine::class, LatteFactory::class)
            ->setParams([
                'finder' => DI::get(Finder::class),
                'cachePath' => $di->config('view.cache', $this->app->getCacheDir('views')),
                'autorefresh' => ! $this->app->isProduction() || $this->app->isProduction() && $this->app->isDebug(),
            ]);
    }

    public function boot(IContainer $di): void
    {
        add_filter(Hooks::FILTER_NAMESPACE, [$this, 'filterPresenterNamespace']);
        add_filter(Hooks::FILTER_CONTROLLER_SUFFIX, [$this, 'filterPresenterSuffix']);
        add_filter(Hooks::FILTER_DEFAULT_CONTROLLER, [$this, 'filterDefaultPresenterName']);

        if ($di->config('view.mode', Presenter::MODE_PRINT) !== Presenter::MODE_NO_PRINT) {
            return;
        }

        Presenter::$printMode = Presenter::MODE_NO_PRINT;
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

    public function provideConfig(IContainer $di): void
    {
        $di->alias(ITemplateFactory::class, TemplateFactory::class);
    }
}
