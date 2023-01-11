<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Latte;

use Devly\Utils\Str;
use Latte\CompileException;
use Latte\Compiler;
use Latte\Helpers as LatteHelpers;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;

class UIMacros extends MacroSet
{
    public static function install(Compiler $compiler): void
    {
        $me = new static($compiler);
        $me->addMacro('component', [$me, 'macroControl']);
        $me->addMacro('control', [$me, 'macroControl']);
    }

    /**
     * {control name[:method] [params]}
     */
    public function macroControl(MacroNode $node, PhpWriter $writer): string
    {

        if ($node->context !== [\Latte\Compiler::CONTENT_HTML, \Latte\Compiler::CONTEXT_HTML_TEXT]) {
            $escapeMod = \Latte\Helpers::removeFilter($node->modifiers, 'noescape') ? '' : '|escape';
        }

        if ($node->modifiers) {
            trigger_error('Modifiers are deprecated in ' . $node->getNotation(), E_USER_DEPRECATED);
        }

        $node->modifiers .= $escapeMod ?? '';

        $words = $node->tokenizer->fetchWords();
        if (! $words) {
            throw new CompileException('Missing control name in {control}');
        }

        $name = $writer->formatWord($words[0]);
        $method = ucfirst($words[1] ?? '');
        $method = Str::match('#^\w*$#D', $method)
            ? "render$method"
            : "{\"render$method\"}";

        $tokens = $node->tokenizer;
        $pos = $tokens->position;
        $wrap = false;
        while ($tokens->nextToken()) {
            if ($tokens->isCurrent('=>', '(expand)') && !$tokens->depth) {
                $wrap = true;
                break;
            }
        }

        $tokens->position = $pos;
        $param = $wrap ? $writer->formatArray() : $writer->formatArgs();

        return "/* line $node->startLine */ "
            . ($name[0] === '$' ? "if (is_object($name)) \$_tmp = $name; else " : '')
            . '$_tmp = $this->global->uiControl->getComponent(' . $name . '); '
            . 'if ($_tmp instanceof Devly\ThemeKit\UI\IRenderable) $_tmp->redrawControl(null, false); '
            . ($node->modifiers === ''
                ? "\$_tmp->$method($param);"
                : $writer->write(
                    "ob_start(function () {}); \$_tmp->$method($param); \$ʟ_fi = new LR\\FilterInfo(%var); echo %modifyContent(ob_get_clean());",
                    \Latte\Engine::CONTENT_HTML,
                )
            );
    }

}
