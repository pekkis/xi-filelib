<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Command;

use Xi\Filelib\Queue\Queue;
use Xi\Filelib\Tool\UuidGenerator\PHPUuidGenerator;
use Xi\Filelib\Exception\InvalidArgumentException;

class CommandFactory
{
    const STRATEGY_SYNCHRONOUS = 'sync';
    const STRATEGY_ASYNCHRONOUS = 'async';

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var Commander
     */
    private $commander;

    /**
     * @var array
     */
    private $commandDefinitions = array();

    /**
     * @param Queue $queue
     * @param Commander $commander
     */
    public function __construct(Queue $queue, Commander $commander)
    {
        $this->queue = $queue;
        $this->commander = $commander;
        foreach ($commander->getCommandDefinitions() as $cd) {
            $this->addCommandDefinition($cd);
        }
    }

    /**
     * @param string $command
     * @return string
     */
    public function getCommandStrategy($command)
    {
        return $this->getCommandDefinition($command)->getStrategy();
    }

    /**
     * @param string $command
     * @param string $strategy
     * @return CommandFactory
     */
    public function setCommandStrategy($command, $strategy)
    {
        $this->getCommandDefinition($command)->setStrategy($strategy);
        return $this;
    }

    /**
     * @param string $commandClass
     * @param array $args
     * @return Command
     */
    public function createCommand($command, array $args = array())
    {
        $commandDefinition = $this->getCommandDefinition($command);
        $reflClass = new \ReflectionClass($commandDefinition->getClass());
        $command = $reflClass->newInstanceArgs($args);
        return new Executable($command, $this->createExecutionStrategy($commandDefinition->getStrategy()));
    }

    private function createExecutionStrategy($strategy)
    {
        switch ($strategy) {
            case self::STRATEGY_SYNCHRONOUS:
                $strategy = new SynchronousExecutionStrategy();
                break;
            case self::STRATEGY_ASYNCHRONOUS:
                $strategy = new AsynchronousExecutionStrategy($this->queue);
                break;
        }

        return $strategy;
    }

    /**
     * @param CommandDefinition $commandDefinition
     * @return CommandFactory
     */
    private function addCommandDefinition(CommandDefinition $commandDefinition)
    {
        $this->commandDefinitions[$commandDefinition->getName()] = $commandDefinition;
        return $this;
    }

    /**
     * @param string $name
     * @return CommandDefinition
     * @throws InvalidArgumentException
     */
    private function getCommandDefinition($command)
    {

        if (!isset($this->commandDefinitions[$command])) {
            throw new InvalidArgumentException(
                sprintf("Command definition '%s' not found", $command)
            );
        }

        return $this->commandDefinitions[$command];
    }
}
