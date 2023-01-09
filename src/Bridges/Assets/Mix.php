<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\Assets;

use Devly\WP\Assets\Asset;

class Mix
{
    private MixResolver $resolver;

    public function __construct(MixResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function get(string $path): Asset
    {
        return new Asset($this->resolver->getPath($path), $this->resolver->getUrl($path));
    }
}
