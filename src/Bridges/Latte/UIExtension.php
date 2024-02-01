<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Latte;

use Devly\ThemeKit\Bridges\Latte\Nodes\ControlNode;
use Devly\ThemeKit\UI\Control;
use Latte\Extension;

class UIExtension extends Extension
{
    public function __construct(
        private readonly ?Control $control,
    ) {
    }

    public function getProviders(): array
    {
        return [
            'uiControl' => $this->control,
            'uiPresenter' => $this->control?->getPresenterIfExists(),
        ];
    }

    public function getTags(): array
    {
        return [
            'control' => ControlNode::create(...),
        ];
    }
}
