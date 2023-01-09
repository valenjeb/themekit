<?php

declare(strict_types=1);

namespace Devly\ThemeKit\UI;

use ArrayAccess;
use Devly\ThemeKit\UI\Contracts\ISignalReceiver;
use Nette\ComponentModel\Container;
use Nette\ComponentModel\IComponent;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

use function sprintf;

abstract class Component extends Container implements ISignalReceiver, ArrayAccess
{
    use \Nette\ComponentModel\ArrayAccess;

    /** @var array<string, mixed> */
    protected array $params;

    /**
     * Returns the presenter where this component belongs to.
     */
    public function getPresenter(): Presenter
    {
        return $this->lookup(Presenter::class);
    }

    /**
     * Returns the presenter where this component belongs to.
     */
    public function getPresenterIfExists(): ?Presenter
    {
        return $this->lookup(Presenter::class, false);
    }

    /**
     * Returns a fully-qualified name that uniquely identifies the component
     * within the presenter hierarchy.
     */
    public function getUniqueId(): string
    {
        return $this->lookupPath(Presenter::class);
    }

    protected function createComponent(string $name): ?IComponent
    {
        $res = parent::createComponent($name);
        if ($res && ! $res instanceof ISignalReceiver) {
            $type = get_class($res);
            $msg  = sprintf(
                "It seems that component '%s' of type %s is not intended to be used in the Presenter.",
                $name,
                $type
            );
            trigger_error(esc_html($msg));
        }

        return $res;
    }

    /**
     * Returns component param.
     *
     * @param mixed $default
     *
     * @return mixed
     */
    final public function getParameter(string $name, $default = null)
    {
        return $this->params[$name] ?? $default;
    }

    /**
     * Returns component parameters.
     *
     * @return array<string, mixed>
     */
    final public function getParameters(): array
    {
        return $this->params;
    }

    /**
     * Returns a fully-qualified name that uniquely identifies the parameter.
     */
    final public function getParameterId(string $name): string
    {
        $uid = $this->getUniqueId();

        return $uid === '' ? $name : $uid . self::NAME_SEPARATOR . $name;
    }

    /**
     * Calls public method if exists.
     *
     * @param array<string, mixed> $params
     *
     * @return bool does method exist?
     */
    protected function tryCall(string $method, array $params): bool
    {
        $rc = new ReflectionClass(static::class);

        if ($rc->hasMethod($method)) {
            $rm = $rc->getMethod($method);
            if ($rm->isPrivate()) {
                throw new RuntimeException(sprintf(
                    'Method %s() can not be called because it is private.',
                    $rm->getName()
                ));
            }

            try {
                $rm->invokeArgs($this, $params);
            } catch (ReflectionException $e) {
                throw new RuntimeException(sprintf(
                    'Method %s() can not be called invoked.',
                    $rm->getName()
                ), 0, $e);
            }

            return true;
        }

        return false;
    }

    /**
     * Calls signal handler method.
     *
     * @throws RuntimeException if there is no handler method.
     */
    public function signalReceived(string $signal): void
    {
        if (! $this->tryCall(self::formatSignalMethod($signal), $this->params)) {
            $class = static::class;

            throw new RuntimeException(sprintf("There is no handler for signal '%s' in class %s.", $signal, $class));
        }
    }

    /**
     * Formats signal handler method name -> case sensitivity doesn't matter.
     */
    public static function formatSignalMethod(string $signal): string
    {
        return 'handle' . $signal;
    }
}
