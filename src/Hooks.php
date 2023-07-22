<?php

declare(strict_types=1);

namespace Devly\ThemeKit;

class Hooks
{
    /** -------------------------------
     * Actions
     * ------------------------------- */
    public const ACTION_BEFORE_INIT = 'themekit/before_init';

    /** -------------------------------
     * Filters
     * ------------------------------- */
    public const FILTER_REGISTERED_ALIASES           = 'themekit/registered_aliases';
    public const FILTER_REGISTERED_SERVICE_PROVIDERS = 'themekit/registered_service_providers';
    public const FILTER_CACHE_DIR_PATH               = 'themekit/cache_directory_path';
}
