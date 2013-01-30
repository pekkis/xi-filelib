<?php

namespace Xi\Filelib\Command;

interface ExecutionStrategy
{
    public function execute(Command $command);
}
