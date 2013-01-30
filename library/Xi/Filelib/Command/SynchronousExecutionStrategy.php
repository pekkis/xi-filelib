<?php

namespace Xi\Filelib\Command;

class SynchronousExecutionStrategy implements ExecutionStrategy
{
    public function execute(Command $command)
    {
        return $command->execute();
    }
}
