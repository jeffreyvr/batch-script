<div class="wrap" style="margin-top: 0; margin-right: 0; height: 100vh; overflow:hidden;">

    <div style="display: flex; gap: 10px; width: 100%; align-items: items-start;">
        <form id="batch-form" style="width: 30%; padding: 0 15px;">
            <h1><?php _e('Batch Script', 'batch-script'); ?></h1>

            <div class="form-group">
                <label for="take">Take</label>
                <input type="number" min="1" name="take" id="take" class="disable-when-running">
            </div>

            <div class="form-group">
                <label for="skip">Skip</label>
                <input type="number" min="1" name="skip" id="skip" class="disable-when-running">
            </div>

            <div class="form-group">
                <label for="interval">Interval</label>
                <input type="number" min="1" name="interval" id="interval" class="disable-when-running">
            </div>

            <button class="button button-primary disable-when-running" id="start">Start</button>
            <button class="button" id="stop">Stop</button>

            <input type="hidden" name="batch_id" value="<?php echo wp_generate_uuid4(); ?>">
        </form>

        <div style="width: 70%; border-left: 1px solid #ddd;">
            <textarea name="code" id="code"><?php echo addslashes("<?php \n\n// your code here"); ?></textarea>

            <div id="output" style="overflow-y: scroll;"></div>
        </div>
    </div>

    <style>
        #wpbody-content  {
            padding: 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 5px;
            border: 1px solid #eee;
        }
        #wpcontent {
            padding-left: 0;
        }
        .log-message {
            border-bottom: 1px solid #eee;
            padding: 40px 10px 10px 10px;
            margin: 10px 0;
            position:relative;
        }
        .timestamp {
            position: absolute;
            top: 5px;
            left: 5px;
            color: #999;
            font-size: 12px;
            margin-bottom: 5px;
            border: 1px solid #ddd;
            padding: 2px 5px;
            border-radius: 3px;
        }

        .CodeMirror {
            height: 50vh;
        }

        #output {
            position: relative;
            height: 50vh;
            border-top: 1px solid #ddd;
            font-family: monospace;
            background: #eee;
        }
    </style>

    <script>
    jQuery(function($) {
        class BatchProcessor {
            constructor(formSelector, outputSelector, startSelector, stopSelector, intervalSelector, skipSelector, takeSelector, codeSelector) {
                this.form = $(formSelector);
                this.output = $(outputSelector);
                this.start = $(startSelector);
                this.stop = $(stopSelector);
                this.interval = $(intervalSelector);
                this.skipSelector = $(skipSelector);
                this.takeSelector = $(takeSelector);
                this.codeSelector = $(codeSelector);
                this.isRunning = false;
                this.finished = false;
                this.hasBeenRunning = false;
                this.skip = 0;
                this.take = 0;
                this.initEventHandlers();
                this.initCodeEditor();
            }

            initEventHandlers() {
                this.start.on('click', (e) => {
                    e.preventDefault();

                    if(this.finished) {
                        alert('Batch has already finished.');

                        return;
                    }

                    if (!this.hasBeenRunning) {
                        this.skip = parseInt(this.skipSelector.val());
                        this.take = parseInt(this.takeSelector.val());
                        this.hasBeenRunning = true;
                    }

                    this.isRunning = true;
                    this.disableForm();
                    this.process();
                });

                this.stop.on('click', (e) => {
                    e.preventDefault();
                    this.isRunning = false;
                    this.enableForm();
                });
            }

            disableForm() {
                this.form.find('.disable-when-running').attr('disabled', true);
            }

            enableForm() {
                this.form.find('.disable-when-running').attr('disabled', false);
            }

            initCodeEditor() {
                var editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
                editorSettings.codemirror = _.extend(
                    {},
                    editorSettings.codemirror,
                    {
                        mode: 'application/x-httpd-php',
                        indentUnit: 4,
                        tabSize: 4,
                        lineNumbers: true
                    }
                );
                this.codeEditor = wp.codeEditor.initialize(this.codeSelector, editorSettings);
            }

            clearOutput() {
                this.output.html('');
            }

            process() {
                if (!this.isRunning) {
                    return;
                }

                $.post(ajaxurl, {
                    batch_id: this.form.find('[name="batch_id"]').val(),
                    action: 'bs_run',
                    arguments: {
                        skip: this.skip,
                        take: this.take,
                        code: this.codeEditor.codemirror.getValue(),
                    },
                }, (response) => {
                    this.output.append(response.data.message);

                    if(response.data.errorLine) {
                        this.codeEditor.codemirror.focus();
                        this.codeEditor.codemirror.setCursor(response.data.errorLine - 1);
                    }

                    this.skip += response.data.processed;

                    if (response.data.continue) {
                        setTimeout(() => {
                            this.process();
                        }, this.interval.val() * 1000);
                    } else {
                        this.isRunning = false;

                        if (response.data.finished) {
                            this.finished = true;
                        }
                    }
                });
            }
        }

        let batchProcessor = new BatchProcessor('#batch-form', '#output', '#start', '#stop', '#interval', '#skip', '#take', '#code');
    });
    </script>
</div>
