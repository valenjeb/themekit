<?php

declare(strict_types=1);

namespace Devly\ThemeKit\UI\Contracts;

use Devly\ThemeKit\UI\Control;

interface ITemplateFactory
{
    public function create(?Control $control = null, ?string $class = null): ITemplate;
}
