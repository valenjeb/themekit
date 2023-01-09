<?php

declare(strict_types=1);

namespace Devly\ThemeKit\UI;

use Nette\ComponentModel\IComponent;

final class Multiplier extends Component
{
    /** @var callable */
    private $factory;

    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }

    protected function createComponent(string $name): IComponent
    {
        return ($this->factory)($name, $this);
    }
}
