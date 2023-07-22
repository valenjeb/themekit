<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Latte;

use Devly\DI\Contracts\IBootableProvider;
use Devly\DI\Contracts\IContainer;
use Devly\DI\DI;
use Devly\DI\ServiceProvider;
use Devly\ThemeKit\Application;
use Devly\ThemeKit\UI\Contracts\ITemplateFactory;
use Devly\ThemeKit\UI\DefaultPresenter;
use Devly\ThemeKit\UI\Finder;
use Devly\ThemeKit\UI\Presenter;
use Devly\WP\Routing\Hooks;

class LatteServiceProvider extends ServiceProvider implements IBootableProvider
{
    /** @var array|string[] */
    public array $provides = [
        LatteEngine::class,
        Finder::class,
    ];

    /** @var Application */
    protected IContainer $container;

    public function init(): void
    {
        $this->container->alias(ITemplateFactory::class, TemplateFactory::class);
    }

    /** @var Application $di */
    public function register(): void
    {
        $this->container->defineShared(Finder::class)
            ->setParam(
                'paths',
                $this->container->config('view.paths', $this->container->config('view.dirname', 'views'))
            );

        $this->container->defineShared(LatteEngine::class, LatteFactory::class)
            ->setParams([
                'finder'      => DI::get(Finder::class),
                'cachePath'   => $this->container->config('view.cache', $this->container->getCacheDir('views')),
                'autorefresh' => ! $this->container->isProduction()
                    || $this->container->isProduction() && $this->container->isDebug(),
            ]);
    }

    public function boot(): void
    {
        add_filter(Hooks::FILTER_NAMESPACE, [$this, 'filterPresenterNamespace']);
        add_filter(Hooks::FILTER_CONTROLLER_SUFFIX, [$this, 'filterPresenterSuffix']);
        add_filter(Hooks::FILTER_DEFAULT_CONTROLLER, [$this, 'filterDefaultPresenterName']);

        Presenter::$printMode = $this->container->config('view.mode', Presenter::MODE_NO_PRINT);
    }

    public function filterPresenterNamespace(string $namespace): string
    {
        $namespace = $this->container->config('view.namespace.presenter');

        if ($namespace) {
            return $namespace;
        }

        return $this->container->config('app.namespace', 'App') . '\\UI\\Presenters';
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
