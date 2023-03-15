<?php

declare(strict_types=1);

namespace Devly\ThemeKit\UI;

use Devly\ThemeKit\UI\Contracts\IRenderable;
use Devly\ThemeKit\UI\Contracts\ITemplate;
use Devly\ThemeKit\UI\Contracts\ITemplateFactory;
use Invalid;
use Latte\Runtime\HtmlStringable;
use Nette\ComponentModel\IContainer;
use Nette\InvalidStateException;
use RuntimeException;
use stdClass;

use function array_shift;
use function count;

/**
 * @property-read ITemplate $template
 */
abstract class Control extends Component implements IRenderable
{
    private ?ITemplateFactory $templateFactory = null;

    private ?ITemplate $template = null;

    private array $invalidSnippets = [];

    /********************* template factory ****************d*g**/

    public function setTemplateFactory(ITemplateFactory $templateFactory): self
    {
        $this->templateFactory = $templateFactory;

        return $this;
    }

    public function getTemplate(): ITemplate
    {
        if ($this->template === null) {
            $this->template = $this->createTemplate();
        }

        return $this->template;
    }

    protected function createTemplate(?string $class = null): ITemplate
    {
        $templateFactory = $this->getTemplateFactory();

        return $templateFactory->create($this, $class);
    }

    public function getTemplateFactory(): ITemplateFactory
    {
        if (isset($this->templateFactory)) {
            return $this->templateFactory;
        }

        $parent = $this->getParent();

        if ($parent instanceof Multiplier) {
            $parent = $parent->getParent();
        }

        if ($parent instanceof Control) {
            return $parent->getTemplateFactory();
        }

        throw new InvalidStateException('Service TemplateFactory has not been set.');
    }

    /**
     * Saves the message to template, that can be displayed after redirect.
     *
     * @param  string|stdClass|HtmlStringable $message
     */
    public function flashMessage($message, string $type = 'info'): stdClass
    {
        $id                                           = $this->getParameterId('flash');
        $flash                                        = $message instanceof stdClass ? $message : (object) [
            'message' => $message,
            'type' => $type,
        ];
        $messages                                     = $this->getPresenter()->getFlashSession()->$id;
        $messages[]                                   = $flash;
        $this->getTemplate()->flashes                 = $messages;
        $this->getPresenter()->getFlashSession()->$id = $messages;

        return $flash;
    }

    /********************* rendering ****************d*g**/

    /**
     * Forces control or its snippet to repaint.
     */
    public function redrawControl(?string $snippet = null, bool $redraw = true): void
    {
        if ($redraw) {
            $this->invalidSnippets[$snippet ?? "\0"] = true;
        } elseif ($snippet === null) {
            $this->invalidSnippets = [];
        } else {
            $this->invalidSnippets[$snippet] = false;
        }
    }

    /**
     * Is required to repaint the control or its snippet?
     */
    public function isControlInvalid(?string $snippet = null): bool
    {
        if ($snippet !== null) {
            return $this->invalidSnippets[$snippet] ?? isset($this->invalidSnippets["\0"]);
        }

        if (count($this->invalidSnippets) > 0) {
            return true;
        }

        $queue = [$this];
        do {
            foreach (array_shift($queue)->getComponents() as $component) {
                if ($component instanceof IRenderable) {
                    if ($component->isControlInvalid()) {
                        // $this->invalidSnippets['__child'] = true; // as cache
                        return true;
                    }
                } elseif ($component instanceof IContainer) {
                    $queue[] = $component;
                }
            }
        } while ($queue);

        return false;
    }

    /**
     * Returns snippet HTML ID.
     */
    public function getSnippetId(string $name): string
    {
        // HTML 4 ID & NAME: [A-Za-z][A-Za-z0-9:_.-]*
        return 'snippet-' . $this->getUniqueId() . '-' . $name;
    }
}
