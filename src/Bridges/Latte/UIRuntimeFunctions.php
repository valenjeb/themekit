<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Latte;

use Devly\Exceptions\FileNotFoundException;
use Devly\ThemeKit\Application;
use Devly\WP\Assets\Asset;
use Latte\Runtime\Html;
use Throwable;
use WP_Post_Type;
use WP_Term;

use function Devly\ThemeKit\svg;

final class UIRuntimeFunctions
{
    private static UIRuntimeFunctions $instance;
    private LatteEngine $engine;

    private function __construct(LatteEngine $engine)
    {
        $this->engine = $engine;

        $engine->addFunction('get_header', [$this, 'getHeader']);
        $engine->addFunction('get_footer', [$this, 'getFooter']);
        $engine->addFunction('get_template_part', [$this, 'getTemplatePart']);
        $engine->addFunction('render_template', [$this, 'renderTemplate']);
        $engine->addFunction('archive_has_thumbnail', [$this, 'archiveHasThumbnail']);
        $engine->addFunction('archive_thumbnail_id', [$this, 'archiveThumbnailId']);
        $engine->addFunction('the_archive_thumbnail', [$this, 'theArchiveThumbnail']);
        $engine->addFunction('get_the_archive_thumbnail_url', [$this, 'getTheArchiveThumbnailUrl']);
        $engine->addFunction('svg', [$this, 'renderSvg']);
    }

    public static function install(LatteEngine $engine): void
    {
        if (isset(self::$instance)) {
            return;
        }

        self::$instance = new self($engine);
    }

    /** @param array<string, mixed> $args */
    public function getHeader(?string $name = null, array $args = []): void
    {
        try {
            $templates = [];
            if ($name) {
                $templates[] = sprintf('partials/header-%s.latte', $name);
                $templates[] = sprintf('header-%s.latte', $name);
            }

            $templates[] = 'partials/header.latte';
            $templates[] = 'header.latte';

            do_action('get_header', $name, $args);

            $this->renderTemplate($templates);
        } catch (FileNotFoundException $e) {
            get_header($name, $args);
        }
    }

    /** @param array<string, mixed> $args */
    public function getFooter(?string $name = null, array $args = []): void
    {
        try {
            $templates = [];
            if ($name) {
                $templates[] = sprintf('partials/footer-%s.latte', $name);
                $templates[] = sprintf('footer-%s.latte', $name);
            }

            $templates[] = 'partials/footer.latte';
            $templates[] = 'footer.latte';

            do_action('get_footer', $name, $args);

            $this->renderTemplate($templates);
        } catch (Throwable $e) {
            get_footer($name, $args);
        }
    }

    /** @param array<string, mixed> $args */
    public function getTemplatePart(string $slug, ?string $name = null, array $args = []): void
    {
        try {
            do_action('get_template_part_' . $slug, $slug, $name, $args);

            $templates = [];
            if ($name) {
                $templates[] = sprintf('%s-%s.latte', $slug, $name);
            }

            $templates[] = $slug . '.latte';

            do_action('get_template_part', $slug, $name, $templates, $args);

            $this->renderTemplate($templates, $args);
        } catch (Throwable $e) {
            get_template_part($slug, $name, $args);
        }
    }

    /**
     * @param string|string[] $templates
     * @param mixed           $params
     */
    public function renderTemplate($templates, $params = []): void
    {
        $templates = is_array($templates) ? $templates : [$templates];
        $located   = null;

        foreach ($templates as $template) {
            try {
                $located = $this->engine->getFinder()->find($template);
                break;
            } catch (FileNotFoundException $e) {
            }
        }

        if (! $located) {
            $message = sprintf('Templates could not be located: %s.', implode(', ', $templates));

            throw new FileNotFoundException($message);
        }

        $this->engine->render($located, $params);
    }

    public function archiveHasThumbnail(): bool
    {
        return $this->archiveThumbnailId() !== 0;
    }

    public function archiveThumbnailId(): int
    {
        $obj = get_queried_object();
        if ($obj instanceof WP_Post_Type) {
            return get_option($obj->name . '_archive_thumbnail_id', 0);
        }

        if ($obj instanceof WP_Term) {
            return get_term_meta($obj->term_id, '_archive_thumbnail_id') ?: 0;
        }

        return 0;
    }

    /**
     * @param string|string[] $attr
     */
    public function getTheArchiveThumbnail(string $size = 'post-thumbnail', $attr = ''): string
    {
        $attachment_id = $this->archiveThumbnailId();

        $html = '';

        if ($attachment_id !== 0) {
            $html = wp_get_attachment_image($attachment_id, $size, false, $attr);
        }

        return $html;
    }

    /**
     * @param string|string[] $attr
     */
    public function theArchiveThumbnail(string $size = 'post-thumbnail', $attr = ''): void
    {
        echo $this->getTheArchiveThumbnail($size, $attr);
    }

    public function getTheArchiveThumbnailUrl(string $size = 'post-thumbnail')
    {
        $attachment_id = $this->archiveThumbnailId();

        if ($attachment_id === 0) {
            return false;
        }

        return wp_get_attachment_image_url($this->archiveThumbnailId(), $size);
    }

    /** @param string|Asset $path */
    public function renderSvg($path): Html
    {
        return new Html(svg($path));
    }
}
