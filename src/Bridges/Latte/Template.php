<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Latte;

use Devly\ThemeKit\UI\Control;
use Devly\ThemeKit\UI\Presenter;
use Devly\WP\Models\Post;
use Devly\WP\Models\Site;
use Devly\WP\Models\Theme;
use Devly\WP\Models\User;
use Illuminate\Support\Collection;

class Template extends \Devly\ThemeKit\UI\Template
{
    public Site $site;
    public Theme $theme;
    public User $user;
    public ?Control $control;
    public ?Presenter $presenter;
    public ?Post $post;
    /** @var Collection<Post>|null */
    public ?Collection $posts = null;

    /**
     * Registers run-time filter.
     */
    public function addFilter(?string $name, callable $callback): self
    {
        $engine = $this->getEngine();

        $engine->addFilter($name, $callback);

        return $this;
    }

    /**
     * Registers run-time function.
     */
    public function addFunction(string $name, callable $callback): self
    {
        $engine = $this->getEngine();

        $engine->addFunction($name, $callback);

        return $this;
    }
}
