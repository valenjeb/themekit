<?php

declare(strict_types=1);

namespace Devly\ThemeKit\UI;

use Devly\Exceptions\FileNotFoundException;

use function array_map;
use function func_get_args;
use function is_array;
use function is_file;
use function sprintf;

class Finder
{
    /** @var array|string[] */
    private array $paths;

    /** @param string|string[] $paths... */
    public function __construct($paths)
    {
        $this->paths = is_array($paths) ? $paths : func_get_args();
    }

    /** @throws FileNotFoundException If template file not found. */
    public function find(string $template): string
    {
        if (is_file($template)) {
            return $template;
        }

        $paths = array_map(
            static fn ($path) => (! empty($path) ? trailingslashit($path) : '') . $template,
            $this->paths
        );

        $located = locate_template($paths);

        if (! empty($located)) {
            return $located;
        }

        throw new FileNotFoundException(sprintf('Template file "%s" not found.', $template));
    }
}
