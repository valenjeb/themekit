<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\ACF;

use Devly\DI\Contracts\IBootableServiceProvider;
use Devly\DI\Contracts\IContainer;
use Devly\WP\Models\Filter;

use function function_exists;

class ACFServiceProvider implements IBootableServiceProvider
{
    public function boot(IContainer $app): void
    {
        add_filter(Filter::POST_PRE_GET_META_FIELD, [$this, 'getField'], 0, 3);
        add_filter(Filter::SITE_PRE_GET_OPTION, [$this, 'getOption'], 0, 2);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function getField($value, string $key, object $object)
    {
        if (! function_exists('get_field')) {
            return $value;
        }

        return get_field($key, $object->ID) ?? $value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function getOption($value, string $key)
    {
        if (! function_exists('get_field')) {
            return $value;
        }

        return get_field($key, 'option') ?? $value;
    }
}
