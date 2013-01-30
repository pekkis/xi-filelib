<?php

namespace Xi\Filelib\Command;

use Xi\Filelib\Queue\Queue;

class AsynchronousExecutionStrategy implements ExecutionStrategy
{
    private $queue;

    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    public function execute(Command $command)
    {
        return $this->queue->enqueue($command);
    }
}
