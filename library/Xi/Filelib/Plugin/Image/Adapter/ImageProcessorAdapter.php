<?php

namespace Xi\Filelib\Plugin\Image\Adapter;

use Xi\Filelib\Plugin\Image\CommandHelper;

interface ImageProcessorAdapter
{
    public function execute($source, $target, CommandHelper $command);
}
