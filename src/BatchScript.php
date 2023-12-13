<?php

namespace Jeffreyvr\BatchScript;

class BatchScript
{
    /**
     * @var BatchScript
     */
    private static $instance;

    public static function instance(): self
    {
        if (! isset(self::$instance) && ! (self::$instance instanceof BatchScript)) {
            self::$instance = new BatchScript();
        }

        return self::$instance;
    }

    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'loadTextdomain']);
        add_action('wp_ajax_bs_run', [Action::class, 'handle']);

        new UserInterface();
    }

    public function loadTextdomain(): void
    {
        load_plugin_textdomain(
            'batch-script',
            false,
            dirname(plugin_basename(BATCHSCRIPT_PLUGIN_FILE)).'/languages/'
        );
    }
}
