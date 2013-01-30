<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Command;

/**
 * Command definition
 */
class CommandDefinition
{
    /**
     * @param string $name
     * @param string $class
     * @param string $strategy
     */
    public function __construct($name, $class, $strategy)
    {
        $this->name = $name;
        $this->class = $class;
        $this->setStrategy($strategy);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $strategy
     */
    public function setStrategy($strategy)
    {
        if (!in_array(
            $strategy,
            array(CommandFactory::STRATEGY_ASYNCHRONOUS, CommandFactory::STRATEGY_SYNCHRONOUS)
        )) {
            throw new \InvalidArgumentException("Invalid command strategy '{$strategy}'");
        }

        $this->strategy = $strategy;
    }

    /**
     * @return string
     */
    public function getStrategy()
    {
        return $this->strategy;
    }
}
