<?php

namespace Jeffreyvr\BatchScript;

use Jeffreyvr\BatchScript\Batch;

class Action
{
    public static function handle()
    {
        if(! current_user_can('manage_options')) {
            wp_send_json_error('You do not have sufficient permissions to access this page.');
        }

        $batch_id = $_POST['batch_id'] ?? null;

        $batch = new Batch($batch_id, [
            'code' => $_POST['arguments']['code'],
            'skip' => $_POST['arguments']['skip'],
            'take' => $_POST['arguments']['take'],
        ]);

        $batch->run();

        wp_send_json_success($batch->summary());

        exit;
    }
}
