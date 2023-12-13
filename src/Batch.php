<?php

namespace Jeffreyvr\BatchScript;

class Batch
{
    public string $message = '';

    public bool $continue = true;

    public bool $finished = false;

    public int $processed = 0;

    public ?int $errorLine = null;

    public function __construct(public string $id, public array $arguments = [])
    {
        $exists = get_option('bs_run_'.$id, false);

        if (! $exists) {
            add_option('bs_run_'.$id, [
                'arguments' => $this->arguments
            ]);
        } else {
            update_option('bs_run_'.$id, array_merge($exists, [
                'arguments' => $this->arguments
            ]));
        }
    }

    public function keep($key, $value)
    {
        $option = get_option('bs_run_'.$this->id);

        $option['records'][$key] = $value;

        update_option('bs_run_'.$this->id, $option);

        return $this;
    }

    public function get($key, $fallback = null)
    {
        $option = get_option('bs_run_'.$this->id);

        return $option['records'][$key] ?? $fallback;
    }

    public function forget($key)
    {
        $option = get_option('bs_run_'.$this->id);

        unset($option['records'][$key]);

        update_option('bs_run_'.$this->id, $option);

        return $this;
    }

    public function run()
    {
        extract($this->arguments);

        $code = str_replace(['<?php', '<?', '?>'], '', $code);

        // Handy alias for $this in the code editor.
        $process = $this;

        try {
            eval(stripslashes($code));
        } catch (\Throwable $th) {
            $this->error($th->getMessage().' on line '.$th->getLine());
            $this->errorLine = $th->getLine();
        }

        return $this;
    }

    public function error($message)
    {
        $this->log('<div style="color: red;"><strong>ERROR:</strong> '.$message.'</div>');

        $this->stop();

        return $this;
    }

    public function log(...$data)
    {
        $this->message .= '<div class="log-message">';

        $this->message .= '<div class="timestamp">'.wp_date('Y-m-d H:i:s').'</div>';

        $this->message .= implode("<br>", $data);

        $this->message .= '</div>';

        return $this;
    }

    public function stop()
    {
        $this->continue = false;

        $this->log('Stopped');
    }

    public function finish()
    {
        $this->finished = true;
        $this->continue = false;

        $this->log('Finished');
    }

    public function processed($amount)
    {
        $this->processed = $amount;
    }

    public function summary()
    {
        return [
            'id' => $this->id,
            'arguments' => $this->arguments,
            'continue' => $this->continue,
            'message' => $this->message,
            'finished' => $this->finished,
            'processed' => $this->processed,
            'errorLine' => $this->errorLine,
        ];
    }
}
