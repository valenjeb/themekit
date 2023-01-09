<?php

/**
 * Plugin Name:     ThemeKit
 * Plugin URI:      https://github.com/valenjeb/themekit
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          Valentin Jebelev
 * Author URI:      https://github.com/valenjeb
 * Text Domain:     themekit
 * Domain Path:     /languages
 * Version:         0.1.0
 */

declare(strict_types=1);

$autoload = __DIR__ . '/vendor/autoload.php';

if (! file_exists($autoload)) {
	wp_die('ThemeKit dependencies are not available. Please run `composer install` from the plugin root directory.');
}

require_once $autoload;
