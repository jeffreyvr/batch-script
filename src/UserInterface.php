<?php

namespace Jeffreyvr\BatchScript;

class UserInterface
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'addMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    function enqueue(): void
    {
        $cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'application/x-httpd-php'));
        wp_localize_script('jquery', 'cm_settings', $cm_settings);

        wp_enqueue_style('wp-codemirror');
    }

    public function addMenu(): void
    {
        add_menu_page(
            __('Batch Script', 'batchscripts'),
            __('Batch Script', 'batchscripts'),
            'manage_options',
            'batch-script',
            [$this, 'render'],
            'dashicons-editor-code',
            99
        );
    }

    public function render(): void
    {
        include __DIR__.'/../views/user-interface.php';
    }
}
