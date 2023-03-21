<?php

declare(strict_types=1);

namespace Devly\ThemeKit\Bridges\ACF;

use Devly\DI\Contracts\IBootableServiceProvider;
use Devly\WP\Models\Filter;
use Devly\WP\Models\Post;
use Devly\WP\Models\Term;

use function function_exists;
use function sprintf;

class ACFServiceProvider implements IBootableServiceProvider
{
    public function boot(): void
    {
        add_filter(Filter::POST_PRE_GET_META_FIELD, [$this, 'getField'], 0, 3);
        add_filter(Filter::POST_PRE_SET_META_FIELD, [$this, 'setField'], 0, 5);
        add_filter(Filter::SITE_PRE_GET_OPTION, [$this, 'getOption'], 0, 2);
        add_filter(Filter::SITE_PRE_SET_OPTION, [$this, 'setOption'], 0, 3);
        add_filter(Filter::TERM_PRE_GET_META_FIELD, [$this, 'getTermField'], 0, 3);
        add_filter(Filter::TERM_PRE_SET_META_FIELD, [$this, 'setTermField'], 0, 5);
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
     * @param mixed $previous
     */
    public function setField(?bool $result, string $key, $value, $previous, Post $postObject): ?bool
    {
        if (! function_exists('update_field')) {
            return $result;
        }

        return update_field($key, $value, $postObject->ID);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function getTermField($value, string $key, Term $object)
    {
        if (! function_exists('get_field')) {
            return $value;
        }

        $termID = sprintf('%s_%s', $object->getCoreObject()->taxonomy, $object->ID);

        return get_field($key, $termID) ?? $value;
    }

    /**
     * @param mixed $value
     * @param mixed $previous
     */
    public function setTermField(?bool $result, string $key, $value, $previous, Term $object): ?bool
    {
        if (! function_exists('update_field')) {
            return $result;
        }

        $termID = sprintf('%s_%s', $object->getCoreObject()->taxonomy, $object->ID);

        return update_field($key, $value, $termID);
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

    /** @param mixed $value */
    public function setOption(?bool $result, string $key, $value): ?bool
    {
        if (! function_exists('update_field')) {
            return $result;
        }

        return update_field($key, $value, 'option');
    }
}
