<?php

declare(strict_types=1);

namespace Devly\ThemeKit\UI;

use Devly\ThemeKit\UI\Contracts\IEngine;
use Devly\ThemeKit\UI\Contracts\ITemplate;
use Devly\Utils\Arr;
use Exception;
use ReflectionObject;
use ReflectionProperty;
use RuntimeException;
use stdClass;

class Template implements ITemplate
{
    protected IEngine $engine;

    protected ?string $file = null;

    /** @var stdClass[] */
    public array $flashes = [];

    public function __construct(IEngine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Renders template to output.
     *
     * @param array<string, mixed> $args
     *
     * @throws RuntimeException If a template file has not been set.
     */
    final public function render(?string $template = null, array $args = []): void
    {
        Arr::toObject($args, $this);

        $template ??= $this->getFile();
        if (! $template) {
            throw new RuntimeException('A template file has not been set.');
        }

        $this->engine->render($template, $this);
    }

    /**
     * Renders template to string.
     *
     * @param array<string, mixed> $args
     *
     * @throws RuntimeException If a template file has not been set.
     */
    final public function renderToString(?string $template = null, array $args = []): string
    {
        Arr::toObject($args, $this);

        $template ??= $this->getFile();
        if (! $template) {
            throw new RuntimeException('A template file has not been set.');
        }

        return $this->engine->renderToString($template, $this);
    }

    /**
     * Retrieves the template file to be rendered by the composer.
     */
    final public function getFile(): ?string
    {
        return $this->file;
    }

    /**
     * Sets the path to the template file.
     */
    final public function setFile(string $file): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Returns array of all parameters.
     *
     * @return array<string, mixed>
     */
    final public function getParameters(): array
    {
        $res = [];
        foreach ((new ReflectionObject($this))->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            if (! $prop->isInitialized($this)) {
                continue;
            }

            $res[$prop->getName()] = $prop->getValue($this);
        }

        return $res;
    }

    /**
     * Sets all parameters.
     *
     * @param array<string, mixed> $params
     */
    public function setParameters(array $params): self
    {
        return Arr::toObject($params, $this);
    }

    /**
     * Renders template to string.
     */
    public function __toString(): string
    {
        return $this->renderToString($this->file, $this->getParameters());
    }

    /**
     * Prevents un-serialization.
     *
     * @throws Exception
     */
    final public function __wakeup(): void
    {
        throw new Exception('Object un-serialization is not supported by class ' . static::class);
    }

    public function getEngine(): IEngine
    {
        return $this->engine;
    }

    public function setEngine(IEngine $engine): void
    {
        $this->engine = $engine;
    }
}
