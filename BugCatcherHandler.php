<?php

namespace Grav\Plugin;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class BugCatcherHandler extends AbstractProcessingHandler
{
    private $callback;

    /**
     * Writes the record down to the log of the implementing handler
     * @param  array $record
     */
    protected function write(array $record)
    {
        call_user_func($this->callback, $record);
    }

    /**
     * @param callback $callback The callback to fire
     * @param int $level The minimum logging level at which this handler will be triggered
     * @param Boolean $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($callback, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->callback = $callback;
    }
}