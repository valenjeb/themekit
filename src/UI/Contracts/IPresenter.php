<?php

declare(strict_types=1);

namespace Devly\ThemeKit\UI\Contracts;

use Devly\WP\Routing\Contracts\IResponse;
use Devly\WP\Routing\Request;

interface IPresenter
{
    /** @return IResponse|void */
    public function run(Request $request);
}
