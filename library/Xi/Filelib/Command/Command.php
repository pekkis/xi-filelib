<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Command;

use Xi\Filelib\Queue\Enqueueable;
use Serializable;

interface Command extends Enqueueable, Serializable
{
    public function execute();
}
