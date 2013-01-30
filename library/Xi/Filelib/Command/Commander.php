<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Command;

/**
 * Commandeers can command a command factory
 */
interface Commander
{
    /**
     * Returns an array of commands and strategies
     *
     * @return array
     */
    public function getCommandDefinitions();
}
