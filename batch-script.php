<?php
/**
 * BatchScript
 *
 * @wordpress-plugin
 * Plugin name:         Batch Script
 * Description:         Simply run a script in batches.
 * Version:             0.1.0
 * Requires at least:   6.0
 * Requires PHP:        8.0
 * Author:              Jeffrey van Rossum
 * Author URI:          https://vanrossum.dev
 * Text Domain:         batch-script
 * Domain Path:         /languages
 * License:             MIT
 */

use Jeffreyvr\BatchScript\BatchScript;

define('BATCHSCRIPT_PLUGIN_VERSION', '0.1.0');
define('BATCHSCRIPT_PLUGIN_FILE', __FILE__);
define('BATCHSCRIPT_PLUGIN_DIR', __DIR__);

if (! class_exists(BatchScript::class)) {
    if (is_file(__DIR__.'/vendor/autoload_packages.php')) {
        require_once __DIR__.'/vendor/autoload_packages.php';
    }
}

function batchScript()
{
    return BatchScript::instance();
}

batchScript();
