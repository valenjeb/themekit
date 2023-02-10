<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Latte;

use Devly\Exceptions\ObjectNotFoundException;
use Devly\ThemeKit\Application;
use Devly\ThemeKit\Facades\Site;
use Devly\ThemeKit\UI\Contracts\ITemplate;
use Devly\ThemeKit\UI\Contracts\ITemplateFactory;
use Devly\ThemeKit\UI\Control;
use Devly\ThemeKit\UI\Presenter;
use Devly\Utils\Arr;
use Devly\WP\Models\Page;
use Devly\WP\Models\Post;
use Devly\WP\Models\Theme;
use Devly\WP\Models\User;
use Illuminate\Support\Collection;
use WP_Post;

use function array_key_exists;
use function array_pop;
use function class_exists;
use function explode;
use function get_class;
use function implode;
use function str_replace;

class TemplateFactory implements ITemplateFactory
{
    /** @var array<callable(Template): void>  Occurs when a new template is created */
    public array $onCreate = [];
    protected LatteFactory $latteFactory;

    protected Application $app;
    private string $defaultTemplate;

    public function __construct(Application $app, ?string $defaultTemplate = null)
    {
        $this->defaultTemplate = $defaultTemplate ?? Template::class;
        $this->app             = $app;
    }

    public function create(?Control $control = null, ?string $class = null): ITemplate
    {
        $class   ??= $this->formatTemplateName($control);
        $presenter = $control ? $control->getPresenterIfExists() : null;
        $engine    = $this->getLatteEngine();

        $engine->addProvider('uiControl', $control);

        if ($presenter) {
            $engine->addProvider('uiPresenter', $presenter);
        }

        UIMacros::install($engine->getCompiler());
        UIRuntimeFunctions::install($engine);

        $template = $this->app->makeWith($class, ['engine' => $engine]);
        Arr::invoke($this->onCreate, $template);

        $template->control   = $control;
        $template->presenter = $presenter;
        $template->site      = Site::getInstance();
        $template->theme     = new Theme();
        $template->user      = new User(wp_get_current_user());
        $postTypes           = $this->app->config('app.posts', []);
        $postTypes['post']   = Post::class;
        $postTypes['page']   = Page::class;

        if (is_single() || is_page()) {
            $post = $GLOBALS['post'] ?? null;
            if ($post) {
                $template->post = $this->ensureGlobalPost($post, $postTypes);
            }
        }

        if (is_archive() || is_home() || is_search()) {
            $template->posts = $this->getGlobalPosts($postTypes);
        }

        return $template;
    }

    public function formatTemplateName(?Control $control = null): string
    {
        $namespace = $this->app->config(
            'view.namespace.template',
            $this->app->config('app.namespace', 'App') . '\\UI\\Templates'
        );

        $defaultTemplate = $this->app->config('view.template', $namespace . '\\DefaultTemplate');

        if ($control !== null) {
            $parts       = explode('\\', get_class($control));
            $controlName = array_pop($parts);
            if ($control instanceof Presenter) {
                $templateNamespace = str_replace(['Presenter', 'Controller'], '', $controlName);
            } else {
                $templateNamespace = str_replace('Component', '', $controlName);
            }

            $class = implode('\\', $parts) . '\\' . $templateNamespace . '\\Template';
            if (class_exists($class)) {
                return $class;
            }

            $className = str_replace(['Presenter', 'Controller', 'Component'], 'Template', $controlName);
            $class     = $namespace . '\\' . $className;
            if (class_exists($class)) {
                return $class;
            }

            $class = $namespace . '\\' . $controlName . 'Template';
            if (class_exists($class)) {
                return $class;
            }
        }

        if (class_exists($defaultTemplate)) {
            return $defaultTemplate;
        }

        return $this->defaultTemplate;
    }

    protected function getLatteEngine(): LatteEngine
    {
        return $this->app->get(LatteEngine::class);
    }

    /** @param array<string, string> $postTypes */
    protected function getGlobalPosts(array $postTypes): Collection
    {
        if ($this->app->has('view.posts')) {
            return $this->app->get('view.posts');
        }

        $posts = $GLOBALS['posts'] ?? [];

        $posts = Collection::make($posts)->map(static function ($post) use ($postTypes) {
            // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
            if (! array_key_exists($post->post_type, $postTypes)) {
                return new Post($post);
            }

            // phpcs:ignore Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
            $class = $postTypes[$post->post_type];

            try {
                return new $class($post);
            } catch (ObjectNotFoundException $e) {
                return new Post($post);
            }
        });

        $this->app->instance('view.posts', $posts);

        return $this->app->get('view.posts');
    }

    /** @param array<string, string> $postTypes */
    protected function ensureGlobalPost(WP_Post $post, array $postTypes): ?Post
    {
        if ($this->app->has('view.post')) {
            return $this->app->get('view.post');
        }

        try {
            $postType = $post->post_type; // phpcs:ignore

            if (array_key_exists($postType, $postTypes)) {
                try {
                    $postClass = $postTypes[$postType];

                    $post = new $postClass($post);
                } catch (ObjectNotFoundException $e) {
                    $post = null;
                }
            } else {
                $post = new Post($post);
            }

            $this->app->instance('view.post', $post);
        } catch (ObjectNotFoundException $e) {
            $this->app->instance('view.post', null);
        }

        return $this->app->get('view.post');
    }
}
